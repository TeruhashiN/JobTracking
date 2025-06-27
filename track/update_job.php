<?php
session_start();
include '../database/db_connect.php';
// not yet used

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $track_id = intval($_POST['track_id']);
    $company = mysqli_real_escape_string($conn, $_POST['company']);
    $position = mysqli_real_escape_string($conn, $_POST['position']);
    $applied_date = mysqli_real_escape_string($conn, $_POST['applied_date']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $interview_date = !empty($_POST['interview_date']) ? mysqli_real_escape_string($conn, $_POST['interview_date']) : null;
    $follow_date = !empty($_POST['follow_date']) ? mysqli_real_escape_string($conn, $_POST['follow_date']) : null;
    $salary_range = !empty($_POST['salary_range']) ? mysqli_real_escape_string($conn, $_POST['salary_range']) : null;
    $job_type = !empty($_POST['job_type']) ? mysqli_real_escape_string($conn, $_POST['job_type']) : null;
    $notes = !empty($_POST['notes']) ? mysqli_real_escape_string($conn, $_POST['notes']) : null;
    
    $username = $_SESSION['username'];

    // Validate required fields
    if (empty($track_id) || empty($company) || empty($position) || empty($applied_date) || empty($status)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
        exit;
    }

    // Update job application (only if it belongs to the current user)
    $sql = "UPDATE track SET 
            company = ?, position = ?, applied_date = ?, status = ?, 
            interview_date = ?, follow_date = ?, salary_range = ?, job_type = ?, notes = ?
            WHERE track_id = ? AND username = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssssssssss", $company, $position, $applied_date, $status, $interview_date, $follow_date, $salary_range, $job_type, $notes, $track_id, $username);
        
        if (mysqli_stmt_execute($stmt)) {
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                echo json_encode(['success' => true, 'message' => 'Job application updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No changes made or job application not found']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating job application: ' . mysqli_error($conn)]);
        }
        
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error preparing statement: ' . mysqli_error($conn)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

mysqli_close($conn);
?>