<?php
session_start();
require_once '../db.php';

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['loggedIn']) || !$_SESSION['loggedIn']) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['req_id']) && isset($_POST['reason'])) {
    $req_id = $_POST['req_id'];
    $reason = $_POST['reason'];
    error_log($reason);
    
    // Start transaction
    $db->begin_transaction();
    
    try {
        // First, insert into approval table
        $emp_id = $_SESSION['ID'];
        $stmt = $db->prepare("INSERT INTO approval (AP_DESC, AP_DATE, EMP_ID) VALUES (?, NOW(), ?)");
        $stmt->bind_param("si", $reason, $emp_id);
        $stmt->execute();
        
        // Get the last inserted approval ID
        $ap_id = $db->insert_id;
        
        // Then update requisition with status and the approval ID
        $stmt = $db->prepare("UPDATE purchase_or_requisition_form SET PRF_STATUS = 'rejected', AP_ID = ? WHERE PRF_ID = ?");
        $stmt->bind_param("is", $ap_id, $req_id);
        
        if ($stmt->execute()) {
            $db->commit();
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("Failed to update requisition status");
        }
    } catch (Exception $e) {
        $db->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

$db->close();
?> 