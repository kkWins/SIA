<?php
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];

    try {
        $stmt = $db->prepare("DELETE FROM supplier_products WHERE sp_prod_id = ?");
        $stmt->bind_param("i", $product_id);
        $success = $stmt->execute();

        echo json_encode(['success' => $success]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} 