<?php
require_once '../db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Get supplier details
    $stmt = $db->prepare("SELECT * FROM supplier WHERE SP_ID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $supplier = $stmt->get_result()->fetch_assoc();
    
    // Get supplier products
    $stmt = $db->prepare("SELECT * FROM supplier_products WHERE sp_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode([
        'success' => true,
        'supplier' => $supplier,
        'products' => $products
    ]);
} 