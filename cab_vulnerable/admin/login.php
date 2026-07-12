<?php 
session_start();
require_once '../includes/config.php';

// If already logged in as admin, redirect to dashboard
if(isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

// ⚠️ VULNERABILITY: SQL Injection in admin login
// ⚠️ VULNERABILITY: Weak default credentials
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // ⚠️ VULNERABILITY: Direct string concatenation allows SQL injection
    $query = "SELECT * FROM admins WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $query);
    
    if($result && mysqli_num_rows($result) > 0) {
        $admin = mysqli_fetch_assoc($result);
        
        // ⚠️ VULNERABILITY: No session regeneration
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Invalid admin credentials!';
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
<body class="bg-dark">
    <div class="container">
        <div class="row justify-content-center" style="min-height: 100vh; align-items: center;">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0 text-center">
                            <i class="fas fa-shield-alt"></i> Admin Login
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <div class="vulnerability-warning">
                            <i class="fas fa-exclamation-triangle"></i> Vulnerable Admin Panel
                        </div>
                        
                        <form method="POST" action="" class="mt-3">
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
                        
                        <div class="alert alert-info mt-3">
                            <strong>Default Credentials:</strong><br>
                            Username: fadmin<br>
                            Password: fadmin123
                        </div>
                        
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-bug"></i> <strong>Try SQL Injection:</strong><br>
                            Username: <code>admin' OR '1'='1' --</code> OR <code> ' OR 1=1 #</code>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="<?php echo BASE_URL; ?>index.php" class="text-muted">
                                <i class="fas fa-arrow-left"></i> Back to Main Site
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
