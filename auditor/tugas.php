<?php
require_once '../inc/inc_koneksi.php';
require_once '../inc/inc_fungsi.php';

cek_role(['auditor']);

$user_id = $_SESSION['user_id'];

// Filter status
$filter_status = isset($_GET['status']) ? esc($_GET['status']) : '';

// Build WHERE
$where = "(ja.ketua_auditor = $user_id OR FIND_IN_SET($user_id, ja.anggota_auditor))";
if ($filter_status != '' && $filter_status != 'semua') {
    $where .= " AND ja.status = '$filter_status'";
}

// Get audit assignments
$query = "SELECT ja.*, 
          ua.nama_unit, ua.jenis, ua.kode_unit,
          u_auditee.nama_lengkap as auditee_nama, u_auditee.email as auditee_email,
          u_ketua.nama_lengkap as ketua_nama,
          (SELECT COUNT(*) FROM penilaian WHERE jadwal_audit_id=ja.id) as jumlah_penilaian,
          (SELECT COUNT(*) FROM indikator WHERE status='aktif') as total_indikator,
          (SELECT AVG(skor) FROM penilaian WHERE jadwal_audit_id=ja.id) as rata_skor
          FROM jadwal_audit ja
          JOIN unit_audit ua ON ja.unit_audit_id = ua.id
          JOIN users u_auditee ON ja.auditee_id = u_auditee.id
          JOIN users u_ketua ON ja.ketua_auditor = u_ketua.id
          WHERE $where
          ORDER BY 
            CASE ja.status 
              WHEN 'berlangsung' THEN 1 
              WHEN 'dijadwalkan' THEN 2 
              WHEN 'selesai' THEN 3 
            END,
            ja.tanggal_mulai ASC";
$audit = mysqli_query($koneksi, $query);

// Statistics
$stats = [
    'total' => mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM jadwal_audit WHERE (ketua_auditor=$user_id OR FIND_IN_SET($user_id, anggota_auditor))"))['total'],
    'dijadwalkan' => mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM jadwal_audit WHERE (ketua_auditor=$user_id OR FIND_IN_SET($user_id, anggota_auditor)) AND status='dijadwalkan'"))['total'],
    'berlangsung' => mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM jadwal_audit WHERE (ketua_auditor=$user_id OR FIND_IN_SET($user_id, anggota_auditor)) AND status='berlangsung'"))['total'],
    'selesai' => mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM jadwal_audit WHERE (ketua_auditor=$user_id OR FIND_IN_SET($user_id, anggota_auditor)) AND status='selesai'"))['total']
];

