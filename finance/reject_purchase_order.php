<?php
session_start();
require_once '../db.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['po_id']) && isset($_POST['reject_reason'])) {
    try {
        $po_id = $_POST['po_id'];
        $reject_reason = $_POST['reject_reason'];
        $emp_id = $_SESSION['ID']; // Assuming emp_id is stored in session

        $db->begin_transaction();

        // Insert into APPROVAL table
        $stmt = $db->prepare("INSERT INTO APPROVAL (ap_desc, emp_id) VALUES (?, ?)");
        $stmt->bind_param("si", $reject_reason, $emp_id);
        $stmt->execute();
        $ap_id = $db->insert_id;

        // Update PURCHASE_ORDER table
        $stmt = $db->prepare("UPDATE PURCHASE_ORDER SET PO_STATUS = 'rejected', ap_id = ? WHERE PO_ID = ?");
        $stmt->bind_param("is", $ap_id, $po_id);
        $stmt->execute();

        $db->commit();
        $response['success'] = true;
        $response['message'] = 'Purchase order rejected successfully';

    } catch (Exception $e) {
        $db->rollback();
        $response['message'] = 'Error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request';
}

echo json_encode($response);
?> 