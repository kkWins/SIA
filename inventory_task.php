<?php

require_once 'db.php';

// Create connection using the existing db.php configuration
$connection = new mysqli($servername, $username, $password, $database, 3306);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

$withdrawals = [];  // Variable to store withdrawal records
$pendingCount = 0;  // Variable to store the count of pending withdrawals

if (isset($_SESSION['ID'])) {
    $employeeId = $_SESSION['ID'];

    // Query to fetch requisition data along with department name and employee name
    $query = "
    SELECT 
        REQ.PRF_ID AS requisition_id,
        DEPT.DEPT_NAME,
        CONCAT(EMP.EMP_FNAME, ' ', EMP.EMP_LNAME) AS employee_name
    FROM 
        purchase_or_requisition_form REQ
    INNER JOIN 
        EMPLOYEE EMP ON REQ.EMP_ID = EMP.EMP_ID
    INNER JOIN 
        DEPARTMENT DEPT ON EMP.DEPT_ID = DEPT.DEPT_ID
    LEFT JOIN 
        RF_WITHDRAWAL RF ON REQ.PRF_ID = RF.PRF_ID  -- Joining RF_WITHDRAWAL based on PRF_ID
    WHERE 
        RF.EMP_ID = ? AND
        REQ.PRF_STATUS != 'pending' AND
        (RF.WD_DATE_WITHDRAWN IS NULL OR RF.WD_DATE_DELIVERED IS NULL)  -- Filtering based on withdrawal status
    GROUP BY 
        REQ.PRF_ID, DEPT.DEPT_NAME, EMP.EMP_FNAME, EMP.EMP_LNAME  -- Grouping by requisition and employee info
    ORDER BY 
        REQ.PRF_DATE DESC
";

    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $employeeId); // Bind the employee ID to the query
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $withdrawals[] = $row;
        }
    } else {
        // No requisition records found
        $withdrawals = [];  // Empty array if no records are found
    }

    // Query to count pending RF_WITHDRAWAL records for the employee
    $countQuery = "
        SELECT COUNT(*) AS pending_count
        FROM RF_WITHDRAWAL
        WHERE EMP_ID = ? 
        AND (WD_DATE_WITHDRAWN IS NULL OR WD_DATE_DELIVERED IS NULL)
        GROUP BY PRF_ID
        ORDER BY PRF_ID DESC
    ";
    
    $pendingCounts = []; // To store pending counts

    $countStmt = $connection->prepare($countQuery);
    $countStmt->bind_param("i", $employeeId); // Bind the employee ID to the count query
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    
    if ($countResult->num_rows > 0) {
        while ($row = $countResult->fetch_assoc()) {
            $pendingCounts[] = $row['pending_count']; // Store pending counts as simple array entries
        }
    }
    

    $stmt->close();
    $countStmt->close();
} else {
    // If session ID is not set
    $withdrawals = [];  // Empty array if session ID is not set
}

$connection->close();
?>
