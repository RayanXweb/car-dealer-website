<?php
require_once 'database.php';

// =============================================
// SECURITY FUNCTIONS
// =============================================

function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function sanitizeSQL($input) {
    return db()->real_escape_string($input);
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateToken();
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

function generateTransactionNumber() {
    return 'TRX-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -4));
}

// =============================================
// AUTH FUNCTIONS
// =============================================

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function isUser() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user';
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . SITE_URL . 'pages/login.php');
        exit();
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        $_SESSION['error'] = 'Anda tidak memiliki akses ke halaman ini.';
        header('Location: ' . SITE_URL . 'pages/index.php');
        exit();
    }
}

function requireGuest() {
    if (isLoggedIn()) {
        header('Location: ' . SITE_URL . 'pages/index.php');
        exit();
    }
}

// =============================================
// USER FUNCTIONS
// =============================================

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = db();
    $stmt = $db->prepare("SELECT id, username, email, full_name, phone, avatar, role, status FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getUserById($id) {
    $db = db();
    $stmt = $db->prepare("SELECT id, username, email, full_name, phone, avatar, role, status, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

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
    
    if (isset($data['password']) && !empty($data['password'])) {
        $fields[] = "password = ?";
        $params[] = hashPassword($data['password']);
        $types .= "s";
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

// =============================================
// PRODUCT FUNCTIONS
// =============================================

function getProduct($id) {
    $db = db();
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getProducts($limit = null, $offset = 0, $filters = []) {
    $db = db();
    $sql = "SELECT * FROM products WHERE status != 'sold'";
    $params = [];
    $types = "";
    
    // Search
    if (!empty($filters['search'])) {
        $search = "%{$filters['search']}%";
        $sql .= " AND (name LIKE ? OR brand LIKE ? OR model LIKE ? OR description LIKE ?)";
        $params = array_merge($params, [$search, $search, $search, $search]);
        $types .= "ssss";
    }
    
    // Brand
    if (!empty($filters['brand'])) {
        $sql .= " AND brand = ?";
        $params[] = $filters['brand'];
        $types .= "s";
    }
    
    // Type
    if (!empty($filters['type'])) {
        $sql .= " AND type = ?";
        $params[] = $filters['type'];
        $types .= "s";
    }
    
    // Price range
    if (!empty($filters['price_min'])) {
        $sql .= " AND price >= ?";
        $params[] = $filters['price_min'];
        $types .= "d";
    }
    
    if (!empty($filters['price_max'])) {
        $sql .= " AND price <= ?";
        $params[] = $filters['price_max'];
        $types .= "d";
    }
    
    // Featured
    if (!empty($filters['featured'])) {
        $sql .= " AND is_featured = 1";
    }
    
    // New
    if (!empty($filters['new'])) {
        $sql .= " AND is_new = 1";
    }
    
    // Sorting
    $sort = $filters['sort'] ?? 'newest';
    switch ($sort) {
        case 'price_low':
            $sql .= " ORDER BY price ASC";
            break;
        case 'price_high':
            $sql .= " ORDER BY price DESC";
            break;
        case 'popular':
            $sql .= " ORDER BY views DESC";
            break;
        case 'newest':
        default:
            $sql .= " ORDER BY created_at DESC";
            break;
    }
    
    if ($limit !== null) {
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";
    }
    
    $stmt = $db->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getTotalProducts($filters = []) {
    $db = db();
    $sql = "SELECT COUNT(*) as total FROM products WHERE status != 'sold'";
    $params = [];
    $types = "";
    
    if (!empty($filters['search'])) {
        $search = "%{$filters['search']}%";
        $sql .= " AND (name LIKE ? OR brand LIKE ? OR model LIKE ? OR description LIKE ?)";
        $params = array_merge($params, [$search, $search, $search, $search]);
        $types .= "ssss";
    }
    
    if (!empty($filters['brand'])) {
        $sql .= " AND brand = ?";
        $params[] = $filters['brand'];
        $types .= "s";
    }
    
    if (!empty($filters['type'])) {
        $sql .= " AND type = ?";
        $params[] = $filters['type'];
        $types .= "s";
    }
    
    $stmt = $db->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['total'] ?? 0;
}

function getFeaturedProducts($limit = 6) {
    $db = db();
    $stmt = $db->prepare("SELECT * FROM products WHERE status = 'available' AND is_featured = 1 ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getNewProducts($limit = 6) {
    $db = db();
    $stmt = $db->prepare("SELECT * FROM products WHERE status = 'available' AND is_new = 1 ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getRelatedProducts($productId, $brand, $limit = 4) {
    $db = db();
    $stmt = $db->prepare("SELECT * FROM products WHERE brand = ? AND id != ? AND status = 'available' ORDER BY created_at DESC LIMIT ?");
    $stmt->bind_param("sii", $brand, $productId, $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getProductBrands() {
    $db = db();
    return $db->query("SELECT DISTINCT brand FROM products WHERE status = 'available' ORDER BY brand")->fetch_all(MYSQLI_ASSOC);
}

function getProductTypes() {
    $db = db();
    return $db->query("SELECT DISTINCT type FROM products WHERE status = 'available' AND type IS NOT NULL ORDER BY type")->fetch_all(MYSQLI_ASSOC);
}

// =============================================
// CART FUNCTIONS
// =============================================

function getCart($userId = null, $sessionId = null) {
    $db = db();
    $sql = "SELECT c.*, p.name, p.price, p.image, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE ";
    
    if ($userId) {
        $sql .= "c.user_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $userId);
    } else if ($sessionId) {
        $sql .= "c.session_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("s", $sessionId);
    } else {
        return [];
    }
    
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getCartCount($userId = null, $sessionId = null) {
    $items = getCart($userId, $sessionId);
    return array_sum(array_column($items, 'quantity'));
}

function getCartTotal($userId = null, $sessionId = null) {
    $items = getCart($userId, $sessionId);
    $total = 0;
    foreach ($items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

function addToCart($productId, $quantity = 1, $userId = null, $sessionId = null) {
    $db = db();
    
    if ($userId) {
        $check = $db->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $check->bind_param("ii", $userId, $productId);
    } else if ($sessionId) {
        $check = $db->prepare("SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ?");
        $check->bind_param("si", $sessionId, $productId);
    } else {
        return false;
    }
    
    $check->execute();
    $result = $check->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $newQty = $row['quantity'] + $quantity;
        $update = $db->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $update->bind_param("ii", $newQty, $row['id']);
        return $update->execute();
    } else {
        if ($userId) {
            $insert = $db->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $insert->bind_param("iii", $userId, $productId, $quantity);
        } else {
            $insert = $db->prepare("INSERT INTO cart (session_id, product_id, quantity) VALUES (?, ?, ?)");
            $insert->bind_param("sii", $sessionId, $productId, $quantity);
        }
        return $insert->execute();
    }
}

function removeFromCart($cartId, $userId = null, $sessionId = null) {
    $db = db();
    $sql = "DELETE FROM cart WHERE id = ?";
    $params = [$cartId];
    $types = "i";
    
    if ($userId) {
        $sql .= " AND user_id = ?";
        $params[] = $userId;
        $types .= "i";
    } else if ($sessionId) {
        $sql .= " AND session_id = ?";
        $params[] = $sessionId;
        $types .= "s";
    }
    
    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$params);
    return $stmt->execute();
}

function clearCart($userId = null, $sessionId = null) {
    $db = db();
    if ($userId) {
        $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
    } else if ($sessionId) {
        $stmt = $db->prepare("DELETE FROM cart WHERE session_id = ?");
        $stmt->bind_param("s", $sessionId);
    } else {
        return false;
    }
    return $stmt->execute();
}

function updateCartQuantity($cartId, $quantity, $userId = null, $sessionId = null) {
    $db = db();
    $sql = "UPDATE cart SET quantity = ? WHERE id = ?";
    $params = [$quantity, $cartId];
    $types = "ii";
    
    if ($userId) {
        $sql .= " AND user_id = ?";
        $params[] = $userId;
        $types .= "i";
    } else if ($sessionId) {
        $sql .= " AND session_id = ?";
        $params[] = $sessionId;
        $types .= "s";
    }
    
    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$params);
    return $stmt->execute();
}

// =============================================
// ORDER FUNCTIONS
// =============================================

function createOrder($userId, $data) {
    $db = db();
    $orderNumber = generateOrderNumber();
    $totalAmount = $data['total'] ?? 0;
    $discount = $data['discount'] ?? 0;
    $finalAmount = $totalAmount - $discount;
    
    $db->beginTransaction();
    
    try {
        // Insert order
        $stmt = $db->prepare("INSERT INTO orders (
            user_id, order_number, total_amount, discount, final_amount,
            customer_name, customer_phone, customer_email, customer_address, notes,
            status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
        
        $stmt->bind_param("isdddsssss", 
            $userId, $orderNumber, $totalAmount, $discount, $finalAmount,
            $data['customer_name'], $data['customer_phone'], $data['customer_email'],
            $data['customer_address'], $data['notes']
        );
        $stmt->execute();
        $orderId = $db->insertId;
        
        // Insert order items
        foreach ($data['items'] as $item) {
            $subtotal = $item['price'] * $item['quantity'];
            $stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iiidd", $orderId, $item['product_id'], $item['quantity'], $item['price'], $subtotal);
            $stmt->execute();
        }
        
        // Clear cart
        clearCart($userId);
        
        $db->commit();
        logActivity($userId, 'create_order', "Order created: $orderNumber");
        
        return ['success' => true, 'order_id' => $orderId, 'order_number' => $orderNumber];
        
    } catch (Exception $e) {
        $db->rollback();
        logActivity($userId, 'order_error', "Order creation failed: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function getOrder($id) {
    $db = db();
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getOrderByNumber($number) {
    $db = db();
    $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ?");
    $stmt->bind_param("s", $number);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getOrderItems($orderId) {
    $db = db();
    $stmt = $db->prepare("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getUserOrders($userId, $limit = null, $offset = 0) {
    $db = db();
    $sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
    if ($limit !== null) {
        $sql .= " LIMIT ? OFFSET ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("iii", $userId, $limit, $offset);
    } else {
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $userId);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function updateOrderStatus($orderId, $status) {
    $db = db();
    $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $orderId);
    return $stmt->execute();
}

// =============================================
// OFFER FUNCTIONS
// =============================================

function createOffer($data) {
    $db = db();
    $stmt = $db->prepare("INSERT INTO offers (user_id, product_id, customer_name, customer_phone, customer_email, message, offer_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'new')");
    $stmt->bind_param("iissssd", $data['user_id'], $data['product_id'], $data['customer_name'], $data['customer_phone'], $data['customer_email'], $data['message'], $data['offer_amount']);
    return $stmt->execute();
}

function getOffers($status = null) {
    $db = db();
    $sql = "SELECT o.*, p.name as product_name FROM offers o LEFT JOIN products p ON o.product_id = p.id";
    if ($status) {
        $sql .= " WHERE o.status = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("s", $status);
    } else {
        $stmt = $db->prepare($sql . " ORDER BY o.created_at DESC");
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function updateOfferStatus($offerId, $status, $response = null) {
    $db = db();
    $sql = "UPDATE offers SET status = ?";
    $params = [$status];
    $types = "s";
    
    if ($response !== null) {
        $sql .= ", admin_response = ?, responded_at = NOW()";
        $params[] = $response;
        $types .= "s";
    }
    
    $sql .= " WHERE id = ?";
    $params[] = $offerId;
    $types .= "i";
    
    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$params);
    return $stmt->execute();
}

// =============================================
// WISHLIST FUNCTIONS
// =============================================

function addToWishlist($userId, $productId) {
    $db = db();
    $stmt = $db->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $userId, $productId);
    return $stmt->execute();
}

function removeFromWishlist($userId, $productId) {
    $db = db();
    $stmt = $db->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $userId, $productId);
    return $stmt->execute();
}

function getWishlist($userId) {
    $db = db();
    $stmt = $db->prepare("SELECT p.* FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.user_id = ? ORDER BY w.created_at DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function isInWishlist($userId, $productId) {
    $db = db();
    $stmt = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

// =============================================
// AD FUNCTIONS
// =============================================

function getActiveAds($position = null) {
    $db = db();
    $sql = "SELECT * FROM ads WHERE is_active = 1 AND (start_date IS NULL OR start_date <= NOW()) AND (end_date IS NULL OR end_date >= NOW())";
    if ($position) {
        $sql .= " AND position = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("s", $position);
    } else {
        $stmt = $db->prepare($sql);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function incrementAdClick($adId) {
    $db = db();
    $stmt = $db->prepare("UPDATE ads SET clicks = clicks + 1 WHERE id = ?");
    $stmt->bind_param("i", $adId);
    return $stmt->execute();
}

function incrementAdImpression($adId) {
    $db = db();
    $stmt = $db->prepare("UPDATE ads SET impressions = impressions + 1 WHERE id = ?");
    $stmt->bind_param("i", $adId);
    return $stmt->execute();
}

// =============================================
// LOG FUNCTIONS
// =============================================

function logActivity($userId, $action, $description) {
    $db = db();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Get username
    $username = null;
    if ($userId) {
        $user = getUserById($userId);
        $username = $user['username'] ?? null;
    }
    
    $stmt = $db->prepare("INSERT INTO activity_logs (user_id, username, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $userId, $username, $action, $description, $ip, $userAgent);
    return $stmt->execute();
}

function getActivityLogs($limit = 50, $offset = 0) {
    $db = db();
    $stmt = $db->prepare("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// =============================================
// SETTINGS FUNCTIONS
// =============================================

function getSetting($key) {
    $db = db();
    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['setting_value'];
    }
    return null;
}

function getSettings($group = null) {
    $db = db();
    $sql = "SELECT * FROM settings";
    if ($group) {
        $sql .= " WHERE setting_group = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("s", $group);
    } else {
        $stmt = $db->prepare($sql);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $settings = [];
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

function updateSetting($key, $value) {
    $db = db();
    $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->bind_param("sss", $key, $value, $value);
    return $stmt->execute();
}

function updateSettings($settings) {
    $db = db();
    $db->beginTransaction();
    try {
        foreach ($settings as $key => $value) {
            updateSetting($key, $value);
        }
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollback();
        return false;
    }
}

// =============================================
// UPLOAD FUNCTIONS
// =============================================

function uploadImage($file, $targetDir = null, $prefix = '') {
    if ($targetDir === null) {
        $targetDir = UPLOAD_PATH;
    }
    
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Gagal upload file'];
    }
    
    $fileName = basename($file['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    if (!in_array($fileExt, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'error' => 'Tipe file tidak diizinkan. Hanya: ' . implode(', ', ALLOWED_EXTENSIONS)];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'Ukuran file terlalu besar. Maksimal ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB'];
    }
    
    $newName = $prefix . time() . '_' . uniqid() . '.' . $fileExt;
    $targetPath = $targetDir . $newName;
    
    // Resize image if it's an image
    if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'webp'])) {
        $image = imageCreateFromAny($file['tmp_name']);
        if ($image) {
            $maxWidth = 1200;
            $maxHeight = 800;
            $width = imagesx($image);
            $height = imagesy($image);
            
            if ($width > $maxWidth || $height > $maxHeight) {
                $ratio = min($maxWidth / $width, $maxHeight / $height);
                $newWidth = round($width * $ratio);
                $newHeight = round($height * $ratio);
                
                $resized = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                
                imagejpeg($resized, $targetPath, IMAGE_QUALITY);
                imagedestroy($resized);
                imagedestroy($image);
                return ['success' => true, 'filename' => $newName];
            }
        }
    }
    
    // Move file directly
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $newName];
    }
    
    return ['success' => false, 'error' => 'Gagal menyimpan file'];
}

function imageCreateFromAny($filepath) {
    $type = exif_imagetype($filepath);
    switch ($type) {
        case IMAGETYPE_JPEG:
            return imagecreatefromjpeg($filepath);
        case IMAGETYPE_PNG:
            return imagecreatefrompng($filepath);
        case IMAGETYPE_GIF:
            return imagecreatefromgif($filepath);
        case IMAGETYPE_WEBP:
            return imagecreatefromwebp($filepath);
        default:
            return false;
    }
}

function deleteImage($filename, $targetDir = null) {
    if ($targetDir === null) {
        $targetDir = UPLOAD_PATH;
    }
    $path = $targetDir . $filename;
    if (file_exists($path)) {
        return unlink($path);
    }
    return true;
}

// =============================================
// FORMAT FUNCTIONS
// =============================================

function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function formatDate($date, $format = 'd/m/Y H:i') {
    return date($format, strtotime($date));
}

function formatDateIndonesian($date) {
    $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $timestamp = strtotime($date);
    return date('d', $timestamp) . ' ' . $months[date('n', $timestamp) - 1] . ' ' . date('Y H:i', $timestamp);
}

function formatTimeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return $diff . ' detik yang lalu';
    if ($diff < 3600) return floor($diff / 60) . ' menit yang lalu';
    if ($diff < 86400) return floor($diff / 3600) . ' jam yang lalu';
    if ($diff < 604800) return floor($diff / 86400) . ' hari yang lalu';
    return formatDate($datetime);
}

function getStatusBadge($status) {
    $map = [
        'pending' => ['class' => 'warning', 'label' => 'Pending'],
        'confirmed' => ['class' => 'info', 'label' => 'Dikonfirmasi'],
        'processing' => ['class' => 'primary', 'label' => 'Diproses'],
        'shipping' => ['class' => 'primary', 'label' => 'Dikirim'],
        'completed' => ['class' => 'success', 'label' => 'Selesai'],
        'cancelled' => ['class' => 'danger', 'label' => 'Dibatalkan'],
        'refund' => ['class' => 'secondary', 'label' => 'Refund'],
        'new' => ['class' => 'info', 'label' => 'Baru'],
        'seen' => ['class' => 'secondary', 'label' => 'Dilihat'],
        'responded' => ['class' => 'success', 'label' => 'Direspon'],
        'accepted' => ['class' => 'success', 'label' => 'Diterima'],
        'rejected' => ['class' => 'danger', 'label' => 'Ditolak'],
        'available' => ['class' => 'success', 'label' => 'Tersedia'],
        'sold' => ['class' => 'danger', 'label' => 'Terjual'],
        'coming' => ['class' => 'warning', 'label' => 'Segera'],
        'preorder' => ['class' => 'info', 'label' => 'Pre-order'],
    ];
    
    $info = $map[$status] ?? ['class' => 'secondary', 'label' => $status];
    return '<span class="badge bg-' . $info['class'] . '">' . $info['label'] . '</span>';
}

function getStatusBadgeFull($status) {
    $badge = getStatusBadge($status);
    return '<span class="status-badge">' . $badge . '</span>';
}

function truncateText($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

// =============================================
// WHATSAPP FUNCTIONS
// =============================================

function generateWhatsAppLink($number, $message) {
    $number = preg_replace('/[^0-9]/', '', $number);
    if (substr($number, 0, 1) === '0') {
        $number = '62' . substr($number, 1);
    }
    if (substr($number, 0, 1) !== '62' && substr($number, 0, 1) !== '1') {
        $number = '62' . $number;
    }
    $message = urlencode($message);
    return "https://wa.me/{$number}?text={$message}";
}

function generateOfferWhatsAppMessage($product, $offer) {
    $message = "Halo, saya tertarik dengan mobil berikut:\n\n";
    $message .= "🚗 *" . $product['name'] . "*\n";
    $message .= "💰 Harga: " . formatCurrency($product['price']) . "\n";
    $message .= "💬 Penawaran: " . formatCurrency($offer['offer_amount']) . "\n\n";
    $message .= "Nama: " . $offer['customer_name'] . "\n";
    $message .= "📱 " . $offer['customer_phone'] . "\n";
    $message .= "📧 " . ($offer['customer_email'] ?? '-') . "\n\n";
    $message .= "Pesan: " . ($offer['message'] ?? '-');
    return $message;
}

function generateOrderWhatsAppMessage($order, $items) {
    $message = "Halo, saya ingin memesan mobil:\n\n";
    $message .= "📋 *Order: " . $order['order_number'] . "*\n\n";
    $message .= "*Detail Pesanan:*\n";
    foreach ($items as $item) {
        $message .= "• " . $item['name'] . " (x" . $item['quantity'] . ") - " . formatCurrency($item['subtotal']) . "\n";
    }
    $message .= "\n💰 *Total: " . formatCurrency($order['final_amount']) . "*\n\n";
    $message .= "👤 " . $order['customer_name'] . "\n";
    $message .= "📱 " . $order['customer_phone'] . "\n";
    $message .= "📧 " . ($order['customer_email'] ?? '-') . "\n";
    if ($order['customer_address']) {
        $message .= "📍 " . $order['customer_address'] . "\n";
    }
    return $message;
}

// =============================================
// STATISTICS FUNCTIONS
// =============================================

function getDashboardStats() {
    $db = db();
    $stats = [];
    
    // Total products
    $result = $db->query("SELECT COUNT(*) as total FROM products");
    $stats['products'] = $result->fetch_assoc()['total'];
    
    // Total orders
    $result = $db->query("SELECT COUNT(*) as total FROM orders");
    $stats['orders'] = $result->fetch_assoc()['total'];
    
    // Total users
    $result = $db->query("SELECT COUNT(*) as total FROM users");
    $stats['users'] = $result->fetch_assoc()['total'];
    
    // Pending orders
    $result = $db->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
    $stats['pending_orders'] = $result->fetch_assoc()['total'];
    
    // Total revenue
    $result = $db->query("SELECT SUM(final_amount) as total FROM orders WHERE status = 'completed'");
    $stats['revenue'] = $result->fetch_assoc()['total'] ?? 0;
    
    // Monthly revenue
    $result = $db->query("SELECT SUM(final_amount) as total FROM orders WHERE status = 'completed' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
    $stats['revenue_month'] = $result->fetch_assoc()['total'] ?? 0;
    
    // Total views
    $result = $db->query("SELECT SUM(views) as total FROM products");
    $stats['views'] = $result->fetch_assoc()['total'] ?? 0;
    
    return $stats;
}

function getSalesReport($startDate, $endDate) {
    $db = db();
    $stmt = $db->prepare("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as total_orders,
            SUM(final_amount) as total_revenue,
            AVG(final_amount) as average_order
        FROM orders 
        WHERE status IN ('completed', 'shipping', 'processing')
            AND created_at BETWEEN ? AND ?
        GROUP BY DATE(created_at)
        ORDER BY date DESC
    ");
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getPopularProducts($limit = 10) {
    $db = db();
    $stmt = $db->prepare("
        SELECT p.*, COUNT(oi.id) as total_sold
        FROM products p
        LEFT JOIN order_items oi ON p.id = oi.product_id
        LEFT JOIN orders o ON oi.order_id = o.id AND o.status = 'completed'
        GROUP BY p.id
        ORDER BY total_sold DESC, p.views DESC
        LIMIT ?
    ");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// =============================================
// MISC FUNCTIONS
// =============================================

function generateSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function isValidPhone($phone) {
    return preg_match('/^[0-9+\-\s()]{10,15}$/', $phone);
}

function getClientIP() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    return trim($ip);
}

function isAjaxRequest() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function redirect($url) {
    header('Location: ' . $url);
    exit();
}

function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function showFlash() {
    $flash = getFlash();
    if ($flash) {
        $class = $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'error' ? 'danger' : $flash['type']);
        echo '<div class="alert alert-' . $class . ' alert-dismissible fade show">';
        echo $flash['message'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
    }
}
?>
