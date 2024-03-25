<?php
session_start();
// Include the database connection file
include './connect.php';

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    // Get the email and password from the form
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Validate email and password
    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "Email and password are required.";
        header("Location: index.php");
        exit();
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['login_error'] = "Invalid email format.";
        header("Location: index.php");
        exit();
    }

    // Prepare the SQL statement with a placeholder for email
    $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";

    // Use prepared statements to prevent SQL injection
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);

    // Execute the prepared statement
    if ($stmt->execute()) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && $user['password'] === $password) {
            // User found and password matches, set user data in the session
            $_SESSION["user_id"] = $user['id'];
            $_SESSION["user_id"] = $user['id'];
            $_SESSION["email"] = $user['email'];
            $_SESSION["fname"] = $user['fname'];
            $_SESSION["lname"] = $user['lname'];
            $_SESSION["acctype"] = $user['acctype'];
            $_SESSION["accnum"] = $user['accnum'];
            $_SESSION["gender"] = $user['gender'];
            $_SESSION["dob"] = $user['dob'];
            $_SESSION["phone"] = $user['phone'];
            $_SESSION["address"] = $user['address'];
            $_SESSION["country"] = $user['country'];
            $_SESSION["state"] = $user['state'];
            $_SESSION["city"] = $user['city'];
            $_SESSION["zipcode"] = $user['zipcode'];
            $_SESSION["rtn"] = $user['rtn'];
            $_SESSION["pin"] = $user['pin'];
            $_SESSION["ssn"] = $user['ssn'];
            // ... (previous code)
            header("Location: dashboard.php");
            exit();
        } else {
            // Invalid credentials, show an error message
            $_SESSION['login_error'] = "Invalid email or password. Please try again.";
            // Redirect back to the login page
            header("Location: index.php");
            exit();
        }
    } else {
        // Error occurred while executing the SQL statement
        $_SESSION['login_error'] = "An error occurred while processing your request. Please try again later.";
        header("Location: index.php");
        exit();
    }
}


        if (!isset($_SESSION['balance'])) {
            // Query to fetch the user's balance from the 'users' table based on the account number
            $query = "SELECT balance FROM users WHERE accnum = :accnum";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':accnum', $_SESSION['accnum'], PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
            if ($result) {
                // Assign the fetched balance to the session variable
                $_SESSION['balance'] = $result['balance'];
            } else {
                // Handle the case when balance is not available or account number is not found in the database
                $_SESSION['balance'] = 'N/A';
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
    <title>Bank of the West</title>
</head>
<body>
      <div class="container">
           <div class="logo-img">
         <img src="./auth/img/new.png">
         <!--<p class="small">We are here for you.</p>-->
          </div>
             <!--<div class="bg-img"></div>-->
          <!--<img class="bg-img" src="./auth/img/man.jpg">-->
        <div class="box form-box">
            <header>Login</header>
            <!-- Add the following PHP code to display the error message -->
            <?php if (isset($_SESSION['login_error']) && !empty($_SESSION['login_error'])): ?>
                <div class="error-message">
                    <?php echo $_SESSION['login_error']; ?>
                </div>
                <?php
                // Clear the login error message after displaying it
                unset($_SESSION['login_error']);
                ?>
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