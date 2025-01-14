<?php
require_once 'db.php';

// Get all suppliers
$query = "SELECT * FROM supplier WHERE SP_STATUS = '1' ORDER BY SP_ID DESC";
$result = $db->query($query);
$active_suppliers = $result->fetch_all(MYSQLI_ASSOC);

// Add query for inactive suppliers
$query = "SELECT * FROM supplier WHERE SP_STATUS = '0' ORDER BY SP_ID DESC";
$result = $db->query($query);
$inactive_suppliers = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Add FontAwesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2> </h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
            <i class="fa-solid fa-plus"></i> Add Supplier
        </button>
    </div>
    <h3>Active Suppliers</h3>
    <div class="card rounded-4 p-4">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Contact Number</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($active_suppliers as $supplier): ?>
                    <tr>
                        <td><?= htmlspecialchars($supplier['SP_NAME']) ?></td>
                        <td><?= htmlspecialchars($supplier['SP_ADDRESS']) ?></td>
                        <td><?= htmlspecialchars($supplier['SP_NUMBER']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-info view-supplier" data-id="<?= $supplier['SP_ID'] ?>" title="View">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-primary edit-supplier" data-id="<?= $supplier['SP_ID'] ?>" title="Edit">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button class="btn btn-sm btn-warning deactivate-supplier" data-id="<?= $supplier['SP_ID'] ?>" title="Deactivate">
                                <i class="fa-solid fa-ban"></i>
                            </button>
                            <button class="btn btn-sm btn-danger hard-delete-supplier" data-id="<?= $supplier['SP_ID'] ?>" title="Delete Permanently">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        <h3>Inactive Suppliers</h3>
        <div class="card rounded-4 p-4">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Contact Number</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inactive_suppliers as $supplier): ?>
                        <tr>
                            <td><?= htmlspecialchars($supplier['SP_NAME']) ?></td>
                            <td><?= htmlspecialchars($supplier['SP_ADDRESS']) ?></td>
                            <td><?= htmlspecialchars($supplier['SP_NUMBER']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-success activate-supplier" data-id="<?= $supplier['SP_ID'] ?>" title="Activate">
                                    <i class="fa-solid fa-check"></i>
                                </button>
                                <button class="btn btn-sm btn-info view-supplier" data-id="<?= $supplier['SP_ID'] ?>" title="View">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addSupplierForm">
                    <div class="mb-3">
                        <label class="form-label">Supplier Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control" name="address" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Number</label>
                        <input type="text" class="form-control" name="contact" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Products Supplied</label>
                        <div id="productsContainer">
                            <div class="product-entry mb-2">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="products[]" placeholder="Product name" required>
                                    <textarea class="form-control" name="descriptions[]" placeholder="Description"></textarea>
                                    <button type="button" class="btn btn-danger remove-product">×</button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm mt-2" id="addProduct">
                            Add Another Product
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveSupplier">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- View Supplier Modal -->
<div class="modal fade" id="viewSupplierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Supplier Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="supplier-info">
                    <p><strong>Name:</strong> <span id="view-name"></span></p>
                    <p><strong>Address:</strong> <span id="view-address"></span></p>
                    <p><strong>Contact:</strong> <span id="view-contact"></span></p>
                </div>
                <div class="supplier-products mt-4">
                    <h6>Products Supplied</h6>
                    <div id="view-products"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Supplier Modal -->
<div class="modal fade" id="editSupplierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editSupplierForm">
                    <input type="hidden" name="supplier_id" id="edit-supplier-id">
                    <div class="mb-3">
                        <label class="form-label">Supplier Name</label>
                        <input type="text" class="form-control" name="name" id="edit-name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control" name="address" id="edit-address" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Number</label>
                        <input type="text" class="form-control" name="contact" id="edit-contact" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Products Supplied</label>
                        <div id="editProductsContainer"></div>
                        <button type="button" class="btn btn-secondary btn-sm mt-2" id="editAddProduct">
                            Add Another Product
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="updateSupplier">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {

    // Add new supplier
    $('#saveSupplier').click(function() {
        // Validate required fields
        const name = $('[name="name"]').val().trim();
        const address = $('[name="address"]').val().trim();
        const contact = $('[name="contact"]').val().trim();
        const products = $('[name="products[]"]').map(function() {
            return $(this).val().trim();
        }).get();

        // Check if any required field is empty
        if (!name) {
            alert('Please enter supplier name');
            return;
        }
        if (!address) {
            alert('Please enter supplier address');
            return;
        }
        if (!contact) {
            alert('Please enter contact number');
            return;
        }
        if (products.length === 0 || products.some(p => !p)) {
            alert('Please enter at least one product');
            return;
        }

        const formData = new FormData($('#addSupplierForm')[0]);
        
        $.ajax({
            url: 'inventory/add_supplier.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                const result = JSON.parse(response);
                if (result.success) {
                    alert('Supplier added successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            },
            error: function() {
                alert('Error adding supplier');
            }
        });
    });

    // Add new product field
    $('#addProduct').click(function() {
        const newProduct = `
            <div class="product-entry mb-2">
                <div class="input-group">
                    <input type="text" class="form-control" name="products[]" placeholder="Product name" required>
                    <textarea class="form-control" name="descriptions[]" placeholder="Description"></textarea>
                    <button type="button" class="btn btn-danger remove-product">×</button>
                </div>
            </div>`;
        $('#productsContainer').append(newProduct);
    });

    // Remove product field
    $(document).on('click', '.remove-product', function() {
        $(this).closest('.product-entry').remove();
    });

    // View supplier details
    $('.view-supplier').click(function() {
        const supplierId = $(this).data('id');
        $.get('inventory/get_supplier.php', { id: supplierId }, function(response) {
            const data = JSON.parse(response);
            $('#view-name').text(data.supplier.SP_NAME);
            $('#view-address').text(data.supplier.SP_ADDRESS);
            $('#view-contact').text(data.supplier.SP_NUMBER);
            
            // Display products
            const productsHtml = data.products.map(p => 
                `<div class="product-item mb-2">
                    <strong>${p.product_name}</strong>
                    ${p.product_description ? `<p class="mb-0">${p.product_description}</p>` : ''}
                </div>`
            ).join('');
            $('#view-products').html(productsHtml);
            
            $('#viewSupplierModal').modal('show');
        });
    });

    // Edit supplier
    $('.edit-supplier').click(function() {
        const supplierId = $(this).data('id');
        $.get('inventory/get_supplier.php', { id: supplierId }, function(response) {
            const data = JSON.parse(response);
            $('#edit-supplier-id').val(data.supplier.SP_ID);
            $('#edit-name').val(data.supplier.SP_NAME);
            $('#edit-address').val(data.supplier.SP_ADDRESS);
            $('#edit-contact').val(data.supplier.SP_NUMBER);
            
            // Display existing products
            $('#editProductsContainer').empty();
            data.products.forEach(p => {
                const productHtml = `
                    <div class="product-entry mb-2">
                        <div class="input-group">
                            <input type="text" class="form-control" name="products[]" value="${p.product_name}" required>
                            <textarea class="form-control" name="descriptions[]">${p.product_description || ''}</textarea>
                            <button type="button" class="btn btn-danger remove-product">×</button>
                        </div>
                    </div>`;
                $('#editProductsContainer').append(productHtml);
            });
            
            $('#editSupplierModal').modal('show');
        });
    });

    // Add product field in edit modal
    $('#editAddProduct').click(function() {
        const newProduct = `
            <div class="product-entry mb-2">
                <div class="input-group">
                    <input type="text" class="form-control" name="products[]" placeholder="Product name" required>
                    <textarea class="form-control" name="descriptions[]" placeholder="Description"></textarea>
                    <button type="button" class="btn btn-danger remove-product">×</button>
                </div>
            </div>`;
        $('#editProductsContainer').append(newProduct);
    });

    // Update supplier
    $('#updateSupplier').click(function() {
        const formData = new FormData($('#editSupplierForm')[0]);
        
        $.ajax({
            url: 'inventory/update_supplier.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                const result = JSON.parse(response);
                if (result.success) {
                    alert('Supplier updated successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            },
            error: function() {
                alert('Error updating supplier');
            }
        });
    });

    // Deactivate supplier
    $('.deactivate-supplier').click(function() {
        const supplierId = $(this).data('id');
        if (confirm('Are you sure you want to deactivate this supplier?')) {
            $.post('inventory/delete_supplier.php', 
                { 
                    id: supplierId,
                    delete_type: 'soft'
                }, 
                function(response) {
                    const result = JSON.parse(response);
                    if (result.success) {
                        alert('Supplier deactivated successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + result.message);
                    }
                }
            );
        }
    });

    // Hard delete supplier
    $('.hard-delete-supplier').click(function() {
        const supplierId = $(this).data('id');
        if (confirm('WARNING: This will permanently delete the supplier and cannot be undone!\n\nAre you sure you want to continue?')) {
            $.post('inventory/delete_supplier.php', 
                { 
                    id: supplierId,
                    delete_type: 'hard'
                }, 
                function(response) {
                    const result = JSON.parse(response);
                    if (result.success) {
                        alert('Supplier permanently deleted!');
                        location.reload();
                    } else {
                        alert('Error: ' + result.message);
                    }
                }
            );
        }
    });

    // Activate supplier
    $('.activate-supplier').click(function() {
        const supplierId = $(this).data('id');
        if (confirm('Are you sure you want to activate this supplier?')) {
            $.post('inventory/activate_supplier.php', { id: supplierId }, function(response) {
                const result = JSON.parse(response);
                if (result.success) {
                    alert('Supplier activated successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            });
        }
    });
});
</script> 
</body>
</html> 