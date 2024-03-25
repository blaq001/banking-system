<?php
session_start();

// Unset all session variables
session_unset();

// Destroy all session data
session_destroy();

// Redirect the user to the login page or any other page you prefer
header("Location: index.php");
exit;
?>


