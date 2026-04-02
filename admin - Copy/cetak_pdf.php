<?php
require_once '../inc/inc_koneksi.php';
require_once '../inc/inc_fungsi.php';

cek_login();

$jadwal_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get jadwal info
$query = "SELECT ja.*, 
          ua.nama_unit, ua.jenis, ua.kode_unit, ua.pimpinan,
          u1.nama_lengkap as ketua_nama, u1.unit_kerja as ketua_unit,
          u2.nama_lengkap as auditee_nama
          FROM jadwal_audit ja
          JOIN unit_audit ua ON ja.unit_audit_id = ua.id
          JOIN users u1 ON ja.ketua_auditor = u1.id
          JOIN users u2 ON ja.auditee_id = u2.id
          WHERE ja.id = $jadwal_id";
$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) == 0) {
    die('Data tidak ditemukan!');
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
    <title>Laporan Audit - <?php echo $jadwal['kode_audit']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
        }
        body {
            font-size: 12pt;
            line-height: 1.5;
        }
        .header-logo {
            text-align: center;
            border-bottom: 3px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .score-box {
            text-align: center;
            padding: 20px;
            border: 3px solid #333;
            border-radius: 10px;
            margin: 20px 0;
        }
        .score-number {
            font-size: 48pt;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        table, th, td {
            border: 1px solid #333;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
        }
        .signature-section {
            margin-top: 50px;
        }
        .signature-box {
            text-align: center;
            margin-top: 80px;
        }
    </style>
</head>
<body>
    <button class="btn btn-primary no-print" onclick="window.print()" style="position: fixed; top: 10px; right: 10px;">
        <i class="bi bi-printer"></i> Print / Save PDF
    </button>

    <div class="container mt-4">
        <!-- Header -->
        <div class="header-logo">
            <h2>LAPORAN HASIL AUDIT MUTU INTERNAL (AMI)</h2>
            <h3>SISTEM PENJAMINAN MUTU INTERNAL (SPMI)</h3>
            <p class="mb-0">Universitas/Institusi XYZ</p>
        </div>

        <!-- Info Audit -->
        <table class="table-borderless mb-4">
            <tr>
                <td width="30%"><strong>Kode Audit</strong></td>
                <td width="5%">:</td>
                <td><?php echo $jadwal['kode_audit']; ?></td>
            </tr>
            <tr>
                <td><strong>Unit yang Diaudit</strong></td>
                <td>:</td>
                <td><?php echo $jadwal['nama_unit']; ?></td>
            </tr>
            <tr>
                <td><strong>Pimpinan Unit</strong></td>
                <td>:</td>
                <td><?php echo $jadwal['pimpinan']; ?></td>
            </tr>
            <tr>
                <td><strong>Periode</strong></td>
                <td>:</td>
                <td><?php echo $jadwal['tahun_akademik'] . ' - ' . ucfirst($jadwal['semester']); ?></td>
            </tr>
            <tr>
                <td><strong>Tanggal Pelaksanaan</strong></td>
                <td>:</td>
                <td>
                    <?php echo tanggal_indo($jadwal['tanggal_mulai']); ?> s/d 
                    <?php echo tanggal_indo($jadwal['tanggal_selesai']); ?>
                </td>
            </tr>
            <tr>
                <td><strong>Ketua Auditor</strong></td>
                <td>:</td>
                <td><?php echo $jadwal['ketua_nama']; ?></td>
            </tr>
            <tr>
                <td><strong>Auditee</strong></td>
                <td>:</td>
                <td><?php echo $jadwal['auditee_nama']; ?></td>
            </tr>
        </table>

        <!-- Ringkasan Hasil -->
        <h4 class="mt-4 mb-3">I. RINGKASAN HASIL AUDIT</h4>
        
        <div class="score-box">
            <h5>RATA-RATA SKOR AUDIT</h5>
            <div class="score-number"><?php echo number_format($rata_skor, 2); ?></div>
            <p class="mb-0">dari skala 4.00</p>
            <h5 class="mt-3"><?php echo $kategori; ?></h5>
        </div>

        <table>
            <tr>
                <th width="30%">Kategori</th>
                <th>Keterangan</th>
            </tr>
            <tr>
                <td>Total Indikator Dinilai</td>
                <td><?php echo $total_penilaian; ?> Indikator</td>
            </tr>
            <tr>
                <td>Persentase Pencapaian</td>
                <td><?php echo number_format(($rata_skor / 4) * 100, 1); ?>%</td>
            </tr>
            <tr>
                <td>Status Pemenuhan Standar</td>
                <td><strong><?php echo $kategori; ?></strong></td>
            </tr>
        </table>

        <!-- Skor Per Standar -->
        <h4 class="mt-4 mb-3">II. SKOR PER STANDAR SPMI</h4>
        
        <table>
            <thead>
                <tr>
                    <th width="10%">Kode</th>
                    <th width="40%">Nama Standar</th>
                    <th width="15%" class="text-center">Jumlah Indikator</th>
                    <th width="15%" class="text-center">Rata-rata Skor</th>
                    <th width="20%">Kategori</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($skor_per_standar as $kode => $data): 
                    $avg = $data['total'] / $data['count'];
                    
                    if ($avg >= 3.5) $kat = 'Sangat Baik';
                    elseif ($avg >= 3.0) $kat = 'Baik';
                    elseif ($avg >= 2.0) $kat = 'Cukup';
                    else $kat = 'Perlu Perbaikan';
                ?>
                <tr>
                    <td><?php echo $kode; ?></td>
                    <td><?php echo $data['nama']; ?></td>
                    <td class="text-center"><?php echo $data['count']; ?></td>
                    <td class="text-center"><strong><?php echo number_format($avg, 2); ?></strong></td>
                    <td><?php echo $kat; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Detail Penilaian -->
        <h4 class="mt-4 mb-3">III. DETAIL PENILAIAN PER INDIKATOR</h4>
        
        <?php 
        mysqli_data_seek($penilaian, 0);
        $current_standar = '';
        while ($p = mysqli_fetch_assoc($penilaian)): 
            if ($current_standar != $p['standar_kode']):
                if ($current_standar != '') echo '</tbody></table>';
                $current_standar = $p['standar_kode'];
        ?>
        <h5 class="mt-3"><?php echo $p['standar_kode']; ?> - <?php echo $p['nama_standar']; ?></h5>
        <table>
            <thead>
                <tr>
                    <th width="10%">Kode</th>
                    <th width="35%">Indikator</th>
                    <th width="10%">Skor</th>
                    <th width="45%">Deskripsi Pencapaian</th>
                </tr>
            </thead>
            <tbody>
        <?php endif; ?>
                <tr>
                    <td><?php echo $p['indikator_kode']; ?></td>
                    <td><?php echo $p['nama_indikator']; ?></td>
                    <td class="text-center"><strong><?php echo $p['skor']; ?></strong></td>
                    <td>
                        <?php 
                        switch ($p['skor']) {
                            case 0: echo $p['rubrik_0']; break;
                            case 1: echo $p['rubrik_1']; break;
                            case 2: echo $p['rubrik_2']; break;
                            case 3: echo $p['rubrik_3']; break;
                            case 4: echo $p['rubrik_4']; break;
                        }
                        ?>
                    </td>
                </tr>
        <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Kesimpulan -->
        <h4 class="mt-4 mb-3">IV. KESIMPULAN DAN REKOMENDASI</h4>
        
        <p style="text-align: justify;">
            Berdasarkan hasil Audit Mutu Internal (AMI) yang telah dilaksanakan pada 
            <strong><?php echo $jadwal['nama_unit']; ?></strong> periode 
            <strong><?php echo $jadwal['tahun_akademik'] . ' - ' . ucfirst($jadwal['semester']); ?></strong>, 
            diperoleh rata-rata skor <strong><?php echo number_format($rata_skor, 2); ?></strong> dari skala 4.00.
        </p>
        
        <p style="text-align: justify;">
            Hasil ini menunjukkan bahwa unit berada dalam kategori 
            <strong>"<?php echo $kategori; ?>"</strong>. 
            <?php if ($rata_skor >= 3.5): ?>
            Unit telah menunjukkan kinerja yang sangat baik dan melampaui standar yang ditetapkan. 
            Diharapkan unit dapat mempertahankan dan terus meningkatkan capaian ini.
            <?php elseif ($rata_skor >= 3.0): ?>
            Unit telah memenuhi standar yang ditetapkan dengan baik. 
            Diharapkan unit dapat terus meningkatkan kualitas untuk mencapai kategori sangat baik.
            <?php elseif ($rata_skor >= 2.0): ?>
            Unit hampir memenuhi standar yang ditetapkan. 
            Diperlukan perbaikan dan peningkatan pada beberapa indikator untuk mencapai standar yang diharapkan.
            <?php else: ?>
            Unit perlu melakukan perbaikan signifikan untuk memenuhi standar yang ditetapkan. 
            Diperlukan tindak lanjut dan pendampingan intensif.
            <?php endif; ?>
        </p>

        <!-- Tanda Tangan -->
        <div class="signature-section">
            <div class="row">
                <div class="col-6">
                    <p class="mb-0">Mengetahui,</p>
                    <p class="mb-0"><strong>Auditee (Pimpinan Unit)</strong></p>
                    <div class="signature-box">
                        <p class="mb-0"><strong><u><?php echo $jadwal['pimpinan']; ?></u></strong></p>
                    </div>
                </div>
                <div class="col-6">
                    <p class="mb-0"><?php echo ucfirst(nama_kota()); ?>, <?php echo tanggal_indo(date('Y-m-d')); ?></p>
                    <p class="mb-0"><strong>Ketua Auditor</strong></p>
                    <div class="signature-box">
                        <p class="mb-0"><strong><u><?php echo $jadwal['ketua_nama']; ?></u></strong></p>
                    </div>
                </div>
            </div>
        </div>

        <div style="page-break-after: always;"></div>
        
        <!-- Footer -->
        <div class="text-center mt-5">
            <small>
                <em>Dokumen ini dibuat secara otomatis melalui Sistem E-SPMI</em><br>
                <em>Dicetak pada: <?php echo date('d F Y H:i:s'); ?></em>
            </small>
        </div>
    </div>

    <script>
        // Auto print on load (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>