<?php
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $action = $_POST['action'];

    try {
        // Get supplier ID for the product
        $supplier_stmt = $db->prepare("SELECT sp_id, status FROM supplier_products WHERE sp_prod_id = ?");
        $supplier_stmt->bind_param("i", $product_id);
        $supplier_stmt->execute();
        $product_result = $supplier_stmt->get_result()->fetch_assoc();
        
        if (!$product_result) {
            throw new Exception('Product not found');
        }

        $supplier_id = $product_result['sp_id'];

        // For deactivation, check if it's the last active product
        if ($action === 'deactivate') {
            $active_check = $db->prepare("SELECT COUNT(*) as active_count FROM supplier_products 
                                        WHERE sp_id = ? AND status = 'active' AND sp_prod_id != ?");
            $active_check->bind_param("ii", $supplier_id, $product_id);
            $active_check->execute();
            $count_result = $active_check->get_result()->fetch_assoc();

            if ($count_result['active_count'] === 0) {
                throw new Exception('Cannot deactivate the last active product. A supplier must have at least one active product.');
            }
        }

        // For deletion, check if product is inactive
        if ($action === 'delete') {
            if ($product_result['status'] !== 'inactive') {
                throw new Exception('Only inactive products can be permanently deleted');
            }
        }

        switch ($action) {
            case 'deactivate':
                $stmt = $db->prepare("UPDATE supplier_products SET status = 'inactive' WHERE sp_prod_id = ?");
                break;
            case 'activate':
                $stmt = $db->prepare("UPDATE supplier_products SET status = 'active' WHERE sp_prod_id = ?");
                break;
            case 'delete':
                $stmt = $db->prepare("DELETE FROM supplier_products WHERE sp_prod_id = ? AND status = 'inactive'");
                break;
            default:
                throw new Exception('Invalid action');
        }

        $stmt->bind_param("i", $product_id);
        $success = $stmt->execute();

        echo json_encode(['success' => $success]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} 