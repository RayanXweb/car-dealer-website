<?php
// =============================================
// AUTHENTICATION LIBRARY
// CHERY MOBIL OFFICIAL
// =============================================

require_once 'session.php';
require_once 'database.php';
require_once 'functions.php';

/**
 * Login user
 * @param string $username Username or email
 * @param string $password Password
 * @param bool $remember Remember me
 * @return array ['success' => bool, 'user' => array|null, 'error' => string|null]
 */
function login($username, $password, $remember = false) {
    $db = db();
    
    // Check if user exists
    $stmt = $db->prepare("SELECT id, username, email, password, full_name, phone, role, status FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        // Check if user is banned
        if ($user['status'] === 'banned') {
            logActivity($user['id'], 'login_failed', 'Login attempt - Account banned');
            return ['success' => false, 'error' => 'Akun Anda telah diblokir. Hubungi admin.'];
        }
        
        // Verify password
        if (verifyPassword($password, $user['password'])) {
            // Check if account is inactive
            if ($user['status'] === 'inactive') {
                logActivity($user['id'], 'login_failed', 'Login attempt - Account inactive');
                return ['success' => false, 'error' => 'Akun Anda belum aktif. Silakan verifikasi email.'];
            }
            
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['full_name'] ?? $user['username'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_phone'] = $user['phone'] ?? '';
            $_SESSION['login_time'] = time();
            
            // Update last login
            $updateStmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->bind_param("i", $user['id']);
            $updateStmt->execute();
            
            // Remember me
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $expiry = time() + 86400 * 30; // 30 days
                setcookie('remember_token', $token, $expiry, '/', '', false, true);
                
                // Store token in database (optional)
                $tokenStmt = $db->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                $tokenStmt->bind_param("si", $token, $user['id']);
                $tokenStmt->execute();
            }
            
            logActivity($user['id'], 'login', 'User logged in successfully');
            return ['success' => true, 'user' => $user];
        }
        
        logActivity($user['id'], 'login_failed', 'Login attempt - Invalid password');
        return ['success' => false, 'error' => 'Password yang Anda masukkan salah.'];
    }
    
    logActivity(null, 'login_failed', "Login attempt - User not found: $username");
    return ['success' => false, 'error' => 'Username atau email tidak ditemukan.'];
}

/**
 * Register new user
 * @param array $data User data
 * @return array ['success' => bool, 'user_id' => int|null, 'error' => string|null]
 */
function register($data) {
    $db = db();
    
    // Validate
    if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
        return ['success' => false, 'error' => 'Semua field wajib diisi.'];
    }
    
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'error' => 'Format email tidak valid.'];
    }
    
    if (strlen($data['password']) < PASSWORD_MIN_LENGTH) {
        return ['success' => false, 'error' => 'Password minimal ' . PASSWORD_MIN_LENGTH . ' karakter.'];
    }
    
    if (!preg_match('/[A-Z]/', $data['password'])) {
        return ['success' => false, 'error' => 'Password harus mengandung huruf kapital.'];
    }
    
    if (!preg_match('/[a-z]/', $data['password'])) {
        return ['success' => false, 'error' => 'Password harus mengandung huruf kecil.'];
    }
    
    if (!preg_match('/[0-9]/', $data['password'])) {
        return ['success' => false, 'error' => 'Password harus mengandung angka.'];
    }
    
    // Check existing username
    $check = $db->prepare("SELECT id FROM users WHERE username = ?");
    $check->bind_param("s", $data['username']);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        return ['success' => false, 'error' => 'Username sudah terdaftar.'];
    }
    
    // Check existing email
    $check = $db->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $data['email']);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        return ['success' => false, 'error' => 'Email sudah terdaftar.'];
    }
    
    // Hash password
    $hashed = hashPassword($data['password']);
    
    // Insert user
    $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name, phone, role, status) VALUES (?, ?, ?, ?, ?, 'user', 'active')");
    $stmt->bind_param("sssss", $data['username'], $data['email'], $hashed, $data['full_name'], $data['phone']);
    
    if ($stmt->execute()) {
        $userId = $db->insertId;
        logActivity($userId, 'register', "New user registered: {$data['username']}");
        
        // Send welcome email (optional)
        // sendWelcomeEmail($data['email'], $data['username']);
        
        return ['success' => true, 'user_id' => $userId];
    }
    
    return ['success' => false, 'error' => 'Registrasi gagal: ' . $db->error];
}

/**
 * Logout user
 */
function logout() {
    if (isLoggedIn()) {
        $userId = $_SESSION['user_id'];
        logActivity($userId, 'logout', 'User logged out');
    }
    
    // Clear remember me cookie
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }
    
    // Destroy session
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    // Check session
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        // Check session timeout
        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > SESSION_TIMEOUT) {
            logout();
            return false;
        }
        return true;
    }
    
    // Check remember me cookie
    if (isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];
        $db = db();
        $stmt = $db->prepare("SELECT id, username, email, full_name, phone, role, status FROM users WHERE remember_token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        if ($user) {
            // Auto login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['full_name'] ?? $user['username'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_phone'] = $user['phone'] ?? '';
            $_SESSION['login_time'] = time();
            
            logActivity($user['id'], 'auto_login', 'Auto login via remember token');
            return true;
        }
    }
    
    return false;
}

/**
 * Check if user is admin
 * @return bool
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Check if user is regular user
 * @return bool
 */
function isUser() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user';
}

