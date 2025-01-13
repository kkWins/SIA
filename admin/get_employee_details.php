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
    if (!isset($_GET['emp_id'])) {
        throw new Exception('Employee ID is required');
    }

    $emp_id = $_GET['emp_id'];

    $query = "SELECT 
                E.EMP_ID,
                E.EMP_FNAME,
                E.EMP_LNAME,
                E.EMP_EMAIL,
                E.EMP_POSITION,
                D.DEPT_NAME,
                E.EMP_NUMBER
            FROM EMPLOYEE E
            LEFT JOIN DEPARTMENT D ON E.DEPT_ID = D.DEPT_ID
            WHERE E.EMP_ID = ?";

    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $emp_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Employee not found');
    }
    
    $employee = $result->fetch_assoc();
    echo json_encode(['status' => 'success', 'data' => $employee]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    if (isset($db)) {
        $db->close();
    }
}
?> 