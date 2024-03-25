<?php
session_start();
// Include the database connection file
include './connect.php';

$prevBalance = isset($_SESSION['prev_balance']) ? floatval($_SESSION['prev_balance']) : null;

// Check if the user is logged in and has valid session data
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Redirect the user to the login page if not logged in
    header("Location: index.php");
    exit();
}

// Check if the transfer was successful
$transfer_success = $_SESSION['transfer_success'] ?? false;

// Fetch the user's balance from the database based on their account number
$accnum = $_SESSION['accnum'] ?? '';

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
// if (!empty($accnum)) {
//     // Query to fetch the user's balance from the 'users' table based on the account number
//     $query = "SELECT balance FROM credit WHERE accnum = :accnum";
//     $stmt = $pdo->prepare($query);
//     $stmt->bindParam(':accnum', $accnum, PDO::PARAM_STR);
//     $stmt->execute();
//     $result = $stmt->fetch(PDO::FETCH_ASSOC);

//     if ($result) {
//         // Get the current balance from the database
//         $currentBalance = floatval($result['balance']);

//         // Deduct the transferred amount from the current balance
//         if ($transfer_success && isset($_SESSION['amount']) && is_numeric($_SESSION['amount'])) {
//             $transferredAmount = floatval($_SESSION['amount']);
//             $currentBalance -= $transferredAmount;
//         }

//         // Update the user's balance in the 'users' table
//         $updateQuery = "UPDATE users SET balance = :balance WHERE accnum = :accnum";
//         $updateStmt = $pdo->prepare($updateQuery);
//         $updateStmt->bindParam(':balance', $currentBalance, PDO::PARAM_STR);
//         $updateStmt->bindParam(':accnum', $accnum, PDO::PARAM_STR);
//         $updateStmt->execute();

//         // Assign the updated balance to the session variable
//         $_SESSION['balance'] = $currentBalance;
//     } else {
//         // Handle the case when balance is not available or account number is not found in the database
//         $_SESSION['balance'] = 'N/A';
//     }
// }

if (is_numeric($_SESSION['balance']) && $prevBalance !== null) {
    $currentBalance = floatval($_SESSION['balance']);

    if ($currentBalance > $prevBalance) {
        $creditedAmount = $currentBalance - $prevBalance;
        $updatedBalance = $currentBalance;
        echo '<div class="alert alert-success">Your account has been credited with $' . number_format($creditedAmount, 2) . '. Your updated balance is $' . number_format($updatedBalance, 2) . '.</div>';
    }
}

// Check if the transfer was successful and show the successful transfer message
// if ($transfer_success) {
//   // Determine the transferred amount, transferred account number, and the user's current balance after the transfer
//   $transferredAmount = isset($_SESSION['amount']) && is_numeric($_SESSION['amount']) ? floatval($_SESSION['amount']) : 0.0;
//   $transferredName = $_SESSION['hname'] ?? 'N/A';
//   $currentUserBalance = $_SESSION['balance'];
//   echo '<div class="alert alert-success">Transfer was successful! Transferred amount: $" . number_format($transferredAmount, 2) . ". Holder name: " . $transferredName .  ". Your current balance is $" . number_format($currentUserBalance, 2) . ".</div>';

  // Construct the successful transfer message
  
//   $successMessage = "Transfer was successful! Transferred amount: $" . number_format($transferredAmount, 2) . ". Holder name: " . $transferredName .  ". Your current balance is $" . number_format($currentUserBalance, 2) . ".";

  // After displaying the success message, clear the transfer_success flag to prevent repeated updates
//   $_SESSION['transfer_success_message'] = $successMessage;
//   $_SESSION['transfer_success'] = false;
// }

// Check if a success message exists in the session and display it
// if (isset($_SESSION['transfer_success_message'])) {
//   echo '<div class="alert alert-success">' . $_SESSION['transfer_success_message'] . '</div>';
//   // Clear the session variable after displaying the message
//   unset($_SESSION['transfer_success_message']);
// }

// Fetch the user's balance from the database based on their account number
$accnum = $_SESSION['accnum'] ?? '';
if (!empty($accnum)) {
    // Query to fetch the user's balance from the 'credit' table based on the account number
    $query = "SELECT * FROM credit WHERE accnum = :accnum";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':accnum', $accnum, PDO::PARAM_STR);
    $stmt->execute();
    $creditedTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$query = "SELECT * FROM transactions WHERE accnum = :accnum ORDER BY date DESC";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':accnum', $accnum, PDO::PARAM_STR);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get the latest transfer date for highlighting the row
