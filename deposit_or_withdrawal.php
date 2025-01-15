<?php
session_start();
require_once 'db.php';

// Create connection using the existing db.php configuration
$connection = new mysqli($servername, $username, $password, $database, 3306);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Check if the AJAX request was made
if (isset($_POST['action'])) {
    $action = $_POST['action'];  // 'deposit' or 'withdraw'
    $itemId = $_POST['item_id'];
    $quantity = $_POST['quantity'];
    $empId = $_SESSION['ID']; // Assuming employee ID is stored in the session
    $date = date('Y-m-d H:i:s'); // Current date and time

    if ($action == 'deposit') {
        // Deposit Table Insert
        $description = $_POST['description'];  // Custom description for deposit (e.g., restocking, etc.)

        // Insert deposit record into the database
        $sql = "INSERT INTO deposit (DP_QUANTITY, DP_DATE, DP_DESCRIPTION, EMP_ID, INV_ID) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("issii", $quantity, $date, $description, $empId, $itemId);
        
        if ($stmt->execute()) {
            // Update the inventory quantity after deposit
            $updateSql = "UPDATE inventory SET INV_QUANTITY = INV_QUANTITY + ? WHERE INV_ID = ?";
            $updateStmt = $connection->prepare($updateSql);
            $updateStmt->bind_param("ii", $quantity, $itemId);
            $updateStmt->execute();
            
            echo "Deposit recorded and inventory updated successfully.";
        } else {
            echo "Error in recording deposit.";
        }
    } elseif ($action == 'withdraw') {
        // Check if the withdrawal quantity is not greater than the available inventory
        $checkSql = "SELECT INV_QUANTITY FROM inventory WHERE INV_ID = ?";
        $checkStmt = $connection->prepare($checkSql);
        $checkStmt->bind_param("i", $itemId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            $inventoryRow = $result->fetch_assoc();
            $availableQuantity = $inventoryRow['INV_QUANTITY'];

            // If the withdrawal quantity is greater than the available quantity
            if ($quantity > $availableQuantity) {
                echo "Error: Insufficient inventory for withdrawal.";
                exit;  // Stop further execution
            }

            // Withdrawal Table Insert
            $reason = $_POST['reason'];  // Custom reason for withdrawal (e.g., sale, repair, etc.)

            // Insert withdrawal record into the database using the actual attribute names
            $sql = "INSERT INTO withdrawal (WDL_QUANTITY, WDL_DATE, WDL_REASON, EMP_ID, INV_ID) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $connection->prepare($sql);
            $stmt->bind_param("issii", $quantity, $date, $reason, $empId, $itemId);
            
            if ($stmt->execute()) {
                // Update the inventory quantity after withdrawal
                $updateSql = "UPDATE inventory SET INV_QUANTITY = INV_QUANTITY - ? WHERE INV_ID = ?";
                $updateStmt = $connection->prepare($updateSql);
                $updateStmt->bind_param("ii", $quantity, $itemId);
                $updateStmt->execute();
                
                echo "Withdrawal recorded and inventory updated successfully.";
            } else {
                echo "Error in recording withdrawal.";
            }
        } else {
            echo "Error: Item not found in inventory.";
        }
    }
} else {
    echo "Invalid request.";
}

$connection->close();
?>
