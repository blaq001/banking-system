<?php
session_start();
// Include the database connection file
include '../connect.php';

// Check if the form was submitted
if (isset($_POST['login'])) {
    // Retrieve the form data
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Perform validation on the form data (you can add more validation based on your requirements)
    // For simplicity, we'll assume the admin username is 'admin' and password is 'password'
    if ($email === 'admin' && $password === 'password') {
        // If the credentials are correct, set a session variable to mark the admin as logged in
        $_SESSION['admin_logged_in'] = true;
        // Redirect the admin to the internal_transfer.php page or any other admin page
        header("Location: dashboard.php");
        exit();
    } else {
        // If the credentials are incorrect, set an error message
        $loginError = "Invalid email or password. Please try again.";
    }
}
?>