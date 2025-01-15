<?php
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplier_id = $_POST['supplier_id'];
    $name = $_POST['name'];
    $address = $_POST['address'];
    $contact = $_POST['contact'];
    $products = $_POST['products'];
    $descriptions = $_POST['descriptions'];

    // Check for existing supplier with the same name (excluding current supplier)
    $check_stmt = $db->prepare("SELECT SP_ID FROM supplier WHERE SP_NAME = ? AND SP_ID != ? AND SP_STATUS != '0'");
    $check_stmt->bind_param("si", $name, $supplier_id);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'A supplier with this name already exists']);
        exit;
    }

    $db->begin_transaction();

    try {
        // Update supplier
        $stmt = $db->prepare("UPDATE supplier SET SP_NAME = ?, SP_ADDRESS = ?, SP_NUMBER = ? WHERE SP_ID = ?");
        $stmt->bind_param("sssi", $name, $address, $contact, $supplier_id);
        $stmt->execute();
        
        // Delete existing products
        $stmt = $db->prepare("DELETE FROM supplier_products WHERE sp_id = ?");
        $stmt->bind_param("i", $supplier_id);
        $stmt->execute();
        
        // Insert updated products
        $stmt = $db->prepare("INSERT INTO supplier_products (sp_id, product_name, product_description) VALUES (?, ?, ?)");
        for ($i = 0; $i < count($products); $i++) {
            if (!empty($products[$i])) {
                $stmt->bind_param("iss", $supplier_id, $products[$i], $descriptions[$i]);
                $stmt->execute();
            }
        }

        $db->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $db->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} 