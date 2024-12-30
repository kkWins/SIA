<?php

require_once 'db.php';

if(isset($_GET['pr_id'])){
    $pdId = $_GET['pr_id'];

    $sql = "SELECT 
    po_list.POL_ID,
    po_list.POL_QUANTITY,
    po_list.POL_PRICE,
    inventory.INV_ID,
    inventory.INV_MODEL_NAME,
    inventory.INV_BRAND,
    supplier.SP_ID,
    supplier.SP_NAME,
    supplier.SP_ADDRESS,
    supplier.SP_NUMBER
FROM 
    PO_LIST po_list
JOIN 
    INVENTORY inventory ON po_list.INV_ID = inventory.INV_ID
JOIN 
    PURCHASE_ORDER po ON po_list.PO_ID = po.PO_ID
JOIN 
    SUPPLIER supplier ON po.SP_ID = supplier.SP_ID
WHERE 
    po.PO_ID = ?";

    $stmt = $db->prepare($sql);
    $stmt->bind_param("s", $pdId);
    $stmt->execute();

    $result = $stmt->get_result();





}else{
    $sql = "SELECT * FROM purchase_order";

    $result = $db->query($sql);
    $po = [];

    while($row = $result->fetch_assoc()){
        $pos[] = $row;
    }





}

?>