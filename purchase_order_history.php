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
    $isManager = ($row['EMP_POSITION'] === 'Manager');
}
$positionStmt->close();

echo"<H3>Purchase Order Historyyy</H3>";

// Get all suppliers for the dropdown
$supplierQuery = "SELECT DISTINCT SP_NAME FROM supplier ORDER BY SP_NAME";
$supplierResult = $db->query($supplierQuery);

// Get unique dates for both dropdowns
$orderDatesQuery = "SELECT DISTINCT DATE_FORMAT(PO_ORDER_DATE, '%y/%m/%d') as formatted_date 
                   FROM purchase_order 
                   ORDER BY PO_ORDER_DATE DESC";
$orderDatesResult = $db->query($orderDatesQuery);

$arrivalDatesQuery = "SELECT DISTINCT DATE_FORMAT(PO_ARRIVAL_DATE, '%y/%m/%d') as formatted_date 
                     FROM purchase_order 
                     ORDER BY PO_ARRIVAL_DATE DESC";
$arrivalDatesResult = $db->query($arrivalDatesQuery);

// Update the search controls
echo <<<HTML
<div class="mb-4">
    <div class="row g-3">
        <!-- Supplier Dropdown -->
        <div class="col-md-4">
            <label class="form-label">Supplier</label>
            <select class="form-select" id="supplierFilter">
                <option value="">All Suppliers</option>
HTML;

while ($supplier = $supplierResult->fetch_assoc()) {
    echo "<option value='" . htmlspecialchars($supplier['SP_NAME']) . "'>" . htmlspecialchars($supplier['SP_NAME']) . "</option>";
}

echo <<<HTML
            </select>
        </div>

        <!-- Order Date Search -->
        <div class="col-md-4">
            <label class="form-label">Search Order Date (YY/MM/DD)</label>
            <input type="text" class="form-control" id="orderDateSearch" placeholder="Search Order Date">
        </div>

        <!-- Expected Arrival Search -->
        <div class="col-md-4">
            <label class="form-label">Search Expected Arrival (YY/MM/DD)</label>
            <input type="text" class="form-control" id="arrivalDateSearch" placeholder="Search Expected Arrival">
        </div>
    </div>
    
    <!-- Clear Filters Button -->
    <div class="row mt-2">
        <div class="col">
            <button class="btn btn-secondary" id="clearFilters">Clear All Filters</button>
        </div>
    </div>
</div>
HTML;

// Main query (remove the search-related WHERE clauses)
$query = "SELECT 
            po.PO_ID,
            po.PO_ORDER_DATE,
            po.PO_ARRIVAL_DATE,
            po.PO_STATUS,
            CONCAT(emp.EMP_FNAME, ' ', emp.EMP_MNAME, ' ', emp.EMP_LNAME) as employee_name,
            emp.EMP_NUMBER,
            sp.SP_NAME as supplier_name,
            sp.SP_ADDRESS,
            sp.SP_NUMBER,
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

// Prepare and bind parameters
$stmt = $db->prepare($query);

if ($stmt) {
    $types = "";
    $params = array();
    
    if (!$isAdmin && !$isManager) {
        $types .= "i";
        $params[] = &$_SESSION['department_id'];
    }
    
    if (!empty($params)) {
        array_unshift($params, $types);
        call_user_func_array(array($stmt, 'bind_param'), $params);
    }
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
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6>Supplier Details</h6>
                        <div id="supplierDetails"></div>
                    </div>
                    <div class="col-md-6">
                        <h6>Ship To</h6>
                        <div id="shipToDetails"></div>
                    </div>
                </div>
                <h6>Items</h6>
                <div id="itemsList"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>

</div>

<!-- Add JavaScript for modal functionality -->
<script>
$(document).ready(function() {
    // Function to format input as YY/MM/DD while typing
    function formatDateInput(input) {
        let value = input.value.replace(/\D/g, ''); // Remove non-digits
        if (value.length > 0) {
            // Add slashes automatically
            if (value.length <= 2) { // Year
                value = value;
            } else if (value.length <= 4) { // Year + Month
                value = value.slice(0, 2) + '/' + value.slice(2);
            } else { // Year + Month + Day
                value = value.slice(0, 2) + '/' + value.slice(2, 4) + '/' + value.slice(4, 6);
            }
            input.value = value;
        }
    }

    // Function to filter table rows
    function filterTable() {
        const supplierFilter = $('#supplierFilter').val();
        const orderDateSearch = $('#orderDateSearch').val();
        const arrivalDateSearch = $('#arrivalDateSearch').val();

        $('tbody tr').each(function() {
            const row = $(this);
            const supplier = row.find('td:eq(1)').text();
            const orderDate = row.find('td:eq(2)').text(); // Format: YY/MM/DD
            const arrivalDate = row.find('td:eq(3)').text(); // Format: YY/MM/DD

            // Check supplier
            const matchesSupplier = !supplierFilter || supplier === supplierFilter;

            // Check order date - progressive matching
            const matchesOrderDate = !orderDateSearch || orderDate.startsWith(orderDateSearch);

            // Check arrival date - progressive matching
            const matchesArrivalDate = !arrivalDateSearch || arrivalDate.startsWith(arrivalDateSearch);

            // Show/hide row based on all filters
            if (matchesSupplier && matchesOrderDate && matchesArrivalDate) {
                row.show();
            } else {
                row.hide();
            }
        });
    }

    // Handle date input formatting and filtering
    $('#orderDateSearch, #arrivalDateSearch').on('input', function() {
        formatDateInput(this);
        filterTable();
    });

    // Prevent non-numeric input
    $('#orderDateSearch, #arrivalDateSearch').on('keypress', function(e) {
        if (!/\d/.test(e.key)) {
            e.preventDefault();
        }
    });

    // Attach event listener for supplier
    $('#supplierFilter').on('change', filterTable);

    // Clear all filters
    $('#clearFilters').click(function() {
        $('#supplierFilter').val('');
        $('#orderDateSearch, #arrivalDateSearch').val('');
        $('tbody tr').show();
    });

    $('.view-details').click(function() {
        const poId = $(this).data('po-id');
        
        // Fetch items and details for this Purchase Order
        $.ajax({
            url: 'get_purchase_order_items.php',
            type: 'POST',
            data: { 
                po_id: poId
            },
            success: function(response) {
                const data = JSON.parse(response);
                
                // Update supplier details
                $('#supplierDetails').html(`
                    <p><strong>Name:</strong> ${data.supplier.name}</p>
                    <p><strong>Address:</strong> ${data.supplier.address}</p>
                    <p><strong>Contact:</strong> ${data.supplier.contact}</p>
                `);
                
                // Update ship to details
                $('#shipToDetails').html(`
                    <p><strong>Name:</strong> ${data.employee.name}</p>
                    <p><strong>Company:</strong> ${data.employee.company}</p>
                    <p><strong>Address:</strong> ${data.employee.address}</p>
                    <p><strong>Contact No:</strong> ${data.employee.number}</p>
                `);
                
                // Update items list
                $('#itemsList').html(data.items);
            },
            error: function() {
                $('#itemsList').html('<div class="alert alert-danger">Error loading details</div>');
            }
        });
    });
});
</script>