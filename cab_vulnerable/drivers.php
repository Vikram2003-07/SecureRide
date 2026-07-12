<?php 
require_once 'includes/config.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get all drivers
$query = "SELECT * FROM drivers ORDER BY rating DESC";
$result = mysqli_query($conn, $query);

include 'includes/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <h2><i class="fas fa-users"></i> Available Drivers</h2>
        <p class="text-muted">Browse our professional drivers and their ratings</p>
    </div>
</div>

<div class="row mt-4">
    <?php while($driver = mysqli_fetch_assoc($result)): ?>
    <div class="col-md-4 mb-4">
        <div class="card driver-card h-100">
            <div class="card-body">
                <div class="text-center">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($driver['name']); ?>&size=100&background=ffc107&color=212529" 
                         alt="<?php echo $driver['name']; ?>" class="rounded-circle mb-3">
                </div>
                <h5 class="text-center"><?php echo $driver['name']; ?></h5>
                <p class="text-center text-muted mb-2">
                    <i class="fas fa-car"></i> <?php echo $driver['car_model']; ?><br>
                    <small><?php echo $driver['car_number']; ?></small>
                </p>
                <div class="text-center driver-rating mb-2">
                    <?php 
                    $rating = $driver['rating'];
                    for($i = 1; $i <= 5; $i++) {
                        if($i <= floor($rating)) {
                            echo '<i class="fas fa-star"></i>';
                        } elseif($i - $rating < 1) {
                            echo '<i class="fas fa-star-half-alt"></i>';
                        } else {
                            echo '<i class="far fa-star"></i>';
                        }
                    }
                    ?>
                    <span class="ms-2"><?php echo $rating; ?>/5.0</span>
                </div>
                <p class="text-center mb-2">
                    <i class="fas fa-phone"></i> <?php echo $driver['phone']; ?>
                </p>
                <div class="text-center">
                    <?php
                    $status_class = '';
                    switch($driver['status']) {
                        case 'available': $status_class = 'status-available'; break;
                        case 'busy': $status_class = 'status-busy'; break;
                        case 'offline': $status_class = 'status-offline'; break;
                    }
                    ?>
                    <span class="driver-status <?php echo $status_class; ?>">
                        <?php echo ucfirst($driver['status']); ?>
                    </span>
                </div>
                <?php if($driver['status'] == 'available'): ?>
                <div class="text-center mt-3">
                    <a href="booking.php?driver_id=<?php echo $driver['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-taxi"></i> Book Now
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<?php include 'includes/footer.php'; ?>
