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
    // Query for detailed view
    $sql = "SELECT 
        CONCAT(e.EMP_FNAME, ' ', e.EMP_LNAME) AS Employee_Name,
        e.EMP_NUMBER AS Contact_No,
        d.DEPT_NAME AS Department,
        prf.PRF_ID AS Requisition_ID,
        prf.PRF_DATE AS Requisition_Date,
        il.IT_ID AS Item_ID,
        i.INV_MODEL_NAME AS Item_Name,
        il.IT_DESCRIPTION AS Description,
        il.IT_QUANTITY AS Quantity
    FROM 
        item_list il
    JOIN 
        purchase_or_requisition_form prf ON il.PRF_ID = prf.PRF_ID
    JOIN 
        employee e ON prf.EMP_ID = e.EMP_ID
    JOIN 
        department d ON e.DEPT_ID = d.DEPT_ID
    JOIN
        inventory i ON il.INV_ID = i.INV_ID
    WHERE 
        prf.PRF_ID = ? AND e.DEPT_ID = $dept_id;";

    $stmt = $connection->prepare($sql);
    $stmt->bind_param("s", $_GET['req_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $firstRow = $result->fetch_assoc();
        $response = [
            'requisition_id' => $firstRow['Requisition_ID'],
            'employee_name' => $firstRow['Employee_Name'],
            'contact_no' => $firstRow['Contact_No'],
            'department' => $firstRow['Department'],
            'date' => $firstRow['Requisition_Date'],
            'items' => []
        ];
        
        // Reset result pointer
        $result->data_seek(0);
        
        while ($row = $result->fetch_assoc()) {
            $response['items'][] = [
                'item_name' => $row['Item_Name'],
                'description' => $row['Description'],
                'quantity' => $row['Quantity']
            ];
        }
    } else {
        $response = ['items' => ''];
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
        prf.PRF_STATUS IN ('pending') 
        AND e.DEPT_ID = $dept_id
    GROUP BY 
        prf.PRF_ID;";

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