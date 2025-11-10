<?php
session_start();

function checkAuth($required_role = null) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }
    
    if ($required_role && $_SESSION['role'] != $required_role) {
        header("Location: ../unauthorized.php");
        exit();
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}
?>