<?php

require_once 'includes/db_connect.php';

// Clear all session data 
session_unset();

// Destroy the session on the server 
session_destroy();

//  Delete the session cookie from the user's browser 
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}


session_start();
$_SESSION['message'] = "You have been logged out successfully.";
$_SESSION['message_type'] = "success";

header("Location: " . BASE_URL . "login.php");
exit();
?>
