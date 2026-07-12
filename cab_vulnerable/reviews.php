<?php 
require_once 'includes/config.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';
$edit_review = null;

// ⚠️ VULNERABILITY: IDOR - Edit ANY review by changing URL parameter
if(isset($_GET['edit'])) {
    $review_id = $_GET['edit']; // ⚠️ No validation of ownership!
    
    // ⚠️ VULNERABILITY: SQL Injection + No authorization check
    $edit_query = "SELECT r.*, d.name as driver_name, b.pickup_location, b.dropoff_location 
                   FROM reviews r 
                   JOIN drivers d ON r.driver_id = d.id 
                   JOIN bookings b ON r.booking_id = b.id
                   WHERE r.id=$review_id"; // Anyone can edit any review!
    $edit_result = mysqli_query($conn, $edit_query);
    $edit_review = mysqli_fetch_assoc($edit_result);
}

// ⚠️ VULNERABILITY: Update review without ownership check
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_review'])) {
    $review_id = $_POST['review_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    
    // ⚠️ VULNERABILITY: No check if this review belongs to current user!
    $update_query = "UPDATE reviews SET rating='$rating', comment='$comment' WHERE id=$review_id";
    
    if(mysqli_query($conn, $update_query)) {
        $success = 'Review updated successfully!';
        $edit_review = null;
        header('Location: reviews.php');
        exit();
    } else {
        $error = 'Update failed!';
    }
}

// Get user's reviews
$query = "SELECT r.*, d.name as driver_name, b.pickup_location, b.dropoff_location 
          FROM reviews r 
          JOIN drivers d ON r.driver_id = d.id 
          JOIN bookings b ON r.booking_id = b.id
          WHERE r.user_id=$user_id 
          ORDER BY r.created_at DESC";
$result = mysqli_query($conn, $query);

// ⚠️ VULNERABILITY: Stored XSS in reviews
// Comment input is not sanitized and displayed without escaping
if($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['update_review'])) {
    $booking_id = $_POST['booking_id'];
    $driver_id = $_POST['driver_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment']; // ⚠️ No sanitization!
    
    // ⚠️ VULNERABILITY: SQL Injection
    $insert_query = "INSERT INTO reviews (booking_id, user_id, driver_id, rating, comment) 
                     VALUES ($booking_id, $user_id, $driver_id, $rating, '$comment')";
    
    if(mysqli_query($conn, $insert_query)) {
        $success = 'Review submitted successfully!';
        
        // Update driver rating (simple average)
        $avg_query = "SELECT AVG(rating) as avg_rating FROM reviews WHERE driver_id=$driver_id";
        $avg_result = mysqli_query($conn, $avg_query);
        $avg_rating = mysqli_fetch_assoc($avg_result)['avg_rating'];
        
        $update_driver = "UPDATE drivers SET rating='$avg_rating' WHERE id=$driver_id";
        mysqli_query($conn, $update_driver);
    }
}

// Get completed bookings without reviews
$pending_reviews_query = "SELECT b.*, d.name as driver_name, d.id as driver_id 
                          FROM bookings b 
                          JOIN drivers d ON b.driver_id = d.id 
                          LEFT JOIN reviews r ON b.id = r.booking_id 
                          WHERE b.user_id=$user_id AND b.status='completed' AND r.id IS NULL";
$pending_result = mysqli_query($conn, $pending_reviews_query);

include 'includes/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <h2><i class="fas fa-star"></i> Reviews & Ratings</h2>
        <p class="text-muted">Rate your ride experience and help other users</p>
    </div>
</div>

<div class="alert alert-danger">
    <strong><i class="fas fa-exclamation-triangle"></i> IDOR Vulnerability:</strong> 
    Try editing someone else's review! Change the <code>?edit=X</code> ID in the URL to edit any review!
</div>

<div class="alert alert-danger">
    <strong><i class="fas fa-exclamation-triangle"></i> XSS Vulnerability:</strong> 
    Try injecting JavaScript in your review comment: <code>&lt;script&gt;alert('XSS')&lt;/script&gt;</code>
</div>

