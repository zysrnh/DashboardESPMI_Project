<?php
require_once '../inc/inc_koneksi.php';
require_once '../inc/inc_fungsi.php';

cek_role(['auditor']);

$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panduan Auditor - E-SPMI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>
        .panduan-card {
            transition: all 0.3s;
            cursor: pointer;
        }
        .panduan-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
        .step-number {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
        }
        .accordion-button:not(.collapsed) {
            background-color: #667eea;
            color: white;
        }
        .faq-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
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
                        <h2 class="mb-0"><i class="bi bi-question-circle"></i> Panduan Auditor</h2>
                        <p class="text-muted mb-0">Panduan lengkap melakukan audit internal mutu</p>
                    </div>
                </div>

                <!-- Quick Access Cards -->
                <div class="row g-4 mb-5">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm panduan-card" onclick="scrollToSection('alur')">
                            <div class="card-body text-center">
                                <div class="faq-icon bg-primary text-white mx-auto mb-3">
                                    <i class="bi bi-diagram-3"></i>
                                </div>
                                <h5>Alur Audit</h5>
                                <p class="text-muted mb-0">Tahapan proses audit</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm panduan-card" onclick="scrollToSection('penilaian')">
                            <div class="card-body text-center">
                                <div class="faq-icon bg-success text-white mx-auto mb-3">
                                    <i class="bi bi-clipboard-check"></i>
                                </div>
                                <h5>Cara Penilaian</h5>
                                <p class="text-muted mb-0">Panduan melakukan penilaian</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm panduan-card" onclick="scrollToSection('skor')">
                            <div class="card-body text-center">
                                <div class="faq-icon bg-warning text-white mx-auto mb-3">
                                    <i class="bi bi-star"></i>
                                </div>
                                <h5>Skala Skor</h5>
                                <p class="text-muted mb-0">Pedoman pemberian skor</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm panduan-card" onclick="scrollToSection('faq')">
                            <div class="card-body text-center">
                                <div class="faq-icon bg-info text-white mx-auto mb-3">
                                    <i class="bi bi-question-lg"></i>
                                </div>
                                <h5>FAQ</h5>
                                <p class="text-muted mb-0">Pertanyaan umum</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alur Audit -->
                <div id="alur" class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-diagram-3"></i> Alur Proses Audit Internal</h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="d-flex">
                                    <div class="step-number bg-primary me-3">1</div>
                                    <div>
                                        <h5>Persiapan Audit</h5>
                                        <p class="text-muted">
                                            • Terima penugasan dari admin<br>
                                            • Pelajari standar dan indikator<br>
                                            • Koordinasi dengan tim auditor<br>
                                            • Hubungi auditee untuk jadwal
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex">
                                    <div class="step-number bg-success me-3">2</div>
                                    <div>
                                        <h5>Pelaksanaan Audit</h5>
                                        <p class="text-muted">
                                            • Buka menu "Tugas Audit Saya"<br>
                                            • Klik "Mulai Penilaian"<br>
                                            • Isi skor untuk setiap indikator<br>
                                            • Tambahkan catatan jika perlu
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex">
                                    <div class="step-number bg-warning me-3">3</div>
                                    <div>
                                        <h5>Pelaporan</h5>
                                        <p class="text-muted">
                                            • Review hasil penilaian<br>
                                            • Pastikan semua terisi<br>
                                            • Finalisasi laporan audit<br>
                                            • Sistem otomatis generate laporan
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cara Penilaian -->
                <div id="penilaian" class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="bi bi-clipboard-check"></i> Cara Melakukan Penilaian</h4>
                    </div>
                    <div class="card-body">
                        <ol class="list-group list-group-numbered">
                            <li class="list-group-item border-0 ps-0">
                                <strong>Buka Halaman Penilaian</strong>
                                <p class="mb-0 text-muted">Dari menu "Tugas Audit Saya", klik tombol "Mulai Penilaian" pada audit yang akan dilakukan.</p>
                            </li>
                            <li class="list-group-item border-0 ps-0">
                                <strong>Pahami Standar & Indikator</strong>
                                <p class="mb-0 text-muted">Baca dengan teliti standar SPMI dan indikator yang akan dinilai. Setiap indikator memiliki deskripsi dan kriteria penilaian.</p>
                            </li>
                            <li class="list-group-item border-0 ps-0">
                                <strong>Kumpulkan Bukti</strong>
                                <p class="mb-0 text-muted">Minta dokumen pendukung dari auditee seperti: dokumen kebijakan, SOP, laporan, foto kegiatan, dll.</p>
                            </li>
                            <li class="list-group-item border-0 ps-0">
                                <strong>Berikan Skor</strong>
                                <p class="mb-0 text-muted">Pilih skor 0-4 berdasarkan pedoman skala skor. Pertimbangkan bukti yang ada dan kondisi aktual di unit audit.</p>
                            </li>
                            <li class="list-group-item border-0 ps-0">
                                <strong>Tambahkan Catatan</strong>
                                <p class="mb-0 text-muted">Tulis catatan/komentar untuk menjelaskan alasan pemberian skor, temuan, atau rekomendasi perbaikan.</p>
                            </li>
                            <li class="list-group-item border-0 ps-0">
                                <strong>Simpan & Review</strong>
                                <p class="mb-0 text-muted">Simpan penilaian secara berkala. Setelah semua indikator dinilai, review kembali sebelum finalisasi.</p>
                            </li>
                        </ol>
                    </div>
                </div>

                <!-- Skala Skor -->
                <div id="skor" class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-warning text-white">
                        <h4 class="mb-0"><i class="bi bi-star"></i> Pedoman Skala Skor Penilaian</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th width="10%" class="text-center">Skor</th>
                                        <th width="20%">Kategori</th>
                                        <th width="70%">Deskripsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-center fs-3"><span class="badge bg-success">4</span></td>
                                        <td><strong>Sangat Baik</strong></td>
                                        <td>
                                            • Semua kriteria indikator terpenuhi dengan sangat baik<br>
                                            • Dokumen lengkap, sistematis, dan terimplementasi penuh<br>
                                            • Terdapat inovasi/best practice yang dapat dijadikan contoh<br>
                                            • Hasil/dampak sangat terukur dan melampaui target
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center fs-3"><span class="badge bg-primary">3</span></td>
                                        <td><strong>Baik</strong></td>
                                        <td>
                                            • Sebagian besar kriteria indikator terpenuhi dengan baik<br>
                                            • Dokumen lengkap dan terimplementasi<br>
                                            • Hasil/dampak terukur dan sesuai target<br>
                                            • Masih ada ruang untuk peningkatan kecil
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center fs-3"><span class="badge bg-info">2</span></td>
                                        <td><strong>Cukup</strong></td>
                                        <td>
                                            • Kriteria indikator terpenuhi secara minimal<br>
                                            • Dokumen ada tetapi belum lengkap<br>
                                            • Implementasi masih parsial/sebagian<br>
                                            • Hasil/dampak belum optimal
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center fs-3"><span class="badge bg-warning">1</span></td>
                                        <td><strong>Kurang</strong></td>
                                        <td>
                                            • Hanya sedikit kriteria yang terpenuhi<br>
                                            • Dokumen kurang lengkap atau tidak sistematis<br>
                                            • Implementasi sangat terbatas<br>
                                            • Hasil/dampak tidak terukur
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center fs-3"><span class="badge bg-danger">0</span></td>
                                        <td><strong>Tidak Ada</strong></td>
                                        <td>
                                            • Kriteria indikator tidak terpenuhi sama sekali<br>
                                            • Tidak ada dokumen pendukung<br>
                                            • Tidak ada implementasi<br>
                                            • Tidak ada hasil/dampak
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <i class="bi bi-info-circle"></i> <strong>Tips Penilaian:</strong><br>
                            • Objektif: Nilai berdasarkan bukti dan fakta, bukan asumsi<br>
                            • Konsisten: Gunakan standar yang sama untuk semua unit<br>
                            • Adil: Pertimbangkan konteks dan kondisi unit audit<br>
                            • Konstruktif: Berikan feedback yang membangun untuk perbaikan
                        </div>
                    </div>
                </div>

                <!-- FAQ -->
                <div id="faq" class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0"><i class="bi bi-question-lg"></i> Pertanyaan yang Sering Diajukan (FAQ)</h4>
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="faqAccordion">
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                        Apa yang harus dilakukan jika auditee tidak menyediakan dokumen pendukung?
                                    </button>
                                </h2>
                                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Hubungi auditee dan berikan tenggang waktu yang wajar (misalnya 3-7 hari) untuk melengkapi dokumen. Jika tetap tidak ada, berikan skor sesuai kondisi aktual dan catat hal ini sebagai temuan audit. Koordinasikan dengan Ketua Auditor atau admin jika diperlukan.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                        Bagaimana jika ragu dalam memberikan skor?
                                    </button>
                                </h2>
                                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Diskusikan dengan Ketua Auditor atau tim auditor lain. Rujuk kembali ke pedoman skala skor dan standar SPMI. Jika masih ragu, berikan skor konservatif (lebih rendah) dan jelaskan alasannya di catatan penilaian.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                        Apakah skor yang sudah disimpan bisa diubah?
                                    </button>
                                </h2>
                                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Ya, skor dapat diubah selama audit masih berstatus "Berlangsung". Setelah audit selesai dan status berubah menjadi "Selesai", skor tidak dapat diubah lagi. Pastikan untuk review semua penilaian sebelum finalisasi.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                        Berapa lama waktu yang ideal untuk melakukan audit?
                                    </button>
                                </h2>
                                <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Tergantung kompleksitas unit dan jumlah indikator. Umumnya 2-5 hari kerja, termasuk persiapan, desk evaluation, visitasi, dan penyusunan laporan. Jadwal audit sudah ditentukan oleh admin, pastikan menyelesaikan sebelum tanggal selesai.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                        Apa perbedaan Ketua Auditor dan Anggota Tim?
                                    </button>
                                </h2>
                                <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <strong>Ketua Auditor:</strong> Bertanggung jawab koordinasi tim, komunikasi dengan auditee, memastikan kelancaran proses, dan finalisasi laporan.<br>
                                        <strong>Anggota Tim:</strong> Membantu proses audit, melakukan penilaian pada indikator yang ditugaskan, dan berkontribusi dalam penyusunan laporan.<br><br>
                                        Keduanya memiliki akses yang sama untuk melakukan penilaian di sistem.
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                        Bagaimana cara menghubungi admin jika ada masalah teknis?
                                    </button>
                                </h2>
                                <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        Hubungi admin melalui email atau sistem notifikasi internal. Jelaskan masalah dengan detail (screenshot jika perlu). Admin akan membantu menyelesaikan masalah teknis sesegera mungkin.
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Download Materials -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-download"></i> Unduh Materi Pendukung</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center border rounded p-3">
                                    <div class="faq-icon bg-danger text-white me-3">
                                        <i class="bi bi-file-pdf"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">Pedoman Audit Internal SPMI</h6>
                                        <small class="text-muted">Format: PDF | Ukuran: 2.5 MB</small>
                                    </div>
                                    <a href="#" class="btn btn-sm btn-primary">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center border rounded p-3">
                                    <div class="faq-icon bg-success text-white me-3">
                                        <i class="bi bi-file-excel"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">Template Catatan Audit</h6>
                                        <small class="text-muted">Format: Excel | Ukuran: 150 KB</small>
                                    </div>
                                    <a href="#" class="btn btn-sm btn-success">
                                        <i class="bi bi-download"></i>
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
    <script>
        function scrollToSection(sectionId) {
            const element = document.getElementById(sectionId);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    </script>
</body>
</html>