<?php
error_reporting(0);
// RESET ADMIN PASSWORD
require_once '../includes/config.php';

$success = false;

if(isset($_GET['reset']) && $_GET['reset'] == 'yes') {
    try {
        // New password: admin123
        $new_password = 'admin123';
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update admin password
        $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username = 'admin'");
        $stmt->execute([$hashed]);
        
        $success = true;
    } catch(PDOException $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Admin Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning">
                        <h4 class="mb-0">🔑 Reset Admin Password</h4>
                    </div>
                    <div class="card-body">
                        <?php if($success): ?>
                            <div class="alert alert-success">
                                <h5>✅ Password Reset Successful!</h5>
                                <hr>
                                <p><strong>Username:</strong> admin</p>
                                <p><strong>New Password:</strong> admin123</p>
                                <hr>
                                <a href="login.php" class="btn btn-success">Go to Login</a>
                            </div>
                            <div class="alert alert-danger mt-3">
                                <strong>⚠️ SECURITY:</strong> Delete this file (reset_password.php) after logging in!
                            </div>
                        <?php else: ?>
                            <p>Click the button below to reset the admin password to <strong>admin123</strong></p>
                            <a href="?reset=yes" class="btn btn-warning btn-lg w-100">
                                Reset Password
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
