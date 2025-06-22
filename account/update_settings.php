<?php
session_start();
include '../database/db_connect.php';

$userID = $_SESSION['userID'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $userID) {
    $newGender = $_POST['gender'];
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];

    // Update gender unconditionally
    $genderUpdateQuery = "UPDATE account SET gender = ? WHERE userID = ?";
    $stmt = mysqli_prepare($conn, $genderUpdateQuery);
    mysqli_stmt_bind_param($stmt, 'si', $newGender, $userID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $_SESSION['gender'] = $newGender;

    // Update password only if both fields are filled
    if (!empty($currentPassword) && !empty($newPassword)) {
        // Get current hashed password from DB
        $query = "SELECT password FROM account WHERE userID = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $userID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $hashedPassword);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if (password_verify($currentPassword, $hashedPassword)) {
            $newHashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $updatePassQuery = "UPDATE account SET password = ? WHERE userID = ?";
            $stmt = mysqli_prepare($conn, $updatePassQuery);
            mysqli_stmt_bind_param($stmt, 'si', $newHashed, $userID);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            header("Location: ../function/dashboard.php?settings=wrongpass");
            exit;
        }
    }

    header("Location: ../function/dashboard.php?settings=success");
    exit;
} else {
    header("Location: ../function/dashboard.php?settings=unauthorized");
    exit;
}
?>
