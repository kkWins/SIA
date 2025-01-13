<?php
require_once 'db.php';

if (!isset($_GET['pr_id'])) {
    return;
}

$pr_id = mysqli_real_escape_string($db, $_GET['pr_id']);

// Get purchase order details
$sql = "SELECT 
    po.PO_ID,
    po.PO_STATUS,
    po.PO_PR_DATE_CREATED,
    supplier.SP_NAME,
    supplier.SP_ADDRESS,
    supplier.SP_NUMBER,
    approval.ap_desc,
    approval.ap_date,
    CONCAT(emp.EMP_FNAME, ' ', emp.EMP_LNAME) AS fullname,
    emp.EMP_NUMBER,
    CONCAT(appr_pr.EMP_FNAME, ' ', appr_pr.EMP_LNAME) AS approvedby
FROM 
    purchase_order po
LEFT JOIN 
    supplier ON po.SP_ID = supplier.SP_ID
LEFT JOIN
    approval ON po.ap_id = approval.ap_id
LEFT JOIN employee emp ON emp.EMP_ID = po.EMP_ID
LEFT JOIN employee appr_pr ON appr_pr.emp_id = approval.EMP_ID
WHERE 
    po.PO_ID = ?";

$stmt = $db->prepare($sql);
$stmt->bind_param("s", $pr_id);
$stmt->execute();
$poDetails = $stmt->get_result()->fetch_assoc();

if ($poDetails) {
    // Get items in the purchase order
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
    $stmt->bind_param("s", $pr_id);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $poDetails['items'] = $items;
}

mysqli_close($db); 