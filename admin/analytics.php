<?php
require_once '../inc/inc_koneksi.php';
require_once '../inc/inc_fungsi.php';

cek_role(['admin']);

// Get data untuk grafik
// 1. Skor per standar (rata-rata)
$query_standar = "SELECT s.kode, s.nama_standar,
                  AVG(p.skor) as rata_skor
                  FROM standar s
                  LEFT JOIN indikator i ON s.id = i.standar_id
                  LEFT JOIN penilaian p ON i.id = p.indikator_id
                  WHERE s.status='aktif'
                  GROUP BY s.id
                  ORDER BY s.urutan ASC";
$data_standar = mysqli_query($koneksi, $query_standar);

// 2. Tren audit per periode
$query_tren = "SELECT 
               CONCAT(tahun_akademik, ' - ', semester) as periode,
               COUNT(*) as jumlah,
               AVG((SELECT AVG(skor) FROM penilaian WHERE jadwal_audit_id=ja.id)) as rata_skor
               FROM jadwal_audit ja
               WHERE status='selesai'
               GROUP BY tahun_akademik, semester
               ORDER BY tahun_akademik ASC, semester ASC";
$data_tren = mysqli_query($koneksi, $query_tren);

// 3. Top 5 unit terbaik
$query_top = "SELECT ua.nama_unit,
              AVG(p.skor) as rata_skor,
              COUNT(DISTINCT ja.id) as jumlah_audit
              FROM unit_audit ua
              JOIN jadwal_audit ja ON ua.id = ja.unit_audit_id
              JOIN penilaian p ON ja.id = p.jadwal_audit_id
              WHERE ja.status='selesai'
              GROUP BY ua.id
              ORDER BY rata_skor DESC
              LIMIT 5";
$data_top = mysqli_query($koneksi, $query_top);

// 4. Distribusi skor
$query_distribusi = "SELECT 
                     CASE 
                         WHEN skor >= 3.5 THEN 'Sangat Baik (3.5-4.0)'
                         WHEN skor >= 3.0 THEN 'Baik (3.0-3.49)'
                         WHEN skor >= 2.0 THEN 'Cukup (2.0-2.99)'
                         ELSE 'Perlu Perbaikan (<2.0)'
                     END as kategori,
                     COUNT(*) as jumlah
                     FROM penilaian
                     GROUP BY kategori
                     ORDER BY MIN(skor) DESC";
$data_distribusi = mysqli_query($koneksi, $query_distribusi);

