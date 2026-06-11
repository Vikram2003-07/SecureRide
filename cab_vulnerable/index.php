<?php 
require_once 'includes/config.php';

// If already logged in, redirect to dashboard
if(isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

include 'includes/header.php'; 
?>

<div class="hero-section">
    <h1><i class="fas fa-taxi"></i> Welcome to QuickCab</h1>
    <p>Your reliable ride, anytime, anywhere</p>
    <a href="register.php" class="btn btn-dark btn-lg me-2">Get Started</a>
    <a href="login.php" class="btn btn-outline-dark btn-lg">Login</a>
</div>

<div class="row mt-5">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-clock fa-3x text-warning mb-3"></i>
                <h5 class="card-title">24/7 Availability</h5>
                <p class="card-text">Book a ride anytime, day or night. Our drivers are always ready to serve you.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-dollar-sign fa-3x text-warning mb-3"></i>
                <h5 class="card-title">Affordable Prices</h5>
                <p class="card-text">Transparent pricing with no hidden charges. Pay what you see.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-shield-alt fa-3x text-warning mb-3"></i>
                <h5 class="card-title">Safe & Secure</h5>
                <p class="card-text">Verified drivers and secure payment options for your peace of mind.</p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-5">
    <div class="col-12">
        <div class="vulnerability-warning">
            <i class="fas fa-exclamation-triangle"></i> 
            WARNING: This is the VULNERABLE version - For cybersecurity education only!
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <h3 class="text-center mb-4">How It Works</h3>
    </div>
    <div class="col-md-3 text-center">
        <div class="mb-3">
            <div class="rounded-circle bg-warning d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                <span class="fs-3 fw-bold text-dark">1</span>
            </div>
        </div>
        <h5>Create Account</h5>
        <p>Sign up in seconds with your email</p>
    </div>
    <div class="col-md-3 text-center">
        <div class="mb-3">
            <div class="rounded-circle bg-warning d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                <span class="fs-3 fw-bold text-dark">2</span>
            </div>
        </div>
        <h5>Choose Driver</h5>
        <p>Select from our verified drivers</p>
    </div>
    <div class="col-md-3 text-center">
        <div class="mb-3">
            <div class="rounded-circle bg-warning d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                <span class="fs-3 fw-bold text-dark">3</span>
            </div>
        </div>
        <h5>Book Ride</h5>
        <p>Enter pickup and destination</p>
    </div>
    <div class="col-md-3 text-center">
        <div class="mb-3">
            <div class="rounded-circle bg-warning d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                <span class="fs-3 fw-bold text-dark">4</span>
            </div>
        </div>
        <h5>Enjoy Ride</h5>
        <p>Reach your destination safely</p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
