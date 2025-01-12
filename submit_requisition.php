<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['items'], $_POST['quantities'], $_POST['reasons'])) {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "moonlight_db";

    $connection = new mysqli($servername, $username, $password, $database);
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    $connection->begin_transaction();

    try {
        $emp_id = $_SESSION['ID'];
        $emp_position = $_SESSION['role'];
        //$prf_date = date("Y-m-d");

        $purpose = $_POST['reason'];



        if ($emp_position === "Staff"){
            $prf_status = 'pending';
        }else if($emp_position === "Manager"){
            $prf_status = 'approved';
        }


        $stmt = $connection->prepare("INSERT INTO purchase_or_requisition_form (PRF_DATE, PRF_STATUS,EMP_ID) VALUES (NOW(), ?,?)");
        $stmt->bind_param("si", $prf_status, $emp_id);
        $stmt->execute();
        $prf_id = $stmt->insert_id;
        $stmt->close();

        foreach ($_POST['items'] as $index => $itemId) {
            $quantity = $_POST['quantities'][$index];
            $reason = $_POST['reasons'][$index];

            $stmt = $connection->prepare("INSERT INTO item_list (IT_QUANTITY, IT_DATE, IT_DESCRIPTION, INV_ID, PRF_ID) VALUES (?, NOW(), ?, ?, ?)");
            //$it_date = date("Y-m-d");
            $stmt->bind_param("isii", $quantity, $reason, $itemId, $prf_id);
            $stmt->execute();
            $stmt->close();
        }

        $connection->commit();
        echo 1;
    } catch (Exception $e) {
        $connection->rollback();
        echo "Error: " . $e->getMessage();
    } finally {
        $connection->close();
    }
} else {
    echo "Invalid request.";
}
?>
