<?php
require_once '../inc/inc_koneksi.php';
require_once '../inc/inc_fungsi.php';

cek_role(['admin']);

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Cek apakah ada jadwal audit yang terkait
    $check = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM jadwal_audit WHERE unit_audit_id=$id");
    $count = mysqli_fetch_assoc($check)['total'];
    
    if ($count > 0) {
        $_SESSION['error'] = 'Tidak dapat menghapus unit karena masih memiliki ' . $count . ' jadwal audit terkait!';
    } else {
        $query = "UPDATE unit_audit SET status='nonaktif' WHERE id=$id";
        if (mysqli_query($koneksi, $query)) {
            log_aktivitas($_SESSION['user_id'], 'Menonaktifkan unit audit ID: ' . $id, 'unit_audit', $id);
            $_SESSION['success'] = 'Unit audit berhasil dinonaktifkan';
        }
    }
    header("Location: unit.php");
    exit();
}

// Handle Form Submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nama_unit = esc($_POST['nama_unit']);
    $jenis = esc($_POST['jenis']);
    $kode_unit = esc($_POST['kode_unit']);
    $pimpinan = esc($_POST['pimpinan']);
    $email = esc($_POST['email']);
    $telepon = esc($_POST['telepon']);
    $status = esc($_POST['status']);
    
    if ($id > 0) {
        // Update
        $sql = "UPDATE unit_audit SET 
                nama_unit='$nama_unit', 
                jenis='$jenis', 
                kode_unit='$kode_unit', 
                pimpinan='$pimpinan', 
                email='$email', 
                telepon='$telepon', 
                status='$status'
                WHERE id=$id";
        
        if (mysqli_query($koneksi, $sql)) {
            log_aktivitas($_SESSION['user_id'], 'Mengupdate unit audit: ' . $nama_unit, 'unit_audit', $id);
            $_SESSION['success'] = 'Unit audit berhasil diupdate';
        }
    } else {
        // Cek duplikasi kode unit
        $check = mysqli_query($koneksi, "SELECT id FROM unit_audit WHERE kode_unit='$kode_unit'");
        if (mysqli_num_rows($check) > 0) {
            $_SESSION['error'] = 'Kode unit sudah digunakan!';
        } else {
            // Insert
            $sql = "INSERT INTO unit_audit (nama_unit, jenis, kode_unit, pimpinan, email, telepon, status) 
                    VALUES ('$nama_unit', '$jenis', '$kode_unit', '$pimpinan', '$email', '$telepon', '$status')";
            
            if (mysqli_query($koneksi, $sql)) {
                log_aktivitas($_SESSION['user_id'], 'Menambah unit audit baru: ' . $nama_unit, 'unit_audit', mysqli_insert_id($koneksi));
                $_SESSION['success'] = 'Unit audit berhasil ditambahkan';
            }
        }
    }
    
    header("Location: unit.php");
    exit();
}

// Ambil data unit audit
$query = "SELECT ua.*, 
          (SELECT COUNT(*) FROM jadwal_audit WHERE unit_audit_id=ua.id) as jumlah_audit
          FROM unit_audit ua 
          ORDER BY ua.jenis ASC, ua.nama_unit ASC";
$units = mysqli_query($koneksi, $query);

