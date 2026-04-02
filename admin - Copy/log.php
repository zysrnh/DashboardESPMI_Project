<?php
require_once '../inc/inc_koneksi.php';
require_once '../inc/inc_fungsi.php';

cek_role(['admin']);

// Pagination
$limit = 50;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Count total
$count_query = "SELECT COUNT(*) as total FROM log_aktivitas";
$total_records = mysqli_fetch_assoc(mysqli_query($koneksi, $count_query))['total'];
$total_pages = ceil($total_records / $limit);

// Get logs
$query = "SELECT la.*, u.nama_lengkap, u.email, u.role
          FROM log_aktivitas la
          JOIN users u ON la.user_id = u.id
          ORDER BY la.created_at DESC
          LIMIT $limit OFFSET $offset";
$logs = mysqli_query($koneksi, $query);

// Stats
$total_logs = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM log_aktivitas"))['total'];
$today_logs = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM log_aktivitas WHERE DATE(created_at) = CURDATE()"))['total'];
$users_today = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(DISTINCT user_id) as total FROM log_aktivitas WHERE DATE(created_at) = CURDATE()"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Aktivitas - E-SPMI</title>
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
                <div class="row mb-4">
                    <div class="col-md-8">
                        <h2 class="mb-0"><i class="bi bi-activity"></i> Log Aktivitas</h2>
                        <p class="text-muted mb-0">Monitoring aktivitas pengguna sistem</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <button class="btn btn-danger" onclick="if(confirm('Hapus log lebih dari 30 hari?')) window.location.href='log_clear.php'">
                            <i class="bi bi-trash"></i> Clear Old Logs
                        </button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted mb-1">Total Logs</h6>
                                        <h3 class="mb-0"><?php echo number_format($total_logs); ?></h3>
                                    </div>
                                    <div class="stat-icon bg-primary">
                                        <i class="bi bi-database"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted mb-1">Hari Ini</h6>
                                        <h3 class="mb-0"><?php echo number_format($today_logs); ?></h3>
                                    </div>
                                    <div class="stat-icon bg-success">
                                        <i class="bi bi-calendar-check"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="text-muted mb-1">User Aktif</h6>
                                        <h3 class="mb-0"><?php echo number_format($users_today); ?></h3>
                                    </div>
                                    <div class="stat-icon bg-info">
                                        <i class="bi bi-people"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logs List -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-list-ul"></i> Aktivitas (<?php echo number_format($total_records); ?> records)</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">ID</th>
                                        <th width="12%">Waktu</th>
                                        <th width="15%">User</th>
                                        <th width="8%">Role</th>
                                        <th width="35%">Aktivitas</th>
                                        <th width="10%">Tabel</th>
                                        <th width="8%">ID Terkait</th>
                                        <th width="7%">IP</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($logs) == 0): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <i class="bi bi-inbox display-4 text-muted"></i>
                                            <p class="text-muted mt-2">Tidak ada log</p>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php while ($log = mysqli_fetch_assoc($logs)): ?>
                                    <tr>
                                        <td><?php echo $log['id'] ?? '-'; ?></td>
                                        <td>
                                            <small><?php echo isset($log['created_at']) ? date('d/m/Y H:i:s', strtotime($log['created_at'])) : '-'; ?></small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($log['nama_lengkap'] ?? 'User'); ?>&size=28&background=667eea&color=fff" 
                                                     class="rounded-circle me-2" width="28" height="28">
                                                <small>
                                                    <strong><?php echo $log['nama_lengkap'] ?? 'Unknown'; ?></strong><br>
                                                    <span class="text-muted" style="font-size: 0.75rem;"><?php echo $log['email'] ?? '-'; ?></span>
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <?php 
                                            $role = $log['role'] ?? 'unknown';
                                            $badge_color = 'secondary';
                                            if ($role == 'admin') $badge_color = 'danger';
                                            elseif ($role == 'auditor') $badge_color = 'primary';
                                            elseif ($role == 'auditee') $badge_color = 'info';
                                            ?>
                                            <span class="badge bg-<?php echo $badge_color; ?>">
                                                <?php echo strtoupper($role); ?>
                                            </span>
                                        </td>
                                        <td><small><?php echo $log['aktivitas'] ?? '-'; ?></small></td>
                                        <td>
                                            <?php if (!empty($log['tabel_terkait'])): ?>
                                            <span class="badge bg-secondary"><?php echo $log['tabel_terkait']; ?></span>
                                            <?php else: ?>
                                            <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php echo $log['id_terkait'] ?? '-'; ?>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?php echo $log['ip_address'] ?? '-'; ?></small>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="card-footer bg-white">
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo ($page-1); ?>">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                <?php 
                                $start = max(1, $page - 2);
                                $end = min($total_pages, $page + 2);
                                
                                for ($i = $start; $i <= $end; $i++): 
                                ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo ($page+1); ?>">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <div class="text-center mt-2">
                            <small class="text-muted">
                                Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?> 
                                (<?php echo number_format($total_records); ?> total records)
                            </small>
                        </div>
                    </div>
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