<?php
require_once '../inc/inc_koneksi.php';
require_once '../inc/inc_fungsi.php';

cek_role(['admin']);

// Filter parameters
$tahun_filter = isset($_GET['tahun']) ? esc($_GET['tahun']) : '';
$semester_filter = isset($_GET['semester']) ? esc($_GET['semester']) : '';
$unit_filter = isset($_GET['unit']) ? (int)$_GET['unit'] : 0;
$status_filter = isset($_GET['status']) ? esc($_GET['status']) : '';

// Build query
$where = [];
if ($tahun_filter) $where[] = "ja.tahun_akademik = '$tahun_filter'";
if ($semester_filter) $where[] = "ja.semester = '$semester_filter'";
if ($unit_filter) $where[] = "ja.unit_audit_id = $unit_filter";
if ($status_filter) $where[] = "ja.status = '$status_filter'";

$where_clause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

// Get laporan data
$query = "SELECT ja.*, 
          ua.nama_unit, ua.jenis, ua.kode_unit,
          u1.nama_lengkap as ketua_nama,
          u2.nama_lengkap as auditee_nama,
          (SELECT COUNT(*) FROM penilaian WHERE jadwal_audit_id=ja.id) as jumlah_penilaian,
          (SELECT AVG(skor) FROM penilaian WHERE jadwal_audit_id=ja.id) as rata_skor
          FROM jadwal_audit ja
          JOIN unit_audit ua ON ja.unit_audit_id = ua.id
          JOIN users u1 ON ja.ketua_auditor = u1.id
          JOIN users u2 ON ja.auditee_id = u2.id
          $where_clause
          ORDER BY ja.tanggal_mulai DESC";
$laporan = mysqli_query($koneksi, $query);

// Get units for filter
$query_unit = "SELECT * FROM unit_audit WHERE status='aktif' ORDER BY nama_unit ASC";
$units = mysqli_query($koneksi, $query_unit);

// Statistik
$stats_query = "SELECT 
                COUNT(*) as total_audit,
                COUNT(CASE WHEN ja.status='selesai' THEN 1 END) as audit_selesai,
                AVG(CASE WHEN ja.status='selesai' THEN (SELECT AVG(skor) FROM penilaian WHERE jadwal_audit_id=ja.id) END) as rata_skor_keseluruhan
                FROM jadwal_audit ja
                $where_clause";
