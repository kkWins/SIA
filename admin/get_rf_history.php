<?php
require_once 'db.php';

// Pagination settings
$items_per_page = 4;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM purchase_or_requisition_form";
$count_result = mysqli_query($db, $count_query);
$total_items = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_items / $items_per_page);

// Modified query with LIMIT and OFFSET
$query = "SELECT 
    prf.PRF_ID,
    CONCAT(emp.EMP_FNAME, ' ', emp.EMP_LNAME) as FULL_NAME,
    dept.DEPT_NAME,
    prf.PRF_DATE,
    prf.PRF_STATUS
FROM purchase_or_requisition_form prf
JOIN employee emp ON prf.EMP_ID = emp.EMP_ID
JOIN department dept ON emp.DEPT_ID = dept.DEPT_ID
ORDER BY prf.PRF_DATE DESC
LIMIT $items_per_page OFFSET $offset";

$result = mysqli_query($db, $query);
$requisitions = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $requisitions[] = $row;
    }
}

// Add pagination info to the result
$pagination = [
    'current_page' => $current_page,
    'total_pages' => $total_pages,
    'items_per_page' => $items_per_page
];

mysqli_close($db); 