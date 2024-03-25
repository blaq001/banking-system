<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
// Include the database connection file
include './connect.php';


// Initialize a session variable to store the previous balance if available
$prevBalance = isset($_SESSION['prev_balance']) ? floatval($_SESSION['prev_balance']) : null;

// Check if the user is logged in and has valid session data
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Redirect the user to the login page if not logged in
    header("Location: index.php");
    exit();
}

// Fetch the user's data from the session and store them in variables
$fname = $_SESSION['fname'] ?? '';
$accnum = $_SESSION['accnum'] ?? '';
$balance = isset($_SESSION['balance']) ? floatval($_SESSION['balance']) : 0.00;

// Check for insufficient funds
// if ($balance <= 0.00) {
//     // Set an error message for insufficient funds
//     $_SESSION['transfer_error'] = "Insufficient funds. Transfer cannot be completed.";
//     // Redirect back to the transactions page with the error message
//     header("Location: transactions.php");
//     exit();
// }


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

// Check if the form was submitted
if (isset($_POST['transfer'])) {
    // Retrieve the form data 
    $acctype = $_SESSION['acctype'] ?? '';
    $s_acct = $_POST['s_acct'] ?? '';
    $tx_type = $_POST['tx_type'] ?? '';
    $bk_name = $_POST['bk_name'] ?? '';
    $hname = $_POST['hname'] ?? '';
    $to_accno = $_POST['to_accno'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $descrip = $_POST['descrip'] ?? '';

$amount = is_numeric($amount) ? floatval($amount) : 0.0;

// Check if acctype is not set in the session (first time) or if the user's account type has changed
if (!isset($_SESSION['acctype']) || $_SESSION['acctype'] !== $acctype) {
    // Update the acctype in the session
    $_SESSION['acctype'] = $acctype;
}


    // Perform additional validation on the form data (you can add more validation based on your requirements)

    // Check if the user's balance is sufficient for the transfer
    if ($balance < floatval($amount)) {
        // Set an error message for insufficient funds
        $_SESSION['transfer_error'] = "Insufficient funds. Transfer cannot be completed.";
        // Redirect back to the transactions page with the error message
        header("Location: internal_transfer.php");
        exit();
    } elseif ($s_acct !== $fname) {
        // Ensure that the source account matches the currently logged-in user's first name
        // Set an error message and prevent the transfer
        $_SESSION['transfer_error'] = "You can only transfer from your own account.";
        // Redirect back to the transactions page with the error message
        header("Location: internal_transfer.php");
        exit();
    } else {
        // Generate the OTP
        $otp = generateOTP();

        // Check if the generated OTP is not null
        if (empty($otp)) {
            // Handle the case when the OTP could not be generated
            $_SESSION['transfer_error'] = "An error occurred while generating the OTP.";
            // Redirect back to the transactions page with the error message
            header("Location: internal_transfer.php");
            exit();
        }

        // Store the OTP in the database (for admin verification)
        $query = "INSERT INTO otp_table (accnum, otp) VALUES (:accnum, :otp)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':accnum', $accnum, PDO::PARAM_STR);
        $stmt->bindParam(':otp', $otp, PDO::PARAM_STR);

        try {
            // Execute the SQL query to store the OTP
            $stmt->execute();

            // Store the transfer data and OTP in session for later use
            $_SESSION['transfer_data'] = array(
                's_acct' => $s_acct,
                'accnum' => $accnum,
                'tx_type' => $tx_type,
                'bk_name' => $bk_name,
                'hname' => $hname,
                'to_accno' => $to_accno,
                'amount' => $amount,
                'descrip' => $descrip,
                'otp' => $otp,
            );

            // Redirect to the OTP verification page
            header("Location: otp.php");
            exit();
        } catch (PDOException $e) {
            // Handle the case when the SQL query to store the OTP fails
            $_SESSION['transfer_error'] = "An error occurred while processing the transfer.";
            // Redirect back to the transactions page with the error message
            header("Location: internal_transfer.php");
            exit();
        }
    }
}

if (!empty($accnum)) {
    // Query to fetch the user's balance from the 'users' table based on the account number
    $query = "SELECT balance FROM users WHERE accnum = :accnum";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':accnum', $accnum, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Assign the fetched balance to the session variable
        $_SESSION['balance'] = $result['balance'];
        
        // Check for insufficient funds
        // if ($_SESSION['balance'] <= 0.00) {
        //     // Set an error message for insufficient funds
        //     $_SESSION['transfer_error'] = "Insufficient funds. Transfer cannot be completed.";
        //     // Redirect back to the transactions page with the error message
        //     header("Location: internal_transfer.php");
        //     exit();
        // }
  } else {
      // Handle the case when balance is not available or account number is not found in the database
      $_SESSION['balance'] = 'N/A';
  }

  if (is_numeric($_SESSION['balance']) && $prevBalance !== null) {
    $currentBalance = floatval($_SESSION['balance']);

    if ($currentBalance > $prevBalance) {
        $creditedAmount = $currentBalance - $prevBalance;
        $updatedBalance = $currentBalance;
        echo '<div class="alert alert-success">Your account has been credited with $' . number_format($creditedAmount, 2) . '. Your updated balance is $' . number_format($updatedBalance, 2) . '.</div>';
    }
}

// Update the previous balance in the session with the current balance
$_SESSION['prev_balance'] = $_SESSION['balance'];
}
?>

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
    <link rel="stylesheet" href="css/style.css" />
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
          <a href="internal_transfer.php" class="active">
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

        <!-- Add this inside the <body> tag, after the </main> tag -->
