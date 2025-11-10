<?php
require_once 'config/session.php';
if (isLoggedIn()) {
    switch (getUserRole()) {
        case 'admin':
            header("Location: admin/dashboard.php");
            break;
        case 'guru':
            header("Location: guru/dashboard.php");
            break;
        case 'siswa':
            header("Location: siswa/dashboard.php");
            break;
        default:
            header("Location: login.php");
    }
    exit();
} else {
    header("Location: login.php");
    exit();
}
?>