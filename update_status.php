<?php

require_once 'db.php';

// Create connection using the existing db.php configuration
$connection = new mysqli($servername, $username, $password, $database, 3306);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Define the PRF_ID you want to update
$prf_id = $_POST['PRF_ID'] ?? null; // Assuming the ID is passed via POST

if ($prf_id) {
    // Check if there are any rows in rf_withdrawal for this PRF_ID
    $checkQuery = "
        SELECT COUNT(*) AS matching_rows
        FROM rf_withdrawal
        WHERE PRF_ID = ? AND (
            WD_DATE IS NOT NULL OR
            WD_DATE_RECEIVED IS NOT NULL OR
            WD_DATE_DELIVERED IS NOT NULL OR
            WD_DATE_WITHDRAWN IS NOT NULL
        )
    ";

    $stmt = $connection->prepare($checkQuery);
    $stmt->bind_param("i", $prf_id);
    $stmt->execute();
    $stmt->bind_result($matchingRows);
    $stmt->fetch();
    $stmt->close();

    if ($matchingRows > 0) {
        // If any rows exist with not all dates NULL, prevent closing the status
        echo "Cannot close the requisition since there are still pending task/delivery";
    } else {
        // Proceed to update the PRF_STATUS to 'closed'
        $updateQuery = "UPDATE purchase_or_requisition_form SET PRF_STATUS = 'closed' WHERE PRF_ID = ?";

        $stmt = $connection->prepare($updateQuery);
        $stmt->bind_param("i", $prf_id);

        if ($stmt->execute()) {
            echo "Status updated to 'closed' successfully.";
        } else {
            echo "Error updating record: " . $connection->error;
        }

        $stmt->close();
    }
} else {
    echo "PRF_ID is missing. Please provide a valid ID.";
}

// Close the connection
$connection->close();

?>
