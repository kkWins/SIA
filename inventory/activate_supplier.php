<?php
require_once '../db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Supplier ID is required']);
    exit;
}

try {
    $supplier_id = (int)$_POST['id'];
    
    $stmt = $db->prepare("UPDATE supplier SET SP_STATUS = '1' WHERE SP_ID = ?");
    $stmt->bind_param("i", $supplier_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Supplier activated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Supplier not found or already active']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
} 