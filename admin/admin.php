<?php
session_start();
// Include the database connection file
include '../connect.php';

    // Initialize the update status and message variables
    $updateStatus = false;
    $updateMessage = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
  $email = $_POST['email'];
  $password = $_POST['password'];

  // After executing the query, add the following code to check for errors
if (!$stmt) {
    die('Error: ' . $pdo->errorInfo()[2]); // Display the error message
}

// Convert balance to a numeric value
// $balance = floatval($_POST['balance']);

  // Fetch admin data from the admin table based on the provided email
  $query = "SELECT * FROM admin WHERE email = :email";
  $stmt = $pdo->prepare($query);
  $stmt->bindParam(':email', $email, PDO::PARAM_STR);
  $stmt->execute();
  $adminData = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($adminData && password_verify($password, $adminData['password'])) {
      // Admin login successful, set session variables or redirect to admin dashboard
      // ...
  } else {
      // Admin login failed, show error message
      $loginError = "Invalid email or password.";
  }

  // Function to generate a 6-digit OTP
function generateOTP()
{
    return str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Retrieve the user's account number from the request (you can pass this as a parameter to this script)
$accountNumber = $_POST['account_number'] ?? '';

if (!empty($accountNumber)) {
    // Generate the OTP
    $otp = generateOTP();

    // Store the OTP in the database
    $query = "INSERT INTO otp_table (account_number, otp) VALUES (:account_number, :otp)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':account_number', $accountNumber, PDO::PARAM_STR);
    $stmt->bindParam(':otp', $otp, PDO::PARAM_STR);
    $stmt->execute();

    // Respond with the generated OTP
    echo $otp;
}
}

