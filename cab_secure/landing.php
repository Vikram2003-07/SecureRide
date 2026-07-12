<?php error_reporting(0); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickCab - Choose Your Version</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .version-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            transition: transform 0.3s;
        }
        .version-card:hover {
            transform: translateY(-5px);
        }
        .vulnerable-card {
            border-top: 5px solid #dc3545;
        }
        .secure-card {
            border-top: 5px solid #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center text-white mb-5">
            <h1 class="display-4"><i class="fas fa-taxi"></i> QuickCab Platform</h1>
            <p class="lead">Choose Your Demo Version</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6">
                <div class="version-card vulnerable-card">
                    <div class="text-center mb-4">
                        <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                    </div>
                    <h3 class="text-center text-danger">Vulnerable Version</h3>
                    <p class="text-center text-muted mb-4">Demonstrates security vulnerabilities</p>
                    <ul class="list-unstyled mb-4">
                        <li><i class="fas fa-times text-danger me-2"></i> SQL Injection</li>
                        <li><i class="fas fa-times text-danger me-2"></i> IDOR Vulnerability</li>
                        <li><i class="fas fa-times text-danger me-2"></i> XSS Attacks</li>
                        <li><i class="fas fa-times text-danger me-2"></i> Price Manipulation</li>
                        <li><i class="fas fa-times text-danger me-2"></i> Weak Authentication</li>
                    </ul>
                    <a href="../cab_vulnerable/index.php" class="btn btn-danger btn-lg w-100">
                        <i class="fas fa-bug me-2"></i> View Vulnerable Version
                    </a>
                    <p class="text-center mt-3 small text-muted">⚠️ Educational purposes only</p>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="version-card secure-card">
                    <div class="text-center mb-4">
                        <i class="fas fa-shield-alt fa-3x text-success"></i>
                    </div>
                    <h3 class="text-center text-success">Secure Version</h3>
                    <p class="text-center text-muted mb-4">Implements security best practices</p>
                    <ul class="list-unstyled mb-4">
                        <li><i class="fas fa-check text-success me-2"></i> Prepared Statements</li>
                        <li><i class="fas fa-check text-success me-2"></i> Authorization Checks</li>
                        <li><i class="fas fa-check text-success me-2"></i> XSS Protection</li>
                        <li><i class="fas fa-check text-success me-2"></i> Server Validation</li>
                        <li><i class="fas fa-check text-success me-2"></i> Strong Authentication</li>
                    </ul>
                    <a href="login.php" class="btn btn-success btn-lg w-100">
                        <i class="fas fa-lock me-2"></i> View Secure Version
                    </a>
                    <p class="text-center mt-3 small text-success">✅ Production-ready security</p>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-5 text-white">
            <p><i class="fas fa-graduation-cap me-2"></i> Cybersecurity Educational Project</p>
        </div>
    </div>
</body>
</html>
