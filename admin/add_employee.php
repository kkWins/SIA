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
    // Validate if all required fields are present
    if (empty($_POST['emp_fname']) || empty($_POST['emp_lname']) || empty($_POST['emp_email']) || 
        empty($_POST['emp_password']) || empty($_POST['department']) || 
        empty($_POST['position']) || empty($_POST['contact_no'])) {
        throw new Exception('All fields are required');
    }

    // Get form data
    $fname = $_POST['emp_fname'];
    $lname = $_POST['emp_lname'];
    $email = $_POST['emp_email'];
    $password = $_POST['emp_password'];
    $department = $_POST['department'];
    $position = $_POST['position'];
    $contact = $_POST['contact_no'];

    // Check if email already exists
    $email_check = "SELECT COUNT(*) as count FROM EMPLOYEE WHERE EMP_EMAIL = ?";
    $check_stmt = $db->prepare($email_check);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        throw new Exception('Email already exists. Please use a different email.');
    }

    // Get department ID based on department name
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

    // Insert new employee
    $query = "INSERT INTO EMPLOYEE (EMP_FNAME, EMP_LNAME, EMP_EMAIL, EMP_PASSWORD, DEPT_ID, EMP_POSITION, EMP_NUMBER) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $db->prepare($query);
    $stmt->bind_param("sssssss", $fname, $lname, $email, $password, $dept_id, $position, $contact);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Employee added successfully']);
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