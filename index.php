<?php
require_once 'inc/inc_koneksi.php';

// Redirect berdasarkan role jika sudah login
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: admin/index.php");
            break;
        case 'auditor':
            header("Location: auditor/index.php");
            break;
        case 'auditee':
            header("Location: auditee/index.php");
            break;
    }
    exit();
} else {
    header("Location: login.php");
    exit();
}
?>