<?php
require_once '../inc/inc_koneksi.php';
require_once '../inc/inc_fungsi.php';

cek_role(['admin']);

// Get current settings
$settings = [];
$query = "SELECT * FROM pengaturan";
$result = mysqli_query($koneksi, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $settings[$row['nama_setting']] = $row['nilai'];
}

// Default values jika belum ada di database
$defaults = [
    'nama_aplikasi' => 'E-SPMI',
    'nama_institusi' => 'Universitas/Institusi',
    'alamat_institusi' => '',
    'email_institusi' => '',
    'telepon_institusi' => '',
    'website_institusi' => '',
    'logo_institusi' => '',
    'tahun_akademik_aktif' => date('Y') . '/' . (date('Y') + 1),
    'semester_aktif' => 'ganjil',
    'batas_skor_baik' => '3.5',
    'batas_skor_cukup' => '3.0',
    'batas_skor_kurang' => '2.0',
    'email_notifikasi' => '1',
    'auto_backup' => '0',
    'maintenance_mode' => '0'
];

foreach ($defaults as $key => $value) {
    if (!isset($settings[$key])) {
        $settings[$key] = $value;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Sistem - E-SPMI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
</head>
<body>
    <?php include 'inc_sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'inc_navbar.php'; ?>
        
        <div class="content-wrapper">
            <div class="container-fluid">
                
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-0"><i class="bi bi-gear"></i> Pengaturan Sistem</h2>
                        <p class="text-muted mb-0">Konfigurasi dan pengaturan aplikasi</p>
                    </div>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Tabs -->
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#umum">
                            <i class="bi bi-info-circle"></i> Umum
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tampilan">
                            <i class="bi bi-palette"></i> Tampilan
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#akademik">
                            <i class="bi bi-calendar-check"></i> Akademik
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#penilaian">
                            <i class="bi bi-clipboard-check"></i> Penilaian
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#notifikasi">
                            <i class="bi bi-bell"></i> Notifikasi
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#sistem">
                            <i class="bi bi-server"></i> Sistem
                        </button>
                    </li>
                </ul>

                <form action="pengaturan_simpan.php" method="POST" enctype="multipart/form-data">
                    <div class="tab-content">
                        
                        <!-- Tab Umum -->
                        <div class="tab-pane fade show active" id="umum">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">Informasi Institusi</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Nama Aplikasi</label>
                                            <input type="text" name="nama_aplikasi" class="form-control" 
                                                   value="<?php echo $settings['nama_aplikasi']; ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Nama Institusi</label>
                                            <input type="text" name="nama_institusi" class="form-control" 
                                                   value="<?php echo $settings['nama_institusi']; ?>" required>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Alamat Institusi</label>
                                            <textarea name="alamat_institusi" class="form-control" rows="2"><?php echo $settings['alamat_institusi']; ?></textarea>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" name="email_institusi" class="form-control" 
                                                   value="<?php echo $settings['email_institusi']; ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Telepon</label>
                                            <input type="text" name="telepon_institusi" class="form-control" 
                                                   value="<?php echo $settings['telepon_institusi']; ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Website</label>
                                            <input type="url" name="website_institusi" class="form-control" 
                                                   value="<?php echo $settings['website_institusi']; ?>">
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label">Logo Institusi</label>
                                            
                                            <?php if (!empty($settings['logo_institusi']) && file_exists('../assets/uploads/' . $settings['logo_institusi'])): ?>
                                            <div class="mb-2">
                                                <img src="../assets/uploads/<?php echo $settings['logo_institusi']; ?>" 
                                                    alt="Logo" class="img-thumbnail" style="max-height: 150px;">
                                                <br>
                                                <small class="text-muted">Logo saat ini</small>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <input type="file" name="logo_institusi" class="form-control" accept="image/*">
                                            <small class="text-muted">Format: JPG, PNG, GIF. Maksimal 2MB</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Tampilan -->
                        <div class="tab-pane fade" id="tampilan">
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">Personalisasi Tampilan</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label d-block">Warna Utama (Sidebar)</label>
                                            <div class="d-flex align-items-center">
                                                <input type="color" name="warna_sidebar" class="form-control form-control-color me-3" 
                                                       value="<?php echo $settings['warna_sidebar']; ?>" id="sidebarColorPicker">
                                                <input type="text" class="form-control" value="<?php echo $settings['warna_sidebar']; ?>" 
                                                       id="sidebarColorText" oninput="document.getElementById('sidebarColorPicker').value = this.value">
                                            </div>
                                            <small class="text-muted">Gunakan kode HEX untuk kustomisasi warna sidebar.</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label d-block">Warna Aksen (Tombol & Aktif)</label>
                                            <div class="d-flex align-items-center">
                                                <input type="color" name="warna_aksen" class="form-control form-control-color me-3" 
                                                       value="<?php echo $settings['warna_aksen']; ?>" id="accentColorPicker">
                                                <input type="text" class="form-control" value="<?php echo $settings['warna_aksen']; ?>" 
                                                       id="accentColorText" oninput="document.getElementById('accentColorPicker').value = this.value">
                                            </div>
                                            <small class="text-muted">Warna untuk tombol, link aktif, dan elemen interaktif lainnya.</small>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-light border mt-2">
                                        <strong>Tips:</strong> Gunakan warna yang kontras untuk kenyamanan navigasi.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Akademik -->
                        <div class="tab-pane fade" id="akademik">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">Pengaturan Periode Akademik</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Tahun Akademik Aktif</label>
                                            <input type="text" name="tahun_akademik_aktif" class="form-control" 
                                                   value="<?php echo $settings['tahun_akademik_aktif']; ?>" 
                                                   placeholder="2024/2025" required>
                                            <small class="text-muted">Format: YYYY/YYYY</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Semester Aktif</label>
                                            <select name="semester_aktif" class="form-select" required>
                                                <option value="ganjil" <?php echo $settings['semester_aktif'] == 'ganjil' ? 'selected' : ''; ?>>Ganjil</option>
                                                <option value="genap" <?php echo $settings['semester_aktif'] == 'genap' ? 'selected' : ''; ?>>Genap</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Penilaian -->
                        <div class="tab-pane fade" id="penilaian">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">Standar Penilaian</h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle"></i> Atur batas skor untuk kategori penilaian audit
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Batas Skor "Baik"</label>
                                            <input type="number" name="batas_skor_baik" class="form-control" 
                                                   value="<?php echo $settings['batas_skor_baik']; ?>" 
                                                   step="0.1" min="0" max="4" required>
                                            <small class="text-muted">Skor ≥ nilai ini = Baik (Hijau)</small>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Batas Skor "Cukup"</label>
                                            <input type="number" name="batas_skor_cukup" class="form-control" 
                                                   value="<?php echo $settings['batas_skor_cukup']; ?>" 
                                                   step="0.1" min="0" max="4" required>
                                            <small class="text-muted">Skor ≥ nilai ini = Cukup (Biru)</small>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Batas Skor "Kurang"</label>
                                            <input type="number" name="batas_skor_kurang" class="form-control" 
                                                   value="<?php echo $settings['batas_skor_kurang']; ?>" 
                                                   step="0.1" min="0" max="4" required>
                                            <small class="text-muted">Skor ≥ nilai ini = Kurang (Kuning), &lt; nilai ini = Buruk (Merah)</small>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-secondary mt-3">
                                        <strong>Contoh Kategori:</strong><br>
                                        <span class="badge bg-success">Baik</span> = Skor ≥ <?php echo $settings['batas_skor_baik']; ?><br>
                                        <span class="badge bg-primary">Cukup</span> = Skor ≥ <?php echo $settings['batas_skor_cukup']; ?> dan &lt; <?php echo $settings['batas_skor_baik']; ?><br>
                                        <span class="badge bg-warning">Kurang</span> = Skor ≥ <?php echo $settings['batas_skor_kurang']; ?> dan &lt; <?php echo $settings['batas_skor_cukup']; ?><br>
                                        <span class="badge bg-danger">Buruk</span> = Skor &lt; <?php echo $settings['batas_skor_kurang']; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Notifikasi -->
                        <div class="tab-pane fade" id="notifikasi">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">Pengaturan Notifikasi</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" name="email_notifikasi" 
                                               value="1" <?php echo $settings['email_notifikasi'] == '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label">
                                            Aktifkan Notifikasi Email
                                        </label>
                                        <small class="d-block text-muted">Kirim email untuk jadwal audit, hasil audit, dll</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Sistem -->
                        <div class="tab-pane fade" id="sistem">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">Pengaturan Sistem</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" name="auto_backup" 
                                               value="1" <?php echo $settings['auto_backup'] == '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label">
                                            Auto Backup Database
                                        </label>
                                        <small class="d-block text-muted">Backup otomatis setiap minggu</small>
                                    </div>
                                    
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" name="maintenance_mode" 
                                               value="1" <?php echo $settings['maintenance_mode'] == '1' ? 'checked' : ''; ?>>
                                        <label class="form-check-label text-danger">
                                            <strong>Mode Maintenance</strong>
                                        </label>
                                        <small class="d-block text-muted">Aktifkan untuk menonaktifkan akses sementara (Admin tetap bisa akses)</small>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">Informasi Sistem</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td width="40%">Versi Aplikasi</td>
                                            <td><strong>1.0.0</strong></td>
                                        </tr>
                                        <tr>
                                            <td>PHP Version</td>
                                            <td><strong><?php echo phpversion(); ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td>MySQL Version</td>
                                            <td><strong><?php echo mysqli_get_server_info($koneksi); ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td>Server</td>
                                            <td><strong><?php echo $_SERVER['SERVER_SOFTWARE']; ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td>Database Size</td>
                                            <td>
                                                <?php 
                                                $db_size = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(data_length + index_length) / 1024 / 1024 AS size FROM information_schema.TABLES WHERE table_schema = 'e-spmi'"));
                                                echo '<strong>' . number_format($db_size['size'], 2) . ' MB</strong>';
                                                ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Submit Button -->
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Simpan Pengaturan
                            </button>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                        </div>
                    </div>
                </form>

            </div>
        </div>
        <?php include 'footer.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebar-toggle.js"></script>
    <script>
        // Sync color pickers and text inputs
        document.getElementById('sidebarColorPicker').addEventListener('input', function() {
            document.getElementById('sidebarColorText').value = this.value;
        });
        document.getElementById('accentColorPicker').addEventListener('input', function() {
            document.getElementById('accentColorText').value = this.value;
        });
        
        document.getElementById('sidebarColorText').addEventListener('input', function() {
            if(/^#[0-9A-F]{6}$/i.test(this.value)) {
                document.getElementById('sidebarColorPicker').value = this.value;
            }
        });
        document.getElementById('accentColorText').addEventListener('input', function() {
            if(/^#[0-9A-F]{6}$/i.test(this.value)) {
                document.getElementById('accentColorPicker').value = this.value;
            }
        });
    </script>
</body>
</html>