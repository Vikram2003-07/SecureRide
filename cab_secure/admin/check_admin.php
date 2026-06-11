<?php
error_reporting(0);
// QUICK DIAGNOSTIC - Check what's in the admins table
require_once '../includes/config.php';

echo "<h2>Admins Table Check</h2>";

try {
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'admins'");
    if($stmt->rowCount() == 0) {
        echo "<p style='color: red;'>❌ Table 'admins' does NOT exist!</p>";
        echo "<p>Creating table now...</p>";
        
        $pdo->exec("CREATE TABLE admins (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        echo "<p style='color: green;'>✅ Table created!</p>";
    } else {
        echo "<p style='color: green;'>✅ Table 'admins' exists</p>";
    }
    
    // Show all admins
    $stmt = $pdo->query("SELECT id, username, created_at FROM admins");
    $admins = $stmt->fetchAll();
    
    if(count($admins) > 0) {
        echo "<p>Found " . count($admins) . " admin(s):</p>";
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Username</th><th>Created</th></tr>";
        foreach($admins as $admin) {
            echo "<tr>";
            echo "<td>" . $admin['id'] . "</td>";
            echo "<td>" . htmlspecialchars($admin['username']) . "</td>";
            echo "<td>" . $admin['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No admins found in table!</p>";
        echo "<p>Creating default admin now...</p>";
        
        $hashed = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
        $stmt->execute(['admin', $hashed]);
        
        echo "<p style='color: green;'>✅ Default admin created!</p>";
        echo "<p><strong>Username:</strong> admin<br><strong>Password:</strong> admin123</p>";
    }
    
    echo "<hr>";
    echo "<p><a href='login.php'>Go to Admin Login</a></p>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
