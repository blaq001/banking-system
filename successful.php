<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
// Include the database connection file
include './connect.php';

// Check if the user is logged in and has valid session data
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Redirect the user to the login page if not logged in
    header("Location: login.php");
    exit();
}

// Function to generate a random OTP (One-Time Password)
function generateOTP()
{
    $otpLength = 6; // Change this value to set the length of the OTP
    $characters = '0123456789'; // Characters to use for generating OTP
    $otp = '';

    for ($i = 0; $i < $otpLength; $i++) {
        $otp .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $otp;
}

// Add a PHP variable to determine whether to show the modal or not
$showModal = false;

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['approve_transfer'])) {
    // Check if all OTP values are set
    if (isset($_POST['otp1'], $_POST['otp2'], $_POST['otp3'], $_POST['otp4'], $_POST['otp5'], $_POST['otp6'])) {
        // Retrieve the admin-approved OTP from the form and concatenate it to form the complete OTP
        $submittedOTP = $_POST['otp1'] . $_POST['otp2'] . $_POST['otp3'] . $_POST['otp4'] . $_POST['otp5'] . $_POST['otp6'];

        // Fetch the stored OTP for the user from the database based on accnum
        $query = "SELECT otp FROM otp_table WHERE accnum = :accnum ORDER BY created_at DESC LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':accnum', $_SESSION['accnum'], PDO::PARAM_STR);
        $stmt->execute();
        $storedOTP = $stmt->fetchColumn();

        if ($storedOTP === $submittedOTP) {
            // Admin-approved OTP is correct, proceed with the fund transfer

            // Retrieve the transfer data and OTP from session
            $transferData = $_SESSION['transfer_data'];

            // Perform the fund transfer using the $transferData
            $s_acct = $transferData['s_acct']; // Source account name
            $accnum = $transferData['accnum']; // Source account number
            $tx_type = $transferData['tx_type']; // Transfer type
            $bk_name = $transferData['bk_name']; // Bank name
            $to_accno = $transferData['to_accno']; // Recipient account number
            $to_rtn = $transferData['to_rtn']; // Recipient routing number
            $amount = floatval($transferData['amount']); // Transfer amount
            $descrip = $transferData['descrip']; // Transfer description
            
            // Assuming $transactionDate contains the date value to be inserted
        //   $formattedDate = date('Y-m-d H:i:s', strtotime($transactionDate));


            // Assuming you have a table 'transactions' to store the transfer details
            $query = "INSERT INTO transactions (s_acct, accnum, tx_type, bk_name, to_accno, to_rtn, amount, descrip) 
            VALUES (:s_acct, :accnum, :tx_type, :bk_name, :to_accno, :to_rtn, :amount, :descrip)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':s_acct', $s_acct, PDO::PARAM_STR);
            $stmt->bindParam(':accnum', $accnum, PDO::PARAM_STR);
            $stmt->bindParam(':tx_type', $tx_type, PDO::PARAM_STR);
            $stmt->bindParam(':bk_name', $bk_name, PDO::PARAM_STR);
            $stmt->bindParam(':to_accno', $to_accno, PDO::PARAM_STR);
            $stmt->bindParam(':to_rtn', $to_rtn, PDO::PARAM_STR);
            $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
            $stmt->bindParam(':descrip', $descrip, PDO::PARAM_STR);
            // $stmt->bindParam(':date', $formattedDate, PDO::PARAM_STR);

            try {
              // Execute the SQL query to store the transfer details in the 'transactions' table
              $stmt->execute();
      
               // Calculate the updated balance after the transfer
        $prevBalance = floatval($_SESSION['balance']);
        $updatedBalance = $prevBalance - $amount;

        // Update the user's balance in the 'users' table
        $updateBalanceQuery = "UPDATE users SET balance = :balance WHERE accnum = :accnum";
        $updateBalanceStmt = $pdo->prepare($updateBalanceQuery);
        $updateBalanceStmt->bindParam(':balance', $updatedBalance, PDO::PARAM_STR);
        $updateBalanceStmt->bindParam(':accnum', $_SESSION['accnum'], PDO::PARAM_STR);
        $updateBalanceStmt->execute();

        // Store the updated balance in the session
        $_SESSION['balance'] = number_format($updatedBalance, 2); // Format the balance with 2 decimal places
      
              // Store the transferred amount and updated balance in the session for display on the dashboard
              $_SESSION['amount'] = number_format($amount, 2); // Format the transferred amount with 2 decimal places
              $_SESSION['updatedBalance'] = number_format($updatedBalance, 2);
      
              // Store the success message in a session variable
              $successMessage = "Transfer was successful! Transferred amount: $" . number_format($amount, 2) . ". Recipient account number: " . $to_accno . ". Your current balance: $" . number_format($updatedBalance, 2);
              $_SESSION['transfer_success_message'] = $successMessage;

                // Clear the OTP from the database and session (optional)
                $deleteQuery = "DELETE FROM otp_table WHERE accnum = :accnum AND otp = :otp";
                $deleteStmt = $pdo->prepare($deleteQuery);
                $deleteStmt->bindParam(':accnum', $_SESSION['accnum'], PDO::PARAM_STR);
                $deleteStmt->bindParam(':otp', $submittedOTP, PDO::PARAM_STR);
                $deleteStmt->execute();

                unset($_SESSION['transfer_data']); // Clear the transfer data from session after successful transfer

                // Redirect back to the user-side dashboard with success message and transfer details
                header("Location: otp.php");
                exit();
            } catch (PDOException $e) {
                // Handle any errors that occurred during the database operation
                echo "Error: " . $e->getMessage();
            }
            
            $showModal = true;
        } else {
            // The admin-approved OTP is incorrect, show an error message
            $otpError = "Invalid OTP. Please try again.";
        }
    } else {
        // One or more OTP fields are missing, show an error message
        $otpError = "Please enter all six OTP digits.";
    }
}

