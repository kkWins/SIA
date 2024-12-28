<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['prf_id'])) {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "moonlight_db";

    $connection = new mysqli($servername, $username, $password, $database);
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    // Begin a transaction
    $connection->begin_transaction();

    try {
        // Get the PRF_ID from POST
        $PRF_ID = $_POST['prf_id'];

        // Prepare and execute the SQL query to fetch PRF_STATUS
        $stmt = $connection->prepare("SELECT PRF_STATUS FROM purchase_or_requisition_form WHERE PRF_ID = ?");
        $stmt->bind_param("i", $PRF_ID); // Assuming PRF_ID is an integer
        $stmt->execute();
        $stmt->bind_result($prf_status);

        // Fetch the result
        if ($stmt->fetch()) {
            // Close the statement after fetching the result
            $stmt->close();

            if ($prf_status === "Pending") {
                // Prepare and execute deletion of associated items from item_list
                $delete_items_stmt = $connection->prepare("DELETE FROM item_list WHERE PRF_ID = ?");
                $delete_items_stmt->bind_param("i", $PRF_ID);
                $delete_items_stmt->execute();
                $delete_items_stmt->close();

                // Now, delete the PRF record from purchase_or_requisition_form
                $delete_prf_stmt = $connection->prepare("DELETE FROM purchase_or_requisition_form WHERE PRF_ID = ?");
                $delete_prf_stmt->bind_param("i", $PRF_ID);
                $delete_prf_stmt->execute();
                $delete_prf_stmt->close();

                // Commit the transaction
                $connection->commit();

                // Return success
                echo 1;
            } elseif ($prf_status === "Approved") {
                // Return "Approved" status
                echo 2;
            } elseif ($prf_status === "Rejected") {
                // Return "Rejected" status
                echo 3;
            }
        } else {
            // Return failure if no record found
            echo 0;
        }

    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $connection->rollback();
        echo 0;
    } finally {
        // Close the connection
        $connection->close();
    }
} else {
    echo 0;
}
?>
