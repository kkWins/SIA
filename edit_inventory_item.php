<?php

// Include the existing db.php for database configuration
require_once 'db.php';

// Create connection using the existing db.php configuration
$connection = new mysqli($servername, $username, $password, $database, 3306);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the data from the POST request
    $itemId = $_POST['item_id'];
    $location = $_POST['location'];

    // Validate the data
    if (!empty($itemId) && !empty($location)) {
        // Prepare the query to update the inventory location
        $query = "UPDATE inventory SET INV_LOCATION = ? WHERE INV_ID = ?";
        
        // Prepare the statement
        $stmt = $connection->prepare($query);
        
        // Bind parameters to the statement
        $stmt->bind_param('si', $location, $itemId);

        // Execute the query
        if ($stmt->execute()) {
            echo "Item location updated successfully.";
        } else {
            echo "Error updating item location: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Invalid input data.";
    }
}

// Close the database connection
$connection->close();

?>
