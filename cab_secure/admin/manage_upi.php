<?php
session_name('SECURE_ADMIN_SESSION');
require_once '../includes/config.php';
require_once '../includes/auth.php';

$success = '';
$error = '';

$qr_file = __DIR__ . '/../uploads/upi_qr_path.txt';
$upi_id_file = __DIR__ . '/../uploads/upi_id.txt';

/**
 * Verifies an image file is actually a QR code by decoding it via API.
 * Returns decoded QR text on success, false on failure.
 */
function verify_qr_code_image(string $file_path): string|false {
    $url = 'https://api.qrserver.com/v1/read-qr-code/';
    $cfile = new CURLFile($file_path, mime_content_type($file_path), basename($file_path));
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => ['file' => $cfile],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err || $response === false) {
        return false;
    }
    $data = json_decode($response, true);
    // API returns: [{"type":"qrcode","symbol":[{"seq":0,"data":"...","error":null}]}]
    if (!is_array($data) || empty($data[0]['symbol'][0]['data']) || $data[0]['symbol'][0]['error'] !== null) {
        return false;
    }
    return $data[0]['symbol'][0]['data'];
}

/**
 * Verifies a QR code URL by downloading & decoding it.
 */
function verify_qr_code_url(string $image_url): string|false {
    // Download to temp file
    $tmp = tempnam(sys_get_temp_dir(), 'qr_verify_');
    $ch = curl_init($image_url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_MAXREDIRS      => 3,
    ]);
    $content = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (!$content || $http_code !== 200) {
        @unlink($tmp);
        return false;
    }
    file_put_contents($tmp, $content);

    // Verify it's an image
    $mime = mime_content_type($tmp);
    if (!in_array($mime, ['image/png','image/jpeg','image/gif','image/webp'])) {
        @unlink($tmp);
        return false;
    }

    $result = verify_qr_code_image($tmp);
    @unlink($tmp);
    return $result;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ✅ CSRF Validation
    if (!validate_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } elseif (isset($_POST['save_upi_id']) && isset($_POST['upi_id']) && !empty(trim($_POST['upi_id']))) {
        // Save UPI ID and generate QR code with fixed ₹1 amount
        $upi_id = trim($_POST['upi_id']);
        file_put_contents($upi_id_file, $upi_id);
        $current_upi_id = $upi_id;
        // Generate QR code URL with fixed ₹1
        $qr_data = 'upi://pay?pa=' . urlencode($upi_id) . '&pn=QuickCab&am=1&cu=INR&tn=CabBooking';
        $current_qr = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qr_data);
        file_put_contents($qr_file, $current_qr);
        $success = 'UPI ID saved! QR code generated with fixed ₹1 amount.';
    } elseif (isset($_POST['qr_url']) && !empty(trim($_POST['qr_url']))) {
        $url = filter_var(trim($_POST['qr_url']), FILTER_VALIDATE_URL);
        if (!$url) {
            $error = 'Invalid URL provided.';
        } else {
            // ✅ Verify the URL actually leads to a valid QR code image
            $qr_data = verify_qr_code_url($url);
            if ($qr_data === false) {
                $error = 'QR code verification failed. The image at the provided URL does not appear to contain a valid QR code. Please upload a real UPI QR code image.';
            } else {
                file_put_contents($qr_file, $url);
                $success = 'UPI QR Code URL updated successfully! (QR content verified: ' . htmlspecialchars(substr($qr_data, 0, 60)) . (strlen($qr_data) > 60 ? '…' : '') . ')';
            }
        }
    } elseif (isset($_FILES['qr_image']) && $_FILES['qr_image']['error'] == 0) {
        // ✅ Step 1: Verify real MIME type via finfo
        $allowed_types = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $_FILES['qr_image']['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed_types)) {
            $error = 'Invalid file type. Only PNG/JPG/GIF/WEBP images are accepted.';
        } else {
            // ✅ Step 2: Verify the image actually contains a QR code
            $qr_data = verify_qr_code_image($_FILES['qr_image']['tmp_name']);
            if ($qr_data === false) {
                $error = 'QR code verification failed. The uploaded image does not contain a valid QR code. Please upload a real UPI QR code image.';
            } else {
                // Step 3: Save it
                $ext  = ($mime === 'image/png') ? 'png' : (($mime === 'image/gif') ? 'gif' : (($mime === 'image/webp') ? 'webp' : 'jpg'));
                $dest = __DIR__ . '/../uploads/upi_qr.' . $ext;
                if (move_uploaded_file($_FILES['qr_image']['tmp_name'], $dest)) {
                    $rel_url = BASE_URL . 'uploads/upi_qr.' . $ext;
                    file_put_contents($qr_file, $rel_url);
                    $success = 'UPI QR Code image uploaded and verified successfully! (QR content: ' . htmlspecialchars(substr($qr_data, 0, 60)) . (strlen($qr_data) > 60 ? '…' : '') . ')';
                } else {
                    $error = 'Failed to save the uploaded image.';
                }
            }
        }
    }
}

