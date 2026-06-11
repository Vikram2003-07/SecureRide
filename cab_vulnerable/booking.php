<?php 
require_once 'includes/config.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Get available drivers
$drivers_query = "SELECT * FROM drivers WHERE status='available'";
$drivers_result = mysqli_query($conn, $drivers_query);

// ⚠️ VULNERABILITY: Price manipulation via POST parameters
// ⚠️ VULNERABILITY: No CSRF protection
// ⚠️ VULNERABILITY: No authorization checks
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $driver_id = $_POST['driver_id'];
    $pickup = $_POST['pickup_location'];
    $dropoff = $_POST['dropoff_location'];
    
    // ⚠️ VULNERABILITY: Fare comes from client side and can be manipulated
    $fare = $_POST['fare']; // Should be calculated server-side!
    
    // ⚠️ VULNERABILITY: SQL Injection in INSERT
    $query = "INSERT INTO bookings (user_id, driver_id, pickup_location, dropoff_location, fare, status) 
              VALUES ($user_id, '$driver_id', '$pickup', '$dropoff', '$fare', 'pending')";
    
    if(mysqli_query($conn, $query)) {
        $booking_id = mysqli_insert_id($conn);
        
        // Create payment record
        $txn_id = 'TXN-' . date('Ymd') . '-' . rand(1000, 9999);
        $payment_query = "INSERT INTO payments (booking_id, user_id, amount, payment_method, transaction_id, status) 
                         VALUES ($booking_id, $user_id, '$fare', 'Credit Card', '$txn_id', 'pending')";
        mysqli_query($conn, $payment_query);
        
        header('Location: payment.php?booking_id=' . $booking_id);
        exit();
    } else {
        $error = 'Booking failed. Please try again.';
    }
}

include 'includes/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <h2><i class="fas fa-taxi"></i> Book a Ride</h2>
        <p class="text-muted">Choose a driver and enter your trip details</p>
    </div>
</div>

<div class="alert alert-warning">
    <strong><i class="fas fa-bug"></i> Vulnerability Alert:</strong> 
    Try manipulating the fare value in the form using browser developer tools! Also try intercepting the <code>/cab_vulnerable/api/fare.php</code> API call with Burp Suite to tamper the fare.
</div>

<?php if($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="row mt-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 text-white">Trip Details</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="" id="bookingForm">
                    <div class="mb-3">
                        <label for="driver_id" class="form-label">Select Driver</label>
                        <select class="form-control" id="driver_id" name="driver_id" required onchange="updateFare()">
                            <option value="">Choose a driver...</option>
                            <?php while($driver = mysqli_fetch_assoc($drivers_result)): ?>
                                <option value="<?php echo $driver['id']; ?>" data-rating="<?php echo $driver['rating']; ?>">
                                    <?php echo $driver['name']; ?> - <?php echo $driver['car_model']; ?> (<?php echo $driver['car_number']; ?>) 
                                    - Rating: <?php echo $driver['rating']; ?>/5.0
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="pickup_location" class="form-label">Pickup Location</label>
                        <input type="text" class="form-control" id="pickup_location" name="pickup_location" 
                               placeholder="Enter pickup address" required onchange="updateFare()">
                    </div>
                    
                    <div class="mb-3">
                        <label for="dropoff_location" class="form-label">Drop-off Location</label>
                        <input type="text" class="form-control" id="dropoff_location" name="dropoff_location" 
                               placeholder="Enter destination address" required onchange="updateFare()">
                    </div>
                    
                    <div class="mb-3">
                        <label for="fare" class="form-label">Estimated Fare (₹)</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <!-- ⚠️ VULNERABILITY: Fare is editable on client side — API Tampering possible -->
                            <input type="number" class="form-control" id="fare" name="fare" 
                                   step="0.01" value="0.00" required>
                        </div>
                        <small class="text-danger">⚠️ This value is fetched from <code>api/fare.php</code> but can be manipulated via DevTools or Burp Suite!</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-check-circle"></i> Confirm Booking
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card bg-light">
            <div class="card-body">
                <h5><i class="fas fa-info-circle"></i> Booking Information</h5>
                <hr>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-check text-success"></i> Available 24/7</li>
                    <li class="mb-2"><i class="fas fa-check text-success"></i> Safe & Verified Drivers</li>
                    <li class="mb-2"><i class="fas fa-check text-success"></i> Transparent Pricing</li>
                    <li class="mb-2"><i class="fas fa-check text-success"></i> Easy Cancellation</li>
                </ul>
                
                <div class="alert alert-danger mt-3">
                    <strong><i class="fas fa-bug"></i> Exploits to try:</strong><br>
                    1. Open DevTools → inspect fare field → change value to 0.01<br>
                    2. Intercept <code>api/fare.php</code> with Burp Suite → modify response<br>
                    3. Submit form with manipulated fare → get a free ride!
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ⚠️ VULNERABILITY: Fare fetched from an API endpoint — response can be tampered!
async function updateFare() {
    const pickup  = document.getElementById('pickup_location').value;
    const dropoff = document.getElementById('dropoff_location').value;
    const driver  = document.getElementById('driver_id').value;
    
    if (pickup && dropoff && driver) {
        try {
            // ⚠️ API Tampering: This API call can be intercepted and the fare modified
            const res = await fetch(`/cab_vulnerable/api/fare.php?pickup=${encodeURIComponent(pickup)}&dropoff=${encodeURIComponent(dropoff)}`);
            const data = await res.json();
            document.getElementById('fare').value = data.fare;
        } catch(e) {
            // Fallback: simple client-side calculation (also manipulable)
            const distance = Math.abs(pickup.length - dropoff.length) + 10;
            document.getElementById('fare').value = (50 + distance * 15).toFixed(2);
        }
    }
}
</script>

<?php include 'includes/footer.php'; ?>
