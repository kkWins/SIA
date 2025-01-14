<?php
require_once 'db.php';

// Pagination settings
$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM purchase_order WHERE PO_STATUS IN ('pending', 'rejected')";
$count_result = mysqli_query($db, $count_query);
$total_items = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_items / $items_per_page);

// Fetch purchase requests
$query = "SELECT 
    po.PO_ID,
    po.PO_STATUS,
    po.PO_PR_DATE_CREATED,
    supplier.SP_NAME,
    CONCAT(emp.EMP_FNAME, ' ', emp.EMP_LNAME) AS fullname
FROM purchase_order po
JOIN supplier ON po.SP_ID = supplier.SP_ID
JOIN employee emp ON emp.EMP_ID = po.emp_id
ORDER BY po.PO_ORDER_DATE DESC
LIMIT $items_per_page OFFSET $offset";

$result = mysqli_query($db, $query);
$purchase_requests = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $purchase_requests[] = $row;
    }
}

mysqli_close($db); 