<?php
error_reporting(0);
// ✅ SECURE AUTHENTICATION & AUTHORIZATION MODULE

// ✅ SECURITY: Require User Login (Prevent URL Manipulation)
function require_auth() {
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        redirect('login.php');
    }
    
    // ✅ SECURITY: Check session validity
    if (!isset($_SESSION['user_email'])) {
        session_unset();
        session_destroy();
        redirect('login.php');
    }
}

// ✅ SECURITY: Require Admin Login (Role-Based Access Control)
function require_admin() {
    if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
        redirect('admin/login.php');
    }
}

// ✅ SECURITY: Get Current User ID (IDOR Prevention)
// Always get user ID from session, NEVER from GET/POST parameters
function get_user_id() {
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
    return (int)$_SESSION['user_id'];
}

// ✅ SECURITY: Get Current Admin ID
function get_admin_id() {
    if (!isset($_SESSION['admin_id'])) {
        redirect('admin/login.php');
    }
    return (int)$_SESSION['admin_id'];
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if admin is logged in
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// ✅ SECURITY: Verify Booking Ownership (IDOR Protection)
function verify_booking_owner($booking_id, $user_id, $pdo) {
    $stmt = $pdo->prepare("SELECT user_id FROM bookings WHERE id = ?");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch();
    
    if (!$booking || $booking['user_id'] != $user_id) {
        return false;
    }
    return true;
}

// ✅ SECURITY: Input Validation Functions
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_phone($phone) {
    return preg_match('/^[0-9]{10,15}$/', $phone);
}

function validate_password($password) {
    // Minimum 8 characters
    return strlen($password) >= 8;
}
?>
