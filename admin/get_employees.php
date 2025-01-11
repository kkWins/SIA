<?php

require_once 'db.php'; // Make sure this path is correct

function get_employees() {
    global $db; // Use the correct database connection variable name
    
    // Check if user is logged in and is an Admin
    if (!isset($_SESSION['loggedIn']) || $_SESSION['role'] !== 'Admin') {
        return null;
    }

    try {
        // Pagination settings
        $limit = 5; // Records per page
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $start = ($page - 1) * $limit;

        // Get total records for pagination
        $total_query = "SELECT COUNT(*) as total FROM EMPLOYEE E WHERE E.EMP_POSITION != 'Admin'";
        $total_result = $db->query($total_query);
        $total_row = $total_result->fetch_assoc();
        $total_records = $total_row['total'];
        $total_pages = ceil($total_records / $limit);

        // Main query with pagination
        $query = "SELECT 
                    E.EMP_ID,
                    CONCAT(E.EMP_FNAME, ' ', E.EMP_LNAME) as FULL_NAME,
                    E.EMP_EMAIL,
                    E.EMP_POSITION,
                    D.DEPT_NAME,
                    E.EMP_NUMBER
                FROM EMPLOYEE E
                LEFT JOIN DEPARTMENT D ON E.DEPT_ID = D.DEPT_ID
                WHERE E.EMP_POSITION != 'Admin'
                ORDER BY E.EMP_ID DESC
                LIMIT ?, ?";

        $stmt = $db->prepare($query);
        $stmt->bind_param("ii", $start, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $employees = [];
        
        while($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }
        
        return [
            'employees' => $employees,
            'pagination' => [
                'total_pages' => $total_pages,
                'current_page' => $page,
                'total_records' => $total_records
            ]
        ];
        
    } catch (Exception $e) {
        return null;
    }
}
?>
