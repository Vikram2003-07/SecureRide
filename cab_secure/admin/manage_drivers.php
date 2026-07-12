<?php
error_reporting(0);
session_name('SECURE_ADMIN_SESSION');
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';

// ✅ SECURITY: Require Admin Authentication
require_admin();

$success = '';
$error = '';

// ✅ SECURITY: Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Add new driver
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_driver'])) {
    // ✅ SECURITY: CSRF validation
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $name       = trim($_POST['name'] ?? '');
        $phone      = trim($_POST['phone'] ?? '');
        $car_model  = trim($_POST['car_model'] ?? '');
        $car_number = trim($_POST['car_number'] ?? '');

        if ($name && $phone && $car_model && $car_number) {
            try {
                // ✅ SECURITY: Prepared statement — no SQL injection
                $stmt = $pdo->prepare("INSERT INTO drivers (name, phone, car_model, car_number, status) VALUES (?, ?, ?, ?, 'available')");
                $stmt->execute([$name, $phone, $car_model, $car_number]);
                $success = 'Driver added successfully!';
            } catch (PDOException $e) {
                $error = 'Failed to add driver.';
            }
        } else {
            $error = 'All fields are required.';
        }
    }
}

// Update driver status
if (isset($_GET['update_status']) && isset($_GET['driver_id']) && isset($_GET['status'])) {
    $allowed_statuses = ['available', 'busy', 'offline'];
    $driver_id  = (int) $_GET['driver_id'];
    $new_status = $_GET['status'];

    if (in_array($new_status, $allowed_statuses) && $driver_id > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE drivers SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $driver_id]);
        } catch (PDOException $e) {
            // silently fail
        }
    }
    header('Location: manage_drivers.php');
    exit();
}

// Delete driver
if (isset($_GET['delete'])) {
    $driver_id = (int) $_GET['delete'];
    if ($driver_id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM drivers WHERE id = ?");
            $stmt->execute([$driver_id]);
        } catch (PDOException $e) {
            // silently fail
        }
    }
    header('Location: manage_drivers.php');
    exit();
}

// Fetch all drivers
try {
    $stmt = $pdo->query("SELECT * FROM drivers ORDER BY id DESC");
    $drivers = $stmt->fetchAll();
} catch (PDOException $e) {
    $drivers = [];
    $error = 'Error loading drivers.';
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
                <a class="nav-link" href="manage_bookings.php">Bookings</a>
                <a class="nav-link active" href="manage_drivers.php">Drivers</a>
                <a class="nav-link" href="../index.php">Main Site</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2><i class="fas fa-users-cog"></i> Manage Drivers</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0 text-white">Add New Driver</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <div class="row g-2">
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
                            <?php foreach ($drivers as $driver): ?>
                            <tr>
                                <td><?php echo (int)$driver['id']; ?></td>
                                <td><?php echo htmlspecialchars($driver['name']); ?></td>
                                <td><?php echo htmlspecialchars($driver['phone']); ?></td>
                                <td><?php echo htmlspecialchars($driver['car_model']); ?></td>
                                <td><?php echo htmlspecialchars($driver['car_number']); ?></td>
                                <td>
                                    <span class="text-warning">
                                        <?php echo htmlspecialchars($driver['rating'] ?? '0.0'); ?>/5.0
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $status_class = match($driver['status']) {
                                        'available' => 'bg-success',
                                        'busy'      => 'bg-warning text-dark',
                                        default     => 'bg-secondary',
                                    };
                                    ?>
                                    <span class="badge <?php echo $status_class; ?>">
                                        <?php echo ucfirst(htmlspecialchars($driver['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="?update_status=1&driver_id=<?php echo (int)$driver['id']; ?>&status=available"
                                           class="btn btn-success" title="Set Available">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <a href="?update_status=1&driver_id=<?php echo (int)$driver['id']; ?>&status=busy"
                                           class="btn btn-warning" title="Set Busy">
                                            <i class="fas fa-clock"></i>
                                        </a>
                                        <a href="?update_status=1&driver_id=<?php echo (int)$driver['id']; ?>&status=offline"
                                           class="btn btn-secondary" title="Set Offline">
                                            <i class="fas fa-moon"></i>
                                        </a>
                                        <a href="?delete=<?php echo (int)$driver['id']; ?>"
                                           class="btn btn-danger"
                                           onclick="return confirm('Delete this driver?')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
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
