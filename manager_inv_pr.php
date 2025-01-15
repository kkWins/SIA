<?php

require_once 'db.php';

if(isset($_GET['pr_id'])){
    $prId = $_GET['pr_id'];

    // First query to get PO details, supplier info, and approval info
    $sql = "SELECT 
        po.PO_ID,
        po.PO_PR_DATE_CREATED,
        po.PO_STATUS,
        supplier.SP_NAME,
        supplier.SP_ADDRESS,
        supplier.SP_NUMBER,
        approval.ap_desc,
        CONCAT(emp.EMP_FNAME, ' ', emp.EMP_LNAME) AS fullname
    FROM 
        purchase_order po
    JOIN employee emp ON emp.EMP_ID = po.EMP_ID
    LEFT JOIN 
        supplier ON po.SP_ID = supplier.SP_ID
    LEFT JOIN
        approval ON po.ap_id = approval.ap_id
    WHERE 
        po.PO_ID = ?";

    $stmt = $db->prepare($sql);
    $stmt->bind_param("s", $prId);
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
    $stmt->bind_param("s", $prId);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Store results in response array
    $response = [
        'po_details' => $poDetails,
        'items' => $items
    ];
} else {
    $sql = "SELECT po.PO_ID, 
    po.PO_PR_DATE_CREATED,
    po.PO_STATUS, 
    po.SP_ID, 
    sp.SP_NAME,
    CONCAT(emp.EMP_FNAME , ' ', emp.EMP_LNAME) AS fullname
    FROM purchase_order po 
    JOIN supplier sp ON sp.SP_ID = po.SP_ID
    JOIN employee emp ON emp.emp_id = po.EMP_ID
    WHERE po.PO_STATUS IN ('pending', 'rejected')
    ORDER BY po.po_id DESC;";
    
    $result = $db->query($sql);
    $pos = [];
    while($row = $result->fetch_assoc()){
        $pos[] = $row;
    }
}

?>