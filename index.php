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
        }
        #sidebar {
            width: 250px;
            background-color: #f8f9fa;
            padding: 15px;
            border-right: 1px solid #ddd;
        }
        #content {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
        }
        .sidebar-link {
            display: block;
            margin: 10px 0;
            padding: 10px;
            text-decoration: none;
            color: #000;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .sidebar-link:hover {
            background-color: #e9ecef;
        }
    </style>
</head>
<body>
    <div id="sidebar">
        <h4>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h4>
        <p>Department: <strong><?= htmlspecialchars($_SESSION['department']) ?></strong></p>
        <p>Role: <strong><?= htmlspecialchars($_SESSION['role']) ?></strong></p>

        <!-- Role-based sidebar links -->
        <?php if ($department == 'Finance'): ?>
            <?php if ($role == 'Manager'): ?>
                <a href="#" class="sidebar-link" id="purchase-order-link">Purchase Order</a>
                <a href="#" class="sidebar-link" id="requisition-approval-link">Requisition Approval</a>
            <?php elseif ($role == 'Staff'): ?>
                <a href="#" class="sidebar-link" id="requisition-form-link">Requisition Form</a>
            <?php endif; ?>
        <?php elseif ($department == 'Inventory'): ?>
            <?php if ($role == 'Manager'): ?>
                <a href="#" class="sidebar-link" id="withdrawal-deposit-link">Withdrawal & Deposit</a>
                <a href="#" class="sidebar-link" id="purchase-request-link">Purchase Request</a>
            <?php elseif ($role == 'Staff'): ?>
                <a href="#" class="sidebar-link" id="requisition-form-link">Requisition Form</a>
            <?php endif; ?>
        <?php elseif ($department == 'Labor'): ?>
            <?php if ($role == 'Manager'): ?>
                <a href="#" class="sidebar-link" id="requisition-approval-link">Requisition Approval</a>
            <?php elseif ($role == 'Staff'): ?>
                <a href="#" class="sidebar-link" id="requisition-form-link">Requisition Form</a>
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
        });
    </script>
</body>
</html>
