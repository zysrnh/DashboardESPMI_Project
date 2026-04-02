<?php
require_once 'inc/inc_koneksi.php';
require_once 'inc/inc_fungsi.php';  // ✅ TAMBAHKAN INI - PENTING!

if (isset($_SESSION['user_id'])) {
    log_aktivitas($_SESSION['user_id'], 'Logout dari sistem');
}

session_destroy();
header("Location: login.php");
exit();
?>