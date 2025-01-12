<?php
require_once 'db.php';

if (isset($_GET['po_id'])) {
    $po_id = $_GET['po_id'];
    
    // Get purchase order details
    $query = "SELECT po.*, sp.* 
              FROM purchase_order po
              JOIN supplier sp ON po.SP_ID = sp.SP_ID
              WHERE po.PO_ID = ? AND po.PO_STATUS = 'approved'";
    
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $po_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $poDetails = $result->fetch_assoc();
        
        // Get items in the purchase order
        $items_query = "SELECT pl.*, inv.INV_MODEL_NAME, inv.INV_BRAND
                       FROM po_list pl
                       JOIN inventory inv ON pl.INV_ID = inv.INV_ID
                       WHERE pl.PO_ID = ?";
        
        $stmt = $db->prepare($items_query);
        $stmt->bind_param("i", $po_id);
        $stmt->execute();
        $items_result = $stmt->get_result();
        
        $poDetails['items'] = [];
        while ($item = $items_result->fetch_assoc()) {
            $poDetails['items'][] = $item;
        }
    } else {
        $poDetails = null;
    }
    
    $stmt->close();
}
?> 