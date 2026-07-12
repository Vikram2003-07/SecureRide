<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickCab - Book Your Ride</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-warning">
        <div class="container">
            <a class="navbar-brand text-dark fw-bold" href="<?php echo BASE_URL; ?>index.php">
                <i class="fas fa-taxi"></i> QuickCab
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="<?php echo BASE_URL; ?>dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="<?php echo BASE_URL; ?>booking.php">Book Ride</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="<?php echo BASE_URL; ?>history.php">My Rides</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="<?php echo BASE_URL; ?>profile.php">Profile</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="<?php echo BASE_URL; ?>logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="<?php echo BASE_URL; ?>login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="<?php echo BASE_URL; ?>register.php">Register</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="<?php echo BASE_URL; ?>admin/login.php">Admin</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
