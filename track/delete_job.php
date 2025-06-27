<?php
session_start();
include '../database/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('success' => false, 'message' => 'Invalid request method'));
    exit;
}

try {
    // Check if user is logged in
    if (!isset($_SESSION['username'])) {
        throw new Exception("User not logged in. Please log in to delete job applications.");
    }
    
    // Get user ID from session
    $username = $_SESSION['username'];
    $user_query = "SELECT userID FROM account WHERE username = ?";
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
    mysqli_stmt_close($user_stmt);
    
    // Validate track_id
    if (!isset($_POST['track_id']) || empty($_POST['track_id'])) {
        throw new Exception("Job application ID is required");
    }
    
    $track_id = intval($_POST['track_id']);
    
    if ($track_id <= 0) {
        throw new Exception("Invalid job application ID");
    }
    
    // First check if the job belongs to the current user
    $check_query = "SELECT track_id, company, position FROM track WHERE track_id = ? AND userID = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    
    if (!$check_stmt) {
        throw new Exception("Database prepare failed: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($check_stmt, "ii", $track_id, $userID);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) === 0) {
        throw new Exception("Job application not found or you don't have permission to delete it");
    }
    
    $job_data = mysqli_fetch_assoc($check_result);
    mysqli_stmt_close($check_stmt);
    
    // Delete the job application
    $delete_query = "DELETE FROM track WHERE track_id = ? AND userID = ?";
    $delete_stmt = mysqli_prepare($conn, $delete_query);
    
    if (!$delete_stmt) {
        throw new Exception("Database prepare failed: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($delete_stmt, "ii", $track_id, $userID);
    
    if (mysqli_stmt_execute($delete_stmt)) {
        $affected_rows = mysqli_stmt_affected_rows($delete_stmt);
        
        if ($affected_rows > 0) {
            // Log the successful deletion
            error_log("Job application deleted successfully: ID $track_id, User: $username, Company: {$job_data['company']}, Position: {$job_data['position']}");
            
            echo json_encode(array(
                'success' => true,
                'message' => "Job application for {$job_data['position']} at {$job_data['company']} deleted successfully!",
                'deleted_id' => $track_id
            ));
        } else {
            throw new Exception("No job application was deleted. It may have already been removed.");
        }
    } else {
        throw new Exception("Failed to delete job application: " . mysqli_stmt_error($delete_stmt));
    }
    
    mysqli_stmt_close($delete_stmt);
    
} catch (Exception $e) {
    // Log the error
    error_log("Delete job error: " . $e->getMessage());
    
    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => 'DELETE_JOB_ERROR'
    ));
} catch (mysqli_sql_exception $e) {
    // Handle specific database errors
    error_log("Database error in delete_job.php: " . $e->getMessage());
    
    echo json_encode(array(
        'success' => false,
        'message' => 'Database error occurred. Please try again.',
        'error_code' => 'DATABASE_ERROR'
    ));
}

// Close database connection
if (isset($conn)) {
    mysqli_close($conn);
}
?>