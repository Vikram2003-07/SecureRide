<?php
error_reporting(0);
require_once 'includes/config.php';
require_once 'includes/auth.php';

// ✅ SECURITY: Require Authentication
require_auth();

$user_id = get_user_id();
$success = '';
$error   = '';

// Get user info
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
} catch(PDOException $e) {
    $error = "Error loading profile.";
}

// =============================================
// ✅ HANDLE PROFILE IMAGE UPLOAD (separate POST action)
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_image') {

    // ✅ CSRF Validation
    if (!validate_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } elseif (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] === UPLOAD_ERR_NO_FILE) {
        $error = 'Please select an image file to upload.';
    } else {
        $file = $_FILES['profile_image'];

        // ✅ SECURITY: Use the hardened validate_file_upload() from config.php
        // Checks: upload error, size, extension whitelist (jpg/jpeg only),
        //         server-side finfo MIME detection, and getimagesize() genuine image check.
        $validation = validate_file_upload($file);

        if ($validation !== true) {
            $error = $validation;
        } else {
            // ✅ SECURITY: Generate a random filename — never use the user-supplied name
            // This prevents path traversal (../../../evil.php) and overwrite attacks.
            $new_filename = 'avatar_' . $user_id . '_' . bin2hex(random_bytes(8)) . '.jpg';
            $upload_dir   = __DIR__ . '/uploads/';
            $upload_path  = $upload_dir . $new_filename;

            // Create uploads directory if missing
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Delete old avatar to avoid orphaned files
                if (!empty($user['profile_image'])) {
                    $old_file = $upload_dir . basename($user['profile_image']);
                    if (file_exists($old_file) && is_file($old_file)) {
                        unlink($old_file);
                    }
                }

                try {
                    $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                    $stmt->execute([$new_filename, $user_id]);
                    $success = 'Profile image updated successfully!';

                    // Refresh user data
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user = $stmt->fetch();
                } catch(PDOException $e) {
                    // Remove the uploaded file if DB update fails
                    @unlink($upload_path);
                    $error = 'Failed to save image reference. Please try again.';
                }
            } else {
                $error = 'Failed to move uploaded file. Check server permissions.';
            }
        }
    }
}

// =============================================
// ✅ HANDLE REMOVE PROFILE IMAGE
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove_image') {
    if (!validate_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } elseif (!empty($user['profile_image'])) {
        $old_file = __DIR__ . '/uploads/' . basename($user['profile_image']);
        if (file_exists($old_file) && is_file($old_file)) {
            unlink($old_file);
        }
        try {
            $stmt = $pdo->prepare("UPDATE users SET profile_image = NULL WHERE id = ?");
            $stmt->execute([$user_id]);
            $success = 'Profile photo removed.';
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        } catch(PDOException $e) {
            $error = 'Could not remove photo. Please try again.';
        }
    }
}

// =============================================
// ✅ HANDLE PROFILE INFO UPDATE
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {

    // ✅ CSRF Validation
    if (!validate_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $username     = clean_input($_POST['username']);
        $phone        = clean_input($_POST['phone']);
        $new_password = $_POST['new_password'];

        // ✅ SECURITY: Input Validation
        if (!validate_phone($phone)) {
            $error = 'Invalid phone number format.';
        } else {
            try {
                if (!empty($new_password)) {
                    // ✅ Password Validation
                    if (!validate_password($new_password)) {
                        $error = 'Password must be at least 8 characters.';
                    } else {
                        // ✅ SECURITY: Password Hashing
                        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt   = $pdo->prepare("UPDATE users SET username = ?, phone = ?, password = ? WHERE id = ?");
                        $stmt->execute([$username, $phone, $hashed, $user_id]);
                        $success = 'Profile updated successfully!';
                    }
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, phone = ? WHERE id = ?");
                    $stmt->execute([$username, $phone, $user_id]);
                    $success = 'Profile updated successfully!';
                }

                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user                = $stmt->fetch();
                $_SESSION['username'] = $user['username'];
            } catch(PDOException $e) {
                $error = 'Update failed. Please try again.';
            }
        }
    }
}

include 'includes/header.php';

// Build avatar URL (use uploaded image or a default avatar placeholder)
$avatar_url = !empty($user['profile_image'])
    ? BASE_URL . 'uploads/' . escape_output($user['profile_image'])
    : 'https://ui-avatars.com/api/?name=' . urlencode($user['username']) . '&background=f59e0b&color=fff&size=120';
?>

