<?php

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

$emp_id = $_SESSION['ID'];

// Fetch purchase or requisition records that are within the last 7 days and have a Pending status




$sql = "SELECT * FROM purchase_or_requisition_form 
WHERE prf_date > DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
AND emp_id = " . $emp_id . " AND prf_status = 'pending'";

$result1 = $connection->query($sql);

$response = []; // Initialize the response variable

if ($result1->num_rows > 0) {
    // There are records where prf_date is within the last 7 days for the specific emp_id
    $row = $result1->fetch_assoc();  // Fetch the first record
    
    // Now query the ITEM_LIST based on PRF_ID
    $sql = "
    SELECT 
        i.INV_MODEL_NAME AS item_name, 
        il.IT_QUANTITY AS quantity, 
        il.IT_DESCRIPTION AS description
    FROM ITEM_LIST il
    JOIN INVENTORY i ON il.INV_ID = i.INV_ID
    WHERE il.PRF_ID = " . $row['PRF_ID'];

    $result2 = $connection->query($sql);

    if ($result2 && $result2->num_rows > 0) {
        $items = [];
        while ($item = $result2->fetch_assoc()) {
            $items[] = [
                'item_name' => $item['item_name'],
                'quantity' => $item['quantity'],
                'description' => $item['description']
            ];
        }
        
        // Store the response data
        $response = [
            'prf_id' => $row['PRF_ID'],
            'prf_status' => $row['PRF_STATUS'], // Include the PRF status from the first query
            'items' => $items // Include the list of items
        ];
    } else {
        // If no items found for the PRF_ID
        $response = ['prf_status' => $row['prf_status'], 'items' => []]; // No items found
    }
} else {
    // No records found where prf_date is within the last 7 days for the specific emp_id
    $response = ['prf_status' => 'No Pending Requisition', 'items' => []]; // No pending requisition
}

// You can now use the $response variable as needed instead of echoing
// For example, you can return it from a function or include it in another file.
