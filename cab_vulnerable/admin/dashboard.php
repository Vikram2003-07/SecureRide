<?php 
session_start();
require_once '../includes/config.php';

// ⚠️ VULNERABILITY: Weak authentication check
if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Get statistics
$total_users_query = "SELECT COUNT(*) as total FROM users";
$total_users = mysqli_fetch_assoc(mysqli_query($conn, $total_users_query))['total'];

$total_drivers_query = "SELECT COUNT(*) as total FROM drivers";
$total_drivers = mysqli_fetch_assoc(mysqli_query($conn, $total_drivers_query))['total'];

$total_bookings_query = "SELECT COUNT(*) as total FROM bookings";
$total_bookings = mysqli_fetch_assoc(mysqli_query($conn, $total_bookings_query))['total'];

$total_revenue_query = "SELECT SUM(amount) as total FROM payments WHERE status='completed'";
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, $total_revenue_query))['total'] ?? 0;

// Recent bookings
$recent_bookings = "SELECT b.*, u.username, d.name as driver_name 
                    FROM bookings b 
                    JOIN users u ON b.user_id = u.id 
                    JOIN drivers d ON b.driver_id = d.id 
                    ORDER BY b.booking_time DESC LIMIT 10";
$bookings_result = mysqli_query($conn, $recent_bookings);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - QuickCab</title>
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
                <a class="nav-link" href="manage_bookings.php">Bookings</a>
                <a class="nav-link" href="manage_drivers.php">Drivers</a>
                <a class="nav-link" href="manage_upi.php"><i class="fas fa-qrcode"></i> UPI QR</a>
                <a class="nav-link" href="<?php echo BASE_URL; ?>index.php">Main Site</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <h2>Admin Dashboard</h2>
        <p class="text-muted">Welcome, <?php echo $_SESSION['admin_username']; ?></p>
        
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="admin-stats bg-primary">
                    <h3><?php echo $total_users; ?></h3>
                    <p><i class="fas fa-users"></i> Total Users</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="admin-stats bg-success">
                    <h3><?php echo $total_drivers; ?></h3>
                    <p><i class="fas fa-car"></i> Total Drivers</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="admin-stats bg-warning">
                    <h3><?php echo $total_bookings; ?></h3>
                    <p><i class="fas fa-taxi"></i> Total Bookings</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="admin-stats bg-danger">
                    <h3>$<?php echo number_format($total_revenue, 2); ?></h3>
                    <p><i class="fas fa-dollar-sign"></i> Revenue</p>
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
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Driver</th>
                                        <th>From → To</th>
                                        <th>Fare</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($booking = mysqli_fetch_assoc($bookings_result)): ?>
                                    <tr>
                                        <td>#<?php echo $booking['id']; ?></td>
                                        <td><?php echo $booking['username']; ?></td>
                                        <td><?php echo $booking['driver_name']; ?></td>
                                        <td>
                                            <small>
                                                <?php echo substr($booking['pickup_location'], 0, 20); ?>... → 
                                                <?php echo substr($booking['dropoff_location'], 0, 20); ?>...
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
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
