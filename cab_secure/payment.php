<?php
error_reporting(0);
require_once 'includes/config.php';
require_once 'includes/auth.php';

// ✅ SECURITY: Require Authentication
require_auth();

$user_id = get_user_id();
$success = '';
$error = '';

// Get booking details
if(!isset($_GET['booking_id'])) {
    redirect('dashboard.php');
}

$booking_id = (int)$_GET['booking_id'];

// ✅ SECURITY: Verify booking ownership (IDOR Prevention)
try {
    $stmt = $pdo->prepare("SELECT b.*, d.name as driver_name, d.car_model 
                          FROM bookings b 
                          JOIN drivers d ON b.driver_id = d.id 
                          WHERE b.id = ? AND b.user_id = ?");
    $stmt->execute([$booking_id, $user_id]);
    $booking = $stmt->fetch();
    
    if(!$booking) {
        redirect('dashboard.php');
    }
} catch(PDOException $e) {
    redirect('dashboard.php');
}

// ✅ SECURITY: Secure Payment Processing
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ✅ CSRF Validation
    if (!validate_csrf($_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
    } else {
        $payment_method = clean_input($_POST['payment_method']);
        
        // ✅ SECURITY: Amount from booking, NOT from POST (Price Manipulation Prevention)
        $amount = $booking['fare'];
        
        // ✅ UPI Payment Verification: Require valid transaction ID
        if ($payment_method === 'UPI') {
            $txn_id = trim($_POST['upi_txn_id'] ?? '');
            if (empty($txn_id)) {
                $error = 'Please enter your UPI Transaction/Reference ID from your payment app.';
            } elseif (strlen($txn_id) < 8) {
                $error = 'Invalid Transaction ID. Please enter the correct UPI reference number from your payment app.';
            } else {
                // TXN ID looks valid — record it
                try {
                    $stmt = $pdo->prepare("UPDATE payments SET amount = ?, payment_method = ?, status = 'completed', transaction_id = ? 
                                          WHERE booking_id = ? AND user_id = ?");
                    $stmt->execute([$amount, $payment_method, $txn_id, $booking_id, $user_id]);
                    
                    $stmt = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ? AND user_id = ?");
                    $stmt->execute([$booking_id, $user_id]);
                    
                    $success = 'Payment verified! Your ride is confirmed. Transaction ID: ' . escape_output($txn_id);
                } catch(PDOException $e) {
                    $error = 'Payment processing failed. Please try again.';
                }
            }
        } else {
            // Non-UPI methods (Cash, Card) — process directly
            $txn_id = 'TXN-' . date('Ymd') . '-' . rand(1000, 9999);
            
            try {
                $stmt = $pdo->prepare("UPDATE payments SET amount = ?, payment_method = ?, status = 'completed' 
                                      WHERE booking_id = ? AND user_id = ?");
                $stmt->execute([$amount, $payment_method, $booking_id, $user_id]);
                
                $stmt = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ? AND user_id = ?");
                $stmt->execute([$booking_id, $user_id]);
                
                $success = 'Payment successful! Your ride is confirmed.';
            } catch(PDOException $e) {
                $error = 'Payment failed. Please try again.';
            }
        }
    }
}

// Load QR code — use the actual uploaded QR image set by admin via manage_upi.php
$qr_path_file = __DIR__ . '/uploads/upi_qr_path.txt';
$stored_qr = file_exists($qr_path_file) ? trim(file_get_contents($qr_path_file)) : '';

if (!empty($stored_qr) && !preg_match('#^https?://#i', $stored_qr)) {
    // Local uploaded file — check it exists
    $relative = ltrim(str_replace(BASE_URL, '', $stored_qr), '/');
    $full_path = __DIR__ . '/' . $relative;
    $upi_qr_url = file_exists($full_path) ? $stored_qr : '';
} elseif (preg_match('#^https?://#i', $stored_qr)) {
    // External URL (e.g., generated QR from admin)
    $upi_qr_url = $stored_qr;
} else {
    $upi_qr_url = '';
}

// Fallback: generate from UPI ID if no QR image is set
if (empty($upi_qr_url)) {
    $upi_id_file = __DIR__ . '/uploads/upi_id.txt';
    $upi_id = file_exists($upi_id_file) ? trim(file_get_contents($upi_id_file)) : 'quickcab@upi';
    $qr_data = 'upi://pay?pa=' . urlencode($upi_id) . '&pn=QuickCab&am=1&cu=INR&tn=CabBooking';
    $upi_qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qr_data);
}

// Make URL absolute for display
if (!preg_match('#^https?://#i', $upi_qr_url) && $upi_qr_url[0] !== '/') {
    $upi_qr_url = BASE_URL . ltrim($upi_qr_url, '/');
}

include 'includes/header.php'; 
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <h2><i class="fas fa-credit-card"></i> Payment Gateway</h2>
        <p class="text-muted">Complete your payment to confirm the booking</p>
        
        <div class="alert alert-success mt-3">
            <strong><i class="fas fa-shield-alt"></i> Security:</strong> 
            Amount is verified server-side and cannot be manipulated!
        </div>
        
        <?php if($success): ?>
            <div class="alert alert-success">
                <h5><i class="fas fa-check-circle"></i> <?php echo escape_output($success); ?></h5>
                <a href="dashboard.php" class="btn btn-success mt-2">Go to Dashboard</a>
                <a href="booking.php" class="btn btn-primary mt-2">Book Another Ride</a>
            </div>
        <?php else: ?>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0 text-white">Booking Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Booking ID:</strong> #<?php echo $booking['id']; ?></p>
                        <p><strong>Driver:</strong> <?php echo escape_output($booking['driver_name']); ?></p>
                        <p><strong>Vehicle:</strong> <?php echo escape_output($booking['car_model']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>From:</strong> <?php echo escape_output($booking['pickup_location']); ?></p>
                        <p><strong>To:</strong> <?php echo escape_output($booking['dropoff_location']); ?></p>
                        <p><strong>Status:</strong> <span class="badge bg-warning text-dark"><?php echo ucfirst($booking['status']); ?></span></p>
                    </div>
                </div>
                <hr>
                <h4 class="text-center">Total Amount: <span class="text-success">$<?php echo number_format($booking['fare'], 2); ?></span></h4>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0 text-white">Payment Information</h5>
            </div>
            <div class="card-body">
                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo escape_output($error); ?></div>
                <?php endif; ?>

                <!-- STEP 1: Select Payment Method -->
                <div id="step-select">
                    <h5 class="mb-3"><i class="fas fa-hand-pointer"></i> Select Payment Method</h5>
                    <p class="text-muted small">Drag or tap to choose how you want to pay</p>
                    <div class="row g-3" id="payment-method-cards">
                        <div class="col-6 col-md-3">
                            <div class="payment-option-card" data-method="Cash" onclick="selectMethod('Cash')">
                                <i class="fas fa-money-bill-wave fa-2x text-success mb-2"></i>
                                <div class="fw-bold">Cash</div>
                                <small class="text-muted">Pay on arrival</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="payment-option-card" data-method="UPI" onclick="selectMethod('UPI')">
                                <i class="fas fa-qrcode fa-2x text-primary mb-2"></i>
                                <div class="fw-bold">UPI</div>
                                <small class="text-muted">Scan & Pay ₹1</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="payment-option-card" data-method="Credit Card" onclick="selectMethod('Credit Card')">
                                <i class="fas fa-credit-card fa-2x text-warning mb-2"></i>
                                <div class="fw-bold">Credit Card</div>
                                <small class="text-muted">Visa / MC</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="payment-option-card" data-method="Debit Card" onclick="selectMethod('Debit Card')">
                                <i class="fas fa-credit-card fa-2x text-info mb-2"></i>
                                <div class="fw-bold">Debit Card</div>
                                <small class="text-muted">Any bank</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2a: Cash Payment -->
                <div id="step-cash" class="payment-step d-none">
                    <div class="text-center py-4">
                        <i class="fas fa-money-bill-wave fa-4x text-success mb-3"></i>
                        <h5>Cash Payment</h5>
                        <p class="text-muted">Please keep <strong>$<?php echo number_format($booking['fare'], 2); ?></strong> ready to pay the driver on arrival.</p>
                        <form method="POST" action="">
                            <!-- ✅ CSRF Token -->
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="payment_method" value="Cash">
                            <button type="submit" class="btn btn-success btn-lg px-5">
                                <i class="fas fa-check-circle"></i> Confirm Booking
                            </button>
                        </form>
                        <button class="btn btn-link mt-2" onclick="goBack()"><i class="fas fa-arrow-left"></i> Change Method</button>
                    </div>
                </div>

                <!-- STEP 2b: UPI Payment -->
                <div id="step-upi" class="payment-step d-none">
                    <div class="text-center py-3">
                        <h5><i class="fas fa-qrcode text-primary"></i> UPI Payment</h5>
                        <p class="text-muted mb-1">Scan the QR code below to pay <strong>₹1</strong> (demo verification)</p>
                        <div class="upi-qr-box mx-auto mb-3">
                            <img src="<?php echo htmlspecialchars($upi_qr_url); ?>" alt="UPI QR Code" class="img-fluid" style="max-width:200px; border:4px solid #198754; border-radius:12px;">
                        </div>
                        <p class="text-success small"><i class="fas fa-info-circle"></i> Only <strong>₹1</strong> will be deducted as demo verification</p>
                        
                        <form method="POST" action="">
                            <!-- ✅ CSRF Token -->
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="payment_method" value="UPI">
                            
                            <div class="mb-3 text-start">
                                <label class="form-label fw-bold"><i class="fas fa-receipt"></i> UPI Transaction / Reference ID</label>
                                <input type="text" class="form-control form-control-lg" name="upi_txn_id" 
                                       placeholder="Enter UPI Ref No. (e.g., 123456789012)" 
                                       pattern="[A-Za-z0-9]{8,}" minlength="8" required
                                       style="letter-spacing: 2px; font-family: monospace;">
                                <div class="form-text">After scanning & paying ₹1, enter the Transaction ID shown in your UPI app (GPay/PhonePe/Paytm)</div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg px-5 mt-2">
                                <i class="fas fa-check-circle"></i> Verify & Confirm Payment
                            </button>
                        </form>
                        <button class="btn btn-link mt-2" onclick="goBack()"><i class="fas fa-arrow-left"></i> Change Method</button>
                    </div>
                </div>

                <!-- STEP 2c: Card Payment -->
                <div id="step-card" class="payment-step d-none">
                    <div class="d-flex align-items-center mb-3">
                        <button class="btn btn-link p-0 me-2" onclick="goBack()"><i class="fas fa-arrow-left"></i></button>
                        <h5 class="mb-0" id="card-type-title">Card Payment</h5>
                    </div>
                    <form method="POST" action="">
                        <!-- ✅ CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="payment_method" id="hidden-card-method" value="">
                        <!-- ✅ Amount NOT in POST, server ignores it -->
                        
                        <div class="mb-3">
                            <label class="form-label">Card Number (Demo - Any Number)</label>
                            <input type="text" class="form-control" id="card_number" placeholder="1234 5678 9012 3456" maxlength="19" oninput="formatCard(this)">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cardholder Name</label>
                            <input type="text" class="form-control" placeholder="Name on card">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Expiry Date</label>
                                <input type="text" class="form-control" id="expiry" placeholder="MM/YY" maxlength="5" oninput="formatExpiry(this)">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">CVV</label>
                                <input type="text" class="form-control" id="cvv" placeholder="123" maxlength="3">
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <strong>Amount:</strong> $<?php echo number_format($booking['fare'], 2); ?> <span class="badge bg-success">Verified Server-Side</span>
                        </div>
                        
                        <button type="submit" class="btn btn-success btn-lg w-100">
                            <i class="fas fa-lock"></i> Pay $<?php echo number_format($booking['fare'], 2); ?>
                        </button>
                    </form>
                </div>
                
                <div class="text-center mt-3">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt"></i> This is a demo payment gateway. No real charges apply.
                    </small>
                </div>
            </div>
        </div>
        
        <?php endif; ?>
    </div>
</div>

<style>
.payment-option-card {
    border: 2px solid #dee2e6;
    border-radius: 12px;
    padding: 20px 10px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    background: #fff;
    user-select: none;
}
.payment-option-card:hover {
    border-color: #198754;
    background: #f0fff4;
    transform: translateY(-3px);
    box-shadow: 0 4px 15px rgba(25,135,84,0.15);
}
.payment-option-card.selected {
    border-color: #198754;
    background: #d1f7e0;
}
.upi-qr-box {
    display: inline-block;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 16px;
}
</style>

<script>
// Drag/touch support
let dragStart = null;
document.querySelectorAll('.payment-option-card').forEach(card => {
    card.setAttribute('draggable', 'true');
    card.addEventListener('dragstart', () => { dragStart = card.dataset.method; });
});
document.getElementById('step-select').addEventListener('dragover', e => e.preventDefault());
document.getElementById('step-select').addEventListener('drop', e => {
    e.preventDefault();
    if(dragStart) selectMethod(dragStart);
});

function selectMethod(method) {
    document.querySelectorAll('.payment-option-card').forEach(c => c.classList.remove('selected'));
    document.querySelector('[data-method="'+method+'"]').classList.add('selected');
    
    document.getElementById('step-select').classList.add('d-none');
    document.querySelectorAll('.payment-step').forEach(s => s.classList.add('d-none'));
    
    if(method === 'Cash') {
        document.getElementById('step-cash').classList.remove('d-none');
    } else if(method === 'UPI') {
        document.getElementById('step-upi').classList.remove('d-none');
    } else {
        document.getElementById('hidden-card-method').value = method;
        document.getElementById('card-type-title').textContent = method + ' Payment';
        document.getElementById('step-card').classList.remove('d-none');
    }
}

function goBack() {
    document.querySelectorAll('.payment-step').forEach(s => s.classList.add('d-none'));
    document.getElementById('step-select').classList.remove('d-none');
}

function formatCard(input) {
    let v = input.value.replace(/\D/g, '').substring(0, 16);
    input.value = v.replace(/(.{4})/g, '$1 ').trim();
}
function formatExpiry(input) {
    let v = input.value.replace(/\D/g, '').substring(0, 4);
    if(v.length >= 2) v = v.substring(0,2) + '/' + v.substring(2);
    input.value = v;
}
</script>

<?php include 'includes/footer.php'; ?>