$current_qr = file_exists($qr_file) ? trim(file_get_contents($qr_file)) : '';

// Load current UPI ID
$current_upi_id = file_exists($upi_id_file) ? trim(file_get_contents($upi_id_file)) : 'quickcab@upi';

// Check if stored QR is a local file or external URL
$needs_generated_qr = empty($current_qr);
if (!$needs_generated_qr && !preg_match('#^https?://#i', $current_qr)) {
    // Local path — strip BASE_URL to get relative path, then check filesystem
    $relative = ltrim(str_replace(BASE_URL, '', $current_qr), '/');
    $qr_file_path = __DIR__ . '/../' . $relative;
    if (!file_exists($qr_file_path)) {
        $needs_generated_qr = true;
    }
}
if ($needs_generated_qr) {
    $qr_data = 'upi://pay?pa=' . urlencode($current_upi_id) . '&pn=QuickCab&am=1&cu=INR&tn=CabBooking';
    $current_qr = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qr_data);
} elseif (!preg_match('#^https?://#i', $current_qr) && $current_qr[0] !== '/') {
    // Legacy relative path — make it absolute so it works from any subdirectory
    $current_qr = BASE_URL . ltrim($current_qr, '/');
}

include '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-qrcode"></i> Manage UPI QR Code</h5>
            </div>
            <div class="card-body">
                <a href="dashboard.php" class="btn btn-sm btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>

                <?php if ($success): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo escape_output($success); ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="fas fa-times-circle"></i> <?php echo escape_output($error); ?></div>
                <?php endif; ?>

                <h6 class="text-muted mb-3">Current QR Code:</h6>
                <div class="text-center mb-4">
                    <img src="<?php echo escape_output($current_qr); ?>" alt="Current UPI QR" style="max-width:180px; border:3px solid #198754; border-radius:10px; padding:5px;">
                    <p class="small text-muted mt-2">This QR code is shown to customers during UPI payment (₹1 demo charge)</p>
                </div>

                <hr>
                <h6>Set UPI ID (For ₹1 Payment)</h6>
                <form method="POST" class="mb-4">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="mb-2">
                        <label class="form-label">UPI ID (e.g., quickcab@upi)</label>
                        <input type="text" class="form-control" name="upi_id" placeholder="Enter UPI ID" value="<?php echo escape_output($current_upi_id); ?>">
                        <div class="form-text text-muted">This UPI ID will be used to generate a QR code that always shows exactly ₹1</div>
                    </div>
                    <button type="submit" name="save_upi_id" class="btn btn-warning w-100"><i class="fas fa-save"></i> Save UPI ID & Generate QR</button>
                </form>

                <hr>
                <h6>Or Use Custom QR Code URL</h6>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="mb-3">
                        <label class="form-label">QR Code Image URL</label>
                        <input type="url" class="form-control" name="qr_url" placeholder="https://..." value="">
                        <div class="form-text text-muted"><i class="fas fa-info-circle"></i> The image at this URL will be downloaded and decoded to verify it is a real QR code. Note: Amount may vary.</div>
                    </div>
                    <button type="submit" class="btn btn-success w-100"><i class="fas fa-save"></i> Verify & Update QR URL</button>
                </form>

                <hr>
                <h6>Or Upload QR Image</h6>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="mb-3">
                        <label class="form-label">Upload QR Code Image (PNG/JPG only)</label>
                        <input type="file" class="form-control" name="qr_image" accept="image/png,image/jpeg,image/gif,image/webp">
                        <div class="form-text text-muted"><i class="fas fa-info-circle"></i> The image will be decoded server-side to confirm it contains a valid QR code.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-upload"></i> Verify & Upload QR Image</button>
                </form>

                <div class="alert alert-info mt-3">
                    <i class="fas fa-shield-alt"></i> <strong>Secure:</strong> Every image is decoded via QR scanner API to confirm it is a genuine QR code before being saved. Random or non-QR images are rejected.
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
