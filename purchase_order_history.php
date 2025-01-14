<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

// Session checks
if (!isset($_SESSION['loggedIn']) || !$_SESSION['loggedIn']) {
    echo "<p>Error: Please log in first</p>";
    exit;
}

// Get employee ID from session (checking both possible session variables)
$employeeId = isset($_SESSION['ID']) ? $_SESSION['ID'] : (isset($_SESSION['EMP_ID']) ? $_SESSION['EMP_ID'] : null);

if (!$employeeId) {
    echo "<p>Error: Employee ID not found in session</p>";
    exit;
}

// Check if user is manager or admin
$isAdmin = false;
$isManager = false;
$checkPositionQuery = "SELECT EMP_POSITION FROM employee WHERE EMP_ID = ?";
$positionStmt = $db->prepare($checkPositionQuery);
$positionStmt->bind_param("i", $employeeId);
$positionStmt->execute();
$positionResult = $positionStmt->get_result();
if ($row = $positionResult->fetch_assoc()) {
    $isAdmin = ($row['EMP_POSITION'] === 'Admin');
    $isManager = ($row['EMP_POSITION'] === 'Manager');
}
$positionStmt->close();

echo"<H3>Purchase Order History</H3>";

// Main query modification
$query = "SELECT 
            po.PO_ID,
            po.PO_ORDER_DATE,
            po.PO_ARRIVAL_DATE,
            po.PO_STATUS,
            CONCAT(emp.EMP_FNAME, ' ', emp.EMP_MNAME, ' ', emp.EMP_LNAME) as employee_name,
            sp.SP_NAME as supplier_name,
            dep.DEPT_NAME
          FROM purchase_order po
          JOIN employee emp ON po.EMP_ID = emp.EMP_ID
          JOIN supplier sp ON po.SP_ID = sp.SP_ID
          JOIN department dep ON emp.DEPT_ID = dep.DEPT_ID";

// Only filter by department if user is not an admin or manager
if (!$isAdmin && !$isManager) {
    $query .= " WHERE emp.DEPT_ID = ?";
}

$query .= " ORDER BY po.PO_ORDER_DATE DESC";

$stmt = $db->prepare($query);
if (!$isAdmin && !$isManager) {
    $stmt->bind_param("i", $_SESSION['department_id']);
}

if (!$stmt->execute()) {
    echo "<p>Error executing query: " . $stmt->error . "</p>";
    exit;
}

$result = $stmt->get_result();

// Display the results
if ($result->num_rows > 0) {
    echo "<table class='table table-striped'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>PO ID</th>";
    echo "<th>Employee Name</th>";
    echo "<th>Department</th>";
    echo "<th>Supplier</th>";
    echo "<th>Order Date</th>";
    echo "<th>Expected Arrival</th>";
    echo "<th>Status</th>";
    echo "<th>Action</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['PO_ID']) . "</td>";
        echo "<td>" . htmlspecialchars($row['employee_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['DEPT_NAME']) . "</td>";
        echo "<td>" . htmlspecialchars($row['supplier_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['PO_ORDER_DATE']) . "</td>";
        echo "<td>" . htmlspecialchars($row['PO_ARRIVAL_DATE']) . "</td>";
        echo "<td>" . htmlspecialchars($row['PO_STATUS']) . "</td>";
        echo "<td>";
        echo "<button class='btn btn-sm btn-info view-details' data-bs-toggle='modal' data-bs-target='#itemsModal' data-po-id='" . htmlspecialchars($row['PO_ID']) . "'>View Details</button>";
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</tbody>";
    echo "</table>";
} else {
    echo "<div class='alert alert-info'>No purchase orders found.</div>";
}

$stmt->close();
?>

<!-- Add Modal HTML structure -->
<div class="modal fade" id="itemsModal" tabindex="-1" aria-labelledby="itemsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemsModalLabel">Purchase Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="itemsList"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add JavaScript for modal functionality -->
<script>
$(document).ready(function() {
    $('.view-details').click(function() {
        const poId = $(this).data('po-id');
        
        // Fetch items for this Purchase Order
        $.ajax({
            url: 'get_purchase_order_items.php',
            type: 'POST',
            data: { 
                po_id: poId
            },
            success: function(response) {
                $('#itemsList').html(response);
            },
            error: function() {
                $('#itemsList').html('<div class="alert alert-danger">Error loading items</div>');
            }
        });
    });
});
</script>