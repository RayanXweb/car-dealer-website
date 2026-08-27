<?php
// =============================================
// SESSION MANAGEMENT
// CHERY MOBIL OFFICIAL
// =============================================

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Regenerate session ID periodically
if (!isset($_SESSION['created'])) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
} elseif (time() - $_SESSION['created'] > SESSION_TIMEOUT) {
    // Session timeout
    session_unset();
    session_destroy();
    session_start();
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// Security headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Prevent session fixation
if (!isset($_SESSION['ip_address'])) {
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
} elseif ($_SESSION['ip_address'] !== ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0')) {
    // IP changed - possible session hijacking
    session_unset();
    session_destroy();
    session_start();
    session_regenerate_id(true);
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
}

/**
 * Get CSRF token
 * @return string
 */
function getCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token
 * @return bool
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate CSRF input field
 * @return string
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . getCsrfToken() . '">';
}
?>
