<?php
require_once '../inc/inc_koneksi.php';
require_once '../inc/inc_fungsi.php';

cek_role(['admin']);

// Get standar_id from URL or POST
$standar_id = isset($_GET['standar_id']) ? (int)$_GET['standar_id'] : (isset($_POST['standar_id']) ? (int)$_POST['standar_id'] : 0);

// Get standar info
if ($standar_id > 0) {
    $query_standar = "SELECT * FROM standar WHERE id=$standar_id";
    $result_standar = mysqli_query($koneksi, $query_standar);
    if (mysqli_num_rows($result_standar) == 0) {
        $_SESSION['error'] = 'Standar tidak ditemukan!';
        header("Location: standar.php");
        exit();
    }
    $standar_info = mysqli_fetch_assoc($result_standar);
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $query = "UPDATE indikator SET status='nonaktif' WHERE id=$id";
    if (mysqli_query($koneksi, $query)) {
        log_aktivitas($_SESSION['user_id'], 'Menonaktifkan indikator ID: ' . $id, 'indikator', $id);
        $_SESSION['success'] = 'Indikator berhasil dinonaktifkan';
    }
    header("Location: indikator.php" . ($standar_id > 0 ? "?standar_id=$standar_id" : ""));
    exit();
}

// Handle Form Submit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $standar_id_form = (int)$_POST['standar_id'];
    $kode = esc($_POST['kode']);
    $nama_indikator = esc($_POST['nama_indikator']);
    $rubrik_0 = esc($_POST['rubrik_0']);
    $rubrik_1 = esc($_POST['rubrik_1']);
    $rubrik_2 = esc($_POST['rubrik_2']);
    $rubrik_3 = esc($_POST['rubrik_3']);
    $rubrik_4 = esc($_POST['rubrik_4']);
    $urutan = (int)$_POST['urutan'];
    $status = esc($_POST['status']);
    
    if ($id > 0) {
        // Update
        $sql = "UPDATE indikator SET 
                standar_id=$standar_id_form,
                kode='$kode', 
                nama_indikator='$nama_indikator', 
                rubrik_0='$rubrik_0', 
                rubrik_1='$rubrik_1', 
                rubrik_2='$rubrik_2', 
                rubrik_3='$rubrik_3', 
                rubrik_4='$rubrik_4', 
                urutan=$urutan, 
                status='$status'
                WHERE id=$id";
        
        if (mysqli_query($koneksi, $sql)) {
            log_aktivitas($_SESSION['user_id'], 'Mengupdate indikator: ' . $nama_indikator, 'indikator', $id);
            $_SESSION['success'] = 'Indikator berhasil diupdate';
        }
    } else {
        // Insert
        $sql = "INSERT INTO indikator (standar_id, kode, nama_indikator, rubrik_0, rubrik_1, rubrik_2, rubrik_3, rubrik_4, urutan, status) 
                VALUES ($standar_id_form, '$kode', '$nama_indikator', '$rubrik_0', '$rubrik_1', '$rubrik_2', '$rubrik_3', '$rubrik_4', $urutan, '$status')";
        
        if (mysqli_query($koneksi, $sql)) {
            log_aktivitas($_SESSION['user_id'], 'Menambah indikator baru: ' . $nama_indikator, 'indikator', mysqli_insert_id($koneksi));
            $_SESSION['success'] = 'Indikator berhasil ditambahkan';
        }
    }
    
    header("Location: indikator.php?standar_id=$standar_id_form");
    exit();
}

// Ambil data indikator
if ($standar_id > 0) {
    $query = "SELECT i.*, s.nama_standar, s.kode as standar_kode
              FROM indikator i 
              JOIN standar s ON i.standar_id = s.id
              WHERE i.standar_id=$standar_id
              ORDER BY i.urutan ASC";
} else {
    $query = "SELECT i.*, s.nama_standar, s.kode as standar_kode
              FROM indikator i 
              JOIN standar s ON i.standar_id = s.id
              ORDER BY s.urutan ASC, i.urutan ASC";
}
$indikator = mysqli_query($koneksi, $query);

