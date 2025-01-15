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

    // Step 1: Update the WD_DATE_RECEIVED field for the given WD_ID
    $updateWithdrawal = "UPDATE RF_WITHDRAWAL SET WD_DATE_RECEIVED = NOW() WHERE WD_ID = ? AND WD_DATE_RECEIVED IS NULL";
    $stmt = $connection->prepare($updateWithdrawal);
    $stmt->bind_param("i", $wd_id);

    if ($stmt->execute()) {
        // Fetch the PRF_ID and INV_ID for the withdrawal record
        $fetchRequisitionDetails = "SELECT PRF_ID, INV_ID FROM RF_WITHDRAWAL WHERE WD_ID = ?";
        $stmt = $connection->prepare($fetchRequisitionDetails);
        $stmt->bind_param("i", $wd_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $requisitionDetails = $result->fetch_assoc();

        if ($requisitionDetails) {
            $prf_id = $requisitionDetails['PRF_ID'];

            // Step 2: Query to get the sum of WD_QUANTITY (only when WD_DATE_RECEIVED and WD_DATE are not NULL) grouped by INV_ID for the given PRF_ID
            $sumWithdrawalsQuery = "
                SELECT INV_ID, SUM(CASE 
                                      WHEN WD_DATE_RECEIVED IS NOT NULL AND WD_DATE IS NOT NULL THEN WD_QUANTITY
                                      ELSE 0 
                                  END) AS Delivered
                FROM RF_WITHDRAWAL
                WHERE PRF_ID = ?
                GROUP BY INV_ID
            ";
            $stmt = $connection->prepare($sumWithdrawalsQuery);
            $stmt->bind_param("i", $prf_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $withdrawalData = [];

            // Fetch all sum of withdrawals by INV_ID for this PRF_ID
            while ($withdrawalRow = $result->fetch_assoc()) {
                $withdrawalData[$withdrawalRow['INV_ID']] = $withdrawalRow['Delivered'];
            }

            // Step 3: Compare ITEM_LIST quantity with the total withdrawal quantity
            $fulfilled = true; // Assume the requisition is fulfilled unless proven otherwise

            $fetchItemList = "
                SELECT IT_QUANTITY, INV_ID
                FROM ITEM_LIST
                WHERE PRF_ID = ?
            ";
            $stmtItemList = $connection->prepare($fetchItemList);
            $stmtItemList->bind_param("i", $prf_id);
            $stmtItemList->execute();
            $itemResult = $stmtItemList->get_result();

            // Loop through each item in ITEM_LIST
            while ($itemRow = $itemResult->fetch_assoc()) {
                $inv_id = $itemRow['INV_ID'];
                $item_quantity = $itemRow['IT_QUANTITY'];

                // Check if there is a corresponding sum of withdrawals for this INV_ID
                $total_delivered = isset($withdrawalData[$inv_id]) ? $withdrawalData[$inv_id] : 0;

                // If the total delivered quantity is less than the item quantity, the requisition is not fulfilled
                if ($total_delivered < $item_quantity) {
                    $fulfilled = false;
                    break;
                }
            }

            // Step 4: If all items are fulfilled, update the requisition status to 'closed'
            if ($fulfilled) {
                $updateRequisitionStatus = "UPDATE PURCHASE_OR_REQUISITION_FORM SET PRF_STATUS = 'closed' WHERE PRF_ID = ?";
                $stmt = $connection->prepare($updateRequisitionStatus);
                $stmt->bind_param("i", $prf_id);
                $stmt->execute();
            }

            echo 'success'; // Return success if the process completes
        } else {
            echo 'error'; // Error: No requisition details found
        }
    } else {
        echo 'error'; // Error updating WD_DATE_RECEIVED
    }

    // Close the statement and connection
    $stmt->close();
    $connection->close();
} else {
    echo 'error'; // If no WD_ID was provided
}
?>
