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
        throw new Exception("User not logged in. Please log in to add job applications.");
    }
    
    // Get user ID from session or database
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
    
    // Get and sanitize POST data
    $company = trim(mysqli_real_escape_string($conn, $_POST['company']));
    $position = trim(mysqli_real_escape_string($conn, $_POST['position']));
    $applied_date = mysqli_real_escape_string($conn, $_POST['applied_date']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Handle optional fields - set to NULL if empty
    $interview_date = (!empty($_POST['interview_date'])) ? mysqli_real_escape_string($conn, $_POST['interview_date']) : null;
    $follow_date = (!empty($_POST['follow_date'])) ? mysqli_real_escape_string($conn, $_POST['follow_date']) : null;
    $salary_range = (!empty($_POST['salary_range'])) ? trim(mysqli_real_escape_string($conn, $_POST['salary_range'])) : null;
    $job_type = (!empty($_POST['job_type'])) ? mysqli_real_escape_string($conn, $_POST['job_type']) : null;
    $notes = (!empty($_POST['notes'])) ? trim(mysqli_real_escape_string($conn, $_POST['notes'])) : null;
    
    // Validate required fields
    if (empty($company)) {
        throw new Exception("Company name is required");
    }
    if (empty($position)) {
        throw new Exception("Position is required");
    }
    if (empty($applied_date)) {
        throw new Exception("Applied date is required");
    }
    if (empty($status)) {
        throw new Exception("Status is required");
    }
    
    // Additional validation: If status is Interview, interview_date should be provided
    if ($status === 'Interview' && empty($interview_date)) {
        throw new Exception("Interview date is required when status is set to Interview");
    }
    
    // Validate date formats
    if (!empty($applied_date) && !DateTime::createFromFormat('Y-m-d', $applied_date)) {
        throw new Exception("Invalid applied date format");
    }
    
    if (!empty($interview_date) && !DateTime::createFromFormat('Y-m-d\TH:i', $interview_date)) {
        throw new Exception("Invalid interview date format");
    }
    
    if (!empty($follow_date) && !DateTime::createFromFormat('Y-m-d', $follow_date)) {
        throw new Exception("Invalid follow-up date format");
    }
    
    // Validate status
    $valid_statuses = ['Applied', 'Interview', 'On Progress', 'Accepted', 'Rejected'];
    if (!in_array($status, $valid_statuses)) {
        throw new Exception("Invalid status value");
    }
    
    // Prepare SQL query - including userID
    $query = "INSERT INTO track (userID, company, position, applied_date, status, interview_date, follow_date, salary_range, job_type, notes, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        throw new Exception("Database prepare failed: " . mysqli_error($conn));
    }
    
    // Bind parameters - userID is integer, rest are strings
    mysqli_stmt_bind_param($stmt, "isssssssss", 
        $userID,
        $company, 
        $position, 
        $applied_date, 
        $status, 
        $interview_date, 
        $follow_date, 
        $salary_range, 
        $job_type, 
        $notes
    );
    
    if (mysqli_stmt_execute($stmt)) {
        $new_id = mysqli_insert_id($conn);
        
        // Log the successful addition
        error_log("Job application added successfully: ID $new_id, User: $username, Company: $company, Position: $position");
        
        echo json_encode(array(
            'success' => true,
            'message' => "Job application for $position at $company added successfully!",
            'track_id' => $new_id,
            'data' => array(
                'userID' => $userID,
                'company' => $company,
                'position' => $position,
                'applied_date' => $applied_date,
                'status' => $status,
                'interview_date' => $interview_date,
                'follow_date' => $follow_date,
                'salary_range' => $salary_range,
                'job_type' => $job_type,
                'notes' => $notes
            )
        ));
    } else {
        throw new Exception("Failed to execute query: " . mysqli_stmt_error($stmt));
    }
    
    mysqli_stmt_close($stmt);
    
} catch (Exception $e) {
    // Log the error
    error_log("Add job error: " . $e->getMessage());
    
    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => 'ADD_JOB_ERROR'
    ));
} catch (mysqli_sql_exception $e) {
    // Handle specific database errors
    error_log("Database error in add_job.php: " . $e->getMessage());
    
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