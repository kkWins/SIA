<?php
// Prevent any output before JSON response
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_clean();
header('Content-Type: application/json');

session_start();
require_once('../db.php');

try {
    // Check if user is logged in and has appropriate role
    if (!isset($_SESSION['loggedIn']) || $_SESSION['department'] . " " . $_SESSION['role'] !== 'Finance Manager') {
        throw new Exception('Unauthorized access');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $po_id = $_POST['po_id'] ?? '';
    $payment_type = $_POST['payment_type'] ?? '';
    $payment_amount = $_POST['payment_amount'] ?? '';
    $payment_change = $_POST['payment_change'] ?? '';
    
    // Validate inputs
    if (empty($po_id) || empty($payment_type) || empty($payment_amount)) {
        throw new Exception('Missing required fields');
    }
    
    // Begin transaction
    $db->begin_transaction();

    try {
        // Check if payment already exists
        $check_stmt = $db->prepare("SELECT PO_ID FROM payment_details WHERE PO_ID = ?");
        $check_stmt->bind_param("i", $po_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing payment
            $stmt = $db->prepare("UPDATE payment_details 
                                  SET PD_PAYMENT_TYPE = ?, 
                                      PD_CHANGE = ?,
                                      PD_AMMOUNT = ?
                                  WHERE PO_ID = ?");
        } else {
            // Insert new payment
            $stmt = $db->prepare("INSERT INTO payment_details 
                                  (PD_PAYMENT_TYPE, PD_CHANGE, PD_AMMOUNT, PO_ID) 
                                  VALUES (?, ?, ?, ?)");
        }
        
        $stmt->bind_param("sddi", $payment_type, $payment_change, $payment_amount, $po_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to save payment details');
        }


        // Commit transaction
        $db->commit();
        
        echo json_encode(['success' => true, 'message' => 'Payment processed successfully']);
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $db->close();
    }
}
?> 