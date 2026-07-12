<?php
error_reporting(0);
// ✅ SECURE VERSION - Production-Grade Security Configuration

// ✅ SECURITY: Session Configuration (MUST be set BEFORE session_start)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS
ini_set('session.cookie_samesite', 'Strict');

// Start session AFTER ini_set
session_start();

// Database configuration (same database as vulnerable version)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'cab_booking'); // SAME DATABASE as vulnerable version

// Base URL
define('BASE_URL', '/SecureRide/cab_secure/');

// ✅ SECURITY: PDO Connection with Exception Handling
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Session timeout (30 minutes)
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    session_unset();
    session_destroy();
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

// ✅ SECURITY: CSRF Token Generation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ✅ SECURITY: Security Headers
header("X-Frame-Options: SAMEORIGIN");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

// ✅ SECURITY HELPER FUNCTIONS

// XSS Protection - Output Escaping
function escape_output($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Input Sanitization
function clean_input($data) {
    return trim(strip_tags($data));
}

// CSRF Token Validation
function validate_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Session Regeneration
function regenerate_session() {
    session_regenerate_id(true);
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ✅ SECURITY: Server-Side Fare Calculation (Price Manipulation Prevention)
// Base fare: ₹50 + ₹15 per km — calculated server-side using geocoded distance
function calculate_fare($distance) {
    $base_fare = 50.00;  // ₹50 base
    $per_km    = 15.00;  // ₹15 per km
    return round($base_fare + ($distance * $per_km), 2);
}

// ✅ SECURITY: File Upload Validation (server-side only – no trust in client MIME)
function validate_file_upload($file) {
    $max_size = 2 * 1024 * 1024; // 2MB

    // ✅ SECURITY: Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return "File upload error. Please try again.";
    }

    // ✅ SECURITY: File size check
    if ($file['size'] > $max_size) {
        return "File size must be less than 2MB.";
    }

    // ✅ SECURITY: Whitelist extension – ONLY .jpg / .jpeg
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg'])) {
        return "Only .jpg and .jpeg image files are allowed.";
    }

    // ✅ SECURITY: Server-side MIME detection using finfo (ignores client-supplied type)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $real_mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($real_mime, ['image/jpeg'])) {
        return "Invalid file content. Only JPEG images are permitted.";
    }

    // ✅ SECURITY: Verify file is a genuine image using getimagesize()
    // This catches renamed PHP/HTML/XSS/PDF payloads that pass extension checks
    $image_info = @getimagesize($file['tmp_name']);
    if ($image_info === false) {
        return "Uploaded file is not a valid image.";
    }
    if (!in_array($image_info['mime'], ['image/jpeg'])) {
        return "File content does not match an allowed image type.";
    }

    return true;
}

// ✅ SECURITY: Redirect Helper
function redirect($location) {
    header('Location: ' . BASE_URL . $location);
    exit();
}
?>
