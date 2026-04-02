<?php
require_once '../inc/inc_koneksi.php';
require_once '../inc/inc_fungsi.php';

cek_role(['admin']);

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $query = "UPDATE users SET status='nonaktif' WHERE id=$id";
    if (mysqli_query($koneksi, $query)) {
        log_aktivitas($_SESSION['user_id'], 'Menonaktifkan user ID: ' . $id, 'users', $id);
        $_SESSION['success'] = 'User berhasil dinonaktifkan';
    }
    header("Location: users.php");
    exit();
}

// Handle Form Submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $username = esc($_POST['username']);
    $nama_lengkap = esc($_POST['nama_lengkap']);
    $email = esc($_POST['email']);
    $role = esc($_POST['role']);
    $unit_kerja = esc($_POST['unit_kerja']);
    $status = esc($_POST['status']);
    
    if ($id > 0) {
        // Update
        $sql = "UPDATE users SET 
                username='$username', 
                nama_lengkap='$nama_lengkap', 
                email='$email', 
                role='$role', 
                unit_kerja='$unit_kerja', 
                status='$status'";
        
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $sql .= ", password='$password'";
        }
        
        $sql .= " WHERE id=$id";
        
        if (mysqli_query($koneksi, $sql)) {
            log_aktivitas($_SESSION['user_id'], 'Mengupdate user: ' . $nama_lengkap, 'users', $id);
            $_SESSION['success'] = 'User berhasil diupdate';
        }
    } else {
        // Insert
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password, nama_lengkap, email, role, unit_kerja, status) 
                VALUES ('$username', '$password', '$nama_lengkap', '$email', '$role', '$unit_kerja', '$status')";
        
        if (mysqli_query($koneksi, $sql)) {
            log_aktivitas($_SESSION['user_id'], 'Menambah user baru: ' . $nama_lengkap, 'users', mysqli_insert_id($koneksi));
            $_SESSION['success'] = 'User berhasil ditambahkan';
        }
    }
    
    header("Location: users.php");
    exit();
}

// Ambil data users
$query = "SELECT * FROM users ORDER BY created_at DESC";
$users = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Kelola Pengguna - E-SPMI Sistem Penjaminan Mutu Internal">
    <title>Kelola Pengguna - E-SPMI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
</head>
<body>
    <?php include 'inc_sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'inc_navbar.php'; ?>
        
        <div class="content-wrapper">
            <div class="container-fluid">
                <!-- Page Header -->
                <div class="d-flex flex-wrap justify-content-between align-items-start mb-4 gap-3">
                    <div class="page-header mb-0">
                        <h1 class="page-title">Kelola Pengguna</h1>
                        <p class="page-subtitle">Manajemen data pengguna sistem</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal" onclick="resetForm()">
                        <i class="bi bi-person-plus-fill"></i> Tambah Pengguna
                    </button>
                </div>

                <!-- Alert -->
                <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Tabel Users -->
                <div class="table-container">
                    <div class="table-responsive">
                        <table id="usersTable" class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Username</th>
                                    <th>Nama Lengkap</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Unit Kerja</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                while ($user = mysqli_fetch_assoc($users)): 
                                $role_icons = ['admin'=>'bi-shield-fill','auditor'=>'bi-person-badge-fill','auditee'=>'bi-person-fill'];
                                ?>
                                <tr>
                                    <td style="color:#94a3b8;font-weight:600;"><?php echo $no++; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['nama_lengkap']); ?>&background=4f46e5&color=fff&size=32"
                                                 style="width:32px;height:32px;border-radius:50%;">
                                            <strong style="color:#1e293b;"><?php echo htmlspecialchars($user['username']); ?></strong>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['nama_lengkap']); ?></td>
                                    <td style="color:#64748b;font-size:0.85rem;"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php
                                        $role_class = ['admin'=>'role-badge-admin','auditor'=>'role-badge-auditor','auditee'=>'role-badge-auditee'];
                                        $rc = $role_class[$user['role']] ?? 'role-badge-admin';
                                        ?>
                                        <span class="role-badge <?php echo $rc; ?>">
                                            <i class="bi <?php echo $role_icons[$user['role']] ?? 'bi-person'; ?>"></i>
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td style="font-size:0.85rem;color:#475569;"><?php echo htmlspecialchars($user['unit_kerja'] ?: '-'); ?></td>
                                    <td>
                                        <?php if ($user['status'] == 'aktif'): ?>
                                        <span class="status-badge-aktif"><i class="bi bi-circle-fill me-1" style="font-size:0.5rem;"></i>Aktif</span>
                                        <?php else: ?>
                                        <span class="status-badge-nonaktif"><i class="bi bi-circle me-1" style="font-size:0.5rem;"></i>Nonaktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-sm btn-warning" onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)" title="Edit">
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <a href="?delete=<?php echo $user['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Yakin ingin menonaktifkan user ini?')" title="Nonaktifkan">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php include 'footer.php'; ?>
    </div>

    <!-- Modal Form User -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="userId">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="username" id="username" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password <span class="text-danger" id="pwdRequired">*</span></label>
                                <input type="password" class="form-control" name="password" id="password">
                                <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama_lengkap" id="nama_lengkap" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" id="email" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select" name="role" id="role" required>
                                    <option value="">Pilih Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="auditor">Auditor</option>
                                    <option value="auditee">Auditee</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" name="status" id="status" required>
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Unit Kerja</label>
                            <input type="text" class="form-control" name="unit_kerja" id="unit_kerja" 
                                   placeholder="Contoh: Fakultas Teknik, Prodi Informatika, LPM">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#usersTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                }
            });
        });

        function resetForm() {
            document.getElementById('modalTitle').innerHTML = 'Tambah Pengguna';
            document.getElementById('userId').value = '';
            document.getElementById('username').value = '';
            document.getElementById('password').value = '';
            document.getElementById('nama_lengkap').value = '';
            document.getElementById('email').value = '';
            document.getElementById('role').value = '';
            document.getElementById('unit_kerja').value = '';
            document.getElementById('status').value = 'aktif';
            document.getElementById('password').required = true;
            document.getElementById('pwdRequired').style.display = 'inline';
        }

        function editUser(user) {
            document.getElementById('modalTitle').innerHTML = 'Edit Pengguna';
            document.getElementById('userId').value = user.id;
            document.getElementById('username').value = user.username;
            document.getElementById('nama_lengkap').value = user.nama_lengkap;
            document.getElementById('email').value = user.email;
            document.getElementById('role').value = user.role;
            document.getElementById('unit_kerja').value = user.unit_kerja || '';
            document.getElementById('status').value = user.status;
            document.getElementById('password').required = false;
            document.getElementById('pwdRequired').style.display = 'none';
            
            var modal = new bootstrap.Modal(document.getElementById('userModal'));
            modal.show();
        }
    </script>
    <script src="../assets/js/sidebar-toggle.js"></script>
</body>
</html>