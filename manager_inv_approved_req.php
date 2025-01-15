<?php

require_once 'db.php';

// Create connection using the existing db.php configuration
$connection = new mysqli($servername, $username, $password, $database, 3306);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Check if we're requesting details for a specific requisition
if (isset($_GET['req_id'])) {
    // Query for requisition details (same as your original code)
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
        prf.PRF_ID = ?;";

    $stmt = $connection->prepare($requisitionSql);
    $stmt->bind_param("s", $_GET['req_id']);
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
            'items' => []
        ];
    } else {
        echo "Requisition details not found.";
        exit;
    }

    // Get employees from the "Inventory" department
    $staffSql = "SELECT e.EMP_ID, CONCAT(EMP_FNAME, ' ', EMP_LNAME) AS Employee_Name
                 FROM employee e
                 JOIN department d ON e.DEPT_ID = d.DEPT_ID
                 WHERE d.DEPT_NAME = 'Inventory'";  // Assuming the department name is 'Inventory'
    
    $staffResult = $connection->query($staffSql);
    $response['staff'] = [];
    
    if ($staffResult->num_rows > 0) {
        while ($staffRow = $staffResult->fetch_assoc()) {
            $response['staff'][] = [
                'emp_id' => $staffRow['EMP_ID'],
                'employee_name' => $staffRow['Employee_Name']
            ];
        }
    }

    // Query for item details (same as your original code)
$itemsSql = "SELECT 
            il.IT_ID AS Item_ID,
            i.INV_MODEL_NAME AS Item_Name,
            i.INV_QUANTITY AS Stock,
            il.IT_DESCRIPTION AS Description,
            il.IT_QUANTITY AS Quantity,
            SUM(CASE 
                    WHEN rw.WD_DATE IS NOT NULL THEN rw.WD_QUANTITY
                    ELSE 0 
                END) AS Withdrawed,
            SUM(CASE 
                    WHEN rw.WD_DATE_RECEIVED IS NOT NULL AND rw.WD_DATE IS NOT NULL THEN rw.WD_QUANTITY
                    ELSE 0 
                END) AS Delivered,
            SUM(CASE 
                    WHEN rw.WD_DATE IS NOT NULL 
                        AND rw.WD_DATE_WITHDRAWN IS NULL 
                        AND rw.WD_DATE_DELIVERED IS NULL 
                        AND rw.WD_DATE_RECEIVED IS NULL 
                    THEN rw.WD_QUANTITY
                    ELSE 0 
                END) AS To_Be_Delivered
        FROM 
            item_list il
        LEFT JOIN 
            inventory i ON il.INV_ID = i.INV_ID
        LEFT JOIN 
            rf_withdrawal rw ON il.PRF_ID = rw.PRF_ID AND il.INV_ID = rw.INV_ID
        WHERE 
            il.PRF_ID = ? 
        GROUP BY 
            i.INV_ID";


    $stmt = $connection->prepare($itemsSql);
    $stmt->bind_param("s", $_GET['req_id']);
    $stmt->execute();
    $itemsResult = $stmt->get_result();

    if ($itemsResult->num_rows > 0) {
        while ($row = $itemsResult->fetch_assoc()) {
            $response['items'][] = [
                'item_id' => $row['Item_ID'],
                'item_name' => $row['Item_Name'],
                'description' => $row['Description'],
                'quantity' => $row['Quantity'],
                'stock' => $row['Stock'],
                'withdrawed' => $row['Withdrawed'],
                'delivered' => $row['Delivered']
            ];
        }
    } else {
        echo "No items found for this requisition.";
        exit;
    }

} else {
    // Query for list view - modified to use GROUP BY
    $sql = "SELECT 
        CONCAT(e.EMP_FNAME, ' ', e.EMP_LNAME) AS Employee_Name,
        e.DEPT_ID AS Department_ID,
        prf.PRF_ID AS Requisition_ID,
        prf.PRF_DATE AS Submitted_Date,
        prf.PRF_STATUS AS Requisition_Status
    FROM 
        purchase_or_requisition_form prf
    JOIN 
        employee e ON prf.EMP_ID = e.EMP_ID
    JOIN 
        department d ON e.DEPT_ID = d.DEPT_ID
    WHERE 
        prf.PRF_STATUS = 'approved' 
    GROUP BY 
        prf.PRF_ID
    ORDER BY prf.prf_id DESC;
        ";

    $result = $connection->query($sql);
    $response = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $response[] = [
                'requisition_id' => $row['Requisition_ID'],
                'employee_name' => $row['Employee_Name'],
                'submitted_date' => $row['Submitted_Date'],
                'requisition_status' => $row['Requisition_Status']
            ];
        }
    } else {
        $response = ['status' => 'No Pending Requisitions'];
    }
}

$connection->close();