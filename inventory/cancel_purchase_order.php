<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['po_id'])) {
    $po_id = $_POST['po_id'];
    
    try {
        // Update the PO status to canceled
        $stmt = $db->prepare("UPDATE purchase_order SET PO_STATUS = 'canceled' WHERE PO_ID = ?");
        $stmt->bind_param("i", $po_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to cancel purchase order']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?> 