<div class="row">
    <div class="col-md-12">
        <h2><i class="fas fa-user"></i> My Profile</h2>
        <p class="text-muted">Manage your account details, photo, and settings</p>
    </div>
</div>

<?php if($error): ?>
    <div class="alert alert-danger mt-3">
        <i class="fas fa-exclamation-circle"></i> <?php echo escape_output($error); ?>
    </div>
<?php endif; ?>

<?php if($success): ?>
    <div class="alert alert-success mt-3">
        <i class="fas fa-check-circle"></i> <?php echo escape_output($success); ?>
    </div>
<?php endif; ?>

<div class="row mt-4">

    <!-- =============================================
         PROFILE IMAGE UPLOAD CARD
         ============================================= -->
    <div class="col-md-4 mb-4">
        <div class="card text-center">
            <div class="card-header">
                <h5 class="mb-0 text-white"><i class="fas fa-camera"></i> Profile Photo</h5>
            </div>
            <div class="card-body">

                <!-- Current Avatar -->
                <img id="avatar-preview"
                     src="<?php echo $avatar_url; ?>"
                     alt="Profile Photo"
                     class="rounded-circle mb-3 border border-3 border-warning"
                     style="width:120px;height:120px;object-fit:cover;">

                <!-- ✅ SECURE UPLOAD FORM
                     - enctype="multipart/form-data" required for file uploads
                     - accept attribute provides client-side UX hint (NOT a security control)
                     - All real security is enforced SERVER-SIDE in PHP above
                -->
                <form method="POST" action="" enctype="multipart/form-data">
                    <!-- ✅ CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo escape_output($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="action"     value="upload_image">

                    <div class="mb-3">
                        <label for="profile_image" class="form-label fw-semibold">
                            Upload New Photo
                        </label>
                        <!-- accept is a UX hint only; server-side validation is the real guard -->
                        <input type="file"
                               class="form-control"
                               id="profile_image"
                               name="profile_image"
                               accept=".jpg,.jpeg"
                               onchange="previewAvatar(this)">
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-shield-alt text-success"></i>
                            Allowed: <strong>.jpg, .jpeg</strong> only &bull; Max: <strong>2 MB</strong><br>
                            PDFs, scripts, and all other file types are <strong>blocked</strong>.
                        </small>
                    </div>

                    <button type="submit" class="btn btn-warning w-100">
                        <i class="fas fa-upload"></i> Upload Photo
                    </button>
                </form>

                <?php if (!empty($user['profile_image'])): ?>
                <form method="POST" action="" class="mt-2">
                    <input type="hidden" name="csrf_token" value="<?php echo escape_output($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="action"     value="remove_image">
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100"
                            onclick="return confirm('Remove your profile photo?');">
                        <i class="fas fa-trash"></i> Remove Photo
                    </button>
                </form>
                <?php endif; ?>

            </div>
        </div>

        <!-- Account Info -->
        <div class="card bg-light mt-3">
            <div class="card-body">
                <h5><i class="fas fa-info-circle"></i> Account Info</h5>
                <hr>
                <p><strong>User ID:</strong> #<?php echo (int)$user['id']; ?></p>
                <p><strong>Member Since:</strong> <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
                <p><strong>Status:</strong> <span class="badge bg-success">Active</span></p>
            </div>
        </div>
    </div>

    <!-- =============================================
         PROFILE INFO FORM
         ============================================= -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 text-white">Profile Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <!-- ✅ CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo escape_output($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="action"     value="update_profile">

                    <div class="mb-3">
                        <label for="username" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="username" name="username"
                               value="<?php echo escape_output($user['username']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email"
                               value="<?php echo escape_output($user['email']); ?>" disabled>
                        <small class="text-muted">Email cannot be changed</small>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone"
                               value="<?php echo escape_output($user['phone']); ?>"
                               pattern="[0-9]{10,15}" required>
                    </div>

                    <hr>
                    <h5>Change Password</h5>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password"
                               minlength="8" placeholder="Leave blank to keep current password">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

<!-- ✅ CLIENT-SIDE PREVIEW (UX only – no security trust placed here) -->
<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        var file = input.files[0];

        // Client-side format hint (not a security gate)
        var allowed = ['image/jpeg'];
        if (!allowed.includes(file.type)) {
            document.getElementById('avatar-preview').src =
                'https://ui-avatars.com/api/?name=Error&background=dc3545&color=fff&size=120';
            return;
        }

        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatar-preview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}
</script>

<?php include 'includes/footer.php'; ?>
