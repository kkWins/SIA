<?php
session_start();
require_once 'db.php';

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

try {
    // Start transaction
    $db->begin_transaction();

    // Get data and convert vendor_id to integer
    $emp_id = $_SESSION['ID'];
    $vendor_id = intval($data['vendor_id']); // Convert to integer
    $items = $data['items'];

    // Insert into PO table
    $stmt = $db->prepare("INSERT INTO purchase_order (PO_STATUS, EMP_ID, SP_ID) 
                         VALUES ('PENDING', ?, ?)");
    $stmt->bind_param("ii", $emp_id, $vendor_id); // Both parameters are integers
    $stmt->execute();
    $po_id = $db->insert_id;

    // Insert items into PO_LIST table
    $stmt = $db->prepare("INSERT INTO po_list (PO_ID, INV_ID, POL_QUANTITY, POL_PRICE) 
                         VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiid", $po_id, $item_id, $quantity, $price); // Bind parameters for MySQLi

    foreach ($items as $item) {
        $item_id = $item['item_id'];
        $quantity = $item['quantity'];
        $price = $item['price'];
        $stmt->execute();
    }

    $db->commit(); // Commit transaction
    echo json_encode(['success' => true, 'po_id' => $po_id]);

} catch (mysqli_sql_exception $e) {
    $db->rollback(); // Rollback transaction
    error_log("Error submitting purchase request: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
