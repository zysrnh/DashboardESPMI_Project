<?php
require_once '../inc/inc_koneksi.php';
require_once '../inc/inc_fungsi.php';

cek_login();

$user_id = $_SESSION['user_id'];

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profil'])) {
        $nama_lengkap = esc($_POST['nama_lengkap']);
        $email = esc($_POST['email']);
        $no_hp = esc($_POST['no_hp']);
        $unit_kerja = esc($_POST['unit_kerja']);
        $jabatan = esc($_POST['jabatan']);
        
        $sql = "UPDATE users SET 
                nama_lengkap='$nama_lengkap',
                email='$email',
                no_hp='$no_hp',
                unit_kerja='$unit_kerja',
                jabatan='$jabatan'
                WHERE id=$user_id";
        
        if (mysqli_query($koneksi, $sql)) {
            $_SESSION['nama_lengkap'] = $nama_lengkap;
            $_SESSION['success'] = 'Profil berhasil diupdate!';
        } else {
            $_SESSION['error'] = 'Gagal update profil!';
        }
        
        header("Location: profil.php");
        exit();
    }
    
    if (isset($_POST['update_password'])) {
        $password_lama = $_POST['password_lama'];
        $password_baru = $_POST['password_baru'];
        $password_konfirmasi = $_POST['password_konfirmasi'];
        
        // Validasi password lama
        $check = mysqli_query($koneksi, "SELECT password FROM users WHERE id=$user_id");
        $user = mysqli_fetch_assoc($check);
        
        if (!password_verify($password_lama, $user['password'])) {
            $_SESSION['error'] = 'Password lama salah!';
        } elseif ($password_baru !== $password_konfirmasi) {
            $_SESSION['error'] = 'Password baru tidak cocok!';
        } elseif (strlen($password_baru) < 6) {
            $_SESSION['error'] = 'Password minimal 6 karakter!';
        } else {
            $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password='$password_hash' WHERE id=$user_id";
            
            if (mysqli_query($koneksi, $sql)) {
                log_aktivitas($user_id, 'Mengubah password', 'users', $user_id);
                $_SESSION['success'] = 'Password berhasil diubah!';
            } else {
                $_SESSION['error'] = 'Gagal ubah password!';
            }
        }
        
        header("Location: profil.php");
        exit();
    }
}

// Get user data
$user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id=$user_id"));

// Get aktivitas terakhir
$query_aktivitas = "SELECT * FROM log_aktivitas WHERE user_id=$user_id ORDER BY created_at DESC LIMIT 10";
$aktivitas = mysqli_query($koneksi, $query_aktivitas);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - E-SPMI</title>
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
                <h2 class="mb-4">Profil Saya</h2>

                <!-- Alert -->
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

                <div class="row">
                    <!-- Profile Card -->
                    <div class="col-md-4 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['nama_lengkap']); ?>&size=150&background=667eea&color=fff" 
                                     class="rounded-circle mb-3" width="150" height="150">
                                <h4 class="mb-1"><?php echo $user['nama_lengkap']; ?></h4>
                                <p class="text-muted mb-2"><?php echo $user['email']; ?></p>
                                <span class="badge bg-<?php 
                                    echo $user['role'] == 'admin' ? 'danger' : ($user['role'] == 'auditor' ? 'primary' : 'info'); 
                                ?>">
                                    <?php echo strtoupper($user['role']); ?>
                                </span>
                                <span class="badge bg-<?php echo $user['status'] == 'aktif' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                                
                                <hr>
                                
                                <div class="text-start">
                                    <p class="mb-2">
                                        <i class="bi bi-building text-muted"></i>
                                        <strong>Unit Kerja:</strong><br>
                                        <small><?php echo $user['unit_kerja']; ?></small>
                                    </p>
                                    <p class="mb-2">
                                        <i class="bi bi-briefcase text-muted"></i>
                                        <strong>Jabatan:</strong><br>
                                        <small><?php echo $user['jabatan']; ?></small>
                                    </p>
                                    <p class="mb-0">
                                        <i class="bi bi-phone text-muted"></i>
                                        <strong>No. HP:</strong><br>
                                        <small><?php echo $user['no_hp'] ?: '-'; ?></small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Forms -->
                    <div class="col-md-8">
                        <!-- Update Profil -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="bi bi-person-circle"></i> Edit Profil</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="update_profil" value="1">
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="nama_lengkap" 
                                                   value="<?php echo $user['nama_lengkap']; ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" name="email" 
                                                   value="<?php echo $user['email']; ?>" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">No. HP</label>
                                            <input type="text" class="form-control" name="no_hp" 
                                                   value="<?php echo $user['no_hp']; ?>" placeholder="08xxxxxxxxxx">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Unit Kerja <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="unit_kerja" 
                                                   value="<?php echo $user['unit_kerja']; ?>" required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Jabatan <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="jabatan" 
                                               value="<?php echo $user['jabatan']; ?>" required>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Simpan Perubahan
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Update Password -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="bi bi-lock"></i> Ubah Password</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="update_password" value="1">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Password Lama <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" name="password_lama" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Password Baru <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" name="password_baru" 
                                               minlength="6" required>
                                        <small class="text-muted">Minimal 6 karakter</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" name="password_konfirmasi" 
                                               minlength="6" required>
                                    </div>

                                    <button type="submit" class="btn btn-warning">
                                        <i class="bi bi-key"></i> Ubah Password
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Aktivitas Terakhir -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Aktivitas Terakhir</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <?php while ($log = mysqli_fetch_assoc($aktivitas)): ?>
                                    <div class="list-group-item px-0">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <p class="mb-1"><?php echo $log['aksi']; ?></p>
                                                <small class="text-muted">
                                                    <i class="bi bi-clock"></i> 
                                                    <?php echo tanggal_indo_lengkap($log['created_at']); ?>
                                                </small>
                                            </div>
                                            <span class="badge bg-secondary"><?php echo $log['tabel']; ?></span>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <?php include 'footer.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebar-toggle.js"></script>
</body>
</html>