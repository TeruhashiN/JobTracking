<?php
// Create this new file: ../track/get_job_details.php
session_start();
include '../database/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(array('success' => false, 'message' => 'Invalid request method'));
    exit;
}

try {
    // Check if user is logged in
    if (!isset($_SESSION['username'])) {
        throw new Exception("User not logged in");
    }
    
    // Get user ID
    $username = $_SESSION['username'];
    $user_query = "SELECT userID FROM account WHERE username = ?";
    $user_stmt = mysqli_prepare($conn, $user_query);
    mysqli_stmt_bind_param($user_stmt, "s", $username);
    mysqli_stmt_execute($user_stmt);
    $user_result = mysqli_stmt_get_result($user_stmt);
    
    if (mysqli_num_rows($user_result) === 0) {
        throw new Exception("User not found");
    }
    
    $user_row = mysqli_fetch_assoc($user_result);
    $userID = $user_row['userID'];
    mysqli_stmt_close($user_stmt);
    
    // Get track_id from request
    if (!isset($_GET['track_id']) || empty($_GET['track_id'])) {
        throw new Exception("Job ID is required");
    }
    
    $track_id = (int)$_GET['track_id'];
    
    // Fetch job details
    $query = "SELECT track_id, company, position, applied_date, status, interview_date, follow_date, salary_range, job_type, notes 
              FROM track 
              WHERE track_id = ? AND userID = ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $track_id, $userID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        throw new Exception("Job application not found");
    }
    
    $job = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    // Format dates for HTML input fields
    if ($job['interview_date']) {
        $job['interview_date'] = date('Y-m-d\TH:i', strtotime($job['interview_date']));
    }
    
    if ($job['applied_date']) {
        $job['applied_date'] = date('Y-m-d', strtotime($job['applied_date']));
    }
    
    if ($job['follow_date']) {
        $job['follow_date'] = date('Y-m-d', strtotime($job['follow_date']));
    }
    
    echo json_encode(array(
        'success' => true,
        'data' => $job
    ));
    
} catch (Exception $e) {
    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage()
    ));
}

mysqli_close($conn);
?>