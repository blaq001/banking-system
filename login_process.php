<?php
session_start();
// Include the database connection file
include './connect.php';

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Function to validate email format
function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    // Get the email and password from the form
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Validate email and password
    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "Email and password are required.";
        header("Location: login.php");
        exit();
    } elseif (!isValidEmail($email)) {
        $_SESSION['login_error'] = "Invalid email format.";
        header("Location: login.php");
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

        if ($email && $user['password'] === $password) {
            // User found and password matches, set user data in the session
            $_SESSION["user_id"] = $user['id'];
            // ... (previous code)
            header("Location: index.php");
            exit();
        } else {
            // Invalid credentials, show an error message
            $_SESSION['login_error'] = "Invalid email or password. Please try again.";
            // Redirect back to the login page
            header("Location: login.php");
            exit();
        }
    } else {
        // Error occurred while executing the SQL statement
        $_SESSION['login_error'] = "An error occurred while processing your request. Please try again later.";
        header("Location: login.php");
        exit();
    }
}

// // If the user is already logged in, redirect them to the dashboard page
// if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
//     header("Location: index.php");
//     exit();
// }


// if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
//     // Login form submitted
//     $email = $_POST["email"];
//     $password = $_POST["password"];

//     // Prepare the SQL statement with a placeholder for email
//     $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";

//     // Use prepared statements to prevent SQL injection
//     $stmt = $pdo->prepare($sql);
//     $stmt->bindParam(':email', $email);

//     // Execute the prepared statement
//     if ($stmt->execute()) {
//         $user = $stmt->fetch(PDO::FETCH_ASSOC);

//         if ($user && $user['password'] === $password) {
//             // User found and password matches, set user data in the session
//             $_SESSION["user_id"] = $user['id'];
//             $_SESSION["email"] = $user['email'];
//             $_SESSION["fname"] = $user['fname'];
//             $_SESSION["lname"] = $user['lname'];
//             $_SESSION["acctype"] = $user['acctype'];
//             $_SESSION["accnum"] = $user['accnum'];
//             $_SESSION["gender"] = $user['gender'];
//             $_SESSION["dob"] = $user['dob'];
//             $_SESSION["phone"] = $user['phone'];
//             $_SESSION["address"] = $user['address'];
//             $_SESSION["country"] = $user['country'];
//             $_SESSION["state"] = $user['state'];
//             $_SESSION["city"] = $user['city'];
//             $_SESSION["zipcode"] = $user['zipcode'];
//             $_SESSION["rtn"] = $user['rtn'];
//             $_SESSION["pin"] = $user['pin'];
//             $_SESSION["ssn"] = $user['ssn'];
            
        
//             // Redirect the user to the dashboard or any other page after successful login
//             header("Location: index.php");
//             exit();
//         }
//         } else {
//             // Invalid credentials, show an error message or redirect back to the login page with an error flag
//             header("Location: login.php");
//             exit();
//         }

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
}
?>
