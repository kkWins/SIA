<?php

require_once 'db.php';

// Create connection using the existing db.php configuration
$connection = new mysqli($servername, $username, $password, $database, 3306);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Query for inventory data
$sql = "SELECT 
            INV_ID,
            INV_QUANTITY,
            INV_MODEL_NAME,
            INV_BRAND,
            INV_LOCATION,
            INV_DATE_CREATED
        FROM 
            inventory";
$result = $connection->query($sql);

// Prepare the response
$response = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $response[] = [
            'id' => $row['INV_ID'],
            'quantity' => $row['INV_QUANTITY'],
            'model_name' => $row['INV_MODEL_NAME'],
            'brand' => $row['INV_BRAND'],
            'location' => $row['INV_LOCATION'],
            'date_created' => $row['INV_DATE_CREATED']
        ];
    }
} else {
    $response = ['status' => 'No inventory items found.'];
}

$connection->close();
