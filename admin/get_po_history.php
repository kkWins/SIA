<?php
require_once 'db.php';

// Set the number of items per page
$items_per_page = 3;

// Get the current page number
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Initialize the WHERE clause and parameters array
$where_conditions = ["po.PO_STATUS IN ('approved', 'completed')"]; 
$params = [];
$param_types = "";

// Handle filters
if (!empty($_GET['filter_id'])) {
    $where_conditions[] = "po.PO_ID LIKE ?";
    $params[] = "%" . $_GET['filter_id'] . "%";
    $param_types .= "s";
}

if (!empty($_GET['filter_supplier'])) {
    $where_conditions[] = "sp.SP_NAME LIKE ?";
    $params[] = "%" . $_GET['filter_supplier'] . "%";
    $param_types .= "s";
}

if (!empty($_GET['filter_date'])) {
    $where_conditions[] = "DATE(approval.ap_date) = ?";
    $params[] = $_GET['filter_date'];
    $param_types .= "s";
}

if (!empty($_GET['filter_status'])) {
    $where_conditions[] = "po.PO_STATUS = ?";
    $params[] = $_GET['filter_status'];
    $param_types .= "s";
}

// Combine WHERE conditions
$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total number of filtered purchase orders
$count_query = "SELECT COUNT(*) as total 
                FROM purchase_order po
                JOIN supplier sp ON po.SP_ID = sp.SP_ID
                LEFT JOIN approval ON approval.ap_id = po.ap_id
                $where_clause";

if (!empty($params)) {
    $count_stmt = $db->prepare($count_query);
    $count_stmt->bind_param($param_types, ...$params);
    $count_stmt->execute();
    $total_rows = $count_stmt->get_result()->fetch_assoc()['total'];
    $count_stmt->close();
} else {
    $total_rows = $db->query($count_query)->fetch_assoc()['total'];
}

// Calculate total pages
$total_pages = ceil($total_rows / $items_per_page);

// Get filtered purchase orders for current page
$query = "SELECT po.*, sp.SP_NAME, approval.ap_date
          FROM purchase_order po
          JOIN supplier sp ON po.SP_ID = sp.SP_ID
          LEFT JOIN approval ON approval.ap_id = po.ap_id
          $where_clause
          ORDER BY po.PO_ID DESC
          LIMIT ?, ?";

// Add pagination parameters
$param_types .= "ii";
$params[] = $offset;
$params[] = $items_per_page;

$stmt = $db->prepare($query);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$purchase_orders = [];
while ($row = $result->fetch_assoc()) {
    $purchase_orders[] = $row;
}

// Pagination data
$pagination = [
    'current_page' => $page,
    'total_pages' => $total_pages,
    'items_per_page' => $items_per_page
];

$stmt->close();
?> 