<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json');

// Check if user is logged in and is an Admin
if (!isset($_SESSION['loggedIn']) || $_SESSION['role'] !== 'Admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

try {
    // Validate required fields
    if (empty($_POST['emp_id']) || empty($_POST['emp_fname']) || empty($_POST['emp_lname']) || 
        empty($_POST['emp_email']) || empty($_POST['department']) || 
        empty($_POST['position']) || empty($_POST['contact_no'])) {
        throw new Exception('All fields are required except password');
    }

    $emp_id = $_POST['emp_id'];
    $fname = $_POST['emp_fname'];
    $lname = $_POST['emp_lname'];
    $email = $_POST['emp_email'];
    $department = $_POST['department'];
    $position = $_POST['position'];
    $contact = $_POST['contact_no'];

    // Check if email exists for other employees
    $email_check = "SELECT COUNT(*) as count FROM EMPLOYEE WHERE EMP_EMAIL = ? AND EMP_ID != ?";
    $check_stmt = $db->prepare($email_check);
    $check_stmt->bind_param("si", $email, $emp_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        throw new Exception('Email already exists for another employee');
    }

    // Get department ID
    $dept_query = "SELECT DEPT_ID FROM DEPARTMENT WHERE DEPT_NAME = ?";
    $dept_stmt = $db->prepare($dept_query);
    $dept_stmt->bind_param("s", $department);
    $dept_stmt->execute();
    $dept_result = $dept_stmt->get_result();
    
    if ($dept_result->num_rows === 0) {
        throw new Exception('Invalid department selected');
    }
    
    $dept_row = $dept_result->fetch_assoc();
    $dept_id = $dept_row['DEPT_ID'];

    // Update query
    if (!empty($_POST['emp_password'])) {
        // Update with new password
        $password = password_hash($_POST['emp_password'], PASSWORD_DEFAULT);
        $query = "UPDATE EMPLOYEE SET 
                    EMP_FNAME = ?, 
                    EMP_LNAME = ?, 
                    EMP_EMAIL = ?, 
                    EMP_PASSWORD = ?,
                    DEPT_ID = ?, 
                    EMP_POSITION = ?, 
                    EMP_NUMBER = ?
                WHERE EMP_ID = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("sssssssi", $fname, $lname, $email, $password, $dept_id, $position, $contact, $emp_id);
    } else {
        // Update without changing password
        $query = "UPDATE EMPLOYEE SET 
                    EMP_FNAME = ?, 
                    EMP_LNAME = ?, 
                    EMP_EMAIL = ?, 
                    DEPT_ID = ?, 
                    EMP_POSITION = ?, 
                    EMP_NUMBER = ?
                WHERE EMP_ID = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("ssssssi", $fname, $lname, $email, $dept_id, $position, $contact, $emp_id);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Employee updated successfully']);
    } else {
        throw new Exception($db->error);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    if (isset($db)) {
        $db->close();
    }
}
?> 