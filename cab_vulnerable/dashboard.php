<?php 
require_once 'includes/config.php';

// ⚠️ VULNERABILITY: Weak authentication check
// No session validation or timeout
if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user info
$query = "SELECT * FROM users WHERE id=$user_id";
$user_result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($user_result);

// Get recent bookings
$bookings_query = "SELECT b.*, d.name as driver_name, d.car_model 
                  FROM bookings b 
                  JOIN drivers d ON b.driver_id = d.id 
                  WHERE b.user_id=$user_id 
                  ORDER BY b.booking_time DESC LIMIT 5";
$bookings_result = mysqli_query($conn, $bookings_query);

// Get stats
$total_rides_query = "SELECT COUNT(*) as total FROM bookings WHERE user_id=$user_id";
$total_rides_result = mysqli_query($conn, $total_rides_query);
$total_rides = mysqli_fetch_assoc($total_rides_result)['total'];

$total_spent_query = "SELECT SUM(amount) as total FROM payments WHERE user_id=$user_id AND status='completed'";
$total_spent_result = mysqli_query($conn, $total_spent_query);
$total_spent = mysqli_fetch_assoc($total_spent_result)['total'] ?? 0;

include 'includes/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <h2>Welcome, <?php echo $user['username']; ?>!</h2>
        <p class="text-muted">Manage your rides and profile from your dashboard</p>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h3><?php echo $total_rides; ?></h3>
                <p class="mb-0">Total Rides</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h3>$<?php echo number_format($total_spent, 2); ?></h3>
                <p class="mb-0">Total Spent</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <h3><i class="fas fa-taxi"></i></h3>
                <p class="mb-0"><a href="booking.php" class="text-dark text-decoration-none">Book New Ride</a></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h3><i class="fas fa-user"></i></h3>
                <p class="mb-0"><a href="profile.php" class="text-white text-decoration-none">My Profile</a></p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-5">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 text-white">Recent Bookings</h5>
            </div>
            <div class="card-body">
                <?php if(mysqli_num_rows($bookings_result) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Driver</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Fare</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($booking = mysqli_fetch_assoc($bookings_result)): ?>
                                <tr>
                                    <td>#<?php echo $booking['id']; ?></td>
                                    <td><?php echo $booking['driver_name']; ?></td>
                                    <td><?php echo substr($booking['pickup_location'], 0, 30); ?>...</td>
                                    <td><?php echo substr($booking['dropoff_location'], 0, 30); ?>...</td>
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
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="history.php" class="btn btn-primary">View All Bookings</a>
                <?php else: ?>
                    <p class="text-center text-muted">No bookings yet. <a href="booking.php">Book your first ride!</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 text-white">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="booking.php" class="btn btn-warning w-100 mb-2">
                            <i class="fas fa-plus-circle"></i> New Booking
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="drivers.php" class="btn btn-info w-100 mb-2">
                            <i class="fas fa-users"></i> View Drivers
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="history.php" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-history"></i> Ride History
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="reviews.php" class="btn btn-success w-100 mb-2">
                            <i class="fas fa-star"></i> My Reviews
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
