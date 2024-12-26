<?php
session_start();
if (!isset($_SESSION['loggedIn']) || !$_SESSION['loggedIn']) {
    echo "<h3>You are not authorized to view this content.</h3>";
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
    <style>
        body {
            display: flex;
            height: 100vh;
            overflow: hidden;
            background-color: #343a40; /* Dark background */
        }
        #sidebar {
            width: 250px;
            background-color: #212529; /* Darker sidebar */
            padding: 15px;
            border-right: 1px solid #495057; /* Lighter border */
            color: #ffffff; /* White text */
            display: flex;
            flex-direction: column;
            height: 100vh; /* Full height */
        }
        #content {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
            color: #ffffff; /* White text for content */
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
            padding: 10px;
            transition: 0.3s;
        }
        .tabs h4, .tabs p {
            color: #ffffff; /* White text for headings and paragraphs */
            font-size: 13px;
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
                left: -250px; /* Hide sidebar by default on mobile */
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
        <h4>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h4>
        <p>Department: <strong><?= htmlspecialchars($_SESSION['department']) ?></strong></p>
        <p>Role: <strong><?= htmlspecialchars($_SESSION['role']) ?></strong></p>

                <!-- Role-based sidebar links -->
        <!-- Inside the sidebar section -->
        <?php if ($department == 'Finance'): ?>
            <?php if ($role == 'Manager'): ?>
                <a href="#" class="sidebar-link" id="purchase-order-link">Purchase Order</a>
                <a href="#" class="sidebar-link" id="requisition-approval-link">Requisition Approval</a>
                <a href="#" class="sidebar-link" id="requisition-form-link">Requisition Form</a>
                <a href="#" class="sidebar-link" id="requisition-history-link">Requisition History</a>
                <a href="logout.php" class="sidebar-link">Logout</a>  
            <?php elseif ($role == 'Staff'): ?>
                <a href="#" class="sidebar-link" id="requisition-form-link">Requisition Form</a>
                <a href="#" class="sidebar-link" id="requisition-history-link">Requisition History</a>
                <a href="logout.php" class="sidebar-link">Logout</a>    
            <?php endif; ?>
        <?php elseif ($department == 'Inventory'): ?>
            <?php if ($role == 'Manager'): ?>
                <a href="#" class="sidebar-link" id="withdrawal-deposit-link">Withdrawal & Deposit</a>
                <a href="#" class="sidebar-link" id="purchase-request-link">Purchase Request</a>
                <a href="#" class="sidebar-link" id="requisition-form-link">Requisition Form</a>
                <a href="#" class="sidebar-link" id="requisition-history-link">Requisition History</a> 
                <a href="logout.php" class="sidebar-link">Logout</a>   
            <?php elseif ($role == 'Staff'): ?>
                <a href="#" class="sidebar-link" id="requisition-form-link">Requisition Form</a>
                <a href="#" class="sidebar-link" id="requisition-history-link">Requisition History</a>  
                <a href="logout.php" class="sidebar-link">Logout</a>  
            <?php endif; ?>
        <?php elseif ($department == 'Labor'): ?>
            <?php if ($role == 'Manager'): ?>
                <a href="#" class="sidebar-link" id="requisition-approval-link">Requisition Approval</a>
                <a href="#" class="sidebar-link" id="requisition-form-link">Requisition Form</a>
                <a href="#" class="sidebar-link" id="requisition-history-link">Requisition History</a>
                <a href="logout.php" class="sidebar-link">Logout</a>  
            <?php elseif ($role == 'Staff'): ?>
                <a href="#" class="sidebar-link" id="requisition-form-link">Requisition Form</a>
                <a href="#" class="sidebar-link" id="requisition-history-link">Requisition History</a>
                <a href="logout.php" class="sidebar-link">Logout</a>  
            <?php endif; ?>
        <?php endif; ?>

        <a href="logout.php" class="sidebar-link text-danger">Logout</a>
    </div>

    <div id="content">
        <h3>Welcome to your dashboard!</h3>
        <p>Select an option from the sidebar to proceed.</p>
    </div>

    <script>
        $(document).ready(function () {
            function loadContent(content) {
                $.get('load_content.php', { content: content }, function (response) {
                    $('#content').html(response);
                });
            }

            // Sidebar link actions to load the respective content
            $('#purchase-order-link').click(function (e) {
                e.preventDefault();
                loadContent('purchase_order');
            });

            $('#requisition-approval-link').click(function (e) {
                e.preventDefault();
                loadContent('requisition_approval');
            });

            $('#withdrawal-deposit-link').click(function (e) {
                e.preventDefault();
                loadContent('withdrawal_deposit');
            });

            $('#purchase-request-link').click(function (e) {
                e.preventDefault();
                loadContent('purchase_request');
            });

            // Requisition form link
            $('#requisition-form-link').click(function (e) {
                e.preventDefault();
                loadContent('requisition_form');
            });
            $('#requisition-history-link').click(function (e) {
                e.preventDefault();
                loadContent('requisition_history');
            });
        });
    </script>
</body>
</html>
