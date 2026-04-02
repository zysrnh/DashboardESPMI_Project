<?php
require_once '../inc/inc_koneksi.php';
require_once '../inc/inc_fungsi.php';

cek_login();

// Get all standar with indikator
$query = "SELECT s.*, 
          (SELECT COUNT(*) FROM indikator WHERE standar_id=s.id AND status='aktif') as jumlah_indikator
          FROM standar s
          WHERE s.status='aktif'
          ORDER BY s.urutan ASC";
$standar = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Standar SPMI - E-SPMI</title>
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-0"><i class="bi bi-book"></i> Standar SPMI</h2>
                        <p class="text-muted">Referensi Standar Sistem Penjaminan Mutu Internal</p>
                    </div>
                    <button class="btn btn-primary" onclick="window.print()">
                        <i class="bi bi-printer"></i> Print
                    </button>
                </div>

                <!-- Info Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="mb-3"><i class="bi bi-info-circle text-primary"></i> Tentang SPMI</h5>
                        <p class="mb-2">
                            <strong>Sistem Penjaminan Mutu Internal (SPMI)</strong> adalah kegiatan sistemik 
                            penjaminan mutu pendidikan tinggi oleh setiap perguruan tinggi secara otonom untuk 
                            mengendalikan dan meningkatkan penyelenggaraan pendidikan tinggi secara berencana 
                            dan berkelanjutan.
                        </p>
                        <p class="mb-0">
                            SPMI terdiri dari beberapa standar yang mencakup seluruh aspek pengelolaan perguruan tinggi 
                            untuk menjamin mutu pendidikan, penelitian, dan pengabdian kepada masyarakat.
                        </p>
                    </div>
                </div>

                <!-- Accordion Standar -->
                <div class="accordion" id="accordionStandar">
                    <?php 
                    $no = 0;
                    while ($std = mysqli_fetch_assoc($standar)): 
                        $no++;
                        
                        // Get indikator
                        $query_ind = "SELECT * FROM indikator 
                                     WHERE standar_id=" . $std['id'] . " 
                                     AND status='aktif' 
                                     ORDER BY urutan ASC";
                        $indikator = mysqli_query($koneksi, $query_ind);
                    ?>
                    <div class="accordion-item mb-3 border-0 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button <?php echo $no > 1 ? 'collapsed' : ''; ?>" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#collapse<?php echo $no; ?>">
                                <div class="w-100 me-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong class="text-primary fs-5"><?php echo $std['kode']; ?></strong> - 
                                            <?php echo $std['nama_standar']; ?>
                                        </div>
                                        <span class="badge bg-info fs-6">
                                            <?php echo $std['jumlah_indikator']; ?> Indikator
                                        </span>
                                    </div>
                                </div>
                            </button>
                        </h2>
                        <div id="collapse<?php echo $no; ?>" 
                             class="accordion-collapse collapse <?php echo $no == 1 ? 'show' : ''; ?>" 
                             data-bs-parent="#accordionStandar">
                            <div class="accordion-body">
                                <!-- Deskripsi Standar -->
                                <div class="alert alert-light">
                                    <h6 class="mb-2"><i class="bi bi-file-text"></i> Deskripsi Standar</h6>
                                    <p class="mb-0"><?php echo $std['deskripsi'] ?: 'Tidak ada deskripsi.'; ?></p>
                                </div>

                                <!-- Indikator List -->
                                <h6 class="mb-3 mt-4"><i class="bi bi-list-check"></i> Daftar Indikator</h6>
                                
                                <?php if (mysqli_num_rows($indikator) == 0): ?>
                                <p class="text-muted">Belum ada indikator untuk standar ini.</p>
                                <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="10%">Kode</th>
                                                <th width="35%">Indikator</th>
                                                <th width="50%">Rubrik Penilaian</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $ind_no = 1;
                                            while ($ind = mysqli_fetch_assoc($indikator)): 
                                            ?>
                                            <tr>
                                                <td><?php echo $ind_no++; ?></td>
                                                <td><span class="badge bg-dark"><?php echo $ind['kode']; ?></span></td>
                                                <td><?php echo $ind['nama_indikator']; ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            type="button" 
                                                            data-bs-toggle="collapse" 
                                                            data-bs-target="#rubrik<?php echo $ind['id']; ?>">
                                                        <i class="bi bi-eye"></i> Lihat Rubrik
                                                    </button>
                                                    
                                                    <div class="collapse mt-2" id="rubrik<?php echo $ind['id']; ?>">
                                                        <div class="card card-body bg-light">
                                                            <small>
                                                                <div class="mb-2">
                                                                    <span class="badge bg-danger">0</span> 
                                                                    <strong>Tidak Memenuhi:</strong> 
                                                                    <?php echo $ind['rubrik_0']; ?>
                                                                </div>
                                                                <div class="mb-2">
                                                                    <span class="badge bg-warning">1</span> 
                                                                    <strong>Kurang:</strong> 
                                                                    <?php echo $ind['rubrik_1']; ?>
                                                                </div>
                                                                <div class="mb-2">
                                                                    <span class="badge bg-info">2</span> 
                                                                    <strong>Cukup:</strong> 
                                                                    <?php echo $ind['rubrik_2']; ?>
                                                                </div>
                                                                <div class="mb-2">
                                                                    <span class="badge bg-primary">3</span> 
                                                                    <strong>Baik:</strong> 
                                                                    <?php echo $ind['rubrik_3']; ?>
                                                                </div>
                                                                <div class="mb-0">
                                                                    <span class="badge bg-success">4</span> 
                                                                    <strong>Sangat Baik:</strong> 
                                                                    <?php echo $ind['rubrik_4']; ?>
                                                                </div>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

                <!-- Footer Info -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body">
                        <h6><i class="bi bi-info-circle"></i> Keterangan</h6>
                        <ul class="mb-0">
                            <li>Setiap standar terdiri dari beberapa indikator yang terukur</li>
                            <li>Penilaian menggunakan skala 0-4 dengan rubrik yang jelas</li>
                            <li>Standar ini menjadi acuan dalam pelaksanaan Audit Mutu Internal (AMI)</li>
                        </ul>
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