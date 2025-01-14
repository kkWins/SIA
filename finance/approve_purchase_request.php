<?php
session_start();
require_once '../db.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['po_id'])) {
    try {
        $po_id = $_POST['po_id'];
        $emp_id = $_SESSION['ID']; // Assuming emp_id is stored in session

        error_log($po_id . " " . $emp_id);

        $db->begin_transaction();

        // Insert into APPROVAL table
        $stmt = $db->prepare("INSERT INTO APPROVAL (ap_desc, ap_date, emp_id) VALUES ('Approved', NOW(), ?)");
        $stmt->bind_param("i", $emp_id);
        $stmt->execute();
        $ap_id = $db->insert_id;

        // Update PURCHASE_ORDER table
        $stmt = $db->prepare("UPDATE PURCHASE_ORDER SET PO_STATUS = 'approved', ap_id = ? WHERE PO_ID = ?");
        $stmt->bind_param("is", $ap_id, $po_id);
        $stmt->execute();

        $db->commit();
        $response['success'] = true;
        $response['message'] = 'Purchase order approved successfully';

    } catch (Exception $e) {
        $db->rollback();
        $response['message'] = 'Error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request';
}

echo json_encode($response);
?>