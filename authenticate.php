<?php
session_start();

// Database connection
$servername = "localhost";
$db_username = "root";
$db_password = ""; // Use your database password if set
$database = "moonlight_db";

$response = ['status' => 'error', 'message' => 'Invalid email or password'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Create database connection
    $connection = new mysqli($servername, $db_username, $db_password, $database);

    // Check connection
    if ($connection->connect_error) {
        $response['message'] = 'Database connection failed: ' . $connection->connect_error;
        echo json_encode($response);
        exit;
    }

    // Updated query to include EMP_STATUS check
    $stmt = $connection->prepare("SELECT E.EMP_ID,
                                        CONCAT(E.EMP_FNAME, ' ', E.EMP_LNAME) AS EMP_NAME,
                                        E.EMP_PASSWORD,
                                        E.EMP_POSITION,
                                        D.DEPT_NAME,
                                        D.DEPT_ID,
                                        E.EMP_EMAIL,
                                        E.EMP_STATUS
                                FROM EMPLOYEE E
                                LEFT JOIN DEPARTMENT D ON E.DEPT_ID = D.DEPT_ID
                                WHERE E.EMP_EMAIL = ? 
                                AND E.EMP_PASSWORD = ?
                                AND (D.DEPT_ID IS NOT NULL OR E.EMP_POSITION = 'Admin')");
                                
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a user was found
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Check if account is active
        if ($user['EMP_STATUS'] == 0) {
            $response['message'] = 'Account is deactivated. Please contact administrator.';
        } else {
            // Set session variables
            $_SESSION['loggedIn'] = true;
            $_SESSION['ID'] = $user['EMP_ID'];
            $_SESSION['username'] = $user['EMP_NAME'];
            $_SESSION['role'] = $user['EMP_POSITION'];
            $_SESSION['department'] = $user['DEPT_NAME'];
            $_SESSION['department_id'] = $user['DEPT_ID'];
            $_SESSION['emp_email'] = $user['EMP_EMAIL'];

            $response = ['status' => 'success', 'message' => 'Login successful'];
        }
    } else {
        $response['message'] = 'Invalid email or password';
    }

    // Close statement and connection
    $stmt->close();
    $connection->close();
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>