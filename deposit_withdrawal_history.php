<?php

require_once 'db.php';

// Create connection using the existing db.php configuration
$connection = new mysqli($servername, $username, $password, $database, 3306);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Initialize variables to store the query results
$depositData = [];
$withdrawalData = [];

// Fetch deposit history
$depositSql = "SELECT DP_QUANTITY, DP_DATE, DP_DESCRIPTION, EMP_ID, INV_ID FROM deposit";
$depositResult = $connection->query($depositSql);
if ($depositResult->num_rows > 0) {
    while ($row = $depositResult->fetch_assoc()) {
        $depositData[] = $row; // Store the deposit result in an array
    }
} 

// Fetch withdrawal history
$withdrawalSql = "SELECT WDL_QUANTITY, WDL_DATE, WDL_REASON, EMP_ID, INV_ID FROM withdrawal";
$withdrawalResult = $connection->query($withdrawalSql);
if ($withdrawalResult->num_rows > 0) {
    while ($row = $withdrawalResult->fetch_assoc()) {
        $withdrawalData[] = $row; // Store the withdrawal result in an array
    }
}

$connection->close();
?>
