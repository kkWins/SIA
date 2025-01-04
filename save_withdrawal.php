<?php

require_once 'db.php';  // Include database connection

// Create connection using the existing db.php configuration
$connection = new mysqli($servername, $username, $password, $database, 3306);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Check if data is received via POST
if (isset($_POST['withdrawal_id'])) {
    $withdrawalId = $_POST['withdrawal_id'];
    $wdDateWithdrawn = isset($_POST['wd_date_withdrawn']) ? $_POST['wd_date_withdrawn'] : null;
    $wdDateDelivered = isset($_POST['wd_date_delivered']) ? $_POST['wd_date_delivered'] : null;

    // Prepare SQL update query based on which dates are sent
    $updateFields = [];
    $params = [];
    $types = '';

    if ($wdDateWithdrawn) {
        $updateFields[] = "WD_DATE_WITHDRAWN = ?";
        $params[] = $wdDateWithdrawn;
        $types .= "s";  // string type for date
    }

    if ($wdDateDelivered) {
        $updateFields[] = "WD_DATE_DELIVERED = ?";
        $params[] = $wdDateDelivered;
        $types .= "s";  // string type for date
    }

    // If no dates are provided, return error
    if (empty($updateFields)) {
        echo json_encode(['success' => false, 'message' => 'No valid date provided to update.']);
        exit();
    }

    // Prepare the SQL query with only the fields that need to be updated
    $query = "UPDATE RF_WITHDRAWAL SET " . implode(", ", $updateFields) . " WHERE WD_ID = ?";
    $params[] = $withdrawalId;
    $types .= "i";  // integer type for withdrawal ID

    // Prepare and execute the query
    if ($stmt = $connection->prepare($query)) {
        // Bind parameters and execute the statement
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        // Check if the update was successful
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes were made.']);
        }

        // Close the statement
        $stmt->close();
    } else {
        // Handle error if the query preparation fails
        echo json_encode(['success' => false, 'message' => 'Query preparation failed.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing withdrawal ID.']);
}

// Close the connection
$connection->close();
?>
