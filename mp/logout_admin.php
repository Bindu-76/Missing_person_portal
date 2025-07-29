<?php
session_start();

// Destroy all session data
$_SESSION = [];
session_destroy();

// Redirect to admin login page
header("Location: admin_login.php");
exit;
?>