// Ambil semua standar untuk dropdown
$query_standar_all = "SELECT * FROM standar WHERE status='aktif' ORDER BY urutan ASC";
$standar_all = mysqli_query($koneksi, $query_standar_all);

// Statistik
$stats = [
    'total' => mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM indikator WHERE status='aktif'" . ($standar_id > 0 ? " AND standar_id=$standar_id" : "")))['total'],
    'aktif' => mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM indikator WHERE status='aktif'" . ($standar_id > 0 ? " AND standar_id=$standar_id" : "")))['total'],
    'nonaktif' => mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM indikator WHERE status='nonaktif'" . ($standar_id > 0 ? " AND standar_id=$standar_id" : "")))['total']
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Indikator - E-SPMI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>
        .rubrik-card {
            border-left: 4px solid #667eea;
            transition: all 0.3s;
        }
        .rubrik-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .score-badge {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
        }
        .score-0 { background: #dc3545; color: white; }
        .score-1 { background: #ffc107; color: white; }
        .score-2 { background: #0dcaf0; color: white; }
        .score-3 { background: #0d6efd; color: white; }
        .score-4 { background: #198754; color: white; }
        
        .indikator-row {
            cursor: pointer;
            transition: all 0.2s;
        }
        .indikator-row:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <?php include 'inc_sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'inc_navbar.php'; ?>
        
        <div class="content-wrapper">
            <div class="container-fluid">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="standar.php">Standar</a></li>
                        <li class="breadcrumb-item active">Indikator</li>
                    </ol>
                </nav>

                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-0">Kelola Indikator</h2>
                        <?php if ($standar_id > 0): ?>
                        <p class="text-muted mb-0">
                            <span class="badge bg-dark"><?php echo $standar_info['kode']; ?></span> 
                            <?php echo $standar_info['nama_standar']; ?>
                        </p>
                        <?php else: ?>
                        <p class="text-muted">Manajemen indikator penilaian audit</p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <a href="standar.php" class="btn btn-secondary me-2">
                            <i class="bi bi-arrow-left"></i> Kembali ke Standar
                        </a>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#indikatorModal" onclick="resetForm()">
                            <i class="bi bi-plus-circle"></i> Tambah Indikator
                        </button>
                    </div>
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

                <!-- Statistik -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Total Indikator</h6>
                                        <h3 class="mb-0"><?php echo $stats['total']; ?></h3>
                                    </div>
                                    <div class="stat-icon bg-primary">
                                        <i class="bi bi-list-check"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Indikator Aktif</h6>
                                        <h3 class="mb-0"><?php echo $stats['aktif']; ?></h3>
                                    </div>
                                    <div class="stat-icon bg-success">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Nonaktif</h6>
                                        <h3 class="mb-0"><?php echo $stats['nonaktif']; ?></h3>
                                    </div>
                                    <div class="stat-icon bg-secondary">
                                        <i class="bi bi-x-circle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Indikator -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="indikatorTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="8%">Kode</th>
                                        <th width="12%">Standar</th>
                                        <th width="35%">Nama Indikator</th>
                                        <th width="10%">Rubrik</th>
                                        <th width="7%">Urutan</th>
                                        <th width="8%">Status</th>
                                        <th width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    while ($row = mysqli_fetch_assoc($indikator)): 
                                    ?>
                                    <tr class="indikator-row">
                                        <td><?php echo $no++; ?></td>
                                        <td><span class="badge bg-dark"><?php echo $row['kode']; ?></span></td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo $row['standar_kode']; ?>
                                            </span><br>
                                            <small class="text-muted"><?php echo substr($row['nama_standar'], 0, 30); ?>...</small>
                                        </td>
                                        <td>
                                            <strong><?php echo $row['nama_indikator']; ?></strong>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <span class="score-badge score-0" title="Skor 0">0</span>
                                                <span class="score-badge score-1" title="Skor 1">1</span>
                                                <span class="score-badge score-2" title="Skor 2">2</span>
                                                <span class="score-badge score-3" title="Skor 3">3</span>
                                                <span class="score-badge score-4" title="Skor 4">4</span>
                                            </div>
                                        </td>
                                        <td><?php echo $row['urutan']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $row['status'] == 'aktif' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick='viewRubrik(<?php echo json_encode($row); ?>)' title="Lihat Rubrik">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning" onclick='editIndikator(<?php echo json_encode($row); ?>)' title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <a href="?delete=<?php echo $row['id']; ?><?php echo $standar_id > 0 ? '&standar_id='.$standar_id : ''; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Yakin ingin menonaktifkan indikator ini?')"
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

            </div>
        </div>
        <?php include 'footer.php'; ?>
    </div>

    <!-- Modal Form Indikator -->
    <div class="modal fade" id="indikatorModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">Tambah Indikator</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="indikatorId">
                        <input type="hidden" name="submit" value="1">
                        
                        <!-- Info Standar -->
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> <strong>Petunjuk Pengisian:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Isi rubrik penilaian untuk setiap skor (0, 1, 2, 3, 4)</li>
                                <li>Skor 0 = Tidak memenuhi / Tidak ada</li>
                                <li>Skor 4 = Sangat baik / Melampaui standar</li>
                                <li>Gunakan kalimat yang jelas dan terukur</li>
                            </ul>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Standar <span class="text-danger">*</span></label>
                                <select class="form-select" name="standar_id" id="standar_id" required>
                                    <option value="">Pilih Standar</option>
                                    <?php 
                                    mysqli_data_seek($standar_all, 0);
                                    while ($std = mysqli_fetch_assoc($standar_all)): 
                                    ?>
                                    <option value="<?php echo $std['id']; ?>" <?php echo ($standar_id == $std['id']) ? 'selected' : ''; ?>>
                                        <?php echo $std['kode'] . ' - ' . $std['nama_standar']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Kode Indikator <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="kode" id="kode" required placeholder="Contoh: 1.1">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" name="status" id="status" required>
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Indikator / Aspek Penilaian <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="nama_indikator" id="nama_indikator" rows="2" required 
                                      placeholder="Contoh: CPL disusun dengan melibatkan pemangku kepentingan dan dunia kerja"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Urutan <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="urutan" id="urutan" required min="1" value="1">
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3"><i class="bi bi-star"></i> Rubrik Penilaian</h5>

                        <!-- Rubrik Skor 0 -->
                        <div class="card rubrik-card mb-3">
                            <div class="card-header bg-danger text-white">
                                <strong><span class="score-badge score-0">0</span> Skor 0 - Tidak Memenuhi</strong>
                            </div>
                            <div class="card-body">
                                <textarea class="form-control" name="rubrik_0" id="rubrik_0" rows="3" required 
                                          placeholder="Deskripsi kondisi yang mendapat skor 0..."></textarea>
                            </div>
                        </div>

                        <!-- Rubrik Skor 1 -->
                        <div class="card rubrik-card mb-3">
                            <div class="card-header bg-warning text-white">
                                <strong><span class="score-badge score-1">1</span> Skor 1 - Kurang</strong>
                            </div>
                            <div class="card-body">
                                <textarea class="form-control" name="rubrik_1" id="rubrik_1" rows="3" required 
                                          placeholder="Deskripsi kondisi yang mendapat skor 1..."></textarea>
                            </div>
                        </div>

                        <!-- Rubrik Skor 2 -->
                        <div class="card rubrik-card mb-3">
                            <div class="card-header bg-info text-white">
                                <strong><span class="score-badge score-2">2</span> Skor 2 - Cukup</strong>
                            </div>
                            <div class="card-body">
                                <textarea class="form-control" name="rubrik_2" id="rubrik_2" rows="3" required 
                                          placeholder="Deskripsi kondisi yang mendapat skor 2..."></textarea>
                            </div>
                        </div>

                        <!-- Rubrik Skor 3 -->
                        <div class="card rubrik-card mb-3">
                            <div class="card-header bg-primary text-white">
                                <strong><span class="score-badge score-3">3</span> Skor 3 - Baik</strong>
                            </div>
                            <div class="card-body">
                                <textarea class="form-control" name="rubrik_3" id="rubrik_3" rows="3" required 
                                          placeholder="Deskripsi kondisi yang mendapat skor 3..."></textarea>
                            </div>
                        </div>

                        <!-- Rubrik Skor 4 -->
                        <div class="card rubrik-card mb-3">
                            <div class="card-header bg-success text-white">
                                <strong><span class="score-badge score-4">4</span> Skor 4 - Sangat Baik</strong>
                            </div>
                            <div class="card-body">
                                <textarea class="form-control" name="rubrik_4" id="rubrik_4" rows="3" required 
                                          placeholder="Deskripsi kondisi yang mendapat skor 4..."></textarea>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Indikator
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal View Rubrik -->
    <div class="modal fade" id="viewRubrikModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="bi bi-eye"></i> Detail Rubrik Penilaian</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="viewRubrikContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#indikatorTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                },
                order: [[5, 'asc']], // Sort by urutan
                pageLength: 25
            });
        });

        function resetForm() {
            document.getElementById('modalTitle').innerHTML = '<i class="bi bi-plus-circle"></i> Tambah Indikator';
            document.getElementById('indikatorId').value = '';
            document.getElementById('standar_id').value = '<?php echo $standar_id; ?>';
            document.getElementById('kode').value = '';
            document.getElementById('nama_indikator').value = '';
            document.getElementById('rubrik_0').value = '';
            document.getElementById('rubrik_1').value = '';
            document.getElementById('rubrik_2').value = '';
            document.getElementById('rubrik_3').value = '';
            document.getElementById('rubrik_4').value = '';
            document.getElementById('urutan').value = '1';
            document.getElementById('status').value = 'aktif';
        }

        function editIndikator(indikator) {
            document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil"></i> Edit Indikator';
            document.getElementById('indikatorId').value = indikator.id;
            document.getElementById('standar_id').value = indikator.standar_id;
            document.getElementById('kode').value = indikator.kode;
            document.getElementById('nama_indikator').value = indikator.nama_indikator;
            document.getElementById('rubrik_0').value = indikator.rubrik_0;
            document.getElementById('rubrik_1').value = indikator.rubrik_1;
            document.getElementById('rubrik_2').value = indikator.rubrik_2;
            document.getElementById('rubrik_3').value = indikator.rubrik_3;
            document.getElementById('rubrik_4').value = indikator.rubrik_4;
            document.getElementById('urutan').value = indikator.urutan;
            document.getElementById('status').value = indikator.status;
            
            var modal = new bootstrap.Modal(document.getElementById('indikatorModal'));
            modal.show();
        }

        function viewRubrik(indikator) {
            let html = `
                <div class="mb-3">
                    <h5><span class="badge bg-dark">${indikator.kode}</span> ${indikator.nama_indikator}</h5>
                    <p class="text-muted mb-0">
                        <span class="badge bg-info">${indikator.standar_kode}</span> ${indikator.nama_standar}
                    </p>
                </div>
                <hr>
                
                <div class="row g-3">
                    <div class="col-12">
                        <div class="card rubrik-card">
                            <div class="card-header bg-danger text-white">
                                <strong><span class="score-badge score-0">0</span> Skor 0 - Tidak Memenuhi</strong>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">${indikator.rubrik_0}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="card rubrik-card">
                            <div class="card-header bg-warning text-white">
                                <strong><span class="score-badge score-1">1</span> Skor 1 - Kurang</strong>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">${indikator.rubrik_1}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="card rubrik-card">
                            <div class="card-header bg-info text-white">
                                <strong><span class="score-badge score-2">2</span> Skor 2 - Cukup</strong>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">${indikator.rubrik_2}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="card rubrik-card">
                            <div class="card-header bg-primary text-white">
                                <strong><span class="score-badge score-3">3</span> Skor 3 - Baik</strong>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">${indikator.rubrik_3}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="card rubrik-card">
                            <div class="card-header bg-success text-white">
                                <strong><span class="score-badge score-4">4</span> Skor 4 - Sangat Baik</strong>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">${indikator.rubrik_4}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('viewRubrikContent').innerHTML = html;
            var modal = new bootstrap.Modal(document.getElementById('viewRubrikModal'));
            modal.show();
        }
    </script>
    <script src="../assets/js/sidebar-toggle.js"></script>
</body>
</html>