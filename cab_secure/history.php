<?php
error_reporting(0);
require_once 'includes/config.php';
require_once 'includes/auth.php';

// ✅ SECURITY: Require Authentication (URL Manipulation Prevention)
require_auth();

// ✅ SECURITY: Get user ID from session only (IDOR Prevention)
$user_id =get_user_id();

// Get user's bookings only
try {
    $stmt = $pdo->prepare("SELECT b.*, d.name as driver_name, d.car_model 
                          FROM bookings b 
                          JOIN drivers d ON b.driver_id = d.id 
                          WHERE b.user_id = ? 
                          ORDER BY b.booking_time DESC");
    $stmt->execute([$user_id]);
    $bookings_result = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Error loading booking history.";
}

include 'includes/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <h2><i class="fas fa-history"></i> My Booking History</h2>
        <p class="text-muted">View all your past and current bookings</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 text-white">All Bookings</h5>
    </div>
    <div class="card-body">
        <?php if(count($bookings_result) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Driver</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Fare</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($bookings_result as $booking): ?>
                        <tr>
                            <td>#<?php echo $booking['id']; ?></td>
                            <td><?php echo escape_output($booking['driver_name']); ?></td>
                            <td><?php echo escape_output(substr($booking['pickup_location'], 0, 30)); ?>...</td>
                            <td><?php echo escape_output(substr($booking['dropoff_location'], 0, 30)); ?>...</td>
                            <td>$<?php echo number_format($booking['fare'], 2); ?></td>
                            <td>
                                <?php
                                $badge_class = '';
                                switch($booking['status']) {
                                    case 'completed': $badge_class = 'bg-success'; break;
                                    case 'confirmed': $badge_class = 'bg-info'; break;
                                    case 'pending': $badge_class = 'bg-warning'; break;
                                    case 'cancelled': $badge_class = 'bg-danger'; break;
                                }
                                ?>
                                <span class="badge <?php echo $badge_class; ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($booking['booking_time'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">No bookings yet. <a href="booking.php">Book your first ride!</a></p>
        <?php endif; ?>
    </div>
</div>

<div class="alert alert-success mt-4">
    <i class="fas fa-shield-alt"></i> <strong>IDOR Protection:</strong>
    You can only view YOUR bookings. User ID from session only - URL manipulation blocked!
</div>

<?php include 'includes/footer.php'; ?>
