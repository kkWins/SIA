<?php

// Database connection
$servername = "localhost";
$username = "root";
$password = ""; // Add your password if applicable
$database = "moonlight_db";

// Create connection
$db = new mysqli($servername, $username, $password, $database, 3306);

// Check connection
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

?>