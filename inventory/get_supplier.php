<?php
require_once '../db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Get supplier details
    $stmt = $db->prepare("SELECT * FROM supplier WHERE SP_ID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $supplier = $stmt->get_result()->fetch_assoc();
    
    // Get all supplier products (both active and inactive)
    $query = "SELECT sp_prod_id, product_name, product_description, unit_price, status 
              FROM supplier_products 
              WHERE sp_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode([
        'success' => true,
        'supplier' => $supplier,
        'products' => $products
    ]);
} 