<?php
session_start();
require_once('../db.php');

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['loggedIn']) || $_SESSION['department'] . " " . $_SESSION['role'] !== 'Finance Manager') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $po_id = $_POST['po_id'] ?? '';
    $order_datetime = $_POST['order_datetime'] ?? '';
    $arrival_datetime = $_POST['arrival_datetime'] ?? '';
    
    // Validate inputs
    if (empty($po_id) || empty($order_datetime) || empty($arrival_datetime)) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
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

        // Only update if dates are empty or different
        if (!$existing_data || 
            $existing_data['PO_ORDER_DATE'] != $order_datetime || 
            $existing_data['PO_ARRIVAL_DATE'] != $arrival_datetime) {
            
            // Update the purchase order
            $stmt = $db->prepare("UPDATE purchase_order 
                                  SET PO_ORDER_DATE = ?, 
                                      PO_ARRIVAL_DATE = ?,
                                      PO_STATUS = 'approved'
                                  WHERE PO_ID = ?");
                                  
            $stmt->bind_param("ssi", $order_datetime, $arrival_datetime, $po_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error']);
            }
            
            $stmt->close();
        } else {
            // Dates are the same, just return success
            echo json_encode(['success' => true, 'message' => 'No changes needed']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$db->close();
?> 