<?php
error_reporting(0);
require_once 'includes/config.php';
require_once 'includes/auth.php';

// ✅ SECURITY: Require Authentication
require_auth();

// ✅ SECURITY: Get user ID from session (IDOR Prevention)
$user_id = get_user_id();

$success = '';
$error = '';

// Get available drivers
try {
    $stmt = $pdo->query("SELECT * FROM drivers WHERE status='available'");
    $drivers_result = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Error loading drivers.";
}

// ✅ SECURITY: Secure Booking Process
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ✅ CSRF Validation
    if (!validate_csrf($_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
    } else {
        $driver_id = (int)$_POST['driver_id'];
        $pickup    = clean_input($_POST['pickup_location']);
        $dropoff   = clean_input($_POST['dropoff_location']);

        // ✅ SECURITY: Re-calculate distance server-side using Nominatim geocoding
        // We geocode both addresses on the server — client cannot tamper with distance
        $distance = server_geocode_distance($pickup, $dropoff);

        if ($distance === false) {
            $error = 'Could not calculate distance for the given locations. Please check the addresses and try again.';
        } elseif (empty($pickup) || empty($dropoff) || $driver_id <= 0) {
            $error = 'Please fill all fields correctly.';
        } else {
            // ✅ SECURITY: Server-Side Fare Calculation (NEVER trust client)
            $fare = calculate_fare($distance);

            try {
                // ✅ SECURITY: Prepared Statement
                $stmt = $pdo->prepare("INSERT INTO bookings (user_id, driver_id, pickup_location, dropoff_location, fare, status) 
                                      VALUES (?, ?, ?, ?, ?, 'pending')");
                $stmt->execute([$user_id, $driver_id, $pickup, $dropoff, $fare]);

                $booking_id = $pdo->lastInsertId();

                // Create payment record
                $txn_id = 'TXN-' . date('Ymd') . '-' . rand(1000, 9999);
                $stmt = $pdo->prepare("INSERT INTO payments (booking_id, user_id, amount, payment_method, transaction_id, status) 
                                      VALUES (?, ?, ?, 'Credit Card', ?, 'pending')");
                $stmt->execute([$booking_id, $user_id, $fare, $txn_id]);

                redirect('payment.php?booking_id=' . $booking_id);
            } catch(PDOException $e) {
                $error = 'Booking failed. Please try again.';
            }
        }
    }
}

// ✅ SECURITY: Server-side geocoding using Nominatim (OpenStreetMap) — no API key needed
function server_geocode_distance($pickup, $dropoff) {
    $coords1 = nominatim_geocode($pickup);
    $coords2 = nominatim_geocode($dropoff);

    if (!$coords1 || !$coords2) {
        return false;
    }

    return haversine_distance($coords1['lat'], $coords1['lon'], $coords2['lat'], $coords2['lon']);
}

function nominatim_geocode($address) {
    $url = 'https://nominatim.openstreetmap.org/search?q=' . urlencode($address) . '&format=json&limit=1';
    $opts = [
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: CabBookSecure/1.0 (educational-demo)\r\n",
            'timeout' => 5
        ]
    ];
    $context = stream_context_create($opts);
    $response = @file_get_contents($url, false, $context);
    if (!$response) return false;

    $data = json_decode($response, true);
    if (empty($data)) return false;

    return ['lat' => (float)$data[0]['lat'], 'lon' => (float)$data[0]['lon']];
}

// Haversine formula — returns distance in km
function haversine_distance($lat1, $lon1, $lat2, $lon2) {
    $R = 6371; // Earth radius in km
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return round($R * $c, 2);
}

include 'includes/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <h2><i class="fas fa-taxi"></i> Book a Ride</h2>
        <p class="text-muted">Choose a driver and enter your trip details</p>
    </div>
</div>

<div class="alert alert-success">
    <strong><i class="fas fa-shield-alt"></i> Security Active:</strong> 
    Distance is calculated server-side using real geocoding (Nominatim/OpenStreetMap). Fare cannot be manipulated by the client!
</div>

<?php if($error): ?>
    <div class="alert alert-danger"><?php echo escape_output($error); ?></div>
<?php endif; ?>

<div class="row mt-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 text-white">Trip Details</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="" id="bookingForm">
                    <!-- ✅ CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="mb-3">
                        <label for="driver_id" class="form-label">Select Driver</label>
                        <select class="form-control" id="driver_id" name="driver_id" required>
                            <option value="">Choose a driver...</option>
                            <?php foreach($drivers_result as $driver): ?>
                                <option value="<?php echo $driver['id']; ?>" data-rating="<?php echo $driver['rating']; ?>">
                                    <?php echo escape_output($driver['name']); ?> - <?php echo escape_output($driver['car_model']); ?> (<?php echo escape_output($driver['car_number']); ?>) 
                                    - Rating: <?php echo $driver['rating']; ?>/5.0
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="pickup_location" class="form-label">Pickup Location</label>
                        <input type="text" class="form-control" id="pickup_location" name="pickup_location" 
                               placeholder="e.g. Chennai Central, Tamil Nadu" required>
                    </div>

                    <div class="mb-3">
                        <label for="dropoff_location" class="form-label">Drop-off Location</label>
                        <input type="text" class="form-control" id="dropoff_location" name="dropoff_location" 
                               placeholder="e.g. Chennai Airport, Tamil Nadu" required>
                    </div>

                    <!-- Fare preview (read-only, calculated client-side for display only) -->
                    <div class="mb-3">
                        <label class="form-label">Estimated Fare Preview</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="text" class="form-control" id="fare_preview" 
                                   placeholder="Click 'Calculate Fare' to estimate" readonly>
                        </div>
                        <small class="text-success">
                            ✅ This is a preview only. The actual fare is <strong>re-calculated server-side</strong> using real geocoding when you confirm.
                        </small>
                    </div>

                    <div id="distance_info" class="alert alert-info d-none">
                        <i class="fas fa-route"></i> <span id="distance_text"></span>
                    </div>

                    <div class="d-flex gap-2 mb-3">
                        <button type="button" class="btn btn-outline-primary" id="calcBtn" onclick="estimateFare()">
                            <i class="fas fa-calculator"></i> Calculate Fare
                        </button>
                    </div>

                    <div class="alert alert-secondary">
                        <strong>Fare Formula:</strong> ₹50 base fare + ₹15 per km (server-side calculation)
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

                <div class="alert alert-success mt-3">
                    <strong><i class="fas fa-shield-alt"></i> How fare is secured:</strong><br>
                    1. You enter pickup & drop-off<br>
                    2. Server geocodes both via OpenStreetMap<br>
                    3. Haversine distance calculated server-side<br>
                    4. Fare = ₹50 + ₹15/km (server only)<br>
                    5. Client cannot override the fare!
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Client-side preview using Nominatim (for UX only — server re-validates everything)
async function geocode(address) {
    const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(address)}&format=json&limit=1`;
    try {
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        if (data.length === 0) return null;
        return { lat: parseFloat(data[0].lat), lon: parseFloat(data[0].lon) };
    } catch(e) {
        return null;
    }
}

function haversine(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2)**2 +
              Math.cos(lat1 * Math.PI/180) * Math.cos(lat2 * Math.PI/180) *
              Math.sin(dLon/2)**2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}

async function estimateFare() {
    const pickup  = document.getElementById('pickup_location').value.trim();
    const dropoff = document.getElementById('dropoff_location').value.trim();
    const btn     = document.getElementById('calcBtn');
    const info    = document.getElementById('distance_info');
    const distTxt = document.getElementById('distance_text');
    const fareBox = document.getElementById('fare_preview');

    if (!pickup || !dropoff) {
        alert('Please enter both pickup and drop-off locations first.');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Calculating...';
    fareBox.value = '';
    info.classList.add('d-none');

    const [c1, c2] = await Promise.all([geocode(pickup), geocode(dropoff)]);

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-calculator"></i> Calculate Fare';

    if (!c1 || !c2) {
        fareBox.value = 'Location not found';
        return;
    }

    const dist = haversine(c1.lat, c1.lon, c2.lat, c2.lon).toFixed(2);
    const fare = (50 + dist * 15).toFixed(2);

    fareBox.value = fare;
    distTxt.textContent = `Estimated distance: ${dist} km  |  Preview fare: ₹${fare}`;
    info.classList.remove('d-none');
}
</script>

<?php include 'includes/footer.php'; ?>
