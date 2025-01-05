<?php

require_once 'db.php';

// Create connection using the existing db.php configuration
$connection = new mysqli($servername, $username, $password, $database, 3306);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

if (isset($_GET['req_id'])) {
    // Query to fetch RF_WITHDRAWAL details along with employee and inventory information
    $query = "
        SELECT 
            RW.WD_ID,
            RW.WD_QUANTITY,
            RW.WD_DATE,
            RW.WD_DATE_RECEIVED,
            RW.WD_DATE_WITHDRAWN,
            RW.WD_DATE_DELIVERED,
            CONCAT(E.EMP_FNAME, ' ', E.EMP_LNAME) AS Withdrawn_By,
            I.INV_MODEL_NAME AS Item_Name
        FROM 
            RF_WITHDRAWAL RW
        JOIN 
            EMPLOYEE E ON RW.EMP_ID = E.EMP_ID
        JOIN 
            INVENTORY I ON RW.INV_ID = I.INV_ID
        WHERE 
            RW.PRF_ID = ? AND RW.WD_DATE_WITHDRAWN IS NOT NULL
        ORDER BY 
            RW.WD_DATE DESC;
    ";

    $stmt = $connection->prepare($query);
    $stmt->bind_param("s", $_GET['req_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $withdrawalData = [];

        while ($row = $result->fetch_assoc()) {
            $withdrawalData[] = [
                'withdrawal_id' => $row['WD_ID'],
                'quantity' => $row['WD_QUANTITY'],
                'withdraw_date' => $row['WD_DATE'],
                'received_date' => $row['WD_DATE_RECEIVED'],
                'date_withdrawn' => $row['WD_DATE_WITHDRAWN'], // Date Withdrawn
                'date_delivered' => $row['WD_DATE_DELIVERED'], // Date Delivered
                'withdrawn_by' => $row['Withdrawn_By'],
                'item_name' => $row['Item_Name']
            ];
        }

        // Store result in a variable for further use
        $finalResult = $withdrawalData;
        
    } else {
        $finalResult = "No withdrawal details found.";
    }
} else {
    // Handle case when 'req_id' is not set
    $finalResult = "No requisition ID provided.";
}

echo json_encode($finalResult);

$connection->close();

?>
