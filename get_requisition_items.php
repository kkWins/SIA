<?php
// Add database connection at the start
require_once 'db.php';

// At the start of the file
error_log('POST data received: ' . print_r($_POST, true));

$status = isset($_POST['status']) ? strtolower(trim($_POST['status'])) : '';
$prf_id = isset($_POST['prf_id']) ? $_POST['prf_id'] : '';

if (empty($prf_id)) {
    die("No PRF ID provided");
}

// Fetch rejection reason first
$rejection_reason = '';
if ($status === 'rejected') {
    $reason_query = "SELECT a.ap_desc 
                    FROM purchase_or_requisition_form prf 
                    LEFT JOIN approval a ON prf.AP_ID = a.ap_id 
                    WHERE prf.PRF_ID = ?";
    
    $reason_stmt = $db->prepare($reason_query);
    $reason_stmt->bind_param("i", $prf_id);
    $reason_stmt->execute();
    $reason_result = $reason_stmt->get_result();
    
    if ($row = $reason_result->fetch_assoc()) {
        $rejection_reason = $row['ap_desc'];
    }
    $reason_stmt->close();
}

// Updated query to use correct table names
$items_query = "SELECT il.IT_ID, il.IT_QUANTITY, il.IT_DESCRIPTION, 
                       i.INV_MODEL_NAME, i.INV_BRAND
                FROM item_list il
                JOIN inventory i ON il.INV_ID = i.INV_ID
                WHERE il.PRF_ID = ?";

$items_stmt = $db->prepare($items_query);
$items_stmt->bind_param("i", $prf_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

// Display items table
echo "<table class='table'>";
echo "<thead><tr><th>Item Name</th><th>Brand</th><th>Quantity</th><th>Description</th></tr></thead>";
echo "<tbody>";

while ($item = $items_result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($item['INV_MODEL_NAME']) . "</td>";
    echo "<td>" . htmlspecialchars($item['INV_BRAND']) . "</td>";
    echo "<td>" . htmlspecialchars($item['IT_QUANTITY']) . "</td>";
    echo "<td>" . htmlspecialchars($item['IT_DESCRIPTION']) . "</td>";
    echo "</tr>";
}

echo "</tbody>";
echo "</table>";

$items_stmt->close();


// Display rejection reason if status is rejected
if ($status === 'rejected' && !empty($rejection_reason)) {
    echo "<div class='alert alert-danger'>";
    echo "<strong>Rejection Reason:</strong><br>";
    echo htmlspecialchars($rejection_reason);
    echo "</div>";
}
echo "</div>";
