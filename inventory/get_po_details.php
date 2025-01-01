<?php
header('Content-Type: application/json');
require_once '../db.php';  // Adjust path as needed

if (isset($_GET['po_id'])) {
    $poId = $_GET['po_id'];
    
    try {
        // First query to get PO details and supplier info
        $sql = "SELECT 
            po.*,
            supplier.SP_NAME,
            supplier.SP_ADDRESS,
            supplier.SP_NUMBER
        FROM 
            purchase_order po
        LEFT JOIN 
            supplier ON po.SP_ID = supplier.SP_ID
        WHERE 
            po.PO_ID = ?";

        $stmt = $db->prepare($sql);
        $stmt->bind_param("s", $poId);
        $stmt->execute();
        $poDetails = $stmt->get_result()->fetch_assoc();

        if (!$poDetails) {
            throw new Exception('Purchase order not found');
        }

        // Second query to get items
        $sql = "SELECT 
            po_list.*,
            inventory.INV_MODEL_NAME,
            inventory.INV_BRAND
        FROM 
            PO_LIST po_list
        JOIN 
            INVENTORY inventory ON po_list.INV_ID = inventory.INV_ID
        WHERE 
            po_list.PO_ID = ?";

        $stmt = $db->prepare($sql);
        $stmt->bind_param("s", $poId);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        echo json_encode([
            'success' => true,
            'po_details' => $poDetails,
            'items' => $items
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No PO ID provided'
    ]);
}
?>