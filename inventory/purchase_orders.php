<?php

require_once 'db.php';

if(isset($_GET['po_id'])){
    $po_id = $_GET['po_id'];

    // First query to get PO details, supplier info, and approval info
    $sql = "SELECT 
        po.PO_ID,
        CONCAT(emp.EMP_FNAME, ' ', emp.EMP_LNAME) AS deliverTo,
        emp.EMP_NUMBER,
        po.PO_ORDER_DATE,
        po.PO_ARRIVAL_DATE,
        po.PO_STATUS,
        supplier.SP_NAME,
        supplier.SP_ADDRESS,
        supplier.SP_NUMBER,
        approval.ap_desc,
        approval.ap_date,
        pd.PD_PAYMENT_TYPE,
        pd.PD_CHANGE,
        pd.PD_AMMOUNT
    FROM 
        purchase_order po
    LEFT JOIN 
        supplier ON po.SP_ID = supplier.SP_ID
    LEFT JOIN
        approval ON po.ap_id = approval.ap_id
    LEFT JOIN
        payment_details pd ON po.PO_ID = pd.PO_ID
    LEFT JOIN 
    	employee emp ON emp.EMP_ID = po.EMP_ID
    WHERE 
        po.PO_ID = ?";

    $stmt = $db->prepare($sql);
    $stmt->bind_param("s", $po_id);
    $stmt->execute();
    $poDetails = $stmt->get_result()->fetch_assoc();

    // Second query to get items
    $sql = "SELECT 
        po_list.POL_ID,
        po_list.POL_QUANTITY,
        po_list.POL_PRICE,
        inventory.INV_MODEL_NAME,
        inventory.INV_BRAND
    FROM 
        PO_LIST po_list
    JOIN 
        INVENTORY inventory ON po_list.INV_ID = inventory.INV_ID
    WHERE 
        po_list.PO_ID = ?";

    $stmt = $db->prepare($sql);
    $stmt->bind_param("s", $po_id);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Store results in response array
    $response = [
        'po_details' => $poDetails,
        'items' => $items,
        'payment_details' => [
            'PD_PAYMENT_TYPE' => $poDetails['PD_PAYMENT_TYPE'] ?? null,
            'PD_CHANGE' => $poDetails['PD_CHANGE'] ?? null,
            'PD_AMMOUNT' => $poDetails['PD_AMMOUNT'] ?? null
        ]
    ];
} else {
    $sql = "SELECT po.PO_ID, 
    po.PO_ORDER_DATE, 
    po.PO_ARRIVAL_DATE, 
    po.PO_STATUS, 
    po.SP_ID, 
    ap.ap_date,
    sp.SP_NAME 
    FROM purchase_order po 
    JOIN supplier sp ON sp.SP_ID = po.SP_ID
    JOIN approval ap ON ap.ap_id = po.ap_id
    WHERE po.PO_STATUS IN ('approved', 'completed', 'canceled')
    ORDER BY po.PO_ID DESC";

    $result = $db->query($sql);
    $pos = [];
    while($row = $result->fetch_assoc()){
        $pos[] = $row;
    }
}

?>