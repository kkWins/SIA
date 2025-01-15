<?php
require_once 'db.php';

// Get filter values
$filter_id = isset($_GET['filter_id']) ? mysqli_real_escape_string($db, $_GET['filter_id']) : '';
$filter_name = isset($_GET['filter_name']) ? mysqli_real_escape_string($db, $_GET['filter_name']) : '';
$filter_department = isset($_GET['filter_department']) ? mysqli_real_escape_string($db, $_GET['filter_department']) : '';
$filter_date = isset($_GET['filter_date']) ? mysqli_real_escape_string($db, $_GET['filter_date']) : '';
$filter_status = isset($_GET['filter_status']) ? mysqli_real_escape_string($db, $_GET['filter_status']) : '';

// Build WHERE clause based on filters
$where_conditions = [];
if ($filter_id) {
    $where_conditions[] = "prf.PRF_ID LIKE '%$filter_id%'";
}
if ($filter_name) {
    $where_conditions[] = "CONCAT(emp.EMP_FNAME, ' ', emp.EMP_LNAME) LIKE '%$filter_name%'";
}
if ($filter_department) {
    $where_conditions[] = "dept.DEPT_NAME = '$filter_department'";
}
if ($filter_date) {
    $where_conditions[] = "DATE(prf.PRF_DATE) = '$filter_date'";
}
if ($filter_status) {
    $where_conditions[] = "prf.PRF_STATUS = '$filter_status'";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total 
                FROM purchase_or_requisition_form prf
                JOIN employee emp ON prf.EMP_ID = emp.EMP_ID
                JOIN department dept ON emp.DEPT_ID = dept.DEPT_ID
                $where_clause";
$count_result = mysqli_query($db, $count_query);
$total_items = mysqli_fetch_assoc($count_result)['total'];

// Pagination settings
$items_per_page = 4;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;
$total_pages = ceil($total_items / $items_per_page);

// Modified query with filters, LIMIT and OFFSET
$query = "SELECT 
    prf.PRF_ID,
    CONCAT(emp.EMP_FNAME, ' ', emp.EMP_LNAME) as FULL_NAME,
    dept.DEPT_NAME,
    prf.PRF_DATE,
    prf.PRF_STATUS
FROM purchase_or_requisition_form prf
JOIN employee emp ON prf.EMP_ID = emp.EMP_ID
JOIN department dept ON emp.DEPT_ID = dept.DEPT_ID
$where_clause
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