<?php
session_start();
include '../database/db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('success' => false, 'message' => 'Invalid request method'));
    exit;
}

try {
    // Get POST data
    $track_id = (int)$_POST['track_id'];
    
    // Validate required fields
    if (empty($track_id)) {
        throw new Exception("Missing track ID");
    }
    
    // First check if the record exists
    $check_query = "SELECT track_id FROM track WHERE track_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "i", $track_id);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($result) === 0) {
        throw new Exception("Job application not found");
    }
    
    mysqli_stmt_close($check_stmt);
    
    // Prepare delete query
    $delete_query = "DELETE FROM track WHERE track_id = ?";
    $delete_stmt = mysqli_prepare($conn, $delete_query);
    
    if (!$delete_stmt) {
        throw new Exception("Prepare failed: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($delete_stmt, "i", $track_id);
    
    if (mysqli_stmt_execute($delete_stmt)) {
        if (mysqli_stmt_affected_rows($delete_stmt) > 0) {
            echo json_encode(array(
                'success' => true,
                'message' => 'Job application deleted successfully'
            ));
        } else {
            throw new Exception("Failed to delete job application");
        }
    } else {
        throw new Exception("Execute failed: " . mysqli_stmt_error($delete_stmt));
    }
    
    mysqli_stmt_close($delete_stmt);
    
} catch (Exception $e) {
    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage()
    ));
}

mysqli_close($conn);
?>