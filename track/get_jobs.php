<?php
session_start();
include '../database/db_connect.php';

header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['username'])) {
        throw new Exception("User not logged in. Please log in to view job applications.");
    }
    
    // Get user ID from session or database
    $username = $_SESSION['username'];
    $user_query = "SELECT userID, currency FROM account WHERE username = ?";
    $user_stmt = mysqli_prepare($conn, $user_query);
    
    if (!$user_stmt) {
        throw new Exception("Database prepare failed: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($user_stmt, "s", $username);
    mysqli_stmt_execute($user_stmt);
    $user_result = mysqli_stmt_get_result($user_stmt);
    
    if (mysqli_num_rows($user_result) === 0) {
        throw new Exception("User not found. Please log in again.");
    }
    
    $user_row = mysqli_fetch_assoc($user_result);
    $userID = $user_row['userID'];
    $currency = $user_row['currency']; // <-- Currency
    mysqli_stmt_close($user_stmt);
    
    // Fetch job applications for the current user only
    $query = "SELECT track_id, company, position, applied_date, status, interview_date, follow_date, salary_range, job_type, notes, created_at 
              FROM track 
              WHERE userID = ? 
              ORDER BY created_at DESC, applied_date DESC";
    
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        throw new Exception("Database prepare failed: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "i", $userID);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Failed to execute query: " . mysqli_stmt_error($stmt));
    }
    
    $result = mysqli_stmt_get_result($stmt);
    $jobs = array();
    
    while ($row = mysqli_fetch_assoc($result)) {
        // Format dates properly
        $row['applied_date'] = date('Y-m-d', strtotime($row['applied_date']));
        
        if (!empty($row['interview_date'])) {
            $row['interview_date'] = date('Y-m-d H:i:s', strtotime($row['interview_date']));
        }
        
        if (!empty($row['follow_date'])) {
            $row['follow_date'] = date('Y-m-d', strtotime($row['follow_date']));
        }
        
        // Add currency prefix to salary_range if present
        if (!empty($row['salary_range'])) {
            $row['salary_range'] = $currency . number_format($row['salary_range']);
        }

        $jobs[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    
    echo json_encode(array(
        'success' => true,
        'data' => $jobs,
        'count' => count($jobs),
        'user' => $username
    ));
    
} catch (Exception $e) {
    // Log the error
    error_log("Get jobs error: " . $e->getMessage());
    
    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => 'GET_JOBS_ERROR',
        'data' => array()
    ));
} catch (mysqli_sql_exception $e) {
    // Handle specific database errors
    error_log("Database error in get_jobs.php: " . $e->getMessage());
    
    echo json_encode(array(
        'success' => false,
        'message' => 'Database error occurred. Please try again.',
        'error_code' => 'DATABASE_ERROR',
        'data' => array()
    ));
}

// Close database connection
if (isset($conn)) {
    mysqli_close($conn);
}
?>