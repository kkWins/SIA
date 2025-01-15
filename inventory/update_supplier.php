<?php
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplier_id = $_POST['supplier_id'];
    $name = $_POST['name'];
    $address = $_POST['address'];
    $contact = $_POST['contact'];
    
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
        
        // Handle products
        if (isset($_POST['products'])) {
            $products = $_POST['products'];
            $descriptions = $_POST['descriptions'] ?? [];
            $prices = $_POST['prices'] ?? [];
            $product_ids = $_POST['product_ids'] ?? [];
            $product_statuses = $_POST['product_status'] ?? [];
            
            // Check for duplicate product names within the same supplier
            $product_names = array_map('strtolower', $products); // Convert to lowercase for case-insensitive comparison
            $duplicate_names = array_diff_assoc($product_names, array_unique($product_names));
            
            if (!empty($duplicate_names)) {
                throw new Exception("Duplicate product name found: '" . ucfirst(array_values($duplicate_names)[0]) . "'. Product names must be unique for each supplier.");
            }

            // Prepare the NOT IN clause safely
            $placeholders = str_repeat('?,', count(array_filter($product_ids)) - 1) . '?';
            $placeholders = !empty($placeholders) ? $placeholders : '0'; // Fallback if no product IDs

            // Get existing product names for this supplier (excluding the current products being updated)
            $existing_products_stmt = $db->prepare("
                SELECT LOWER(product_name) as product_name 
                FROM supplier_products 
                WHERE sp_id = ? 
                AND sp_prod_id NOT IN ($placeholders)
                AND status = 'active'
            ");
            
            // Bind parameters
            $params = array_filter($product_ids);
            array_unshift($params, $supplier_id);
            $types = str_repeat('i', count($params));
            $existing_products_stmt->bind_param($types, ...$params);
            
            $existing_products_stmt->execute();
            $result = $existing_products_stmt->get_result();
            $existing_names = [];
            while ($row = $result->fetch_assoc()) {
                $existing_names[] = $row['product_name'];
            }

            // Check for conflicts with existing products
            foreach ($product_names as $index => $product_name) {
                if (in_array($product_name, $existing_names)) {
                    throw new Exception("Product name '" . ucfirst($products[$index]) . "' already exists for this supplier.");
                }
            }
            
            // Get all existing product IDs for this supplier
            $existing_products_stmt = $db->prepare("SELECT sp_prod_id FROM supplier_products WHERE sp_id = ?");
            $existing_products_stmt->bind_param("i", $supplier_id);
            $existing_products_stmt->execute();
            $result = $existing_products_stmt->get_result();
            $existing_product_ids = [];
            while ($row = $result->fetch_assoc()) {
                $existing_product_ids[] = $row['sp_prod_id'];
            }
            
            // Determine which products to keep (those in the form submission)
            $products_to_keep = array_filter($product_ids, function($id) { return !empty($id); });
            
            // Delete products that are no longer in the form
            $products_to_delete = array_diff($existing_product_ids, $products_to_keep);
            if (!empty($products_to_delete)) {
                $delete_stmt = $db->prepare("UPDATE supplier_products SET status = 'inactive' WHERE sp_prod_id = ?");
                foreach ($products_to_delete as $delete_id) {
                    $delete_stmt->bind_param("i", $delete_id);
                    $delete_stmt->execute();
                }
            }
            
            // Update or insert remaining products
            $insert_stmt = $db->prepare("INSERT INTO supplier_products (sp_prod_id, sp_id, product_name, product_description, unit_price, status) 
                                       VALUES (?, ?, ?, ?, ?, ?)
                                       ON DUPLICATE KEY UPDATE 
                                       product_name = VALUES(product_name),
                                       product_description = VALUES(product_description),
                                       unit_price = VALUES(unit_price),
                                       status = VALUES(status)");
            
            foreach ($products as $index => $product) {
                $product_id = !empty($product_ids[$index]) ? $product_ids[$index] : null;
                $description = $descriptions[$index] ?? '';
                $price = $prices[$index] ?? 0;
                $status = $product_statuses[$index] ?? 'active';
                
                $insert_stmt->bind_param("iissds", $product_id, $supplier_id, $product, $description, $price, $status);
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