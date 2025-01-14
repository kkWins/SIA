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

// Main query for requisitions
$query = "SELECT 
            prf.PRF_ID, 
            prf.PRF_DATE, 
            prf.PRF_STATUS,
            a.ap_desc as rejection_reason
          FROM purchase_or_requisition_form prf
          LEFT JOIN approval a ON prf.AP_ID = a.ap_id
          WHERE prf.EMP_ID = ?
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
    echo "<th>Action</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['PRF_ID']) . "</td>";
        echo "<td>" . htmlspecialchars($row['PRF_DATE']) . "</td>";
        echo "<td>";
        switch(strtolower($row['PRF_STATUS'])) {
            case 'pending':
                echo "<span class='badge bg-warning text-dark'>Pending</span>";
                break;
            case 'approved':
                echo "<span class='badge bg-success'>Approved</span>";
                break;
            case 'rejected':
                echo "<span class='badge bg-danger'>Rejected</span>";
                break;
            default:
                echo "<span class='badge bg-secondary'>" . htmlspecialchars($row['PRF_STATUS']) . "</span>";
        }
        echo "</td>";
        echo "<td>";
        echo "<button class='btn btn-sm btn-info view-details' data-bs-toggle='modal' data-bs-target='#itemsModal' data-prf-id='" . htmlspecialchars($row['PRF_ID']) . "'>View Details</button>";
        echo "</td>";
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";
} else {
    echo "<div class='alert alert-info'>No requisition history found for your account. Submit a new requisition to see it here.</div>";
}

$stmt->close();

// Modal HTML
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
});
</script>
HTML;
