<?php
require_once 'db.php';

// Session checks
if (!isset($_SESSION['loggedIn']) || !$_SESSION['loggedIn']) {
    echo "<p>Error: Please log in first</p>";
    exit;
}

// Add this search form before the main query
echo <<<HTML
<div class="mb-3">
    <div class="input-group">
        <input type="text" class="form-control" id="managerSearch" placeholder="Search by Employee Name">
        <input type="date" class="form-control" id="dateSearch">
        <button class="btn btn-outline-secondary" type="button" id="clearSearch">Clear</button>
    </div>
</div>
HTML;

$query = "SELECT 
    prf.PRF_ID,
    CONCAT(emp.EMP_FNAME, ' ', emp.EMP_MNAME, ' ', emp.EMP_LNAME) as employee_name,
    dept.DEPT_NAME,
    prf.PRF_DATE,
    prf.PRF_STATUS,
    CONCAT(r.EMP_FNAME, ' ', r.EMP_MNAME, ' ', r.EMP_LNAME) as rejected_by,
    a.ap_desc as rejection_reason,
    a.ap_date as rejection_date
FROM purchase_or_requisition_form prf
JOIN employee emp ON prf.EMP_ID = emp.EMP_ID
JOIN department dept ON emp.DEPT_ID = dept.DEPT_ID
LEFT JOIN approval a ON prf.AP_ID = a.ap_id
LEFT JOIN employee r ON a.EMP_ID = r.EMP_ID
WHERE prf.PRF_STATUS IN ('rejected', 'closed')
AND emp.DEPT_ID = ?
ORDER BY prf.PRF_DATE DESC";

$stmt = $db->prepare($query);
$stmt->bind_param("i", $_SESSION['department_id']);

if (!$stmt->execute()) {
    echo "<p>Error executing query: " . $stmt->error . "</p>";
    exit;
}

$result = $stmt->get_result();



// Only show table if there are results
if ($result->num_rows > 0) {
    echo "<table class='table table-striped'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>PRF ID</th>";
    echo "<th>Employee Name</th>";
    echo "<th>Department</th>";
    echo "<th>Date & Time</th>";
    echo "<th>Status</th>";
    echo "<th>Rejected By</th>";
    echo "<th>Rejection Date</th>";
    echo "<th>Action</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['PRF_ID']) . "</td>";
        echo "<td>" . htmlspecialchars($row['employee_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['DEPT_NAME']) . "</td>";
        echo "<td>" . date('F j, Y h:i A', strtotime($row['PRF_DATE'])) . "</td>";
        echo "<td>";
        switch($row['PRF_STATUS']) {
            case 'approved':
                echo "<span class='badge bg-success'>Approved</span>";
                break;
            case 'rejected':
                echo "<span class='badge bg-danger'>Rejected</span>";
                break;
            case 'pending':
                echo "<span class='badge bg-warning'>Pending</span>";
                break;
        }
        echo "</td>";
        echo "<td>" . ($row['PRF_STATUS'] === 'rejected' ? htmlspecialchars($row['rejected_by']) : '-') . "</td>";
        echo "<td>" . ($row['PRF_STATUS'] === 'rejected' ? date('F j, Y h:i A', strtotime($row['rejection_date'])) : '-') . "</td>";
        echo "<td>";
        echo "<button class='btn btn-sm btn-info view-details' data-bs-toggle='modal' 
              data-bs-target='#itemsModal' 
              data-prf-id='" . htmlspecialchars($row['PRF_ID']) . "'
              data-rejection-reason='" . htmlspecialchars($row['rejection_reason']) . "'>View Details</button>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";
} else {
    echo "<div class='alert alert-info'>No rejected or closed requisitions found.</div>";
}

$stmt->close();

// Modal HTML
echo <<<HTML
<div class="modal fade" id="itemsModal" tabindex="-1" aria-labelledby="itemsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemsModalLabel">Requisition Details</h5>
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
        const prfId = $(this).data('prf-id');
        const status = $(this).closest('tr').find('td:eq(4) .badge').text().toLowerCase();
        
        // Fetch items for this PRF
        $.ajax({
            url: 'get_requisition_items.php',
            type: 'POST',
            data: { 
                prf_id: prfId,
                status: status
            },
            success: function(response) {
                $('#itemsList').html(response);
            },
            error: function() {
                $('#itemsList').html('<div class="alert alert-danger">Error loading items</div>');
            }
        });
    });

    $('#managerSearch').on('input', function() {
        // Remove special characters from input
        $(this).val($(this).val().replace(/[^a-zA-Z\s]/g, ''));
        filterTable();
    });

    $('#dateSearch').on('input', function() {
        filterTable();
    });

    function filterTable() {
        const searchText = $('#managerSearch').val().toLowerCase();
        const searchDate = $('#dateSearch').val();
        
        $('tbody tr').each(function() {
            const employeeName = $(this).find('td:eq(1)').text().toLowerCase();
            const date = $(this).find('td:eq(3)').text().split(' ')[0]; // Get just the date part
            
            const matchesName = employeeName.includes(searchText);
            const matchesDate = !searchDate || date === searchDate;
            
            if (matchesName && matchesDate) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    $('#clearSearch').click(function() {
        $('#managerSearch').val('');
        $('#dateSearch').val('');
        $('tbody tr').show();
    });
});
</script>
HTML;
