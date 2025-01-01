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
    if ($conca === 'Finance Manager') {
        echo "<h2>Purchase Order</h2>
              <p>Manage your purchase orders here.</p>";
    } else {
        echo "<h3>You do not have access to this content.</h3>";
    }
} elseif ($content === 'requisition_approval') {
    echo "<h2>Requisition Approval</h2>";

    // Check if viewing details of a specific requisition
    if (isset($_GET['req_id'])) {
        include('staff_manager_approval.php');

        // Display detailed view
        if (isset($response['items'])) {
            echo "
            <div class='card rounded-4 p-4'>
                <h3>Requisition ID: " . htmlspecialchars($_GET['req_id']) . "</h3>
                <div class='row mb-3'>
                    <div class='col-md-6'>
                        <p><strong>Name:</strong> " . htmlspecialchars($response['employee_name']) . "</p>
                        <p><strong>Contact no:</strong> " . htmlspecialchars($response['contact_no']) . "</p>
                    </div>
                    <div class='col-md-6'>
                        <p><strong>Date of Request:</strong> " . htmlspecialchars($response['date']) . "</p>
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
                    
            echo "</tbody>
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
                    alert('Requisition approved successfully!');
                    window.location.href = '?content=requisition_approval'; // Redirect without req_id
                });

                // Reject button click event
                $('.reject-btn').on('click', function() {
                    alert('Requisition rejected successfully!');
                    window.location.href = '?content=requisition_approval'; // Redirect without req_id
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
                                    <a href='#' class='btn btn-sm btn-primary view-requisition' 
                                        data-content='requisition_approval' 
                                        data-id='" . $req['requisition_id'] . "'>
                                        <i class='fas fa-eye'></i> View
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

} elseif ($content === 'withdrawal_deposit') {
    if ($conca === 'Inventory Manager') {
        echo "<h2>Withdrawal & Deposit</h2>
              <p>Manage withdrawals and deposits here.</p>";
    } else {
        echo "<h3>You do not have access to this content.</h3>";
    }
} elseif ($content === 'purchase_request') {
    include('manager_inv_pr.php');
    if ($conca === 'Inventory Manager') {
        echo "<h2>Purchase Request</h2>

                    ";


                if(isset($_GET['pr_id'])) {
                  if($response['po_details']) {
                      echo "
                      <div class='card rounded-4 p-4'>
                          <h3>Purchase Request # {$_GET['pr_id']}</h3>
                          <div class='row mb-3'>
                              <div class='col-md-6'>
                                  <p><strong>Supplier Name:</strong> {$response['po_details']['SP_NAME']}</p>
                                  <p><strong>Contact no:</strong> {$response['po_details']['SP_NUMBER']}</p>
                              </div>
                              <div class='col-md-6'>
                                  <p><strong>Date of Order:</strong> {$response['po_details']['PO_ORDER_DATE']}</p>
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
                              
                              echo "</div>";
                          } else {
                              echo 'Purchase Order not found';
                    }
                }else{
                    if(!empty($pos)){
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
                                    
                                    $('#prItems tbody tr').each(function() {
                                        const item = $(this).find('.item-select').val();
                                        if (item) {
                                            items.push({
                                                item_id: item,
                                                quantity: $(this).find('.quantity').val(),
                                                price: $(this).find('.price').val(),
                                                total: $(this).find('.total').text()
                                            });
                                        }
                                    });

                                    if (vendor && items.length > 0) {
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
                                    } else {
                                        alert('Please fill in all required fields.');
                                    }
                                });
                            });
                            </script>

                            <div class='card rounded-4 p-4'>
                                <table class='table' id='requisitions-table'>
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Supplier</th>
                                            <th>Submitted Date</th>
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
                                                <td>" . htmlspecialchars($po['PO_ID']) . "</td>
                                                <td>" . htmlspecialchars($po['SP_NAME']) . "</td>
                                                <td>" . htmlspecialchars($po['PO_ORDER_DATE']) . "</td>
                                                <td>" . htmlspecialchars($po['PO_STATUS']) . "</td>
                                                <td>
                                                    <a href='#' class='btn btn-sm btn-primary view-requisition' 
                                                        data-content='purchase_request' 
                                                        data-id='" . $po['PO_ID'] . "'>
                                                        <i class='fas fa-eye'></i> View
                                                    </a>
                                                    <a href='#' class='btn btn-sm btn-danger delete-po' 
                                                        data-content='purchase_request' 
                                                        data-id='" . $po['PO_ID'] . "'>
                                                        <i class='fas fa-eye'></i> Delete
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
                            <h5>No Purchase order yet.</h5>
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
            if($response['items']){
                echo "
                    <div class='card rounded-4 p-4'>
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
                                    <th>Quantity/Stock</th>
                                </tr>
                            </thead>
                            <tbody>";
                            
                    foreach ($response['items'] as $item) {
                        echo "<tr>
                                <td>" . htmlspecialchars($item['item_name']) . "</td>
                                <td>" . htmlspecialchars($item['description']) . "</td>
                                <td>" . htmlspecialchars($item['quantity'] . "/" . $item['stock']) . "</td>
                            </tr>";
                    }
                            
                    echo "</tbody>
                        </table>
                        
                        <div class='mt-3'>
                            <button class='btn btn-danger' id='rejectBtn' data-id='{$_GET['req_id']}'>Cancel</button>
                            <button class='btn btn-success' id='approveBtn' data-id='{$_GET['req_id']}'>Send</button>
                        </div>
                    </div>";
            }else{
                echo 'Requisition not found';
            }
            
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
                                <a href='#' class='btn btn-sm btn-primary view-requisition' 
                                    data-content='approved_requisitions' 
                                    data-id='" . $req['requisition_id'] . "'>
                                    <i class='fas fa-eye'></i> View
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
} elseif ($content === 'requisition_history') {
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

    if ($response['prf_status'] == 'Pending') {
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
        echo '<button class="deleteBtn" data-prf-id="' . $response['prf_id'] . '">Delete</button>';
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
                    var quantity = $(this).find('td').eq(1).text(); // Quantity
                    var reason = $(this).find('td').eq(2).text(); // Reason

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
}else {
    echo "<h3>Content not found.</h3>";
}
?>
