<?php
require_once 'db.php';  // Include the database connection

// Create connection using the existing db.php configuration
$connection = new mysqli($servername, $username, $password, $database, 3306);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Check if withdrawal_id is provided
if (isset($_POST['withdrawal_id'])) {
    $withdrawalId = $_POST['withdrawal_id'];

    // Prepare the SQL statement to update the status of the withdrawal
    // Assuming you have a column `cancelled` in the RF_WITHDRAWAL table to mark the withdrawal as canceled
    $sql = "UPDATE RF_WITHDRAWAL SET WD_DATE = NULL WHERE WD_ID = ?";

    // Prepare the statement
    if ($stmt = $connection->prepare($sql)) {
        // Bind the withdrawal ID parameter
        $stmt->bind_param("i", $withdrawalId);

        // Execute the query
        if ($stmt->execute()) {
            // Successful cancellation
            echo json_encode(['success' => true, 'message' => 'Success to cancel withdrawal']);
        } else {
            // Error during execution
            echo json_encode(['success' => false, 'message' => 'Failed to cancel withdrawal']);
        }

        // Close the statement
        $stmt->close();
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
