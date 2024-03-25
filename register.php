<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include './connect.php';
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="auth/style.css">
    <title>BOW</title>
</head>
<body>
    <div class="container">
        <!-- Step 1: Personal Information -->
        <div class="box form-box" id="step1">
        <form action="register_process.php" method="post">
            <header>Sign Up - Step 1</header>
            <div class="two-forms">
                <div class="field input">
                    <label>Firstname</label>
                    <input type="text" name="fname" id="firstname" autocomplete="off" required>
                </div>
                
                <div class="field input">
                    <label>Lastname</label>
                    <input type="text" name="lname" id="lastname" autocomplete="off" required>
                </div>
                </div>

                <div class="field input">
                    <label>Email</label>
                    <input type="email" name="email" id="email" autocomplete="off" required>
                </div>

                <div class="two-forms">
                <div class="field input">
                    <label>Password</label>
                    <input type="password" name="password" id="password" autocomplete="off" required>
                </div>

                <div class="field input">
                    <label>Confirm Password</label>
                    <input type="password" name="cpassword" id="cpassword" autocomplete="off" required>
                </div>
                </div>

                <div class="input-box">
                <label>Gender</label>
                    <select id="gender" name="gender" class="input-field" required>
                        <option value="" disabled selected>Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                    <i class="bx bx-user"></i>
                </div>

                <div class="field input">
                    <label>Date of Birth</label>
                    <input type="text" name="dob" id="dob" autocomplete="off" required>
                </div>

                <div class="field input">
                    <label>Phone Number</label>
                    <input type="text" name="phone" id="phone" autocomplete="off" required>
                </div>

                <div class="field">
                    <input type="button" class="btn" value="Next" onclick="nextStep(2)">
                </div>
        </div>

        <!-- Step 2: Password -->
        <div class="box form-box" id="step2" style="display: none;">
            <header>Sign Up - Step 2</header>
                <div class="field input">
                    <label>Address</label>
                    <input type="text" name="address" id="address" autocomplete="off" required>
                </div>

                <div class="two-forms">
                <div class="field input">
                    <label>Country</label>
                    <input type="text" name="country" id="country" autocomplete="off" required>
                </div>

                <div class="field input">
                    <label>State</label>
                    <input type="text" name="state" id="state" autocomplete="off" required>
                </div>
                </div>


                <div class="two-forms">
                <div class="field input">
                    <label>City</label>
                    <input type="text" name="city" id="city" autocomplete="off" required>
                </div>

                <div class="field input">
                    <label>Zipcode</label>
                    <input type="text" name="zipcode" id="zipcode" autocomplete="off" required>
                </div>
                </div>

                <div class="field">
                    <input type="button" class="btn" value="Next" onclick="nextStep(3)">
                </div>

                <div class="field">
                    <input type="button" class="btn" value="Previous" onclick="previousStep(1)">
                </div>
        </div>

                <!-- Step 3: Password -->
        <div class="box form-box" id="step3" style="display: none;">
            <header>Sign Up - Step 3</header>
                <div class="field input">
                    <label>Routing Number</label>
                    <input type="text" name="rtn" id="rtn" autocomplete="off" required>
                </div>

                <div class="two-forms">
                <div class="field input">
                    <label>PIN</label>
                    <input type="password" name="pin" id="pin" autocomplete="off" required>
                </div>

                <div class="field input">
                    <label>SSN</label>
                    <input type="text" name="ssn" id="ssn" autocomplete="off" required>
                </div>
                </div>

                <div class="input-box">
                <label>Account Type</label>
                <select id="acctype" name="acctype" class="input-field" required>
                    <option value="" disabled selected>Select Account Type</option>
                    <option value="Checking">Checking</option>
                    <option value="Saving">Saving</option>
                    <option value="Fixed">Fixed deposit</option>
                </select>
                <i class="bx bx-building"></i>
            </div>

                <div class="field">
                    <input type="submit" class="btn" name="register" value="Register" required>
                </div>

                <div class="field">
                    <input type="button" class="btn" value="Previous" onclick="previousStep(2)">
                </div>
                <div class="links">
                    Already a member? <a href="login.php">Sign In</a>
                </div>
        </div>
        </form>
    </div>

    <script>
        let currentStep = 1;
        const steps = document.querySelectorAll('.form-box');

        function nextStep(step) {
            steps[currentStep - 1].style.display = 'none';
            steps[step - 1].style.display = 'block';
            currentStep = step;
        }

        function previousStep(step) {
            steps[currentStep - 1].style.display = 'none';
            steps[step - 1].style.display = 'block';
            currentStep = step;
        }
    </script>
</body>
</html>