<?php
require_once '../inc/inc_koneksi.php';
require_once '../inc/inc_fungsi.php';

cek_role(['auditee']);

$user_id = $_SESSION['user_id'];

// Get riwayat audit unit
$query = "SELECT ja.*, 
          ua.nama_unit, ua.kode_unit,
          u1.nama_lengkap as ketua_nama,
          (SELECT COUNT(*) FROM penilaian WHERE jadwal_audit_id=ja.id) as jumlah_penilaian,
          (SELECT AVG(skor) FROM penilaian WHERE jadwal_audit_id=ja.id) as rata_skor
          FROM jadwal_audit ja
          JOIN unit_audit ua ON ja.unit_audit_id = ua.id
          JOIN users u1 ON ja.ketua_auditor = u1.id
          WHERE ja.auditee_id = $user_id
          ORDER BY ja.tanggal_mulai DESC";
$riwayat = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Audit - E-SPMI</title>
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
                <h2 class="mb-4"><i class="bi bi-clock-history"></i> Riwayat Audit Unit</h2>

                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="riwayatTable" class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Kode Audit</th>
                                        <th>Periode</th>
                                        <th>Tanggal</th>
                                        <th>Auditor</th>
                                        <th>Progress</th>
                                        <th>Skor</th>
                                        <th>Kategori</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    while ($row = mysqli_fetch_assoc($riwayat)): 
                                        // Determine kategori
                                        $kategori = '-';
                                        $kategori_class = 'secondary';
                                        if ($row['rata_skor']) {
                                            if ($row['rata_skor'] >= 3.5) {
                                                $kategori = 'Sangat Baik';
                                                $kategori_class = 'success';
                                            } elseif ($row['rata_skor'] >= 3.0) {
                                                $kategori = 'Baik';
                                                $kategori_class = 'primary';
                                            } elseif ($row['rata_skor'] >= 2.0) {
                                                $kategori = 'Cukup';
                                                $kategori_class = 'warning';
                                            } else {
                                                $kategori = 'Perlu Perbaikan';
                                                $kategori_class = 'danger';
                                            }
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo $row['kode_audit']; ?></strong></td>
                                        <td>
                                            <?php echo $row['tahun_akademik']; ?><br>
                                            <small><?php echo ucfirst($row['semester']); ?></small>
                                        </td>
                                        <td>
                                            <small>
                                                <?php echo date('d/m/Y', strtotime($row['tanggal_mulai'])); ?><br>
                                                s/d<br>
                                                <?php echo date('d/m/Y', strtotime($row['tanggal_selesai'])); ?>
                                            </small>
                                        </td>
                                        <td><small><?php echo $row['ketua_nama']; ?></small></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo $row['jumlah_penilaian']; ?> penilaian
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($row['rata_skor']): ?>
                                            <strong class="text-<?php echo $kategori_class; ?>">
                                                <?php echo number_format($row['rata_skor'], 2); ?>
                                            </strong>
                                            <?php else: ?>
                                            <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $kategori_class; ?>">
                                                <?php echo $kategori; ?>
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
                                            <a href="hasil.php?id=<?php echo $row['id']; ?>" 
                                               class="btn btn-sm btn-primary" title="Lihat Detail">
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
        </div>
        <?php include 'footer.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#riwayatTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                },
                order: [[1, 'desc']],
                pageLength: 25
            });
        });
    </script>
    <script src="../assets/js/sidebar-toggle.js"></script>
</body>
</html>