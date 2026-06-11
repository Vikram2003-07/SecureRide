<?php
error_reporting(0);
session_name('SECURE_ADMIN_SESSION');
session_start();
require_once '../includes/config.php';
// Admin already logged in
if(isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

// ✅ SECURITY: Secure Admin Login
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    try {
        // ✅ SECURITY: Prepared Statement
        $stmt = $pdo->prepare("SELECT id, username, password FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        // ✅ SECURITY: Password Verification
        if ($admin && password_verify($password, $admin['password'])) {
            // Session regeneration
            session_regenerate_id(true);
            
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid username or password!';
        }
    } catch(PDOException $e) {
        $error = 'Login failed. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - QuickCab</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0"><i class="fas fa-shield-alt"></i> Admin Login</h4>
                    </div>
                    <div class="card-body">
                        <?php if($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-sign-in-alt"></i> Login as Admin
                            </button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <a href="../index.php">← Back to Main Site</a>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <strong>Test Admin:</strong><br>
                            Username: admin<br>
                            Password: admin123
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
