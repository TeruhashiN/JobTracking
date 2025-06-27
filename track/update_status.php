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
        throw new Exception("User not logged in. Please log in to update job applications.");
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
    
    // Validate required fields
    if (!isset($_POST['track_id']) || empty($_POST['track_id'])) {
        throw new Exception("Job application ID is required");
    }
    
    if (!isset($_POST['status']) || empty($_POST['status'])) {
        throw new Exception("Status is required");
    }
    
    $track_id = intval($_POST['track_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    if ($track_id <= 0) {
        throw new Exception("Invalid job application ID");
    }
    
    // Validate status
    $valid_statuses = ['Applied', 'Interview', 'On Progress', 'Accepted', 'Rejected'];
    if (!in_array($status, $valid_statuses)) {
        throw new Exception("Invalid status value");
    }
    
    // First check if the job belongs to the current user
    $check_query = "SELECT track_id, company, position, status FROM track WHERE track_id = ? AND userID = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    
    if (!$check_stmt) {
        throw new Exception("Database prepare failed: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($check_stmt, "ii", $track_id, $userID);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) === 0) {
        throw new Exception("Job application not found or you don't have permission to update it");
    }
    
    $job_data = mysqli_fetch_assoc($check_result);
    $old_status = $job_data['status'];
    mysqli_stmt_close($check_stmt);
    
    // Update the status
    $update_query = "UPDATE track SET status = ? WHERE track_id = ? AND userID = ?";
    $update_stmt = mysqli_prepare($conn, $update_query);
    
    if (!$update_stmt) {
        throw new Exception("Database prepare failed: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($update_stmt, "sii", $status, $track_id, $userID);
    
    if (mysqli_stmt_execute($update_stmt)) {
        $affected_rows = mysqli_stmt_affected_rows($update_stmt);
        
        if ($affected_rows > 0) {
            // Log the successful update
            error_log("Job status updated successfully: ID $track_id, User: $username, Company: {$job_data['company']}, Position: {$job_data['position']}, Status: $old_status -> $status");
            
            echo json_encode(array(
                'success' => true,
                'message' => "Status updated successfully from '$old_status' to '$status'",
                'track_id' => $track_id,
                'old_status' => $old_status,
                'new_status' => $status
            ));
        } else {
            // This might happen if the status was already the same
            echo json_encode(array(
                'success' => true,
                'message' => "Status is already set to '$status'",
                'track_id' => $track_id,
                'status' => $status
            ));
        }
    } else {
        throw new Exception("Failed to update status: " . mysqli_stmt_error($update_stmt));
    }
    
    mysqli_stmt_close($update_stmt);
    
} catch (Exception $e) {
    // Log the error
    error_log("Update status error: " . $e->getMessage());
    
    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => 'UPDATE_STATUS_ERROR'
    ));
} catch (mysqli_sql_exception $e) {
    // Handle specific database errors
    error_log("Database error in update_status.php: " . $e->getMessage());
    
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