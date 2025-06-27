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
    $company = mysqli_real_escape_string($conn, $_POST['company']);
    $position = mysqli_real_escape_string($conn, $_POST['position']);
    $applied_date = mysqli_real_escape_string($conn, $_POST['applied_date']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $interview_date = !empty($_POST['interview_date']) ? mysqli_real_escape_string($conn, $_POST['interview_date']) : null;
    $follow_date = !empty($_POST['follow_date']) ? mysqli_real_escape_string($conn, $_POST['follow_date']) : null;
    $salary_range = !empty($_POST['salary_range']) ? mysqli_real_escape_string($conn, $_POST['salary_range']) : null;
    $job_type = !empty($_POST['job_type']) ? mysqli_real_escape_string($conn, $_POST['job_type']) : null;
    $notes = !empty($_POST['notes']) ? mysqli_real_escape_string($conn, $_POST['notes']) : null;
    
    // Validate required fields
    if (empty($company) || empty($position) || empty($applied_date) || empty($status)) {
        throw new Exception("Please fill in all required fields");
    }
    
    // Prepare SQL query
    $query = "INSERT INTO track (company, position, applied_date, status, interview_date, follow_date, salary_range, job_type, notes) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "sssssssss", 
        $company, $position, $applied_date, $status, 
        $interview_date, $follow_date, $salary_range, $job_type, $notes
    );
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(array(
            'success' => true,
            'message' => 'Job application added successfully',
            'track_id' => mysqli_insert_id($conn)
        ));
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