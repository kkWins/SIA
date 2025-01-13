<?php
require_once 'db.php';

// Session checks
if (!isset($_SESSION['loggedIn']) || !$_SESSION['loggedIn']) {
    echo "<p>Error: Please log in first</p>";
    exit;
}

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
          JOIN department dep ON emp.DEPT_ID = dep.DEPT_ID
          WHERE emp.DEPT_ID = ?
          ORDER BY po.PO_ORDER_DATE DESC";

$stmt = $db->prepare($query);
$stmt->bind_param("i", $_SESSION['department_id']);

if (!$stmt->execute()) {
    echo "<p>Error executing query: " . $stmt->error . "</p>";
    exit;
}

$result = $stmt->get_result();

// Display table if there are results
if ($result->num_rows > 0) {
    echo "<table class='table table-striped'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>PO ID</th>";
    echo "<th>Employee Name</th>";
    echo "<th>Supplier</th>";
    echo "<th>Department</th>";
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
        echo "<td>" . htmlspecialchars($row['supplier_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['DEPT_NAME']) . "</td>";
        echo "<td>" . htmlspecialchars($row['PO_ORDER_DATE']) . "</td>";
        echo "<td>" . htmlspecialchars($row['PO_ARRIVAL_DATE']) . "</td>";
        echo "<td>";
        switch($row['PO_STATUS']) {
            case 'completed':
                echo "<span class='badge bg-success'>Completed</span>";
                break;
            case 'cancelled':
                echo "<span class='badge bg-danger'>Cancelled</span>";
                break;
            case 'pending':
                echo "<span class='badge bg-warning'>Pending</span>";
                break;
        }
        echo "</td>";
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

// Modal HTML
echo <<<HTML
<div class="modal fade" id="itemsModal" tabindex="-1" aria-labelledby="itemsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
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

<script>
$(document).ready(function() {
    $('.view-details').click(function() {
        const poId = $(this).data('po-id');
        
        // Fetch items for this PO
        $.ajax({
            url: 'get_purchase_order_items.php',
            type: 'POST',
            data: { po_id: poId },
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
HTML;
?>