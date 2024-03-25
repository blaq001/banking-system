<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include './connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {
    // Registration form submitted

    // Get the form data from $_POST
    $email = $_POST['email'];
    $password = $_POST['password'];
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $country = $_POST['country'];
    $state = $_POST['state'];
    $city = $_POST['city'];
    $zipcode = $_POST['zipcode'];
    $accnum = $_POST['accnum'];
    $rtn = $_POST['rtn'];
    $pin = $_POST['pin']; // For demonstration, storing the PIN in plain text (Not recommended in production)
    $ssn = $_POST['ssn'];
    $acctype = $_POST['acctype'];


    //account number generation...
    $accnum ='29176573827673930057000463891234567890';
    $accnum = str_shuffle($accnum);
    $accnum = substr($accnum,0, 10);

    // Set initial balance
    $balance = ''; // You can set a default value here or retrieve it from the form if you have a balance input field.

    // Prepare the SQL statement
    $sql = "INSERT INTO users (email, password, fname, lname, gender, dob, phone, balance, address, country, state, city, zipcode, accnum, rtn, pin, ssn, acctype) 
            VALUES (:email, :password, :fname, :lname, :gender, :dob, :phone, :balance, :address, :country, :state, :city, :zipcode, :accnum, :rtn, :pin, :ssn, :acctype)";

    // Use prepared statements to prevent SQL injection
    $stmt = $pdo->prepare($sql);

    // Bind the parameters
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':fname', $fname);
    $stmt->bindParam(':lname', $lname);
    $stmt->bindParam(':gender', $gender);
    $stmt->bindParam(':dob', $dob);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':balance', $balance);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':country', $country);
    $stmt->bindParam(':state', $state);
    $stmt->bindParam(':city', $city);
    $stmt->bindParam(':zipcode', $zipcode);
    $stmt->bindParam(':accnum', $accnum);
    $stmt->bindParam(':rtn', $rtn);
    $stmt->bindParam(':pin', $pin);
    $stmt->bindParam(':ssn', $ssn);
    $stmt->bindParam(':acctype', $acctype);

    // Execute the prepared statement
    if ($stmt->execute()) {
        // Redirect the user to a registration success page or any other page
        header("Location: index.php");
        exit();
    } else {
        // Handle the case where the insert operation fails (e.g., display an error message)
        header("Location: registration_failed.php");
        exit();
    }
}
?>
