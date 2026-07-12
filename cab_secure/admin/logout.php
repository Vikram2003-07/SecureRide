<?php
error_reporting(0);
session_name('SECURE_ADMIN_SESSION');
session_start();
session_unset();
session_destroy();
header('Location: login.php');
exit();
?>
