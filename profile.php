<?php
session_start();
// Include the database connection file
include './connect.php';
// include './logout.php';

// Initialize a session variable to store the previous balance if available
$prevBalance = isset($_SESSION['prev_balance']) ? floatval($_SESSION['prev_balance']) : null;



// Check if the user is logged in and has valid session data
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Redirect the user to the login page if not logged in
    header("Location: login.php");
    exit();
}

// Retrieve the user's data from the session and store them in variables
$fname = $_SESSION['fname'] ?? '';
$lname = $_SESSION['lname'] ?? '';
$gender = $_SESSION['gender'] ?? '';
$dob = $_SESSION['dob'] ?? '';
$phone = $_SESSION['phone'] ?? '';
$address = $_SESSION['address'] ?? '';
$country = $_SESSION['country'] ?? '';
$state = $_SESSION['state'] ?? '';
$city = $_SESSION['city'] ?? '';
$zipcode = $_SESSION['zipcode'] ?? '';
$accnum = $_SESSION['accnum'] ?? '';
$rtn = $_SESSION['rtn'] ?? '';
$pin = $_SESSION['pin'] ?? '';
$ssn = $_SESSION['ssn'] ?? '';
// $acctype = $_SESSION['acctype'] ?? '';
$balance = $_SESSION['balance'] ?? '';
$acctype = isset($_SESSION['acctype']) ? $_SESSION['acctype'] : ''; // Check if 'acctype' key exists in the session


// Fetch the user's balance from the database based on their account number
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
          <a href="profile.php" class="active">
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

        
                        <!-- Add this div to display the "Account credited" message -->
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
<div class="recent-orders">
          <div class="order">
            <h2>Profile</h2>
            <table id="recent-orders--table">
              <thead>
                <tr>
                  <th>Field</th>
                  <th>Value</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Firstname</td>
                  <td><?php echo $_SESSION['fname']; ?></td>
                </tr>
                <tr>
                  <td>Lastname</td>
                  <td><?php echo $_SESSION['lname']; ?></td>
                </tr>
                <tr>
                  <td>Email</td>
                  <td><?php echo $_SESSION['email']; ?></td>
                </tr>
                <tr>
                  <td>Phone</td>
                  <td><?php echo $_SESSION['phone']; ?></td>
                </tr>
                <tr>
                  <td>Gender</td>
                  <td><?php echo $_SESSION['gender']; ?></td>
                </tr>
                <tr>
                  <td>Address</td>
                  <td><?php echo $_SESSION['address']; ?></td>
                </tr>
                <tr>
                  <td>City</td>
                  <td><?php echo $_SESSION['city']; ?></td>
                </tr>
                <tr>
                  <td>State</td>
                  <td><?php echo $_SESSION['state']; ?></td>
                </tr>
                <tr>
                  <td>Country</td>
                  <td><?php echo $_SESSION['country']; ?></td>
                </tr>
                <tr>
                  <td>Zip</td>
                  <td><?php echo $_SESSION['zipcode']; ?></td>
                </tr>
                <tr>
                  <td>SSN</td>
                  <td><?php echo str_repeat('*', strlen($_SESSION['ssn']) - 4) . substr($_SESSION['ssn'], -4); ?></td>
                </tr>
                <tr>
                  <td>Account Type</td>
                  <td><?php echo $_SESSION['acctype']; ?></td>
                </tr>
                <tr>
                  <td>Account Pin</td>
                  <td><?php echo $_SESSION['pin']; ?></td>
                </tr>
                <tr>
                  <td>Routing Number</td>
                  <td><?php echo $_SESSION['rtn']; ?></td>
                </tr>
                <tr>
                  <td>Account Number</td>
                  <td><?php echo $_SESSION['accnum']; ?></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="footer">
          <p href="#">Copyright reserve 2023</p>
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
