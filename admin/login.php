<?php
session_start();
// Include the database connection file
include './connect.php';

// Check if the form was submitted
if (isset($_POST['login'])) {
    // Retrieve the form data
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Perform validation on the form data (you can add more validation based on your requirements)
    // For simplicity, we'll assume the admin username is 'admin' and password is 'password'
    if ($email === 'admin@mail.com' && $password === '12345') {
        // If the credentials are correct, set a session variable to mark the admin as logged in
        $_SESSION['admin_logged_in'] = true;
        // Redirect the admin to the internal_transfer.php page or any other admin page
        header("Location: admin.php");
        exit();
    } else {
        // If the credentials are incorrect, set an error message
        $loginError = "Invalid email or password. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="auth/style.css">
    <title>Login</title>
</head>
<body>
      <div class="container">
        <div class="box form-box">
            <header>Login</header>
            <?php if (isset($loginError)) : ?>
        <p><?php echo $loginError; ?></p>
    <?php endif; ?>
            <form action="" method="post">
                <div class="field input">
                    <label>Email</label>
                    <input type="text" name="email" id="email" autocomplete="off" required>
                </div>

                <div class="field input">
                    <label>Password</label>
                    <input type="password" name="password" id="password" autocomplete="off" required>
                </div>

                <div class="field">
                    
                    <input type="submit" class="btn" name="login" value="Login" required>
                </div>
                <!--<div class="links">-->
                <!--    Don't have account? <a href="register.php">Sign Up Now</a>-->
                <!--</div>-->
            </form>
        </div>
      </div>
</body>
</html>