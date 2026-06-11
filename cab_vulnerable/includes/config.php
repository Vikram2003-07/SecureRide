<?php
// ⚠️ VULNERABLE VERSION - FOR EDUCATIONAL PURPOSES ONLY
// This configuration file contains intentional security vulnerabilities

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP password
define('DB_NAME', 'cab_booking');

// ⚠️ VULNERABILITY: Using mysqli procedural instead of PDO
// No prepared statements will be used in this version
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// ⚠️ VULNERABILITY: No session security flags
// Session can be hijacked via XSS or network sniffing
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Base URL
define('BASE_URL', '/cab_booking/cab_vulnerable/');

// ⚠️ VULNERABILITY: No CSRF token generation
// Forms vulnerable to Cross-Site Request Forgery

// ⚠️ VULNERABILITY: No Content Security Policy
// No protection against XSS attacks
?>