$stats_result = mysqli_query($koneksi, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Audit - E-SPMI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>
        .score-card {
            border-left: 4px solid;
            transition: all 0.3s;
        }
        .score-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .score-excellent { border-color: #198754; }
        .score-good { border-color: #0d6efd; }
        .score-fair { border-color: #ffc107; }
        .score-poor { border-color: #dc3545; }
        
        .progress-bar-animated {
            animation: progress-animation 2s ease-in-out;
        }
        
        @keyframes progress-animation {
            from { width: 0; }
        }
        
        @media print {
            .no-print { display: none !important; }
            .sidebar { display: none !important; }
            .main-content { margin-left: 0 !important; }
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
                <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                    <div>
                        <h2 class="mb-0">Laporan Hasil Audit</h2>
                        <p class="text-muted">Rekapitulasi dan analisis hasil audit mutu internal</p>
                    </div>
                    <div>
                        <button class="btn btn-success" onclick="exportExcel()">
                            <i class="bi bi-file-earmark-excel"></i> Export Excel
                        </button>
                        <button class="btn btn-danger" onclick="exportPDF()">
                            <i class="bi bi-file-earmark-pdf"></i> Export PDF
                        </button>
                        <button class="btn btn-primary" onclick="window.print()">
                            <i class="bi bi-printer"></i> Print
                        </button>
                    </div>
                </div>

                <!-- Filter -->
                <div class="card border-0 shadow-sm mb-4 no-print">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0"><i class="bi bi-funnel"></i> Filter Laporan</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="row align-items-end">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Tahun Akademik</label>
                                    <select class="form-select" name="tahun">
                                        <option value="">Semua Tahun</option>
                                        <option value="2024/2025" <?php echo $tahun_filter == '2024/2025' ? 'selected' : ''; ?>>2024/2025</option>
                                        <option value="2025/2026" <?php echo $tahun_filter == '2025/2026' ? 'selected' : ''; ?>>2025/2026</option>
                                        <option value="2026/2027" <?php echo $tahun_filter == '2026/2027' ? 'selected' : ''; ?>>2026/2027</option>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Semester</label>
                                    <select class="form-select" name="semester">
                                        <option value="">Semua</option>
                                        <option value="ganjil" <?php echo $semester_filter == 'ganjil' ? 'selected' : ''; ?>>Ganjil</option>
                                        <option value="genap" <?php echo $semester_filter == 'genap' ? 'selected' : ''; ?>>Genap</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Unit</label>
                                    <select class="form-select" name="unit">
                                        <option value="">Semua Unit</option>
                                        <?php 
                                        mysqli_data_seek($units, 0);
                                        while ($unit = mysqli_fetch_assoc($units)): 
                                        ?>
                                        <option value="<?php echo $unit['id']; ?>" <?php echo $unit_filter == $unit['id'] ? 'selected' : ''; ?>>
                                            <?php echo $unit['nama_unit']; ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="">Semua Status</option>
                                        <option value="selesai" <?php echo $status_filter == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                        <option value="berlangsung" <?php echo $status_filter == 'berlangsung' ? 'selected' : ''; ?>>Berlangsung</option>
                                        <option value="dijadwalkan" <?php echo $status_filter == 'dijadwalkan' ? 'selected' : ''; ?>>Dijadwalkan</option>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-search"></i> Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Statistik Ringkasan -->
                <div class="row g-4 mb-4">
                    <div class="col-xl-4 col-md-6">
                        <div class="card score-card score-excellent border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Total Audit</h6>
                                        <h3 class="mb-0"><?php echo $stats['total_audit']; ?></h3>
                                        <small class="text-success">
                                            <i class="bi bi-check-circle"></i> 
                                            <?php echo $stats['audit_selesai']; ?> Selesai
                                        </small>
                                    </div>
                                    <div class="stat-icon bg-primary">
                                        <i class="bi bi-clipboard-data"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <div class="card score-card score-good border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Rata-rata Skor</h6>
                                        <h3 class="mb-0">
                                            <?php 
                                            $avg_score = $stats['rata_skor_keseluruhan'] ?? 0;
                                            echo number_format($avg_score, 2); 
                                            ?> / 4.00
                                        </h3>
                                        <small class="text-info">
                                            <?php 
                                            if ($avg_score >= 3.5) echo '<i class="bi bi-star-fill"></i> Sangat Baik';
                                            elseif ($avg_score >= 3.0) echo '<i class="bi bi-star"></i> Baik';
                                            elseif ($avg_score >= 2.0) echo '<i class="bi bi-star-half"></i> Cukup';
                                            else echo '<i class="bi bi-exclamation-circle"></i> Perlu Perbaikan';
                                            ?>
                                        </small>
                                    </div>
                                    <div class="stat-icon bg-success">
                                        <i class="bi bi-graph-up-arrow"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6">
                        <div class="card score-card score-fair border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Persentase Pencapaian</h6>
                                        <h3 class="mb-0">
                                            <?php 
                                            $percentage = ($avg_score / 4) * 100;
                                            echo number_format($percentage, 1); 
                                            ?>%
                                        </h3>
                                        <div class="progress mt-2" style="height: 6px;">
                                            <div class="progress-bar progress-bar-animated bg-warning" 
                                                 role="progressbar" 
                                                 style="width: <?php echo $percentage; ?>%"></div>
                                        </div>
                                    </div>
                                    <div class="stat-icon bg-warning">
                                        <i class="bi bi-speedometer2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Laporan -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0"><i class="bi bi-table"></i> Detail Laporan Audit</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="laporanTable" class="table table-hover table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th width="3%">No</th>
                                        <th width="10%">Kode Audit</th>
                                        <th width="15%">Unit</th>
                                        <th width="10%">Periode</th>
                                        <th width="12%">Tanggal</th>
                                        <th width="12%">Auditor</th>
                                        <th width="12%">Auditee</th>
                                        <th width="8%">Penilaian</th>
                                        <th width="8%">Skor</th>
                                        <th width="8%">Status</th>
                                        <th width="7%" class="no-print">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    while ($row = mysqli_fetch_assoc($laporan)): 
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><strong><?php echo $row['kode_audit']; ?></strong></td>
                                        <td>
                                            <span class="badge bg-info mb-1"><?php echo $row['kode_unit']; ?></span><br>
                                            <small><?php echo $row['nama_unit']; ?></small>
                                        </td>
                                        <td><?php echo $row['tahun_akademik']; ?><br><small><?php echo ucfirst($row['semester']); ?></small></td>
                                        <td>
                                            <small>
                                                <?php echo date('d/m/Y', strtotime($row['tanggal_mulai'])); ?><br>
                                                s/d<br>
                                                <?php echo date('d/m/Y', strtotime($row['tanggal_selesai'])); ?>
                                            </small>
                                        </td>
                                        <td><small><?php echo $row['ketua_nama']; ?></small></td>
                                        <td><small><?php echo $row['auditee_nama']; ?></small></td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary"><?php echo $row['jumlah_penilaian']; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($row['rata_skor']): ?>
                                            <strong class="
                                                <?php 
                                                if ($row['rata_skor'] >= 3.5) echo 'text-success';
                                                elseif ($row['rata_skor'] >= 3.0) echo 'text-primary';
                                                elseif ($row['rata_skor'] >= 2.0) echo 'text-warning';
                                                else echo 'text-danger';
                                                ?>
                                            ">
                                                <?php echo number_format($row['rata_skor'], 2); ?>
                                            </strong>
                                            <?php else: ?>
                                            <span class="text-muted">-</span>
                                            <?php endif; ?>
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
                                        <td class="no-print">
                                            <a href="laporan_detail.php?id=<?php echo $row['id']; ?>" 
                                               class="btn btn-sm btn-primary" 
                                               title="Lihat Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if ($row['status'] == 'selesai'): ?>
                                            <a href="cetak_laporan.php?id=<?php echo $row['id']; ?>" 
                                               class="btn btn-sm btn-success" 
                                               target="_blank"
                                               title="Cetak Laporan">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Footer Info -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="bi bi-info-circle"></i> Keterangan Skor</h6>
                                <ul class="list-unstyled">
                                    <li><span class="badge bg-success">3.51 - 4.00</span> Sangat Baik (Melampaui Standar)</li>
                                    <li><span class="badge bg-primary">3.01 - 3.50</span> Baik (Memenuhi Standar)</li>
                                    <li><span class="badge bg-warning">2.01 - 3.00</span> Cukup (Hampir Memenuhi)</li>
                                    <li><span class="badge bg-danger">0.00 - 2.00</span> Kurang (Perlu Perbaikan)</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="bi bi-calendar-check"></i> Informasi Laporan</h6>
                                <ul class="list-unstyled">
                                    <li><i class="bi bi-check-circle text-success"></i> Laporan dibuat secara otomatis dari hasil penilaian</li>
                                    <li><i class="bi bi-check-circle text-success"></i> Skor dihitung dari rata-rata penilaian semua indikator</li>
                                    <li><i class="bi bi-check-circle text-success"></i> Data dapat diexport ke Excel dan PDF</li>
                                </ul>
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
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#laporanTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                },
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="bi bi-file-earmark-excel"></i> Excel',
                        className: 'btn btn-success btn-sm d-none',
                        title: 'Laporan Audit E-SPMI',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm d-none',
                        title: 'Laporan Audit E-SPMI',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9]
                        }
                    }
                ],
                pageLength: 25,
                order: [[1, 'desc']]
            });
        });

        function exportExcel() {
            $('.buttons-excel').click();
        }

        function exportPDF() {
            $('.buttons-pdf').click();
        }
    </script>
    <script src="../assets/js/sidebar-toggle.js"></script>
</body>
</html>