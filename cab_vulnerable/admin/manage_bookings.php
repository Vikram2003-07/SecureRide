<?php 
require_once '../includes/config.php';

// ⚠️ VULNERABILITY: Weak admin authentication
if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$success = '';

// ⚠️ VULNERABILITY: No CSRF protection
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['status'];
    
    // ⚠️ VULNERABILITY: SQL Injection
    $update_query = "UPDATE bookings SET status='$new_status' WHERE id=$booking_id";
    
    if(mysqli_query($conn, $update_query)) {
        $success = "Booking #$booking_id updated to $new_status!";
    } else {
        $error = "Update failed.";
    }
}

// Get all bookings
$query = "SELECT b.*, u.username, d.name as driver_name 
          FROM bookings b 
          JOIN users u ON b.user_id = u.id 
          JOIN drivers d ON b.driver_id = d.id 
          ORDER BY b.booking_time DESC";
$result = mysqli_query($conn, $query);

include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <h2><i class="fas fa-taxi"></i> Manage Bookings</h2>
        <p class="text-muted">Update booking statuses</p>
    </div>
</div>

<?php if($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="alert alert-warning">
    <strong><i class="fas fa-exclamation-triangle"></i> Admin Panel:</strong>
    Change booking statuses to "completed" to make them reviewable!
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0 text-white">All Bookings</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Driver</th>
                        <th>Route</th>
                        <th>Fare</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($booking = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>#<?php echo $booking['id']; ?></td>
                        <td><?php echo $booking['username']; ?></td>
                        <td><?php echo $booking['driver_name']; ?></td>
                        <td>
                            <small>
                                <?php echo substr($booking['pickup_location'], 0, 15); ?>... → 
                                <?php echo substr($booking['dropoff_location'], 0, 15); ?>...
                            </small>
                        </td>
                        <td>$<?php echo number_format($booking['fare'], 2); ?></td>
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
                        <td><?php echo date('M d, H:i', strtotime($booking['booking_time'])); ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                <select name="status" class="form-select form-select-sm" style="width: auto; display: inline-block;">
                                    <option value="pending" <?php if($booking['status']=='pending') echo 'selected'; ?>>Pending</option>
                                    <option value="confirmed" <?php if($booking['status']=='confirmed') echo 'selected'; ?>>Confirmed</option>
                                    <option value="completed" <?php if($booking['status']=='completed') echo 'selected'; ?>>Completed</option>
                                    <option value="cancelled" <?php if($booking['status']=='cancelled') echo 'selected'; ?>>Cancelled</option>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-sm btn-primary">
                                    <i class="fas fa-save"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
