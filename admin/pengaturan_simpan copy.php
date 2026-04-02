<?php
require_once '../inc/inc_koneksi.php';
require_once '../inc/inc_fungsi.php';

cek_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Array of settings to save
    $settings = [
        'nama_aplikasi',
        'nama_institusi',
        'alamat_institusi',
        'email_institusi',
        'telepon_institusi',
        'website_institusi',
        'tahun_akademik_aktif',
        'semester_aktif',
        'batas_skor_baik',
        'batas_skor_cukup',
        'batas_skor_kurang'
    ];
    
    // Checkbox settings (will be 0 if not checked)
    $checkbox_settings = [
        'email_notifikasi',
        'auto_backup',
        'maintenance_mode'
    ];
    
    $success = true;
    
    // Save regular settings
    foreach ($settings as $key) {
        if (isset($_POST[$key])) {
            $value = mysqli_real_escape_string($koneksi, $_POST[$key]);
            
            // Check if setting exists
            $check = mysqli_query($koneksi, "SELECT id FROM pengaturan WHERE nama_setting='$key'");
            
            if (mysqli_num_rows($check) > 0) {
                // Update
                $query = "UPDATE pengaturan SET nilai='$value' WHERE nama_setting='$key'";
            } else {
                // Insert
                $query = "INSERT INTO pengaturan (nama_setting, nilai) VALUES ('$key', '$value')";
            }
            
            if (!mysqli_query($koneksi, $query)) {
                $success = false;
            }
        }
    }
    
    // Save checkbox settings
    foreach ($checkbox_settings as $key) {
        $value = isset($_POST[$key]) ? '1' : '0';
        
        $check = mysqli_query($koneksi, "SELECT id FROM pengaturan WHERE nama_setting='$key'");
        
        if (mysqli_num_rows($check) > 0) {
            $query = "UPDATE pengaturan SET nilai='$value' WHERE nama_setting='$key'";
        } else {
            $query = "INSERT INTO pengaturan (nama_setting, nilai) VALUES ('$key', '$value')";
        }
        
        if (!mysqli_query($koneksi, $query)) {
            $success = false;
        }
    }
    
    // Handle logo upload
    if (isset($_FILES['logo_institusi']) && $_FILES['logo_institusi']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['logo_institusi']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            // Buat folder uploads jika belum ada
            $upload_dir = '../assets/uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $new_filename = 'logo_' . time() . '.' . $ext;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['logo_institusi']['tmp_name'], $upload_path)) {
                // Hapus logo lama jika ada
                $old_logo = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT nilai FROM pengaturan WHERE nama_setting='logo_institusi'"));
                if ($old_logo && !empty($old_logo['nilai']) && file_exists($upload_dir . $old_logo['nilai'])) {
                    unlink($upload_dir . $old_logo['nilai']);
                }
                
                $check = mysqli_query($koneksi, "SELECT id FROM pengaturan WHERE nama_setting='logo_institusi'");
                
                if (mysqli_num_rows($check) > 0) {
                    $query = "UPDATE pengaturan SET nilai='$new_filename' WHERE nama_setting='logo_institusi'";
                } else {
                    $query = "INSERT INTO pengaturan (nama_setting, nilai) VALUES ('logo_institusi', '$new_filename')";
                }
                
                mysqli_query($koneksi, $query);
            } else {
                $_SESSION['error'] = "Gagal upload logo!";
            }
        } else {
            $_SESSION['error'] = "Format file tidak didukung! Gunakan: JPG, PNG, GIF";
        }
    }
    
    if ($success) {
        log_aktivitas($_SESSION['user_id'], "Mengubah pengaturan sistem", 'pengaturan', 0);
        $_SESSION['success'] = "Pengaturan berhasil disimpan!";
    } else {
        if (!isset($_SESSION['error'])) {
            $_SESSION['error'] = "Gagal menyimpan pengaturan!";
        }
    }
    
    header("Location: pengaturan.php");
    exit();
}

header("Location: pengaturan.php");
exit();