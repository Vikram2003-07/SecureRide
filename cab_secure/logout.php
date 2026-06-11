<?php
error_reporting(0);
// ✅ SECURITY: Proper logout with session destruction
session_start();
session_unset();
session_destroy();
header('Location: login.php');
exit();
?>
