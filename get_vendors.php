<?php
session_start();
require_once 'db.php';

try {
    // Assuming $db is a mysqli connection
    $query = "SELECT SP_ID as id, SP_NAME as name FROM supplier";
    $result = $db->query($query);
    
    if ($result) {
        $vendors = [];
        while ($row = $result->fetch_assoc()) {
            $vendors[] = $row;
        }
        echo json_encode($vendors);
    } else {
        echo json_encode(['error' => 'Query failed: ' . $db->error]);
    }
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