// Statistics
$total_audit = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM jadwal_audit"))['total'];
$total_selesai = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM jadwal_audit WHERE status='selesai'"))['total'];
$rata_keseluruhan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT AVG(skor) as rata FROM penilaian"))['rata'];
$total_unit = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM unit_audit WHERE status='aktif'"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics & Grafik - E-SPMI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <?php include 'inc_sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'inc_navbar.php'; ?>
        
        <div class="content-wrapper">
            <div class="container-fluid">
                <h2 class="mb-4"><i class="bi bi-bar-chart-line"></i> Analytics & Grafik</h2>

                <!-- Stats Overview -->
                <div class="row g-4 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Total Audit</h6>
                                        <h3 class="mb-0"><?php echo $total_audit; ?></h3>
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
                                        <h6 class="text-muted mb-2">Audit Selesai</h6>
                                        <h3 class="mb-0"><?php echo $total_selesai; ?></h3>
                                    </div>
                                    <div class="stat-icon bg-success">
                                        <i class="bi bi-check-circle"></i>
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
                                        <h6 class="text-muted mb-2">Rata-rata Skor</h6>
                                        <h3 class="mb-0"><?php echo number_format($rata_keseluruhan, 2); ?></h3>
                                    </div>
                                    <div class="stat-icon bg-warning">
                                        <i class="bi bi-star-fill"></i>
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
                                        <h6 class="text-muted mb-2">Total Unit</h6>
                                        <h3 class="mb-0"><?php echo $total_unit; ?></h3>
                                    </div>
                                    <div class="stat-icon bg-info">
                                        <i class="bi bi-building"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 1 -->
                <div class="row mb-4">
                    <!-- Skor Per Standar -->
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Rata-rata Skor Per Standar</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="chartStandar" height="300"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Distribusi Skor -->
                    <div class="col-lg-6 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Distribusi Kategori Penilaian</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="chartDistribusi" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 2 -->
                <div class="row mb-4">
                    <!-- Tren Audit -->
                    <div class="col-lg-8 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Tren Audit Per Periode</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="chartTren" height="200"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Top 5 Unit -->
                    <div class="col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="bi bi-trophy"></i> Top 5 Unit Terbaik</h5>
                            </div>
                            <div class="card-body">
                                <?php 
                                $rank = 1;
                                mysqli_data_seek($data_top, 0);
                                while ($top = mysqli_fetch_assoc($data_top)): 
                                ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span>
                                            <span class="badge bg-<?php echo $rank == 1 ? 'warning' : ($rank == 2 ? 'secondary' : 'info'); ?>">
                                                #<?php echo $rank++; ?>
                                            </span>
                                            <small><?php echo $top['nama_unit']; ?></small>
                                        </span>
                                        <strong class="text-success"><?php echo number_format($top['rata_skor'], 2); ?></strong>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" 
                                             style="width: <?php echo ($top['rata_skor'] / 4) * 100; ?>%"></div>
                                    </div>
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
    <script>
        // Data untuk Chart Standar
        const dataStandar = {
            labels: [
                <?php 
                mysqli_data_seek($data_standar, 0);
                while ($d = mysqli_fetch_assoc($data_standar)) {
                    echo "'" . $d['kode'] . "',";
                }
                ?>
            ],
            datasets: [{
                label: 'Rata-rata Skor',
                data: [
                    <?php 
                    mysqli_data_seek($data_standar, 0);
                    while ($d = mysqli_fetch_assoc($data_standar)) {
                        echo ($d['rata_skor'] ?? 0) . ",";
                    }
                    ?>
                ],
                backgroundColor: 'rgba(102, 126, 234, 0.8)',
                borderColor: 'rgba(102, 126, 234, 1)',
                borderWidth: 1
            }]
        };

        new Chart(document.getElementById('chartStandar'), {
            type: 'bar',
            data: dataStandar,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 4,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Data untuk Chart Distribusi
        const dataDistribusi = {
            labels: [
                <?php 
                mysqli_data_seek($data_distribusi, 0);
                while ($d = mysqli_fetch_assoc($data_distribusi)) {
                    echo "'" . $d['kategori'] . "',";
                }
                ?>
            ],
            datasets: [{
                data: [
                    <?php 
                    mysqli_data_seek($data_distribusi, 0);
                    while ($d = mysqli_fetch_assoc($data_distribusi)) {
                        echo $d['jumlah'] . ",";
                    }
                    ?>
                ],
                backgroundColor: [
                    'rgba(25, 135, 84, 0.8)',
                    'rgba(13, 110, 253, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(220, 53, 69, 0.8)'
                ]
            }]
        };

        new Chart(document.getElementById('chartDistribusi'), {
            type: 'doughnut',
            data: dataDistribusi,
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Data untuk Chart Tren
        const dataTren = {
            labels: [
                <?php 
                mysqli_data_seek($data_tren, 0);
                while ($d = mysqli_fetch_assoc($data_tren)) {
                    echo "'" . $d['periode'] . "',";
                }
                ?>
            ],
            datasets: [{
                label: 'Jumlah Audit',
                data: [
                    <?php 
                    mysqli_data_seek($data_tren, 0);
                    while ($d = mysqli_fetch_assoc($data_tren)) {
                        echo $d['jumlah'] . ",";
                    }
                    ?>
                ],
                backgroundColor: 'rgba(13, 202, 240, 0.2)',
                borderColor: 'rgba(13, 202, 240, 1)',
                borderWidth: 2,
                tension: 0.4
            }, {
                label: 'Rata-rata Skor',
                data: [
                    <?php 
                    mysqli_data_seek($data_tren, 0);
                    while ($d = mysqli_fetch_assoc($data_tren)) {
                        echo ($d['rata_skor'] ?? 0) . ",";
                    }
                    ?>
                ],
                backgroundColor: 'rgba(255, 193, 7, 0.2)',
                borderColor: 'rgba(255, 193, 7, 1)',
                borderWidth: 2,
                tension: 0.4,
                yAxisID: 'y1'
            }]
        };

        new Chart(document.getElementById('chartTren'), {
            type: 'line',
            data: dataTren,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        position: 'left'
                    },
                    y1: {
                        beginAtZero: true,
                        max: 4,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });
    </script>
    <script src="../assets/js/sidebar-toggle.js"></script>
</body>
</html>