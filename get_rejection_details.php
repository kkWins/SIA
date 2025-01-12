<?php
require_once 'db.php';

if (!isset($_GET['prf_id'])) {
    echo "No requisition ID provided";
    exit;
}

$prf_id = $_GET['prf_id'];

$query = "SELECT prf.PRF_ID, 
          CONCAT(emp.EMP_FNAME, ' ', emp.EMP_LNAME) as employee_name,
          inv.INV_MODEL_NAME,
          prf.PRF_QUANTITY,
          prf.PRF_DATE,
          prf.rejection_reason,
          prf.PRF_DESC
          FROM purchase_or_requisition_form prf
          JOIN employee emp ON prf.EMP_ID = emp.EMP_ID
          JOIN inventory inv ON prf.INV_ID = inv.INV_ID
          WHERE prf.PRF_ID = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $prf_id);
$stmt->execute();
$result = $stmt->get_result();
$details = $result->fetch_assoc();

if ($details) {
    echo "<div class='container'>
            <div class='row'>
                <div class='col-md-6'>
                    <p><strong>Employee:</strong> " . htmlspecialchars($details['employee_name']) . "</p>
                    <p><strong>Date Submitted:</strong> " . htmlspecialchars($details['PRF_DATE']) . "</p>
                </div>
                <div class='col-md-6'>
                    <p><strong>Item:</strong> " . htmlspecialchars($details['INV_MODEL_NAME']) . "</p>
                    <p><strong>Quantity:</strong> " . htmlspecialchars($details['PRF_QUANTITY']) . "</p>
                </div>
            </div>
            <div class='row mt-3'>
                <div class='col-12'>
                    <p><strong>Reason for Request:</strong></p>
                    <p>" . htmlspecialchars($details['PRF_DESC']) . "</p>
                </div>
            </div>
            <div class='row mt-3'>
                <div class='col-12'>
                    <p><strong>Rejection Reason:</strong></p>
                    <p class='text-danger'>" . htmlspecialchars($details['rejection_reason']) . "</p>
                </div>
            </div>
          </div>";
} else {
    echo "Requisition details not found";
}

$stmt->close();