<!-- Add this code at the end of your <body> tag, before the closing </body> tag -->


        <div class="recent-orders">
          <!-- <h2>E-Statement</h2> -->
          <!-- <table id="recent-orders--table"> -->
             <!-- Step 1: Personal Information -->
<div class="box form-box" id="step1">
        <form action="" method="post">
            <!-- Display the error message if it exists -->
            <?php if (isset($_SESSION['transfer_error']) && !empty($_SESSION['transfer_error'])) : ?>
                <div class="error-message"><?php echo $_SESSION['transfer_error']; ?></div>
                <?php unset($_SESSION['transfer_error']); // Clear the transfer_error session variable after displaying the error message ?>
            <?php endif; ?>

    <!-- Display the account-credited message if it exists -->
    <div class="account-credited-message">
    <?php
    if (is_numeric($_SESSION['balance']) && $prevBalance !== null) {
        $currentBalance = floatval($_SESSION['balance']);

        if ($currentBalance > $prevBalance) {
            $creditedAmount = $currentBalance - $prevBalance;
            $updatedBalance = $currentBalance;
            echo 'Your account has been credited with $' . number_format($creditedAmount, 2) . '. Your updated balance is $' . number_format($updatedBalance, 2) . '.';
        }
    }
    ?>
</div>
<?php if (isset($accountCreditedMessage)) : ?>
    <div class="account-credited-message">
        <?php echo $accountCreditedMessage; ?>
    </div>
<?php endif; ?>

            <header>Make Transfers</header>
            <!-- <p>All fields are required</p> -->
            <div class="field input">
    <label>Account Type</label>
    <select id="acctype" name="acctype" class="input-field" required>
        <option value="" disabled selected>Select Account Type</option>
        <?php
        // Fetch the unique account types from the database and populate the dropdown options
        $query = "SELECT DISTINCT acctype FROM users";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $accountTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($accountTypes as $type) {
            echo "<option value='{$type['acctype']}' " . ($acctype === $type['acctype'] ? 'selected' : '') . ">{$type['acctype']}</option>";
        }
        ?>
    </select>
</div>

            <div class="field input">
            <label>Source Account</label>
            <select id="s_acct" name="s_acct" class="input-field" required>
                <option value="" disabled selected>Select Account</option>
                <?php
                // Fetch the user's accounts from the database and populate the dropdown options
                $query = "SELECT fname FROM users WHERE id = :id";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
                $stmt->execute();
                $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($accounts as $account) {
                echo "<option value='{$account['fname']}'>{$account['fname']}</option>";
                }
                ?>
                </select>
            </div>

            <div class="field input">
            <label>Source Acc. Number</label>
                <select id="accnum" name="accnum" class="input-field" required>
                <option value="" disabled selected>Select Account</option>
                <?php
                // Fetch the user's accounts from the database and populate the dropdown options
                $query = "SELECT accnum FROM users WHERE id = :id";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
                $stmt->execute();
                $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($accounts as $account) {
                echo "<option value='{$account['accnum']}'>{$account['accnum']}</option>";
                }
                ?>
                </select>
            </div>         

            <div class="field input">
            <label>Transfer Type</label>
                <select id="tx_type" name="tx_type" class="input-field" required>
                <option value="" disabled selected>Select Transfer Type</option>
                <option value="Internal">Internal Transfer</option>
                <option value="Domestic">Domestic Transfer</option>
                <option value="External">External Transfer</option>
                <option value="International">International Transfer</option>
                </select>
            </div>

                <div class="field input">
                    <label>Bank Name</label>
                    <input type="text" name="bk_name" id="bk_name" autocomplete="off" required>
                </div>
                
                <div class="field input">
                    <label>Holder Name</label>
                    <input type="text" name="hname" id="hname" autocomplete="off" required>
                </div>

                <div class="two-forms">
                <div class="field input">
                    <label>Account Number</label>
                    <input type="text" name="to_accno" id="to_accno" autocomplete="off" required>
                </div>

                <div class="field input">
                    <label>Amount</label>
                    <input type="text" name="amount" id="amount" autocomplete="off" required>
                </div>
                </div>
                
                <div class="field input">
                    <label>Routine Number</label>
                    <input type="text" id="to_accno" autocomplete="off" required>
                </div>

                <div class="field input">
                    <label>Description</label>
                    <input type="text" name="descrip" id="descrip" autocomplete="off" required>
                </div>

                <div class="field">
                    <input type="submit" class="btn" name="transfer" value="Transfer" required>
                </div>
              </div>
          <!-- </table> -->
          <div class="footer">
          <p href="#">Copyright reserve 2023</p></div>
              </div>
          
        <!-- </div> -->
        
      </main>
        
      </div>
    </div>
    

    <!-- <script src="./constants/recent-order-data.js"></script> -->
    <!-- <script src="./constants/update-data.js"></script>
    <script src="./constants/sales-analytics-data.js"></script> -->
    <script src="css/index.js"></script>
  </body>
</html>
