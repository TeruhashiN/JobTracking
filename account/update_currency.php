<?php
session_start();
include '../database/db_connect.php'; // Make sure your DB connection is here

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['currency']) && isset($_SESSION['userID'])) {
    $currency = mysqli_real_escape_string($conn, $_POST['currency']);
    $userID = $_SESSION['userID'];

    // Update database
    $query = "UPDATE account SET currency = '$currency' WHERE userID = '$userID'";
    if (mysqli_query($conn, $query)) {
        // Update session
        $_SESSION['currency'] = $currency;
        header('Location: ../function/dashboard.php?currency=success');
        exit;
    } else {
        header('Location: ../function/dashboard.php?currency=error');
        exit;
    }
} else {
    header('Location: ../function/dashboard.php?currency=error');
    exit;
}
