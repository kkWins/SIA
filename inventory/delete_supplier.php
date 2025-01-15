<?php
require_once '../db.php';

// Ensure proper JSON response headers
header('Content-Type: application/json');

// Disable error output
ini_set('display_errors', 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $supplier_id = intval($_POST['id']);
    $delete_type = $_POST['delete_type'] ?? 'soft';
    
    try {
        // Start transaction
        $db->begin_transaction();
        
        if ($delete_type === 'soft') {
            $stmt = $db->prepare("UPDATE supplier SET SP_STATUS = '0' WHERE SP_ID = ?");
            if (!$stmt) {
                throw new Exception("Failed to prepare deactivation statement: " . $db->error);
            }
            
            $stmt->bind_param("i", $supplier_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Error deactivating supplier: " . $stmt->error);
            }
        } else {
            // Hard delete - first delete related records
            $stmt = $db->prepare("DELETE FROM supplier_products WHERE sp_id = ?");
            if (!$stmt) {
                throw new Exception("Failed to prepare product deletion statement: " . $db->error);
            }
            
            $stmt->bind_param("i", $supplier_id);
            if (!$stmt->execute()) {
                throw new Exception("Error deleting supplier products: " . $stmt->error);
            }
            
            // Then delete the supplier
            $stmt = $db->prepare("DELETE FROM supplier WHERE SP_ID = ?");
            if (!$stmt) {
                throw new Exception("Failed to prepare supplier deletion statement: " . $db->error);
            }
            
            $stmt->bind_param("i", $supplier_id);
            if (!$stmt->execute()) {
                throw new Exception("Error deleting supplier: " . $stmt->error);
            }
        }
        
        // Commit transaction
        $db->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => ($delete_type === 'soft') ? 'Supplier deactivated successfully' : 'Supplier deleted permanently'
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $db->rollback();
        
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
} 