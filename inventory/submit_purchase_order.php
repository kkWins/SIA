<?php
session_start();
require_once('../db.php');

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['loggedIn']) || $_SESSION['department'] . " " . $_SESSION['role'] !== 'Inventory Manager') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $po_id = $_POST['po_id'] ?? '';
    $order_datetime = $_POST['order_datetime'] ?? null;
    $arrival_datetime = $_POST['arrival_datetime'] ?? null;
    
    // Only proceed with non-empty dates
    if (empty($order_datetime)) {
        echo json_encode(['success' => false, 'message' => 'Order date is required']);
        exit;
    }
    
    try {
        // First check if dates already exist
        $check_stmt = $db->prepare("SELECT PO_ORDER_DATE, PO_ARRIVAL_DATE FROM purchase_order WHERE PO_ID = ?");
        $check_stmt->bind_param("i", $po_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $existing_data = $result->fetch_assoc();
        $check_stmt->close();

        // Prepare the SQL query based on which dates are provided
        if (!empty($arrival_datetime)) {
            $sql = "UPDATE purchase_order 
                   SET PO_ORDER_DATE = ?, 
                       PO_ARRIVAL_DATE = ?,
                       PO_STATUS = 'approved'
                   WHERE PO_ID = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("ssi", $order_datetime, $arrival_datetime, $po_id);
        } else {
            // Only update order date if arrival date is empty
            $sql = "UPDATE purchase_order 
                   SET PO_ORDER_DATE = ?,
                       PO_STATUS = 'approved'
                   WHERE PO_ID = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("si", $order_datetime, $po_id);
        }
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$db->close();
?> 