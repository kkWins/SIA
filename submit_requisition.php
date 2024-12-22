<?php
session_start();

// Check if the user is logged in and authorized
if (!isset($_SESSION['loggedIn']) || !$_SESSION['loggedIn']) {
    echo "You must be logged in to submit a requisition.";
    exit;
}

// Check if the request method is POST and required data is available
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['items'], $_POST['quantities'], $_POST['reasons'])) {
    // Retrieve the data from the POST request
    $items = $_POST['items'];       // Array of selected items
    $quantities = $_POST['quantities'];  // Array of quantities
    $reasons = $_POST['reasons'];   // Array of reasons

    // Database connection settings
    $servername = "localhost";
    $db_username = "root";  // Use your database username
    $db_password = "";      // Use your database password
    $database = "moonlight_db"; // Use your database name

    // Create database connection
    $connection = new mysqli($servername, $db_username, $db_password, $database);

    // Check for connection error
    if ($connection->connect_error) {
        echo "Connection failed: " . $connection->connect_error;
        exit;
    }

    // Begin transaction to ensure both inserts are done together
    $connection->begin_transaction();

    try {
        // Insert the requisition into the purchase_or_requisition_form table
        $emp_id = $_SESSION['ID']; // Employee ID from session
        $prf_date = date("Y-m-d"); // Current date for the requisition
        $prf_status = 'Pending';  // Initial status of the requisition (can be changed later)
        
        $stmt = $connection->prepare("INSERT INTO purchase_or_requisition_form (PRF_DATE, PRF_STATUS, EMP_ID) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $prf_date, $prf_status, $emp_id);
        $stmt->execute();
        $prf_id = $stmt->insert_id; // Get the PRF_ID of the newly inserted requisition
        $stmt->close();

        // Insert the items into the item_list table
        foreach ($items as $index => $item) {
            $quantity = $quantities[$index];
            $reason = $reasons[$index];

            // Get the inventory ID for the item (assuming a simple name-to-ID lookup, adjust as needed)
            $stmt = $connection->prepare("SELECT INV_ID FROM inventory WHERE Inv_Model_NAME = ?");
            $stmt->bind_param("s", $item);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $inventory = $result->fetch_assoc();
                $inv_id = $inventory['INV_ID'];
                
                // Insert the item into the item_list table
                $stmt = $connection->prepare("INSERT INTO item_list (IT_QUANTITY, IT_DATE, IT_DESCRIPTION, INV_ID, PR_ID) VALUES (?, ?, ?, ?, ?)");
                $it_date = date("Y-m-d");
                $stmt->bind_param("issii", $quantity, $it_date, $reason, $inv_id, $prf_id);
                $stmt->execute();
                $stmt->close();
            } else {
                throw new Exception("Item not found in inventory: $item");
            }
        }

        // Commit the transaction
        $connection->commit();

        // Return success message
        echo "1"; // Success code for front-end to process
    } catch (Exception $e) {
        // Rollback transaction if an error occurs
        $connection->rollback();

        // Return error message
        echo "Error occurred: " . $e->getMessage();
    } finally {
        // Close the database connection
        $connection->close();
    }
} else {
    echo "Invalid request method or missing data.";
}
?>
