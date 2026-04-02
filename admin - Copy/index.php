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
    <meta name="description" content="Dashboard Admin E-SPMI - Sistem Penjaminan Mutu Internal">
    <title>Dashboard Admin - E-SPMI</title>
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
                        <h1 class="page-title">Dashboard</h1>
                        <p class="page-subtitle">Selamat datang kembali, <strong><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></strong></p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div style="background:white;border:1px solid #e8edf5;border-radius:10px;padding:8px 14px;font-size:0.82rem;color:#64748b;display:flex;align-items:center;gap:8px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                            <i class="bi bi-calendar3" style="color:#4f46e5;"></i>
                            <strong style="color:#1e293b;"><?php echo tanggal_indo(date('Y-m-d')); ?></strong>
                        </div>
                        <a href="jadwal.php" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-lg"></i> Jadwal Baru
                        </a>
                    </div>
                </div>

                <!-- Statistik Cards -->
                <div class="row g-3 mb-4">
                    <!-- Total Users -->
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card stat-card-primary">
                            <div class="stat-card-icon"><i class="bi bi-people-fill"></i></div>
                            <div class="stat-label">Total Pengguna</div>
                            <div class="stat-value"><?php echo array_sum($stats_user); ?></div>
                            <div class="stat-sub"><i class="bi bi-person-check me-1"></i><?php echo $stats_user['admin'] ?? 0; ?> Admin &bull; <?php echo $stats_user['auditor'] ?? 0; ?> Auditor &bull; <?php echo $stats_user['auditee'] ?? 0; ?> Auditee</div>
                        </div>
                    </div>

                    <!-- Total Standar -->
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card stat-card-info">
                            <div class="stat-card-icon"><i class="bi bi-clipboard2-check-fill"></i></div>
                            <div class="stat-label">Standar SPMI</div>
                            <div class="stat-value"><?php echo $total_standar; ?></div>
                            <div class="stat-sub"><i class="bi bi-check-circle me-1"></i>Standar aktif terdaftar</div>
                        </div>
                    </div>

                    <!-- Total Indikator -->
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card stat-card-warning">
                            <div class="stat-card-icon"><i class="bi bi-list-check"></i></div>
                            <div class="stat-label">Total Indikator</div>
                            <div class="stat-value"><?php echo $total_indikator; ?></div>
                            <div class="stat-sub"><i class="bi bi-stars me-1"></i>Aspek penilaian aktif</div>
                        </div>
                    </div>

                    <!-- Total Unit -->
                    <div class="col-xl-3 col-md-6">
                        <div class="stat-card stat-card-success">
                            <div class="stat-card-icon"><i class="bi bi-building-fill"></i></div>
                            <div class="stat-label">Unit Audit</div>
                            <div class="stat-value"><?php echo $total_unit; ?></div>
                            <div class="stat-sub"><i class="bi bi-diagram-3 me-1"></i>Fakultas &amp; Program Studi</div>
                        </div>
                    </div>
                </div>

                <!-- Audit Status Summary -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="stat-card-white text-center">
                            <div class="stat-icon-wrap mx-auto mb-2" style="background:#eff6ff;color:#2563eb;"><i class="bi bi-calendar-event-fill"></i></div>
                            <div style="font-size:1.5rem;font-weight:800;color:#1e293b;"><?php echo $stats_audit['dijadwalkan'] ?? 0; ?></div>
                            <div style="font-size:0.75rem;color:#64748b;font-weight:600;">Dijadwalkan</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card-white text-center">
                            <div class="stat-icon-wrap mx-auto mb-2" style="background:#fefce8;color:#ca8a04;"><i class="bi bi-hourglass-split"></i></div>
                            <div style="font-size:1.5rem;font-weight:800;color:#1e293b;"><?php echo $stats_audit['berlangsung'] ?? 0; ?></div>
                            <div style="font-size:0.75rem;color:#64748b;font-weight:600;">Berlangsung</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card-white text-center">
                            <div class="stat-icon-wrap mx-auto mb-2" style="background:#ecfdf5;color:#059669;"><i class="bi bi-check-circle-fill"></i></div>
                            <div style="font-size:1.5rem;font-weight:800;color:#1e293b;"><?php echo $stats_audit['selesai'] ?? 0; ?></div>
                            <div style="font-size:0.75rem;color:#64748b;font-weight:600;">Selesai</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card-white text-center">
                            <div class="stat-icon-wrap mx-auto mb-2" style="background:#fef2f2;color:#dc2626;"><i class="bi bi-x-circle-fill"></i></div>
                            <div style="font-size:1.5rem;font-weight:800;color:#1e293b;"><?php echo $stats_audit['dibatalkan'] ?? 0; ?></div>
                            <div style="font-size:0.75rem;color:#64748b;font-weight:600;">Dibatalkan</div>
                        </div>
                    </div>
                </div>

                <!-- Audit Terbaru + Chart -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-calendar-event-fill me-2" style="color:#4f46e5;"></i> Audit Terbaru</span>
                                <a href="jadwal.php" class="btn btn-sm" style="background:#f0f4ff;color:#4f46e5;font-size:0.78rem;font-weight:600;">Lihat Semua <i class="bi bi-arrow-right"></i></a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Kode</th>
                                                <th>Unit</th>
                                                <th>Auditor</th>
                                                <th>Periode</th>
                                                <th>Status</th>
                                                <th class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($audit = mysqli_fetch_assoc($recent_audits)): ?>
                                            <tr>
                                                <td><span style="font-weight:700;color:#4f46e5;"><?php echo htmlspecialchars($audit['kode_audit']); ?></span></td>
                                                <td><?php echo htmlspecialchars($audit['nama_unit']); ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($audit['ketua']); ?>&background=4f46e5&color=fff&size=28"
                                                             style="width:28px;height:28px;border-radius:50%;">
                                                        <span style="font-size:0.85rem;"><?php echo htmlspecialchars($audit['ketua']); ?></span>
                                                    </div>
                                                </td>
                                                <td><span style="font-size:0.82rem;color:#64748b;"><?php echo htmlspecialchars($audit['tahun_akademik']); ?></span></td>
                                                <td>
                                                    <?php
                                                    $badge_styles = [
                                                        'dijadwalkan' => 'background:#eff6ff;color:#2563eb;',
                                                        'berlangsung' => 'background:#fefce8;color:#ca8a04;',
                                                        'selesai'     => 'background:#ecfdf5;color:#059669;',
                                                        'dibatalkan'  => 'background:#fef2f2;color:#dc2626;'
                                                    ];
                                                    $bs = $badge_styles[$audit['status']] ?? 'background:#f1f5f9;color:#64748b;';
                                                    ?>
                                                    <span style="<?php echo $bs; ?> padding:3px 10px;border-radius:99px;font-size:0.72rem;font-weight:700;">
                                                        <?php echo ucfirst($audit['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="jadwal.php" class="btn btn-sm" style="background:#f0f4ff;color:#4f46e5;border-radius:8px;">
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
                        <div class="card h-100">
                            <div class="card-header d-flex align-items-center">
                                <i class="bi bi-pie-chart-fill me-2" style="color:#4f46e5;"></i> Distribusi Audit
                            </div>
                            <div class="card-body d-flex flex-column justify-content-center">
                                <div style="height:200px;position:relative;">
                                    <canvas id="auditChart"></canvas>
                                </div>
                                <div class="mt-3">
                                    <?php
                                    $legend = [
                                        ['label'=>'Dijadwalkan','key'=>'dijadwalkan','color'=>'#4f46e5'],
                                        ['label'=>'Berlangsung','key'=>'berlangsung','color'=>'#f59e0b'],
                                        ['label'=>'Selesai',    'key'=>'selesai',    'color'=>'#10b981'],
                                        ['label'=>'Dibatalkan', 'key'=>'dibatalkan', 'color'=>'#ef4444'],
                                    ];
                                    foreach ($legend as $lg): ?>
                                    <div class="d-flex align-items-center justify-content-between mb-1" style="font-size:0.8rem;">
                                        <div class="d-flex align-items-center gap-2">
                                            <span style="width:8px;height:8px;border-radius:2px;background:<?php echo $lg['color']; ?>;display:inline-block;"></span>
                                            <span style="color:#64748b;"><?php echo $lg['label']; ?></span>
                                        </div>
                                        <strong style="color:#1e293b;"><?php echo $stats_audit[$lg['key']] ?? 0; ?></strong>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Aktivitas Terbaru -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-clock-history me-2" style="color:#4f46e5;"></i> Aktivitas Terbaru</span>
                                <a href="log.php" class="btn btn-sm" style="background:#f0f4ff;color:#4f46e5;font-size:0.78rem;font-weight:600;">Lihat Semua <i class="bi bi-arrow-right"></i></a>
                            </div>
                            <div class="card-body" style="padding:0 1.5rem;">
                                <?php while ($log = mysqli_fetch_assoc($recent_logs)):
                                    $td = time() - strtotime($log['created_at']);
                                    if ($td < 60) $ago = $td . ' detik lalu';
                                    elseif ($td < 3600) $ago = floor($td/60) . ' menit lalu';
                                    elseif ($td < 86400) $ago = floor($td/3600) . ' jam lalu';
                                    else $ago = floor($td/86400) . ' hari lalu';
                                ?>
                                <div class="activity-item">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($log['nama_lengkap']); ?>&background=4f46e5&color=fff&size=40"
                                         class="activity-avatar" alt="avatar">
                                    <div class="activity-content flex-grow-1">
                                        <p class="activity-title"><?php echo htmlspecialchars($log['nama_lengkap']); ?></p>
                                        <p class="activity-desc"><?php echo htmlspecialchars($log['aktivitas']); ?></p>
                                    </div>
                                    <span class="activity-time"><?php echo $ago; ?></span>
                                </div>
                                <?php endwhile; ?>
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