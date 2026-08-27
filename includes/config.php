<?php
// =============================================
// CONFIGURATION FILE
// CHERY MOBIL OFFICIAL
// =============================================

// ===== DATABASE =====
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'car_dealer');

// ===== SITE =====
define('SITE_NAME', 'Chery Mobil Official');
define('SITE_URL', 'http://localhost/car-dealer-website/');
define('ADMIN_EMAIL', 'admin@cherymobil.com');
define('WHATSAPP_NUMBER', '6282117985579');
define('SITE_TIMEZONE', 'Asia/Jakarta');
date_default_timezone_set(SITE_TIMEZONE);

// ===== SECURITY =====
define('HASH_ALGO', 'sha256');
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes
define('CSRF_TOKEN_LENGTH', 32);
define('PASSWORD_MIN_LENGTH', 8);

// ===== PATHS =====
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads/');
define('ASSETS_PATH', SITE_URL . 'assets/');
define('ADMIN_PATH', SITE_URL . 'admin/');
define('PAGES_PATH', SITE_URL . 'pages/');

// ===== UPLOAD =====
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
define('IMAGE_QUALITY', 85);

// ===== PAGINATION =====
define('PRODUCTS_PER_PAGE', 12);
define('ORDERS_PER_PAGE', 20);
define('USERS_PER_PAGE', 20);

// ===== CURRENCY =====
define('CURRENCY_SYMBOL', 'Rp');
define('CURRENCY_FORMAT', 'IDR');

// ===== ERROR REPORTING =====
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', ROOT_PATH . '/logs/error.log');

// ===== SECURITY HEADERS =====
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self' https: http: data: 'unsafe-inline' 'unsafe-eval'");
?>
