<?php
require_once '../inc/inc_koneksi.php';
require_once '../inc/inc_fungsi.php';

cek_role(['admin']);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get jadwal info
$query = "SELECT ja.*, 
          ua.nama_unit, ua.jenis, ua.kode_unit, ua.pimpinan,
          u1.nama_lengkap as ketua_nama, u1.unit_kerja as ketua_unit,
          u2.nama_lengkap as auditee_nama
          FROM jadwal_audit ja
          JOIN unit_audit ua ON ja.unit_audit_id = ua.id
          JOIN users u1 ON ja.ketua_auditor = u1.id
          JOIN users u2 ON ja.auditee_id = u2.id
          WHERE ja.id = $id";
$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = 'Jadwal audit tidak ditemukan!';
    header("Location: laporan.php");
    exit();
}

$jadwal = mysqli_fetch_assoc($result);

// Get penilaian
$query_penilaian = "SELECT p.*, 
                    s.kode as standar_kode, s.nama_standar,
                    i.kode as indikator_kode, i.nama_indikator,
                    i.rubrik_0, i.rubrik_1, i.rubrik_2, i.rubrik_3, i.rubrik_4
                    FROM penilaian p
                    JOIN indikator i ON p.indikator_id = i.id
                    JOIN standar s ON i.standar_id = s.id
                    WHERE p.jadwal_audit_id = $id
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Laporan - <?php echo $jadwal['kode_audit']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>
        .score-box {
            text-align: center;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .score-number {
            font-size: 3rem;
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
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="laporan.php">Laporan</a></li>
                                <li class="breadcrumb-item active">Detail</li>
                            </ol>
                        </nav>
                        <h2 class="mb-0">Detail Laporan Audit</h2>
                        <p class="text-muted"><?php echo $jadwal['kode_audit']; ?></p>
                    </div>
                    <div>
                        <button class="btn btn-primary" onclick="window.print()">
                            <i class="bi bi-printer"></i> Print
                        </button>
                        <a href="laporan.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <!-- Informasi Audit -->
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
                                        <td><strong><?php echo $jadwal['nama_unit']; ?></strong></td>
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
                                            <?php echo date('d F Y', strtotime($jadwal['tanggal_mulai'])); ?> s/d 
                                            <?php echo date('d F Y', strtotime($jadwal['tanggal_selesai'])); ?>
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
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ringkasan Skor -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body score-box <?php 
                                if ($rata_skor >= 3.5) echo 'bg-success text-white';
                                elseif ($rata_skor >= 3.0) echo 'bg-primary text-white';
                                elseif ($rata_skor >= 2.0) echo 'bg-warning';
                                else echo 'bg-danger text-white';
                            ?>">
                                <h6 class="mb-2">Rata-rata Skor</h6>
                                <div class="score-number"><?php echo number_format($rata_skor, 2); ?></div>
                                <p class="mb-0">dari 4.00</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body score-box bg-info text-white">
                                <h6 class="mb-2">Total Penilaian</h6>
                                <div class="score-number"><?php echo $total_penilaian; ?></div>
                                <p class="mb-0">Indikator</p>
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
                                            <strong class="
                                                <?php 
                                                if ($avg >= 3.5) echo 'text-success';
                                                elseif ($avg >= 3.0) echo 'text-primary';
                                                elseif ($avg >= 2.0) echo 'text-warning';
                                                else echo 'text-danger';
                                                ?>
                                            ">
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
                        <h5 class="mb-0"><i class="bi bi-list-check"></i> Detail Penilaian Indikator</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="10%">Kode</th>
                                        <th width="30%">Indikator</th>
                                        <th width="8%">Skor</th>
                                        <th width="32%">Deskripsi Pencapaian</th>
                                        <th width="15%">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    mysqli_data_seek($penilaian, 0);
                                    $current_standar = '';
                                    while ($p = mysqli_fetch_assoc($penilaian)): 
                                        // Header standar
                                        if ($current_standar != $p['standar_kode']) {
                                            $current_standar = $p['standar_kode'];
                                            echo '<tr class="table-secondary">
                                                    <td colspan="6"><strong>' . $p['standar_kode'] . ' - ' . $p['nama_standar'] . '</strong></td>
                                                  </tr>';
                                        }
                                        
                                        // Get rubrik description
                                        $rubrik_desc = '';
                                        switch ($p['skor']) {
                                            case 0: $rubrik_desc = $p['rubrik_0']; break;
                                            case 1: $rubrik_desc = $p['rubrik_1']; break;
                                            case 2: $rubrik_desc = $p['rubrik_2']; break;
                                            case 3: $rubrik_desc = $p['rubrik_3']; break;
                                            case 4: $rubrik_desc = $p['rubrik_4']; break;
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo $p['indikator_kode']; ?></td>
                                        <td><?php echo $p['nama_indikator']; ?></td>
                                        <td class="text-center">
                                            <strong class="
                                                <?php 
                                                if ($p['skor'] >= 4) echo 'text-success';
                                                elseif ($p['skor'] >= 3) echo 'text-primary';
                                                elseif ($p['skor'] >= 2) echo 'text-warning';
                                                else echo 'text-danger';
                                                ?>
                                            ">
                                                <?php echo $p['skor']; ?>
                                            </strong>
                                        </td>
                                        <td><small><?php echo $rubrik_desc; ?></small></td>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>