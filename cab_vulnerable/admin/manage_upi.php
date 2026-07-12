<?php
require_once '../includes/config.php';

// Simple admin check (vulnerable - no proper session check)
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$success = '';
$error   = '';

$qr_file = __DIR__ . '/../uploads/upi_qr_path.txt';

/**
 * ⚠️ VULNERABLE QR "Verification":
 * Only checks if the original filename contains the word "qr" (case-insensitive).
 * This is trivially bypassable — an attacker can rename any file to "qr_shell.php.jpg"
 * and it will pass this check. No actual QR decoding is performed.
 */
function vulnerable_verify_qr(string $original_filename): bool {
    return stripos($original_filename, 'qr') !== false;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['qr_url']) && !empty(trim($_POST['qr_url']))) {
        $url = trim($_POST['qr_url']);
        // ⚠️ Vulnerable: no URL validation, no QR decode, no CSRF check
        // Just checks if the URL contains "qr" in the filename portion
        $url_filename = basename(parse_url($url, PHP_URL_PATH) ?? '');
        if (!vulnerable_verify_qr($url_filename)) {
            $error = 'QR verification failed: filename must contain "qr". (Hint: rename your file.)';
        } else {
            file_put_contents($qr_file, $url);
            $success = 'UPI QR Code URL updated successfully! (QR verified by filename check)';
        }
    } elseif (isset($_FILES['qr_image']) && $_FILES['qr_image']['error'] == 0) {
        // ⚠️ VULNERABLE: No MIME type check, no extension restriction
        // Accepts ANY file — PHP shells, scripts, executables, anything
        if (!vulnerable_verify_qr($_FILES['qr_image']['name'])) {
            $error = 'QR verification failed: the filename must contain "qr". (e.g. rename to "my_qr.php")';
        } else {
            $ext  = pathinfo($_FILES['qr_image']['name'], PATHINFO_EXTENSION);
            $dest = __DIR__ . '/../uploads/upi_qr.' . $ext;
            if (move_uploaded_file($_FILES['qr_image']['tmp_name'], $dest)) {
                $url = BASE_URL . 'uploads/upi_qr.' . $ext;
                file_put_contents($qr_file, $url);
                $success = 'File uploaded successfully! (QR verified by filename check)';
            } else {
                $error = 'Failed to upload file.';
            }
        }
    }
}

$current_qr = file_exists($qr_file) ? trim(file_get_contents($qr_file)) : '';

// Show uploaded QR if exists, otherwise show generated QR
$needs_generated_qr = empty($current_qr);
if (!$needs_generated_qr && !preg_match('#^https?://#i', $current_qr)) {
    $relative = ltrim(str_replace(BASE_URL, '', $current_qr), '/');
    $qr_file_path = __DIR__ . '/../' . $relative;
    if (!file_exists($qr_file_path)) {
        $needs_generated_qr = true;
    }
}
if ($needs_generated_qr) {
    $qr_data = 'upi://pay?pa=quickcab@upi&pn=QuickCab&am=1&cu=INR&tn=CabBooking';
    $current_qr = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qr_data);
} elseif (!preg_match('#^https?://#i', $current_qr) && $current_qr[0] !== '/') {
    $current_qr = BASE_URL . ltrim($current_qr, '/');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage UPI QR Code - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-qrcode"></i> Manage UPI QR Code</h5>
                </div>
                <div class="card-body">
                    <a href="dashboard.php" class="btn btn-sm btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><i class="fas fa-times-circle"></i> <?php echo $error; ?></div>
                    <?php endif; ?>

                    <h6 class="text-muted mb-3">Current QR Code:</h6>
                    <div class="text-center mb-4">
                        <img src="<?php echo htmlspecialchars($current_qr); ?>" alt="Current UPI QR" style="max-width:180px; border:3px solid #f0ad00; border-radius:10px; padding:5px;">
                        <p class="small text-muted mt-2">This QR code is shown to customers during UPI payment</p>
                    </div>

                    <hr>
                    <h6>Update via URL</h6>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">QR Code Image URL</label>
                            <input type="url" class="form-control" name="qr_url" placeholder="https://..." value="<?php echo htmlspecialchars($current_qr); ?>">
                            <div class="form-text text-muted"><i class="fas fa-info-circle"></i> URL filename must contain "qr" to pass verification.</div>
                        </div>
                        <button type="submit" class="btn btn-warning w-100"><i class="fas fa-save"></i> Verify & Update QR URL</button>
                    </form>

                    <hr>
                    <h6>Or Upload QR Image</h6>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Upload Any File</label>
                            <input type="file" class="form-control" name="qr_image">
                            <div class="form-text text-muted"><i class="fas fa-info-circle"></i> Filename must contain "qr". Any file type accepted.</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-upload"></i> Verify & Upload QR Image</button>
                    </form>

                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-bug"></i> <strong>Vulnerable:</strong> No file type or MIME validation. Accepts ANY file (PHP, shell scripts, etc.) — only checks if filename contains "qr". Trivially upload a shell as "qr_shell.php".
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
