<?php
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $supplier_id = $_POST['id'];
    
    $stmt = $db->prepare("UPDATE supplier SET SP_STATUS = '1' WHERE SP_ID = ?");
    $stmt->bind_param("i", $supplier_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Supplier activated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error activating supplier']);
    }
} 