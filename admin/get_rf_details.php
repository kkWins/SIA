<?php
require_once 'db.php';

if (!isset($_GET['req_id'])) {
    return;
}

$req_id = mysqli_real_escape_string($db, $_GET['req_id']);

// Get requisition header information
$query = "SELECT 
    prf.PRF_ID,
    CONCAT(emp.EMP_FNAME, ' ', emp.EMP_LNAME) AS FULL_NAME,
    dept.DEPT_NAME,
    prf.PRF_DATE,
    prf.PRF_STATUS,
    ap.ap_desc,
    ap.ap_date,
    rf.WD_DATE_RECEIVED,
    il.IT_QUANTITY,
    CONCAT(appr_emp.EMP_FNAME, ' ', appr_emp.EMP_LNAME) AS APPROVER_NAME
FROM purchase_or_requisition_form prf
JOIN employee emp ON prf.EMP_ID = emp.EMP_ID
JOIN department dept ON emp.DEPT_ID = dept.DEPT_ID
LEFT JOIN approval ap ON ap.ap_id = prf.ap_id
LEFT JOIN employee appr_emp ON appr_emp.EMP_ID = ap.emp_id
LEFT JOIN rf_withdrawal rf ON prf.PRF_ID = rf.PRF_ID
LEFT JOIN item_list il ON il.PRF_ID = prf.PRF_ID
WHERE prf.PRF_ID = '$req_id'";

$result = mysqli_query($db, $query);
$requisitionDetails = mysqli_fetch_assoc($result);

if ($requisitionDetails) {
    // Get requisition items
    $items_query = "SELECT 
        i.INV_MODEL_NAME as item_name,
        il.IT_QUANTITY as quantity,
        il.IT_DESCRIPTION as description,
        il.IT_QUANTITY,
        rf.WD_DATE_RECEIVED
    FROM item_list il
    JOIN inventory i ON il.INV_ID = i.INV_ID
    LEFT JOIN rf_withdrawal rf ON il.PRF_ID = rf.PRF_ID AND il.INV_ID = rf.INV_ID
    WHERE il.PRF_ID = '$req_id'";

    $items_result = mysqli_query($db, $items_query);
    $items = [];
    
    while ($item = mysqli_fetch_assoc($items_result)) {
        $items[] = $item;
    }
    
    $requisitionDetails['items'] = $items;
}

mysqli_close($db); 