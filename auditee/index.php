<?php
require_once '../inc/inc_koneksi.php';
require_once '../inc/inc_fungsi.php';

cek_role(['auditee']);

$user_id = $_SESSION['user_id'];

// Get audit untuk unit yang dipimpin user ini
$query = "SELECT ja.*, 
          ua.nama_unit, ua.jenis, ua.kode_unit,
          u1.nama_lengkap as ketua_nama,
          (SELECT COUNT(*) FROM penilaian WHERE jadwal_audit_id=ja.id) as jumlah_penilaian,
          (SELECT COUNT(*) FROM indikator WHERE status='aktif') as total_indikator,
          (SELECT AVG(skor) FROM penilaian WHERE jadwal_audit_id=ja.id) as rata_skor
          FROM jadwal_audit ja
          JOIN unit_audit ua ON ja.unit_audit_id = ua.id
          JOIN users u1 ON ja.ketua_auditor = u1.id
          WHERE ja.auditee_id = $user_id
          ORDER BY ja.tanggal_mulai DESC";
$audit = mysqli_query($koneksi, $query);

// Statistik
$stats = [
    'total' => mysqli_num_rows($audit),
    'selesai' => mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM jadwal_audit WHERE auditee_id=$user_id AND status='selesai'"))['total'],
    'berlangsung' => mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM jadwal_audit WHERE auditee_id=$user_id AND status='berlangsung'"))['total'],
    'dijadwalkan' => mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM jadwal_audit WHERE auditee_id=$user_id AND status='dijadwalkan'"))['total']
];

// Get user info
$user_info = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id=$user_id"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Auditee - E-SPMI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>
        .score-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            border: 5px solid;
        }
        .score-excellent { border-color: #198754; color: #198754; }
        .score-good { border-color: #0d6efd; color: #0d6efd; }
        .score-fair { border-color: #ffc107; color: #ffc107; }
        .score-poor { border-color: #dc3545; color: #dc3545; }
    </style>
</head>
<body>
    <?php include 'inc_sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'inc_navbar.php'; ?>
        
        <div class="content-wrapper">
            <div class="container-fluid">
                <!-- Header -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <h2 class="mb-0">Dashboard Auditee</h2>
                        <p class="text-muted">Selamat datang, <?php echo $_SESSION['nama_lengkap']; ?></p>
                        <p class="mb-0">
                            <span class="badge bg-info"><?php echo $user_info['unit_kerja']; ?></span>
                            <span class="badge bg-secondary"><?php echo $user_info['jabatan']; ?></span>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="badge bg-primary fs-6">
                            <i class="bi bi-calendar"></i> <?php echo tanggal_indo(date('Y-m-d')); ?>
                        </span>
                    </div>
                </div>

                <!-- Statistik -->
                <div class="row g-4 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Total Audit</h6>
                                        <h3 class="mb-0"><?php echo $stats['total']; ?></h3>
                                    </div>
                                    <div class="stat-icon bg-primary">
                                        <i class="bi bi-clipboard-data"></i>
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

                <!-- Daftar Audit -->
                <div class="row">
                    <?php 
                    mysqli_data_seek($audit, 0);
                    if (mysqli_num_rows($audit) == 0): 
                    ?>
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-inbox display-1 text-muted"></i>
                                <h5 class="mt-3">Belum Ada Audit</h5>
                                <p class="text-muted">Unit Anda belum dijadwalkan untuk diaudit.</p>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <?php while ($row = mysqli_fetch_assoc($audit)): 
                        $progress = $row['total_indikator'] > 0 ? ($row['jumlah_penilaian'] / $row['total_indikator']) * 100 : 0;
                        
                        // Determine score category
                        $score_class = '';
                        $score_label = '';
                        if ($row['rata_skor'] >= 3.5) {
                            $score_class = 'score-excellent';
                            $score_label = 'Sangat Baik';
                        } elseif ($row['rata_skor'] >= 3.0) {
                            $score_class = 'score-good';
                            $score_label = 'Baik';
                        } elseif ($row['rata_skor'] >= 2.0) {
                            $score_class = 'score-fair';
                            $score_label = 'Cukup';
                        } else {
                            $score_class = 'score-poor';
                            $score_label = 'Perlu Perbaikan';
                        }
                    ?>
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0"><?php echo $row['kode_audit']; ?></h5>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar-event"></i>
                                            <?php echo date('d/m/Y', strtotime($row['tanggal_mulai'])); ?> - 
                                            <?php echo date('d/m/Y', strtotime($row['tanggal_selesai'])); ?>
                                        </small>
                                    </div>
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
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-center mb-3">
                                    <div class="col-md-5">
                                        <?php if ($row['status'] == 'selesai' && $row['rata_skor']): ?>
                                        <div class="score-circle <?php echo $score_class; ?>">
                                            <div class="fs-2 fw-bold"><?php echo number_format($row['rata_skor'], 2); ?></div>
                                            <small>dari 4.00</small>
                                        </div>
                                        <p class="text-center mt-2 mb-0 fw-bold"><?php echo $score_label; ?></p>
                                        <?php else: ?>
                                        <div class="score-circle border-secondary text-secondary">
                                            <i class="bi bi-hourglass-split fs-1"></i>
                                            <small>Proses Audit</small>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-7">
                                        <div class="mb-3">
                                            <small class="text-muted d-block mb-1">Unit</small>
                                            <span class="badge bg-info"><?php echo $row['kode_unit']; ?></span>
                                            <p class="mb-0"><small><?php echo $row['nama_unit']; ?></small></p>
                                        </div>
                                        <div class="mb-3">
                                            <small class="text-muted d-block mb-1">Periode</small>
                                            <strong><?php echo $row['tahun_akademik']; ?></strong> - 
                                            <?php echo ucfirst($row['semester']); ?>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block mb-1">Auditor</small>
                                            <small><?php echo $row['ketua_nama']; ?></small>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <small class="text-muted">Progress Penilaian</small>
                                        <small class="text-muted">
                                            <strong><?php echo $row['jumlah_penilaian']; ?></strong> / 
                                            <?php echo $row['total_indikator']; ?> Indikator
                                        </small>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar <?php echo $progress >= 100 ? 'bg-success' : 'bg-primary'; ?>" 
                                             role="progressbar" 
                                             style="width: <?php echo $progress; ?>%">
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <a href="hasil.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">
                                        <i class="bi bi-eye"></i> Lihat Detail Hasil Audit
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>
        <?php include 'footer.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebar-toggle.js"></script>
</body>
</html>