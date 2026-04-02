<?php
session_start();

// Konfigurasi Database
$host = "localhost";
$user = "u838029285_userespmi";
$pass = "Espmi#1234";
$db = "u838029285_espmi";

// Koneksi ke Database
$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek Koneksi
if (!$koneksi) {
    die("Gagal terkoneksi: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($koneksi, "utf8mb4");

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Base URL
define('BASE_URL', 'public_html');
define('UPLOAD_PATH', __DIR__ . '/uploads/bukti/');

// Fungsi untuk mencegah SQL Injection
function esc($str) {
    global $koneksi;
    return mysqli_real_escape_string($koneksi, $str);
}

// Fungsi untuk cek login
function cek_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "login.php");
        exit();
    }
}

// Fungsi untuk cek role
function cek_role($allowed_roles = []) {
    cek_login();
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: " . BASE_URL . "index.php");
        exit();
    }
}
?>