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
                        <div class="card stat-card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Total Pengguna</h6>
                                        <h3 class="mb-0"><?php echo array_sum($stats_user); ?></h3>
                                        <small class="text-success">
                                            <i class="bi bi-arrow-up"></i> Aktif
                                        </small>
                                    </div>
                                    <div class="stat-icon bg-primary">
                                        <i class="bi bi-people"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Standar -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Total Standar</h6>
                                        <h3 class="mb-0"><?php echo $total_standar; ?></h3>
                                        <small class="text-info">
                                            <i class="bi bi-check-circle"></i> Kategori
                                        </small>
                                    </div>
                                    <div class="stat-icon bg-success">
                                        <i class="bi bi-clipboard-check"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Indikator -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Total Indikator</h6>
                                        <h3 class="mb-0"><?php echo $total_indikator; ?></h3>
                                        <small class="text-warning">
                                            <i class="bi bi-list-check"></i> Aspek
                                        </small>
                                    </div>
                                    <div class="stat-icon bg-warning">
                                        <i class="bi bi-list-task"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Unit -->
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Unit Audit</h6>
                                        <h3 class="mb-0"><?php echo $total_unit; ?></h3>
                                        <small class="text-danger">
                                            <i class="bi bi-building"></i> Fakultas/Prodi
                                        </small>
                                    </div>
                                    <div class="stat-icon bg-danger">
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
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0"><i class="bi bi-calendar-event"></i> Audit Terbaru</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Kode</th>
                                                <th>Unit</th>
                                                <th>Ketua Auditor</th>
                                                <th>Periode</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($audit = mysqli_fetch_assoc($recent_audits)): ?>
                                            <tr>
                                                <td><strong><?php echo $audit['kode_audit']; ?></strong></td>
                                                <td><?php echo $audit['nama_unit']; ?></td>
                                                <td><?php echo $audit['ketua']; ?></td>
                                                <td><?php echo $audit['tahun_akademik'] . ' - ' . ucfirst($audit['semester']); ?></td>
                                                <td>
                                                    <?php
                                                    $badge_class = [
                                                        'dijadwalkan' => 'primary',
                                                        'berlangsung' => 'warning',
                                                        'selesai' => 'success',
                                                        'dibatalkan' => 'danger'
                                                    ];
                                                    ?>
                                                    <span class="badge bg-<?php echo $badge_class[$audit['status']]; ?>">
                                                        <?php echo ucfirst($audit['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="jadwal_detail.php?id=<?php echo $audit['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
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
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Statistik Audit</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="auditChart"></canvas>
                                <div class="mt-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span><i class="bi bi-circle-fill text-primary"></i> Dijadwalkan</span>
                                        <strong><?php echo $stats_audit['dijadwalkan'] ?? 0; ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span><i class="bi bi-circle-fill text-warning"></i> Berlangsung</span>
                                        <strong><?php echo $stats_audit['berlangsung'] ?? 0; ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span><i class="bi bi-circle-fill text-success"></i> Selesai</span>
                                        <strong><?php echo $stats_audit['selesai'] ?? 0; ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span><i class="bi bi-circle-fill text-danger"></i> Dibatalkan</span>
                                        <strong><?php echo $stats_audit['dibatalkan'] ?? 0; ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Log Aktivitas -->
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Aktivitas Terbaru</h5>
                            </div>
                            <div class="card-body">
                                <div class="activity-timeline">
                                    <?php while ($log = mysqli_fetch_assoc($recent_logs)): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon bg-primary">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <div class="activity-content">
                                            <div class="d-flex justify-content-between">
                                                <h6 class="mb-0"><?php echo $log['nama_lengkap']; ?></h6>
                                                <small class="text-muted">
                                                    <?php 
                                                    $time_diff = time() - strtotime($log['created_at']);
                                                    if ($time_diff < 60) {
                                                        echo $time_diff . ' detik lalu';
                                                    } elseif ($time_diff < 3600) {
                                                        echo floor($time_diff / 60) . ' menit lalu';
                                                    } elseif ($time_diff < 86400) {
                                                        echo floor($time_diff / 3600) . ' jam lalu';
                                                    } else {
                                                        echo floor($time_diff / 86400) . ' hari lalu';
                                                    }
                                                    ?>
                                                </small>
                                            </div>
                                            <p class="mb-0 text-muted"><?php echo $log['aktivitas']; ?></p>
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
                        'rgba(13, 110, 253, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(25, 135, 84, 0.8)',
                        'rgba(220, 53, 69, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
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