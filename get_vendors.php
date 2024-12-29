<?php
session_start();
require_once 'db.php';

try {
    $stmt = $db->query("SELECT SP_ID as id, SP_NAME as name FROM supplier");
    $vendors = [];
    while ($row = $stmt->fetch_assoc()) {
        $vendors[] = $row;
    }
    
    // Return JSON without setting headers (since it's working in get_inventory.php)
    echo json_encode($vendors);
    
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
