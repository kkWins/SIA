<?php
require_once '../db.php';

header('Content-Type: application/json');

try {
    $response = ['exists' => false];

    if (!isset($_POST['name'])) {
        throw new Exception('Supplier name is required');
    }

    $name = trim($_POST['name']);
    
    if (empty($name)) {
        throw new Exception('Supplier name cannot be empty');
    }

    // Prepare statement to prevent SQL injection
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM supplier WHERE LOWER(SP_NAME) = LOWER(?)");
    
    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . $db->error);
    }

    $stmt->bind_param("s", $name);
    
    if (!$stmt->execute()) {
        throw new Exception('Database query failed: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $response['exists'] = ($row['count'] > 0);
    $response['success'] = true;

} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
}

echo json_encode($response); 