?>

<!DOCTYPE html>
<!-- The rest of your HTML and frontend code goes here -->

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
    <link rel="stylesheet" href="css/style.css" />
    
    <style>
        /* Rest of the CSS code remains the same */

.succes {
  background-color: #4BB543;
}
.succes-animation {
  animation: succes-pulse 2s infinite;
}

.danger {
  background-color: #CA0B00;
}
.danger-animation {
  animation: danger-pulse 2s infinite;
}

.custom-modal {
  position: relative;
  width: 350px;
  min-height: 250px;
  background-color: #fff;
  border-radius: 30px;
  margin: 40px 10px;
}
.custom-modal .content { 
  position: absolute;
  width: 100%;
  text-align: center;
  bottom: 0;
}
.custom-modal .content .type {
  font-size: 18px;
  color: #999;
}
.custom-modal .content .message-type {
  font-size: 24px;
  color: #000;
}
.custom-modal .border-bottom {
  position: absolute;
  width: 300px;
  height: 20px;
  border-radius: 0 0 30px 30px;
  bottom: -20px;
  margin: 0 25px;
}
.custom-modal .icon-top {
  position: absolute;
  width: 100px;
  height: 100px;
  border-radius: 50%;
  top: -30px;
  margin: 0 125px;
  font-size: 30px;
  color: #fff;
  line-height: 100px;
  text-align: center;
}
@keyframes succes-pulse { 
  0% {
    box-shadow: 0px 0px 30px 20px rgba(75, 181, 67, .2);
  }
  50% {
    box-shadow: 0px 0px 30px 20px rgba(75, 181, 67, .4);
  }
  100% {
    box-shadow: 0px 0px 30px 20px rgba(75, 181, 67, .2);
  }
}
@keyframes danger-pulse { 
  0% {
    box-shadow: 0px 0px 30px 20px rgba(202, 11, 0, .2);
  }
  50% {
    box-shadow: 0px 0px 30px 20px rgba(202, 11, 0, .4);
  }
  100% {
    box-shadow: 0px 0px 30px 20px rgba(202, 11, 0, .2);
  }
}


.page-wrapper {
  height: 100vh;
  background-color: #eee;
  display: none;
  align-items: center;
  justify-content: center;
  padding: 80px 0;
}

