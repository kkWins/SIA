<?php
session_start();
require_once '../db.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['po_id'])) {
    $po_id = $_POST['po_id'];
    
    try {
        // Update the PO status to completed
        $stmt = $db->prepare("UPDATE purchase_order SET PO_STATUS = 'completed' WHERE PO_ID = ?");
        $stmt->bind_param("i", $po_id);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Purchase order completed successfully';
        } else {
            $response['message'] = 'Error updating purchase order status';
        }
        
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request';
}

header('Content-Type: application/json');
echo json_encode($response); 