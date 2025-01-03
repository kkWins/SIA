<?php
require_once 'db.php'; // Include your MySQLi database connection file

// Create connection using the existing db.php configuration
$connection = new mysqli($servername, $username, $password, $database, 3306);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Retrieve data sent by the AJAX request
$reqId = $_POST['req_id'];
$withdrawAmount = $_POST['withdraw_amount'];
$itemId = $_POST['item_id'];
$empId = $_POST['emp_id']; // Get the employee ID from the AJAX request

// Step 1: Get the inventory ID and the item quantity (IT_QUANTITY) from ITEM_LIST for the item being withdrawn
$query = "SELECT INV_ID, IT_QUANTITY FROM ITEM_LIST WHERE IT_ID = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $itemId); // 'i' for integer
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

// Check if item exists
if (!$item) {
    echo json_encode(['success' => false, 'message' => 'Item not found']);
    exit;
}

$invId = $item['INV_ID'];
$itemQuantity = $item['IT_QUANTITY'];  // The available quantity in ITEM_LIST

// Step 2: Get the total quantity already withdrawn for the same inventory ID and requisition form ID
$query = "SELECT SUM(WD_QUANTITY) as total_withdrawn FROM RF_WITHDRAWAL WHERE INV_ID = ? AND WD_DATE IS NOT NULL AND PRF_ID = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param("ii", $invId, $reqId); // 'ii' for two integers
$stmt->execute();
$result = $stmt->get_result();
$withdrawnData = $result->fetch_assoc();
$totalWithdrawn = $withdrawnData['total_withdrawn'] ?? 0;

// Step 3: Validate the withdrawal amount

// Check if the total withdrawal (requested + previous) exceeds the available item quantity
if ($withdrawAmount + $totalWithdrawn > $itemQuantity) {
    echo json_encode(['success' => false, 'message' => 'Total withdrawal amount exceeds item quantity in ITEM_LIST']);
    exit;
}

// Check if the withdrawal amount exceeds the available inventory stock
if ($withdrawAmount > $itemQuantity) {
    echo json_encode(['success' => false, 'message' => 'Withdrawal amount exceeds available inventory stock']);
    exit;
}

// Step 4: If checks pass, perform the withdrawal (insert into RF_WITHDRAWAL)
$query = "INSERT INTO RF_WITHDRAWAL (WD_QUANTITY, WD_DATE, WD_DATE_RECEIVED, EMP_ID, PRF_ID, INV_ID) 
          VALUES (?, NOW(), NULL, ?, ?, ?)";
$stmt = $connection->prepare($query);
$stmt->bind_param("iiii", $withdrawAmount, $empId, $reqId, $invId); // 'iiii' for four integers
$stmt->execute();

// Return success response
echo json_encode(['success' => true, 'message' => 'Withdrawal successful!']);

$connection->close();
?>
