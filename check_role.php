<?php
session_start();

// Check if the role is set in the session and return it
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Staff') {
    echo 'Staff';
}elseif(isset($_SESSION['role']) && $_SESSION['role'] === 'Manager'){
    echo 'Manager';
}else {
    echo 'not_staff'; // If not staff, return something else (or do nothing)
}
?>
