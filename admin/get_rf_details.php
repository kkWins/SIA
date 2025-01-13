<?php
require_once 'db.php';

if (!isset($_GET['req_id'])) {
    return;
}

$req_id = mysqli_real_escape_string($db, $_GET['req_id']);

// Get requisition header information
$query = "SELECT 
    prf.PRF_ID,
    CONCAT(emp.EMP_FNAME, ' ', emp.EMP_LNAME) as FULL_NAME,
    dept.DEPT_NAME,
    prf.PRF_DATE,
    prf.PRF_STATUS
FROM purchase_or_requisition_form prf
JOIN employee emp ON prf.EMP_ID = emp.EMP_ID
JOIN department dept ON emp.DEPT_ID = dept.DEPT_ID
WHERE prf.PRF_ID = '$req_id'";

$result = mysqli_query($db, $query);
$requisitionDetails = mysqli_fetch_assoc($result);

if ($requisitionDetails) {
    // Get requisition items
    $items_query = "SELECT 
        i.INV_MODEL_NAME as item_name,
        il.iT_QUANTITY as quantity,
        il.iT_DESCRIPTION as description
    FROM item_list il
    JOIN inventory i ON il.INV_ID = i.INV_ID
    WHERE il.PRF_ID = '$req_id'";

    $items_result = mysqli_query($db, $items_query);
    $items = [];
    
    while ($item = mysqli_fetch_assoc($items_result)) {
        $items[] = $item;
    }
    
    $requisitionDetails['items'] = $items;
}

mysqli_close($db); 