<?php
require_once 'db.php';

// Check if logged in
if (!isset($_SESSION['loggedIn']) || !$_SESSION['loggedIn']) {
    echo "Please log in first";
    exit;
}

// Check if PO_ID was provided
if (!isset($_POST['po_id'])) {
    echo "No purchase order ID provided";
    exit;
}

$query = "SELECT 
            poi.ITEM_ID,
            poi.ITEM_QUANTITY,
            poi.ITEM_UNIT_PRICE,
            (poi.ITEM_QUANTITY * poi.ITEM_UNIT_PRICE) as total_price,
            i.ITEM_NAME,
            i.ITEM_UNIT
          FROM purchase_order_items poi
          JOIN item i ON poi.ITEM_ID = i.ITEM_ID
          WHERE poi.PO_ID = ?";

$stmt = $db->prepare($query);
$stmt->bind_param("s", $_POST['po_id']);

if (!$stmt->execute()) {
    echo "Error executing query: " . $stmt->error;
    exit;
}

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<table class='table table-bordered'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>Item Name</th>";
    echo "<th>Quantity</th>";
    echo "<th>Unit</th>";
    echo "<th>Unit Price</th>";
    echo "<th>Total Price</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    
    $grand_total = 0;
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['ITEM_NAME']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ITEM_QUANTITY']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ITEM_UNIT']) . "</td>";
        echo "<td>₱" . number_format($row['ITEM_UNIT_PRICE'], 2) . "</td>";
        echo "<td>₱" . number_format($row['total_price'], 2) . "</td>";
        echo "</tr>";
        
        $grand_total += $row['total_price'];
    }
    
    // Add grand total row
    echo "<tr class='table-info'>";
    echo "<td colspan='4' class='text-end'><strong>Grand Total:</strong></td>";
    echo "<td><strong>₱" . number_format($grand_total, 2) . "</strong></td>";
    echo "</tr>";
    
    echo "</tbody>";
    echo "</table>";
} else {
    echo "<div class='alert alert-info'>No items found for this purchase order.</div>";
}

$stmt->close();
?>
