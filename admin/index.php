<?php
require_once '../inc/inc_koneksi.php';
require_once '../inc/inc_fungsi.php';

cek_role(['admin']);

// Ambil statistik
$query_user = "SELECT role, COUNT(*) as total FROM users WHERE status='aktif' GROUP BY role";
$result_user = mysqli_query($koneksi, $query_user);
$stats_user = [];
while ($row = mysqli_fetch_assoc($result_user)) {
    $stats_user[$row['role']] = $row['total'];
}

$total_standar = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM standar WHERE status='aktif'"))['total'];
$total_indikator = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM indikator WHERE status='aktif'"))['total'];
$total_unit = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM unit_audit WHERE status='aktif'"))['total'];

$query_audit = "SELECT status, COUNT(*) as total FROM jadwal_audit GROUP BY status";
$result_audit = mysqli_query($koneksi, $query_audit);
$stats_audit = [];
while ($row = mysqli_fetch_assoc($result_audit)) {
    $stats_audit[$row['status']] = $row['total'];
}

// Ambil audit terbaru
$query_recent = "SELECT ja.*, ua.nama_unit, u.nama_lengkap as ketua 
                 FROM jadwal_audit ja 
                 JOIN unit_audit ua ON ja.unit_audit_id = ua.id 
                 JOIN users u ON ja.ketua_auditor = u.id 
                 ORDER BY ja.created_at DESC LIMIT 5";
$recent_audits = mysqli_query($koneksi, $query_recent);

// Ambil aktivitas terbaru
$query_log = "SELECT la.*, u.nama_lengkap 
              FROM log_aktivitas la 
              JOIN users u ON la.user_id = u.id 
              ORDER BY la.created_at DESC LIMIT 10";
