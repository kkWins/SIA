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
                                <button class="btn btn-sm btn-danger hard-delete-supplier" data-id="<?= $supplier['SP_ID'] ?>" title="Delete Permanently">
                                    <i class="fa-solid fa-trash-can"></i>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addSupplierForm">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="fw-bold form-label">Supplier Name</label>
                            <input type="text" class="form-control" name="name" placeholder="Enter supplier name" required>
                        </div>
                        <div class="col-md-4">
                            <label class="fw-bold form-label">Contact Number</label>
                            <input type="text" class="form-control" name="contact" placeholder="Enter contact number" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold form-label">Address</label>
                        <textarea class="form-control" name="address" placeholder="Enter complete address" rows="1" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold form-label">Products Supplied</label>
                        <div id="productsContainer">
                            <div class="product-entry card mb-3">
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-md-8">
                                            <label class="fw-bold form-label">Product 1</label>
                                            <input type="text" class="form-control" name="products[]" placeholder="Enter product name" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="fw-bold form-label">Unit Price</label>
                                            <input type="number" class="form-control" name="prices[]" placeholder="₱0.00" step="0.01" min="0" required>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="fw-bold form-label">Description</label>
                                        <textarea class="form-control" name="descriptions[]" placeholder="Enter product description" rows="2"></textarea>
                                    </div>
                                    <button type="button" class="btn btn-sm remove-product position-absolute top-0 end-0 m-3">
                                        <i class="fa-solid fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-success w-25" id="addProduct">
                            Add Another Product
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="saveSupplier">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- View Supplier Modal -->
<div class="modal fade" id="viewSupplierModal" tabindex="-1" >
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editSupplierForm">
                    <input type="hidden" name="supplier_id" id="edit-supplier-id">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="fw-bold form-label">Supplier Name</label>
                            <input type="text" class="form-control" name="name" id="edit-name" placeholder="Enter supplier name" required>
                        </div>
                        <div class="col-md-4">
                            <label class="fw-bold form-label">Contact Number</label>
                            <input type="text" class="form-control" name="contact" id="edit-contact" placeholder="Enter contact number" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold form-label">Address</label>
                        <textarea class="form-control" name="address" id="edit-address" placeholder="Enter complete address" rows="1" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold form-label">Products Supplied</label>
                        <div id="editProductsContainer"></div>
                        <button type="button" class="btn btn-success w-25" id="editAddProduct">
                            Add Another Product
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="updateSupplier">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
        const prices = $('[name="prices[]"]').map(function() {
            return $(this).val().trim();
        }).get();

        // Validation checks with more specific messages
        const errors = [];

        if (!name) {
            errors.push("Supplier name is required");
        } else if (name.length < 2) {
            errors.push("Supplier name must be at least 2 characters long");
        }

        if (!address) {
            errors.push("Supplier address is required");
        } else if (address.length < 5) {
            errors.push("Please enter a complete address");
        }

        if (!contact) {
            errors.push("Contact number is required");
        } else if (!/^[0-9+\-\s()]{7,15}$/.test(contact)) {
            errors.push("Please enter a valid contact number");
        }

        if (products.length === 0) {
            errors.push("At least one product is required");
        }

        // Validate each product and price
        products.forEach((product, index) => {
            if (!product) {
                errors.push(`Product name is required for Product ${index + 1}`);
            }
            
            const price = prices[index];
            if (!price) {
                errors.push(`Price is required for Product ${index + 1}`);
            } else if (isNaN(price) || parseFloat(price) <= 0) {
                errors.push(`Invalid price for Product ${index + 1}. Must be greater than 0`);
            }
        });

        // Show all validation errors if any
        if (errors.length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: errors.join('<br>'),
                confirmButtonText: 'OK'
            });
            return;
        }

        
        // Show loading state while checking
        Swal.fire({
            title: 'Checking...',
            text: 'Verifying supplier information',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        // First check for duplicate
        $.ajax({
            url: 'inventory/check_duplicate_supplier.php',
            type: 'POST',
            data: { name: name },
            dataType: 'json',
            success: function(response) {
                if (!response.success) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.error || 'Failed to check for duplicates',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                if (response.exists) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Duplicate Entry',
                        text: 'A supplier with this name already exists!',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                // If no duplicate, proceed with form submission
                const formData = new FormData($('#addSupplierForm')[0]);
                
                // Continue with existing save logic
                $.ajax({
                    url: 'inventory/add_supplier.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        try {
                            const result = JSON.parse(response);
                            if (result.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: 'Supplier added successfully!',
                                    confirmButtonText: 'OK'
                                }).then((result) => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: result.message || 'Failed to add supplier',
                                    confirmButtonText: 'OK'
                                });
                            }
                        } catch (e) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Invalid server response',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: `Failed to add supplier: ${error}`,
                            confirmButtonText: 'OK'
                        });
                    }
                });

            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: `Failed to check for duplicate supplier: ${error}`,
                    confirmButtonText: 'OK'
                });
            }
        });
    });

    // Function to update remove buttons state
    function updateRemoveButtons() {
        const modalId = $('.modal.show').attr('id');
        const container = modalId === 'editSupplierModal' ? '#editProductsContainer' : '#productsContainer';
        const productCount = $(container + ' .product-entry').length;
        $(container + ' .remove-product').prop('disabled', productCount === 1);
    }

    // Initialize remove buttons state
    updateRemoveButtons();

    // Update the remove product handler
    $(document).on('click', '.remove-product', function() {
        $(this).closest('.product-entry').remove();
        // Update the numbering of remaining products
        const container = $(this).closest('.modal').find('.product-entry');
        container.each(function(index) {
            $(this).find('label:first').text('Product ' + (index + 1));
        });
        updateRemoveButtons();
    });

    // Update the add product handlers
    $('#addProduct').click(function() {
        const productCount = $('.product-entry').length + 1;
        const newProduct = `
            <div class="product-entry card mb-3">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-8">
                            <label class="fw-bold form-label">Product ${productCount}</label>
                            <input type="text" class="form-control" name="products[]" placeholder="Enter product name" required>
                        </div>
                        <div class="col-md-4">
                            <label class="fw-bold form-label">Unit Price</label>
                            <input type="number" class="form-control" name="prices[]" placeholder="₱0.00" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="fw-bold form-label">Description</label>
                        <textarea class="form-control" name="descriptions[]" placeholder="Enter product description" rows="2"></textarea>
                    </div>
                    <button type="button" class="btn btn-sm remove-product position-absolute top-0 end-0 m-3">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>
            </div>`;
        $('#productsContainer').append(newProduct);
        updateRemoveButtons();
    });

    // View supplier details
    $('.view-supplier').click(function() {
        const supplierId = $(this).data('id');
        $.get('inventory/get_supplier.php', { id: supplierId }, function(response) {
            const data = JSON.parse(response);
            $('#view-name').text(data.supplier.SP_NAME);
            $('#view-address').text(data.supplier.SP_ADDRESS);
            $('#view-contact').text(data.supplier.SP_NUMBER);
            
            // Display only active products
            const productsHtml = data.products
                .filter(p => p.status === 'active')  // Filter for active products only
                .map(p => 
                    `<div class="product-item mb-2">
                        <strong>${p.product_name}</strong> - ₱${parseFloat(p.unit_price).toFixed(2)}
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
            $('#edit-name').val(data.supplier.SP_NAME)
                .data('original-name', data.supplier.SP_NAME);
            $('#edit-address').val(data.supplier.SP_ADDRESS);
            $('#edit-contact').val(data.supplier.SP_NUMBER);
            
            // Clear existing products
            $('#editProductsContainer').empty();
            
            // Active Products Section
            data.products.filter(p => p.status === 'active').forEach((p, index) => {
                const productHtml = `
                    <div class="product-entry card mb-3" data-product-id="${p.sp_prod_id}">
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-md-8">
                                    <label class="fw-bold form-label">Product ${index + 1}</label>
                                    <input type="text" class="form-control" name="products[]" value="${p.product_name}" required>
                                    <input type="hidden" name="product_ids[]" value="${p.sp_prod_id}">
                                    <input type="hidden" name="product_status[]" value="active">
                                </div>
                                <div class="col-md-4">
                                    <label class="fw-bold form-label">Unit Price</label>
                                    <input type="number" class="form-control" name="prices[]" value="${p.unit_price}" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="fw-bold form-label">Description</label>
                                <textarea class="form-control" name="descriptions[]" rows="2">${p.product_description || ''}</textarea>
                            </div>
                            <button type="button" class="btn btn-sm btn-warning deactivate-product" data-id="${p.sp_prod_id}" title="Deactivate">
                                <i class="fa-solid fa-ban"></i>
                            </button>
                        </div>
                    </div>`;
                $('#editProductsContainer').append(productHtml);
            });

            // Inactive Products Section
            if (data.products.some(p => p.status === 'inactive')) {
                $('#editProductsContainer').append('<h5 class="mt-4 mb-3">Inactive Products</h5>');
                data.products.filter(p => p.status === 'inactive').forEach((p, index) => {
                    const productHtml = `
                        <div class="product-entry card mb-3 bg-light" data-product-id="${p.sp_prod_id}">
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-md-8">
                                        <label class="fw-bold form-label">Product ${index + 1}</label>
                                        <input type="text" class="form-control" value="${p.product_name}" disabled>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="fw-bold form-label">Unit Price</label>
                                        <input type="number" class="form-control" value="${p.unit_price}" disabled>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="fw-bold form-label">Description</label>
                                    <textarea class="form-control" rows="2" disabled>${p.product_description || ''}</textarea>
                                </div>
                                <div class="text-end">
                                    <button type="button" class="btn btn-sm btn-success activate-product" data-id="${p.sp_prod_id}" title="Activate">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger delete-product" data-id="${p.sp_prod_id}" title="Delete Permanently">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </div>
                        </div>`;
                    $('#editProductsContainer').append(productHtml);
                });
            }
            
            $('#editSupplierModal').modal('show');
        });
    });

    // Add product field in edit modal
    $('#editAddProduct').click(function() {
        const productCount = $('#editProductsContainer .product-entry').length + 1;
        const newProduct = `
            <div class="product-entry card mb-3">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-8">
                            <label class="fw-bold form-label">Product ${productCount}</label>
                            <input type="text" class="form-control" name="products[]" placeholder="Enter product name" required>
                        </div>
                        <div class="col-md-4">
                            <label class="fw-bold form-label">Unit Price</label>
                            <input type="number" class="form-control" name="prices[]" placeholder="₱0.00" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="fw-bold form-label">Description</label>
                        <textarea class="form-control" name="descriptions[]" placeholder="Enter product description" rows="2"></textarea>
                    </div>
                </div>
            </div>`;
        $('#editProductsContainer').append(newProduct);
        updateRemoveButtons();
    });

    // Update supplier
    $('#updateSupplier').click(function() {
        // Get the original name for comparison
        const originalName = $('#edit-name').data('original-name');
        const newName = $('#edit-name').val().trim();

        // Validate required fields
        const address = $('#edit-address').val().trim();
        const contact = $('#edit-contact').val().trim();
        const products = $('#editSupplierForm [name="products[]"]').map(function() {
            return $(this).val().trim();
        }).get();
        const prices = $('#editSupplierForm [name="prices[]"]').map(function() {
            return $(this).val().trim();
        }).get();

        // Validation checks with specific messages
        const errors = [];

        if (!newName) {
            errors.push("Supplier name is required");
        } else if (newName.length < 2) {
            errors.push("Supplier name must be at least 2 characters long");
        }

        if (!address) {
            errors.push("Supplier address is required");
        } else if (address.length < 5) {
            errors.push("Please enter a complete address");
        }

        if (!contact) {
            errors.push("Contact number is required");
        } else if (!/^[0-9+\-\s()]{7,15}$/.test(contact)) {
            errors.push("Please enter a valid contact number");
        }

        if (products.length === 0) {
            errors.push("At least one product is required");
        }

        // Validate each product and price
        products.forEach((product, index) => {
            if (!product) {
                errors.push(`Product name is required for Product ${index + 1}`);
            }
            
            const price = prices[index];
            if (!price) {
                errors.push(`Price is required for Product ${index + 1}`);
            } else if (isNaN(price) || parseFloat(price) <= 0) {
                errors.push(`Invalid price for Product ${index + 1}. Must be greater than 0`);
            }
        });

        // Show all validation errors if any
        if (errors.length > 0) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: errors.join('<br>'),
                confirmButtonText: 'OK'
            });
            return;
        }

        // Only check for duplicates if the name has changed
        if (originalName !== newName) {
            checkDuplicateAndUpdate();
        } else {
            // Proceed directly to update if name hasn't changed
            submitUpdate();
        }
    });

    // Helper function to check duplicates and update
    function checkDuplicateAndUpdate() {
        const name = $('#edit-name').val().trim();
        const supplierId = $('#edit-supplier-id').val();

        // Show loading state while checking
        Swal.fire({
            title: 'Checking...',
            text: 'Verifying supplier information',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: 'inventory/check_duplicate_supplier.php',
            type: 'POST',
            data: { 
                name: name,
                supplier_id: supplierId
            },
            dataType: 'json',
            success: function(response) {
                if (!response.success) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.error || 'Failed to check for duplicates',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                if (response.exists) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Duplicate Entry',
                        text: 'A supplier with this name already exists!',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                submitUpdate();
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: `Failed to check for duplicate supplier: ${error}`,
                    confirmButtonText: 'OK'
                });
            }
        });
    }

    // Helper function to submit the update
    function submitUpdate() {
        const formData = new FormData($('#editSupplierForm')[0]);
        
        $.ajax({
            url: 'inventory/update_supplier.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    if (result.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Supplier updated successfully!',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: result.message || 'Failed to update supplier',
                            confirmButtonText: 'OK'
                        });
                    }
                } catch (e) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Invalid server response',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: `Failed to update supplier: ${error}`,
                    confirmButtonText: 'OK'
                });
            }
        });
    }

    // Deactivate supplier
    $('.deactivate-supplier').click(function() {
        const supplierId = $(this).data('id');
        
        // Use SweetAlert2 for confirmation
        Swal.fire({
            title: 'Deactivate Supplier?',
            text: "This supplier will be moved to inactive suppliers list. You can reactivate them later.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107', // Warning color to match the button
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, deactivate',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Deactivating...',
                    text: 'Please wait while we process your request',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Send deactivate request
                $.ajax({
                    url: 'inventory/delete_supplier.php',
                    type: 'POST',
                    data: { 
                        id: supplierId,
                        delete_type: 'soft'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deactivated!',
                                text: 'The supplier has been deactivated successfully.',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to deactivate supplier',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: `Failed to deactivate supplier: ${error}`,
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    });

    // Hard delete supplier
    $('.hard-delete-supplier').click(function() {
        const supplierId = $(this).data('id');
        
        // Use SweetAlert2 for better confirmation dialog
        Swal.fire({
            title: 'Are you absolutely sure?',
            text: "This will permanently delete the supplier and all associated products. This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete permanently!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Deleting...',
                    text: 'Please wait while we delete the supplier',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Send delete request
                $.ajax({
                    url: 'inventory/delete_supplier.php',
                    type: 'POST',
                    data: { 
                        id: supplierId,
                        delete_type: 'hard'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Supplier has been permanently deleted.',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to delete supplier',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: `Failed to delete supplier: ${error}`,
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    });

    // Activate supplier
    $('.activate-supplier').click(function() {
        const supplierId = $(this).data('id');
        
        // Use SweetAlert2 for confirmation
        Swal.fire({
            title: 'Activate Supplier?',
            text: "This supplier will be moved to active suppliers list.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754', // Success/Green color to match the button
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, activate',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Activating...',
                    text: 'Please wait while we process your request',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Send activate request
                $.ajax({
                    url: 'inventory/activate_supplier.php',
                    type: 'POST',
                    data: { id: supplierId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Activated!',
                                text: 'The supplier has been activated successfully.',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to activate supplier',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: `Failed to activate supplier: ${error}`,
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    });

    // Update the product actions handlers
    $(document).on('click', '.deactivate-product', function() {
        const productId = $(this).data('id');
        
        Swal.fire({
            title: 'Deactivate Product?',
            text: "This product will be moved to inactive products list. You can reactivate it later.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, deactivate'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'inventory/update_product_status.php',
                    type: 'POST',
                    data: { 
                        product_id: productId,
                        action: 'deactivate'
                    },
                    success: function(response) {
                        try {
                            const result = JSON.parse(response);
                            if (result.success) {
                                // Refresh the product list
                                $('.edit-supplier[data-id="' + $('#edit-supplier-id').val() + '"]').click();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: result.message || 'Failed to deactivate product'
                                });
                            }
                        } catch (e) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Invalid server response'
                            });
                        }
                    }
                });
            }
        });
    });

    $(document).on('click', '.activate-product', function() {
        const productId = $(this).data('id');
        
        Swal.fire({
            title: 'Activate Product?',
            text: "This product will be moved to active products list.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, activate'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Activating...',
                    text: 'Please wait while we process your request',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: 'inventory/update_product_status.php',
                    type: 'POST',
                    data: { 
                        product_id: productId,
                        action: 'activate'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Product Activated',
                                text: 'The product has been activated successfully.',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                // Refresh the product list
                                $('.edit-supplier[data-id="' + $('#edit-supplier-id').val() + '"]').click();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to activate product',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: `Failed to activate product: ${error}`,
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    });

    // Update the delete-product handler
    $(document).on('click', '.delete-product', function() {
        const productId = $(this).data('id');
        const productStatus = $(this).closest('.product-entry').hasClass('bg-light') ? 'inactive' : 'active';
        
        // Only allow deletion of inactive products
        if (productStatus !== 'inactive') {
            Swal.fire({
                icon: 'error',
                title: 'Cannot Delete Active Product',
                text: 'Please deactivate the product first before attempting to delete it permanently.',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        Swal.fire({
            title: 'Delete Product Permanently?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete permanently!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Deleting...',
                    text: 'Please wait while we delete the product',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: 'inventory/update_product_status.php',
                    type: 'POST',
                    data: { 
                        product_id: productId,
                        action: 'delete'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Product Deleted',
                                text: 'The product has been permanently deleted.',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                // Refresh the product list
                                $('.edit-supplier[data-id="' + $('#edit-supplier-id').val() + '"]').click();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to delete product',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: `Failed to delete product: ${error}`,
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    });
});
</script> 
</body>
</html> 