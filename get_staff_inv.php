<?php

// Include the existing db.php for database configuration
require_once 'db.php';

// Create connection using the existing db.php configuration
$connection = new mysqli($servername, $username, $password, $database, 3306);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Prepare the SQL query to get employees from the inventory department
$sql = "SELECT EMP_ID, EMP_FNAME, EMP_LNAME FROM employee 
        WHERE  DEPT_ID = (SELECT DEPT_ID FROM department WHERE DEPT_NAME = 'Inventory')";

// Execute the query
$result = $connection->query($sql);

// Initialize an array to store the staff list
$staff = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $staff[] = [
            'EMP_ID' => $row['EMP_ID'],
            'EMP_NAME' => $row['EMP_FNAME'] . ' ' . $row['EMP_LNAME']
        ];
    }
}

// Return the result as JSON
echo json_encode($staff);

// Close the connection
$connection->close();
?>