/**
 * Require login - redirect if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . PAGES_PATH . 'login.php');
        exit();
    }
}

/**
 * Require admin - redirect if not admin
 */
function requireAdmin() {
    if (!isAdmin()) {
        $_SESSION['error'] = 'Anda tidak memiliki akses ke halaman ini.';
        header('Location: ' . SITE_URL);
        exit();
    }
}

/**
 * Require guest - redirect if logged in
 */
function requireGuest() {
    if (isLoggedIn()) {
        header('Location: ' . SITE_URL);
        exit();
    }
}

/**
 * Get current user data
 * @return array|null
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = db();
    $stmt = $db->prepare("SELECT id, username, email, full_name, phone, avatar, role, status, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Get user by ID
 * @param int $id User ID
 * @return array|null
 */
function getUserById($id) {
    $db = db();
    $stmt = $db->prepare("SELECT id, username, email, full_name, phone, avatar, role, status, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Update user
 * @param int $id User ID
 * @param array $data User data
 * @return bool
 */
function updateUser($id, $data) {
    $db = db();
    $fields = [];
    $params = [];
    $types = "";
    
    foreach ($data as $key => $value) {
        if ($key !== 'id' && $key !== 'password') {
            $fields[] = "$key = ?";
            $params[] = $value;
            $types .= "s";
        }
    }
    
    // Update password if provided
    if (isset($data['password']) && !empty($data['password'])) {
        if (strlen($data['password']) >= PASSWORD_MIN_LENGTH) {
            $fields[] = "password = ?";
            $params[] = hashPassword($data['password']);
            $types .= "s";
        } else {
            return false;
        }
    }
    
    if (empty($fields)) {
        return false;
    }
    
    $params[] = $id;
    $types .= "i";
    
    $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$params);
    return $stmt->execute();
}

/**
 * Change user password
 * @param int $id User ID
 * @param string $oldPassword Current password
 * @param string $newPassword New password
 * @return array ['success' => bool, 'error' => string|null]
 */
function changePassword($id, $oldPassword, $newPassword) {
    $db = db();
    
    // Get current user
    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if (!$user) {
        return ['success' => false, 'error' => 'User tidak ditemukan'];
    }
    
    // Verify old password
    if (!verifyPassword($oldPassword, $user['password'])) {
        return ['success' => false, 'error' => 'Password lama salah'];
    }
    
    // Validate new password
    if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
        return ['success' => false, 'error' => 'Password minimal ' . PASSWORD_MIN_LENGTH . ' karakter'];
    }
    
    // Update password
    $hashed = hashPassword($newPassword);
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed, $id);
    
    if ($stmt->execute()) {
        logActivity($id, 'password_change', 'Password changed');
        return ['success' => true];
    }
    
    return ['success' => false, 'error' => 'Gagal mengubah password'];
}

/**
 * Reset password (forgot password)
 * @param string $email User email
 * @return array ['success' => bool, 'error' => string|null]
 */
function resetPassword($email) {
    $db = db();
    
    $stmt = $db->prepare("SELECT id, username FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if (!$user) {
        return ['success' => false, 'error' => 'Email tidak ditemukan'];
    }
    
    // Generate reset token
    $token = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', time() + 3600); // 1 hour
    
    $stmt = $db->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE id = ?");
    $stmt->bind_param("ssi", $token, $expiry, $user['id']);
    $stmt->execute();
    
    // Send reset email (implement this)
    // sendResetEmail($email, $token);
    
    logActivity($user['id'], 'password_reset', 'Password reset requested');
    return ['success' => true, 'message' => 'Link reset password telah dikirim ke email Anda'];
}

/**
 * Verify reset token
 * @param string $token Reset token
 * @return array|null User data if valid
 */
function verifyResetToken($token) {
    $db = db();
    $stmt = $db->prepare("SELECT id, username, email FROM users WHERE reset_token = ? AND reset_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Update password with reset token
 * @param string $token Reset token
 * @param string $newPassword New password
 * @return array ['success' => bool, 'error' => string|null]
 */
function updatePasswordWithToken($token, $newPassword) {
    $db = db();
    
    $user = verifyResetToken($token);
    if (!$user) {
        return ['success' => false, 'error' => 'Token tidak valid atau sudah kadaluarsa'];
    }
    
    if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
        return ['success' => false, 'error' => 'Password minimal ' . PASSWORD_MIN_LENGTH . ' karakter'];
    }
    
    $hashed = hashPassword($newPassword);
    $stmt = $db->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?");
    $stmt->bind_param("si", $hashed, $user['id']);
    
    if ($stmt->execute()) {
        logActivity($user['id'], 'password_reset_complete', 'Password reset completed via token');
        return ['success' => true];
    }
    
    return ['success' => false, 'error' => 'Gagal mereset password'];
}

/**
 * Check if email exists
 * @param string $email Email
 * @return bool
 */
function emailExists($email) {
    $db = db();
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

/**
 * Check if username exists
 * @param string $username Username
 * @return bool
 */
function usernameExists($username) {
    $db = db();
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

/**
 * Count total users
 * @return int
 */
function countUsers() {
    $db = db();
    $result = $db->query("SELECT COUNT(*) as total FROM users");
    return $result->fetch_assoc()['total'] ?? 0;
}

/**
 * Count active users
 * @return int
 */
function countActiveUsers() {
    $db = db();
    $result = $db->query("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
    return $result->fetch_assoc()['total'] ?? 0;
}
?>
