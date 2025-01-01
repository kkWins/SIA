<?php
require_once '../db.php';

if (isset($_POST['po_id'])) {
    $poId = $_POST['po_id'];
    
    // Start transaction
    $db->begin_transaction();
    
    try {
        // Delete PO items first (due to foreign key constraints)
        $sql = "DELETE FROM PO_LIST WHERE PO_ID = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("s", $poId);
        $stmt->execute();
        
        // Delete PO header
        $sql = "DELETE FROM purchase_order WHERE PO_ID = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("s", $poId);
        $stmt->execute();
        
        $db->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $db->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No PO ID provided']);
}
?>