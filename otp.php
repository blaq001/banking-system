<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
// Include the database connection file
include './connect.php';

// Check if the user is logged in and has valid session data
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Redirect the user to the login page if not logged in
    header("Location: index.php");
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
    
     // Fetch the user's balance and transfer_allowed status from the database based on their account number
    if (!empty($_SESSION['accnum'])) {
        // Query to fetch the user's balance and transfer_allowed status from the 'users' table based on the account number
        $query = "SELECT balance, transfer_allowed FROM users WHERE accnum = :accnum";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':accnum', $_SESSION['accnum'], PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Assign the fetched balance and transfer_allowed status to the session variables
            $_SESSION['balance'] = $result['balance'];
            $_SESSION['transfer_allowed'] = $result['transfer_allowed'];
        } else {
            // Handle the case when balance is not available or account number is not found in the database
            $_SESSION['balance'] = 'N/A';
            $_SESSION['transfer_allowed'] = 0; // Set to 0 to prevent transfers for unknown users
        }
    }

    // Check if transfer is allowed for the user
    if ($_SESSION['transfer_allowed'] != 1) {
        // Set an error message and prevent the transfer
        $_SESSION['transfer_error'] = "Your transfer cannot be completed, contact your bank.";
        // Redirect back to the same page after processing the form (on failed transfer)
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
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
            $hname = $transferData['hname']; 
            $to_accno = $transferData['to_accno']; // Recipient account number
            $amount = floatval($transferData['amount']); // Transfer amount
            $descrip = $transferData['descrip']; // Transfer description
            
            // Assuming $transactionDate contains the date value to be inserted
        //   $formattedDate = date('Y-m-d H:i:s', strtotime($transactionDate));


            // Assuming you have a table 'transactions' to store the transfer details
            $query = "INSERT INTO transactions (s_acct, accnum, tx_type, bk_name, hname, to_accno, amount, descrip) 
            VALUES (:s_acct, :accnum, :tx_type, :bk_name, :hname, :to_accno, :amount, :descrip)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':s_acct', $s_acct, PDO::PARAM_STR);
            $stmt->bindParam(':accnum', $accnum, PDO::PARAM_STR);
            $stmt->bindParam(':tx_type', $tx_type, PDO::PARAM_STR);
            $stmt->bindParam(':bk_name', $bk_name, PDO::PARAM_STR);
            $stmt->bindParam(':to_accno', $to_accno, PDO::PARAM_STR);
            $stmt->bindParam(':hname', $hname, PDO::PARAM_STR);
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
                
                $showModal = true;

                // Redirect back to the user-side dashboard with success message and transfer details
                // header("Location: otp.php");
                // exit();
            } catch (PDOException $e) {
                // Handle any errors that occurred during the database operation
                echo "Error: " . $e->getMessage();
            }
            
            
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
    <title>Bank of the West</title>

    <link
      href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css" />
    
    <style>
        /* Rest of the CSS code remains the same */
