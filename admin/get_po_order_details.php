<?php
require_once 'db.php';

if (isset($_GET['po_id'])) {
    $po_id = $_GET['po_id'];
    
    // Get purchase order details
    $query = "SELECT po.*, sp.*, 
		pd.PD_PAYMENT_TYPE,
        pd.PD_CHANGE,
        pd.PD_AMMOUNT,
		approval.ap_date,
        CONCAT(emp.EMP_FNAME, ' ', emp.EMP_LNAME) AS deliverTo,
        emp.EMP_NUMBER
FROM purchase_order po
LEFT JOIN supplier sp ON po.SP_ID = sp.SP_ID
LEFT JOIN approval ON approval.ap_id = po.ap_id
LEFT JOIN 
    	employee emp ON emp.EMP_ID = po.EMP_ID
LEFT JOIN 
		payment_details pd ON pd.PO_ID = po.PO_ID
WHERE po.PO_ID = ? AND po.PO_STATUS IN ('approved', 'completed', 'canceled')";
    
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