// Statistik
$stats = [
    'total' => mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM unit_audit WHERE status='aktif'"))['total'],
    'fakultas' => mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM unit_audit WHERE jenis='fakultas' AND status='aktif'"))['total'],
    'prodi' => mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM unit_audit WHERE jenis='prodi' AND status='aktif'"))['total'],
    'unit' => mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM unit_audit WHERE jenis='unit' AND status='aktif'"))['total']
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Kelola Unit Audit - E-SPMI">
    <title>Kelola Unit Audit - E-SPMI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
                        <h1 class="page-title">Unit Audit</h1>
                        <p class="page-subtitle">Manajemen fakultas, prodi, dan unit yang akan diaudit</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#unitModal" onclick="resetForm()">
                        <i class="bi bi-plus-lg"></i> Tambah Unit
                    </button>
                </div>

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

                <!-- Stat Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card stat-card-primary">
                            <div class="stat-card-icon"><i class="bi bi-building-fill"></i></div>
                            <div class="stat-label">Total Unit</div>
                            <div class="stat-value"><?php echo $stats['total']; ?></div>
                            <div class="stat-sub">Unit aktif terdaftar</div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card stat-card-success">
                            <div class="stat-card-icon"><i class="bi bi-buildings-fill"></i></div>
                            <div class="stat-label">Fakultas</div>
                            <div class="stat-value"><?php echo $stats['fakultas']; ?></div>
                            <div class="stat-sub">Unit tingkat fakultas</div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card stat-card-warning">
                            <div class="stat-card-icon"><i class="bi bi-mortarboard-fill"></i></div>
                            <div class="stat-label">Program Studi</div>
                            <div class="stat-value"><?php echo $stats['prodi']; ?></div>
                            <div class="stat-sub">Unit tingkat prodi</div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card stat-card-danger">
                            <div class="stat-card-icon"><i class="bi bi-diagram-3-fill"></i></div>
                            <div class="stat-label">Unit Lainnya</div>
                            <div class="stat-value"><?php echo $stats['unit']; ?></div>
                            <div class="stat-sub">Perpustakaan, LPM, dll</div>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row align-items-end g-3">
                            <div class="col-md-4">
                                <label class="form-label">Filter Jenis</label>
                                <select class="form-select" id="filterJenis">
                                    <option value="">Semua Jenis</option>
                                    <option value="fakultas">Fakultas</option>
                                    <option value="prodi">Program Studi</option>
                                    <option value="unit">Unit Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Filter Status</label>
                                <select class="form-select" id="filterStatus">
                                    <option value="">Semua Status</option>
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">&nbsp;</label>
                                <button class="btn btn-sm btn-primary w-100" onclick="resetFilter()">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0" style="font-weight:700;color:#111827;">Daftar Unit Audit</h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-primary active" id="viewTable" onclick="switchView('table')">
                            <i class="bi bi-table"></i>
                        </button>
                        <button type="button" class="btn btn-sm" id="viewCard" onclick="switchView('card')" style="background:#f3f4f6;border:1px solid #e5e7eb;">
                            <i class="bi bi-grid-3x3"></i>
                        </button>
                    </div>
                </div>

                <!-- Tabel View -->
                <div id="tableView" class="table-container">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="unitTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="10%">Kode</th>
                                        <th width="10%">Jenis</th>
                                        <th width="20%">Nama Unit</th>
                                        <th width="15%">Pimpinan</th>
                                        <th width="15%">Kontak</th>
                                        <th width="8%">Audit</th>
                                        <th width="8%">Status</th>
                                        <th width="12%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    mysqli_data_seek($units, 0);
                                    while ($unit = mysqli_fetch_assoc($units)): 
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><span class="badge bg-dark"><?php echo $unit['kode_unit']; ?></span></td>
                                        <td>
                                            <?php
                                            $jenis_color = [
                                                'fakultas' => 'success',
                                                'prodi' => 'warning',
                                                'unit' => 'danger'
                                            ];
                                            ?>
                                            <span class="jenis-badge badge bg-<?php echo $jenis_color[$unit['jenis']]; ?>">
                                                <?php echo ucfirst($unit['jenis']); ?>
                                            </span>
                                        </td>
                                        <td><strong><?php echo $unit['nama_unit']; ?></strong></td>
                                        <td><?php echo $unit['pimpinan'] ?: '-'; ?></td>
                                        <td class="contact-info">
                                            <?php if ($unit['email']): ?>
                                            <i class="bi bi-envelope"></i> <?php echo $unit['email']; ?><br>
                                            <?php endif; ?>
                                            <?php if ($unit['telepon']): ?>
                                            <i class="bi bi-telephone"></i> <?php echo $unit['telepon']; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo $unit['jumlah_audit']; ?> kali
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $unit['status'] == 'aktif' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($unit['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" onclick='editUnit(<?php echo json_encode($unit); ?>)' title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <a href="?delete=<?php echo $unit['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Yakin ingin menonaktifkan unit ini?')"
                                               title="Nonaktifkan">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Card View -->
                <div id="cardView" class="row g-4" style="display: none;">
                    <?php 
                    mysqli_data_seek($units, 0);
                    while ($unit = mysqli_fetch_assoc($units)): 
                    ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card unit-card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <span class="badge bg-dark mb-2"><?php echo $unit['kode_unit']; ?></span>
                                        <?php
                                        $jenis_color = [
                                            'fakultas' => 'success',
                                            'prodi' => 'warning',
                                            'unit' => 'danger'
                                        ];
                                        ?>
                                        <span class="jenis-badge badge bg-<?php echo $jenis_color[$unit['jenis']]; ?>">
                                            <?php echo ucfirst($unit['jenis']); ?>
                                        </span>
                                    </div>
                                    <span class="badge bg-<?php echo $unit['status'] == 'aktif' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($unit['status']); ?>
                                    </span>
                                </div>
                                
                                <h5 class="card-title mb-3"><?php echo $unit['nama_unit']; ?></h5>
                                
                                <div class="mb-3">
                                    <small class="text-muted d-block">
                                        <i class="bi bi-person-circle"></i> 
                                        <strong>Pimpinan:</strong><br>
                                        <?php echo $unit['pimpinan'] ?: '-'; ?>
                                    </small>
                                </div>

                                <?php if ($unit['email'] || $unit['telepon']): ?>
                                <div class="contact-info mb-3">
                                    <?php if ($unit['email']): ?>
                                    <small class="d-block text-muted mb-1">
                                        <i class="bi bi-envelope"></i> <?php echo $unit['email']; ?>
                                    </small>
                                    <?php endif; ?>
                                    <?php if ($unit['telepon']): ?>
                                    <small class="d-block text-muted">
                                        <i class="bi bi-telephone"></i> <?php echo $unit['telepon']; ?>
                                    </small>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>

                                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                    <span class="badge bg-info">
                                        <i class="bi bi-calendar-check"></i> 
                                        <?php echo $unit['jumlah_audit']; ?> Audit
                                    </span>
                                    <div>
                                        <button class="btn btn-sm btn-warning" onclick='editUnit(<?php echo json_encode($unit); ?>)'>
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="?delete=<?php echo $unit['id']; ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('Yakin ingin menonaktifkan unit ini?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

            </div>
        </div>
        <?php include 'footer.php'; ?>
    </div>

    <!-- Modal Form Unit -->
    <div class="modal fade" id="unitModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Unit Audit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="unitId">
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> <strong>Informasi:</strong>
                            <ul class="mb-0 mt-2">
                                <li><strong>Fakultas:</strong> Unit tingkat fakultas (contoh: Fakultas Teknik)</li>
                                <li><strong>Program Studi:</strong> Unit tingkat prodi (contoh: Prodi Informatika)</li>
                                <li><strong>Unit:</strong> Unit lainnya (contoh: Perpustakaan, LPM)</li>
                            </ul>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Kode Unit <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="kode_unit" id="kode_unit" required 
                                       placeholder="Contoh: FT">
                                <small class="text-muted">Kode unik unit</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Jenis <span class="text-danger">*</span></label>
                                <select class="form-select" name="jenis" id="jenis" required>
                                    <option value="">Pilih Jenis</option>
                                    <option value="fakultas">Fakultas</option>
                                    <option value="prodi">Program Studi</option>
                                    <option value="unit">Unit Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" name="status" id="status" required>
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Unit <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama_unit" id="nama_unit" required 
                                   placeholder="Contoh: Fakultas Teknik, Prodi Informatika, Perpustakaan">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Pimpinan</label>
                            <input type="text" class="form-control" name="pimpinan" id="pimpinan" 
                                   placeholder="Contoh: Dr. Budi Santoso, M.T.">
                            <small class="text-muted">Dekan, Kaprodi, atau Kepala Unit</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" id="email" 
                                       placeholder="Contoh: ft@universitas.ac.id">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Telepon</label>
                                <input type="text" class="form-control" name="telepon" id="telepon" 
                                       placeholder="Contoh: 021-12345678">
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan
                        </button>
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
        let table;
        
        $(document).ready(function() {
            table = $('#unitTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                },
                pageLength: 25
            });

            // Filter Jenis
            $('#filterJenis').on('change', function() {
                table.column(2).search(this.value).draw();
            });

            // Filter Status
            $('#filterStatus').on('change', function() {
                table.column(7).search(this.value).draw();
            });
        });

        function resetFilter() {
            $('#filterJenis').val('');
            $('#filterStatus').val('');
            table.search('').columns().search('').draw();
        }

        function switchView(view) {
            if (view === 'table') {
                $('#tableView').show();
                $('#cardView').hide();
                $('#viewTable').addClass('active');
                $('#viewCard').removeClass('active');
            } else {
                $('#tableView').hide();
                $('#cardView').show();
                $('#viewTable').removeClass('active');
                $('#viewCard').addClass('active');
            }
        }

        function resetForm() {
            document.getElementById('modalTitle').innerHTML = '<i class="bi bi-plus-circle"></i> Tambah Unit Audit';
            document.getElementById('unitId').value = '';
            document.getElementById('kode_unit').value = '';
            document.getElementById('jenis').value = '';
            document.getElementById('nama_unit').value = '';
            document.getElementById('pimpinan').value = '';
            document.getElementById('email').value = '';
            document.getElementById('telepon').value = '';
            document.getElementById('status').value = 'aktif';
        }

        function editUnit(unit) {
            document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil"></i> Edit Unit Audit';
            document.getElementById('unitId').value = unit.id;
            document.getElementById('kode_unit').value = unit.kode_unit;
            document.getElementById('jenis').value = unit.jenis;
            document.getElementById('nama_unit').value = unit.nama_unit;
            document.getElementById('pimpinan').value = unit.pimpinan || '';
            document.getElementById('email').value = unit.email || '';
            document.getElementById('telepon').value = unit.telepon || '';
            document.getElementById('status').value = unit.status;
            
            var modal = new bootstrap.Modal(document.getElementById('unitModal'));
            modal.show();
        }
    </script>
    <script src="../assets/js/sidebar-toggle.js"></script>
</body>
</html>