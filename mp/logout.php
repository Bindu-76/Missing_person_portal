<?php
session_start();

// Clear session data
$_SESSION = [];
session_destroy();

// Redirect to user login page
header("Location: login.php");
exit;
?>
