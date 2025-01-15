<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

// Prevent any whitespace or extra output
ob_clean();

// Check if logged in
if (!isset($_SESSION['loggedIn']) || !$_SESSION['loggedIn']) {
    die("Please log in first");
}

// Check if PO_ID was provided
if (!isset($_POST['po_id'])) {
    die("No purchase order ID provided");
}

$query = "SELECT 
            pol.POL_ID,
            pol.POL_QUANTITY,
            pol.POL_PRICE,
            (pol.POL_QUANTITY * pol.POL_PRICE) as total_price,
            i.INV_MODEL_NAME,
            i.INV_BRAND,
            sp.SP_NAME as supplier_name,
            sp.SP_ADDRESS,
            sp.SP_NUMBER,
            CONCAT(emp.EMP_FNAME, ' ', emp.EMP_MNAME, ' ', emp.EMP_LNAME) as employee_name,
            emp.EMP_NUMBER,
            dep.DEPT_NAME
          FROM po_list pol
          JOIN inventory i ON pol.INV_ID = i.INV_ID
          JOIN purchase_order po ON pol.PO_ID = po.PO_ID
          JOIN employee emp ON po.EMP_ID = emp.EMP_ID
          JOIN supplier sp ON po.SP_ID = sp.SP_ID
          JOIN department dep ON emp.DEPT_ID = dep.DEPT_ID
          WHERE pol.PO_ID = ?";

$stmt = $db->prepare($query);
$stmt->bind_param("s", $_POST['po_id']);

if (!$stmt->execute()) {
    die("Error executing query: " . $stmt->error);
}

$result = $stmt->get_result();
ob_start(); // Start output buffering

if ($result->num_rows > 0) {
    // Store the first row data for supplier and employee details
    $firstRow = $result->fetch_assoc();
    
    // Store supplier and employee info
    $supplierInfo = [
        'name' => $firstRow['supplier_name'],
        'address' => $firstRow['SP_ADDRESS'],
        'contact' => $firstRow['SP_NUMBER']
    ];
    
    $employeeInfo = [
        'name' => $firstRow['employee_name'],
        'number' => $firstRow['EMP_NUMBER'],
        'company' => 'Moonlight',
        'address' => 'Logarta St 6014 Mandaue City, Philippines'
    ];
    
    // Start building the table
    echo "<table class='table table-bordered'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>Item Name</th>";
    echo "<th>Brand</th>";
    echo "<th>Quantity</th>";
    echo "<th>Unit Price</th>";
    echo "<th>Total Price</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    
    $grand_total = 0;
    
    // Process first row
    echo "<tr>";
    echo "<td>" . htmlspecialchars($firstRow['INV_MODEL_NAME']) . "</td>";
    echo "<td>" . htmlspecialchars($firstRow['INV_BRAND']) . "</td>";
    echo "<td>" . htmlspecialchars($firstRow['POL_QUANTITY']) . "</td>";
    echo "<td>₱" . number_format($firstRow['POL_PRICE'], 2) . "</td>";
    echo "<td>₱" . number_format($firstRow['total_price'], 2) . "</td>";
    echo "</tr>";
    
    $grand_total = $firstRow['total_price'];
    
    // Process remaining rows
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['INV_MODEL_NAME']) . "</td>";
        echo "<td>" . htmlspecialchars($row['INV_BRAND']) . "</td>";
        echo "<td>" . htmlspecialchars($row['POL_QUANTITY']) . "</td>";
        echo "<td>₱" . number_format($row['POL_PRICE'], 2) . "</td>";
        echo "<td>₱" . number_format($row['total_price'], 2) . "</td>";
        echo "</tr>";
        
        $grand_total += $row['total_price'];
    }
    
    echo "<tr class='table-info'>";
    echo "<td colspan='4' class='text-end'><strong>Grand Total:</strong></td>";
    echo "<td><strong>₱" . number_format($grand_total, 2) . "</strong></td>";
    echo "</tr>";
    
    echo "</tbody>";
    echo "</table>";
} else {
    echo "<div class='alert alert-info'>No items found for this purchase order.</div>";
}

$itemsHtml = ob_get_clean();

$response = [
    'supplier' => $supplierInfo,
    'employee' => $employeeInfo,
    'items' => $itemsHtml
];

echo json_encode($response);
?>