<?php if($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if($edit_review): ?>
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-edit"></i> Edit Review #<?php echo $_GET['edit']; ?></h5>
                <small>⚠️ No ownership validation - You can edit ANY review!</small>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="review_id" value="<?php echo $_GET['edit']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Driver: <?php echo $edit_review['driver_name']; ?></label>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Rating</label>
                        <select class="form-control" name="rating" required>
                            <option value="5" <?php if($edit_review['rating']==5) echo 'selected'; ?>>5 Stars - Excellent</option>
                            <option value="4" <?php if($edit_review['rating']==4) echo 'selected'; ?>>4 Stars - Good</option>
                            <option value="3" <?php if($edit_review['rating']==3) echo 'selected'; ?>>3 Stars - Average</option>
                            <option value="2" <?php if($edit_review['rating']==2) echo 'selected'; ?>>2 Stars - Poor</option>
                            <option value="1" <?php if($edit_review['rating']==1) echo 'selected'; ?>>1 Star - Very Bad</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Comment</label>
                        <textarea class="form-control" name="comment" rows="3" required><?php echo $edit_review['comment']; ?></textarea>
                        <small class="text-danger">⚠️ Not sanitized - Try XSS!</small>
                    </div>
                    
                    <button type="submit" name="update_review" class="btn btn-danger">
                        <i class="fas fa-save"></i> Update Review
                    </button>
                    <a href="reviews.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if(mysqli_num_rows($pending_result) > 0): ?>
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 text-white">Pending Reviews</h5>
            </div>
            <div class="card-body">
                <?php while($pending = mysqli_fetch_assoc($pending_result)): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h6>Booking #<?php echo $pending['id']; ?> - <?php echo $pending['driver_name']; ?></h6>
                                <p class="text-muted mb-0">
                                    <small>
                                        <?php echo $pending['pickup_location']; ?> → <?php echo $pending['dropoff_location']; ?>
                                    </small>
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <button class="btn btn-warning btn-sm" data-bs-toggle="collapse" 
                                        data-bs-target="#review<?php echo $pending['id']; ?>">
                                    <i class="fas fa-plus"></i> Add Review
                                </button>
                            </div>
                        </div>
                        <div class="collapse mt-3" id="review<?php echo $pending['id']; ?>">
                            <form method="POST" action="">
                                <input type="hidden" name="booking_id" value="<?php echo $pending['id']; ?>">
                                <input type="hidden" name="driver_id" value="<?php echo $pending['driver_id']; ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label">Rating</label>
                                    <select class="form-control" name="rating" required>
                                        <option value="5">5 Stars - Excellent</option>
                                        <option value="4">4 Stars - Good</option>
                                        <option value="3">3 Stars - Average</option>
                                        <option value="2">2 Stars - Poor</option>
                                        <option value="1">1 Star - Very Bad</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Comment</label>
                                    <textarea class="form-control" name="comment" rows="3" 
                                              placeholder="Share your experience..." required></textarea>
                                    <small class="text-danger">⚠️ Not sanitized - Try XSS!</small>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Submit Review
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 text-white">My Reviews</h5>
            </div>
            <div class="card-body">
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <?php while($review = mysqli_fetch_assoc($result)): ?>
                    <div class="review-card">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6><?php echo $review['driver_name']; ?></h6>
                                <div class="stars">
                                    <?php 
                                    for($i = 1; $i <= 5; $i++) {
                                        echo $i <= $review['rating'] ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="text-end">
                                <small class="text-muted"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                                <br>
                                <a href="?edit=<?php echo $review['id']; ?>" class="btn btn-sm btn-warning mt-1">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </div>
                        </div>
                        <p class="mt-2 mb-0">
                            <!-- ⚠️ VULNERABILITY: XSS - Comment displayed without escaping -->
                            <?php echo $review['comment']; ?>
                        </p>
                        <small class="text-muted">
                            Trip: <?php echo substr($review['pickup_location'], 0, 30); ?>... → 
                            <?php echo substr($review['dropoff_location'], 0, 30); ?>...
                        </small>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-center text-muted">No reviews yet. Complete a ride to leave a review!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
