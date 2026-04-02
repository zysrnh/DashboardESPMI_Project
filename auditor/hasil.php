<?php
require_once '../inc/inc_koneksi.php';
require_once '../inc/inc_fungsi.php';

cek_role(['auditor']);

$user_id = $_SESSION['user_id'];
$jadwal_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validasi akses auditor
$check = mysqli_query($koneksi, "SELECT ja.*, ua.nama_unit, ua.kode_unit, ua.pimpinan, 
                                  u1.nama_lengkap as ketua_nama, u1.unit_kerja as ketua_unit,
                                  u2.nama_lengkap as auditee_nama
                                  FROM jadwal_audit ja
                                  JOIN unit_audit ua ON ja.unit_audit_id = ua.id
                                  JOIN users u1 ON ja.ketua_auditor = u1.id
                                  JOIN users u2 ON ja.auditee_id = u2.id
                                  WHERE ja.id=$jadwal_id 
                                  AND (ja.ketua_auditor=$user_id OR FIND_IN_SET($user_id, ja.anggota_auditor))");
if (mysqli_num_rows($check) == 0) {
    $_SESSION['error'] = 'Anda tidak memiliki akses ke audit ini!';
    header("Location: index.php");
    exit();
}

$jadwal = mysqli_fetch_assoc($check);

// Get penilaian dengan detail standar
$query_penilaian = "SELECT p.*, 
                    s.kode as standar_kode, s.nama_standar,
                    i.kode as indikator_kode, i.nama_indikator,
                    i.rubrik_0, i.rubrik_1, i.rubrik_2, i.rubrik_3, i.rubrik_4,
                    u.nama_lengkap as auditor_nama
                    FROM penilaian p
                    JOIN indikator i ON p.indikator_id = i.id
                    JOIN standar s ON i.standar_id = s.id
                    JOIN users u ON p.auditor_id = u.id
                    WHERE p.jadwal_audit_id = $jadwal_id
                    ORDER BY s.urutan ASC, i.urutan ASC";
$penilaian = mysqli_query($koneksi, $query_penilaian);

// Calculate statistics
$total_penilaian = mysqli_num_rows($penilaian);
$total_skor = 0;
$skor_per_standar = [];

mysqli_data_seek($penilaian, 0);
while ($p = mysqli_fetch_assoc($penilaian)) {
    $total_skor += $p['skor'];
    if (!isset($skor_per_standar[$p['standar_kode']])) {
        $skor_per_standar[$p['standar_kode']] = [
            'nama' => $p['nama_standar'],
            'total' => 0,
            'count' => 0
        ];
    }
    $skor_per_standar[$p['standar_kode']]['total'] += $p['skor'];
    $skor_per_standar[$p['standar_kode']]['count']++;
}

$rata_skor = $total_penilaian > 0 ? $total_skor / $total_penilaian : 0;

// Determine category
$kategori = '';
$kategori_class = '';
if ($rata_skor >= 3.5) {
    $kategori = 'Sangat Baik - Melampaui Standar';
    $kategori_class = 'success';
} elseif ($rata_skor >= 3.0) {
    $kategori = 'Baik - Memenuhi Standar';
    $kategori_class = 'primary';
} elseif ($rata_skor >= 2.0) {
    $kategori = 'Cukup - Hampir Memenuhi Standar';
    $kategori_class = 'warning';
} else {
    $kategori = 'Kurang - Perlu Perbaikan';
    $kategori_class = 'danger';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Audit - <?php echo $jadwal['kode_audit']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>
        .score-box {
            text-align: center;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 20px;
        }
        .score-number {
            font-size: 4rem;
            font-weight: bold;
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
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-3 no-print">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Hasil Audit</li>
                    </ol>
                </nav>

                <!-- Actions -->
                <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                    <div>
                        <h2 class="mb-0">Hasil Audit</h2>
                        <p class="text-muted"><?php echo $jadwal['kode_audit']; ?></p>
                    </div>
                    <div>
                        <a href="../admin/cetak_pdf.php?id=<?php echo $jadwal_id; ?>" 
                           class="btn btn-danger" target="_blank">
                            <i class="bi bi-file-pdf"></i> Export PDF
                        </a>
                        <button class="btn btn-primary" onclick="window.print()">
                            <i class="bi bi-printer"></i> Print
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <!-- Info Audit -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Audit</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">Kode Audit</th>
                                        <td><strong><?php echo $jadwal['kode_audit']; ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th>Unit yang Diaudit</th>
                                        <td>
                                            <span class="badge bg-info"><?php echo $jadwal['kode_unit']; ?></span>
                                            <strong><?php echo $jadwal['nama_unit']; ?></strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Pimpinan Unit</th>
                                        <td><?php echo $jadwal['pimpinan']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Periode</th>
                                        <td><?php echo $jadwal['tahun_akademik'] . ' - ' . ucfirst($jadwal['semester']); ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">Tanggal Audit</th>
                                        <td>
                                            <?php echo tanggal_indo($jadwal['tanggal_mulai']); ?> s/d 
                                            <?php echo tanggal_indo($jadwal['tanggal_selesai']); ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Ketua Auditor</th>
                                        <td><?php echo $jadwal['ketua_nama']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Auditee</th>
                                        <td><?php echo $jadwal['auditee_nama']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            <?php
                                            $status_badges = [
                                                'dijadwalkan' => 'primary',
                                                'berlangsung' => 'warning',
                                                'selesai' => 'success',
                                                'dibatalkan' => 'danger'
                                            ];
                                            ?>
                                            <span class="badge bg-<?php echo $status_badges[$jadwal['status']]; ?>">
                                                <?php echo ucfirst($jadwal['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Total Penilaian</th>
                                        <td><strong><?php echo $total_penilaian; ?></strong> Indikator</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ringkasan Skor -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body score-box bg-<?php echo $kategori_class; ?> text-white">
                                <h6 class="mb-2">Rata-rata Skor</h6>
                                <div class="score-number"><?php echo number_format($rata_skor, 2); ?></div>
                                <p class="mb-0">dari 4.00</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body score-box bg-info text-white">
                                <h6 class="mb-2">Kategori Pencapaian</h6>
                                <div class="fs-4 fw-bold mt-3"><?php echo $kategori; ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body score-box bg-secondary text-white">
                                <h6 class="mb-2">Persentase</h6>
                                <div class="score-number"><?php echo number_format(($rata_skor / 4) * 100, 1); ?>%</div>
                                <p class="mb-0">Pencapaian</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Skor Per Standar -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Skor Per Standar</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Standar</th>
                                        <th width="15%" class="text-center">Jumlah Indikator</th>
                                        <th width="15%" class="text-center">Rata-rata Skor</th>
                                        <th width="20%">Kategori</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($skor_per_standar as $kode => $data): 
                                        $avg = $data['total'] / $data['count'];
                                    ?>
                                    <tr>
                                        <td><strong><?php echo $kode; ?></strong> - <?php echo $data['nama']; ?></td>
                                        <td class="text-center"><?php echo $data['count']; ?></td>
                                        <td class="text-center">
                                            <strong class="fs-5 <?php 
                                                if ($avg >= 3.5) echo 'text-success';
                                                elseif ($avg >= 3.0) echo 'text-primary';
                                                elseif ($avg >= 2.0) echo 'text-warning';
                                                else echo 'text-danger';
                                            ?>">
                                                <?php echo number_format($avg, 2); ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <?php 
                                            if ($avg >= 3.5) echo '<span class="badge bg-success">Sangat Baik</span>';
                                            elseif ($avg >= 3.0) echo '<span class="badge bg-primary">Baik</span>';
                                            elseif ($avg >= 2.0) echo '<span class="badge bg-warning">Cukup</span>';
                                            else echo '<span class="badge bg-danger">Perlu Perbaikan</span>';
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Detail Penilaian -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0"><i class="bi bi-list-check"></i> Detail Penilaian Per Indikator</h5>
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="accordionDetail">
                            <?php 
                            mysqli_data_seek($penilaian, 0);
                            $current_standar = '';
                            $accordion_no = 0;
                            while ($p = mysqli_fetch_assoc($penilaian)): 
                                // Header standar
                                if ($current_standar != $p['standar_kode']):
                                    if ($current_standar != '') echo '</tbody></table></div></div></div></div>';
                                    $current_standar = $p['standar_kode'];
                                    $accordion_no++;
                            ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button <?php echo $accordion_no > 1 ? 'collapsed' : ''; ?>" 
                                            type="button" 
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#collapse<?php echo $accordion_no; ?>">
                                        <strong><?php echo $p['standar_kode']; ?> - <?php echo $p['nama_standar']; ?></strong>
                                    </button>
                                </h2>
                                <div id="collapse<?php echo $accordion_no; ?>" 
                                     class="accordion-collapse collapse <?php echo $accordion_no == 1 ? 'show' : ''; ?>" 
                                     data-bs-parent="#accordionDetail">
                                    <div class="accordion-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover">
                                                <thead>
                                                    <tr>
                                                        <th width="10%">Kode</th>
                                                        <th width="30%">Indikator</th>
                                                        <th width="8%">Skor</th>
                                                        <th width="32%">Deskripsi Pencapaian</th>
                                                        <th width="20%">Catatan</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                            <?php endif; ?>
                                                    <tr>
                                                        <td><?php echo $p['indikator_kode']; ?></td>
                                                        <td><?php echo $p['nama_indikator']; ?></td>
                                                        <td class="text-center">
                                                            <span class="badge bg-<?php 
                                                                if ($p['skor'] >= 4) echo 'success';
                                                                elseif ($p['skor'] >= 3) echo 'primary';
                                                                elseif ($p['skor'] >= 2) echo 'warning';
                                                                else echo 'danger';
                                                            ?> fs-6">
                                                                <?php echo $p['skor']; ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <small>
                                                                <?php 
                                                                switch ($p['skor']) {
                                                                    case 0: echo $p['rubrik_0']; break;
                                                                    case 1: echo $p['rubrik_1']; break;
                                                                    case 2: echo $p['rubrik_2']; break;
                                                                    case 3: echo $p['rubrik_3']; break;
                                                                    case 4: echo $p['rubrik_4']; break;
                                                                }
                                                                ?>
                                                            </small>
                                                        </td>
                                                        <td><small><?php echo $p['catatan'] ?: '-'; ?></small></td>
                                                    </tr>
                            <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kesimpulan -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-check-circle"></i> Kesimpulan</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">
                            Berdasarkan hasil audit yang telah dilakukan pada <strong><?php echo $jadwal['nama_unit']; ?></strong> 
                            periode <strong><?php echo $jadwal['tahun_akademik'] . ' - ' . ucfirst($jadwal['semester']); ?></strong>, 
                            diperoleh rata-rata skor <strong class="text-<?php echo $kategori_class; ?>"><?php echo number_format($rata_skor, 2); ?></strong> 
                            dari skala 4.00.
                        </p>
                        <p class="mb-0">
                            Hasil ini menunjukkan bahwa unit berada dalam kategori 
                            <strong class="text-<?php echo $kategori_class; ?>">"<?php echo $kategori; ?>"</strong>.
                        </p>
                    </div>
                </div>

            </div>
        </div>
        <?php include 'footer.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>