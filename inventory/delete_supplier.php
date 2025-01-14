<?php
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $supplier_id = $_POST['id'];
    $delete_type = $_POST['delete_type'] ?? 'soft'; // Default to soft delete
    
    if ($delete_type === 'soft') {
        // Soft delete - just update the status
        $stmt = $db->prepare("UPDATE supplier SET SP_STATUS = '0' WHERE SP_ID = ?");
        $stmt->bind_param("i", $supplier_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Supplier deactivated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deactivating supplier']);
        }
    } else {
        // Hard delete - completely remove the record
        // First delete related records in supplier_products table
        $stmt = $db->prepare("DELETE FROM supplier_products WHERE supplier_id = ?");
        $stmt->bind_param("i", $supplier_id);
        $stmt->execute();
        
        // Then delete the supplier
        $stmt = $db->prepare("DELETE FROM supplier WHERE SP_ID = ?");
        $stmt->bind_param("i", $supplier_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Supplier deleted permanently']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting supplier']);
        }
    }
} 