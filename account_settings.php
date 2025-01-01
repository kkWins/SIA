<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    try {
        $emp_id = $_SESSION['ID'];
        $fname = filter_input(INPUT_POST, 'emp_fname', FILTER_SANITIZE_STRING);
        $lname = filter_input(INPUT_POST, 'emp_lname', FILTER_SANITIZE_STRING);
        $mname = filter_input(INPUT_POST, 'emp_mname', FILTER_SANITIZE_STRING);
        $address = filter_input(INPUT_POST, 'emp_address', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'emp_email', FILTER_VALIDATE_EMAIL);
        $number = filter_input(INPUT_POST, 'emp_number', FILTER_SANITIZE_STRING);
        $password = $_POST['emp_password'] ?? '';

        $sql = "UPDATE employee SET 
                EMP_FNAME = ?, 
                EMP_LNAME = ?, 
                EMP_MNAME = ?, 
                EMP_ADDRESS = ?, 
                EMP_EMAIL = ?, 
                EMP_NUMBER = ?";
        $params = [$fname, $lname, $mname, $address, $email, $number];
        $types = "ssssss";

        if (!empty($password)) {
            $sql .= ", EMP_PASSWORD = ?";
            $params[] = $password;
            $types .= "s";
        }

        $sql .= " WHERE EMP_ID = ?";
        $params[] = $emp_id;
        $types .= "s";

        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            $_SESSION['emp_fname'] = $fname;
            $_SESSION['emp_lname'] = $lname;
            $_SESSION['emp_email'] = $email;
            echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update profile']);
        }
        exit;
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }
}

$stmt = $db->prepare("SELECT * FROM employee WHERE EMP_ID = ?");
$stmt->bind_param("s", $_SESSION['ID']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

?>