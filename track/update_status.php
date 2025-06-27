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
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Validate required fields
    if (empty($track_id) || empty($status)) {
        throw new Exception("Missing required fields");
    }
    
    // Validate status value
    $valid_statuses = array('Applied', 'Interview', 'On Progress', 'Accepted', 'Rejected');
    if (!in_array($status, $valid_statuses)) {
        throw new Exception("Invalid status value");
    }
    
    // Prepare SQL query
    $query = "UPDATE track SET status = ? WHERE track_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "si", $status, $track_id);
    
    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            echo json_encode(array(
                'success' => true,
                'message' => 'Status updated successfully'
            ));
        } else {
            throw new Exception("No rows were updated. Job application may not exist.");
        }
    } else {
        throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
    }
    
    mysqli_stmt_close($stmt);
    
} catch (Exception $e) {
    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage()
    ));
}

mysqli_close($conn);
?>