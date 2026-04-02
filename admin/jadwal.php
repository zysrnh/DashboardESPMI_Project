<?php
require_once '../inc/inc_koneksi.php';
require_once '../inc/inc_fungsi.php';

cek_role(['admin']);

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Cek apakah sudah ada penilaian
    $check = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM penilaian WHERE jadwal_audit_id=$id");
    $count = mysqli_fetch_assoc($check)['total'];
    
    if ($count > 0) {
        $_SESSION['error'] = 'Tidak dapat menghapus jadwal karena sudah ada ' . $count . ' penilaian yang tercatat!';
    } else {
        $query = "DELETE FROM jadwal_audit WHERE id=$id";
        if (mysqli_query($koneksi, $query)) {
            log_aktivitas($_SESSION['user_id'], 'Menghapus jadwal audit ID: ' . $id, 'jadwal_audit', $id);
            $_SESSION['success'] = 'Jadwal audit berhasil dihapus';
        }
    }
    header("Location: jadwal.php");
    exit();
}

// Handle Form Submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $unit_audit_id = (int)$_POST['unit_audit_id'];
    $tahun_akademik = esc($_POST['tahun_akademik']);
    $semester = esc($_POST['semester']);
    $tanggal_mulai = esc($_POST['tanggal_mulai']);
    $tanggal_selesai = esc($_POST['tanggal_selesai']);
    $ketua_auditor = (int)$_POST['ketua_auditor'];
    $anggota_auditor = isset($_POST['anggota_auditor']) ? implode(',', $_POST['anggota_auditor']) : '';
    $auditee_id = (int)$_POST['auditee_id'];
    $status = esc($_POST['status']);
    $keterangan = esc($_POST['keterangan']);
    
    if ($id > 0) {
        // Update
        $sql = "UPDATE jadwal_audit SET 
                unit_audit_id=$unit_audit_id,
                tahun_akademik='$tahun_akademik',
                semester='$semester',
                tanggal_mulai='$tanggal_mulai',
                tanggal_selesai='$tanggal_selesai',
                ketua_auditor=$ketua_auditor,
                anggota_auditor='$anggota_auditor',
                auditee_id=$auditee_id,
                status='$status',
                keterangan='$keterangan'
                WHERE id=$id";
        
        if (mysqli_query($koneksi, $sql)) {
            log_aktivitas($_SESSION['user_id'], 'Mengupdate jadwal audit ID: ' . $id, 'jadwal_audit', $id);
            $_SESSION['success'] = 'Jadwal audit berhasil diupdate';
        }
    } else {
        // Generate kode audit
        $kode_audit = generate_kode_audit($unit_audit_id);
        
        // Insert
        $sql = "INSERT INTO jadwal_audit (kode_audit, unit_audit_id, tahun_akademik, semester, tanggal_mulai, tanggal_selesai, ketua_auditor, anggota_auditor, auditee_id, status, keterangan) 
                VALUES ('$kode_audit', $unit_audit_id, '$tahun_akademik', '$semester', '$tanggal_mulai', '$tanggal_selesai', $ketua_auditor, '$anggota_auditor', $auditee_id, '$status', '$keterangan')";
        
        if (mysqli_query($koneksi, $sql)) {
            log_aktivitas($_SESSION['user_id'], 'Menambah jadwal audit baru: ' . $kode_audit, 'jadwal_audit', mysqli_insert_id($koneksi));
            $_SESSION['success'] = 'Jadwal audit berhasil ditambahkan dengan kode: ' . $kode_audit;
        }
    }
    
    header("Location: jadwal.php");
    exit();
}

// Ambil data jadwal audit
$query = "SELECT ja.*, 
          ua.nama_unit, ua.jenis, ua.kode_unit,
          u1.nama_lengkap as ketua_nama,
          u2.nama_lengkap as auditee_nama,
          (SELECT COUNT(*) FROM penilaian WHERE jadwal_audit_id=ja.id) as jumlah_penilaian
          FROM jadwal_audit ja
          JOIN unit_audit ua ON ja.unit_audit_id = ua.id
          JOIN users u1 ON ja.ketua_auditor = u1.id
          JOIN users u2 ON ja.auditee_id = u2.id
          ORDER BY ja.tanggal_mulai DESC";
