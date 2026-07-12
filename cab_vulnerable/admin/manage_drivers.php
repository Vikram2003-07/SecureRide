<?php 
session_start();
require_once '../includes/config.php';

if(!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$success = '';
$error = '';

// Get all drivers
$query = "SELECT * FROM drivers ORDER BY id DESC";
$result = mysqli_query($conn, $query);

// Add new driver (⚠️ VULNERABILITY: No CSRF protection)
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_driver'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $car_model = $_POST['car_model'];
    $car_number = $_POST['car_number'];
    
    // ⚠️ VULNERABILITY: SQL Injection
    $insert = "INSERT INTO drivers (name, phone, car_model, car_number, status) 
               VALUES ('$name', '$phone', '$car_model', '$car_number', 'available')";
    
    if(mysqli_query($conn, $insert)) {
        $success = 'Driver added successfully!';
    } else {
        $error = 'Failed to add driver.';
    }
    
    // Refresh driver list
    $result = mysqli_query($conn, $query);
}

// Update driver status
if(isset($_GET['update_status'])) {
    $driver_id = $_GET['driver_id'];
    $new_status = $_GET['status'];
    
    // ⚠️ VULNERABILITY: No authorization check, SQL Injection
    $update = "UPDATE drivers SET status='$new_status' WHERE id=$driver_id";
    mysqli_query($conn, $update);
    
    header('Location: manage_drivers.php');
    exit();
}

// Delete driver
if(isset($_GET['delete'])) {
    $driver_id = $_GET['delete'];
    
    // ⚠️ VULNERABILITY: No confirmation, no CSRF protection
    $delete = "DELETE FROM drivers WHERE id=$driver_id";
    mysqli_query($conn, $delete);
    
    header('Location: manage_drivers.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Drivers - Admin</title>
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
                <a class="nav-link active" href="manage_drivers.php">Drivers</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <h2><i class="fas fa-users-cog"></i> Manage Drivers</h2>
        
        <?php if($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0 text-white">Add New Driver</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="name" placeholder="Driver Name" required>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="phone" placeholder="Phone" required>
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="car_model" placeholder="Car Model" required>
                        </div>
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="car_number" placeholder="Car #" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="add_driver" class="btn btn-success w-100">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0 text-white">All Drivers</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Car Model</th>
                                <th>Car Number</th>
                                <th>Rating</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($driver = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $driver['id']; ?></td>
                                <td><?php echo $driver['name']; ?></td>
                                <td><?php echo $driver['phone']; ?></td>
                                <td><?php echo $driver['car_model']; ?></td>
                                <td><?php echo $driver['car_number']; ?></td>
                                <td>
                                    <span class="text-warning">
                                        <?php echo $driver['rating']; ?>/5.0
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $status_class = '';
                                    switch($driver['status']) {
                                        case 'available': $status_class = 'bg-success'; break;
                                        case 'busy': $status_class = 'bg-warning text-dark'; break;
                                        case 'offline': $status_class = 'bg-secondary'; break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $status_class; ?>">
                                        <?php echo ucfirst($driver['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="?update_status=1&driver_id=<?php echo $driver['id']; ?>&status=available" 
                                           class="btn btn-success" title="Set Available">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <a href="?update_status=1&driver_id=<?php echo $driver['id']; ?>&status=busy" 
                                           class="btn btn-warning" title="Set Busy">
                                            <i class="fas fa-clock"></i>
                                        </a>
                                        <a href="?update_status=1&driver_id=<?php echo $driver['id']; ?>&status=offline" 
                                           class="btn btn-secondary" title="Set Offline">
                                            <i class="fas fa-moon"></i>
                                        </a>
                                        <a href="?delete=<?php echo $driver['id']; ?>" 
                                           class="btn btn-danger" 
                                           onclick="return confirm('Delete this driver?')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
