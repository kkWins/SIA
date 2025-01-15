<?php
require_once 'db.php';

$connection = new mysqli($servername, $username, $password, $database, 3306);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Get POST data
$quantity = $_POST['quantity'];
$model_name = $_POST['model_name'];
$brand = $_POST['brand'];
$location = $_POST['location'];
$date_created = date('Y-m-d H:i:s'); // Current date and time

// Insert query
$sql = "INSERT INTO inventory (INV_QUANTITY, INV_MODEL_NAME, INV_BRAND, INV_LOCATION, INV_DATE_CREATED)
        VALUES (?, ?, ?, ?, ?)";
$stmt = $connection->prepare($sql);
$stmt->bind_param("issss", $quantity, $model_name, $brand, $location, $date_created);

if ($stmt->execute()) {
    echo "New inventory item created successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$connection->close();
?>