$jadwal = mysqli_query($koneksi, $query);

// Ambil data untuk dropdown
$query_unit = "SELECT * FROM unit_audit WHERE status='aktif' ORDER BY jenis ASC, nama_unit ASC";
$units = mysqli_query($koneksi, $query_unit);

$query_auditor = "SELECT * FROM users WHERE role='auditor' AND status='aktif' ORDER BY nama_lengkap ASC";
$auditors = mysqli_query($koneksi, $query_auditor);

$query_auditee = "SELECT * FROM users WHERE role='auditee' AND status='aktif' ORDER BY nama_lengkap ASC";
$auditees = mysqli_query($koneksi, $query_auditee);

// Statistik
$stats = [
    'total' => mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM jadwal_audit"))['total'],
    'dijadwalkan' => mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM jadwal_audit WHERE status='dijadwalkan'"))['total'],
    'berlangsung' => mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM jadwal_audit WHERE status='berlangsung'"))['total'],
    'selesai' => mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM jadwal_audit WHERE status='selesai'"))['total']
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Jadwal Audit - E-SPMI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>
        .timeline-item {
            position: relative;
            padding-left: 40px;
            padding-bottom: 30px;
        }
        .timeline-item:before {
            content: '';
            position: absolute;
            left: 15px;
            top: 8px;
            bottom: -30px;
            width: 2px;
            background: #e9ecef;
        }
        .timeline-item:last-child:before {
            display: none;
        }
        .timeline-icon {
            position: absolute;
            left: 0;
            top: 0;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.875rem;
        }
        .status-badge-lg {
            padding: 8px 16px;
            font-size: 0.875rem;
            border-radius: 20px;
        }
    </style>
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
                        <h2 class="mb-0">Kelola Jadwal Audit</h2>
                        <p class="text-muted">Penjadwalan Audit Mutu Internal</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#jadwalModal" onclick="resetForm()">
                        <i class="bi bi-plus-circle"></i> Tambah Jadwal
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

                <!-- Statistik -->
                <div class="row g-4 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Total Jadwal</h6>
                                        <h3 class="mb-0"><?php echo $stats['total']; ?></h3>
                                    </div>
                                    <div class="stat-icon bg-primary">
                                        <i class="bi bi-calendar-event"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Dijadwalkan</h6>
                                        <h3 class="mb-0"><?php echo $stats['dijadwalkan']; ?></h3>
                                    </div>
                                    <div class="stat-icon bg-info">
                                        <i class="bi bi-clock-history"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Berlangsung</h6>
                                        <h3 class="mb-0"><?php echo $stats['berlangsung']; ?></h3>
                                    </div>
                                    <div class="stat-icon bg-warning">
                                        <i class="bi bi-hourglass-split"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Selesai</h6>
                                        <h3 class="mb-0"><?php echo $stats['selesai']; ?></h3>
                                    </div>
                                    <div class="stat-icon bg-success">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Tahun Akademik</label>
                                <select class="form-select" id="filterTahun">
                                    <option value="">Semua Tahun</option>
                                    <option value="2024/2025">2024/2025</option>
                                    <option value="2025/2026">2025/2026</option>
                                    <option value="2026/2027">2026/2027</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Semester</label>
                                <select class="form-select" id="filterSemester">
                                    <option value="">Semua Semester</option>
                                    <option value="ganjil">Ganjil</option>
                                    <option value="genap">Genap</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="filterStatus">
                                    <option value="">Semua Status</option>
                                    <option value="dijadwalkan">Dijadwalkan</option>
                                    <option value="berlangsung">Berlangsung</option>
                                    <option value="selesai">Selesai</option>
                                    <option value="dibatalkan">Dibatalkan</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-secondary w-100" onclick="resetFilter()">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Jadwal -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="jadwalTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="10%">Kode Audit</th>
                                        <th width="15%">Unit</th>
                                        <th width="10%">Periode</th>
                                        <th width="12%">Tanggal</th>
                                        <th width="15%">Tim Auditor</th>
                                        <th width="12%">Auditee</th>
                                        <th width="8%">Progress</th>
                                        <th width="8%">Status</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    while ($row = mysqli_fetch_assoc($jadwal)): 
                                        // Get anggota names
                                        $anggota_names = [];
                                        if (!empty($row['anggota_auditor'])) {
                                            $anggota_ids = explode(',', $row['anggota_auditor']);
                                            foreach ($anggota_ids as $aid) {
                                                $q = mysqli_query($koneksi, "SELECT nama_lengkap FROM users WHERE id=" . (int)$aid);
                                                if ($r = mysqli_fetch_assoc($q)) {
                                                    $anggota_names[] = $r['nama_lengkap'];
                                                }
                                            }
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <strong><?php echo $row['kode_audit']; ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-info mb-1"><?php echo $row['kode_unit']; ?></span><br>
                                            <small><?php echo $row['nama_unit']; ?></small>
                                        </td>
                                        <td>
                                            <?php echo $row['tahun_akademik']; ?><br>
                                            <small class="text-muted"><?php echo ucfirst($row['semester']); ?></small>
                                        </td>
                                        <td>
                                            <small>
                                                <i class="bi bi-calendar-check"></i> <?php echo date('d/m/Y', strtotime($row['tanggal_mulai'])); ?><br>
                                                <i class="bi bi-calendar-x"></i> <?php echo date('d/m/Y', strtotime($row['tanggal_selesai'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <strong>Ketua:</strong> <?php echo $row['ketua_nama']; ?><br>
                                            <?php if (count($anggota_names) > 0): ?>
                                            <small class="text-muted">Anggota: <?php echo implode(', ', $anggota_names); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $row['auditee_nama']; ?></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo $row['jumlah_penilaian']; ?> penilaian
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $status_badges = [
                                                'dijadwalkan' => 'primary',
                                                'berlangsung' => 'warning',
                                                'selesai' => 'success',
                                                'dibatalkan' => 'danger'
                                            ];
                                            ?>
                                            <span class="badge bg-<?php echo $status_badges[$row['status']]; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick='viewDetail(<?php echo json_encode($row); ?>)' title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning" onclick='editJadwal(<?php echo json_encode($row); ?>, <?php echo json_encode($anggota_names); ?>)' title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <a href="?delete=<?php echo $row['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Yakin ingin menghapus jadwal ini?')"
                                               title="Hapus">
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

    <!-- Modal Form Jadwal -->
    <div class="modal fade" id="jadwalModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">Tambah Jadwal Audit</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="jadwalId">
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> <strong>Petunjuk:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Kode audit akan digenerate otomatis</li>
                                <li>Pilih ketua auditor dan anggota tim</li>
                                <li>Pastikan tanggal selesai lebih besar dari tanggal mulai</li>
                            </ul>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Unit yang Diaudit <span class="text-danger">*</span></label>
                                <select class="form-select" name="unit_audit_id" id="unit_audit_id" required>
                                    <option value="">Pilih Unit</option>
                                    <?php 
                                    mysqli_data_seek($units, 0);
                                    while ($unit = mysqli_fetch_assoc($units)): 
                                    ?>
                                    <option value="<?php echo $unit['id']; ?>">
                                        [<?php echo $unit['kode_unit']; ?>] <?php echo $unit['nama_unit']; ?> (<?php echo ucfirst($unit['jenis']); ?>)
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Tahun Akademik <span class="text-danger">*</span></label>
                                <select class="form-select" name="tahun_akademik" id="tahun_akademik" required>
                                    <option value="">Pilih Tahun</option>
                                    <option value="2024/2025">2024/2025</option>
                                    <option value="2025/2026" selected>2025/2026</option>
                                    <option value="2026/2027">2026/2027</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Semester <span class="text-danger">*</span></label>
                                <select class="form-select" name="semester" id="semester" required>
                                    <option value="">Pilih</option>
                                    <option value="ganjil">Ganjil</option>
                                    <option value="genap">Genap</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="tanggal_mulai" id="tanggal_mulai" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="tanggal_selesai" id="tanggal_selesai" required>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h6 class="mb-3"><i class="bi bi-people"></i> Tim Auditor</h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ketua Auditor <span class="text-danger">*</span></label>
                                <select class="form-select" name="ketua_auditor" id="ketua_auditor" required>
                                    <option value="">Pilih Ketua Auditor</option>
                                    <?php 
                                    mysqli_data_seek($auditors, 0);
                                    while ($auditor = mysqli_fetch_assoc($auditors)): 
                                    ?>
                                    <option value="<?php echo $auditor['id']; ?>">
                                        <?php echo $auditor['nama_lengkap']; ?> - <?php echo $auditor['unit_kerja']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Anggota Auditor</label>
                                <select class="form-select" name="anggota_auditor[]" id="anggota_auditor" multiple>
                                    <?php 
                                    mysqli_data_seek($auditors, 0);
                                    while ($auditor = mysqli_fetch_assoc($auditors)): 
                                    ?>
                                    <option value="<?php echo $auditor['id']; ?>">
                                        <?php echo $auditor['nama_lengkap']; ?> - <?php echo $auditor['unit_kerja']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                                <small class="text-muted">Tekan Ctrl untuk memilih lebih dari satu</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Auditee (Pimpinan Unit) <span class="text-danger">*</span></label>
                            <select class="form-select" name="auditee_id" id="auditee_id" required>
                                <option value="">Pilih Auditee</option>
                                <?php 
                                mysqli_data_seek($auditees, 0);
                                while ($auditee = mysqli_fetch_assoc($auditees)): 
                                ?>
                                <option value="<?php echo $auditee['id']; ?>">
                                    <?php echo $auditee['nama_lengkap']; ?> - <?php echo $auditee['unit_kerja']; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" name="status" id="status" required>
                                    <option value="dijadwalkan">Dijadwalkan</option>
                                    <option value="berlangsung">Berlangsung</option>
                                    <option value="selesai">Selesai</option>
                                    <option value="dibatalkan">Dibatalkan</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea class="form-control" name="keterangan" id="keterangan" rows="3" 
                                      placeholder="Catatan tambahan atau informasi penting..."></textarea>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Jadwal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Detail -->
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="bi bi-info-circle"></i> Detail Jadwal Audit</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="detailContent"></div>
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        let table;
        
        $(document).ready(function() {
            table = $('#jadwalTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                },
                order: [[1, 'desc']], // Sort by kode audit
                pageLength: 25
            });

            // Initialize Select2
            $('#anggota_auditor').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#jadwalModal'),
                placeholder: 'Pilih anggota tim auditor'
            });

            // Filter
            $('#filterTahun').on('change', function() {
                table.column(3).search(this.value).draw();
            });

            $('#filterSemester').on('change', function() {
                table.column(3).search(this.value).draw();
            });

            $('#filterStatus').on('change', function() {
                table.column(8).search(this.value).draw();
            });
        });

        function resetFilter() {
            $('#filterTahun').val('');
            $('#filterSemester').val('');
            $('#filterStatus').val('');
            table.search('').columns().search('').draw();
        }

        function resetForm() {
            document.getElementById('modalTitle').innerHTML = '<i class="bi bi-plus-circle"></i> Tambah Jadwal Audit';
            document.getElementById('jadwalId').value = '';
            document.getElementById('unit_audit_id').value = '';
            document.getElementById('tahun_akademik').value = '2025/2026';
            document.getElementById('semester').value = '';
            document.getElementById('tanggal_mulai').value = '';
            document.getElementById('tanggal_selesai').value = '';
            document.getElementById('ketua_auditor').value = '';
            $('#anggota_auditor').val(null).trigger('change');
            document.getElementById('auditee_id').value = '';
            document.getElementById('status').value = 'dijadwalkan';
            document.getElementById('keterangan').value = '';
        }

        function editJadwal(jadwal, anggotaNames) {
            document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil"></i> Edit Jadwal Audit';
            document.getElementById('jadwalId').value = jadwal.id;
            document.getElementById('unit_audit_id').value = jadwal.unit_audit_id;
            document.getElementById('tahun_akademik').value = jadwal.tahun_akademik;
            document.getElementById('semester').value = jadwal.semester;
            document.getElementById('tanggal_mulai').value = jadwal.tanggal_mulai;
            document.getElementById('tanggal_selesai').value = jadwal.tanggal_selesai;
            document.getElementById('ketua_auditor').value = jadwal.ketua_auditor;
            
            // Set anggota auditor
            if (jadwal.anggota_auditor) {
                let anggotaIds = jadwal.anggota_auditor.split(',');
                $('#anggota_auditor').val(anggotaIds).trigger('change');
            } else {
                $('#anggota_auditor').val(null).trigger('change');
            }
            
            document.getElementById('auditee_id').value = jadwal.auditee_id;
            document.getElementById('status').value = jadwal.status;
            document.getElementById('keterangan').value = jadwal.keterangan || '';
            
            var modal = new bootstrap.Modal(document.getElementById('jadwalModal'));
            modal.show();
        }

        function viewDetail(jadwal) {
            let statusBadge = {
                'dijadwalkan': 'primary',
                'berlangsung': 'warning',
                'selesai': 'success',
                'dibatalkan': 'danger'
            };
            
            let html = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Informasi Audit</h6>
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Kode Audit</th>
                                <td><span class="badge bg-dark">${jadwal.kode_audit}</span></td>
                            </tr>
                            <tr>
                                <th>Unit</th>
                                <td><strong>${jadwal.nama_unit}</strong></td>
                            </tr>
                            <tr>
                                <th>Periode</th>
                                <td>${jadwal.tahun_akademik} - ${jadwal.semester.toUpperCase()}</td>
                            </tr>
                            <tr>
                                <th>Tanggal</th>
                                <td>
                                    ${new Date(jadwal.tanggal_mulai).toLocaleDateString('id-ID')} s/d 
                                    ${new Date(jadwal.tanggal_selesai).toLocaleDateString('id-ID')}
                                </td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td><span class="badge bg-${statusBadge[jadwal.status]}">${jadwal.status.toUpperCase()}</span></td>
                            </tr>
                            <tr>
                                <th>Progress</th>
                                <td><span class="badge bg-secondary">${jadwal.jumlah_penilaian} penilaian</span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Tim Audit</h6>
                        <div class="timeline-item">
                            <div class="timeline-icon bg-primary">
                                <i class="bi bi-person-check"></i>
                            </div>
                            <div>
                                <strong>Ketua Auditor</strong><br>
                                <small>${jadwal.ketua_nama}</small>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-icon bg-info">
                                <i class="bi bi-people"></i>
                            </div>
                            <div>
                                <strong>Auditee</strong><br>
                                <small>${jadwal.auditee_nama}</small>
                            </div>
                        </div>
                    </div>
                </div>
                ${jadwal.keterangan ? `
                <hr>
                <h6 class="text-muted mb-2">Keterangan</h6>
                <p class="mb-0">${jadwal.keterangan}</p>
                ` : ''}
            `;
            
            document.getElementById('detailContent').innerHTML = html;
            var modal = new bootstrap.Modal(document.getElementById('detailModal'));
            modal.show();
        }
    </script>
    <script src="../assets/js/sidebar-toggle.js"></script>
</body>
</html>