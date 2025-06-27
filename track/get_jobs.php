<?php
session_start();
include '../database/db_connect.php';

header('Content-Type: application/json');

try {
    // Check if user is logged in (optional, based on your requirements)
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    // Build query - if you want to filter by user, add WHERE clause
    $query = "SELECT * FROM track ORDER BY applied_date DESC";
    
    // If you want to filter by logged-in user only, uncomment the line below
    // and make sure you have a user_id column in your track table
    // $query = "SELECT * FROM track WHERE user_id = ? ORDER BY applied_date DESC";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception("Query failed: " . mysqli_error($conn));
    }
    
    $jobs = array();
    
    while ($row = mysqli_fetch_assoc($result)) {
        $jobs[] = array(
            'track_id' => $row['track_id'],
            'company' => $row['company'],
            'position' => $row['position'],
            'applied_date' => $row['applied_date'],
            'status' => $row['status'],
            'interview_date' => $row['interview_date'],
            'follow_date' => $row['follow_date'],
            'salary_range' => $row['salary_range'],
            'job_type' => $row['job_type'],
            'notes' => $row['notes']
        );
    }
    
    echo json_encode(array(
        'success' => true,
        'data' => $jobs,
        'message' => 'Jobs loaded successfully'
    ));
    
} catch (Exception $e) {
    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage(),
        'data' => array()
    ));
}

mysqli_close($conn);
?>