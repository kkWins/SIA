<?php
session_start();
if (!isset($_SESSION['loggedIn']) || !$_SESSION['loggedIn']) {
    header('Location: login.php');
    exit;
}

$content = $_GET['content'] ?? '';
$role = $_SESSION['role'];
$department = $_SESSION['department'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            display: flex;
            height: 100vh;
            overflow: hidden;
            background-color: #fefefe; /* Dark background */
        }
        #sidebar {
            width: 280px;
            background-color: #212529; /* Darker sidebar */
            padding: 15px;
            border-right: 1px solid #495057; /* Lighter border */
            color: #ffffff; /* White text */
            display: flex;
            flex-direction: column;
            height: 100vh; /* Full height */
        }
        #itemsModal{
            color: #1C1C1C;
        }
        #content {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
            color: #000000; /* White text for content */
        }
        .sidebar-link {
            display: block;
            margin: 10px 0;
            text-decoration: none;
            color: #ffffff; /* White text */
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .sidebar-link:hover {
            background-color: #495057; /* Darker background on hover */
            transition: 0.3s;
        }
        .tabs h4{
            color: #ffffff; /* White text for headings and paragraphs */
            font-size: 20px;
        }
        .tabs p {
            color: #ffffff; /* White text for headings and paragraphs */
            font-size: 13px;
            margin-bottom: 15px;
        }

        /* New styles */
        #sidebar h4, #sidebar p {
            margin: 0;
        }

        #sidebar > div:first-child {
            padding: 20px;
            margin-top: 10px;
        }

        .text-danger {
            color: #dc3545 !important;
        }

        /* Style for active/selected menu item */
        .sidebar-link.active {
            background-color: #2C2C2C;
        }

        /* Add bottom border to user info section */
        #user-info {
            border-bottom: 1px solid #2C2C2C;
            margin-bottom: 10px;
        }

        /* User Profile Dropdown Styles */
        .user-dropdown {
            position: relative;
            padding: 12px;
            cursor: pointer;
            margin-top: auto; /* This pushes it to the bottom */
            border-top: 1px solid #2C2C2C;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px;
        }

        .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #444;
        }

        .user-info {
            flex-grow: 1;
        }

        .username {
            font-weight: 500;
            color: #fff;
        }

        .email {
            font-size: 0.85em;
            color: #888;
        }

        .dropdown-arrow {
            color: #888;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: auto;
            left: 0;
            right: 0;
            background-color: #1C1C1C;
            border: 1px solid #2C2C2C;
            border-radius: 4px;
            margin: 4px 12px;
            z-index: 1000;
            bottom: 100%;
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-item {
            display: block;
            padding: 8px 16px;
            color: #fff;
            text-decoration: none;
            transition: background-color 0.2s;
        }

        .dropdown-item:hover {
            background-color: #2C2C2C;
        }

        .dropdown-divider {
            height: 1px;
            background-color: #2C2C2C;
            margin: 4px 0;
        }

        /* Remove the old logout link styles since it's now in the dropdown */
        .sidebar-link.text-danger {
            display: none;
        }

        /* Responsive styles */
        .navbar-toggler {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            padding: 10px;
            cursor: pointer;
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 1000;
        }

        /* Media query for mobile devices */
        @media (max-width: 768px) {
            body {
                position: relative;
            }

            .navbar-toggler {
                display: block;
            }

            #sidebar {
                position: fixed;
                left: -280px; /* Hide sidebar by default on mobile */
                top: 0;
                bottom: 0;
                transition: left 0.3s ease;
                z-index: 999;
            }

            #sidebar.active {
                left: 0; /* Show sidebar when active */
            }

            #content {
                margin-left: 0;
                width: 100%;
                padding-top: 60px; /* Make room for the toggle button */
            }

            /* Add overlay when sidebar is active */
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 998;
            }

            .sidebar-overlay.active {
                display: block;
            }
        }

        .tabs {
            border-bottom: 1px solid #2C2C2C;
            padding-bottom: 15px;
        }
        </style>

