<?php 
require_once 'includes/config.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Get booking details
if(!isset($_GET['booking_id'])) {
    header('Location: dashboard.php');
    exit();
}

$booking_id = $_GET['booking_id'];

// ⚠️ VULNERABILITY: No authorization check - any user can pay for any booking
$query = "SELECT b.*, d.name as driver_name, d.car_model 
          FROM bookings b 
          JOIN drivers d ON b.driver_id = d.id 
          WHERE b.id=$booking_id";
$result = mysqli_query($conn, $query);
$booking = mysqli_fetch_assoc($result);

if(!$booking) {
    header('Location: dashboard.php');
    exit();
}

// ⚠️ VULNERABILITY: Amount manipulation via POST
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_method = $_POST['payment_method'];
    
    // ⚠️ VULNERABILITY: Amount comes from form, can be manipulated!
    $amount = $_POST['amount']; // Should come from booking fare!
    
    $txn_id = 'TXN-' . date('Ymd') . '-' . rand(1000, 9999);
    
    // Update payment
    $update_query = "UPDATE payments SET amount='$amount', payment_method='$payment_method', status='completed' 
                    WHERE booking_id=$booking_id";
    
    if(mysqli_query($conn, $update_query)) {
        // Update booking status
        $booking_update = "UPDATE bookings SET status='confirmed' WHERE id=$booking_id";
        mysqli_query($conn, $booking_update);
        
        $success = 'Payment successful! Your ride is confirmed.';
    } else {
        $error = 'Payment failed. Please try again.';
    }
}

// Load UPI QR code path - use uploaded QR if available
$upi_qr_file = __DIR__ . '/uploads/upi_qr_path.txt';
$stored_qr_path = file_exists($upi_qr_file) ? trim(file_get_contents($upi_qr_file)) : '';

// Check if stored QR exists and is a valid file
if (!empty($stored_qr_path) && file_exists(__DIR__ . '/' . $stored_qr_path)) {
    $upi_qr_url = $stored_qr_path;
} else {
    // Fallback to generated QR if no uploaded QR
    $upi_qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=upi://pay?pa=quickcab@upi%26pn=QuickCab%26am=1%26cu=INR%26tn=CabBooking';
}

include 'includes/header.php'; 
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <h2><i class="fas fa-credit-card"></i> Payment Gateway</h2>
        <p class="text-muted">Complete your payment to confirm the booking</p>
        
        <div class="alert alert-warning mt-3">
            <strong><i class="fas fa-bug"></i> Vulnerability:</strong> 
            Try changing the amount value using browser developer tools before submitting!
        </div>
        
        <?php if($success): ?>
            <div class="alert alert-success">
                <h5><i class="fas fa-check-circle"></i> <?php echo $success; ?></h5>
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
                        <p><strong>Driver:</strong> <?php echo $booking['driver_name']; ?></p>
                        <p><strong>Vehicle:</strong> <?php echo $booking['car_model']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>From:</strong> <?php echo $booking['pickup_location']; ?></p>
                        <p><strong>To:</strong> <?php echo $booking['dropoff_location']; ?></p>
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
                    <div class="alert alert-danger"><?php echo $error; ?></div>
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
                            <input type="hidden" name="payment_method" value="Cash">
                            <input type="hidden" name="amount" value="<?php echo $booking['fare']; ?>">
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
                        <p class="text-muted mb-1">Scan the QR code below to pay <strong>₹1</strong> (demo charge)</p>
                        <div class="upi-qr-box mx-auto mb-3">
                            <img src="<?php echo htmlspecialchars($upi_qr_url); ?>" alt="UPI QR Code" class="img-fluid" style="max-width:200px; border:4px solid #f0ad00; border-radius:12px;">
                        </div>
                        <p class="text-success small"><i class="fas fa-info-circle"></i> Only <strong>₹1</strong> will be deducted as demo verification</p>
                        <form method="POST" action="">
                            <input type="hidden" name="payment_method" value="UPI">
                            <input type="hidden" name="amount" value="<?php echo $booking['fare']; ?>">
                            <button type="submit" class="btn btn-primary btn-lg px-5 mt-2">
                                <i class="fas fa-check-circle"></i> Payment Done
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
                        <input type="hidden" name="payment_method" id="hidden-card-method" value="">
                        <!-- ⚠️ VULNERABILITY: Amount is editable! -->
                        <input type="hidden" name="amount" id="hidden-card-amount" value="<?php echo $booking['fare']; ?>">
                        
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
                        
                        <div class="mb-3">
                            <label class="form-label">Amount to Pay</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <!-- ⚠️ VULNERABILITY: Amount is editable! -->
                                <input type="number" class="form-control" id="amount_display" 
                                       step="0.01" value="<?php echo $booking['fare']; ?>" 
                                       oninput="document.getElementById('hidden-card-amount').value=this.value; document.getElementById('displayAmount').textContent=parseFloat(this.value).toFixed(2)">
                            </div>
                            <small class="text-danger">⚠️ This can be manipulated to pay less!</small>
                        </div>
                        
                        <button type="submit" class="btn btn-success btn-lg w-100">
                            <i class="fas fa-lock"></i> Pay $<span id="displayAmount"><?php echo number_format($booking['fare'], 2); ?></span>
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
    border-color: #f0ad00;
    background: #fffbf0;
    transform: translateY(-3px);
    box-shadow: 0 4px 15px rgba(240,173,0,0.2);
}
.payment-option-card.selected {
    border-color: #f0ad00;
    background: #fff8e1;
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
    card.addEventListener('dragstart', () => { dragStart = card.dataset.method; });
    card.setAttribute('draggable', 'true');
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
