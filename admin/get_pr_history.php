<?php
require_once 'db.php';

// Initialize the WHERE clause - Remove the status restriction to show all records
$where_conditions = [];  // Changed this line to start with empty conditions
$params = [];

// Handle filters
if (!empty($_GET['filter_id'])) {
    $where_conditions[] = "po.PO_ID LIKE ?";
    $params[] = "%" . $_GET['filter_id'] . "%";
}

if (!empty($_GET['filter_name'])) {
    $where_conditions[] = "CONCAT(emp.EMP_FNAME, ' ', emp.EMP_LNAME) LIKE ?";
    $params[] = "%" . $_GET['filter_name'] . "%";
}

if (!empty($_GET['filter_supplier'])) {
    $where_conditions[] = "supplier.SP_NAME LIKE ?";
    $params[] = "%" . $_GET['filter_supplier'] . "%";
}

if (!empty($_GET['filter_date'])) {
    $where_conditions[] = "DATE(po.PO_PR_DATE_CREATED) = ?";
    $params[] = $_GET['filter_date'];
}

if (!empty($_GET['filter_status'])) {
    $where_conditions[] = "po.PO_STATUS = ?";
    $params[] = $_GET['filter_status'];
}

// Combine WHERE conditions - Only add WHERE if there are conditions
$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Pagination settings
$items_per_page = 3;  // Make sure this matches your requisition form history setting
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total 
                FROM purchase_order po
                JOIN supplier ON po.SP_ID = supplier.SP_ID
                JOIN employee emp ON emp.EMP_ID = po.emp_id
                WHERE po.PO_STATUS != 'completed' " .  // Add this condition
                (!empty($where_conditions) ? "AND " . implode(" AND ", $where_conditions) : "");

$stmt = mysqli_prepare($db, $count_query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, str_repeat('s', count($params)), ...$params);
}
mysqli_stmt_execute($stmt);
$count_result = mysqli_stmt_get_result($stmt);
$total_items = mysqli_fetch_assoc($count_result)['total'];

// Debug output
error_log("Total items found: " . $total_items);
error_log("Items per page: " . $items_per_page);

$total_pages = max(1, ceil($total_items / $items_per_page));
$current_page = min($current_page, $total_pages);

// Set up pagination array
$pagination = [
    'current_page' => $current_page,
    'total_pages' => $total_pages,
    'items_per_page' => $items_per_page,
    'total_items' => $total_items
];

// Main query with filters
$query = "SELECT 
    po.PO_ID,
    po.PO_STATUS,
    po.PO_PR_DATE_CREATED,
    supplier.SP_NAME,
    CONCAT(emp.EMP_FNAME, ' ', emp.EMP_LNAME) AS fullname
FROM purchase_order po
JOIN supplier ON po.SP_ID = supplier.SP_ID
JOIN employee emp ON emp.EMP_ID = po.emp_id
WHERE po.PO_STATUS != 'completed' " .  // Add this condition
    (!empty($where_conditions) ? "AND " . implode(" AND ", $where_conditions) : "") . "
ORDER BY po.PO_ID DESC
LIMIT ? OFFSET ?";

// Add pagination parameters
$params[] = $items_per_page;
$params[] = $offset;

$stmt = mysqli_prepare($db, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, str_repeat('s', count($params)), ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$purchase_requests = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $purchase_requests[] = $row;
    }
}

// Debug output
error_log("Number of records retrieved: " . count($purchase_requests));
error_log("Current page: " . $current_page);
error_log("Total pages: " . $total_pages);