.alert-danger{
    color: red;
}
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
  position: absolute;
  top: 50%;
  left: 50%;
  width: 350px;
  min-height: 250px;
  background-color: #fff;
  border-radius: 30px;
  margin: -125px 0 0 -175px;
  padding: 20px;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
  z-index: 9999;
}
.custom-modal .content { 
  position: absolute;
  /*width: 70%;*/
  text-align: center;
  bottom: 0;
}
.custom-modal .content .type {
  font-size: 18px;
  color: #999;
  text-align: center;
}
.custom-modal .content .message-type {
  font-size: 12px;
  color: #000;
  text-align: center;
}
.custom-modal .border-bottom {
  position: absolute;
  width: 300px;
  height: 20px;
  border-radius: 0 0 30px 30px;
  bottom: -20px;
  margin: 0 10px;
}
.custom-modal .icon-top {
  position: absolute;
  width: 100px;
  height: 100px;
  border-radius: 50%;
  top: -30px;
  margin-left: -50px;
  font-size: 30px;
  color: #fff;
  line-height: 100px;
  text-align: center;
}
.btn{
    margin-bottom: 8px;
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
  /*height: 0;*/
   background-color: rgba(0, 0, 0, 0.5);
  display: none;
  align-items: center;
  justify-content: center;
  /*padding: 80px 0;*/
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
            <img src="./images/dash.png" alt="Logo" />
          <div class="logo">
          </div>
          <div class="close" id="close-btn">
            <span class="material-icons-sharp"> close </span>
          </div>
        </div>

        <div class="sidebar">
          <a href="dashboard.php">
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
            <!--<span class="message-count">26</span>-->
          </a>
          <a href="e_statement.php">
            <span class="material-icons-sharp"> history </span>
            <h3>E-Statement</h3>
          </a>
          <a href="https://userwcbfinance.com/contact-2/">
            <span class="material-icons-sharp"> call </span>
            <h3>Contact Center</h3>
          </a>
          <a href="profile.php">
            <span class="material-icons-sharp"> person </span>
            <h3>Profile</h3>
          </a>
          <a href="logout.php">
            <span class="material-icons-sharp"> logout </span>
            <h3>Logout</h3>
          </a>
        </div>
      </aside>

      <main>
          <div class="header-img">
              <img src="images/tree.jpg">
          </div>
        <h1>Welcome, <?php echo $_SESSION['fname']; ?>	</h1>

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
            <span class="material-icons-sharp"> stacked_line_chart </span>
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
            <span class="material-icons-sharp"> attach_money  </span>
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
            <span class="material-icons-sharp"> savings </span>
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
          <h1>Enter OTP</h1>
          <?php if (isset($otpError)) : ?>
                    <div class="error"><?php echo $otpError; ?></div>
                <?php endif; ?>

          <form id="otp-form" action="" method="post">
              
              <!-- Display the transfer_error message directly within the form -->
    <?php if (isset($_SESSION['transfer_error'])) : ?>
        <div class="alert alert-danger">
            <?php echo $_SESSION['transfer_error']; ?>
        </div>
        <?php unset($_SESSION['transfer_error']); ?> <!-- Clear the error message after displaying it -->
    <?php endif; ?>
    
    <div class="otp-field">
    <input type="text" name="otp1" maxlength="1" required>
                            <input type="text" name="otp2" maxlength="1" autocomplete="off" required>
                            <input type="text" name="otp3" maxlength="1" autocomplete="off" required>
                            <input type="text" name="otp4" maxlength="1" autocomplete="off" required>
                            <input type="text" name="otp5" maxlength="1" autocomplete="off" required>
                            <input type="text" name="otp6" maxlength="1" autocomplete="off" required>
    </div>
    <div class="field">
                        <input type="submit" class="btn" name="approve_transfer" value="Approve Transfer" onclick="submitForm()>
                    </div>
  </form>
  <div class="footer">
          <p href="#">Copyright reserve 2023</p></div>
        </div>
      </main>
      
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
        
      </div>
    </div>
    
    

<script>
    function submitForm() {
        // Get the form element
        const form = document.getElementById("otp-form");
        const formData = new FormData(form);

        // Send the form data asynchronously using AJAX
        fetch(form.action, {
            method: form.method,
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Check if the transfer was successful
            if (data.success) {
                // Show the modal
                showModal();
            } else {
                // Show an error message (if needed)
                // For example, display an error message if the OTP is incorrect
                // You can handle the error case based on your specific requirements
                console.log(data.error);
            }
        })
        .catch(error => {
            console.error('Error occurred:', error);
        });
    }

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
    <?php if ($showModal) : ?>
        showModal();
        <?php $showModal = false; ?> // Reset the variable after showing the modal
    <?php endif; ?>
    
    // Check if the user should be allowed to navigate back to OTP.php
  function preventBackNavigation() {
    history.pushState(null, null, document.URL);
    window.addEventListener('popstate', function () {
      history.pushState(null, null, document.URL);
    });
  }

  // Call the preventBackNavigation function to prevent navigating back to OTP.php
  preventBackNavigation();
</script>




    <!-- <script src="./constants/recent-order-data.js"></script> -->
    <!-- <script src="./constants/update-data.js"></script>
    <script src="./constants/sales-analytics-data.js"></script> -->
    <script src="css/index.js"></script>
  </body>
</html>
