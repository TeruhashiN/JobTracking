<?php
include '../database/db_connect.php'; // Connect to database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure hash
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $role = 'user'; // default role
    $currency = '₱'; // default currency

    // Check if username already exists
    $check = "SELECT * FROM account WHERE username = '$username'";
    $result = mysqli_query($conn, $check);

    if (mysqli_num_rows($result) > 0) {
        header("Location: ../function/dashboard.php?register=exists");
        exit;

    } else {
        $sql = "INSERT INTO account (username, password, gender, role, currency) 
                VALUES ('$username', '$password', '$gender', '$role', '$currency')";

        if (mysqli_query($conn, $sql)) {
            header("Location: ../function/dashboard.php?register=success");
            exit;
        } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
    }

    mysqli_close($conn);
}
?>
