<?php
require_once 'db.php';

if (!isset($_POST['prf_id'])) {
    echo "<div class='alert alert-danger'>Invalid request</div>";
    exit;
}

$prf_id = $_POST['prf_id'];

$query = "SELECT 
            il.IT_QUANTITY,
            il.IT_DESCRIPTION,
            inv.INV_MODEL_NAME
          FROM item_list il
          JOIN inventory inv ON il.INV_ID = inv.INV_ID
          WHERE il.PRF_ID = ?";

$stmt = $db->prepare($query);
$stmt->bind_param("i", $prf_id);

if (!$stmt->execute()) {
    echo "<div class='alert alert-danger'>Error loading items: " . $stmt->error . "</div>";
    exit;
}

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div class='alert alert-info'>No items found for this requisition</div>";
} else {
    echo "<table class='table'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>Item Name</th>";
    echo "<th>Quantity</th>";
    echo "<th>Description</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['INV_MODEL_NAME']) . "</td>";
        echo "<td>" . htmlspecialchars($row['IT_QUANTITY']) . "</td>";
        echo "<td>" . htmlspecialchars($row['IT_DESCRIPTION']) . "</td>";
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";
}

$stmt->close();
