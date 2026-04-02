<?php
require_once '../inc/inc_koneksi.php';
require_once '../inc/inc_fungsi.php';

cek_role(['admin']);

// Hapus log lebih dari 30 hari
$sql = "DELETE FROM log_aktivitas WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)";

if (mysqli_query($koneksi, $sql)) {
    $deleted = mysqli_affected_rows($koneksi);
    log_aktivitas($_SESSION['user_id'], "Menghapus $deleted log lama (>30 hari)", 'log_aktivitas', 0);
    $_SESSION['success'] = "Berhasil menghapus $deleted log lama!";
} else {
    $_SESSION['error'] = "Gagal menghapus log lama!";
}

header("Location: log.php");
exit();