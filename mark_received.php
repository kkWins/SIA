<?php
require_once 'db.php';  // Include the database connection

// Check if the 'wd_id' is provided via POST request
if (isset($_POST['wd_id'])) {
    // Get the WD_ID from the POST request
    $wd_id = $_POST['wd_id'];

    // Prepare the SQL query to update the WD_DATE_RECEIVED for the given WD_ID
    $query = "UPDATE RF_WITHDRAWAL SET WD_DATE_RECEIVED = NOW() WHERE WD_ID = ? AND WD_DATE_RECEIVED IS NULL";

    // Create connection using the existing db.php configuration
    $connection = new mysqli($servername, $username, $password, $database, 3306);

    // Check connection
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    // Prepare and bind the statement
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $wd_id);

    // Execute the query
    if ($stmt->execute()) {
        echo 'success';  // Return success if the update was successful
    } else {
        echo 'error';  // Return error if there was an issue
    }

    // Close the statement and connection
    $stmt->close();
    $connection->close();
} else {
    echo 'error';  // If no WD_ID was provided
}
?>
