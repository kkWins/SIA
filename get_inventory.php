<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = ""; // If you have a password, include it here
$database = "moonlight_dbs";

// Create connection
$connection = new mysqli($servername, $username, $password, $database, 3306);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Fetch inventory data
$sql = "SELECT INV_MODEL_NAME FROM inventory";
$result = $connection->query($sql);

if ($result && $result->num_rows > 0) {
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = [
            'name' => $row['INV_MODEL_NAME']
        ];
    }
    echo json_encode($items);
} else {
    echo json_encode([]);
}

$connection->close();
?>
