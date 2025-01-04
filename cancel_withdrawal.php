<?php
require_once 'db.php'; // Include the database connection

// Create connection using the existing db.php configuration
$connection = new mysqli($servername, $username, $password, $database, 3306);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Check if withdrawal_id is provided
if (isset($_POST['withdrawal_id'])) {
    $withdrawalId = $_POST['withdrawal_id'];

    // Step 1: Fetch the withdrawal details (WD_QUANTITY and INV_ID)
    $sql = "SELECT WD_QUANTITY, INV_ID FROM RF_WITHDRAWAL WHERE WD_ID = ?";
    if ($stmt = $connection->prepare($sql)) {
        $stmt->bind_param("i", $withdrawalId);
        $stmt->execute();
        $result = $stmt->get_result();
        $withdrawal = $result->fetch_assoc();
        $stmt->close();

        // Check if the withdrawal exists
        if ($withdrawal) {
            $withdrawQuantity = $withdrawal['WD_QUANTITY'];
            $invId = $withdrawal['INV_ID'];

            // Step 2: Update the inventory to return the withdrawn quantity
            $updateInventory = "UPDATE INVENTORY SET INV_QUANTITY = INV_QUANTITY + ? WHERE INV_ID = ?";
            if ($stmt = $connection->prepare($updateInventory)) {
                $stmt->bind_param("ii", $withdrawQuantity, $invId);
                $stmt->execute();
                $stmt->close();

                // Step 3: Mark the withdrawal as canceled
                $updateWithdrawal = "UPDATE RF_WITHDRAWAL SET WD_DATE = NULL WHERE WD_ID = ?";
                if ($stmt = $connection->prepare($updateWithdrawal)) {
                    $stmt->bind_param("i", $withdrawalId);
                    $stmt->execute();
                    $stmt->close();

                    // Success response
                    echo json_encode(['success' => true, 'message' => 'Withdrawal canceled and quantity returned to inventory']);
                } else {
                    // Error updating the withdrawal
                    echo json_encode(['success' => false, 'message' => 'Failed to update withdrawal status']);
                }
            } else {
                // Error updating the inventory
                echo json_encode(['success' => false, 'message' => 'Failed to update inventory']);
            }
        } else {
            // Withdrawal not found
            echo json_encode(['success' => false, 'message' => 'Withdrawal record not found']);
        }
    } else {
        // Error preparing the SQL statement
        echo json_encode(['success' => false, 'message' => 'Error preparing query']);
    }
} else {
    // No withdrawal_id received
    echo json_encode(['success' => false, 'message' => 'No withdrawal ID provided']);
}

// Close the database connection
$connection->close();
?>
