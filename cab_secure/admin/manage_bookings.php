<?php
error_reporting(0);
session_name('SECURE_ADMIN_SESSION');
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

// ✅ SECURITY: Require Admin Authentication
require_admin();

$success = '';

// Update booking status
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $booking_id = (int)$_POST['booking_id'];
    $new_status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $booking_id]);
        $success = "Booking #$booking_id updated to $new_status!";
    } catch(PDOException $e) {
        $error = "Update failed.";
    }
}

// Get all bookings
try {
    $stmt = $pdo->query("SELECT b.*, u.username, d.name as driver_name 
                        FROM bookings b 
                        JOIN users u ON b.user_id = u.id 
                        JOIN drivers d ON b.driver_id = d.id 
                        ORDER BY b.booking_time DESC");
    $bookings = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Error loading bookings.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - QuickCab Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-danger">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-shield-alt"></i> Admin Panel
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="manage_bookings.php">Bookings</a>
                <a class="nav-link" href="manage_drivers.php">Drivers</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <h2><i class="fas fa-taxi"></i> Manage Bookings</h2>
        <p class="text-muted">Update booking statuses</p>
        
        <?php if($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <div class="card mt-4">
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
                            <?php foreach($bookings as $booking): ?>
                            <tr>
                                <td>#<?php echo $booking['id']; ?></td>
                                <td><?php echo htmlspecialchars($booking['username']); ?></td>
                                <td><?php echo htmlspecialchars($booking['driver_name']); ?></td>
                                <td>
                                    <small>
                                        <?php echo htmlspecialchars(substr($booking['pickup_location'], 0, 15)); ?>... → 
                                        <?php echo htmlspecialchars(substr($booking['dropoff_location'], 0, 15)); ?>...
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
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
