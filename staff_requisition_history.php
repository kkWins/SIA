<?php
require_once 'db.php';

// Session checks
if (!isset($_SESSION['loggedIn']) || !$_SESSION['loggedIn']) {
    echo "<p>Error: Please log in first</p>";
    exit;
}

if (!isset($_SESSION['ID'])) {
    echo "<p>Error: Employee ID not found in session</p>";
    exit;
}

$emp_id = $_SESSION['ID'];

// Add this search form before the main query
echo <<<HTML
<div class="mb-3">
    <div class="input-group">
        <input type="number" class="form-control" id="staffSearch" placeholder="Search by PRF ID" min="1">
        <input type="date" class="form-control" id="dateSearch">
        <button class="btn btn-outline-secondary" type="button" id="clearSearch">Clear</button>
    </div>
</div>
HTML;

// Main query for requisitions
$query = "SELECT 
            prf.PRF_ID, 
            prf.PRF_DATE, 
            prf.PRF_STATUS,
            CONCAT(r.EMP_FNAME, ' ', r.EMP_MNAME, ' ', r.EMP_LNAME) as rejected_by,
            a.ap_desc as rejection_reason,
            a.ap_date as rejection_date
          FROM purchase_or_requisition_form prf
          LEFT JOIN approval a ON prf.AP_ID = a.ap_id
          LEFT JOIN employee r ON a.EMP_ID = r.EMP_ID
          WHERE prf.EMP_ID = ?
          AND prf.PRF_STATUS IN ('rejected', 'closed')
          ORDER BY prf.PRF_DATE DESC";

$stmt = $db->prepare($query);
$stmt->bind_param("i", $emp_id);

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
    echo "<div class='alert alert-info'>No requisition history found for your account. Submit a new requisition to see it here.</div>";
}

$stmt->close();

// Modal HTML - Update to match manager version
echo <<<HTML
<div class="modal fade" id="itemsModal" tabindex="-1" aria-labelledby="itemsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemsModalLabel">Requisition Items</h5>
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
        const status = $(this).closest('tr').find('td:eq(2) .badge').text().toLowerCase();
        const rejectionReason = $(this).data('rejection-reason');
        
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

    // Update search functionality to handle both PRF ID and date
    $('#staffSearch, #dateSearch').on('input', function() {
        const searchText = $('#staffSearch').val().toLowerCase();
        const searchDate = $('#dateSearch').val();
        
        $('tbody tr').each(function() {
            const prfId = $(this).find('td:eq(0)').text().toLowerCase();
            const date = $(this).find('td:eq(1)').text().split(' ')[0]; // Get just the date part
            
            const matchesText = prfId.includes(searchText);
            const matchesDate = !searchDate || date === searchDate;
            
            if (matchesText && matchesDate) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    $('#clearSearch').click(function() {
        $('#staffSearch').val('');
        $('#dateSearch').val('');
        $('tbody tr').show();
    });
});
</script>
HTML;
