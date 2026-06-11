<?php 
require_once 'includes/config.php';

// If already logged in, redirect to dashboard
if(isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

// ⚠️ VULNERABILITY: No input validation
// ⚠️ VULNERABILITY: Plain text password storage
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password']; // ⚠️ Plain text, not hashed!
    $phone = $_POST['phone'];
    
    // ⚠️ VULNERABILITY: SQL Injection via INSERT
    // Direct string concatenation without prepared statements
    $query = "INSERT INTO users (username, email, password, phone) VALUES ('$username', '$email', '$password', '$phone')";
    
    if(mysqli_query($conn, $query)) {
        $success = 'Registration successful! You can now login.';
    } else {
        $error = 'Registration failed: ' . mysqli_error($conn);
    }
}

include 'includes/header.php'; 
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0 text-white"><i class="fas fa-user-plus"></i> Create Account</h4>
            </div>
            <div class="card-body">
                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if($success): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                        <br><a href="login.php" class="btn btn-sm btn-success mt-2">Go to Login</a>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small class="text-danger">⚠️ Password stored in plain text!</small>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-user-plus"></i> Register
                    </button>
                </form>
                
                <div class="mt-3 text-center">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
