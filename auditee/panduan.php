<?php
require_once '../inc/inc_koneksi.php';
require_once '../inc/inc_fungsi.php';

cek_role(['auditee']);

$user_id = $_SESSION['user_id'];

// Get user info
$user_query = mysqli_query($koneksi, "SELECT * FROM users WHERE id = $user_id");
$user_info = mysqli_fetch_assoc($user_query);

// Get pengaturan institusi untuk email dan telepon
$query_setting = "SELECT nilai FROM pengaturan WHERE nama_setting = 'email_institusi'";
$result_email = mysqli_query($koneksi, $query_setting);
$row_email = mysqli_fetch_assoc($result_email);
$email_institusi = $row_email['nilai'] ?? 'spmi@kampus.ac.id';

$query_telepon = "SELECT nilai FROM pengaturan WHERE nama_setting = 'telepon_institusi'";
$result_telepon = mysqli_query($koneksi, $query_telepon);
$row_telepon = mysqli_fetch_assoc($result_telepon);
$telepon_institusi = $row_telepon['nilai'] ?? '081234567890';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panduan Auditee - E-SPMI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>
        .panduan-card {
            transition: all 0.3s ease;
            border-left: 4px solid #667eea;
        }
        .panduan-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .step-number {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
        }
        .accordion-button:not(.collapsed) {
            background-color: #667eea;
            color: white;
        }
        .badge-info-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .icon-box {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8rem;
        }
        .timeline-item {
            position: relative;
            padding-left: 40px;
            margin-bottom: 30px;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 12px;
            top: 30px;
            bottom: -30px;
            width: 2px;
            background: #e0e0e0;
        }
        .timeline-item:last-child::before {
            display: none;
        }
        .timeline-dot {
            position: absolute;
            left: 0;
            top: 5px;
            width: 26px;
            height: 26px;
            background: #667eea;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 0 0 2px #667eea;
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-0"><i class="bi bi-question-circle"></i> Panduan Auditee</h2>
                        <p class="text-muted mb-0">Panduan lengkap penggunaan sistem E-SPMI untuk Auditee</p>
                    </div>
                    <button class="btn btn-primary" onclick="window.print()">
                        <i class="bi bi-printer"></i> Print Panduan
                    </button>
                </div>

                <!-- Welcome Card -->
                <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body text-white p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h3 class="mb-3"><i class="bi bi-book"></i> Selamat Datang di Panduan E-SPMI</h3>
                                <p class="mb-0 lead">
                                    Panduan ini akan membantu Anda memahami proses audit internal SPMI dan 
                                    bagaimana menggunakan sistem E-SPMI dengan efektif sebagai Auditee.
                                </p>
                            </div>
                            <div class="col-md-4 text-center">
                                <i class="bi bi-mortarboard-fill" style="font-size: 5rem; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Access Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="icon-box mx-auto mb-3">
                                    <i class="bi bi-list-check"></i>
                                </div>
                                <h6 class="fw-bold">Tentang SPMI</h6>
                                <p class="text-muted small mb-0">Pengenalan sistem penjaminan mutu</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="icon-box mx-auto mb-3">
                                    <i class="bi bi-person-badge"></i>
                                </div>
                                <h6 class="fw-bold">Peran Auditee</h6>
                                <p class="text-muted small mb-0">Tugas dan tanggung jawab auditee</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="icon-box mx-auto mb-3">
                                    <i class="bi bi-clipboard-data"></i>
                                </div>
                                <h6 class="fw-bold">Proses Audit</h6>
                                <p class="text-muted small mb-0">Tahapan dan pelaksanaan audit</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="icon-box mx-auto mb-3">
                                    <i class="bi bi-file-earmark-text"></i>
                                </div>
                                <h6 class="fw-bold">Dokumen</h6>
                                <p class="text-muted small mb-0">Persiapan dokumen audit</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="row">
                    <div class="col-lg-8">
                        
                        <!-- Tentang SPMI -->
                        <div class="card border-0 shadow-sm mb-4 panduan-card">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0">
                                    <i class="bi bi-info-circle text-primary"></i> Tentang SPMI
                                </h5>
                            </div>
                            <div class="card-body">
                                <p>
                                    <strong>Sistem Penjaminan Mutu Internal (SPMI)</strong> adalah kegiatan sistemik 
                                    penjaminan mutu pendidikan tinggi oleh setiap perguruan tinggi secara otonom 
                                    untuk mengendalikan dan meningkatkan penyelenggaraan pendidikan tinggi secara 
                                    berencana dan berkelanjutan.
                                </p>
                                <div class="alert alert-info border-0">
                                    <i class="bi bi-lightbulb"></i> 
                                    <strong>Tujuan SPMI:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Menjamin pemenuhan Standar Nasional Pendidikan Tinggi (SN-DIKTI)</li>
                                        <li>Mendorong peningkatan mutu pendidikan berkelanjutan</li>
                                        <li>Menjamin tercapainya visi dan misi perguruan tinggi</li>
                                        <li>Membangun budaya mutu di lingkungan perguruan tinggi</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Peran dan Tanggung Jawab -->
                        <div class="card border-0 shadow-sm mb-4 panduan-card">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0">
                                    <i class="bi bi-person-badge text-success"></i> Peran dan Tanggung Jawab Auditee
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-3">Sebagai <strong>Auditee</strong>, Anda memiliki peran penting dalam proses audit:</p>
                                
                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <h6 class="fw-bold">Menyiapkan Dokumen</h6>
                                    <p class="text-muted">Menyiapkan semua dokumen dan bukti yang diperlukan sesuai dengan standar SPMI</p>
                                </div>

                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <h6 class="fw-bold">Memberikan Informasi</h6>
                                    <p class="text-muted">Memberikan informasi yang akurat dan lengkap kepada auditor</p>
                                </div>

                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <h6 class="fw-bold">Mengikuti Proses Audit</h6>
                                    <p class="text-muted">Berpartisipasi aktif dalam keseluruhan proses audit mulai dari pembukaan hingga penutupan</p>
                                </div>

                                <div class="timeline-item">
                                    <div class="timeline-dot"></div>
                                    <h6 class="fw-bold">Tindak Lanjut</h6>
                                    <p class="text-muted">Melakukan tindak lanjut terhadap temuan dan rekomendasi hasil audit</p>
                                </div>
                            </div>
                        </div>

                        <!-- Tahapan Audit -->
                        <div class="card border-0 shadow-sm mb-4 panduan-card">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0">
                                    <i class="bi bi-diagram-3 text-warning"></i> Tahapan Proses Audit
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="accordion" id="accordionAudit">
                                    
                                    <!-- Tahap 1 -->
                                    <div class="accordion-item border-0 mb-2">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#tahap1">
                                                <div class="step-number me-3">1</div>
                                                <strong>Persiapan Audit</strong>
                                            </button>
                                        </h2>
                                        <div id="tahap1" class="accordion-collapse collapse show" data-bs-parent="#accordionAudit">
                                            <div class="accordion-body">
                                                <ul>
                                                    <li>Menerima notifikasi jadwal audit dari sistem</li>
                                                    <li>Memahami standar dan indikator yang akan diaudit</li>
                                                    <li>Menyiapkan dokumen dan bukti pendukung</li>
                                                    <li>Koordinasi dengan tim unit untuk kelengkapan data</li>
                                                </ul>
                                                <div class="alert alert-warning border-0 mb-0">
                                                    <i class="bi bi-exclamation-triangle"></i> 
                                                    <strong>Tips:</strong> Mulai persiapan segera setelah menerima notifikasi untuk memastikan semua dokumen lengkap
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tahap 2 -->
                                    <div class="accordion-item border-0 mb-2">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#tahap2">
                                                <div class="step-number me-3">2</div>
                                                <strong>Pelaksanaan Audit</strong>
                                            </button>
                                        </h2>
                                        <div id="tahap2" class="accordion-collapse collapse" data-bs-parent="#accordionAudit">
                                            <div class="accordion-body">
                                                <ul>
                                                    <li>Mengikuti rapat pembukaan audit (opening meeting)</li>
                                                    <li>Menyediakan akses ke dokumen dan lokasi yang diperlukan</li>
                                                    <li>Menjawab pertanyaan auditor dengan jujur dan lengkap</li>
                                                    <li>Memberikan penjelasan mengenai implementasi standar</li>
                                                    <li>Mendampingi auditor selama proses audit</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tahap 3 -->
                                    <div class="accordion-item border-0 mb-2">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#tahap3">
                                                <div class="step-number me-3">3</div>
                                                <strong>Pelaporan Hasil</strong>
                                            </button>
                                        </h2>
                                        <div id="tahap3" class="accordion-collapse collapse" data-bs-parent="#accordionAudit">
                                            <div class="accordion-body">
                                                <ul>
                                                    <li>Mengikuti rapat penutupan audit (closing meeting)</li>
                                                    <li>Menerima dan memahami temuan audit</li>
                                                    <li>Mengakses laporan hasil audit melalui sistem</li>
                                                    <li>Menganalisis skor dan kategori penilaian</li>
                                                    <li>Melihat rekomendasi perbaikan dari auditor</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tahap 4 -->
                                    <div class="accordion-item border-0">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#tahap4">
                                                <div class="step-number me-3">4</div>
                                                <strong>Tindak Lanjut</strong>
                                            </button>
                                        </h2>
                                        <div id="tahap4" class="accordion-collapse collapse" data-bs-parent="#accordionAudit">
                                            <div class="accordion-body">
                                                <ul>
                                                    <li>Menyusun rencana tindak lanjut (action plan)</li>
                                                    <li>Melakukan perbaikan pada area yang perlu ditingkatkan</li>
                                                    <li>Mendokumentasikan bukti perbaikan</li>
                                                    <li>Melaporkan progress tindak lanjut secara berkala</li>
                                                    <li>Mempersiapkan diri untuk audit berikutnya</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Persiapan Dokumen -->
                        <div class="card border-0 shadow-sm mb-4 panduan-card">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0">
                                    <i class="bi bi-folder-check text-danger"></i> Persiapan Dokumen Audit
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-3">Dokumen yang perlu disiapkan berdasarkan standar SPMI:</p>
                                
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="40%">Jenis Dokumen</th>
                                                <th>Contoh</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><strong>Dokumen Kebijakan</strong></td>
                                                <td>Renstra, Renop, SK, Peraturan</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Dokumen Standar</strong></td>
                                                <td>Standar Mutu, SOP, Manual</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Dokumen Pelaksanaan</strong></td>
                                                <td>Jadwal, Berita Acara, Notulensi</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Dokumen Bukti</strong></td>
                                                <td>Daftar Hadir, Foto, Sertifikat</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Dokumen Evaluasi</strong></td>
                                                <td>Laporan, Hasil Survey, Analisis</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="alert alert-success border-0 mt-3">
                                    <i class="bi bi-check-circle"></i> 
                                    <strong>Checklist Dokumen:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>✅ Dokumen tersusun rapi dan mudah diakses</li>
                                        <li>✅ Dokumen terbaru dan relevan dengan periode audit</li>
                                        <li>✅ Dokumen asli atau salinan resmi tersedia</li>
                                        <li>✅ Dokumen digital dalam format PDF atau image</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Tips dan Best Practice -->
                        <div class="card border-0 shadow-sm mb-4 panduan-card">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0">
                                    <i class="bi bi-star text-warning"></i> Tips dan Best Practice
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="card bg-success bg-opacity-10 border-success border-start border-3 h-100">
                                            <div class="card-body">
                                                <h6 class="text-success"><i class="bi bi-check-circle-fill"></i> Yang Harus Dilakukan</h6>
                                                <ul class="small mb-0">
                                                    <li>Jujur dan transparan dalam memberikan informasi</li>
                                                    <li>Siapkan dokumen backup digital dan fisik</li>
                                                    <li>Catat semua pertanyaan dan feedback auditor</li>
                                                    <li>Terbuka terhadap kritik dan saran</li>
                                                    <li>Koordinasi dengan tim sebelum audit</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card bg-danger bg-opacity-10 border-danger border-start border-3 h-100">
                                            <div class="card-body">
                                                <h6 class="text-danger"><i class="bi bi-x-circle-fill"></i> Yang Harus Dihindari</h6>
                                                <ul class="small mb-0">
                                                    <li>Menyembunyikan atau memanipulasi data</li>
                                                    <li>Tidak siap saat audit dilakukan</li>
                                                    <li>Defensif terhadap temuan auditor</li>
                                                    <li>Menunda-nunda tindak lanjut</li>
                                                    <li>Tidak mendokumentasikan perbaikan</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        
                        <!-- Menu Sistem -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-bottom">
                                <h6 class="mb-0"><i class="bi bi-grid"></i> Menu Sistem E-SPMI</h6>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <a href="index.php" class="list-group-item list-group-item-action">
                                        <i class="bi bi-speedometer2 text-primary"></i> Dashboard
                                        <small class="d-block text-muted">Ringkasan informasi audit</small>
                                    </a>
                                    <a href="riwayat.php" class="list-group-item list-group-item-action">
                                        <i class="bi bi-clock-history text-success"></i> Riwayat Audit
                                        <small class="d-block text-muted">Daftar audit yang sudah dilakukan</small>
                                    </a>
                                    <a href="grafik.php" class="list-group-item list-group-item-action">
                                        <i class="bi bi-graph-up text-info"></i> Grafik Perkembangan
                                        <small class="d-block text-muted">Visualisasi perkembangan skor</small>
                                    </a>
                                    <a href="standar.php" class="list-group-item list-group-item-action">
                                        <i class="bi bi-book text-warning"></i> Standar SPMI
                                        <small class="d-block text-muted">Referensi standar dan indikator</small>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Kategori Penilaian -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-bottom">
                                <h6 class="mb-0"><i class="bi bi-award"></i> Kategori Penilaian</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-success">Baik</span>
                                        <span class="fw-bold">≥ 3.50</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" style="width: 100%"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-primary">Cukup</span>
                                        <span class="fw-bold">3.00 - 3.49</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-primary" style="width: 75%"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-warning">Kurang</span>
                                        <span class="fw-bold">2.00 - 2.99</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-warning" style="width: 50%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-danger">Buruk</span>
                                        <span class="fw-bold">< 2.00</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-danger" style="width: 25%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Kontak Support -->
                        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <div class="card-body text-white">
                                <h6 class="mb-3"><i class="bi bi-headset"></i> Butuh Bantuan?</h6>
                                <p class="small mb-3">Jika Anda memiliki pertanyaan atau memerlukan bantuan, hubungi:</p>
                                <div class="d-grid gap-2">
                                    <a href="mailto:<?php echo htmlspecialchars($email_institusi); ?>" class="btn btn-light btn-sm text-start">
                                        <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($email_institusi); ?>
                                    </a>
                                    <a href="tel:<?php echo htmlspecialchars($telepon_institusi); ?>" class="btn btn-light btn-sm text-start">
                                        <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($telepon_institusi); ?>
                                    </a>
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
    <script src="../assets/js/sidebar-toggle.js"></script>
</body>
</html>