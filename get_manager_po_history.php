<?php
require_once 'db.php';

// Initialize pagination variables
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Base query
$base_query = "SELECT po.*, sp.SP_NAME, approval.ap_date 
               FROM purchase_order po
               LEFT JOIN supplier sp ON po.SP_ID = sp.SP_ID
               LEFT JOIN approval ON approval.ap_id = po.ap_id
               WHERE po.PO_STATUS IN ('rejected', 'canceled')";

$count_query = "SELECT COUNT(*) as total FROM purchase_order po 
                WHERE po.PO_STATUS IN ('rejected', 'canceled')";

// Add filters if they exist
$params = [];
$types = "";

if (isset($_GET['filter_id']) && !empty($_GET['filter_id'])) {
    $base_query .= " AND po.PO_ID = ?";
    $count_query .= " AND po.PO_ID = ?";
    $params[] = $_GET['filter_id'];
    $types .= "i";
}

if (isset($_GET['filter_supplier']) && !empty($_GET['filter_supplier'])) {
    $base_query .= " AND sp.SP_NAME = ?";
    $count_query .= " AND po.SP_ID IN (SELECT SP_ID FROM supplier WHERE SP_NAME = ?)";
    $params[] = $_GET['filter_supplier'];
    $types .= "s";
}

if (isset($_GET['filter_date']) && !empty($_GET['filter_date'])) {
    $base_query .= " AND DATE(po.PO_PR_DATE_CREATED) = ?";
    $count_query .= " AND DATE(po.PO_PR_DATE_CREATED) = ?";
    $params[] = $_GET['filter_date'];
    $types .= "s";
}

if (isset($_GET['filter_status']) && !empty($_GET['filter_status'])) {
    $base_query .= " AND po.PO_STATUS = ?";
    $count_query .= " AND po.PO_STATUS = ?";
    $params[] = $_GET['filter_status'];
    $types .= "s";
}

// Add ordering and limit
$base_query .= " ORDER BY po.PO_ID DESC LIMIT ? OFFSET ?";
$params[] = $records_per_page;
$params[] = $offset;
$types .= "ii";

// Prepare and execute count query
$count_stmt = $db->prepare($count_query);
if (!empty($params)) {
    $temp_params = array_slice($params, 0, -2); // Remove limit and offset
    $temp_types = substr($types, 0, -2); // Remove last two type indicators
    $count_stmt->bind_param($temp_types, ...$temp_params);
}
$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Prepare and execute main query
$stmt = $db->prepare($base_query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$purchase_orders = [];
while ($row = $result->fetch_assoc()) {
    $purchase_orders[] = $row;
}

// Prepare pagination data
$pagination = [
    'current_page' => $page,
    'total_pages' => $total_pages,
    'records_per_page' => $records_per_page,
    'total_records' => $total_records
];

$stmt->close();
$count_stmt->close();
?>
