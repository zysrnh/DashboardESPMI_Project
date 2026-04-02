<?php
require_once '../inc/inc_koneksi.php';
require_once '../inc/inc_fungsi.php';

cek_role(['auditee']);

$user_id = $_SESSION['user_id'];

// Get user info
$user_query = mysqli_query($koneksi, "
    SELECT u.* 
    FROM users u 
    WHERE u.id = $user_id
");

if (!$user_query) {
    die("Error query user: " . mysqli_error($koneksi));
}

$user_info = mysqli_fetch_assoc($user_query);

if (!$user_info) {
    die("User tidak ditemukan!");
}

// Get unit info dari jadwal_audit (ambil unit dari audit terakhir)
$unit_query = mysqli_query($koneksi, "
    SELECT DISTINCT ua.nama_unit, ua.kode_unit 
    FROM jadwal_audit ja
    JOIN unit_audit ua ON ja.unit_audit_id = ua.id
    WHERE ja.auditee_id = $user_id
    ORDER BY ja.created_at DESC
    LIMIT 1
");

$unit_info = mysqli_fetch_assoc($unit_query);
if ($unit_info) {
    $user_info['nama_unit'] = $unit_info['nama_unit'];
    $user_info['kode_unit'] = $unit_info['kode_unit'];
}

// Get audit history dengan hasil
$query_history = "SELECT ja.*, 
                (SELECT AVG(skor) FROM penilaian WHERE jadwal_audit_id = ja.id) as rata_skor,
                (SELECT COUNT(*) FROM penilaian WHERE jadwal_audit_id = ja.id) as jumlah_penilaian,
                (SELECT COUNT(*) FROM indikator WHERE status='aktif') as total_indikator
                FROM jadwal_audit ja
                WHERE ja.auditee_id = $user_id
                AND ja.status = 'selesai'
                ORDER BY ja.tahun_akademik ASC, ja.semester ASC";
                
$history = mysqli_query($koneksi, $query_history);

if (!$history) {
    die("Error query history: " . mysqli_error($koneksi));
}

// Prepare data untuk chart
$chart_labels = [];
$chart_data = [];
$chart_colors = [];

while ($row = mysqli_fetch_assoc($history)) {
    $label = $row['tahun_akademik'] . ' ' . ucfirst($row['semester']);
    $chart_labels[] = $label;
    $chart_data[] = $row['rata_skor'] ? round($row['rata_skor'], 2) : 0;
    
    // Color based on score
    $skor = $row['rata_skor'] ?? 0;
    if ($skor >= 3.5) {
        $chart_colors[] = 'rgba(40, 167, 69, 0.8)'; // Green
    } elseif ($skor >= 3.0) {
        $chart_colors[] = 'rgba(0, 123, 255, 0.8)'; // Blue
    } elseif ($skor >= 2.0) {
        $chart_colors[] = 'rgba(255, 193, 7, 0.8)'; // Yellow
    } else {
        $chart_colors[] = 'rgba(220, 53, 69, 0.8)'; // Red
    }
}

// Get data per standar (untuk latest audit)
$latest_audit_query = mysqli_query($koneksi, "
    SELECT id FROM jadwal_audit 
    WHERE auditee_id = $user_id 
    AND status = 'selesai' 
    ORDER BY created_at DESC 
    LIMIT 1
");

$standar_labels = [];
$standar_data = [];

if ($latest_audit_query && mysqli_num_rows($latest_audit_query) > 0) {
    $latest_audit = mysqli_fetch_assoc($latest_audit_query);
    $audit_id = $latest_audit['id'];
    
    $query_standar = "SELECT s.nama_standar, AVG(p.skor) as rata_skor
                    FROM penilaian p
                    JOIN indikator i ON p.indikator_id = i.id
                    JOIN standar s ON i.standar_id = s.id
                    WHERE p.jadwal_audit_id = $audit_id
                    GROUP BY s.id, s.nama_standar
                    ORDER BY s.urutan ASC";
    $standar_result = mysqli_query($koneksi, $query_standar);
    
    if ($standar_result) {
        while ($row = mysqli_fetch_assoc($standar_result)) {
            $standar_labels[] = $row['nama_standar'];
            $standar_data[] = round($row['rata_skor'], 2);
        }
    }
}

// Get total audits
$total_audit_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM jadwal_audit WHERE auditee_id=$user_id");
$total_audit = mysqli_fetch_assoc($total_audit_query)['total'] ?? 0;

$total_selesai_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM jadwal_audit WHERE auditee_id=$user_id AND status='selesai'");
$total_selesai = mysqli_fetch_assoc($total_selesai_query)['total'] ?? 0;

// Get latest score
$latest_score = 0;
$latest_score_query = mysqli_query($koneksi, "
    SELECT (SELECT AVG(skor) FROM penilaian WHERE jadwal_audit_id = ja.id) as rata_skor
    FROM jadwal_audit ja
    WHERE ja.auditee_id = $user_id
    AND ja.status = 'selesai'
    ORDER BY ja.created_at DESC
    LIMIT 1
");

if ($latest_score_query && mysqli_num_rows($latest_score_query) > 0) {
    $latest_query = mysqli_fetch_assoc($latest_score_query);
    $latest_score = $latest_query['rata_skor'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grafik Perkembangan - E-SPMI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        <h2 class="mb-0"><i class="bi bi-graph-up"></i> Grafik Perkembangan</h2>
                        <p class="text-muted mb-0">Visualisasi perkembangan hasil audit unit Anda</p>
                    </div>
                </div>

                <!-- Unit Info -->
                <?php if (!empty($user_info['nama_unit'])): ?>
                <div class="alert alert-info border-0 shadow-sm mb-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="bi bi-building fs-1"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-1">
                                <span class="badge bg-primary"><?php echo $user_info['kode_unit'] ?? '-'; ?></span>
                                <?php echo $user_info['nama_unit']; ?>
                            </h5>
                            <p class="mb-0">Auditee: <strong><?php echo $user_info['nama_lengkap']; ?></strong></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Total Audit</h6>
                                        <h3 class="mb-0"><?php echo $total_selesai; ?></h3>
                                        <small class="text-muted">Dari <?php echo $total_audit; ?> penugasan</small>
                                    </div>
                                    <div class="stat-icon bg-primary">
                                        <i class="bi bi-clipboard-check"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Skor Terakhir</h6>
                                        <h3 class="mb-0 <?php 
                                            if ($latest_score >= 3.5) echo 'text-success';
                                            elseif ($latest_score >= 3.0) echo 'text-primary';
                                            elseif ($latest_score >= 2.0) echo 'text-warning';
                                            else echo 'text-danger';
                                        ?>">
                                            <?php echo $latest_score ? number_format($latest_score, 2) : '-'; ?>
                                        </h3>
                                        <small class="text-muted">dari 4.00</small>
                                    </div>
                                    <div class="stat-icon bg-<?php 
                                        if ($latest_score >= 3.5) echo 'success';
                                        elseif ($latest_score >= 3.0) echo 'primary';
                                        elseif ($latest_score >= 2.0) echo 'warning';
                                        else echo 'danger';
                                    ?>">
                                        <i class="bi bi-star-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Kategori</h6>
                                        <h3 class="mb-0">
                                            <?php 
                                            if ($latest_score >= 3.5) echo '<span class="badge bg-success">Baik</span>';
                                            elseif ($latest_score >= 3.0) echo '<span class="badge bg-primary">Cukup</span>';
                                            elseif ($latest_score >= 2.0) echo '<span class="badge bg-warning">Kurang</span>';
                                            elseif ($latest_score > 0) echo '<span class="badge bg-danger">Buruk</span>';
                                            else echo '<span class="badge bg-secondary">-</span>';
                                            ?>
                                        </h3>
                                        <small class="text-muted">Status penilaian</small>
                                    </div>
                                    <div class="stat-icon bg-info">
                                        <i class="bi bi-award"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (count($chart_labels) > 0): ?>
                
                <!-- Trend Chart -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0"><i class="bi bi-graph-up-arrow"></i> Tren Perkembangan Skor Audit</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="trendChart" height="80"></canvas>
                    </div>
                    <div class="card-footer bg-light">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> 
                            Grafik menampilkan rata-rata skor audit dari periode ke periode. 
                            Skor yang meningkat menunjukkan perbaikan kualitas mutu.
                        </small>
                    </div>
                </div>

                <?php if (count($standar_labels) > 0): ?>
                <!-- Standar Breakdown -->
                <div class="row g-4 mb-4">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0"><i class="bi bi-bar-chart-fill"></i> Skor per Standar SPMI (Audit Terakhir)</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="standarChart" height="100"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0"><i class="bi bi-trophy"></i> Pencapaian</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $best_standar = '';
                                $best_score = 0;
                                $worst_standar = '';
                                $worst_score = 4;
                                
                                if (count($standar_data) > 0) {
                                    foreach ($standar_data as $idx => $score) {
                                        if ($score > $best_score) {
                                            $best_score = $score;
                                            $best_standar = $standar_labels[$idx];
                                        }
                                        if ($score < $worst_score) {
                                            $worst_score = $score;
                                            $worst_standar = $standar_labels[$idx];
                                        }
                                    }
                                }
                                ?>
                                
                                <div class="mb-4">
                                    <h6 class="text-success"><i class="bi bi-arrow-up-circle"></i> Standar Terbaik</h6>
                                    <p class="mb-1"><strong><?php echo $best_standar; ?></strong></p>
                                    <h4 class="text-success"><?php echo number_format($best_score, 2); ?></h4>
                                </div>
                                
                                <hr>
                                
                                <div>
                                    <h6 class="text-warning"><i class="bi bi-arrow-down-circle"></i> Perlu Perbaikan</h6>
                                    <p class="mb-1"><strong><?php echo $worst_standar; ?></strong></p>
                                    <h4 class="text-warning"><?php echo number_format($worst_score, 2); ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Rekomendasi -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0"><i class="bi bi-lightbulb"></i> Rekomendasi & Tindak Lanjut</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($latest_score >= 3.5): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle-fill"></i> <strong>Pertahankan Kualitas!</strong><br>
                            Unit Anda memiliki performa yang sangat baik. Terus pertahankan dan tingkatkan standar mutu yang sudah ada.
                        </div>
                        <?php elseif ($latest_score >= 3.0): ?>
                        <div class="alert alert-primary">
                            <i class="bi bi-info-circle-fill"></i> <strong>Performa Baik</strong><br>
                            Unit Anda memiliki performa yang baik. Tingkatkan kembali pada standar yang masih perlu perbaikan.
                        </div>
                        <?php elseif ($latest_score >= 2.0): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle-fill"></i> <strong>Perlu Peningkatan</strong><br>
                            Fokuskan perbaikan pada standar dengan skor rendah. Dokumentasikan semua kegiatan dan implementasi SOP dengan baik.
                        </div>
                        <?php elseif ($latest_score > 0): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-x-circle-fill"></i> <strong>Perlu Perhatian Khusus</strong><br>
                            Segera lakukan perbaikan menyeluruh. Koordinasikan dengan pimpinan untuk tindakan korektif.
                        </div>
                        <?php else: ?>
                        <div class="alert alert-secondary">
                            <i class="bi bi-info-circle"></i> <strong>Belum Ada Data</strong><br>
                            Belum ada hasil audit yang selesai. Grafik akan muncul setelah audit pertama selesai dilakukan.
                        </div>
                        <?php endif; ?>
                        
                        <h6 class="mt-3 mb-2"><i class="bi bi-clipboard-check"></i> Langkah Tindak Lanjut:</h6>
                        <ul>
                            <li>Review hasil audit secara detail pada menu "Riwayat Audit"</li>
                            <li>Identifikasi standar dengan skor rendah untuk diprioritaskan</li>
                            <li>Buat rencana perbaikan (action plan) dengan timeline jelas</li>
                            <li>Dokumentasikan semua bukti implementasi perbaikan</li>
                            <li>Monitoring dan evaluasi berkala terhadap perbaikan</li>
                        </ul>
                    </div>
                </div>

                <?php else: ?>
                
                <!-- No Data -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-graph-up display-1 text-muted"></i>
                        <h4 class="mt-3">Belum Ada Data Grafik</h4>
                        <p class="text-muted">
                            Grafik perkembangan akan muncul setelah audit selesai dilakukan.<br>
                            Tunggu proses audit dari tim auditor untuk melihat hasil perkembangan unit Anda.
                        </p>
                    </div>
                </div>
                
                <?php endif; ?>

            </div>
        </div>
        <?php include 'footer.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/sidebar-toggle.js"></script>
    
    <?php if (count($chart_labels) > 0): ?>
    <script>
        // Trend Chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        const trendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                    label: 'Rata-rata Skor',
                    data: <?php echo json_encode($chart_data); ?>,
                    borderColor: 'rgb(102, 126, 234)',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: <?php echo json_encode($chart_colors); ?>,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Skor: ' + context.parsed.y.toFixed(2) + ' / 4.00';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 4,
                        ticks: {
                            stepSize: 0.5
                        },
                        title: {
                            display: true,
                            text: 'Skor'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Periode'
                        }
                    }
                }
            }
        });

        <?php if (count($standar_labels) > 0): ?>
        // Standar Chart
        const standarCtx = document.getElementById('standarChart').getContext('2d');
        const standarChart = new Chart(standarCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($standar_labels); ?>,
                datasets: [{
                    label: 'Skor',
                    data: <?php echo json_encode($standar_data); ?>,
                    backgroundColor: [
                        'rgba(102, 126, 234, 0.8)',
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(23, 162, 184, 0.8)',
                        'rgba(220, 53, 69, 0.8)',
                        'rgba(108, 117, 125, 0.8)',
                        'rgba(253, 126, 20, 0.8)',
                        'rgba(111, 66, 193, 0.8)'
                    ],
                    borderColor: [
                        'rgb(102, 126, 234)',
                        'rgb(40, 167, 69)',
                        'rgb(255, 193, 7)',
                        'rgb(23, 162, 184)',
                        'rgb(220, 53, 69)',
                        'rgb(108, 117, 125)',
                        'rgb(253, 126, 20)',
                        'rgb(111, 66, 193)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Skor: ' + context.parsed.y.toFixed(2) + ' / 4.00';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 4,
                        ticks: {
                            stepSize: 0.5
                        },
                        title: {
                            display: true,
                            text: 'Skor'
                        }
                    },
                    x: {
                        ticks: {
                            autoSkip: false,
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
    <?php endif; ?>
</body>
</html>