@media only screen and (max-width: 800px) {
  .page-wrapper {
    flex-direction: column;
  }
}

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
          <a href="index.php">
            <span class="material-icons-sharp"> dashboard </span>
            <h3>Dashboard</h3>
          </a>
          <a href="internal_transfer.php">
            <span class="material-icons-sharp"> swap_horiz </span>
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
            <span class="material-icons-sharp"> insert_chart_outlined </span>
            <h3>Transactions</h3>
            <span class="message-count">26</span>
          </a>
          <a href="e_statement.php">
            <span class="material-icons-sharp"> history </span>
            <h3>E-Statement</h3>
          </a>
          <a href="contact.php">
            <span class="material-icons-sharp"> call </span>
            <h3>Contact Center</h3>
          </a>
          <a href="logout.php">
            <span class="material-icons-sharp"> logout </span>
            <h3>Logout</h3>
          </a>
        </div>
      </aside>

      <main>
        <h1>Hi, <?php echo $_SESSION['fname']; ?>	</h1>

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
          <div class="sales">
            <span class="material-icons-sharp"> analytics </span>
            <div class="middle">
              <div class="left">
                <h3>Account Number</h3>
                <h1><?php echo $_SESSION['accnum']; ?></h1>
              </div>

              <!-- <div class="progress">
                <svg>
                  <circle cx="38" cy="38" r="36"></circle>
                </svg>
                <div class="number">
                  <p>81%</p>
                </div>
              </div> -->
            </div>
            <!-- <small class="text-muted"> Last 24 hours </small> -->
          </div>

          <!-- EXPENSES -->
          <div class="expenses">
            <span class="material-icons-sharp"> bar_chart </span>
            <div class="middle">
              <div class="left">
                <h3>Account Balance</h3>
                <?php if (is_numeric($_SESSION['balance'])) : ?>
                <h1><?php echo '$' . number_format(floatval($_SESSION['balance']), 2); ?></h1>
                <?php else : ?>
                  <h3><?php echo $_SESSION['balance']; ?></h3>
                    <?php endif; ?>
              </div>

              <!-- <div class="progress">
                <svg>
                  <circle cx="38" cy="38" r="36"></circle>
                </svg>
                <div class="number">
                  <p>62%</p>
                </div> -->
              <!-- </div> -->
            </div>
            <!-- <small class="text-muted"> Last 24 hours </small> -->
          </div>

          <!-- INCOME -->
          <div class="income">
            <span class="material-icons-sharp"> stacked_line_chart </span>
            <div class="middle">
              <div class="left">
                <h3>Account Type</h3>
                <h1><?php echo $_SESSION['acctype']; ?></h1>
              </div>

              <!-- <div class="progress">
                <svg>
                  <circle cx="38" cy="38" r="36"></circle>
                </svg>
                <div class="number">
                  <p>44%</p>
                </div>
              </div> -->
            </div>
            <!-- <small class="text-muted"> Last 24 hours </small> -->
          </div>
        </div>

        <div class="recent-orders">
          <!-- <h2>E-Statement</h2> -->
          <div id="successModal" class="page-wrapper">
  <div class="custom-modal">
    <div class="succes succes-animation icon-top"><i class="fa fa-check"></i></div>
    <div class="succes border-bottom"></div>
    <div class="content">
      <p class="type">Transfer Success</p>
      <p class="message-type"><?php echo isset($_SESSION['transfer_success_message']) ? $_SESSION['transfer_success_message'] : ''; ?></p>

      <!-- Change the input type to "button" to prevent form submission -->
      <input type="button" class="btn" name="continue" value="Continue" onclick="redirectToTransactions()">
    </div>
  </div>
</div>
          <a href="#">Show All</a>
        </div>
      </main>
      </div>
    </div>
    
    

<script>
  // JavaScript function to show the modal popup
  function showModal() {
    document.getElementById("successModal").style.display = "block";
  }

  // JavaScript function to hide the modal popup
  function hideModal() {
    document.getElementById("successModal").style.display = "none";
    // Redirect to transactions.php after hiding the modal
    redirectToTransactions();
  }

  // Function to redirect to transactions.php when the "Continue" button is clicked
  function redirectToTransactions() {
    window.location.href = "transactions.php";
  }

  // Call the function to show the modal if $_SESSION['show_modal'] is true
  <?php if ($_SESSION['show_modal']) : ?>
    showModal();
    <?php $_SESSION['show_modal'] = false; ?> // Reset the session flag after showing the modal
  <?php endif; ?>
</script>




    <!-- <script src="./constants/recent-order-data.js"></script> -->
    <!-- <script src="./constants/update-data.js"></script>
    <script src="./constants/sales-analytics-data.js"></script> -->
    <script src="css/index.js"></script>
  </body>
</html>
