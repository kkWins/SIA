<?php
require_once '../db.php';

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['po_id'])) {
    $poId = $data['po_id'];
    $vendorId = $data['vendor_id'];
    
    // Start transaction
    $db->begin_transaction();
    
    try {
        // Update PO header
        $sql = "UPDATE purchase_order SET SP_ID = ? WHERE PO_ID = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("ss", $vendorId, $poId);
        $stmt->execute();
        
        // Delete existing PO items
        $sql = "DELETE FROM PO_LIST WHERE PO_ID = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("s", $poId);
        $stmt->execute();
        
        // Insert new items
        $sql = "INSERT INTO PO_LIST (PO_ID, INV_ID, POL_QUANTITY, POL_PRICE) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        
        foreach ($data['items'] as $item) {
            $stmt->bind_param("ssdd", $poId, $item['item_id'], $item['quantity'], $item['price']);
            $stmt->execute();
        }
        
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