// Fetch all registered users' details from the database
$query = "SELECT * FROM users";
$stmt = $pdo->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Update user details if form submitted
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_user'])) {
    // Perform validation and update user details
    $userId = $_POST['user_id'];
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $address = $_POST['address'];
    $country = $_POST['country'];
    $state = $_POST['state'];
    $city = $_POST['city'];
    $zipcode = $_POST['zipcode'];
    $acctype = $_POST['acctype'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $ssn = $_POST['ssn'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $pin = $_POST['pin'];
    $rtn = $_POST['rtn'];
    $accnum = $_POST['accnum'];
    // $balance = $_POST['balance'];
    // Add other user details to update here
    // ...

    // Fetch the user's existing balance from the database
    // $query = "SELECT balance FROM users WHERE id = :user_id";
    // $stmt = $pdo->prepare($query);
    // $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    // $stmt->execute();
    // $existingBalance = $stmt->fetchColumn();

    // Convert balance to a numeric value if provided or retain the existing balance if empty
    // $balance = !empty($_POST['balance']) ? floatval($_POST['balance']) : null;


    // Perform the update query based on the provided user ID
    $updateQuery = "UPDATE users SET fname = :fname, lname = :lname, accnum = :accnum, rtn = :rtn, pin = :pin, dob = :dob, 
    gender = :gender, ssn = :ssn, email = :email, phone = :phone, acctype = :acctype, zipcode = :zipcode, city = :city, state = :state, country = :country, address = :address WHERE id = :user_id";
    $updateStmt = $pdo->prepare($updateQuery);
    $updateStmt->bindParam(':fname', $fname, PDO::PARAM_STR);
    $updateStmt->bindParam(':lname', $lname, PDO::PARAM_STR);
    $updateStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $updateStmt->bindParam(':accnum', $accnum, PDO::PARAM_STR);
    // $updateStmt->bindParam(':balance', $balance, PDO::PARAM_STR);
    $updateStmt->bindParam(':rtn', $rtn, PDO::PARAM_STR);
    $updateStmt->bindParam(':pin', $pin, PDO::PARAM_STR);
    $updateStmt->bindParam(':dob', $dob, PDO::PARAM_STR);
    $updateStmt->bindParam(':gender', $gender, PDO::PARAM_STR);
    $updateStmt->bindParam(':ssn', $ssn, PDO::PARAM_STR);
    $updateStmt->bindParam(':email', $email, PDO::PARAM_STR);
    $updateStmt->bindParam(':phone', $phone, PDO::PARAM_STR);
    $updateStmt->bindParam(':acctype', $acctype, PDO::PARAM_STR);
    $updateStmt->bindParam(':zipcode', $zipcode, PDO::PARAM_STR);
    $updateStmt->bindParam(':city', $city, PDO::PARAM_STR);
    $updateStmt->bindParam(':state', $state, PDO::PARAM_STR);
    $updateStmt->bindParam(':country', $country, PDO::PARAM_STR);
    $updateStmt->bindParam(':address', $address, PDO::PARAM_STR);

     // Execute the update query
     if ($updateStmt->execute()) {
      // Update successful
      $updateStatus = true;
      $updateMessage = "User details updated successfully.";
      // Redirect back to the dashboard after successful update
      header("Location: admin.php");
      exit();
  } else {
      // Handle update error if needed
      $updateError = "Failed to update user details.";
  }
  // Debug: Check the value of $updateStatus
var_dump($updateStatus);
}


// Delete user if form submitted
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_user'])) {
    $userId = $_POST['user_id'];

    // Perform the delete query based on the provided user ID
    $deleteQuery = "DELETE FROM users WHERE id = :user_id";
    $deleteStmt = $pdo->prepare($deleteQuery);
    $deleteStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

    if ($deleteStmt->execute()) {
        // Redirect back to the dashboard after successful deletion
        header("Location: dashboard.php");
        exit();
    } else {
        // Handle delete error if needed
        $deleteError = "Failed to delete user.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>

    <link
      href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp"
      rel="stylesheet"
    />
    <!-- <link rel="stylesheet" href="../style.css" /> -->

    <style>
/* DECLARATIONS */
@import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap");

/* ROOT VARIABLES */
:root {
  --color-primary: #7380ec;
  --color-danger: #ff7782;
  --color-success: #41f1b6;
  --color-warning: #ffbb55;
  --color-white: #fff;
  --color-info-dark: #7d8da1;
  --color-info-light: #dce1eb;
  --color-dark: #363949;
  --color-light: rgba(132, 139, 200, 0.18);
  --color-primary-variant: #111e88;
  --color-dark-variant: #677483;
  --color-background: #f6f6f9;

  --card-border-radius: 2rem;
  --border-radius-1: 0.4rem;
  --border-radius-2: 0.8rem;
  --border-radius-3: 1.2rem;

  --card-padding: 1.8rem;
  --padding-1: 1.2rem;

  --box-shadow: 0 2rem 3rem var(--color-light);
}

/* DARK THEME VARIABLES */
.dark-theme-variables {
  --color-background: #181a1e;
  --color-white: #202528;
  --color-dark: #edeffd;
  --color-dark-variant: #a3bdcc;
  --color-light: rgba(0, 0, 0, 0.4);
  --box-shadow: 0 2rem 3rem var(--color-light);
}

/* STYLES */

/* Custom CSS for user list table */

/* Horizontal scrolling for the user list table */
.scroll {
        /* margin: 4px, 4px;
        padding: 4px;
        background-color: #08c708; */
        width: 1200px;
        overflow-x: auto;
        overflow-y: hidden;
        white-space: nowrap;
    }

table {
  width: 100%;
  border-collapse: collapse;
}

th,
td {
  padding: 10px;
  border-bottom: 1px solid #ddd;
}

th {
  background-color: #f2f2f2;
}

td:last-child {
  white-space: nowrap;
}

/* Style for update and delete buttons */
.action-buttons {
  display: flex;
}

.action-buttons button {
  margin-right: 5px;
  padding: 5px 10px;
  background-color: #007bff;
  color: #fff;
  border: none;
  cursor: pointer;
  border-radius: 5px;
}

.action-buttons button.delete {
  background-color: #dc3545;
}

* {
  margin: 0;
  padding: 0;
  outline: 0;
  appearance: none;
  border: 0;
  text-decoration: none;
  list-style: none;
  box-sizing: border-box;
}
html {
  font-size: 14px;
}
body {
  width: 100vw;
  height: 100vh;
  font-family: poppins, sans-serif;
  font-size: 0.88rem;
  background: var(--color-background);
  user-select: none;
  overflow-x: hidden;
  color: var(--color-dark);
}
.container {
  display: flex;
  width: 96%;
  align-items: stretch;
  justify-content: space-between;
  margin: 0 auto;
  gap: 1.8rem;
  grid-template-columns: 14rem auto 23rem;
}
a {
  color: var(--color-dark);
}
img {
  display: block;
  width: 100%;
}
h1 {
  font-weight: 800;
  font-size: 1.8rem;
}
h2 {
  font-size: 1.4rem;
}
h3 {
  font-size: 0.87rem;
}
h4 {
  font-size: 0.8rem;
}
h5 {
  font-family: 0.77rem;
}
small {
  font-size: 0.75rem;
}
.profile-photo {
  width: 2.8rem;
  height: 2.8rem;
  border-radius: 50%;
  overflow: hidden;
}
.text-muted {
  color: var(--color-info-dark);
}
p {
  color: var(--color-dark-variant);
}
b {
  color: var(--color-dark);
}
.primary {
  color: var(--color-primary);
}
.danger {
  color: var(--color-danger);
}
.success {
  color: var(--color-success);
}
.warning {
  color: var(--color-warning);
}

/* START ASIDE */
aside {
  height: 100vh;
  flex: 1 1 300px;
}
aside .top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 1.4rem;
}
aside .logo {
  display: flex;
  gap: 0.8rem;
}
aside .logo img {
  width: 2rem;
  height: 2rem;
}
aside .close {
  display: none;
}

/* START SIDEBAR */
aside .sidebar {
  display: flex;
  flex-direction: column;
  height: 86vh;
  position: relative;
  padding-bottom: 14px;
  top: 3rem;
}
aside h3 {
  font-weight: 500;
  font-size: 14px;
}
aside .sidebar a {
  display: flex;
  color: var(--color-info-dark);
  margin-left: 2rem;
  gap: 1rem;
  align-items: center;
  position: relative;
  height: 3.7rem;
  transition: all 300ms ease;
}
aside .sidebar a span {
  transition: all 300ms ease;
}
aside .sidebar a:last-child {
  position: absolute;
  bottom: 2rem;
  width: 100%;
}
aside .sidebar a.active {
  background: var(--color-light);
  color: var(--color-primary);
  margin-left: 0;
}
aside .sidebar a.active:before {
  content: "";
  width: 6px;
  height: 100%;
  background: var(--color-primary);
}
aside .sidebar a.active span {
  color: var(--color-primary);
  margin-left: calc(1rem - 6px);
}
aside .sidebar a:hover {
  color: var(--color-primary);
}
aside .sidebar a:hover span {
  margin-left: 1rem;
}
aside .sidebar .message-count {
  background: var(--color-danger);
  color: var(--color-white);
  padding: 2px 10px;
  font-size: 11px;
  border-radius: var(--border-radius-1);
}
/* END SIDEBAR */
/* END ASIDE */

/* START MAIN */
main {
  margin-top: 1.4rem;
  flex: content;
}

/* START INSIGHTS */
main .insights {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.8rem;
}
main .insights > div {
  background: var(--color-white);
  padding: var(--card-padding);
  border-radius: var(--card-border-radius);
  margin-top: 0.4rem;
  box-shadow: var(--box-shadow);
  transition: all 300ms ease;
}
main .insights > div:hover {
  cursor: pointer;
  box-shadow: none;
}
main .insights > div span {
  background: var(--color-primary);
  padding: 0.5rem;
  border-radius: 50%;
  color: var(--color-white);
  font-size: 2rem;
}
main .insights > div.expenses span {
  background: var(--color-danger);
}
main .insights > div.income span {
  background: var(--color-success);
}
main .insights > div .middle {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
main .insights h3 {
  margin: 1rem 0 0.6rem;
  font-size: 1rem;
}
main .insights .progress {
  position: relative;
  width: 92px;
  height: 92px;
  border-radius: 50%;
}
main .insights svg {
  width: 7rem;
  height: 7rem;
}
main .insights svg circle {
  fill: none;
  stroke: var(--color-primary);
  stroke-width: 14;
  stroke-linecap: round;
  transform: translate(5px, 5px);
  stroke-dasharray: 110;
  stroke-dashoffset: 92;
}
main .insights .sales svg circle {
  stroke-dashoffset: -30;
  stroke-dasharray: 200;
}
main .insights .expenses svg circle {
  stroke-dashoffset: 20;
  stroke-dasharray: 80;
}
main .insights .income svg circle {
  stroke-dashoffset: 35;
}
main .insights .progress .number {
  position: absolute;
  top: -2px;
  left: -2px;
  height: 100%;
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
}
main .insights small {
  margin-top: 1.6rem;
  display: block;
}
/* END INSIGHTS */

/* START RECENT ORDERS */
/* main .recent-orders {
  margin-top: 2rem;
}
main .recent-orders h2 {
  margin-bottom: 0.8rem;
}
main .recent-orders table {
  background: var(--color-white);
  width: 100%;
  border-radius: var(--card-border-radius);
  padding: var(--card-padding);
  text-align: center;
  box-shadow: var(--box-shadow);
  transition: all 300ms ease;
  border-collapse: separate;
	border-spacing: 20px;
}
main .recent-orders table:hover {
  cursor: pointer;
  box-shadow: none;
}
main table tbody td {
  height: 2.8rem;
  border-bottom: 1px solid var(--color-light);
  color: var(--color-dark-variant);
}
main table tbody tr:last-child td {
  border: 1;
}
main .recent-orders a {
  text-align: center;
  display: block;
  margin: 1rem auto;
  color: var(--color-primary);
} */
/* END RECENT ORDERS */

/* START RIGHT SECTION */
.right {
  margin-top: 1rem;
}
.top {
  display: flex;
  justify-content: end;
  gap: 2rem;
  padding-bottom: 10px;
}
.right .top button {
  display: none;
}
.right .theme-toggler {
  background: var(--color-light);
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 1.6rem;
  width: 4.2rem;
  cursor: pointer;
  border-radius: var(--border-radius-1);
}
.right .theme-toggler span {
  font-size: 1.2rem;
  width: 50%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}
.right .theme-toggler span.active {
  background: var(--color-primary);
  color: white;
  border-radius: var(--border-radius-1);
}

/* form */
main .transfers{
  margin-top: 4rem;
}
main .transfers h2 {
  margin-bottom: 0.8rem;
}
main .box{
  background: var(--color-white);
  display: flex;
  flex-direction: column;
  padding: 25px 25px;
  border-radius: var(--card-border-radius);
  box-shadow: var(--box-shadow);
}
.form-box{
  width: 95%;
  margin: 0px 10px;
}
.form-box header{
  font-size: 25px;
  font-weight: 600;
  padding-bottom: 10px;
  border-bottom: 1px solid #e6e6e6;
  margin-bottom: 10px;
}
.form {
  display: flex;
  margin-bottom: 10px;
  flex-direction: column;
  height: 40px;
  width: 100%;
  font-size: 16px;
  padding: 0 10px;
  border-radius: 5px;
  border: 1px solid #ccc;
  outline: none;
}
.form-box .field{
  display: flex;
  margin-bottom: 10px;
  flex-direction: column;

}
.form-box .input input{
  height: 40px;
  width: 100%;
  font-size: 16px;
  padding: 0 10px;
  border-radius: 5px;
  border: 1px solid #ccc;
  outline: none;
}
.field.input select option {
  font-size: 15px;
  background: rgb(255, 255, 255);
  color: #504e4e;
  border-radius: 10px;
  height: 40px;
}

.input-field{
  font-size: 15px;
  text-transform: capitalize;
  background: rgb(255, 255, 255);
  color: #504e4e;
  height: 40px;
  width: 100%;
  padding: 10px;
  /* padding: 0 10px 0 45px; */
  border: 1px solid #ccc;
  border-radius: 8px;
  outline: none;
  transition: .2s ease;
  margin-bottom: 10px;
}

.two-forms{
  display: flex;
  gap: 50px;
  width: 100%;
  margin-bottom: 20px;
}

.btn{
  height: 35px;
  background: rgba(76,68,182,0.808);
  border: 0;
  border-radius: 5px;
  color: #fff;
  font-size: 15px;
  cursor: pointer;
  transition: all .3s;
  margin-top: 10px;
  padding: 0px 10px;
}
.btn:hover{
  opacity: 0.82;
}
.submit{
  width: 100%;
}
.links{
  margin-bottom: 15px;
}

.highlight-row {
  background-color: var(--color-success); /* You can change the color to your preference */
}
.error-message{
	font-size: 12px;
	color: red;
}

/* Style for update and delete buttons */
.action-buttons {
  display: flex;
}

.action-buttons button {
  margin-right: 5px;
  padding: 5px 10px;
  background-color: #007bff;
  color: #fff;
  border: none;
  cursor: pointer;
  border-radius: 5px;
}

.action-buttons button.delete {
  background-color: #dc3545;
}

.success-message {
        background-color: #dff0d8;
        color: #3c763d;
        border: 1px solid #d6e9c6;
        padding: 10px;
        margin-bottom: 20px;
        border-radius: 5px;
    }
/* START MEDIA QUERIES */
/* TABLETS AND SMALL LAPTOPS */
@media screen and (max-width: 1200px) {
  .container {
    width: 84%;
    grid-template-columns: 7rem auto 15rem;
  }
  .scroll{
    width: 500px;
    overflow-x: auto;
        overflow-y: hidden;
        white-space: nowrap;
  }
  aside .logo h2 {
    display: none;
  }
  aside .sidebar h3 {
    display: none;
  }
  aside .sidebar a {
    width: 5.6rem;
  }
  aside .sidebar a:last-child {
    position: relative;
    margin-top: 1.8rem;
  }
  main h1{
    right: 10px;
  }
  main .insights {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 10px;
    max-width: 450px; /* Set maximum width for the insights section */
  }
  /* main .recent-orders {
    width: 94%;
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    margin: 2rem 0 0 8.8rem;
  } */
  main .recent-orders {
    width: 100%; /* Set full width for the recent-orders section */
    margin: 2rem auto; /* Center the recent-orders section */
    gap: 1.6rem;
    max-width: 600px; /* Set maximum width for the table */
  }
  main .recent-orders table {
    width: 100%;
  }
  main .recent-orders table thead tr th:first-child,
  main .recent-orders table thead tr th:last-child {
    display: none;
  }
  main .recent-orders table tbody tr td:first-child,
  main .recent-orders table tbody tr td:last-child {
    display: none;
  }
}

/* SMALL TABLETS AND MOBILE */
@media screen and (max-width: 768px) {
  .container {
    width: 100%;
    grid-template-columns: 1fr;
  }

  .scroll {
  width: 500px; 
    overflow-x: auto;
        overflow-y: hidden;
        white-space: nowrap;
  }
  aside {
    position: fixed;
    left: -100%;
    background: var(--color-white);
    width: 18rem;
    z-index: 3;
    box-shadow: 1rem 3rem 4rem var(--color-light);
    height: 100vh;
    padding-right: var(--card-padding);
    display: none;
    animation: showMenu 400ms ease forwards;
  }
  @keyframes showMenu {
    to {
      left: 0;
    }
  }
  aside .logo {
    margin-left: 1rem;
  }
  aside .logo h2 {
    display: inline;
  }
  aside .sidebar h3 {
    display: inline;
  }
  aside .sidebar a {
    width: 100%;
    height: 3.4rem;
  }
  aside .sidebar a:last-child {
    position: absolute;
    bottom: 5rem;
  }
  aside .close {
    display: inline-block;
    cursor: pointer;
  }
  main {
    margin-top: 8rem;
    /* padding: 0 4rem; */
  }
  main .recent-orders {
    position: absolute;
    margin: 3rem 0 0 0;
    width: 100%;
  }
  main .recent-orders table {
    width: 100%;
    margin: 0;
    padding: 10px;
    border-collapse: separate;
	  white-space: nowrap;
  }
  .right {
    width: 94%;
    margin: 0 auto 4rem;
  }
  .right .top {
    position: fixed;
    top: 0;
    left: 0;
    align-items: center;
    padding: 0 0.8rem;
    height: 4.6rem;
    background: var(--color-white);
    width: 100%;
    margin: 0;
    z-index: 2;
    box-shadow: 0 1rem 1rem var(--color-light);
  }
  .right .top .theme-toggler {
    width: 4.4rem;
    position: absolute;
    left: 66%;
  }
  .right .top .profile .info {
    display: none;
  }
  .right .top button {
    display: inline-block;
    background: transparent;
    cursor: pointer;
    color: var(--color-dark);
    position: absolute;
    left: 1rem;
  }
  .right .top button span {
    font-size: 2rem;
  }
}

@media screen and (max-width: 576px) {
  .container {
    width: 100%;
    grid-template-columns: 1fr;
  }
  aside {
    position: fixed;
    left: -100%;
    background: var(--color-white);
    width: 18rem;
    z-index: 3;
    box-shadow: 1rem 3rem 4rem var(--color-light);
    height: 100vh;
    padding-right: var(--card-padding);
    display: none;
    animation: showMenu 400ms ease forwards;
  }
  @keyframes showMenu {
    to {
      left: 0;
    }
  }
  aside .logo {
    margin-left: 1rem;
  }
  aside .logo h2 {
    display: inline;
  }
  aside .sidebar h3 {
    display: inline;
  }
  aside .sidebar a {
    width: 100%;
    height: 3.4rem;
  }
  aside .sidebar a:last-child {
    position: absolute;
    bottom: 5rem;
  }
  aside .close {
    display: inline-block;
    cursor: pointer;
  }
  main {
    margin-top: 8rem;
    padding: 0 1rem;
  }
  main .recent-orders {
    position: relative;
    margin: 3rem 0 0 0;
    width: 100%;
  }
  main .recent-orders table {
    width: 100%;
    margin: 0;
  }
  .right {
    width: 94%;
    margin: 0 auto 4rem;
  }
  .right .top {
    position: fixed;
    top: 0;
    left: 0;
    align-items: center;
    padding: 0 0.8rem;
    height: 4.6rem;
    background: var(--color-white);
    width: 100%;
    margin: 0;
    z-index: 2;
    box-shadow: 0 1rem 1rem var(--color-light);
  }
  .right .top .theme-toggler {
    width: 4.4rem;
    position: absolute;
    left: 66%;
  }
  .right .top .profile .info {
    display: none;
  }
  .right .top button {
    display: inline-block;
    background: transparent;
    cursor: pointer;
    color: var(--color-dark);
    position: absolute;
    left: 1rem;
  }
  .right .top button span {
    font-size: 2rem;
  }
}
/* END MEDIA QUERIES */

  </style>
  </head>
  <body>
    <div class="container">
      <aside>
        <div class="top">
          <div class="logo">
            <img src="./images/logo.png" alt="Logo" />
            <h2>EGA<span class="danger">TOR</span></h2>
          </div>
          <div class="close" id="close-btn">
            <span class="material-icons-sharp"> close </span>
          </div>
        </div>

        <div class="sidebar">
          <a href="#" class="active">
            <span class="material-icons-sharp"> dashboard </span>
            <h3>Dashboard</h3>
          </a>
          <a href="internal_transfer.php">
            <span class="material-icons-sharp"> person_outline </span>
            <h3>Make Transfers</h3>
          </a>
          <!-- <a href="domestic_transfer.php">
            <span class="material-icons-sharp"> receipt_long </span>
            <h3>Domestic Transfer</h3>
          </a>
          <a href="external_transfer.php">
            <span class="material-icons-sharp"> insights </span>
            <h3>External Transfer</h3>
          </a>
          <a href="international_transfer.php">
            <span class="material-icons-sharp"> insights </span>
            <h3>International Transfer</h3>
          </a> -->
          <a href="transactions.php">
            <span class="material-icons-sharp"> mail_outline </span>
            <h3>Transactions</h3>
            <span class="message-count">26</span>
          </a>
          <a href="e_statement.php">
            <span class="material-icons-sharp"> inventory </span>
            <h3>E-Statement</h3>
          </a>
          <a href="contact.php">
            <span class="material-icons-sharp"> report_gmailerrorred </span>
            <h3>Contact Center</h3>
          </a>
          <a href="logout.php">
            <span class="material-icons-sharp"> logout </span>
            <h3>Logout</h3>
          </a>
        </div>
      </aside>

      <main>
        <!-- <h1>Hi, <?php echo $_SESSION['fname']; ?>	</h1> -->

        <div class="right">
        <div class="top">
          <button id="menu-btn">
            <span class="material-icons-sharp"> menu </span>
          </button>
          <div class="theme-toggler">
            <span class="material-icons-sharp active"> light_mode </span>
            <span class="material-icons-sharp"> dark_mode </span>
          </div>
          <!-- <div class="profile">
            <div class="info">
              <p>Hey, <b>Bruno</b></p>
              <small class="text-muted">Admin</small>
            </div>
            <div class="profile-photo">
              <img src="./images/profile-1.jpg" alt="Profile Picture" />
            </div>
          </div> -->
        </div>
        </div>

        <div class="insights">
          <!-- SALES -->
          <!-- <div class="sales">
            <span class="material-icons-sharp"> analytics </span>
            <div class="middle">
              <div class="left">
                <h3>Account Number</h3>
                <h1><?php echo $_SESSION['accnum']; ?></h1>
              </div> -->

              <!-- <div class="progress">
                <svg>
                  <circle cx="38" cy="38" r="36"></circle>
                </svg>
                <div class="number">
                  <p>81%</p>
                </div>
              </div> -->
            <!-- </div> -->
            <!-- <small class="text-muted"> Last 24 hours </small> -->
          <!-- </div> -->

          <!-- EXPENSES -->
          <!-- <div class="expenses">
            <span class="material-icons-sharp"> bar_chart </span>
            <div class="middle">
              <div class="left">
                <h3>Account Balance</h3>
                <?php if (is_numeric($_SESSION['balance'])) : ?>
                <h1><?php echo '$' . number_format(floatval($_SESSION['balance']), 2); ?></h1>
                <?php else : ?>
                  <h3><?php echo $_SESSION['balance']; ?></h3>
                    <?php endif; ?>
              </div> -->

              <!-- <div class="progress">
                <svg>
                  <circle cx="38" cy="38" r="36"></circle>
                </svg>
                <div class="number">
                  <p>62%</p>
                </div> -->
              <!-- </div> -->
            <!-- </div> -->
            <!-- <small class="text-muted"> Last 24 hours </small> -->
          <!-- </div> -->

          <!-- INCOME -->
          <!-- <div class="income">
            <span class="material-icons-sharp"> stacked_line_chart </span>
            <div class="middle">
              <div class="left">
                <h3>Account Type</h3>
                <h1><?php echo $_SESSION['acctype']; ?></h1>
              </div> -->

              <!-- <div class="progress">
                <svg>
                  <circle cx="38" cy="38" r="36"></circle>
                </svg>
                <div class="number">
                  <p>44%</p>
                </div>
              </div> -->
            <!-- </div> -->
            <!-- <small class="text-muted"> Last 24 hours </small> -->
          <!-- </div> -->
        </div>

        
        <div class="scroll">
      <table id="user-list-table">
            <thead>
            <tr>
								<th>Firstname</th>
								<th>Lastname</th>
								<th>Address</th>
								<th>Country</th>
								<th>State</th>
								<th>City</th>
								<th>Zip</th>
								<th>Email</th>
								<th>Phone</th>
								<th>SSN/ID Number</th>
								<th>Gender</th>
								<th>Date of Birth</th>
								<!-- <th>Emp.Status</th> -->
								<th>Account Type</th>
								<!-- <th>Currency</th> -->
								<th>Account Pin</th>
								<th>Routing Number</th>
								<th>Account Number</th>
                <!-- <th>Balance</th> -->
                <th>Action</th>
							</tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user) : ?>
        <tr>
           <!-- Display user data fetched from the database -->
           <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
            <td><input type="text" name="fname" value="<?php echo $user['fname']; ?>"></td>
            <td><input type="text" name="lname" value="<?php echo $user['lname']; ?>"></td>
            <td><input type="text" name="address" value="<?php echo $user['address']; ?>"></td>
            <td><input type="text" name="country" value="<?php echo $user['country']; ?>"></td>
            <td><input type="text" name="state" value="<?php echo $user['state']; ?>"></td>
            <td><input type="text" name="city" value="<?php echo $user['city']; ?>"></td>
            <td><input type="text" name="zipcode" value="<?php echo $user['zipcode']; ?>"></td>
            <td><input type="text" name="email" value="<?php echo $user['email']; ?>"></td>
            <td><input type="text" name="phone" value="<?php echo $user['phone']; ?>"></td>
            <td><input type="text" name="ssn" value="<?php echo $user['ssn']; ?>"></td>
            <td><input type="text" name="gender" value="<?php echo $user['gender']; ?>"></td>
            <td><input type="text" name="dob" value="<?php echo $user['dob']; ?>"></td>
            <td><input type="text" name="acctype" value="<?php echo $user['acctype']; ?>"></td>
            <!-- <td><input type="text" name="currency" value="<?php echo $user['currency']; ?>"></td> -->
            <td><input type="text" name="pin" value="<?php echo $user['pin']; ?>"></td>
            <td><input type="text" name="rtn" value="<?php echo $user['rtn']; ?>"></td>
            <td><input type="text" name="accnum" value="<?php echo $user['accnum']; ?>"></td>
            <!-- <td><input type="text" name="balance" value="<?php echo number_format(floatval($user['balance']), 4); ?>"></td> -->
            <td class="action-buttons">
              <button type="submit" name="update_user">Update</button>
              <button type="submit" name="delete_user" class="delete">Delete</button>
            </td>
          </form>
        </tr>
      <?php endforeach; ?>
        </tbody>
        </table>

         
        <a href="#">Show All</a>
    </div>

    <!-- Display OTPs from otp_table -->
    <h2>Generated OTPs</h2>
    <table border="1">
        <tr>
            <th>Account Number</th>
            <th>OTP</th>
        </tr>
        <?php
        // Fetch all OTPs from otp_table
        $otpQuery = "SELECT * FROM otp_table";
        $otpStmt = $pdo->prepare($otpQuery);
        $otpStmt->execute();
        $otps = $otpStmt->fetchAll(PDO::FETCH_ASSOC);

        // Display OTPs in a table
        foreach ($otps as $otpData) {
            echo "<tr>";
            echo "<td>{$otpData['accnum']}</td>";
            echo "<td>{$otpData['otp']}</td>";
            echo "</tr>";
        }
        ?>
    </table>
    <!-- <script src="./constants/recent-order-data.js"></script> -->
    <!-- <script src="./constants/update-data.js"></script>
    <script src="./constants/sales-analytics-data.js"></script> -->
    <script src="../index.js"></script>
  </body>
</html>
