<?php
require_once '../db.php';

try {
    $db->begin_transaction();

    // Insert supplier
    $stmt = $db->prepare("INSERT INTO supplier (SP_NAME, SP_ADDRESS, SP_NUMBER, SP_STATUS) VALUES (?, ?, ?, '1')");
    $stmt->bind_param("sss", $_POST['name'], $_POST['address'], $_POST['contact']);
    $stmt->execute();
    
    $supplier_id = $db->insert_id;

    // Insert products
    if (!empty($_POST['products'])) {
        $stmt = $db->prepare("INSERT INTO supplier_products (sp_id, inv_id, unit_price) VALUES (?, ?, ?)");
        
        foreach ($_POST['products'] as $index => $inv_id) {
            $price = $_POST['prices'][$index] ?? 0;
            $stmt->bind_param("iid", $supplier_id, $inv_id, $price);
            $stmt->execute();
        }
    }

    $db->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $db->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$db->close(); 