$latestTransferDate = $transactions[0]['date'] ?? null;

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
    
    <style>
  .credited-transaction {
    background-color: #d1f7d1; /* Example background color for credited rows */
  }
</style>

  </head>
  <body>
    <div class="container">
      <aside>
        <div class="top">
          <div class="logo">
               <img src="./images/dash.png" alt="Logo" />
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
          <a href="transactions.php" class="active">
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
          <h2>Credited Transactions</h2>
          <!-- Credit table -->
        <table id="credit-table">
          <thead>
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

            <tr>
              <th>Bank Name</th>
              <th>Holder Name</th>
              <th>Account Number</th>
              <th>Status (Credited)</th>
              <th>Amount (Balance)</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($creditedTransactions)) : ?>
              <?php foreach ($creditedTransactions as $creditedtransact) : ?>
                <tr class="credited-transaction">
                  <td><?php echo $creditedtransact['bk_name']; ?></td>
                  <td><?php echo $creditedtransact['hname']; ?></td>
                  <td><?php echo $creditedtransact['accnum']; ?></td>
                  <td>Credited</td>
                  <td>$<?php echo $creditedtransact['balance']; ?></td>
                  <td><?php echo $creditedtransact['date']; ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else : ?>
              <tr>
                <td colspan="5">No credited transactions found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
          </table>
          </div>
          
          <div class="recent-orders">
          <h2>Debited Transactions</h2>
          <table id="recent-orders--table">
            <thead>
          
        <!-- Display the successful transfer message as a new row in the table -->
        <?php if (isset($_SESSION['transfer_success_message'])) : ?>
          <tr class="success-message-block">
            <td colspan="9"><?php echo $_SESSION['transfer_success_message']; ?></td>
          </tr>
        <?php endif; ?>
              <!--<th>Source Account</th>-->
              <!--<th>Source Account Number</th>-->
              
              <th>Bank Name</th>
              <th>Holder Name</th>
              <th>Account Number</th>
              <th>Status (Debited)</th>
              <th>Amount</th>
              <th>Date</th>
              
						  <!--  <th>Transfer Type</th>-->
								<!--<th>Bank Name</th>-->
								<!--<th>Holder Name</th>-->
								<!--<th>Account Number</th>-->
								<!--<th>Amount</th>-->
								<!--<th>Description</th>-->
								<!--<th>Date</th>-->
							
              </tr>
            </thead>
            <!-- Add tbody here | JS insertion -->
            <?php if (!empty($transactions)) : ?>
    <?php foreach ($transactions as $transaction) : ?>
        <?php
        // Check if the row's date matches the latest successful transfer date
        $highlight_row = $transfer_success && $transaction['date'] === $latestTransferDate;
        ?>
        <tr <?php if ($highlight_row) echo 'class="highlight-row"'; ?>>
          <!--<td><?php echo $transaction['s_acct']; ?></td>-->
          <!--<td><?php echo $transaction['accnum']; ?></td>-->
          <!--<td><?php echo $transaction['tx_type']; ?></td>-->
          <td><?php echo $transaction['bk_name']; ?></td>
           <td><?php echo $transaction['hname']; ?></td>
          <td><?php echo $transaction['to_accno']; ?></td>
          <td>Debited</td>
          <td>$<?php echo $transaction['amount']; ?></td>
          <!--<td><?php echo $transaction['descrip']; ?></td>-->
          <td><?php echo $transaction['date']; ?></td>
        </tr>
      <?php endforeach; ?>
    <?php else : ?>
    <tr>
        <td colspan="9">No transactions found.</td>
    </tr>
<?php endif; ?>

        </table>
        <div class="footer">
          <p>Copyright reserve 2023</p></div>
        </div>
      </main>

      <!-- <div class="right">
        <div class="top">
          <button id="menu-btn">
            <span class="material-icons-sharp"> menu </span>
          </button>
          <div class="theme-toggler">
            <span class="material-icons-sharp active"> light_mode </span>
            <span class="material-icons-sharp"> dark_mode </span>
          </div> -->
          <!-- <div class="profile">
            <div class="info">
              <p>Hey, <b>Bruno</b></p>
              <small class="text-muted">Admin</small>
            </div>
            <div class="profile-photo">
              <img src="./images/profile-1.jpg" alt="Profile Picture" />
            </div>
          </div> -->
        <!-- </div>
        </div> -->


        
      </div>
    </div>

    <!-- <script src="./constants/recent-order-data.js"></script> -->
    <script src="./constants/update-data.js"></script>
    <script src="./constants/sales-analytics-data.js"></script>
    <script src="css/index.js"></script>
  </body>
</html>