// Get user info
$user_info = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id=$user_id"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tugas Audit Saya - E-SPMI</title>
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
                        <h2 class="mb-0"><i class="bi bi-clipboard-check"></i> Tugas Audit Saya</h2>
                        <p class="text-muted mb-0">Daftar penugasan audit yang harus Anda lakukan</p>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Total Penugasan</h6>
                                        <h3 class="mb-0"><?php echo $stats['total']; ?></h3>
                                    </div>
                                    <div class="stat-icon bg-primary">
                                        <i class="bi bi-clipboard-check"></i>
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
                        <form method="GET" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Filter Status</label>
                                <select name="status" class="form-select" onchange="this.form.submit()">
                                    <option value="semua" <?php echo $filter_status == 'semua' ? 'selected' : ''; ?>>Semua Status</option>
                                    <option value="dijadwalkan" <?php echo $filter_status == 'dijadwalkan' ? 'selected' : ''; ?>>Dijadwalkan</option>
                                    <option value="berlangsung" <?php echo $filter_status == 'berlangsung' ? 'selected' : ''; ?>>Berlangsung</option>
                                    <option value="selesai" <?php echo $filter_status == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                </select>
                            </div>
                            <div class="col-md-8 text-end">
                                <a href="tugas.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> Reset Filter
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Daftar Audit -->
                <div class="row">
                    <?php if (mysqli_num_rows($audit) == 0): ?>
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-inbox display-1 text-muted"></i>
                                <h5 class="mt-3">Belum Ada Penugasan Audit</h5>
                                <p class="text-muted">
                                    <?php if ($filter_status != ''): ?>
                                    Tidak ada audit dengan status "<?php echo ucfirst($filter_status); ?>"
                                    <?php else: ?>
                                    Anda belum ditugaskan untuk melakukan audit.
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <?php while ($row = mysqli_fetch_assoc($audit)): 
                        $progress = $row['total_indikator'] > 0 ? ($row['jumlah_penilaian'] / $row['total_indikator']) * 100 : 0;
                        
                        // Cek apakah user adalah ketua atau anggota
                        $is_ketua = ($row['ketua_auditor'] == $user_id);
                        $role_text = $is_ketua ? 'Ketua Auditor' : 'Anggota Tim';
                        
                        // Get anggota tim
                        $anggota_ids = !empty($row['anggota_auditor']) ? explode(',', $row['anggota_auditor']) : [];
                        $anggota_list = [];
                        if (count($anggota_ids) > 0) {
                            $anggota_query = mysqli_query($koneksi, "SELECT nama_lengkap FROM users WHERE id IN (" . implode(',', $anggota_ids) . ")");
                            while ($anggota = mysqli_fetch_assoc($anggota_query)) {
                                $anggota_list[] = $anggota['nama_lengkap'];
                            }
                        }
                    ?>
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0"><?php echo $row['kode_audit']; ?></h5>
                                        <small class="text-muted">
                                            <span class="badge bg-info"><?php echo $row['kode_unit']; ?></span>
                                            <?php echo $row['nama_unit']; ?>
                                        </small>
                                    </div>
                                    <?php
                                    $status_badges = [
                                        'dijadwalkan' => 'primary',
                                        'berlangsung' => 'warning',
                                        'selesai' => 'success'
                                    ];
                                    ?>
                                    <span class="badge bg-<?php echo $status_badges[$row['status']]; ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Role Badge -->
                                <div class="mb-3">
                                    <span class="badge bg-<?php echo $is_ketua ? 'danger' : 'secondary'; ?>">
                                        <i class="bi bi-<?php echo $is_ketua ? 'star-fill' : 'person'; ?>"></i> 
                                        <?php echo $role_text; ?>
                                    </span>
                                </div>

                                <!-- Progress Penilaian -->
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

                                <!-- Info Cards -->
                                <div class="row text-center mb-3">
                                    <div class="col-6">
                                        <div class="border rounded p-2">
                                            <small class="text-muted d-block">Periode</small>
                                            <strong><?php echo $row['tahun_akademik']; ?></strong><br>
                                            <small><?php echo ucfirst($row['semester']); ?></small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="border rounded p-2">
                                            <small class="text-muted d-block">Rata-rata Skor</small>
                                            <strong class="fs-4 <?php 
                                                if ($row['rata_skor'] >= 3.5) echo 'text-success';
                                                elseif ($row['rata_skor'] >= 3.0) echo 'text-primary';
                                                elseif ($row['rata_skor'] >= 2.0) echo 'text-warning';
                                                else echo 'text-danger';
                                            ?>">
                                                <?php echo $row['rata_skor'] ? number_format($row['rata_skor'], 2) : '-'; ?>
                                            </strong>
                                        </div>
                                    </div>
                                </div>

                                <!-- Jadwal -->
                                <div class="mb-3">
                                    <small class="text-muted d-block mb-1">
                                        <i class="bi bi-calendar-event"></i> Jadwal Audit
                                    </small>
                                    <small>
                                        <?php echo date('d/m/Y', strtotime($row['tanggal_mulai'])); ?> - 
                                        <?php echo date('d/m/Y', strtotime($row['tanggal_selesai'])); ?>
                                    </small>
                                </div>

                                <!-- Auditee -->
                                <div class="mb-3">
                                    <small class="text-muted d-block mb-1">
                                        <i class="bi bi-person-circle"></i> Auditee
                                    </small>
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($row['auditee_nama']); ?>&size=28&background=667eea&color=fff" 
                                             class="rounded-circle me-2" width="28" height="28">
                                        <small>
                                            <strong><?php echo $row['auditee_nama']; ?></strong><br>
                                            <span class="text-muted" style="font-size: 0.75rem;"><?php echo $row['auditee_email']; ?></span>
                                        </small>
                                    </div>
                                </div>

                                <!-- Tim Auditor -->
                                <div class="mb-3">
                                    <small class="text-muted d-block mb-1">
                                        <i class="bi bi-people"></i> Tim Auditor
                                    </small>
                                    <small>
                                        <strong>Ketua:</strong> <?php echo $row['ketua_nama']; ?>
                                        <?php if (count($anggota_list) > 0): ?>
                                        <br><strong>Anggota:</strong> <?php echo implode(', ', $anggota_list); ?>
                                        <?php endif; ?>
                                    </small>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-grid gap-2">
                                    <?php if ($row['status'] == 'selesai'): ?>
                                    <a href="hasil.php?id=<?php echo $row['id']; ?>" class="btn btn-success">
                                        <i class="bi bi-eye"></i> Lihat Hasil Audit
                                    </a>
                                    <?php else: ?>
                                    <a href="penilaian.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">
                                        <i class="bi bi-pencil-square"></i> 
                                        <?php echo $progress > 0 ? 'Lanjutkan Penilaian' : 'Mulai Penilaian'; ?>
                                    </a>
                                    <?php endif; ?>
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