$recent_logs = mysqli_query($koneksi, $query_log);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - E-SPMI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
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
                        <h2 class="mb-0">Dashboard Administrator</h2>
                        <p class="text-muted">Selamat datang, <?php echo $_SESSION['nama_lengkap']; ?></p>
                    </div>
                    <div>
                        <span class="badge bg-primary fs-6">
                            <i class="bi bi-calendar"></i> <?php echo tanggal_indo(date('Y-m-d')); ?>
                        </span>
                    </div>
                </div>

                <!-- Statistik Cards -->
                <div class="row g-4 mb-4">
                    <!-- Total Users -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted fw-normal mb-2">Total Pengguna</h6>
                                        <h3 class="fw-bold mb-0"><?php echo array_sum($stats_user); ?></h3>
                                        <div class="mt-2">
                                            <span class="badge bg-success-subtle text-success px-2 py-1" style="font-size: 0.7rem;">
                                                <i class="bi bi-person-check-fill me-1"></i> Aktif
                                            </span>
                                        </div>
                                    </div>
                                    <div class="stat-icon bg-primary shadow-sm">
                                        <i class="bi bi-people"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Standar -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted fw-normal mb-2">Total Standar</h6>
                                        <h3 class="fw-bold mb-0"><?php echo $total_standar; ?></h3>
                                        <div class="mt-2">
                                            <span class="badge bg-info-subtle text-info px-2 py-1" style="font-size: 0.7rem;">
                                                <i class="bi bi-check-circle-fill me-1"></i> Kategori
                                            </span>
                                        </div>
                                    </div>
                                    <div class="stat-icon bg-info shadow-sm text-white">
                                        <i class="bi bi-clipboard-check"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Indikator -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted fw-normal mb-2">Total Indikator</h6>
                                        <h3 class="fw-bold mb-0"><?php echo $total_indikator; ?></h3>
                                        <div class="mt-2">
                                            <span class="badge bg-warning-subtle text-warning px-2 py-1" style="font-size: 0.7rem;">
                                                <i class="bi bi-list-stars me-1"></i> Aspek
                                            </span>
                                        </div>
                                    </div>
                                    <div class="stat-icon bg-warning shadow-sm text-white">
                                        <i class="bi bi-list-task"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Unit -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted fw-normal mb-2">Unit Audit</h6>
                                        <h3 class="fw-bold mb-0"><?php echo $total_unit; ?></h3>
                                        <div class="mt-2">
                                            <span class="badge bg-danger-subtle text-danger px-2 py-1" style="font-size: 0.7rem;">
                                                <i class="bi bi-building-fill me-1"></i> Fakultas/Prodi
                                            </span>
                                        </div>
                                    </div>
                                    <div class="stat-icon bg-danger shadow-sm text-white">
                                        <i class="bi bi-diagram-3"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Audit -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-8">
                        <div class="card border-0">
                            <div class="card-header bg-white py-3 border-0">
                                <h5 class="mb-0 fw-bold"><i class="bi bi-calendar-event me-2"></i> Audit Terbaru</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="border-0">Kode</th>
                                                <th class="border-0">Unit</th>
                                                <th class="border-0">Auditor</th>
                                                <th class="border-0">Periode</th>
                                                <th class="border-0">Status</th>
                                                <th class="border-0 text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($audit = mysqli_fetch_assoc($recent_audits)): ?>
                                            <tr>
                                                <td class="fw-semibold text-primary"><?php echo $audit['kode_audit']; ?></td>
                                                <td><?php echo $audit['nama_unit']; ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($audit['ketua']); ?>&background=random&size=24" class="rounded-circle me-2">
                                                        <span><?php echo $audit['ketua']; ?></span>
                                                    </div>
                                                </td>
                                                <td class="small"><?php echo $audit['tahun_akademik']; ?></td>
                                                <td>
                                                    <?php
                                                    $badge_class = [
                                                        'dijadwalkan' => 'bg-primary-subtle text-primary',
                                                        'berlangsung' => 'bg-warning-subtle text-dark',
                                                        'selesai' => 'bg-success-subtle text-success',
                                                        'dibatalkan' => 'bg-danger-subtle text-danger'
                                                    ];
                                                    ?>
                                                    <span class="badge <?php echo $badge_class[$audit['status']]; ?> px-2 py-1">
                                                        <?php echo ucfirst($audit['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="jadwal_detail.php?id=<?php echo $audit['id']; ?>" class="btn btn-sm btn-icon btn-light rounded-pill">
                                                        <i class="bi bi-arrow-right"></i>
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

                    <div class="col-lg-4">
                        <div class="card border-0">
                            <div class="card-header bg-white py-3 border-0">
                                <h5 class="mb-0 fw-bold"><i class="bi bi-pie-chart me-2"></i> Statistik Audit</h5>
                            </div>
                            <div class="card-body">
                                <div style="height: 220px; position: relative;">
                                    <canvas id="auditChart"></canvas>
                                </div>
                                <div class="mt-4">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="p-2 border rounded-3 bg-light-subtle">
                                                <small class="text-muted d-block mb-1">Dijadwalkan</small>
                                                <h6 class="mb-0 fw-bold text-primary"><?php echo $stats_audit['dijadwalkan'] ?? 0; ?></h6>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-2 border rounded-3 bg-light-subtle">
                                                <small class="text-muted d-block mb-1">Berlangsung</small>
                                                <h6 class="mb-0 fw-bold text-warning"><?php echo $stats_audit['berlangsung'] ?? 0; ?></h6>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-2 border rounded-3 bg-light-subtle">
                                                <small class="text-muted d-block mb-1">Selesai</small>
                                                <h6 class="mb-0 fw-bold text-success"><?php echo $stats_audit['selesai'] ?? 0; ?></h6>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-2 border rounded-3 bg-light-subtle">
                                                <small class="text-muted d-block mb-1">Dibatalkan</small>
                                                <h6 class="mb-0 fw-bold text-danger"><?php echo $stats_audit['dibatalkan'] ?? 0; ?></h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Log Aktivitas -->
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0">
                            <div class="card-header bg-white py-3 border-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2"></i> Aktivitas Terbaru</h5>
                                    <a href="log.php" class="btn btn-sm btn-light text-primary fw-semibold">Lihat Semua</a>
                                </div>
                            </div>
                            <div class="card-body px-0 py-0">
                                <div class="activity-timeline px-4 py-3">
                                    <?php while ($log = mysqli_fetch_assoc($recent_logs)): ?>
                                    <div class="activity-item d-flex mb-4">
                                        <div class="me-3">
                                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($log['nama_lengkap']); ?>&background=3b82f6&color=fff&size=40" class="rounded-circle shadow-sm">
                                        </div>
                                        <div class="activity-content flex-grow-1 border-bottom pb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <h6 class="mb-0 fw-bold"><?php echo $log['nama_lengkap']; ?></h6>
                                                <small class="text-muted bg-light px-2 py-1 rounded">
                                                    <?php 
                                                    $time_diff = time() - strtotime($log['created_at']);
                                                    if ($time_diff < 60) echo $time_diff . ' detik lalu';
                                                    elseif ($time_diff < 3600) echo floor($time_diff / 60) . ' menit lalu';
                                                    elseif ($time_diff < 86400) echo floor($time_diff / 3600) . ' jam lalu';
                                                    else echo floor($time_diff / 86400) . ' hari lalu';
                                                    ?>
                                                </small>
                                            </div>
                                            <p class="mb-0 text-secondary" style="font-size: 0.9rem;"><?php echo $log['aktivitas']; ?></p>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
    <script>
        // Chart Audit
        const ctx = document.getElementById('auditChart');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Dijadwalkan', 'Berlangsung', 'Selesai', 'Dibatalkan'],
                datasets: [{
                    data: [
                        <?php echo $stats_audit['dijadwalkan'] ?? 0; ?>,
                        <?php echo $stats_audit['berlangsung'] ?? 0; ?>,
                        <?php echo $stats_audit['selesai'] ?? 0; ?>,
                        <?php echo $stats_audit['dibatalkan'] ?? 0; ?>
                    ],
                    backgroundColor: [
                        '#3b82f6', // primary (blue)
                        '#f59e0b', // warning (amber)
                        '#10b981', // success (emerald)
                        '#ef4444'  // danger (rose)
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
    <script src="../assets/js/sidebar-toggle.js"></script>
    
</body>
</html>