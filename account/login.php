<?php
session_start();
include '../database/db_connect.php'; // DB connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password']; // Don't hash this yet

    $query = "SELECT * FROM account WHERE username = '$username'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);

        // Verify hashed password
        if (password_verify($password, $row['password'])) {
            // Success: Store session
            $_SESSION['userID'] = $row['userID'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['gender'] = $row['gender'];
            $_SESSION['currency'] = $row['currency'];

            
            header("Location: ../function/dashboard.php?login=success");
            exit;
        } else {
            // Wrong password
            header("Location: ../function/dashboard.php?login=invalid");
            exit;
        }
    } else {
        // Username not found
        header("Location: ../function/dashboard.php?login=invalid");
        exit;
    }
}
?>
