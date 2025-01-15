<?php
session_start();
require_once 'db.php'; // Use the same database connection as get_vendors.php

if (isset($_GET['vendor_id'])) {
    $vendor_id = $_GET['vendor_id'];
    
    // Modified query to use mysqli instead of PDO
    $query = "
        SELECT i.INV_ID as id, 
               i.INV_MODEL_NAME as name, 
               i.INV_BRAND as brand,
               sp.UNIT_PRICE as price
        FROM inventory i
        INNER JOIN supplier_products sp ON i.INV_ID = sp.INV_ID
        WHERE sp.SP_ID = ? AND sp.STATUS = 'active'
        ORDER BY i.INV_MODEL_NAME
    ";
    
    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $vendor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    
    echo json_encode($items);
} else {
    echo json_encode([]);
}
?> 