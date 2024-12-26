<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = ""; // Add your password if applicable
$database = "moonlight_db";

// Create connection
$connection = new mysqli($servername, $username, $password, $database, 3306);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Fetch inventory data
$sql = "SELECT INV_ID, INV_MODEL_NAME FROM inventory";
$result = $connection->query($sql);

if ($result && $result->num_rows > 0) {
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = [
            'id' => $row['INV_ID'],     // Include the inventory ID
            'name' => $row['INV_MODEL_NAME'] // Include the inventory model name
        ];
    }
    echo json_encode($items);
} else {
    echo json_encode([]); // Return an empty array if no data
}

$connection->close();
?>
