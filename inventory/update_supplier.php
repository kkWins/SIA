<?php
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplier_id = $_POST['supplier_id'];
    $name = $_POST['name'];
    $address = $_POST['address'];
    $contact = $_POST['contact'];
    
    $db->begin_transaction();

    try {
        // Update supplier basic info
        $stmt = $db->prepare("UPDATE supplier SET SP_NAME = ?, SP_ADDRESS = ?, SP_NUMBER = ? WHERE SP_ID = ?");
        $stmt->bind_param("sssi", $name, $address, $contact, $supplier_id);
        $stmt->execute();
        
        // Handle products
        if (isset($_POST['products'])) {
            // First, delete all existing products for this supplier
            $db->query("DELETE FROM supplier_products WHERE sp_id = $supplier_id");
            
            $products = $_POST['products'];
            $prices = $_POST['prices'] ?? [];
            
            // Prepare statement for inserting products
            $insert_stmt = $db->prepare("INSERT INTO supplier_products (sp_id, inv_id, unit_price, status) 
                                       VALUES (?, ?, ?, 'active')");
            
            foreach ($products as $index => $inv_id) {
                if (empty($inv_id)) continue; // Skip empty product selections
                
                $price = $prices[$index] ?? 0;
                $insert_stmt->bind_param("iid", $supplier_id, $inv_id, $price);
                $insert_stmt->execute();
            }
        }

        $db->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $db->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} 