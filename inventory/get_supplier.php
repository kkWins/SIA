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
    $query = "SELECT 
                sp.sp_prod_id, 
                sp.unit_price, 
                sp.status,
                sp.inv_id,
                CONCAT(i.INV_MODEL_NAME, ' - ', i.INV_BRAND) as product_name
              FROM supplier_products sp
              JOIN inventory i ON sp.inv_id = i.INV_ID 
              WHERE sp.sp_id = ?";
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