<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['loggedIn']) || !$_SESSION['loggedIn']) {
    echo "<h3>You are not authorized to view this content.</h3>";
    exit;
}

// Get the content from the URL parameter
$content = $_GET['content'] ?? '';
$dept_id = $_SESSION['department_id'];

// Get user role and department from the session
$role = $_SESSION['role'];
$department = $_SESSION['department'];

// Concatenate department and role for authorization
$conca = $department . " " . $role;

// Check the content and show the appropriate page
if ($content === 'purchase_order') {
    if ($conca === 'Inventory Manager') {
        include('inventory/purchase_orders.php');
        echo "<h2>Purchase Order</h2>
        <p>Manage your purchase orders here.</p>";

        if(isset($_GET['po_id'])){
            if($response['po_details']) {
                echo "
                    <div class='card rounded-4 p-4'>
                    <div class='text-start'>
                        <a href='#' class='btn btn-link back-to-list p-0 mb-3' data-content='purchase_order'>
                            <i class='fas fa-arrow-left'></i> Back
                        </a>
                    </div>
                    <div class='row'>
                        <div class='col-md-6'>
                            <h3>MOONLIGHT</h3>
                            <p class='mb-0'>Address: Logarta St 6014 Mandaue City, Philippines</p>
                            <p class='mb-0'>Contact No: 09123456789</p>
                        </div>
                        <div class='col-md-6'>
                            <h3>PURCHASE ORDER</h3>
                            <p class='mb-0'>Date: "; echo date('F j, Y', strtotime($response['po_details']['ap_date'])); echo "</p>
                            <p class='mb-0'>PO-{$response['po_details']['PO_ID']}</p>
                        </div>
                    </div>
                    <div class='row mb-3 mt-3'>
                        <div class='col-md-6'>
                            <h4>Supplier Details</h4>
                            <p class='mb-0'><strong>Supplier Name:</strong> {$response['po_details']['SP_NAME']}</p>
                            <p class='mb-0'><strong>Contact no:</strong> {$response['po_details']['SP_NUMBER']}</p>
                             <p class='mb-0'><strong>Address:</strong> {$response['po_details']['SP_ADDRESS']}</p>
                        </div>
                        <div class='col-md-6'>
                           <h4>SHIP TO</h4>
                           <p class='mb-0'><strong>Name:</strong> {$response['po_details']['deliverTo']}</p>
                           <p class='mb-0'><strong>Company:</strong> Moonlight</p>
                           <p class='mb-0'><strong>Address:</strong> Logarta St 6014 Mandaue City, Philippines</p>
                           <p class='mb-0'><strong>Contact No:</strong> {$response['po_details']['EMP_NUMBER']}</p>
                        </div>
                    </div>";
    
                    if($response['po_details']['PO_STATUS'] === 'rejected') {
                        echo "<div class='alert alert-danger'>
                                <strong>Rejection Reason:</strong> " . htmlspecialchars($response['po_details']['ap_desc']) . "
                              </div>";
                    }
    
                    echo "<table class='table table-striped mt-3'>
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Brand</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total Price</th>
                            </tr>
                        </thead>
                        <tbody>";
                        
                        $grandTotal = 0;
                        foreach ($response['items'] as $item) {
                            $totalPrice = $item['POL_QUANTITY'] * $item['POL_PRICE'];
                            $grandTotal += $totalPrice;
                            echo "<tr>
                                    <td>" . htmlspecialchars($item['INV_MODEL_NAME']) . "</td>
                                    <td>" . htmlspecialchars($item['INV_BRAND']) . "</td>
                                    <td>" . htmlspecialchars($item['POL_QUANTITY']) . "</td>
                                    <td>₱" . number_format($item['POL_PRICE'], 2) . "</td>
                                    <td>₱" . number_format($totalPrice, 2) . "</td>
                                </tr>";
                        }
                        
                        echo "</tbody>
                            <tfoot>
                                <tr>
                                    <td colspan='4' class='text-end'><strong>Grand Total:</strong></td>
                                    <td><strong>₱" . number_format($grandTotal, 2) . "</strong></td>
                                </tr>
                            </tfoot>
                        </table>

                        <!-- Add new date/time inputs with pre-filled values -->
                        <div class='row mb-3 mt-3'>
                            <div class='col-md-6'>
                                <label for='order_datetime' class='form-label'><strong>Order Date & Time:</strong></label>
                                <input type='datetime-local' class='form-control' id='order_datetime' 
                                    value='" . (!empty($response['po_details']['PO_ORDER_DATE']) ? date('Y-m-d\TH:i', strtotime($response['po_details']['PO_ORDER_DATE'])) : '') . "' 
                                    required>
                            </div>
                            <div class='col-md-6'>
                                <label for='arrival_datetime' class='form-label'><strong>Expected Arrival Date & Time:</strong></label>
                                <input type='datetime-local' class='form-control' id='arrival_datetime' 
                                    value='" . (!empty($response['po_details']['PO_ARRIVAL_DATE']) ? date('Y-m-d\TH:i', strtotime($response['po_details']['PO_ARRIVAL_DATE'])) : '') . "' 
                                    required>
                            </div>
                        </div>
    
                        <div class='mt-3'>";
                            if($response['po_details']['PO_STATUS'] === 'completed') {
                                echo "<p class='text-success'>Purchase order is already completed.</p>";
                            } else {
                                // Check if payment details exist
                                $hasPaymentDetails = $response['po_details']['PD_PAYMENT_TYPE'] && 
                                                    $response['po_details']['PD_CHANGE'] && 
                                                    $response['po_details']['PD_AMMOUNT'];
                                
                                if(!$response['po_details']['PO_ORDER_DATE']) {
                                    // If order date is empty, show submit button
                                    echo "<div class='text-end'>
                                            <button class='btn btn-success submit-btn' data-id='" . htmlspecialchars($_GET['po_id']) . "'>Submit</button>
                                        </div>";
                                } else if($response['po_details']['PO_ORDER_DATE'] && !$hasPaymentDetails) {
                                    // If order date exists but no payment details
                                    echo "<p class='text-danger'>Payment details are missing.</p>";
                                } else if($hasPaymentDetails && !$response['po_details']['PO_ARRIVAL_DATE']) {
                                    // If payment details exist but no arrival date
                                    echo "<div class='text-end'>
                                            <button class='btn btn-success submit-btn' data-id='" . htmlspecialchars($_GET['po_id']) . "'>Submit</button>
                                        </div>";
                                } else if($response['po_details']['PO_ARRIVAL_DATE']) {
                                    // If arrival date exists
                                    echo "<div class='text-end'>
                                            <button class='btn btn-success complete-btn' data-id='" . htmlspecialchars($_GET['po_id']) . "'>Complete</button>
                                        </div>";
                                }
                            }
                            echo "
                        </div>
                    </div>
                    <script>
                        $(document).ready(function() {

                            // Add complete button click handler
                            $('.complete-btn').on('click', function() {
                                const poId = $(this).data('id');
                                
                                if (confirm('Are you sure you want to mark this purchase order as completed?')) {
                                    $.ajax({
                                        url: 'inventory/complete_purchase_order.php',
                                        type: 'POST',
                                        data: {
                                            po_id: poId
                                        },
                                        success: function(response) {
                                            try {
                                                if (response.success) {
                                                    alert('Purchase order completed successfully');
                                                    window.location.href = '?content=purchase_order';
                                                } else {
                                                    alert('Error: ' + (response.message || 'Unknown error'));
                                                }
                                            } catch (e) {
                                                console.error('Error parsing response:', e);
                                                alert('Error processing response');
                                            }
                                        },
                                        error: function() {
                                            alert('Error completing purchase order');
                                        }
                                    });
                                }
                            });

                            
                            $('.submit-btn').on('click', function() {
                                const poId = $(this).data('id');
                                const orderDateTime = $('#order_datetime').val();
                                const arrivalDateTime = $('#arrival_datetime').val();
                                console.log(orderDateTime);
                                
                                
                                // Send AJAX request
                                $.ajax({
                                    url: 'inventory/submit_purchase_order.php',
                                    type: 'POST',
                                    data: {
                                        po_id: poId,
                                        order_datetime: orderDateTime,
                                        arrival_datetime: arrivalDateTime
                                    },
                                    success: function(response) {
                                        try {
                                            const result = JSON.parse(response);
                                            if (result.success) {
                                                alert('Purchase order submitted successfully');
                                                window.location.href = '?content=purchase_order';
                                            } else {
                                                alert('Error: ' + (result.message || 'Unknown error'));
                                            }
                                        } catch (e) {
                                            console.error('Error parsing response:', e);
                                            alert('Error processing response');
                                        }
                                    },
                                    error: function() {
                                        alert('Error submitting purchase order');
                                    }
                                });
                            });
                        });
                        </script>";
    
            } else {
                echo 'Purchase Order not found';
            }
        }else{
            if(!empty($pos)){
                echo "
                <div class='card rounded-4 p-4'>
                    <table class='table table-striped' id='requisitions-table'>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Supplier</th>
                                <th>Date of Issue</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>";
                        
                        foreach ($pos as $po) {
                            echo "<tr>
                                    <td>" . "PO-".htmlspecialchars($po['PO_ID']) . "</td>
                                    <td>" . htmlspecialchars($po['SP_NAME']) . "</td>
                                    <td>" . date('F d, Y', strtotime($po['ap_date'])) . "</td>
                                    <td>" . htmlspecialchars($po['PO_STATUS']) . "</td>
                                    <td>
                                        <a href='#' class='btn btn-sm btn-primary view-requisition' 
                                            data-content='purchase_order' 
                                            data-id='" . $po['PO_ID'] . "'>
                                            <i class='fas fa-eye'></i>
                                        </a>
                                    </td>
                                </tr>";
                        }
                
                echo "</tbody>
                    </table>
                </div>";
            }else{
                echo "
                <div>
                    <p>No Pending Purchase Order Found!</p>
                </div>
                ";
            }
        }

    }elseif($conca === 'Finance Manager'){
        include('inventory/purchase_orders.php');
        echo "<h2>Finance Manager - Purchase Orders</h2>
        <p>Manage purchase order payments here.</p>";

        if(isset($_GET['po_id'])){
            if($response['po_details']) {
                echo "
                    <div class='card rounded-4 p-4'>
                    <div class='text-start'>
                        <a href='#' class='btn btn-link back-to-list p-0 mb-3' data-content='purchase_order'>
                            <i class='fas fa-arrow-left'></i> Back
                        </a>
                    </div>
                    <div class='row'>
                        <div class='col-md-6'>
                            <h3>MOONLIGHT</h3>
                            <p class='mb-0'>Address: Logarta St 6014 Mandaue City, Philippines</p>
                            <p class='mb-0'>Contact No: 09123456789</p>
                        </div>
                        <div class='col-md-6'>
                            <h3>PURCHASE ORDER</h3>
                            <p class='mb-0'>Date: "; echo date('F j, Y', strtotime($response['po_details']['ap_date'])); echo "</p>
                            <p class='mb-0'>PO-{$response['po_details']['PO_ID']}</p>
                        </div>
                    </div>
                    <div class='row mb-3 mt-3'>
                        <div class='col-md-6'>
                            <h4>Supplier Details</h4>
                            <p class='mb-0'><strong>Supplier Name:</strong> {$response['po_details']['SP_NAME']}</p>
                            <p class='mb-0'><strong>Contact no:</strong> {$response['po_details']['SP_NUMBER']}</p>
                             <p class='mb-0'><strong>Address:</strong> {$response['po_details']['SP_ADDRESS']}</p>
                        </div>
                        <div class='col-md-6'>
                           <h4>SHIP TO</h4>
                           <p class='mb-0'><strong>Name:</strong> {$response['po_details']['deliverTo']}</p>
                           <p class='mb-0'><strong>Company:</strong> Moonlight</p>
                           <p class='mb-0'><strong>Address:</strong> Logarta St 6014 Mandaue City, Philippines</p>
                           <p class='mb-0'><strong>Contact No:</strong> {$response['po_details']['EMP_NUMBER']}</p>
                        </div>
                    </div>";

                    if($response['po_details']['PO_STATUS'] === 'rejected') {
                        echo "<div class='alert alert-danger'>
                                <strong>Rejection Reason:</strong> " . htmlspecialchars($response['po_details']['ap_desc']) . "
                            </div>";
                    }

                echo "<table class='table table-striped mt-3'>
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Brand</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total Price</th>
                        </tr>
                    </thead>
                    <tbody>";
                    
                    $grandTotal = 0;
                    foreach ($response['items'] as $item) {
                        $totalPrice = $item['POL_QUANTITY'] * $item['POL_PRICE'];
                        $grandTotal += $totalPrice;
                        echo "<tr>
                                <td>" . htmlspecialchars($item['INV_MODEL_NAME']) . "</td>
                                <td>" . htmlspecialchars($item['INV_BRAND']) . "</td>
                                <td>" . htmlspecialchars($item['POL_QUANTITY']) . "</td>
                                <td>₱" . number_format($item['POL_PRICE'], 2) . "</td>
                                <td>₱" . number_format($totalPrice, 2) . "</td>
                            </tr>";
                    }
                    
                    echo "</tbody>
                        <tfoot>
                            <tr>
                                <td colspan='4' class='text-end'><strong>Grand Total:</strong></td>
                                <td><strong>₱" . number_format($grandTotal, 2) . "</strong></td>
                            </tr>
                        </tfoot>
                    </table>

                    <!-- Replace datetime inputs with payment details -->
                    <div class='row mb-3 mt-3'>
                        <div class='col-md-4'>
                            <label for='payment_type' class='form-label'><strong>Payment Type:</strong></label>
                            <select class='form-select' id='payment_type' required>
                                <option value=''>Select payment type</option>
                                <option value='cash' " . ($response['payment_details']['PD_PAYMENT_TYPE'] === 'cash' ? 'selected' : '') . ">Cash</option>
                                <option value='check' " . ($response['payment_details']['PD_PAYMENT_TYPE'] === 'check' ? 'selected' : '') . ">Check</option>
                            </select>
                        </div>
                        <div class='col-md-4'>
                            <label for='payment_amount' class='form-label'><strong>Payment Amount:</strong></label>
                            <input type='number' class='form-control' id='payment_amount' 
                                value='" . (!empty($response['payment_details']['PD_AMMOUNT']) ? $response['payment_details']['PD_AMMOUNT'] : '') . "'
                                min='0' step='0.01' required>
                        </div>
                        <div class='col-md-4'>
                            <label for='payment_change' class='form-label'><strong>Change:</strong></label>
                            <input type='number' class='form-control' id='payment_change' 
                                value='" . (!empty($response['payment_details']['PD_CHANGE']) ? $response['payment_details']['PD_CHANGE'] : '') . "'
                                min='0' step='0.01' required>
                        </div>
                    </div>
    
                    <div class='mt-3'>";
                        
                        if($response['po_details']['PO_STATUS'] === 'completed'){
                            echo "
                            <p class='text-success'>Purchase order is already completed.</p>
                            ";
                        }else{
                            echo "
                            <div class='text-end'>
                                <button class='btn btn-success submit1-btn' data-id='" . htmlspecialchars($_GET['po_id']) . "'>Submit</button>
                            </div>
                            ";
                        }
                    echo "</div>
                </div>

                <script>
                    $(document).ready(function() {
                        $('.submit1-btn').on('click', function() {
                            const poId = $(this).data('id');
                            const paymentType = $('#payment_type').val();
                            const paymentAmount = $('#payment_amount').val();
                            const paymentChange = $('#payment_change').val();
                            
                            // Validate inputs
                            if (!paymentType || !paymentAmount || !paymentChange) {
                                alert('Please fill in all payment details');
                                return;
                            }
                            
                            // Send AJAX request
                            $.ajax({
                                url: 'finance/submit_payment.php',
                                type: 'POST',
                                data: {
                                    po_id: poId,
                                    payment_type: paymentType,
                                    payment_amount: paymentAmount,
                                    payment_change: paymentChange
                                },
                                success: function(response) {
                                    try {
                                        // Check if response is already an object
                                        const result = typeof response === 'object' ? response : JSON.parse(response);
                                        if (result.success) {
                                            alert('Payment submitted successfully');
                                            window.location.href = '?content=purchase_order';
                                        } else {
                                            alert('Error: ' + (result.message || 'Unknown error'));
                                        }
                                    } catch (e) {
                                        alert('Error processing response');
                                    }
                                },
                                error: function(xhr, status, error) {
                                    alert('Error submitting payment. Check console for details.');
                                }
                            });
                        });
                    });
                    </script>";
    
            } else {
                echo 'Purchase Order not found';
            }
        }else{
            if(!empty($pos)){
                echo "
                <div class='card rounded-4 p-4'>
                    <table class='table table-striped' id='requisitions-table'>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Supplier</th>
                                <th>Date of Issue</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>";
                        
                        foreach ($pos as $po) {
                            echo "<tr>
                                    <td>" . "PO-".htmlspecialchars($po['PO_ID']) . "</td>
                                    <td>" . htmlspecialchars($po['SP_NAME']) . "</td>
                                    <td>" . date('F d, Y', strtotime($po['ap_date'])) . "</td>
                                    <td>" . htmlspecialchars($po['PO_STATUS']) . "</td>
                                    <td>
                                        <a href='#' class='btn btn-sm btn-primary view-requisition' 
                                            data-content='purchase_order' 
                                            data-id='" . $po['PO_ID'] . "'>
                                            <i class='fas fa-eye'></i>
                                        </a>
                                    </td>
                                </tr>";
                        }
                
                echo "</tbody>
                    </table>
                </div>";
            }else{
                echo "
                <div>
                    <p>No Pending Purchase Order Found!</p>
                </div>
                ";
            }
        }
    }else {
        echo "<h3>You do not have access to this content.</h3>";
    }
}elseif($content === 'pending_pr'){
    echo "<h2>Pending Purchase Request</h2>";
    include('finance/finance_pending_pr.php');
    
    if(isset($_GET['pending_pr'])){
        if($response['po_details']) {
            echo "
                <div class='card rounded-4 p-4'>
                <div class='text-start'>
                    <a href='#' class='btn btn-link back-to-list p-0 mb-3' data-content='pending_pr'>
                        <i class='fas fa-arrow-left'></i> Back
                    </a>
                </div>
                <h3>Purchase Request # {$_GET['pending_pr']}</h3>
                <div class='row mb-3'>
                    <div class='col-md-6'>
                        <p><strong>Requester:</strong> {$response['po_details']['fullname']}</p>
                        <p><strong>Date of Request:</strong> {$response['po_details']['PO_PR_DATE_CREATED']}</p>
                        
                    </div>
                    <div class='col-md-6'>
                        <p><strong>Supplier Name:</strong> {$response['po_details']['SP_NAME']}</p>
                        <p><strong>Contact no:</strong> {$response['po_details']['SP_NUMBER']}</p>
                        <p><strong>Address:</strong> {$response['po_details']['SP_ADDRESS']}</p>
                    </div>
                </div>";

                if($response['po_details']['PO_STATUS'] === 'rejected') {
                    echo "<div class='alert alert-danger'>
                            <strong>Rejection Reason:</strong> " . htmlspecialchars($response['po_details']['ap_desc']) . "
                          </div>";
                }

                echo "<table class='table'>
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Brand</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total Price</th>
                        </tr>
                    </thead>
                    <tbody>";
                    
                    $grandTotal = 0;
                    foreach ($response['items'] as $item) {
                        $totalPrice = $item['POL_QUANTITY'] * $item['POL_PRICE'];
                        $grandTotal += $totalPrice;
                        echo "<tr>
                                <td>" . htmlspecialchars($item['INV_MODEL_NAME']) . "</td>
                                <td>" . htmlspecialchars($item['INV_BRAND']) . "</td>
                                <td>" . htmlspecialchars($item['POL_QUANTITY']) . "</td>
                                <td>₱" . number_format($item['POL_PRICE'], 2) . "</td>
                                <td>₱" . number_format($totalPrice, 2) . "</td>
                            </tr>";
                    }
                    
                    echo "</tbody>
                        <tfoot>
                            <tr>
                                <td colspan='4' class='text-end'><strong>Grand Total:</strong></td>
                                <td><strong>₱" . number_format($grandTotal, 2) . "</strong></td>
                            </tr>
                        </tfoot>
                    </table>";
                    if($response['po_details']['PO_STATUS'] === 'pending'){
                        echo "<div class='mt-3'>
                                <button class='btn btn-danger reject-btn' data-id='" . htmlspecialchars($_GET['pending_pr']) . "'>Reject</button>
                                <button class='btn btn-success approve-btn' data-id='" . htmlspecialchars($_GET['pending_pr']) . "'>Approve</button>
                            </div>";
                    }
                    
                echo "</div>";

                // Rejection Modal
                echo "
                <div class='modal fade' id='rejectModal' tabindex='-1' aria-labelledby='rejectModalLabel' aria-hidden='true'>
                    <div class='modal-dialog'>
                        <div class='modal-content'>
                            <div class='modal-header'>
                                <h5 class='modal-title' id='rejectModalLabel'>Reject Purchase Request</h5>
                                <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                            </div>
                            <div class='modal-body'>
                                <form id='rejectForm'>
                                    <input type='hidden' id='po_id' name='po_id'>
                                    <div class='mb-3'>
                                        <label for='reject_reason' class='form-label'>Reason for Rejection</label>
                                        <textarea class='form-control' id='reject_reason' name='reject_reason' rows='3' required></textarea>
                                    </div>
                                </form>
                            </div>
                            <div class='modal-footer'>
                                <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                                <button type='button' class='btn btn-danger' id='confirmReject'>Confirm Rejection</button>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Approval Modal -->
                <div class='modal fade' id='approveModal' tabindex='-1' aria-labelledby='approveModalLabel' aria-hidden='true'>
                    <div class='modal-dialog'>
                        <div class='modal-content'>
                            <div class='modal-header'>
                                <h5 class='modal-title' id='approveModalLabel'>Approve Purchase Request</h5>
                                <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                            </div>
                            <div class='modal-body'>
                                <p>Are you sure you want to approve this purchase request?</p>
                                <input type='hidden' id='approve_po_id' name='po_id'>
                            </div>
                            <div class='modal-footer'>
                                <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                                <button type='button' class='btn btn-success' id='confirmApprove'>Confirm Approval</button>
                            </div>
                            
                        </div>
                    </div>
                </div>

                <script>
                    $(document).ready(function() {
                        // Show rejection modal when reject button is clicked
                        $('.reject-btn').on('click', function() {
                            const poId = $(this).data('id');
                            $('#po_id').val(poId);
                            $('#rejectModal').modal('show');
                        });


                        // Show approval modal when approve button is clicked
                        $('.approve-btn').on('click', function() {
                            const poId = $(this).data('id');
                            $('#approve_po_id').val(poId);
                            $('#approveModal').modal('show');
                        });

                        //Handle sa approve sa PR brad
                        $('#confirmApprove').on('click', function(){
                            const poId = $('#approve_po_id').val();

                            $.ajax({
                                url: 'finance/approve_purchase_request.php',
                                type: 'POST',
                                data: {
                                    po_id: poId
                                },
                                success: function(response) {
                                    try {
                                        const result = JSON.parse(response);
                                        if (result.success) {
                                            alert('Purchase order approved successfully');
                                            $('#approveModal').modal('hide');
                                            // Redirect back to the list view
                                            window.location.href = '?content=pending_pr';
                                        } else {
                                            alert('Error: ' + result.message);
                                        }
                                    } catch (e) {
                                        alert('Error processing response');
                                    }
                                },
                                error: function() {
                                    alert('Error processing request');
                                }
                            });
                        
                        
                        });



                        // Handle rejection confirmation
                        $('#confirmReject').on('click', function() {
                            const poId = $('#po_id').val();
                            const rejectReason = $('#reject_reason').val();

                            if (!rejectReason) {
                                alert('Please provide a reason for rejection');
                                return;
                            }

                            $.ajax({
                                url: 'finance/reject_purchase_request.php',
                                type: 'POST',
                                data: {
                                    po_id: poId,
                                    reject_reason: rejectReason
                                },
                                success: function(response) {
                                    try {
                                        const result = JSON.parse(response);
                                        if (result.success) {
                                            alert('Purchase order rejected successfully');
                                            $('#rejectModal').modal('hide');
                                            // Redirect back to the list view
                                            window.location.href = '?content=pending_pr';
                                        } else {
                                            alert('Error: ' + result.message);
                                        }
                                    } catch (e) {
                                        alert('Error processing response');
                                    }
                                },
                                error: function() {
                                    alert('Error processing request');
                                }
                            });
                        });
                    });
                </script>";
        } else {
            echo 'Purchase Order not found';
        }
    }else{
        if(!empty($pos)){
            echo "
            <div class='card rounded-4 p-4'>
                <table class='table' id='requisitions-table'>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Requester</th>
                            <th>Supplier</th>
                            <th>Date of Request</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>";
                    
                    foreach ($pos as $po) {
                        echo "<tr>
                                <td>" . htmlspecialchars($po['PO_ID']) . "</td>
                                <td>" . htmlspecialchars($po['fullname']) . "</td>
                                <td>" . htmlspecialchars($po['SP_NAME']) . "</td>
                                <td>" . date('F d, Y', strtotime($po['PO_PR_DATE_CREATED'])) . "</td>
                                <td>" . htmlspecialchars($po['PO_STATUS']) . "</td>
                                <td>
                                    <a href='#' class='btn btn-sm btn-primary view-requisition' 
                                        data-content='pending_pr' 
                                        data-id='" . $po['PO_ID'] . "'>
                                        <i class='fas fa-eye'></i>
                                    </a>
                                </td>
                            </tr>";
                    }
            
            echo "</tbody>
                </table>
            </div>";
        }else{
            echo "
            <div>
                <p>No Pending Purchase Request Found!</p>
            </div>
            ";
        }
    }
}elseif ($content === 'requisition_approval') {
    echo "<h2>Requisition Approval</h2>";

    // Check if viewing details of a specific requisition
    if (isset($_GET['req_id'])) {
        include('staff_manager_approval.php');

        // Display detailed view
        if (isset($response['items'])) {
            echo "
            <div class='card rounded-4 p-4'>
                <div class='text-start'>
                    <a href='#' class='btn btn-link back-to-list p-0 mb-3' data-content='requisition_approval'>
                        <i class='fas fa-arrow-left'></i> Back
                    </a>
                </div>
                <h3>Requisition ID: " . htmlspecialchars($_GET['req_id']) . "</h3>
                <div class='row mb-3'>
                    <div class='col-md-6'>
                        <p><strong>Name:</strong> " . htmlspecialchars($response['employee_name']) . "</p>
                        <p><strong>Contact no:</strong> " . htmlspecialchars($response['contact_no']) . "</p>
                    </div>
                    <div class='col-md-6'>
                        <p><strong>Date of Request:</strong> " . date('F j, Y h:i A', strtotime($response['date'])) . "</p>
                        <p><strong>Department:</strong> " . htmlspecialchars($response['department']) . "</p>
                    </div>
                </div>
                
                <table class='table'>
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Description</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>";
                    
                        foreach ($response['items'] as $item) {
                            echo "<tr>
                                    <td>" . htmlspecialchars($item['item_name']) . "</td>
                                    <td>" . htmlspecialchars($item['description']) . "</td>
                                    <td>" . htmlspecialchars($item['quantity']) . "</td>
                                </tr>";
                        }
                    
                        echo "
                    </tbody>
                </table>
                
                <div class='mt-3'>
                    <button class='btn btn-danger reject-btn' data-id='" . htmlspecialchars($_GET['req_id']) . "'>Reject</button>
                    <button class='btn btn-success approve-btn' data-id='" . htmlspecialchars($_GET['req_id']) . "'>Approve</button>
                </div>
            </div>";

            echo "
            <script>
            $(document).ready(function() {
                // Approve button click event
                $('.approve-btn').on('click', function() {
                    const reqId = $(this).data('id');
                    
                    // Confirm before approving
                    if (confirm('Are you sure you want to approve this requisition?')) {
                        $.ajax({
                            url: 'all/approve_requisition.php',
                            type: 'POST',
                            data: { req_id: reqId },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    alert('Requisition approved successfully!');
                                    window.location.href = '?content=requisition_approval';
                                } else {
                                    alert('Error: ' + (response.message || 'Failed to approve requisition'));
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error(xhr, status, error);
                                alert('Error processing request');
                            }
                        });
                    }
                });

                // Reject button click event
                $('.reject-btn').on('click', function() {
                    const reqId = $(this).data('id');
                    
                    // Show rejection reason modal
                    const reason = prompt('Please enter rejection reason:');
                    if (reason !== null) {  // Only proceed if user didn't cancel
                        $.ajax({
                            url: 'all/reject_requisition.php',
                            type: 'POST',
                            data: { 
                                req_id: reqId,
                                reason: reason
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    alert('Requisition rejected successfully!');
                                    window.location.href = '?content=requisition_approval';
                                } else {
                                    alert('Error: ' + (response.message || 'Failed to reject requisition'));
                                }
                            },
                            error: function() {
                                alert('Error processing request');
                            }
                        });
                    }
                });
            });
            </script>";
        } else {
            echo "<h3>Requisition not found.</h3>";
        }
    } else {
        // Show list view
        include('staff_manager_approval.php');

        if (!isset($response['status'])) {
            echo "
            <div class='card rounded-4 p-4'>
                <table class='table' id='requisitions-table'>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Requester Name</th>
                            <th>Submitted Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>";
            
                    foreach ($response as $req) {
                        echo "<tr>
                                <td>" . htmlspecialchars($req['requisition_id']) . "</td>
                                <td>" . htmlspecialchars($req['employee_name']) . "</td>
                                <td>" . date('F j, Y h:i A', strtotime($req['submitted_date'])) . "</td>
                                <td>" . htmlspecialchars($req['requisition_status']) . "</td>
                                <td>
                                    <a href='#' class='btn btn-sm btn-primary view-requisition' 
                                        data-content='requisition_approval' 
                                        data-id='" . $req['requisition_id'] . "'>
                                        <i class='fas fa-eye'></i>
                                    </a>
                                </td>
                            </tr>";
                        }
            
                    echo "</tbody>
                </table>
            </div>";
        } else {
            echo "<p>No pending requisitions found.</p>";
        }
    }

} elseif ($content === 'withdrawal_deposit') {
    if ($conca === 'Inventory Manager') {
        echo "<h2>Withdrawal & Deposit</h2>
              <p>Manage withdrawals and deposits here.</p>";
    } else {
        echo "<h3>You do not have access to this content.</h3>";
    }
} elseif ($content === 'manage_suppliers') {
    if ($conca === 'Inventory Manager') {
        echo "<h2>Manage Suppliers</h2>";
        include('inventory/manage_suppliers.php');
    } else {
        echo "<h3>You do not have access to this content.</h3>";
    }

} elseif ($content === 'purchase_request') {
    include('manager_inv_pr.php');
    
    if ($conca === 'Inventory Manager') {
        echo "<h2>Purchase Request</h2>";


                if(isset($_GET['pr_id'])) {
                  if($response['po_details']) {
                      echo "
                      <div class='card rounded-4 p-4'>
                          <div class='text-start'>
                              <a href='#' class='btn btn-link back-to-list p-0 mb-3' data-content='purchase_request'>
                                  <i class='fas fa-arrow-left'></i> Back
                              </a>
                          </div>
                          <h3> PR ID #{$_GET['pr_id']}</h3>
                          <div class='row mb-3'>
                              <div class='col-md-6'>
                                  <p><strong>Requester:</strong> {$response['po_details']['fullname']}</p>
                                  <p><strong>Date of Request:</strong> {$response['po_details']['PO_PR_DATE_CREATED']}</p>
                                  
                                  
                              </div>
                              <div class='col-md-6'>
                                  <p><strong>Supplier Name:</strong> {$response['po_details']['SP_NAME']}</p>
                                  <p><strong>Contact no:</strong> {$response['po_details']['SP_NUMBER']}</p>
                                  <p><strong>Address:</strong> {$response['po_details']['SP_ADDRESS']}</p>
                              </div>
                          </div>";

                          if($response['po_details']['PO_STATUS'] === 'rejected') {
                              echo "<div class='alert alert-danger'>
                                      <strong>Rejection Reason:</strong> " . htmlspecialchars($response['po_details']['ap_desc']) . "
                                    </div>";
                          }

                          echo "<table class='table'>
                              <thead>
                                  <tr>
                                      <th>Item Name</th>
                                      <th>Brand</th>
                                      <th>Quantity</th>
                                      <th>Unit Price</th>
                                      <th>Total Price</th>
                                  </tr>
                              </thead>
                              <tbody>";
                              
                              $grandTotal = 0;
                            foreach ($response['items'] as $item) {
                                  $totalPrice = $item['POL_QUANTITY'] * $item['POL_PRICE'];
                                  $grandTotal += $totalPrice;
                                  echo "<tr>
                                          <td>" . htmlspecialchars($item['INV_MODEL_NAME']) . "</td>
                                          <td>" . htmlspecialchars($item['INV_BRAND']) . "</td>
                                          <td>" . htmlspecialchars($item['POL_QUANTITY']) . "</td>
                                          <td>₱" . number_format($item['POL_PRICE'], 2) . "</td>
                                          <td>₱" . number_format($totalPrice, 2) . "</td>
                                      </tr>";
                            }
                              
                              echo "</tbody>
                                  <tfoot>
                                      <tr>
                                          <td colspan='4' class='text-end'><strong>Grand Total:</strong></td>
                                          <td><strong>₱" . number_format($grandTotal, 2) . "</strong></td>
                                      </tr>
                                  </tfoot>
                                  </table>";
                          } else {
                              echo 'Purchase Order not found';
                    }
                }else{
                    // Always show the Create Purchase Request button
                    echo "
                        <button type='button' class='btn btn-primary mb-3' data-bs-toggle='modal' data-bs-target='#purchaseRequestModal'>
                            Create Purchase Request
                        </button>
                        
                        <!-- Purchase Request Modal -->
                            <div class='modal fade' id='purchaseRequestModal' tabindex='-1' aria-labelledby='purchaseRequestModalLabel' aria-hidden='true'>
                                <div class='modal-dialog modal-lg'>
                                    <div class='modal-content'>
                                            <div class='modal-header'>
                                                <h5 class='modal-title' id='purchaseRequestModalLabel'>Create Purchase Request</h5>
                                                <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                            </div>
                                            <div class='modal-body'>
                                                <form id='purchaseRequestForm'>
                                                    <div class='mb-3'>
                                                        <label for='vendor' class='form-label'>Select Vendor</label>
                                                        <select class='form-select vendor-select' id='vendor' required>
                                                            <option value=''>Select Vendor</option>
                                                        </select>
                                                    </div>
                                                    
                                                    <table class='table' id='prItems'>
                                                        <thead>
                                                            <tr>
                                                                <th>Item</th>
                                                                <th>Quantity</th>
                                                                <th>Unit Price</th>
                                                                <th>Total Price</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>
                                                                    <select class='form-select item-select'>
                                                                        <option value=''>Select Item</option>
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <input type='number' class='form-control quantity' min='1'>
                                                                </td>
                                                                <td>
                                                                    <input type='number' class='form-control price' min='0' step='0.01'>
                                                                </td>
                                                                <td class='total'>0.00</td>
                                                                <td>
                                                                    <button type='button' class='btn btn-danger btn-sm delete-row'>Delete</button>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                        <tfoot>
                                                            <tr>
                                                                <td colspan='3' class='text-end'><strong>Grand Total:</strong></td>
                                                                <td id='grandTotal'>0.00</td>
                                                                <td></td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan='4'>
                                                                    <button type='button' class='btn btn-success btn-sm' id='addNewRow'>
                                                                        <i class='fas fa-plus'></i> Add Another Item
                                                                    </button>
                                                                </td>
                                                                <td></td>
                                                            </tr>
                                                            
                                                        </tfoot>
                                                    </table>
                                                </form>
                                            </div>
                                            <div class='modal-footer'>
                                                <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                                                <button type='button' class='btn btn-primary' id='submitPR'>Submit Purchase Request</button>
                                            </div>
                                    </div>
                                </div>
                            </div>
                          
                        <script>
                            $(document).ready(function() {
                                // Load vendors
                                $.ajax({
                                    url: 'get_vendors.php',
                                    type: 'GET',
                                    success: function(response) {
                                        try {
                                            const data = JSON.parse(response);
                                            data.forEach(function(vendor) {
                                                $('#vendor').append(\"<option value='\" + vendor.id + \"'>\" + vendor.name + \"</option>\");
                                            });
                                        } catch(e) {
                                            console.error('Error parsing JSON:', e);
                                        }
                                    },
                                    error: function(xhr, status, error) {
                                        console.error('Ajax Error:', error);
                                    }
                                });

                                // Load inventory items
                                $.ajax({
                                    url: 'get_inventory.php',
                                    type: 'GET',
                                    success: function(data) {
                                        const items = JSON.parse(data);
                                        const options = items.map(function(item) {
                                            return '<option value=\"' + item.id + '\">' + item.name + '</option>';
                                        }).join('');
                                        $('.item-select').append(options);
                                    }
                                });

                                // Calculate total price
                                function calculateTotal(row) {
                                    const quantity = parseFloat(row.find('.quantity').val()) || 0;
                                    const price = parseFloat(row.find('.price').val()) || 0;
                                    const total = quantity * price;
                                    row.find('.total').text(total.toFixed(2));
                                    calculateGrandTotal();
                                }

                                // Calculate grand total
                                function calculateGrandTotal() {
                                    let grandTotal = 0;
                                    $('#prItems tbody tr').each(function() {
                                        grandTotal += parseFloat($(this).find('.total').text()) || 0;
                                    });
                                    $('#grandTotal').text(grandTotal.toFixed(2));
                                }

                                // Delete row from form (not delete PO)
                                $(document).on('click', '.delete-row', function() {
                                    if ($('#prItems tbody tr').length > 1) {
                                        $(this).closest('tr').remove();
                                        calculateGrandTotal();
                                    } else {
                                        alert('Cannot delete the last row.');
                                    }
                                });

                                $(document).on('click', '.delete-po', function(e) {
                                    e.preventDefault();
                                    e.stopImmediatePropagation();
                                    const poId = $(this).data('id');
                                    
                                    if (confirm('Are you sure you want to delete this purchase order?')) {
                                        $.ajax({
                                            url: 'inventory/delete_po.php',
                                            type: 'POST',
                                            data: { po_id: poId },
                                            dataType: 'json',
                                            success: function(response) {
                                                if (response.success) {
                                                    alert('Purchase order deleted successfully!');
                                                    // Get current URL parameters
                                                    const urlParams = new URLSearchParams(window.location.search);
                                                    // Reload the current page with the same parameters
                                                    window.location.href = window.location.pathname + '?' + urlParams.toString();
                                                } else {
                                                    alert('Error deleting purchase order: ' + (response.message || 'Unknown error'));
                                                }
                                            },
                                            error: function(xhr, status, error) {
                                                console.error('Ajax Error:', error);
                                                alert('Error processing request. Please try again.');
                                            }
                                        });
                                    }
                                });

                                // Calculate totals on input change
                                $(document).on('input', '.quantity, .price', function() {
                                    calculateTotal($(this).closest('tr'));
                                });

                                // Add new row
                                $('#addNewRow').click(function() {
                                    var existingOptions = $('.item-select').first().html();
                                    const newRow = 
                                        '<tr>' +
                                            '<td>' +
                                                '<select class=\"form-select item-select\">' +
                                                existingOptions +
                                                '</select>' +
                                            '</td>' +
                                            '<td>' +
                                                '<input type=\"number\" class=\"form-control quantity\" min=\"1\">' +
                                            '</td>' +
                                            '<td>' +
                                                '<input type=\"number\" class=\"form-control price\" min=\"0\" step=\"0.01\">' +
                                            '</td>' +
                                            '<td class=\"total\">0.00</td>' +
                                            '<td>' +
                                                '<button type=\"button\" class=\"btn btn-danger btn-sm delete-row\">Delete</button>' +
                                            '</td>' +
                                        '</tr>';
                                    $('#prItems tbody').append(newRow);
                                });

                                // Submit new purchase request
                                $('#submitPR').click(function() {
                                    const vendor = $('#vendor').val();
                                    const items = [];
                                    let hasInvalidInput = false;
                                    
                                    $('#prItems tbody tr').each(function() {
                                        const item = $(this).find('.item-select').val();
                                        const quantity = $(this).find('.quantity').val();
                                        const price = $(this).find('.price').val();
                                        
                                        // Check if this row has an item selected
                                        if (item) {
                                            // Validate quantity
                                            if (!quantity || quantity <= 0 || !Number.isInteger(Number(quantity))) {
                                                alert('Please enter a valid whole number for quantity');
                                                hasInvalidInput = true;
                                                return false; // Break the loop
                                            }
                                            
                                            // Validate price
                                            if (!price || price <= 0 || isNaN(Number(price))) {
                                                alert('Please enter a valid number for unit price');
                                                hasInvalidInput = true;
                                                return false; // Break the loop
                                            }
                                            
                                            items.push({
                                                item_id: item,
                                                quantity: quantity,
                                                price: price,
                                                total: $(this).find('.total').text()
                                            });
                                        }
                                    });

                                    // Only proceed if all inputs are valid and we have items
                                    if (!hasInvalidInput && vendor && items.length > 0) {
                                        $.ajax({
                                            url: 'submit_purchase_request.php',
                                            type: 'POST',
                                            contentType: 'application/json',
                                            data: JSON.stringify({
                                                vendor_id: vendor,
                                                items: items
                                            }),
                                            success: function(response) {
                                                try {
                                                    const jsonResponse = JSON.parse(response);
                                                    if (jsonResponse.success) {
                                                        alert('Purchase request submitted successfully!');
                                                        $('#purchaseRequestModal').modal('hide');
                                                        location.reload();
                                                    } else {
                                                        alert('Error submitting purchase request.');
                                                    }
                                                } catch (e) {
                                                    console.error('Error parsing response:', response);
                                                    alert('Error processing server response');
                                                }
                                            },
                                            error: function(xhr, status, error) {
                                                console.error('Ajax Error:', error);
                                                console.log('Response:', xhr.responseText);
                                                alert('Error processing request. Please try again.');
                                            }
                                        });
                                    } else if (!hasInvalidInput) {
                                        alert('Please fill in all required fields.');
                                    }
                                });
                            });
                            </script> 
                            

                          ";

                    if(!empty($pos)){
                        echo "
                            <div class='card rounded-4 p-4'>
                                <table class='table' id='requisitions-table'>
                                    <thead>
                                        <tr>
                                            <th>PR ID</th>
                                            <th>Requester</th>
                                            <th>Supplier</th>
                                            <th>Date of Request</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>";

                                    $items_per_page = 10;
                                    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                    $total_items = count($pos);
                                    $total_pages = ceil($total_items / $items_per_page);
                                    $offset = ($current_page - 1) * $items_per_page;

                                    // Slice the array to get only the items for the current page
                                    $current_items = array_slice($pos, $offset, $items_per_page);

                                    foreach ($current_items as $po) {
                                        echo "<tr>
                                                <td>" . htmlspecialchars('#' . $po['PO_ID']) . "</td>
                                                <td>" . htmlspecialchars($po['fullname']) . "</td>
                                                <td>" . htmlspecialchars($po['SP_NAME']) . "</td>
                                                <td>" . date('F d, Y', strtotime($po['PO_PR_DATE_CREATED'])) . "</td>
                                                <td>" . htmlspecialchars($po['PO_STATUS']) . "</td>
                                                <td>
                                                    <a href='#' class='btn btn-sm btn-primary view-requisition' 
                                                        data-content='purchase_request' 
                                                        data-id='" . $po['PO_ID'] . "'>
                                                        <i class='fas fa-eye'></i>
                                                    </a>
                                                    <a href='#' class='btn btn-sm btn-danger delete-po' 
                                                        data-content='purchase_request' 
                                                        data-id='" . $po['PO_ID'] . "'>
                                                        <i class='fas fa-trash'></i>
                                                    </a>
                                                </td>
                                            </tr>";
                                    }

                                    echo "</tbody>
                                </table>

                                <!-- Pagination -->
                                <nav aria-label=\"Page navigation\" class=\"mt-4\">
                                    <ul class=\"pagination justify-content-center\">
                                        <!-- Previous button -->
                                        <li class=\"page-item " . ($current_page <= 1 ? 'disabled' : '') . "\">
                                            <a class=\"page-link\" href=\"?content=purchase_request&page=" . ($current_page - 1) . "\"" . 
                                            ($current_page <= 1 ? ' tabindex=\"-1\" aria-d  isabled=\"true\"' : '') . "><</a>
                                        </li>
                                        
                                        <!-- Page numbers -->";
                                        
                                        for($i = 1; $i <= $total_pages; $i++) {
                                            echo "<li class=\"page-item " . ($current_page == $i ? 'active' : '') . "\">
                                                <a class=\"page-link\" href=\"?content=purchase_request&page=" . $i . "\">
                                                    " . $i . "
                                                </a>
                                            </li>";
                                        }
                                        
                                        echo "<!-- Next button -->
                                        <li class=\"page-item " . ($current_page >= $total_pages ? 'disabled' : '') . "\">
                                            <a class=\"page-link\" href=\"?content=purchase_request&page=" . ($current_page + 1) . "\"" .
                                            ($current_page >= $total_pages ? ' tabindex=\"-1\" aria-disabled=\"true\"' : '') . ">></a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>";
                        }else{
                        echo "
                        <div>
                            <p>No Purchase request yet.</p>
                        </div>
                        ";
                      }
                  }

              
    } else {
        echo "<h3>You do not have access to this content.</h3>";
    }
} elseif ($content === 'approved_requisitions') {
    if ($conca === 'Inventory Manager') {
        echo "<h2>List of Approved Requisitions</h2>";
        // Check if we're viewing details of a specific requisition
        if (isset($_GET['req_id'])) {
            include('manager_inv_approved_req.php');
            // Display detailed view
            if ($response['items']) {
                echo "
                    <div class='card rounded-4 p-4'>
                        <div class='text-start'>
                            <a href='#' class='btn btn-link back-to-list p-0 mb-3' data-content='approved_requisitions'>
                                <i class='fas fa-arrow-left'></i> Back
                            </a>
                        </div>
                        <h3>Requisition ID {$_GET['req_id']}</h3>
                        <div class='row mb-3'>
                            <div class='col-md-6'>
                                <p><strong>Name:</strong> {$response['employee_name']}</p>
                                <p><strong>Contact no:</strong> {$response['contact_no']}</p>
                            </div>
                            <div class='col-md-6'>
                                <p><strong>Date of Request:</strong> {$response['date']}</p>
                                <p><strong>Department:</strong> {$response['department']}</p>
                            </div>
                        </div>
          
                        <table class='table'>
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Description</th>
                                    <th>Ask</th>
                                    <th>Item Stock</th>
                                    <th>Withdrawn / To Be Withdrawn</th>
                                    <th>Delivered Amount</th>
                                    <th>Withdraw Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>";
                                 
                            foreach ($response['items'] as $item) {
                                echo "<tr>
                                        <td>" . htmlspecialchars($item['item_name']) . "</td>
                                        <td>" . htmlspecialchars($item['description']) . "</td>
                                        <td>" . htmlspecialchars($item['quantity']) . "</td>
                                        <td>" . htmlspecialchars($item['stock']) . "</td>
                                        <td>" . htmlspecialchars($item['withdrawed']) . "</td>
                                        <td>" . htmlspecialchars($item['delivered']) . "</td>
                                        <td>
                                            <input type='number' name='withdraw_amount' 
                                                class='form-control' 
                                                placeholder='Enter amount'>
                                        </td>
                                        <td>
                                            <select name='employee' class='form-control'>
                                                <option value=''>Select Employee</option>";
                                
                                // Display the staff dropdown
                                foreach ($response['staff'] as $staff) {
                                    echo "<option value='" . htmlspecialchars($staff['emp_id']) . "'>" . htmlspecialchars($staff['employee_name']) . "</option>";
                                }
                                
                                echo "</select>
                                        </td>
                                        <td>
                                            <button class='btn btn-primary withdraw-btn' 
                                                data-reqID='{$_GET['req_id']}'
                                                data-id='{$item['item_id']}' 
                                                data-quantity='{$item['quantity']}' 
                                                data-stock='{$item['stock']}'>
                                                Withdraw
                                            </button>
                                        </td>
                                    </tr>";
                            }
                            include('get_rf_withdrawal.php');
                
                            echo "</tbody>
                                </table>
                                
                                <div class='mt-3'>
                                    <button class='btn btn-danger' id='endBtn' data-id='{$_GET['req_id']}'>End</button>
                                </div>
                             ";
                             if (isset($finalResult) && is_array($finalResult) && count($finalResult) > 0) {
                                    echo "<table class='table'>
                                    <thead>
                                        <tr>
                                            <th colspan='7'>Withdrawal History</th>
                                        </tr>
                                        <tr>
                                            <th>Withdrawal ID</th>
                                            <th>Quantity</th>
                                            <th>Task Given Date</th>
                                            <th>Withdraw Date</th>
                                            <th>Delivered Date</th>
                                            <th>Received Date</th>
                                            <th>Tasked To</th>
                                            <th>Item Name</th>
                                            <th>Action</th> <!-- New column for Cancel -->
                                        </tr>
                                    </thead>
                                    <tbody>";
                            
                            foreach ($finalResult as $withdrawal) {
                                $cancelButton = '';
                                // Check if Received Date is NULL and provide a cancel option
                                if (is_null($withdrawal['withdraw_date'])) {
                                    $cancelButton = "<button class='btn btn-danger cancel-btn' data-reqID='{$_GET['req_id']}' data-withdrawal-id='{$withdrawal['withdrawal_id']}'>Cancel</button>";
                                    $receivedText = ''; // No "Received" text if the button is shown
                                } else if(!is_null($withdrawal['received_date'])) {
                                    $cancelButton = ''; // No button if the withdrawal is received
                                    $receivedText = 'Received'; // Display "Received" text if received_date is not null
                                }else{
                                    
                                    $receivedText = ''; // Display "Received" text if received_date is not null
                                }
                            
                                echo "<tr>
                                    <td>{$withdrawal['withdrawal_id']}</td>
                                    <td>{$withdrawal['quantity']}</td>
                                    <td>{$withdrawal['task_given']}</td>
                                    <td>{$withdrawal['withdraw_date']}</td>
                                    <td>{$withdrawal['date_delivered']}</td>
                                    <td>{$withdrawal['received_date']}</td>
                                    <td>{$withdrawal['tasked_to']}</td>
                                    <td>{$withdrawal['item_name']}</td>
                                    <td>{$cancelButton}{$receivedText}</td> <!-- Display Cancel button or 'Received' -->
                                </tr>";
                            }
                            
                            echo "</tbody>
                                </table>";
                            } else {
                                // If no withdrawal details found, display a message
                                echo "<p>No withdrawals yet.</p>";
                            }
            
                            echo"
            
                            </div>";
                        } else {
                            echo 'Requisition not found';
                        }
                
                
                
                    echo"<script>
                        $(document).ready(function() {
                            $('#endBtn').on('click', function(event) {
                                event.preventDefault(); // Prevent default button behavior

                                // Get the PRF_ID from the data attribute
                                const prfId = $(this).data('id');

                                if (prfId) {
                                    // Confirm the user's action before proceeding
                                    if (confirm('Are you sure you want to close this requisition? This action cannot be undone.')) {
                                        // Send an AJAX request to the server
                                        $.ajax({
                                            url: 'update_status.php', // Replace with your PHP script path
                                            method: 'POST',
                                            data: { PRF_ID: prfId }, // Send the PRF_ID to the server
                                            success: function(response) {
                                                if (response.trim() === \"Status updated to 'closed' successfully.\") {
                                                    alert('Requisition successfully closed.');
                                                    location.reload(); // Reload the page to reflect changes
                                                } else if (response.trim() === \"Cannot close PRF. There are records in rf_withdrawal with dates populated.\") {
                                                    alert('Requisition cannot be closed. Some withdrawal records are still active.');
                                                } else {
                                                    alert(response); // Display any other server response
                                                }
                                            },
                                            error: function(xhr, status, error) {
                                                alert('An error occurred: ' + error);
                                            }
                                        });
                                    }
                                } else {
                                    alert('PRF_ID is missing. Please check your setup.');
                                }
                            });



                            // Handle Withdraw Button Click
                            $('.withdraw-btn').on('click', function(event) {
                                event.preventDefault(); // Prevent default form submission

                                var itemId = $(this).data('id');
                                var reqId = $(this).data('reqid');
                                var requestedQuantity = $(this).data('quantity');
                                var availableStock = $(this).data('stock');
                                var withdrawAmount = $(this).closest('tr').find('input[name=\"withdraw_amount\"]').val();
                                var empId = $(this).closest('tr').find('select[name=\"employee\"]').val();

                                if (!empId) {
                                    alert('Please select an employee.');
                                    return;
                                }

                                // Validate the withdraw amount
                                if (withdrawAmount && !isNaN(withdrawAmount)) {
                                    if (withdrawAmount > requestedQuantity) {
                                        alert('Withdrawal amount cannot exceed the requested quantity.');
                                        return;
                                    }
                                    if (withdrawAmount > availableStock) {
                                        alert('Not enough inventory stock.');
                                        return;
                                    }
                                    if (withdrawAmount < 0) {
                                        alert('Do not input negative numbers');
                                        return;
                                    }

                                    // Perform the POST request for withdrawal
                                    $.post('rf_withdrwa.php', {
                                        req_id: reqId,
                                        withdraw_amount: withdrawAmount,
                                        item_id: itemId,
                                        emp_id: empId
                                    }, function(response) {
                                        console.log(response);
                                        if (response.success) {
                                            alert('Withdrawal successful!');
                                            $(document).trigger('loadContentEvent', ['approved_requisitions', reqId]);
                                        } else {
                                            alert('Error: ' + response.message);
                                        }
                                    }, 'json').fail(function(jqXHR, textStatus, errorThrown) {
                                        alert(\"Request failed: \" + textStatus + \" \" + errorThrown);
                                    });
                                } else {
                                    alert('Please enter a valid amount');
                                }
                            });

                            // Handle Cancel Button Click
                            $('.cancel-btn').on('click', function(event) {
                                event.preventDefault(); // Prevent default form submission
                                var reqId = $(this).data('reqid');
                                console.log(reqId);
                                var withdrawalId = $(this).data('withdrawal-id'); // Get the withdrawal ID

                                // Perform the POST request for cancellation
                                $.post('cancel_withdrawal.php', { withdrawal_id: withdrawalId }, function(response) {
                                    console.log(response); // Log the response for debugging
                                    if (response.success) {
                                        // Display success message
                                        alert(response.message);
                                        console.log();
                                        $(document).trigger('loadContentEvent', ['approved_requisitions', reqId]);
                                    } else {
                                        // Display error message
                                        alert('Error: ' + response.message);
                                    }
                                }, 'json').fail(function(jqXHR, textStatus, errorThrown) {
                                    // If the request fails, log the error
                                    console.log(\"Request failed: \" + textStatus + \" \" + errorThrown);
                                    console.log(\"Response: \" + jqXHR.responseText); // Log the raw response from the server
                                });
                            });
                        });
                    </script>";


                
        } else {
            // Show list view
            include('manager_inv_approved_req.php');
            
            if (!isset($response['status'])) {
                echo "
                <div class='card rounded-4 p-4'>
                    <table class='table' id='requisitions-table'>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Requester Name</th>
                                <th>Submitted Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>";
                
                foreach ($response as $req) {
                    echo "<tr>
                            <td>" . htmlspecialchars($req['requisition_id']) . "</td>
                            <td>" . htmlspecialchars($req['employee_name']) . "</td>
                            <td>" . htmlspecialchars($req['submitted_date']) . "</td>
                            <td>
                                <a href='#' 
                                    class='btn btn-sm btn-primary view-requisition' 
                                    data-content='approved_requisitions' 
                                    data-id='" . $req['requisition_id'] . "'>
                                        <i class='fas fa-eye'></i> 
                                </a>
                            </td>
                        </tr>";
                }
                
                echo "</tbody>
                    </table>
                </div>";
            } else {
                echo "<h3>No pending requisitions found.</h3>";
            }
        }
    } else {
        echo "<h3>You do not have access to this content.</h3>";
    }
}
elseif ($content === 'requisition_history') {
    // Inside the requisition_withdrawal section in load_content.php
    echo "<h2>Requisition History</h2>";

    // Button to load requisition history
    echo "<button id=\"goToWithdrawal\" class=\"btn btn-primary\">Go to Requisition History</button>";



} elseif ($content === 'requisition_withdrawal') {
    echo "<h2>Requisition Withdrawal</h2>";

    echo"<button id=\"requisition-form-link\" class=\"btn btn-primary\">Go to Requisition Withdrawal</button>
";


} elseif ($content === 'requisition_form') {
    // Before executing echo, perform the check for previous requisition
    include('check_prev_req.php'); // Assuming the check is done in the included file

    // Make sure $response is initialized before use
    $response = $response ?? ['prf_status' => 'No Pending Requisition', 'items' => []];
    
    

    if (isset($response['prf_status']) && $response['prf_status'] == 'approved') {
        // Approved requisition logic here
        echo "<h2>Approved Requisition</h2>";
        echo "<h4>Status: " . $response['prf_status'] . "</h4>";
    
        // Display requisition details in a compact, horizontal format
        echo "
        <div class='card rounded-4 p-4'>
            <h3>Requisition Details</h3>
            <div class='row'>
                <div class='col-md-4'>
                    <p><strong>Requisition ID:</strong> " . htmlspecialchars($response['requisition_id']) . "</p>
                </div>
                <div class='col-md-4'>
                    <p><strong>Employee Name:</strong> " . htmlspecialchars($response['employee_name']) . "</p>
                </div>
                <div class='col-md-4'>
                    <p><strong>Contact Number:</strong> " . htmlspecialchars($response['contact_no']) . "</p>
                </div>
            </div>
            <div class='row'>
                <div class='col-md-4'>
                    <p><strong>Department:</strong> " . htmlspecialchars($response['department']) . "</p>
                </div>
                <div class='col-md-4'>
                    <p><strong>Requisition Date:</strong> " . htmlspecialchars($response['date']) . "</p>
                </div>
                <div class='col-md-4'>
                    <p><strong>Status:</strong> " . htmlspecialchars($response['prf_status']) . "</p>
                </div>
            </div>";
    
        // Display the items in a table
        echo "
        <h3>Items in Requisition</h3>
        <table class='table'>
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Description</th>
                    <th>Quantity Requested</th>
                    
                    
                    <th>Delivered Quantity</th>
                </tr>
            </thead>
            <tbody>";
    
        // Loop through the items and display them in the table
        foreach ($response['items'] as $item) {
            echo "<tr>
                    <td>" . htmlspecialchars($item['item_name']) . "</td>
                    <td>" . htmlspecialchars($item['description']) . "</td>
                    <td>" . htmlspecialchars($item['quantity']) . "</td>
                    
                    <td>" . ($item['delivered'] === null ? '0' : htmlspecialchars($item['delivered'])) . "</td>
                </tr>";
        }
    
        echo "</tbody></table>";
        // Add an End button
        echo "
        <div class='mt-3'>
            <button class='btn btn-danger endBtn' data-req-id='" . htmlspecialchars($response['requisition_id'], ENT_QUOTES, 'UTF-8') . "'>
                End
            </button>
        </div>";
        
        // Fetch withdrawal history using .get() AJAX request
        echo "<h3>Withdrawal History</h3>";
        echo "<table class='table'>
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Quantity Withdrawn</th>
                        <th>Withdrawn By</th>
                        <th>Date Withdrawn</th>
                        <th>Date Delivered</th>
                        <th>Date Received</th>
                        <th>Acknowledge</th> <!-- New column for Delivered button -->
                    </tr>
                </thead>
                <tbody id='withdrawal-history'></tbody>
            </table>";
        
        // Add JavaScript for AJAX request to fetch withdrawal data
        echo "
        <script>
            $(document).ready(function() {
                var reqId = '" . htmlspecialchars($response['requisition_id']) . "';

                $('.endBtn').on('click', function(event) {
                    event.preventDefault(); // Prevent default button behavior

                    // Get the PRF_ID from the data attribute
                    const prfId = $(this).data('req-id');

                    if (prfId) {
                        // Confirm the user's action before proceeding
                        if (confirm('Are you sure you want to close this requisition? This action cannot be undone.')) {
                            // Send an AJAX request to the server
                            $.ajax({
                                url: 'update_status.php', // Replace with your PHP script path
                                method: 'POST',
                                data: { PRF_ID: prfId }, // Send the PRF_ID to the server
                                success: function(response) {
                                    if (response.trim() === \"Status updated to 'closed' successfully.\") {
                                        alert('Requisition successfully closed.');
                                        location.reload(); // Reload the page to reflect changes
                                    } else if (response.trim() === \"Cannot close PRF. There are records in rf_withdrawal with dates populated.\") {
                                        alert('Requisition cannot be closed. Some withdrawal records are still active.');
                                    } else {
                                        alert(response); // Display any other server response
                                    }
                                },
                                error: function(xhr, status, error) {
                                    alert('An error occurred: ' + error);
                                }
                            });
                        }
                    } else {
                        alert('PRF_ID is missing. Please check your setup.');
                    }
                });

                
                $.get('get_rw_staff.php', { req_id: reqId }, function(data) {
                    var withdrawalData = JSON.parse(data);
                    var html = '';
                    
                    if (Array.isArray(withdrawalData) && withdrawalData.length > 0) {
                        withdrawalData.forEach(function(withdrawal) {
                            html += '<tr>';
                            html += '<td>' + withdrawal.item_name + '</td>';
                            html += '<td>' + withdrawal.quantity + '</td>';
                            html += '<td>' + withdrawal.withdrawn_by + '</td>';
                            html += '<td>' + (withdrawal.date_withdrawn || '') + '</td>';
                            html += '<td>' + (withdrawal.date_delivered || '') + '</td>';
                            html += '<td>' + (withdrawal.received_date  || '') + '</td>';
                            
                            // If the date delivered is null, add a \"Delivered\" button
                            if (withdrawal.date_delivered && !withdrawal.received_date) {
                                html += '<td><button class=\"btn btn-success btn-sm\" onclick=\"markDelivered(' + withdrawal.withdrawal_id + ')\">Mark as Delivered</button></td>';
                            }else if(!withdrawal.received_date){
                                html += '<td></td>'; // Empty cell if already delivered
                            } else {
                                html += '<td>Delivered</td>'; // Empty cell if already delivered
                            }

                            html += '</tr>';
                        });
                    } else {
                        html = '<tr><td colspan=\"6\">No withdrawal information available.</td></tr>';
                    }
                    
                    $('#withdrawal-history').html(html);
                });
            });

            // Function to mark the item as delivered (for future implementation)
            function markDelivered(withdrawalId) {
                $.ajax({
                    url: 'mark_received.php',  // PHP file to process the request
                    type: 'POST',
                    data: { wd_id: withdrawalId },
                    success: function(response) {
                        if (response === 'success') {
                            alert('Item marked as delivered');
                            location.reload();  // Reload the page to update the table
                        } else {
                            alert('Error updating delivered date');
                        }
                    },
                    error: function() {
                        alert('An error occurred while marking as delivered');
                    }
                });
            }
        </script>";

    } elseif (isset($response['prf_status']) && $response['prf_status'] == 'pending') {
        // If there's a pending request, display it
        echo "<h2>Pending Requisition</h2>
              <h4>Status: " . $response['prf_status'] . "</h4>
              ";
                
        // Display the items in a Bootstrap table, now wrapped in a card
        echo "
        <h3>Pending Items</h3>
        <div class='card rounded-4 p-4'>
            <table class='table'>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Reason</th>
                    </tr>
                </thead>
                <tbody>";

        // Loop through the items and display them
        foreach ($response['items'] as $item) {
            echo "<tr>
                    <td>" . htmlspecialchars($item['item_name']) . "</td>
                    <td>" . htmlspecialchars($item['quantity']) . "</td>
                    <td>" . htmlspecialchars($item['description']) . "</td>
                </tr>";
        }

        echo "</tbody></table>
        </div>";  // Close card div
        echo '<button class="btn btn-danger deleteBtn" data-prf-id="' . $response['prf_id'] . '">Delete</button>';
        echo"
            <script>
            $(document).ready(function(){
            
                $(\".deleteBtn\").on('click',function(){
                var prfId = $(this).data(\"prf-id\");
                $.post(\"delete_pending_requisition.php\", { prf_id: prfId }, function(response) {
                    console.log(response);
                    if (response == 1) {
                        alert(\"Record successfully deleted!\");
                        location.reload();
                    } else if(response == 2) {
                        alert(\"Your Request has already been approved\");
                        location.reload();
                    }else if(response == 3) {
                        alert(\"Your Request has already been rejected\");
                        location.reload();
                    }else{
                        alert(\"Your requisition is not found\")
                        location.reload();
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    
                    alert(\"Failed to delete. Please try again.\");
                });
                
                
                });
            
            });
            
            
            </script>
        ";

    } else {
        // No pending requisition, show the requisition form
        echo "
        <h2>Requisition Form</h2>
        <p>Please fill out the requisition form below.</p>
        <h3>Requisition Table</h3>
        <table class='table'>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Reason</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id='requisition-table-body'>
                <tr>
                    <td>
                        <select id='item' name='item' class='form-select'>
                            <option value=''>Loading items...</option>
                        </select>
                    </td>
                    <td>
                        <input type='number' id='quantity' name='quantity' class='form-control' min='1'>
                    </td>
                    <td>
                        <textarea id='reason' name='reason' class='form-control'></textarea>
                    </td>
                    <td>
                        <button type='button' class='btn btn-primary' id='add-row'>Add Item</button>
                    </td>
                </tr>
            </tbody>
        </table>

        <h3>Added Items</h3>
        <table class='table'>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Reason</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id='added-items'>
                <!-- Dynamically added rows will appear here -->
            </tbody>
        </table>

        <button type='button' id='submit-form' class='btn btn-success'>Submit Requisition</button>

        <!-- Modal for displaying submitted requisition -->
        <div class='modal' id='itemsModal' tabindex='-1' role='dialog' aria-labelledby='itemsModalLabel' aria-hidden='true'>
            <div class='modal-dialog' role='document'>
                <div class='modal-content'>
                    <div class='modal-header'>
                        <h5 class='modal-title' id='itemsModalLabel'>Submitted Requisition</h5>
                        <button type='button' class='close' id='close-modal' aria-label='Close'>
                            <span aria-hidden='true'>&times;</span>
                        </button>
                    </div>
                    <div class='modal-body'>
                        <h4>The following items were selected:</h4>
                        <ul id='itemsList'>
                            <!-- Items will be added here dynamically -->
                        </ul>
                    </div>
                    <div class='modal-footer'>
                        <button type='button' class='btn btn-secondary' id='close-modal-footer'>Close</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
        $(document).ready(function() {
            // Populate dropdown with inventory items
            $.ajax({
                url: 'get_inventory.php',
                type: 'GET',
                dataType: 'json',
                success: function(items) {
                    const \$dropdown = $('#item');
                    \$dropdown.empty(); // Clear existing options
                    \$dropdown.append('<option value=\"\">Select Item</option>'); // Default option

                    if (items.length > 0) {
                        items.forEach(function(item) {
                            \$dropdown.append('<option value=\"' + item.id + '\" data-name=\"' + item.name + '\">' + item.name + '</option>');
                        });
                    } else {
                        alert('No items found in inventory.');
                    }
                },
                error: function() {
                    alert('Error fetching inventory items. Please try again.');
                    $('#item').append('<option value=\"\">Error loading items</option>');
                }
            });

            // Add row to table dynamically
            $('#add-row').click(function() {
                var itemID = $('#item').val();
                var itemName = $('#item option:selected').data('name');
                var quantity = $('#quantity').val();
                var reason = $('#reason').val();

                if (itemID && itemName && quantity && reason) {
                    var newRow = '<tr>';
                    newRow += '<td data-id=\"' + itemID + '\">' + itemName + '</td>'; // Display name, store ID
                    newRow += '<td>' + quantity + '</td>';
                    newRow += '<td>' + reason + '</td>';
                    newRow += '<td><button class=\"btn btn-danger btn-sm delete-row\">Delete</button></td>';
                    newRow += '</tr>';

                    // Append the new row to the added items table
                    $('#added-items').append(newRow);

                    // Remove the item from the dropdown
                    $('#item option[value=\"' + itemID + '\"]').remove();

                    // Clear the form fields after adding
                    $('#item').val('');
                    $('#quantity').val('');
                    $('#reason').val('');
                } else {
                    alert('Please fill out all fields before adding an item.');
                }
            });

            // Delete a row from the added items table
            $(document).on('click', '.delete-row', function() {
                var \$row = $(this).closest('tr');
                var itemID = \$row.find('td:first').data('id');
                var itemName = \$row.find('td:first').text();

                // Add the removed item back to the dropdown
                $('#item').append('<option value=\"' + itemID + '\" data-name=\"' + itemName + '\">' + itemName + '</option>');

                // Remove the row from the table
                \$row.remove();
            });

            // Submit the requisition form
            $('#submit-form').click(function() {
                var items = [];
                var quantities = [];
                var reasons = [];

                $('#added-items tr').each(function() {
                    var itemID = $(this).find('td:first').data('id'); // Item ID
                    var quantity = $(this).find('td').eq(1).text(); // Remove extra parenthesis
                    var reason = $(this).find('td').eq(2).text(); // Remove extra parenthesis

                    items.push(itemID);
                    quantities.push(quantity);
                    reasons.push(reason);
                });
            
                if (items.length > 0) {
                    $.ajax({
                        url: 'submit_requisition.php',
                        type: 'POST',
                        data: {
                            reason: \"RF\",
                            items: items,
                            quantities: quantities,
                            reasons: reasons
                        },
                        success: function(response) {
                            console.log(response);
                            if (response == 1) {
                                $('#itemsList').empty();
                                for (var i = 0; i < items.length; i++) {
                                    var itemDetail = 'ID: ' + items[i] + ', Qty: ' + quantities[i] + ', Reason: ' + reasons[i];
                                    $('#itemsList').append('<li>' + itemDetail + '</li>');
                                }
                                // Show the modal using Bootstrap 5 API
                                var myModal = new bootstrap.Modal(document.getElementById('itemsModal'));
                                myModal.show();
                            } else {
                                alert('Error occurred during submission. Please try again.');
                            }
                        },
                        error: function() {
                            alert('Error occurred during submission. Please try again.');
                        }
                    });
                } else {
                    alert('No items have been added to the requisition.');
                }
            });

            $('#close-modal').click(function() {
                $('#itemsModal').modal('hide');
            });

            $('#close-modal-footer').click(function() {
                $('#itemsModal').modal('hide');

                $.ajax({
                    url: 'check_role.php', // PHP file that checks the session role
                    method: 'GET',
                    success: function(response) {
                        if (response === 'Staff') {
                            location.reload(); // Reload the page if the role is 'staff'
                        }
                    }
                });
            });

            $('#itemsModal').on('hidden.bs.modal', function () {
                // Return added items to the dropdown
                $('#added-items tr').each(function() {
                    var itemID = $(this).find('td:first').data('id'); // Item ID
                    var itemName = $(this).find('td:first').text(); // Item Name
                    
                    // Add the item back to the dropdown
                    $('#item').append('<option value=\"' + itemID + '\" data-name=\"' + itemName + '\">' + itemName + '</option>');
                });

                // Clear added items table
                $('#added-items').empty();

                // Reset input fields
                $('#item').val('');
                $('#quantity').val('');
                $('#reason').val('');
            });
        });
        </script>
    ";
    }
}elseif ($content === 'inventory-task') {
    // Include the inventory_task.php file which will populate $withdrawalRecords
    
    
    // Check if there are any withdrawal records to display
    // Check if 'req_id' is set in the URL
    if (isset($_GET['req_id'])) {
        $reqId = $_GET['req_id'];
        echo "<h3>Requisition Withdrawal Task</h3>";

        // Include the file that contains the query result  
        require_once 'task_rf_withdrawal.php';
        
        // Check if there are results
        if ($requisitionData) {
            // Display requisition details in a 3x3 grid inside a Bootstrap card
            echo "<div class='card'>
                    <div class='card-header'>
                        <h5>Requisition Details</h5>
                    </div>
                    <div class='card-body'>
                        <div class='row'>
                            <div class='col-md-4'>
                                <p><strong>Requisition ID:</strong> {$requisitionData['requisition_id']}</p>
                            </div>
                            <div class='col-md-4'>
                                <p><strong>Name:</strong> {$requisitionData['employee_name']}</p>
                            </div>
                            <div class='col-md-4'>
                                <p><strong>Contact No:</strong> {$requisitionData['contact_number']}</p>
                            </div>
                        </div>
                        <div class='row'>
                            <div class='col-md-4'>
                                <p><strong>Date of Request:</strong> {$requisitionData['date_of_request']}</p>
                            </div>
                            <div class='col-md-4'>
                                <p><strong>Department:</strong> {$requisitionData['department_name']}</p>
                            </div>
                        </div>
                    </div>
                </div><br>";
        }
        
        // Check if there are withdrawal records
        if ($withdrawalResult->num_rows > 0) {
            // Now display the withdrawal records in a table
            echo "<table class='table'>
                    <thead>
                        <tr>
                            <th>Withdrawal ID</th>
                            <th>Quantity</th>
                            <th>Task Given Date</th>
                            <th>Model Name</th>
                            <th>Location</th>
                            <th>Withdrawn Date</th>
                            <th>Delivered Date</th>
                            <th>Received Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>";
        
            // Loop through each withdrawal record and display it
            while ($row = $withdrawalResult->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['withdrawal_id']}</td>
                        <td>{$row['WD_QUANTITY']}</td>
                        <td>" . ($row['WD_DATE'] ?: 'Not Withdrawn') . "</td>
                        <td>{$row['INV_MODEL_NAME']}</td>
                        <td>{$row['INV_LOCATION']}</td>
                        <td>";
        
                // If WD_DATE_WITHDRAWN is null, show a date-time input field
                if (is_null($row['WD_DATE_WITHDRAWN'])) {
                    echo "<input type='datetime-local' name='WD_DATE_WITHDRAWN[{$row['withdrawal_id']}]' class='WD_DATE_WITHDRAWN'>";
                } else {
                    echo $row['WD_DATE_WITHDRAWN'];
                }
        
                echo "</td>
                        <td>";
        
                // If WD_DATE_DELIVERED is missing but WD_DATE_WITHDRAWN is null, show a blank field
                if (!is_null($row['WD_DATE_WITHDRAWN']) && is_null($row['WD_DATE_DELIVERED'])) {
                    echo "<input type='datetime-local' name='WD_DATE_DELIVERED[{$row['withdrawal_id']}]' class='WD_DATE_DELIVERED'>";
                } else {
                    echo $row['WD_DATE_DELIVERED'];
                }
        
                echo "</td>
                        <td>";
        
                // If WD_DATE_RECEIVED is null, leave it blank
                if (is_null($row['WD_DATE_RECEIVED'])) {
                    echo "";  // Leave blank instead of showing an input field
                } else {
                    echo $row['WD_DATE_RECEIVED'];  // Display the received date
                }
        
                // Save button for each row
                echo "</td>
                        <td>";
                        if (is_null($row['WD_DATE_WITHDRAWN']) || is_null($row['WD_DATE_DELIVERED']) || is_null($row['WD_DATE_RECEIVED'])) {
                            echo "<button type='button' class='btn btn-primary save-btn' data-wdate ='{$row['WD_DATE_WITHDRAWN']}' data-id='{$row['withdrawal_id']}'>Save</button>";
                        }
                echo"</td>
                    </tr>";
            }
        
            echo "</tbody></table>";
        
            // Script for handling save action
            echo "<script>
            $(document).ready(function() {
                // Attach the click event to each Save button
                var reqId = {$reqId};
                $('.save-btn').click(function() {
                    var withdrawalId = $(this).data('id'); // Get the withdrawal ID from the button
                    var wdate = $(this).data('wdate'); 
        
                    // Get the values of the input fields
                    var wdDateWithdrawn = $('input[name=\"WD_DATE_WITHDRAWN[' + withdrawalId + ']\"]').val();
                    var wdDateDelivered = $('input[name=\"WD_DATE_DELIVERED[' + withdrawalId + ']\"]').val();
        
                    // Prepare data object, but only include the fields that have values
                    var data = { withdrawal_id: withdrawalId };
        
                    if (wdDateWithdrawn) {
                        // Validate the withdrawal date
                        var withdrawnDate = new Date(wdDateWithdrawn);
                        if (!validateDate(withdrawnDate)) {
                            alert(\"Withdrawal date must not be older than now or more than 1 hour in the future.\");
                            return;
                        }
                        data.wd_date_withdrawn = wdDateWithdrawn;
                    }
        
                    if (wdDateDelivered) {
                        // Validate the delivery date
                        var deliveredDate = new Date(wdDateDelivered);
                        if (!validateDate(deliveredDate)) {
                            alert(\"Delivery date must not be older than now or more than 1 hour in the future.\");
                            return;
                        }
        
                        // Normalize both wdate and wdDateDelivered to ensure proper comparison
                        var wdateNormalized = new Date(wdate);
                        var wdDateDeliveredNormalized = new Date(data.wd_date_delivered);
        
                        if (wdateNormalized.getHours() === 12 && wdateNormalized.getMinutes() === 0) {
                            wdateNormalized.setHours(0); // Convert 12:00 AM to 00:00 for comparison
                        }
        
                        if (wdDateDeliveredNormalized < wdateNormalized) {
                            alert(\"Delivery date can't be earlier than withdrawal date\");
                            return;
                        }
        
                        data.wd_date_delivered = wdDateDelivered;
                    }
        
                    // Send the data to the server using AJAX
                    $.ajax({
                        url: 'save_withdrawal.php', // PHP script to handle saving
                        type: 'POST',
                        data: data,
                        success: function(response) {
                            // Process the response from the server (echoed response)
                            var result = JSON.parse(response);
                            if (result.success) {
                                alert('Record saved successfully!');
                                // Optionally, update the UI or feedback
                                $(document).trigger('loadContentEvent', ['inventory-task', reqId]);
                            } else {
                                alert('Error saving record.');
                            }
                        },
                        error: function() {
                            alert('There was an error with the request.');
                        }
                    });
                });
        
                // Function to validate a date (not older than now and not more than 1 hour ahead)
                function validateDate(inputDate) {
                    var now = new Date();
                    var oneHourLater = new Date();
                    oneHourLater.setHours(now.getHours() + 1);  // Set to one hour after the current time
        
                    // Ensure the date is not in the past and is not more than 1 hour in the future
                    return inputDate <= oneHourLater && inputDate >= now;
                }
            });
        </script>";
        

        } else {
            echo "<p>No withdrawal records found for this requisition.</p>";
        }
    } else {
        include('inventory_task.php');
        if (!empty($withdrawals)) {
            // Create the table with the retrieved records
            echo "<h3>Requisition Withdrawal Task</h3>";
    
            echo "<table class='table'>
                    <thead>
                        <tr>
                            <th>Requisition ID</th>
                            <th>Department</th>
                            <th>Employee Name</th>
                            <th>Pending Task</th> <!-- Column for Pending Count -->
                            <th>Action</th> <!-- New column for View button -->
                        </tr>
                    </thead>
                    <tbody>";
            
            // Loop through each withdrawal record and display it
            foreach ($withdrawals as $row) {
                // Add a row for each withdrawal
                echo "<tr>
                        <td>{$row['requisition_id']}</td>
                        <td>{$row['DEPT_NAME']}</td>
                        <td>{$row['employee_name']}</td>
                        <td>" . ($pendingCount > 0 ? $pendingCount : 'N/A') . "</td> <!-- Show Pending Count -->
                        <td>
                            <a href='#' class='btn btn-sm btn-primary view-wd' 
                               data-content='inventory-task' 
                               data-id='" . $row['requisition_id'] . "'>
                               <i class='fas fa-eye'></i> View
                            </a>
                        </td>
                    </tr>";
            }
    
            echo "</tbody></table>";
        } else {
            // If no withdrawal records are found, display a message
            echo "<p>No task available.</p>";
        }
    }
}
elseif ($content === 'account_settings') {
    include('account_settings.php');
    // Display the form
    echo "<div class='container'>
            <div class='card rounded-4 p-4'>
                <h3 class='mb-4'>Account Settings</h3>
                <form id='accountSettingsForm'>
                    <div class='row'>
                        <div class='col-md-4 mb-3'>
                            <label for='emp_fname' class='form-label'>First Name</label>
                            <input type='text' class='form-control' id='emp_fname' name='emp_fname' 
                                value='" . htmlspecialchars($user['EMP_FNAME']) . "' required>
                        </div>
                        <div class='col-md-4 mb-3'>
                            <label for='emp_mname' class='form-label'>Middle Name</label>
                            <input type='text' class='form-control' id='emp_mname' name='emp_mname' 
                                value='" . htmlspecialchars($user['EMP_MNAME']) . "'>
                        </div>
                        <div class='col-md-4 mb-3'>
                            <label for='emp_lname' class='form-label'>Last Name</label>
                            <input type='text' class='form-control' id='emp_lname' name='emp_lname' 
                                value='" . htmlspecialchars($user['EMP_LNAME']) . "' required>
                        </div>
                    </div>

                    <div class='mb-3'>
                        <label for='emp_address' class='form-label'>Address</label>
                        <input type='text' class='form-control' id='emp_address' name='emp_address' 
                            value='" . htmlspecialchars($user['EMP_ADDRESS']) . "' required>
                    </div>

                    <div class='row'>
                        <div class='col-md-6 mb-3'>
                            <label for='emp_email' class='form-label'>Email</label>
                            <input type='email' class='form-control' id='emp_email' name='emp_email' 
                                value='" . htmlspecialchars($user['EMP_EMAIL']) . "' required>
                        </div>
                        <div class='col-md-6 mb-3'>
                            <label for='emp_number' class='form-label'>Contact Number</label>
                            <input type='text' class='form-control' id='emp_number' name='emp_number' 
                                value='" . htmlspecialchars($user['EMP_NUMBER']) . "' required>
                        </div>
                    </div>

                    <div class='mb-3'>
                        <label for='emp_password' class='form-label'>New Password (leave blank to keep current)</label>
                        <input type='password' class='form-control' id='emp_password' name='emp_password'>
                    </div>

                    <div class='mb-3'>
                        <button type='submit' class='btn btn-primary'>Save Changes</button>
                    </div>
                </form>
            </div>
            </div>

            <script>
            $(document).ready(function() {
            $('#accountSettingsForm').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: 'account_settings.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            alert('Profile updated successfully');
                            const fullName = $('#emp_fname').val();
                            $('.username').text(fullName);
                            $('.email').text($('#emp_email').val());
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('An error occurred while updating the profile');
                    }
                });
            });
            });
            </script>";
} elseif ($role === 'Admin') {
    if ($content === 'manage_employees') {
        // Get employees data with pagination
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        require_once 'admin/get_employees.php';
        $result = get_employees(); // Add this line to call the function

        echo "<h2>Manage Employees</h2>

              <p>Add and manage employee accounts here.</p>
              
          <div class='card rounded-4 p-4'>
            <div class='d-flex justify-content-between align-items-center mb-3'>
                <h4>Employee List</h4>
                <button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#addEmployeeModal'>
                    Add Employee
                </button>
            </div>
            <div class='table-responsive'>
                <table class='table table-striped' id='employeesTable'>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Position</th>
                            <th>Contact</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>";
                    
                    if (!empty($result['employees'])) {
                        foreach ($result['employees'] as $row) {
                            // Determine button class and text based on status
                            $statusBtnClass = $row['EMP_STATUS'] == 1 ? 'btn-danger' : 'btn-success';
                            $statusBtnText = $row['EMP_STATUS'] == 1 ? 'Deactivate' : 'Activate';
                            $statusBtnIcon = $row['EMP_STATUS'] == 1 ? 'fa-user-slash' : 'fa-user-check';
                            
                            echo "<tr>
                                    <td>" . htmlspecialchars($row['FULL_NAME']) . "</td>
                                    <td>" . htmlspecialchars($row['EMP_EMAIL']) . "</td>
                                    <td>" . htmlspecialchars($row['DEPT_NAME']) . "</td>
                                    <td>" . htmlspecialchars($row['EMP_POSITION']) . "</td>
                                    <td>" . htmlspecialchars($row['EMP_NUMBER']) . "</td>
                                    <td>
                                        <button class='btn btn-sm btn-primary edit-employee' data-bs-toggle='modal' data-bs-target='#editEmployeeModal' data-id='" . $row['EMP_ID'] . "'>
                                            <i class='fas fa-edit'></i>
                                        </button>
                                        <button class='btn btn-sm " . $statusBtnClass . " toggle-status' 
                                                data-id='" . $row['EMP_ID'] . "'
                                                data-status='" . $row['EMP_STATUS'] . "'>
                                            <i class='fas " . $statusBtnIcon . "'></i> " . $statusBtnText . "
                                        </button>
                                    </td>
                                </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center'>No employees found</td></tr>";
                    }

                    echo "</tbody>
                </table>";

                // Pagination
                if (isset($result['pagination']) && $result['pagination']['total_pages'] > 1) {
                    $pagination = $result['pagination'];
                    echo "<nav aria-label='Page navigation' class='mt-4'>
                            <ul class='pagination justify-content-center'>";
                    
                    // Previous button
                    $prevDisabled = $pagination['current_page'] <= 1 ? ' disabled' : '';
                    echo "<li class='page-item{$prevDisabled}'>
                            <a class='page-link' href='?content=manage_employees&page=" . ($pagination['current_page'] - 1) . "'>Previous</a>
                          </li>";

                    // Page numbers
                    for ($i = 1; $i <= $pagination['total_pages']; $i++) {
                        $active = $pagination['current_page'] == $i ? ' active' : '';
                        echo "<li class='page-item{$active}'>
                                <a class='page-link' href='?content=manage_employees&page={$i}'>{$i}</a>
                              </li>";
                    }

                    // Next button
                    $nextDisabled = $pagination['current_page'] >= $pagination['total_pages'] ? ' disabled' : '';
                    echo "<li class='page-item{$nextDisabled}'>
                            <a class='page-link' href='?content=manage_employees&page=" . ($pagination['current_page'] + 1) . "'>Next</a>
                          </li>";

                    echo "</ul>
                        </nav>";
                }

            echo "</div>
        </div>";

        // Add Employee Modal HTML remains the same
        echo "<!-- Add Employee Modal -->
            <div class='modal fade' id='addEmployeeModal' tabindex='-1' aria-labelledby='addEmployeeModalLabel' aria-hidden='true'>
                <div class='modal-dialog modal-lg'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                            <h5 class='modal-title' id='addEmployeeModalLabel'>Add New Employee</h5>
                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                        </div>
                        <div class='modal-body'>
                            <form id='addEmployeeForm'>
                                <div class='row'>
                                    <div class='col-md-6 mb-3'>
                                        <label for='emp_fname' class='form-label'>First Name</label>
                                        <input type='text' class='form-control' id='emp_fname' name='emp_fname' required>
                                    </div>
                                    <div class='col-md-6 mb-3'>
                                        <label for='emp_lname' class='form-label'>Last Name</label>
                                        <input type='text' class='form-control' id='emp_lname' name='emp_lname' required>
                                    </div>
                                </div>
                                <div class='row'>
                                    <div class='col-md-6 mb-3'>
                                        <label for='emp_email' class='form-label'>Email</label>
                                        <input type='email' class='form-control' id='emp_email' name='emp_email' required>
                                    </div>
                                    <div class='col-md-6 mb-3'>
                                        <label for='emp_password' class='form-label'>Password</label>
                                        <input type='password' class='form-control' id='emp_password' name='emp_password' required>
                                    </div>
                                </div>
                                <div class='row'>
                                    <div class='col-md-4 mb-3'>
                                        <label for='department' class='form-label'>Department</label>
                                        <select class='form-select' id='department' name='department' required>
                                            <option value=''>Select Department</option>
                                            <option value='Finance'>Finance</option>
                                            <option value='Inventory'>Inventory</option>
                                            <option value='Labor'>Labor</option>
                                        </select>
                                    </div>
                                    <div class='col-md-4 mb-3'>
                                        <label for='position' class='form-label'>Position</label>
                                        <select class='form-select' id='position' name='position' required>
                                            <option value=''>Select Position</option>
                                            <option value='Manager'>Manager</option>
                                            <option value='Staff'>Staff</option>
                                        </select>
                                    </div>
                                    <div class='col-md-4 mb-3'>
                                        <label for='contact_no' class='form-label'>Contact Number</label>
                                        <input type='text' class='form-control' id='contact_no' name='contact_no' required>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class='modal-footer'>
                            <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                            <button type='button' class='btn btn-primary' id='submitEmployee'>Add Employee</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deactivate Confirmation Modal -->
            <div class='modal fade' id='toggleStatusModal' tabindex='-1' aria-labelledby='toggleStatusModalLabel' aria-hidden='true'>
            <div class='modal-dialog'>
                <div class='modal-content'>
                    <div class='modal-header'>
                        <h5 class='modal-title' id='toggleStatusModalLabel'>Confirm Status Change</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                    </div>
                    <div class='modal-body'>
                        <p id='toggleStatusMessage'></p>
                        <input type='hidden' id='toggle_emp_id'>
                        <input type='hidden' id='toggle_status'>
                    </div>
                    <div class='modal-footer'>
                        <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancel</button>
                        <button type='button' class='btn btn-primary' id='confirmToggleStatus'>Confirm</button>
                    </div>
                </div>
            </div>
            </div>

            <!-- Edit Employee Modal -->
            <div class='modal fade' id='editEmployeeModal' tabindex='-1' aria-labelledby='editEmployeeModalLabel' aria-hidden='true'>
                <div class='modal-dialog modal-lg'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                            <h5 class='modal-title' id='editEmployeeModalLabel'>Edit Employee</h5>
                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                        </div>
                        <div class='modal-body'>
                            <form id='editEmployeeForm'>
                                <input type='hidden' id='edit_emp_id' name='emp_id'>
                                <div class='row'>
                                    <div class='col-md-6 mb-3'>
                                        <label for='edit_emp_fname' class='form-label'>First Name</label>
                                        <input type='text' class='form-control' id='edit_emp_fname' name='emp_fname' required>
                                    </div>
                                    <div class='col-md-6 mb-3'>
                                        <label for='edit_emp_lname' class='form-label'>Last Name</label>
                                        <input type='text' class='form-control' id='edit_emp_lname' name='emp_lname' required>
                                    </div>
                                </div>
                                <div class='row'>
                                    <div class='col-md-6 mb-3'>
                                        <label for='edit_emp_email' class='form-label'>Email</label>
                                        <input type='email' class='form-control' id='edit_emp_email' name='emp_email' required>
                                    </div>
                                    <div class='col-md-6 mb-3'>
                                        <label for='edit_emp_password' class='form-label'>Password (leave blank if unchanged)</label>
                                        <input type='password' class='form-control' id='edit_emp_password' name='emp_password'>
                                    </div>
                                </div>
                                <div class='row'>
                                    <div class='col-md-4 mb-3'>
                                        <label for='edit_department' class='form-label'>Department</label>
                                        <select class='form-select' id='edit_department' name='department' required>
                                            <option value=''>Select Department</option>
                                            <option value='Finance'>Finance</option>
                                            <option value='Inventory'>Inventory</option>
                                            <option value='Labor'>Labor</option>
                                        </select>
                                    </div>
                                    <div class='col-md-4 mb-3'>
                                        <label for='edit_position' class='form-label'>Position</label>
                                        <select class='form-select' id='edit_position' name='position' required>
                                            <option value=''>Select Position</option>
                                            <option value='Manager'>Manager</option>
                                            <option value='Staff'>Staff</option>
                                        </select>
                                    </div>
                                    <div class='col-md-4 mb-3'>
                                        <label for='edit_contact_no' class='form-label'>Contact Number</label>
                                        <input type='text' class='form-control' id='edit_contact_no' name='contact_no' required>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class='modal-footer'>
                            <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                            <button type='button' class='btn btn-primary' id='updateEmployee'>Update Employee</button>
                        </div>
                    </div>
                </div>
            </div>
          
          <script>
            $(document).ready(function() {
                
                $('.toggle-status').click(function() {
                    const empId = $(this).data('id');
                    const currentStatus = $(this).data('status');
                    const newStatus = currentStatus == 1 ? 0 : 1;
                    const actionText = currentStatus == 1 ? 'deactivate' : 'activate';
                    
                    // Set both values when opening the modal
                    $('#toggle_emp_id').val(empId);
                    $('#toggle_status').val(newStatus);  // Add this line
                    $('#toggleStatusMessage').text('Are you sure you want to ' + actionText + ' this employee?');
                    $('#toggleStatusModal').modal('show');
                });

                // Confirm status toggle
                $('#confirmToggleStatus').click(function() {
                    const empId = $('#toggle_emp_id').val();
                    const newStatus = $('#toggle_status').val();
                    
                    $.ajax({
                        url: 'admin/toggle_employee_status.php',
                        type: 'POST',
                        data: { 
                            emp_id: empId,
                            status: newStatus
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                alert(response.message);
                                $('#toggleStatusModal').modal('hide');
                                window.location.href = 'index.php?content=manage_employees';
                            } else {
                                alert('Error: ' + response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log('Server Response:', xhr.responseText); // Add this for debugging
                            alert('Error changing employee status: ' + error);
                        }
                    });
                });


               
                $('.edit-employee').click(function() {
                const empId = $(this).data('id');
                
                // Fetch employee details
                $.ajax({
                    url: 'admin/get_employee_details.php',
                    type: 'GET',
                    data: { emp_id: empId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            const emp = response.data;
                            // Populate the edit form
                            $('#edit_emp_id').val(emp.EMP_ID);
                            $('#edit_emp_fname').val(emp.EMP_FNAME);
                            $('#edit_emp_lname').val(emp.EMP_LNAME);
                            $('#edit_emp_email').val(emp.EMP_EMAIL);
                            $('#edit_department').val(emp.DEPT_NAME);
                            $('#edit_position').val(emp.EMP_POSITION);
                            $('#edit_contact_no').val(emp.EMP_NUMBER);
                            
                            // Show the modal
                            $('#editEmployeeModal').modal('show');
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Error fetching employee details: ' + error);
                    }
                    });
                });

                $('#updateEmployee').click(function(e) {
                e.preventDefault();
                
                // Form validation
                if (!$('#editEmployeeForm')[0].checkValidity()) {
                    $('#editEmployeeForm')[0].reportValidity();
                    return;
                }

                $.ajax({
                    url: 'admin/update_employee.php',
                    type: 'POST',
                    data: $('#editEmployeeForm').serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            alert(response.message);
                            $('#editEmployeeModal').modal('hide');
                            window.location.href = 'index.php?content=manage_employees';
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Error updating employee: ' + error);
                    }
                    });
                });



                // Form submission
                $('#submitEmployee').click(function(e) {
                    e.preventDefault();
                    
                    // Form validation
                    if (!$('#addEmployeeForm')[0].checkValidity()) {
                        $('#addEmployeeForm')[0].reportValidity();
                        return;
                    }

                    $.ajax({
                        url: 'admin/add_employee.php',
                        type: 'POST',
                        data: $('#addEmployeeForm').serialize(),
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                $('#addEmployeeForm')[0].reset();
                                $('#addEmployeeModal').modal('hide');
                                alert(response.message);
                                // Reload the employee list without refreshing the page
                                window.location.href = '?content=manage_employees';
                            } else {
                                alert('Error: ' + response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log('Response:', xhr.responseText); // Add this for debugging
                            try {
                                const response = JSON.parse(xhr.responseText);
                                alert('Error: ' + response.message);
                            } catch(e) {
                                alert('Error adding employee: ' + error);
                            }
                        }
                    });
                });

                // Clear form when modal is closed
                $('#addEmployeeModal').on('hidden.bs.modal', function () {
                    $('#addEmployeeForm')[0].reset();
                });
            });
          </script>
          
          
          "; // Rest of the modal HTML

    }elseif($content === 'requisition_form_history'){
        if (isset($_GET['req_id'])) {
            // Show detailed view for specific requisition
            include('admin/get_rf_details.php'); // You'll need to create this file
            
            if ($requisitionDetails) {
                echo "<div class='d-flex justify-content-between align-items-center mb-3'>
                        <h3>Requisition Details RF-{$_GET['req_id']}</h3>
                      </div>
                      <div class='card rounded-4 p-4'>
                        <div class='text-start'>
                            <a href='#' class='btn btn-link back-to-list p-0 mb-3' data-content='requisition_form_history'>
                                <i class='fas fa-arrow-left'></i> Back
                            </a>
                        </div>
                          <div class='row'>
                              <div class='col-md-4'>
                                  <p><strong>Requester:</strong> {$requisitionDetails['FULL_NAME']}</p>
                                  <p><strong>Department:</strong> {$requisitionDetails['DEPT_NAME']}</p>
                              </div>
                              <div class='col-md-4'>
                                  <p><strong>Date:</strong> "; echo date('F j, Y h:i A', strtotime($requisitionDetails['PRF_DATE'])); echo "</p>
                                  <p><strong>Status:</strong> {$requisitionDetails['PRF_STATUS']}</p>
                              </div>";

                              if($requisitionDetails['PRF_STATUS'] === 'rejected'){
                                echo "<div class='col-md-4'>
                                        <p><strong>Rejected Date:</strong> "; echo date('F j, Y h:i A', strtotime($requisitionDetails['ap_date'])); echo "</p>
                                        <p><strong>Rejected By:</strong> {$requisitionDetails['APPROVER_NAME']}</p>
                                      </div>";
                              }elseif($requisitionDetails['PRF_STATUS'] === 'approved'){
                                echo "<div class='col-md-4'>
                                        <p><strong>Approved Date:</strong> "; echo date('F j, Y h:i A', strtotime($requisitionDetails['ap_date'])); echo "</p>
                                        <p><strong>Approved By:</strong> {$requisitionDetails['APPROVER_NAME']}</p>
                                      </div>";
                              }
                              
                          echo "</div>";

                          if($requisitionDetails['PRF_STATUS'] === 'rejected'){
                            echo "<div class='alert alert-danger'>
                                    <strong>Rejection Reason:</strong> " . htmlspecialchars($requisitionDetails['ap_desc']) . "
                                  </div>";
                          }
                          
                          echo "<h4>Requested Items</h4>
                          <table class='table'>
                              <thead>
                                  <tr>
                                      <th>Item</th>
                                      <th>Quantity</th>
                                      <th>Description</th>
                                  </tr>
                              </thead>
                              <tbody>";
                          
                foreach ($requisitionDetails['items'] as $item) {
                    echo "<tr>
                            <td>{$item['item_name']}</td>
                            <td>{$item['quantity']}</td>
                            <td>{$item['description']}</td>
                          </tr>";
                }
                
                echo "</tbody>
                      </table>
                      </div>";
            } else {
                echo "<h3>Requisition not found.</h3>";
            }
            
        } else {
            // Show list view
            include('admin/get_rf_history.php');
            
            echo "<h3>Requisition Form History</h3>
                <div class='card rounded-4 p-4'>
                    <table class='table table-striped'>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Requester</th>
                                <th>Department</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>";
            
            if (!empty($requisitions)) {
                foreach ($requisitions as $req) {
                    echo "<tr>
                            <td>RF-{$req['PRF_ID']}</td>
                            <td>{$req['FULL_NAME']}</td>
                            <td>{$req['DEPT_NAME']}</td>
                            <td>"; echo date('F j, Y h:i A', strtotime($req['PRF_DATE'])); echo "</td>
                            <td>{$req['PRF_STATUS']}</td>
                            <td>
                                <a href='#' 
                                class='btn btn-sm btn-primary view-requisition'
                                data-content='requisition_form_history'
                                data-id='" . $req['PRF_ID'] . "'>
                                    <i class='fas fa-eye'></i>
                                </a>
                            </td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='6' class='text-center'>No requisitions found</td></tr>";
            }
            
            echo "</tbody>
                </table>
                </div>";

            // Add pagination navigation
            if (isset($pagination) && $pagination['total_pages'] > 1) {
                echo "<nav aria-label='Page navigation' class='mt-4'>
                        <ul class='pagination justify-content-center'>";
                
                // Previous button
                $prevDisabled = $pagination['current_page'] <= 1 ? ' disabled' : '';
                echo "<li class='page-item{$prevDisabled}'>
                        <a class='page-link' href='?content=requisition_form_history&page=" . 
                        ($pagination['current_page'] - 1) . "'" . 
                        ($pagination['current_page'] <= 1 ? ' tabindex="-1" aria-disabled="true"' : '') . 
                        ">Previous</a>
                      </li>";
                
                // Page numbers
                for ($i = 1; $i <= $pagination['total_pages']; $i++) {
                    $active = $pagination['current_page'] == $i ? ' active' : '';
                    echo "<li class='page-item{$active}'>
                            <a class='page-link' href='?content=requisition_form_history&page={$i}'>
                                {$i}
                            </a>
                          </li>";
                }
                
                // Next button
                $nextDisabled = $pagination['current_page'] >= $pagination['total_pages'] ? ' disabled' : '';
                echo "<li class='page-item{$nextDisabled}'>
                        <a class='page-link' href='?content=requisition_form_history&page=" . 
                        ($pagination['current_page'] + 1) . "'" .
                        ($pagination['current_page'] >= $pagination['total_pages'] ? ' tabindex="-1" aria-disabled="true"' : '') . 
                        ">Next</a>
                      </li>";
                
                echo "</ul>
                    </nav>";
            }
            
            echo "</div>";
        }
    } elseif($content === 'purchase_request_history'){
        if(isset($_GET['pr_id'])){
            include('admin/get_pr_details.php');
            
            if ($poDetails) {
                echo "<div class='d-flex justify-content-between align-items-center mb-3'>
                        
                        <h3>Purchase Request Details PR-{$poDetails['PO_ID']}</h3>
                      </div>
                      <div class='card rounded-4 p-4'>
                          <div class='text-start'>
                            <a href='#' class='btn btn-link back-to-list p-0 mb-3' data-content='purchase_request_history'>
                                <i class='fas fa-arrow-left'></i> Back
                                </a>
                            </div>
                          <div class='row'>
                              <div class='col-md-4'>
                                  <p><strong>Requestor:</strong> {$poDetails['fullname']}</p>
                                  <p><strong>Date of Request:</strong> {$poDetails['PO_PR_DATE_CREATED']}</p>
                                  <p><strong>Contact No. :</strong> {$poDetails['EMP_NUMBER']}</p>
                              </div>
                              <div class='col-md-4'>
                                  <p><strong>Supplier:</strong> {$poDetails['SP_NAME']}</p>
                                  <p><strong>Address:</strong> {$poDetails['SP_ADDRESS']}</p>
                                  <p><strong>Contact:</strong> {$poDetails['SP_NUMBER']}</p>
                              </div>";
                              
                              if($poDetails['PO_STATUS'] === 'rejected'){
                                echo "<div class='col-md-4'>
                                        <p><strong>Rejected Date:</strong> "; echo date('F j, Y h:i A', strtotime($poDetails['ap_date'])); echo "</p>
                                        <p><strong>Status:</strong> {$poDetails['PO_STATUS']}</p>
                                        <p><strong>Rejected By:</strong> {$poDetails['approvedby']}</p>
                                      </div>";
                              }elseif($poDetails['PO_STATUS'] === 'approved'){
                                echo "<div class='col-md-4'>
                                        <p><strong>Approved Date:</strong> "; echo date('F j, Y h:i A', strtotime($poDetails['ap_date'])); echo "</p>
                                        <p><strong>Status:</strong> {$poDetails['PO_STATUS']}</p>
                                        <p><strong>Approved By:</strong> {$poDetails['approvedby']}</p>
                                      </div>";
                              }else{
                                echo "<div class='col-md-4'>
                                        <p><strong>Status:</strong> {$poDetails['PO_STATUS']}</p>
                                      </div>";
                              }

                          echo "
                          
                          
                          </div>";

                          if($poDetails['PO_STATUS'] === 'rejected'){
                            echo "<div class='alert alert-danger'>
                                    <strong>Rejection Reason:</strong> " . htmlspecialchars($poDetails['ap_desc']) . "
                                  </div>";
                          }
                          
                          echo "<h4>Items</h4>
                          <table class='table'>
                              <thead>
                                  <tr>
                                      <th>Item</th>
                                      <th>Quantity</th>
                                      <th>Price</th>
                                      <th>Total</th>
                                  </tr>
                              </thead>
                              <tbody>";
                      
                $grandTotal = 0;
                foreach ($poDetails['items'] as $item) {
                    $total = $item['POL_QUANTITY'] * $item['POL_PRICE'];
                    $grandTotal += $total;
                    echo "<tr>
                            <td>{$item['INV_MODEL_NAME']} ({$item['INV_BRAND']})</td>
                            <td>{$item['POL_QUANTITY']}</td>
                            <td>₱" . number_format($item['POL_PRICE'], 2) . "</td>
                            <td>₱" . number_format($total, 2) . "</td>
                          </tr>";
                }
                
                echo "<tr>
                        <td colspan='3' class='text-end'><strong>Grand Total:</strong></td>
                        <td><strong>₱" . number_format($grandTotal, 2) . "</strong></td>
                      </tr>
                      </tbody>
                      </table>
                      </div>";
            } else {
                echo "<h3>Purchase request not found.</h3>";
            }
        } else {
            include('admin/get_pr_history.php');
            
            echo "<h3>Purchase Request History</h3>
                  <div class='card rounded-4 p-4'>
                      <table class='table table-striped'>
                          <thead>
                              <tr>
                                  <th>ID</th>
                                  <th>Requester</th>
                                  <th>Supplier</th>
                                  <th>Date of Request</th>
                                  <th>Status</th>
                                  <th>Action</th>
                              </tr>
                          </thead>
                          <tbody>";
        
            if (!empty($purchase_requests)) {
                foreach ($purchase_requests as $pr) {
                    echo "<tr>
                            <td>PR-{$pr['PO_ID']}</td>
                            <td>{$pr['fullname']}</td>
                            <td>{$pr['SP_NAME']}</td>
                            <td>" . date('F d, Y', strtotime($pr['PO_PR_DATE_CREATED'])) . "</td>
                            <td>{$pr['PO_STATUS']}</td>
                            <td>
                                <a href='#' 
                                class='btn btn-sm btn-primary view-purchase-request'
                                data-content='purchase_request_history'
                                data-id='" . $pr['PO_ID'] . "'>
                                    <i class='fas fa-eye'></i>
                                </a>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5' class='text-center'>No purchase requests found</td></tr>";
            }
            
            echo "</tbody>
                  </table>";

            // Pagination
            if (isset($pagination) && $pagination['total_pages'] > 1) {
                echo "<nav aria-label='Page navigation' class='mt-4'>
                        <ul class='pagination justify-content-center'>";
                
                // Previous button
                $prevDisabled = $pagination['current_page'] <= 1 ? ' disabled' : '';
                echo "<li class='page-item{$prevDisabled}'>
                        <a class='page-link' href='?content=purchase_request_history&page=" . 
                        ($pagination['current_page'] - 1) . "'" . 
                        ($pagination['current_page'] <= 1 ? ' tabindex="-1" aria-disabled="true"' : '') . 
                        ">Previous</a>
                      </li>";
                
                // Page numbers
                for ($i = 1; $i <= $pagination['total_pages']; $i++) {
                    $active = $pagination['current_page'] == $i ? ' active' : '';
                    echo "<li class='page-item{$active}'>
                            <a class='page-link' href='?content=purchase_request_history&page={$i}'>
                                {$i}
                            </a>
                          </li>";
                }
                
                // Next button
                $nextDisabled = $pagination['current_page'] >= $pagination['total_pages'] ? ' disabled' : '';
                echo "<li class='page-item{$nextDisabled}'>
                        <a class='page-link' href='?content=purchase_request_history&page=" . 
                        ($pagination['current_page'] + 1) . "'" .
                        ($pagination['current_page'] >= $pagination['total_pages'] ? ' tabindex="-1" aria-disabled="true"' : '') . 
                        ">Next</a>
                      </li>";
                
                echo "</ul>
                    </nav>";
            }
            
            echo "</div>";
        }
    } elseif($content === 'purchase_order_history'){
        if(isset($_GET['po_id'])) {
            // Show detailed view for specific PO
            include('admin/get_po_order_details.php');
            
            if ($poDetails) {
                echo "<div class='d-flex justify-content-between align-items-center mb-3'>
                        <h3>Purchase Order Details PO-{$poDetails['PO_ID']}</h3>
                      </div>
                      <div class='card rounded-4 p-4'>
                          <div class='text-start'>
                                <a href='#' class='btn btn-link back-to-list p-0 mb-3' data-content='purchase_order_history'>
                                    <i class='fas fa-arrow-left'></i> Back
                                </a>
                            </div>
                          <div class='row'>
                            <div class='col-md-6'>
                                <h3>MOONLIGHT</h3>
                                <p class='mb-0'>Address: Logarta St 6014 Mandaue City, Philippines</p>
                                <p class='mb-0'>Contact No: 09123456789</p>
                            </div>
                            <div class='col-md-6'>
                                <h3>PURCHASE ORDER</h3>
                                <p class='mb-0'>Date: "; echo date('F j, Y', strtotime($poDetails['ap_date'])); echo "</p>
                                <p class='mb-0'>PO-{$poDetails['PO_ID']}</p>
                            </div>
                        </div>
                        <div class='row mb-3 mt-3'>
                            <div class='col-md-6'>
                                <h4>Supplier Details</h4>
                                <p class='mb-0'><strong>Supplier Name:</strong> {$poDetails['SP_NAME']}</p>
                                <p class='mb-0'><strong>Contact no:</strong> {$poDetails['SP_NUMBER']}</p>
                                <p class='mb-0'><strong>Address:</strong> {$poDetails['SP_ADDRESS']}</p>
                            </div>
                            <div class='col-md-6'>
                            <h4>SHIP TO</h4>
                            <p class='mb-0'><strong>Name:</strong> {$poDetails['deliverTo']}</p>
                            <p class='mb-0'><strong>Company:</strong> Moonlight</p>
                            <p class='mb-0'><strong>Address:</strong> Logarta St 6014 Mandaue City, Philippines</p>
                            <p class='mb-0'><strong>Contact No:</strong> {$poDetails['EMP_NUMBER']}</p>
                            </div>
                        </div>
                        
                        <div class='order-details'>
                            <div class='row'>
                                <div class='col-md-6'>";
                                    if($poDetails['PO_ORDER_DATE']){
                                        echo "<p class='mb-0'><strong>Order Date:</strong> " . date('F d, Y', strtotime($poDetails['PO_ORDER_DATE'])) . "</p>";
                                    }
                                    if($poDetails['PO_ARRIVAL_DATE']){
                                        echo "<p class='mb-0'><strong>Expected Arrival Date:</strong> " . date('F d, Y', strtotime($poDetails['PO_ARRIVAL_DATE'])) . "</p>";
                                    }
                                echo "
                                </div>
                                <div class='col-md-6'>";
                                    if($poDetails['PD_PAYMENT_TYPE']){
                                        echo "<p class='mb-0'><strong>Payment Check:</strong> {$poDetails['PD_PAYMENT_TYPE']}</p>";
                                    }
                                    if($poDetails['PD_AMMOUNT']){
                                        echo "<p class='mb-0'><strong>Amount:</strong> ₱" . number_format($poDetails['PD_AMMOUNT'], 2) . "</p>";
                                    }
                                    if($poDetails['PD_CHANGE']){
                                        echo "<p class='mb-0'><strong>Amount:</strong> ₱" . number_format($poDetails['PD_CHANGE'], 2) . "</p>";
                                    }
                                echo "</div>";                              
                        echo "
                            </div>
                        </div>";
                          
                        echo "<h4 class='mt-3'>Items</h4>
                          <table class='table'>
                              <thead>
                                  <tr>
                                      <th>Item</th>
                                      <th>Quantity</th>
                                      <th>Price</th>
                                      <th>Total</th>
                                  </tr>
                              </thead>
                              <tbody>";
                  
            $grandTotal = 0;
            foreach ($poDetails['items'] as $item) {
                $total = $item['POL_QUANTITY'] * $item['POL_PRICE'];
                $grandTotal += $total;
                echo "<tr>
                        <td>{$item['INV_MODEL_NAME']} ({$item['INV_BRAND']})</td>
                        <td>{$item['POL_QUANTITY']}</td>
                        <td>₱" . number_format($item['POL_PRICE'], 2) . "</td>
                        <td>₱" . number_format($total, 2) . "</td>
                      </tr>";
            }
            
            echo "<tr>
                    <td colspan='3' class='text-end'><strong>Grand Total:</strong></td>
                    <td><strong>₱" . number_format($grandTotal, 2) . "</strong></td>
                  </tr>
                  </tbody>
                  </table>
                  </div>";
            } else {
                echo "<h3>Purchase order not found.</h3>";
            }
        } else {
            // Show list view
            include('admin/get_po_history.php');
            
            echo "<h3>Purchase Order History</h3>
                  <div class='card rounded-4 p-4'>
                      <table class='table table-striped'>
                          <thead>
                              <tr>
                                  <th>ID</th>
                                  <th>Supplier</th>
                                  <th>Date of Issue</th>
                                  <th>Action</th>
                              </tr>
                          </thead>
                          <tbody>";
    
            if (!empty($purchase_orders)) {
                foreach ($purchase_orders as $po) {
                    echo "<tr>
                            <td>PO-{$po['PO_ID']}</td>
                            <td>{$po['SP_NAME']}</td>
                            <td>{$po['ap_date']}</td>
                            <td>
                                <a href='#' 
                                class='btn btn-sm btn-primary view-purchase-order'
                                data-content='purchase_order_history'
                                data-id='" . $po['PO_ID'] . "'>
                                    <i class='fas fa-eye'></i>
                                </a>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5' class='text-center'>No approved purchase orders found</td></tr>";
            }
            
            echo "</tbody>
                  </table>";

            // Pagination
            if (isset($pagination) && $pagination['total_pages'] > 1) {
                echo "<nav aria-label='Page navigation' class='mt-4'>
                        <ul class='pagination justify-content-center'>";
                
                // Previous button
                $prevDisabled = $pagination['current_page'] <= 1 ? ' disabled' : '';
                echo "<li class='page-item{$prevDisabled}'>
                        <a class='page-link' href='?content=purchase_order_history&page=" . 
                        ($pagination['current_page'] - 1) . "'" . 
                        ($pagination['current_page'] <= 1 ? ' tabindex="-1" aria-disabled="true"' : '') . 
                        ">Previous</a>
                      </li>";
                
                // Page numbers
                for ($i = 1; $i <= $pagination['total_pages']; $i++) {
                    $active = $pagination['current_page'] == $i ? ' active' : '';
                    echo "<li class='page-item{$active}'>
                            <a class='page-link' href='?content=purchase_order_history&page={$i}'>
                                {$i}
                            </a>
                          </li>";
                }
                
                // Next button
                $nextDisabled = $pagination['current_page'] >= $pagination['total_pages'] ? ' disabled' : '';
                echo "<li class='page-item{$nextDisabled}'>
                        <a class='page-link' href='?content=purchase_order_history&page=" . 
                        ($pagination['current_page'] + 1) . "'" .
                        ($pagination['current_page'] >= $pagination['total_pages'] ? ' tabindex="-1" aria-disabled="true"' : '') . 
                        ">Next</a>
                      </li>";
                
                echo "</ul>
                    </nav>";
            }
            
            echo "</div>";
        }
    } else {
        echo "<h3>You do not have access to this content.</h3>";
    }
}
else {
    echo "<h3>Content not found.</h3>";
}
?>
