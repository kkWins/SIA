<?php
require_once 'db.php';  // Include the database connection

// Check if the 'wd_id' is provided via POST request
if (isset($_POST['wd_id'])) {
    $wd_id = $_POST['wd_id'];

    // Create connection using the existing db.php configuration
    $connection = new mysqli($servername, $username, $password, $database, 3306);

    // Check connection
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    // Step 1: Fetch the PRF_ID from the withdrawal record
    $fetchRequisitionDetails = "SELECT PRF_ID FROM RF_WITHDRAWAL WHERE WD_ID = ?";
    $stmt = $connection->prepare($fetchRequisitionDetails);
    $stmt->bind_param("i", $wd_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $requisitionDetails = $result->fetch_assoc();

    if ($requisitionDetails) {
        $prf_id = $requisitionDetails['PRF_ID'];

        // Step 2: Check if the requisition is closed
        $checkRequisitionStatus = "SELECT PRF_STATUS FROM PURCHASE_OR_REQUISITION_FORM WHERE PRF_ID = ?";
        $stmt = $connection->prepare($checkRequisitionStatus);
        $stmt->bind_param("i", $prf_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $status = $result->fetch_assoc();

        // Step 3: Return the status of the requisition
        if ($status['PRF_STATUS'] == 'closed') {
            echo 'closed';
        } else {
            echo 'open';
        }
    } else {
        echo 'error';
    }

    // Close the statement and connection
    $stmt->close();
    $connection->close();
} else {
    echo 'error';  // If no WD_ID was provided
}
?>
