<?php 
require_once 'includes/config.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Get user info
$query = "SELECT * FROM users WHERE id=$user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// ⚠️ VULNERABILITY: File upload without validation
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_pic'])) {
    $target_dir = "uploads/";
    
    // ⚠️ VULNERABILITY: No file type validation
    // Any file type can be uploaded, including PHP files
    $filename = basename($_FILES["profile_pic"]["name"]);
    $target_file = $target_dir . $filename;
    
    // ⚠️ VULNERABILITY: No file size check
    // ⚠️ VULNERABILITY: Original filename kept (no randomization)
    if(move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
        // Update database
        $update_query = "UPDATE users SET profile_pic='$filename' WHERE id=$user_id";
        mysqli_query($conn, $update_query);
        
        $success = 'Profile picture updated successfully!';
        $user['profile_pic'] = $filename;
    } else {
        $error = 'Upload failed. Please try again.';
    }
}

// Update profile info
if($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_FILES['profile_pic'])) {
    $username = $_POST['username'];
    $phone = $_POST['phone'];
    
    // ⚠️ VULNERABILITY: SQL Injection
    $update_query = "UPDATE users SET username='$username', phone='$phone' WHERE id=$user_id";
    
    if(mysqli_query($conn, $update_query)) {
        $success = 'Profile updated successfully!';
        $user['username'] = $username;
        $user['phone'] = $phone;
    } else {
        $error = 'Update failed. Please try again.';
    }
}

include 'includes/header.php'; 
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <h2><i class="fas fa-user-circle"></i> My Profile</h2>
        <p class="text-muted">Manage your account information</p>
        
        <div class="alert alert-danger mt-3">
            <strong><i class="fas fa-bug"></i> File Upload Vulnerability:</strong> 
            Try uploading a PHP file! No file type validation exists.
        </div>
        
        <?php if($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0 text-white">Profile Picture</h5>
            </div>
            <div class="card-body text-center">
                <?php if($user['profile_pic'] && file_exists('uploads/' . $user['profile_pic'])): ?>
                    <img src="uploads/<?php echo $user['profile_pic']; ?>" alt="Profile" 
                         class="profile-pic-preview" id="profilePreview">
                <?php else: ?>
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['username']); ?>&size=150&background=ffc107&color=212529" 
                         alt="Profile" class="profile-pic-preview" id="profilePreview">
                <?php endif; ?>
                
                <form method="POST" action="" enctype="multipart/form-data" class="mt-3">
                    <div class="mb-3">
                        <input type="file" class="form-control" name="profile_pic" 
                               accept="*" onchange="previewImage(this)" required>
                        <small class="text-danger">⚠️ Any file type accepted - Try uploading PHP!</small>
                    </div>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-upload"></i> Upload Picture
                    </button>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0 text-white">Personal Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo $user['username']; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo $user['email']; ?>" disabled>
                        <small class="text-muted">Email cannot be changed</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?php echo $user['phone']; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Member Since</label>
                        <input type="text" class="form-control" 
                               value="<?php echo date('F d, Y', strtotime($user['created_at'])); ?>" disabled>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </form>
            </div>
        </div>
        
        <div class="card mt-4 bg-light">
            <div class="card-body">
                <h6><i class="fas fa-info-circle"></i> Account Information</h6>
                <ul class="list-unstyled mb-0">
                    <li><strong>User ID:</strong> <?php echo $user['id']; ?></li>
                    <li><strong>Account Status:</strong> <span class="badge bg-success">Active</span></li>
                    <li><strong>Password:</strong> ••••••••• <small class="text-muted">(stored in plain text!)</small></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
