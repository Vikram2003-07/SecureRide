<?php
error_reporting(0);
require_once 'includes/config.php';
require_once 'includes/auth.php';

// ✅ SECURITY: Redirect if already logged in
if(is_logged_in()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';

// ✅ SECURITY: Secure Registration
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ✅ CSRF Validation
    if (!validate_csrf($_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
    } else {
        $username = clean_input($_POST['username']);
        $email = clean_input($_POST['email']);
        $password = $_POST['password'];
        $phone = clean_input($_POST['phone']);
        
        // ✅ SECURITY: Input Validation
        if (!validate_email($email)) {
            $error = 'Invalid email format.';
        } elseif (!validate_password($password)) {
            $error = 'Password must be at least 8 characters long.';
        } elseif (!validate_phone($phone)) {
            $error = 'Invalid phone number format.';
        } else {
            try {
                // Check if email already exists
                $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $check->execute([$email]);
                
                if ($check->fetch()) {
                    $error = 'Email already registered.';
                } else {
                    // ✅ SECURITY: Password Hashing
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // ✅ SECURITY: Prepared Statement
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, phone) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$username, $email, $hashed_password, $phone]);
                    
                    $success = 'Registration successful! You can now login.';
                }
            } catch(PDOException $e) {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

include 'includes/header.php'; 
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0 text-white">Create New Account</h4>
            </div>
            <div class="card-body">
                <?php if($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo escape_output($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo escape_output($success); ?>
                        <br><a href="login.php" class="btn btn-success btn-sm mt-2">Go to Login</a>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <!-- ✅ CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" 
                               minlength="8" required>
                        <small class="text-muted">Minimum 8 characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               pattern="[0-9]{10,15}" required>
                        <small class="text-muted">10-15 digits</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-user-plus"></i> Register
                    </button>
                </form>
                
                <div class="text-center mt-3">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
