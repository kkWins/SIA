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

// Fetch pending requisitions within the last 7 days or the most recent approved requisition
$sql = "
SELECT * FROM purchase_or_requisition_form 
WHERE (prf_status = 'pending' AND prf_date > DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND emp_id = ?) 
   OR (prf_status = 'approved' AND emp_id = ?)
ORDER BY prf_date DESC LIMIT 1"; // Select only the most relevant record

$stmt = $connection->prepare($sql);
$stmt->bind_param("ii", $emp_id, $emp_id); // Bind emp_id for both conditions
$stmt->execute();
$result1 = $stmt->get_result();

$response = null; // Initialize response as null

if ($result1->num_rows > 0) {
    // Fetch the single most relevant requisition
    $row = $result1->fetch_assoc();

    // Check if the requisition status is approved or pending
    if ($row['PRF_STATUS'] == 'approved') {
        // Approved requisition query
        $requisitionSql = "SELECT 
            CONCAT(e.EMP_FNAME, ' ', e.EMP_MNAME, ' ', e.EMP_LNAME) AS Employee_Name,
            e.EMP_NUMBER AS Contact_No,
            d.DEPT_NAME AS Department,
            prf.PRF_ID AS Requisition_ID,
            prf.PRF_DATE AS Requisition_Date
        FROM 
            purchase_or_requisition_form prf
        JOIN 
            employee e ON prf.EMP_ID = e.EMP_ID
        JOIN 
            department d ON e.DEPT_ID = d.DEPT_ID
        WHERE 
            prf.PRF_ID = ?";

        $stmt = $connection->prepare($requisitionSql);
        $stmt->bind_param("i", $row['PRF_ID']);
        $stmt->execute();
        $requisitionResult = $stmt->get_result();

        if ($requisitionResult->num_rows > 0) {
            $requisitionRow = $requisitionResult->fetch_assoc();
            $response = [
                'requisition_id' => $requisitionRow['Requisition_ID'],
                'employee_name' => $requisitionRow['Employee_Name'],
                'contact_no' => $requisitionRow['Contact_No'],
                'department' => $requisitionRow['Department'],
                'date' => $requisitionRow['Requisition_Date'],
                'prf_status' => $row['PRF_STATUS'],  // Include prf_status here
                'items' => []
            ];
        } else {
            echo "Requisition details not found.";
            exit;
        }

        // Query for item details
        $itemsSql = "
        SELECT 
            il.IT_ID AS Item_ID,
            i.INV_MODEL_NAME AS Item_Name,
           
            il.IT_DESCRIPTION AS Description,
            il.IT_QUANTITY AS Quantity,
            
            SUM(CASE 
                    WHEN rw.WD_DATE_RECEIVED IS NOT NULL AND rw.WD_DATE IS NOT NULL THEN rw.WD_QUANTITY
                    ELSE 0 
                END) AS Delivered
        FROM 
            item_list il
        LEFT JOIN 
            inventory i ON il.INV_ID = i.INV_ID
        LEFT JOIN 
            rf_withdrawal rw ON il.PRF_ID = rw.PRF_ID AND il.inv_id = rw.inv_id
        WHERE 
            il.PRF_ID = ?
        GROUP BY 
            i.INV_ID";

        $stmt = $connection->prepare($itemsSql);
        $stmt->bind_param("i", $row['PRF_ID']);
        $stmt->execute();
        $itemsResult = $stmt->get_result();

        if ($itemsResult->num_rows > 0) {
            while ($itemRow = $itemsResult->fetch_assoc()) {
                $response['items'][] = [
                    'item_id'=> $itemRow['Item_ID'],
                    'item_name' => $itemRow['Item_Name'],
                    'description' => $itemRow['Description'],
                    'quantity' => $itemRow['Quantity'],
                    'delivered' => $itemRow['Delivered']
                ];
            }
        } else {
            echo "No items found for this requisition.";
            exit;
        }
    } else {
        // Pending requisition query
        $sql = "
        SELECT 
            i.INV_MODEL_NAME AS item_name, 
            il.IT_QUANTITY AS quantity, 
            il.IT_DESCRIPTION AS description
        FROM ITEM_LIST il
        JOIN INVENTORY i ON il.INV_ID = i.INV_ID
        WHERE il.PRF_ID = ?";
        
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("i", $row['PRF_ID']); // Bind PRF_ID
        $stmt->execute();
        $result2 = $stmt->get_result();

        $items = [];
        if ($result2 && $result2->num_rows > 0) {
            while ($item = $result2->fetch_assoc()) {
                $items[] = [
                    'item_name' => $item['item_name'],
                    'quantity' => $item['quantity'],
                    'description' => $item['description']
                ];
            }
        }

        // Build the response for the single requisition
        $response = [
            'prf_id' => $row['PRF_ID'],
            'prf_status' => $row['PRF_STATUS'],
            'prf_date' => $row['PRF_DATE'],
            'items' => $items
        ];
    }
} else {
    // No records found
    $response = ['message' => 'No Pending or Approved Requisitions Found'];
}

// Return the response as JSON
//echo json_encode($response);

$connection->close();
?>
