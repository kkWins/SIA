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
    if (!isset($_POST['emp_id']) || !isset($_POST['status'])) {
        throw new Exception('Employee ID and status are required');
    }

    $emp_id = intval($_POST['emp_id']);
    $new_status = $_POST['status']; // Don't convert to integer for ENUM
    error_log("emp_id: " . $emp_id);
    error_log("new_status: " . $new_status);

    // Validate status value
    if ($new_status !== '0' && $new_status !== '1') {
        throw new Exception('Invalid status value');
    }

    // Check if employee exists
    $check_query = "SELECT EMP_STATUS FROM EMPLOYEE WHERE EMP_ID = ?";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bind_param("i", $emp_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Employee not found');
    }

    // Update employee status
    $query = "UPDATE EMPLOYEE SET EMP_STATUS = ? WHERE EMP_ID = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("si", $new_status, $emp_id); // Changed to "si" for string and integer
    
    if ($stmt->execute()) {
        $action = $new_status == '1' ? 'activated' : 'deactivated';
        echo json_encode(['status' => 'success', 'message' => "Employee {$action} successfully"]);
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