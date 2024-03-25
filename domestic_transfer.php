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

// Retrieve the user's data from the session and store them in variables
$fname = $_SESSION['fname'] ?? '';
$lname = $_SESSION['lname'] ?? '';
$accnum = $_SESSION['accnum'] ?? '';
$balance = $_SESSION['balance'] ?? '';

// Check for insufficient funds
if (!empty($balance) && floatval($balance) < 0.01) {
    // Set an error message for insufficient funds
    $_SESSION['transfer_error'] = "Insufficient funds. Transfer cannot be completed.";
    // Redirect back to the transactions page with the error message
    // header("Location: /slide/transactions.php/");
    // exit();
}


// Fetch the user's balance from the database based on their account number
if (!empty($accnum)) {
    // Query to fetch the user's balance from the 'users' table based on the account number
    $query = "SELECT balance, transfer_allowed FROM users WHERE accnum = :accnum";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':accnum', $accnum, PDO::PARAM_STR);
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


// Check if the form was submitted
if (isset($_POST['transfer'])) {
    // Check if transfer is allowed for the user
    if ($_SESSION['transfer_allowed'] != 1) {
        // Set an error message and prevent the transfer
        $_SESSION['transfer_error'] = "Transfers are not allowed for this account, contact your bank.";
        // Redirect back to the same page after processing the form (on failed transfer)
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Retrieve the form data
    $s_acct = $_POST['s_acct'] ?? '';
    $tx_type = $_POST['tx_type'] ?? '';
    $bk_name = $_POST['bk_name'] ?? '';
    $to_accno = $_POST['to_accno'] ?? '';
    $to_rtn = $_POST['to_rtn'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $descrip = $_POST['descrip'] ?? '';

    // Perform additional validation on the form data (you can add more validation based on your requirements)

    // Check if the user's balance is sufficient for the transfer
    if (floatval($balance) < floatval($amount)) {
        // Set an error message for insufficient funds
        $_SESSION['transfer_error'] = "Insufficient funds. Transfer cannot be completed.";
    } else if ($s_acct !== $fname) {
        // Ensure that the source account is the account of the currently signed-in user
        // Set an error message and prevent the transfer
        $_SESSION['transfer_error'] = "You can only transfer from your own account.";
    } else {
        try {
            $pdo->beginTransaction();

            // Deduct the transferred amount from the user's balance
            $query = "UPDATE users SET balance = balance - :amount WHERE fname = :fname";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
            $stmt->bindParam(':fname', $s_acct, PDO::PARAM_STR);
            $stmt->execute();

            // Insert the transaction data into the database
            $query = "INSERT INTO transactions (s_acct, tx_type, bk_name, to_accno, to_rtn, amount, descrip, date)
                      VALUES (:s_acct, :tx_type, :bk_name, :to_accno, :to_rtn, :amount, :descrip, NOW())";

            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':s_acct', $s_acct, PDO::PARAM_STR);
            $stmt->bindParam(':tx_type', $tx_type, PDO::PARAM_STR);
            $stmt->bindParam(':bk_name', $bk_name, PDO::PARAM_STR);
            $stmt->bindParam(':to_accno', $to_accno, PDO::PARAM_STR);
            $stmt->bindParam(':to_rtn', $to_rtn, PDO::PARAM_STR);
            $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
            $stmt->bindParam(':descrip', $descrip, PDO::PARAM_STR);
            $stmt->execute();

            $pdo->commit();
// Set the success message to be displayed on the transactions page
$_SESSION['transfer_success'] = "Transfer was successful!";

// Redirect to the transactions page after processing the form (on successful transfer)
header("Location: /world/transactions.php/");
exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            // Handle any errors that occurred during the transaction
            $_SESSION['transfer_error'] = "An error occurred while processing the transfer.";
        }
    }

    // Redirect back to the same page after processing the form
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
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
    <link rel="stylesheet" href="./style.css" />
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
            <span class="material-icons-sharp"> person_outline </span>
            <h3>Internal Transfer</h3>
          </a>
          <a href="domestic_transfer.php" class="active">
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
          </a>
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

        <div class="transfers">
          <h2>Internal Transfer</h2>

        <!-- Step 1: Personal Information -->
        <div class="box form-box" id="step1">
        <form action="" method="post">
            <header>Sign Up - Step 1</header>

            <div class="two-forms">
            <div class="input-box">
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
                
            <div class="input-box">
            <label>Transfer Type</label>
                <select id="tx_type" name="tx_type" class="input-field" required>
                <option value="" disabled selected>Select Transfer Type</option>
                <option value="internal">Internal Transfer</option>
                <option value="domestic" disabled>Domestic Transfer</option>
                <option value="international" disabled>International Transfer</option>
                </select>
            </div>
            </div>

                <div class="field input">
                    <label>Bank Name</label>
                    <input type="text" name="bk_name" id="bk_name" autocomplete="off" required>
                </div>

                <div class="two-forms">
                <div class="field input">
                    <label>Account Number</label>
                    <input type="text" name="to_accno" id="to_accno" autocomplete="off" required>
                </div>

                <div class="field input">
                    <label>Routing Number</label>
                    <input type="text" name="to_rtn" id="to_rtn" autocomplete="off" required>
                </div>
                </div>

                <div class="field input">
                    <label>Amount</label>
                    <input type="text" name="amount" id="amount" autocomplete="off" required>
                </div>

                <div class="field input">
                    <label>Description</label>
                    <input type="text" name="descrip" id="descrip" autocomplete="off" required>
                </div>

                <div class="field">
                    <input type="submit" class="btn" name="transfer" value="Transfer" required>
                </div>
        </div>
        <div class="footer-area">
          <a href="#">Show All</a>
                </div>
      </main>

        
      </div>
    

    <!-- <script src="./constants/recent-order-data.js"></script> -->
    <!-- <script src="./constants/update-data.js"></script>
    <script src="./constants/sales-analytics-data.js"></script> -->
    <script src="./index.js"></script>
  </body>
</html>
