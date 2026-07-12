<?php
error_reporting(0);
require_once 'includes/config.php';
require_once 'includes/auth.php';

// ✅ SECURITY: Require Authentication
require_auth();

// Get all drivers
try {
    $stmt = $pdo->query("SELECT * FROM drivers ORDER BY rating DESC");
    $drivers = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Error loading drivers.";
}

include 'includes/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <h2><i class="fas fa-users"></i> Our Drivers</h2>
        <p class="text-muted">Browse our verified and professional drivers</p>
    </div>
</div>

<div class="row mt-4">
    <?php foreach($drivers as $driver): ?>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body driver-card">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($driver['name']); ?>&size=100&background=ffc107&color=000" alt="Driver">
                    <h5><?php echo escape_output($driver['name']); ?></h5>
                    <p class="text-muted mb-1"><?php echo escape_output($driver['car_model']); ?></p>
                    <p class="text-muted mb-2"><small><?php echo escape_output($driver['car_number']); ?></small></p>
                    <div class="driver-rating">
                        <i class="fas fa-star"></i> <?php echo $driver['rating']; ?>/5.0
                    </div>
                    <p class="mb-2"><i class="fas fa-phone"></i> <?php echo escape_output($driver['phone']); ?></p>
                    <?php if($driver['status'] == 'available'): ?>
                        <span class="driver-status status-available">Available</span>
                    <?php else: ?>
                        <span class="driver-status status-offline">Offline</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="text-center mt-4">
    <a href="booking.php" class="btn btn-primary btn-lg">
        <i class="fas fa-taxi"></i> Book a Ride Now
    </a>
</div>

<?php include 'includes/footer.php'; ?>
