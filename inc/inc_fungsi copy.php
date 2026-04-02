<?php
// Fungsi untuk format tanggal Indonesia
function tanggal_indo($tanggal) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    $pecahkan = explode('-', $tanggal);
    return $pecahkan[2] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
}

// Fungsi untuk hitung persentase
function hitung_persentase($skor_tercapai, $skor_maksimal) {
    if ($skor_maksimal == 0) return 0;
    return round(($skor_tercapai / $skor_maksimal) * 100, 2);
}

// Fungsi untuk kategori penilaian
function get_kategori($persentase) {
    if ($persentase >= 90) {
        return ['kategori' => 'SANGAT BAIK', 'class' => 'success', 'keterangan' => 'Melampaui SN Dikti'];
    } elseif ($persentase >= 75) {
        return ['kategori' => 'BAIK', 'class' => 'primary', 'keterangan' => 'Memenuhi SN Dikti'];
    } elseif ($persentase >= 60) {
        return ['kategori' => 'CUKUP', 'class' => 'info', 'keterangan' => 'Hampir Memenuhi SN Dikti'];
    } elseif ($persentase >= 40) {
        return ['kategori' => 'KURANG', 'class' => 'warning', 'keterangan' => 'Di Bawah SN Dikti'];
    } else {
        return ['kategori' => 'SANGAT KURANG', 'class' => 'danger', 'keterangan' => 'Tidak Memenuhi SN Dikti'];
    }
}

// Fungsi untuk upload file
function upload_file($file, $allowed_types = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png']) {
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];
    
    if ($file_error !== 0) {
        return ['status' => false, 'message' => 'Error saat upload file'];
    }
    
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    if (!in_array($file_ext, $allowed_types)) {
        return ['status' => false, 'message' => 'Tipe file tidak diizinkan'];
    }
    
    if ($file_size > 10485760) { // 10MB
        return ['status' => false, 'message' => 'Ukuran file maksimal 10MB'];
    }
    
    $new_file_name = uniqid() . '_' . time() . '.' . $file_ext;
    $upload_path = UPLOAD_PATH . $new_file_name;
    
    if (move_uploaded_file($file_tmp, $upload_path)) {
        return [
            'status' => true,
            'file_name' => $file_name,
            'file_path' => 'uploads/bukti/' . $new_file_name,
            'file_type' => $file_ext,
            'file_size' => $file_size
        ];
    }
    
    return ['status' => false, 'message' => 'Gagal upload file'];
}

// Fungsi untuk log aktivitas
function log_aktivitas($user_id, $aktivitas, $tabel_terkait = null, $id_terkait = null) {
    global $koneksi;
    
    $user_id = (int)$user_id;
    $aktivitas = mysqli_real_escape_string($koneksi, $aktivitas);
    
    // Handle optional parameters
    if ($tabel_terkait !== null) {
        $tabel_terkait = "'" . mysqli_real_escape_string($koneksi, $tabel_terkait) . "'";
    } else {
        $tabel_terkait = "NULL";
    }
    
    if ($id_terkait !== null) {
        $id_terkait = (int)$id_terkait;
    } else {
        $id_terkait = "NULL";
    }
    
    // Get IP address
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    
    // Get user agent
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    $query = "INSERT INTO log_aktivitas (user_id, aktivitas, tabel_terkait, id_terkait, ip_address, user_agent, created_at) 
              VALUES ($user_id, '$aktivitas', $tabel_terkait, $id_terkait, '$ip_address', '$user_agent', NOW())";
    
    return mysqli_query($koneksi, $query);
}

// Fungsi untuk generate kode audit
function generate_kode_audit($unit_id) {
    global $koneksi;
    
    $tahun = date('Y');
    $bulan = date('m');
    
    $query = "SELECT COUNT(*) as total FROM jadwal_audit WHERE YEAR(created_at) = '$tahun'";
    $result = mysqli_query($koneksi, $query);
    $row = mysqli_fetch_assoc($result);
    $nomor = str_pad($row['total'] + 1, 4, '0', STR_PAD_LEFT);
    
    return "AMI-{$tahun}{$bulan}-{$nomor}";
}

// Fungsi untuk sanitasi input
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Tambahkan di bagian bawah file inc_fungsi.php

function nama_kota() {
    // Ganti dengan nama kota institusi
    return 'Jakarta';
}

function tanggal_indo_lengkap($datetime) {
    $timestamp = strtotime($datetime);
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $tanggal = date('d', $timestamp);
    $bulan_nama = $bulan[(int)date('m', $timestamp)];
    $tahun = date('Y', $timestamp);
    $jam = date('H:i', $timestamp);
    
    return $tanggal . ' ' . $bulan_nama . ' ' . $tahun . ' ' . $jam;
}
?>