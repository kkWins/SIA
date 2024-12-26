<?php
session_start();

// Database connection
$servername = "localhost";
$db_username = "root";
$db_password = ""; // Use your database password if set
$database = "moonlight_dbs";

$response = ['status' => 'error', 'message' => 'Invalid username or password'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Create database connection
    $connection = new mysqli($servername, $db_username, $db_password, $database);

    // Check connection
    if ($connection->connect_error) {
        $response['message'] = 'Database connection failed: ' . $connection->connect_error;
        echo json_encode($response);
        exit;
    }

    // Prevent SQL injection by using prepared statements
    $stmt = $connection->prepare("SELECT E.EMP_ID,
                                        CONCAT(E.EMP_FNAME, ' ', E.EMP_LNAME) AS EMP_NAME,
                                        E.EMP_PASSWORD,
                                        E.EMP_POSITION,
                                        E.EMP_FNAME,
                                        D.DEPT_NAME,
                                        E.EMP_EMAIL
                                    FROM EMPLOYEE E
                                    JOIN DEPARTMENT D
                                    ON E.DEPT_ID = D.DEPT_ID
                                        WHERE CONCAT(E.EMP_FNAME, ' ', E.EMP_LNAME) = ? AND EMP_PASSWORD = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a user was found
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Set session variables
        $_SESSION['loggedIn'] = true;
        $_SESSION['ID'] = $user['EMP_ID'];
        $_SESSION['username'] = $user['EMP_FNAME'];
        $_SESSION['role'] = $user['EMP_POSITION'];
        $_SESSION['department'] = $user['DEPT_NAME'];
        $_SESSION['emp_email'] = $user['EMP_EMAIL'];

        $response = ['status' => 'success', 'message' => 'Login successful'];
    } else {
        $response['message'] = 'Invalid username or password';
    }

    // Close statement and connection
    $stmt->close();
    $connection->close();
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>