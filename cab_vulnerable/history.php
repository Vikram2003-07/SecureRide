<?php 
require_once 'includes/config.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// ⚠️ VULNERABILITY: IDOR (Insecure Direct Object Reference)
// User can view ANY user's bookings by changing the user_id parameter in URL
// Example: history.php?user_id=2 will show user 2's bookings

if(isset($_GET['user_id'])) {
    // ⚠️ VULNERABILITY: No authorization check!
    // Anyone can view anyone else's booking history
    $user_id = $_GET['user_id'];
} else {
    $user_id = $_SESSION['user_id'];
}

// Get bookings for the user
$query = "SELECT b.*, d.name as driver_name, d.car_model, d.phone as driver_phone, p.status as payment_status 
          FROM bookings b 
          JOIN drivers d ON b.driver_id = d.id 
          LEFT JOIN payments p ON b.id = p.booking_id
          WHERE b.user_id=$user_id 
          ORDER BY b.booking_time DESC";
$result = mysqli_query($conn, $query);

include 'includes/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <h2><i class="fas fa-history"></i> Ride History</h2>
        <p class="text-muted">View all your past and upcoming rides</p>
    </div>
</div>

<div class="alert alert-danger">
    <strong><i class="fas fa-exclamation-triangle"></i> IDOR Vulnerability:</strong> 
    Try changing the URL to <code>history.php?user_id=2</code> to view other users' bookings!
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-white">All Bookings (User ID: <?php echo $user_id; ?>)</h5>
                <a href="booking.php" class="btn btn-sm btn-light">
                    <i class="fas fa-plus"></i> New Booking
                </a>
            </div>
            <div class="card-body">
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Driver</th>
                                    <th>Contact</th>
                                    <th>Pickup Location</th>
                                    <th>Dropoff Location</th>
                                    <th>Fare</th>
                                    <th>Booking Status</th>
                                    <th>Payment</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($booking = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><strong>#<?php echo $booking['id']; ?></strong></td>
                                    <td>
                                        <?php echo $booking['driver_name']; ?><br>
                                        <small class="text-muted"><?php echo $booking['car_model']; ?></small>
                                    </td>
                                    <td><?php echo $booking['driver_phone']; ?></td>
                                    <td><?php echo $booking['pickup_location']; ?></td>
                                    <td><?php echo $booking['dropoff_location']; ?></td>
                                    <td><strong>$<?php echo number_format($booking['fare'], 2); ?></strong></td>
                                    <td>
                                        <?php
                                        $badge_class = '';
                                        switch($booking['status']) {
                                            case 'completed': $badge_class = 'bg-success'; break;
                                            case 'confirmed': $badge_class = 'bg-info'; break;
                                            case 'pending': $badge_class = 'bg-warning text-dark'; break;
                                            case 'cancelled': $badge_class = 'bg-danger'; break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $payment_class = '';
                                        $payment_status = $booking['payment_status'] ?? 'N/A';
                                        switch($payment_status) {
                                            case 'completed': $payment_class = 'bg-success'; break;
                                            case 'pending': $payment_class = 'bg-warning text-dark'; break;
                                            case 'failed': $payment_class = 'bg-danger'; break;
                                            default: $payment_class = 'bg-secondary';
                                        }
                                        ?>
                                        <span class="badge <?php echo $payment_class; ?>">
                                            <?php echo ucfirst($payment_status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y H:i', strtotime($booking['booking_time'])); ?></td>
                                    <td>
                                        <?php if($booking['status'] == 'completed'): ?>
                                            <a href="reviews.php?booking_id=<?php echo $booking['id']; ?>" 
                                               class="btn btn-sm btn-warning" title="Add Review">
                                                <i class="fas fa-star"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                        <p class="text-muted">No bookings found.</p>
                        <a href="booking.php" class="btn btn-primary">Book Your First Ride</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="alert alert-info">
            <strong><i class="fas fa-lightbulb"></i> Tip:</strong> 
            Try accessing bookings of other users by modifying the user_id parameter:
            <ul class="mb-0 mt-2">
                <li><code>history.php?user_id=1</code> - View user 1's bookings</li>
                <li><code>history.php?user_id=2</code> - View user 2's bookings</li>
                <li><code>history.php?user_id=3</code> - View user 3's bookings</li>
            </ul>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
