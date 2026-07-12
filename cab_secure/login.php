<?php
error_reporting(0);
require_once 'includes/config.php';
require_once 'includes/auth.php';

// ✅ SECURITY: Redirect if already logged in
if(is_logged_in()) {
    redirect('dashboard.php');
}

$error = '';

// ✅ SECURITY: Secure Login Implementation
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ✅ CSRF Validation
    if (!validate_csrf($_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = clean_input($_POST['email']);
        $password = $_POST['password'];
        
        // ✅ Email validation
        if (!validate_email($email)) {
            $error = 'Invalid email format.';
        } else {
            try {
                // ✅ SECURITY: Prepared Statement (SQL Injection Prevention)
                $stmt = $pdo->prepare("SELECT id, username, email, password FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                // ✅ SECURITY: Password Verification
                if ($user && password_verify($password, $user['password'])) {
                    // ✅ SECURITY: Session Regeneration (Session Fixation Prevention)
                    regenerate_session();
                    
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_email'] = $user['email'];
                    
                    // Redirect to dashboard
                    redirect('dashboard.php');
                } else {
                    $error = 'Invalid email or password!';
                }
            } catch(PDOException $e) {
                $error = 'Login failed. Please try again.';
            }
        }
    }
}

include 'includes/header.php'; 
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0 text-white">Login to Your Account</h4>
            </div>
            <div class="card-body">
                <?php if($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo escape_output($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <!-- ✅ CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>
                
                <div class="text-center mt-3">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                </div>
                
                <div class="alert alert-info mt-3">
                    <strong>Test Credentials:</strong><br>
                    Email: john@example.com<br>
                    Password: password123
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
