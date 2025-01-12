<?php
require_once 'db.php';

// Set the number of items per page
$items_per_page = 10;

// Get the current page number
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Get total number of approved purchase orders
$count_query = "SELECT COUNT(*) as total FROM purchase_order WHERE PO_STATUS = 'approved'";
$count_result = $db->query($count_query);
$total_rows = $count_result->fetch_assoc()['total'];

// Calculate total pages
$total_pages = ceil($total_rows / $items_per_page);

// Get purchase orders for current page
$query = "SELECT po.*, sp.SP_NAME 
          FROM purchase_order po
          JOIN supplier sp ON po.SP_ID = sp.SP_ID
          WHERE po.PO_STATUS = 'approved'
          ORDER BY po.PO_ORDER_DATE DESC
          LIMIT ?, ?";

$stmt = $db->prepare($query);
$stmt->bind_param("ii", $offset, $items_per_page);
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