</head>
<body>
    <div id="sidebar">
        <div class="tabs">
            <h4>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h4>
            <p>Department: <strong><?= htmlspecialchars($_SESSION['department']) ?></strong></p>
            <p>Role: <strong><?= htmlspecialchars($_SESSION['role']) ?></strong></p>

                    <!-- Role-based sidebar links -->
            <!-- Inside the sidebar section -->
            <?php if ($department == 'Finance'): ?>
                <?php if ($role == 'Manager'): ?>
                    <a href="#" class="sidebar-link" id="purchase-order-link">Purchase Order</a>
                    <a href="#" class="sidebar-link" id="pending-pr-link">Pending PR</a>
                    <a href="#" class="sidebar-link" id="requisition-approval-link">Requisition Approval</a>
                    <a href="#" class="sidebar-link" id="requisition-form-link">Requisition Form</a>
                    <a href="#" class="sidebar-link" id="requisition-history-link">Requisition History</a>

                <?php elseif ($role == 'Staff'): ?>
                    <a href="#" class="sidebar-link" id="requisition-form-link">Requisition Form</a>
                    <a href="#" class="sidebar-link" id="requisition-history-link">Requisition History</a>
    
                <?php endif; ?>
            <?php elseif ($department == 'Inventory'): ?>
                <?php if ($role == 'Manager'): ?>
                    <a href="#" class="sidebar-link" id="withdrawal-deposit-link">Withdrawal & Deposit</a>
                    <a href="#" class="sidebar-link" id="withdrawal-deposit-history-link">Withdrawal & Deposit History</a>
                    <a href="#" class="sidebar-link" id="requisition-approval-link">Requisition Approval</a>
                    <a href="#" class="sidebar-link" id="approved-requisitions-link">Approved Requisitions</a>
                    <a href="#" class="sidebar-link" id="purchase-order-link">Purchase Order</a>
                    <a href="#" class="sidebar-link" id="purchase-request-link">Purchase Request</a>
                    <a href="#" class="sidebar-link" id="requisition-form-link">Requisition Form</a>
                    <a href="#" class="sidebar-link" id="requisition-history-link">Requisition History</a> 
                    <a href="#" class="sidebar-link" id="inventory-task-link">Requisition Withdrawal/Delivery Task</a>   
                    <a href="#" class="sidebar-link" id="manage-suppliers-link">Manage Suppliers</a>
                <?php elseif ($role == 'Staff'): ?>
                    <a href="#" class="sidebar-link" id="requisition-form-link">Requisition Form</a>
                    <a href="#" class="sidebar-link" id="requisition-history-link">Requisition History</a>
                    <a href="#" class="sidebar-link" id="inventory-task-link">Requisition Withdrawal/Delivery Task</a>    

                <?php endif; ?>
            <?php elseif ($department == 'Labor'): ?>
                <?php if ($role == 'Manager'): ?>
                    <a href="#" class="sidebar-link" id="requisition-approval-link">Requisition Approval</a>
                    <a href="#" class="sidebar-link" id="requisition-form-link">Requisition Form</a>
                    <a href="#" class="sidebar-link" id="requisition-history-link">Requisition History</a>

                <?php elseif ($role == 'Staff'): ?>
                    <a href="#" class="sidebar-link" id="requisition-form-link">Requisition Form</a>
                    <a href="#" class="sidebar-link" id="requisition-history-link">Requisition History</a>

                <?php endif; ?>
            <?php endif; ?>

            <!-- Role-based sidebar links -->
            <?php if ($role == 'Admin'): ?>
                <a href="#" class="sidebar-link" id="manage-employees-link">Manage Employees</a>
                <a href="#" class="sidebar-link" id="requisition-form-history-link">Requisition Form History</a>
                <a href="#" class="sidebar-link" id="purchase-request-history-link">Purchase Request History</a>
                <a href="#" class="sidebar-link" id="purchase-order-history-link">Purchase Order History</a>
            <?php endif; ?>
            
        </div>

        <div class="user-dropdown">
            <div class="user-profile" id="userProfileButton">
                <img src="icons/user.png" class="avatar" alt="User avatar">
                <div class="user-info">
                    <div class="username"><?= htmlspecialchars($_SESSION['username']) ?></div>
                    <div class="email"><?= htmlspecialchars($_SESSION['emp_email']) ?></div>
                </div>
                <span class="dropdown-arrow">▾</span>
            </div>
            
            <div class="dropdown-menu" id="userDropdownMenu">
                <a href="#" class="dropdown-item" id="account-settings-link">Account</a>
                <div class="dropdown-divider"></div>
                <a href="logout.php" class="dropdown-item text-danger">Log out</a>
            </div>
        </div>
    </div>

    <div id="content">
        <h3>Welcome to your dashboard!</h3>
        <p>Select an option from the sidebar to proceed.</p>
    </div>

    <script>
        $(document).ready(function () {
            // Get URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const content = urlParams.get('content');
            const reqId = urlParams.get('req_id');
            const prId = urlParams.get('pr_id');
            const page = urlParams.get('page');

            // If there are URL parameters, load the appropriate content
            if (content) {
                loadContent(content, null, page);
            }

            function loadContent(content, id = null, page = null, additionalParams = {}) {
                // Remove any existing page parameter from additionalParams to avoid duplication
                const { page: _, ...filteredParams } = additionalParams;
                
                const params = { content: content, ...filteredParams };
                
                // Add page parameter only if it exists
                if (page) {
                    params.page = page;
                }
                
                // Get search parameter from URL if it exists
                const urlParams = new URLSearchParams(window.location.search);
                const searchTerm = urlParams.get('search');
                if (searchTerm) {
                    params.search = searchTerm;
                }
                
                let url = `?${new URLSearchParams(params).toString()}`;
                
                if (id) {
                    if (content === 'purchase_request' || content === 'purchase_request_history') {
                        params.pr_id = id;
                        url += `&pr_id=${id}`;
                    } else if (content === 'pending_pr') {
                        params.pending_pr = id;
                        url += `&pending_pr=${id}`;
                    } else if (content === 'purchase_order' || content === 'purchase_order_history') {
                        params.po_id = id;
                        url += `&po_id=${id}`;
                    } else if (content === 'requisition_approval' || content === 'approved_requisitions') {
                        params.req_id = id;
                        url += `&req_id=${id}`;
                    } else if (content === 'inventory-task') {
                        params.req_id = id;
                        url += `&req_id=${id}`;
                    } else if (content === 'requisition_form_history') {
                        params.req_id = id;
                        url += `&req_id=${id}`;
                    }
                }

                // Update URL and load content
                history.pushState({}, '', url);
                $.get('load_content.php', params, function(response) {
                    $('#content').html(response);
                });
            }

            // Handle pagination clicks
            $(document).on('click', '.pagination .page-link', function(e) {
                e.preventDefault();
                const href = $(this).attr('href');
                const urlParams = new URLSearchParams(href.split('?')[1]);
                
                // Get all parameters from the clicked link
                const params = {};
                for (const [key, value] of urlParams) {
                    params[key] = value;
                }
                
                // Load content with all parameters
                loadContent(params.content, null, params.page, params);
            });

            // Sidebar link actions to load the respective content
            // Now each main tab click will load content without any additional parameters
            $('#purchase-order-link').click(function (e) {
                e.preventDefault();
                loadContent('purchase_order');
            });

            $('#withdrawal-deposit-history-link').click(function (e) {
                e.preventDefault();
                loadContent('withdrawal_deposit_history');
            });

            $('#pending-pr-link').click(function (e) {
                e.preventDefault();
                loadContent('pending_pr');
            });

            $('#requisition-form-link').click(function (e) {
                e.preventDefault();
                loadContent('requisition_form');
            });

            $('#inventory-task-link').click(function (e) {
                e.preventDefault();
                loadContent('inventory-task');
            });
           

            $('#requisition-approval-link').click(function (e) {
                e.preventDefault();
                loadContent('requisition_approval');
            });

            $('#approved-requisitions-link').click(function (e) {
                e.preventDefault();
                loadContent('approved_requisitions');
            });

            $('#withdrawal-deposit-link').click(function (e) {
                e.preventDefault();
                loadContent('withdrawal_deposit');
            });

            $('#purchase-request-link').click(function (e) {
                e.preventDefault();
                loadContent('purchase_request');
            });


            $('#requisition-history-link').click(function (e) {
                e.preventDefault();
                loadContent('requisition_history');
            });

            $('#account-settings-link').click(function(e) {
                e.preventDefault();
                loadContent('account_settings');
            });

            // Add this with the other sidebar link handlers
            $('#manage-employees-link').click(function (e) {
                e.preventDefault();
                loadContent('manage_employees');
            });

            $('#requisition-form-history-link').click(function (e) {
                e.preventDefault();
                loadContent('requisition_form_history');
            });

            $('#purchase-request-history-link').click(function (e) {
                e.preventDefault();
                loadContent('purchase_request_history');
            });

            $('#purchase-order-history-link').click(function (e) {
                e.preventDefault();
                loadContent('purchase_order_history');
            });

            $('#manage-suppliers-link').click(function (e) {
                e.preventDefault();
                loadContent('manage_suppliers');
            });

            $(document).on('loadContentEvent', function(e, content, id, page) {
                loadContent(content, id, page);
            });

            // For handling detail view clicks (e.g., from a table row)
            $(document).on('click', '.view-requisition', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                const contentType = $(this).data('content');
                loadContent(contentType, id);
            });

            $(document).on('click', '.view-purchase-request', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                const contentType = $(this).data('content');
                loadContent(contentType, id);
            });
            
            $(document).on('click', '.view-purchase-order', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                const contentType = $(this).data('content');
                loadContent(contentType, id);
            });
            
            $(document).on('click', '.view-wd', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                const contentType = $(this).data('content');
                loadContent(contentType, id);
            });

            // Add this with your other document.ready event handlers
            $(document).on('click', '.back-to-list', function(e) {
                e.preventDefault();
                const contentType = $(this).data('content');
                loadContent(contentType);
            });

            // Add this to your existing JavaScript
            $('#userProfileButton').click(function(e) {
                e.stopPropagation();
                $('#userDropdownMenu').toggleClass('show');
            });

            // Close dropdown when clicking outside
            $(document).click(function(e) {
                if (!$(e.target).closest('.user-dropdown').length) {
                    $('#userDropdownMenu').removeClass('show');
                }
            });

            // Add overlay div to body
            $('body').append('<div class="sidebar-overlay"></div>');

            // Toggle sidebar
            $('#sidebarToggle').click(function(e) {
                e.preventDefault();
                $('#sidebar').toggleClass('active');
                $('.sidebar-overlay').toggleClass('active');
            });

            // Close sidebar when clicking overlay
            $('.sidebar-overlay').click(function() {
                $('#sidebar').removeClass('active');
                $('.sidebar-overlay').removeClass('active');
            });

            // Close sidebar when clicking a link (for mobile)
            $('.sidebar-link').click(function() {
                if (window.innerWidth <= 768) {
                    $('#sidebar').removeClass('active');
                    $('.sidebar-overlay').removeClass('active');
                }
            });


            // Handle window resize
            $(window).resize(function() {
                if (window.innerWidth > 768) {
                    $('#sidebar').removeClass('active');
                    $('.sidebar-overlay').removeClass('active');
                }
            });

            

        });
    </script>
</body>
</html>

