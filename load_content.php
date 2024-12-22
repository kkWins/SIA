<?php
session_start();
if (!isset($_SESSION['loggedIn']) || !$_SESSION['loggedIn']) {
    echo "<h3>You are not authorized to view this content.</h3>";
    exit;
}

$content = $_GET['content'] ?? '';
$role = $_SESSION['role'];
$department = $_SESSION['department'];

$conca = $department." ".$role;

if ($content === 'purchase_order' ) {
    if ($conca === 'Finance Manager') {
        echo "<h2>Purchase Order</h2>
              <p>Manage your purchase orders here.</p>";
    } else {
        echo "<h3>You do not have access to this content.</h3>";
    }
} elseif ($content === 'requisition_approval') {
    echo "<h2>Requisition Approval</h2>
          <p>Approve requisitions here.</p>";
} elseif ($content === 'withdrawal_deposit') {
    if ($conca === 'Inventory Manager') {
        echo "<h2>Withdrawal & Deposit</h2>
              <p>Manage withdrawals and deposits here.</p>";
    } else {
        echo "<h3>You do not have access to this content.</h3>";
    }
} elseif ($content === 'purchase_request') {
    if ($conca === 'Inventory Manager') {
        echo "<h2>Purchase Request</h2>
              <p>Manage purchase requests here.</p>";
    } else {
        echo "<h3>You do not have access to this content.</h3>";
    }
} elseif ($content === 'requisition_form') {

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
                        <select id=\"item\" name=\"item\" class=\"form-select\">
                            <option value=\"\">Loading items...</option>
                        </select>

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
                                    console.log( item.name); 
                                    \$dropdown.append('<option value=\"' + item.name + '\">' + item.name + '</option>');
                                
                                });
                            } else {
                            }
                        },
                        error: function() {
                            alert('Error fetching inventory items. Please try again.');
                            $('#item').append('<option value=\"\">Error loading items</option>');
                        }
                    });
                });
            </script>

                    
                    
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
                        <button type='button' class='close' data-dismiss='modal' aria-label='Close'>
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
                        <button type='button' class='btn btn-secondary' id='close-modal' data-dismiss='modal'>Close</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            $(document).ready(function() {
                // Add row to table dynamically
                $('#add-row').click(function() {
                    var item = $('#item').val();
                    var quantity = $('#quantity').val();
                    var reason = $('#reason').val();

                    if (item && quantity && reason) {
                        var newRow = '<tr>';
                        newRow += '<td>' + item + '</td>';
                        newRow += '<td>' + quantity + '</td>';
                        newRow += '<td>' + reason + '</td>';
                        newRow += '<td><button class=\"btn btn-danger btn-sm delete-row\">Delete</button></td>';
                        newRow += '</tr>';

                        // Append the new row to the added items table
                        $('#added-items').append(newRow);

                        // Remove the item from the dropdown
                        $('#item option[value=\"' + item + '\"]').remove();

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
                    // Get the item that is being removed
                    var itemToRemove = $(this).closest('tr').find('td:first').text();

                    // Add the removed item back to the dropdown
                    $('#item').append('<option value=\"' + itemToRemove + '\">' + itemToRemove + '</option>');

                    // Remove the row from the table
                    $(this).closest('tr').remove();
                });

                // Submit the requisition form
                $('#submit-form').click(function() {
                    var items = [];
                    var quantities = [];
                    var reasons = [];

                    // Iterate through each row in the added items table
                    $('#added-items tr').each(function() {
                        var item = $(this).find('td').eq(0).text(); // Item name
                        var quantity = $(this).find('td').eq(1).text(); // Quantity
                        var reason = $(this).find('td').eq(2).text(); // Reason

                        items.push(item);
                        quantities.push(quantity);
                        reasons.push(reason);
                    });
                    console.log({
    items: items[0],
    quantities: quantities[0],
    reasons: reasons[0]
});
                    // Check if there are items to submit
                    if (items.length > 0) {
                        // Send data to the server (authenticate, process, or save requisition data)
                        $.ajax({
                            url: 'submit_requisition.php', // Backend script to process the data
                            type: 'POST',
                            data: {
                                items: items,
                                quantities: quantities,
                                reasons: reasons
                            },
                            success: function(response) {
                            console.log('Server response:', response);
                                if (response == 1) {
                                    // Populate the modal with the items selected
                                    $('#itemsList').empty(); // Clear the existing items in the modal
                                    for (var i = 0; i < items.length; i++) {
                                        var itemDetail = items[i] + ' (Qty: ' + quantities[i] + ', Reason: ' + reasons[i] + ')';
                                        $('#itemsList').append('<li>' + itemDetail + '</li>');
                                    }

                                    // Show the modal with the items
                                    $('#itemsModal').modal('show');
                                } else {
                                    alert('Error occurred during submission. Please try again123.');
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

                // Close the modal and reset form when the modal close button is clicked
                $('#close-modal').click(function() {
                    $('#itemsModal').modal('hide');  // This will hide the modal
                });

                // Reset the form when the modal is closed
                $('#itemsModal').on('hidden.bs.modal', function () {
                    // Clear added items and reset dropdown
                    $('#added-items').empty();
                    $('#item').val('');
                    $('#quantity').val('');
                    $('#reason').val('');

                    // Optionally, restore the dropdown items (if needed)
                    $('#item').html('<option value=\"\">Select Item</option><option value=\"Item 1\">Item 1</option><option value=\"Item 2\">Item 2</option><option value=\"Item 3\">Item 3</option><option value=\"Item 4\">Item 4</option>');
                });
            });
        </script>
    ";
    
} else {
    echo "<h3>Content not found.</h3>";
}
?>
