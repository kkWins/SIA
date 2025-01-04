<?php

require_once 'db.php';  // Include database connection

// Create connection using the existing db.php configuration
$connection = new mysqli($servername, $username, $password, $database, 3306);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Get the requisition ID and employee ID from the URL and session
$reqId = $_GET['req_id'];
$employeeId = $_SESSION['ID'];

// SQL query to fetch requisition details (single result)
$requisitionQuery = "
SELECT 
    REQ.PRF_ID AS requisition_id,
    CONCAT(EMP.EMP_FNAME, ' ', EMP.EMP_LNAME) AS employee_name,
    EMP.EMP_NUMBER AS contact_number,
    REQ.PRF_DATE AS date_of_request,
    DEPT.DEPT_NAME AS department_name
FROM 
    purchase_or_requisition_form REQ
LEFT JOIN 
    EMPLOYEE EMP ON REQ.EMP_ID = EMP.EMP_ID
LEFT JOIN 
    DEPARTMENT DEPT ON EMP.DEPT_ID = DEPT.DEPT_ID
LEFT JOIN 
    RF_WITHDRAWAL RF ON REQ.PRF_ID = RF.PRF_ID
WHERE 
    RF.EMP_ID = ?
    AND REQ.PRF_ID = ?
GROUP BY
	REQ.PRF_ID ASC
";

// Prepare and execute the requisition query
$stmt = $connection->prepare($requisitionQuery);
$stmt->bind_param("ii",  $employeeId,$reqId);  // Bind the req_id and employee ID
$stmt->execute();
$requisitionResult = $stmt->get_result();

// Fetch requisition data (only one row)
$requisitionData = $requisitionResult->fetch_assoc();

// Close the statement for requisition
$stmt->close();

// SQL query to fetch withdrawal records (multiple results)
$withdrawalQuery = "
    SELECT 
        RF.WD_ID AS withdrawal_id,
        RF.WD_QUANTITY,
        RF.WD_DATE,
        RF.WD_DATE_RECEIVED,
        RF.WD_DATE_WITHDRAWN,
        RF.WD_DATE_DELIVERED,
        INV.INV_MODEL_NAME,
        INV.INV_LOCATION
    FROM 
        RF_WITHDRAWAL RF
    INNER JOIN 
        INVENTORY INV ON RF.INV_ID = INV.INV_ID
    WHERE 
        RF.PRF_ID = ? AND
        RF.EMP_ID = ? AND
        RF.WD_DATE IS NOT NULL
";

// Prepare and execute the withdrawal query
$stmt = $connection->prepare($withdrawalQuery);
$stmt->bind_param("ii", $reqId, $employeeId);  // Bind the req_id and employee ID
$stmt->execute();
$withdrawalResult = $stmt->get_result();

// Close the statement for withdrawals
$stmt->close();

// Optionally, close the connection (if you're not reusing it elsewhere)
$connection->close();

?>
