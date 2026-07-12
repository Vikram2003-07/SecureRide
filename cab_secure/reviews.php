<?php
error_reporting(0);
require_once 'includes/config.php';
require_once 'includes/auth.php';

// ✅ SECURITY: Require Authentication
require_auth();

$user_id = get_user_id();
$success = '';
$error = '';

// Get completed/confirmed bookings for review
try {
    $stmt = $pdo->prepare("SELECT b.*, d.name as driver_name 
                          FROM bookings b 
                          JOIN drivers d ON b.driver_id = d.id 
                          WHERE b.user_id = ? AND (b.status = 'completed' OR b.status = 'confirmed')
                          ORDER BY b.booking_time DESC");
    $stmt->execute([$user_id]);
    $bookings = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Error loading bookings.";
}

// ✅ SECURITY: Secure Review Submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ✅ CSRF Validation
    if (!validate_csrf($_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
    } else {
        $booking_id = (int)$_POST['booking_id'];
        $rating = (int)$_POST['rating'];
        $comment = clean_input($_POST['comment']);
        
        // ✅ SECURITY: Verify booking ownership
        if (!verify_booking_owner($booking_id, $user_id, $pdo)) {
            $error = 'Invalid booking.';
        } elseif($rating < 1 || $rating > 5) {
            $error = 'Rating must be between 1 and 5.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO reviews (booking_id, user_id, driver_id, rating, comment) 
                                      SELECT ?, ?, driver_id, ?, ? FROM bookings WHERE id = ? AND user_id = ?");
                $stmt->execute([$booking_id, $user_id, $rating, $comment, $booking_id, $user_id]);
                
                $success = 'Review submitted successfully!';
            } catch(PDOException $e) {
                $error = 'Review submission failed.';
            }
        }
    }
}

// Get user reviews
try {
    $stmt = $pdo->prepare("SELECT r.*, d.name as driver_name, b.pickup_location, b.dropoff_location
                          FROM reviews r 
                          JOIN drivers d ON r.driver_id = d.id
                          JOIN bookings b ON r.booking_id = b.id
                          WHERE r.user_id = ? 
                          ORDER BY r.created_at DESC");
    $stmt->execute([$user_id]);
    $reviews = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Error loading reviews.";
}

include 'includes/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <h2><i class="fas fa-star"></i> My Reviews</h2>
        <p class="text-muted">Rate your rides and provide feedback</p>
    </div>
</div>

<?php if($error): ?>
    <div class="alert alert-danger"><?php echo escape_output($error); ?></div>
<?php endif; ?>

<?php if($success): ?>
    <div class="alert alert-success"><?php echo escape_output($success); ?></div>
<?php endif; ?>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 text-white">Submit Review</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <!-- ✅ CSRF Token -->
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="mb-3">
                        <label for="booking_id" class="form-label">Select Completed Ride</label>
                        <select class="form-control" id="booking_id" name="booking_id" required>
                            <option value="">Choose a ride...</option>
                            <?php foreach($bookings as $booking): ?>
                                <option value="<?php echo $booking['id']; ?>">
                                    #<?php echo $booking['id']; ?> - <?php echo escape_output($booking['driver_name']); ?>
                                    (<?php echo escape_output(substr($booking['pickup_location'], 0, 20)); ?>...)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="rating" class="form-label">Rating</label>
                        <select class="form-control" id="rating" name="rating" required>
                            <option value="">Select rating...</option>
                            <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                            <option value="4">⭐⭐⭐⭐ Good</option>
                            <option value="3">⭐⭐⭐ Average</option>
                            <option value="2">⭐⭐ Poor</option>
                            <option value="1">⭐ Terrible</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="comment" class="form-label">Comment</label>
                        <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit Review
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 text-white">My Reviews (<?php echo count($reviews); ?>)</h5>
            </div>
            <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                <?php if(count($reviews) > 0): ?>
                    <?php foreach($reviews as $review): ?>
                        <div class="review-card">
                            <div class="stars">
                                <?php for($i = 0; $i < $review['rating']; $i++): ?>
                                    <i class="fas fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="mb-1"><strong>Driver:</strong> <?php echo escape_output($review['driver_name']); ?></p>
                            <p class="mb-1 text-muted small">
                                <?php echo escape_output(substr($review['pickup_location'], 0, 25)); ?>... → 
                                <?php echo escape_output(substr($review['dropoff_location'], 0, 25)); ?>...
                            </p>
                            <p class="mb-1"><?php echo escape_output($review['comment']); ?></p>
                            <small class="text-muted"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted">No reviews yet. Complete a ride to leave a review!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
