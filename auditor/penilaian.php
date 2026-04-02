<?php
require_once '../inc/inc_koneksi.php';
require_once '../inc/inc_fungsi.php';

cek_role(['auditor']);

$user_id = $_SESSION['user_id'];
$jadwal_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validasi akses auditor
$check = mysqli_query($koneksi, "SELECT * FROM jadwal_audit WHERE id=$jadwal_id AND (ketua_auditor=$user_id OR FIND_IN_SET($user_id, anggota_auditor))");
if (mysqli_num_rows($check) == 0) {
    $_SESSION['error'] = 'Anda tidak memiliki akses ke audit ini!';
    header("Location: index.php");
    exit();
}

$jadwal = mysqli_fetch_assoc($check);

// Get unit info
$unit_query = "SELECT * FROM unit_audit WHERE id=" . $jadwal['unit_audit_id'];
$unit = mysqli_fetch_assoc(mysqli_query($koneksi, $unit_query));

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_penilaian'])) {
    $indikator_id = (int)$_POST['indikator_id'];
    $skor = (int)$_POST['skor'];
    $catatan = mysqli_real_escape_string($koneksi, $_POST['catatan']);
    $tanggal_penilaian = date('Y-m-d');
    $bukti = '';
    
    // Debug
    error_log("POST Data: jadwal_id=$jadwal_id, indikator_id=$indikator_id, skor=$skor");
    
    // Handle file upload
    if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] == 0) {
        $target_dir = "../uploads/bukti/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'xlsx', 'xls'];
        
        if (in_array($file_extension, $allowed_ext)) {
            $new_filename = 'bukti_' . $jadwal_id . '_' . $indikator_id . '_' . time() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['bukti']['tmp_name'], $target_file)) {
                $bukti = $new_filename;
                error_log("File uploaded: $bukti");
            } else {
                error_log("File upload failed");
            }
        }
    }
    
    // Check if already exists
    $check_sql = "SELECT id FROM penilaian WHERE jadwal_audit_id=$jadwal_id AND indikator_id=$indikator_id";
    $check_penilaian = mysqli_query($koneksi, $check_sql);
    
    error_log("Check SQL: $check_sql");
    error_log("Existing rows: " . mysqli_num_rows($check_penilaian));
    
    if (mysqli_num_rows($check_penilaian) > 0) {
        // Update existing
        $existing = mysqli_fetch_assoc($check_penilaian);
        $update_sql = "UPDATE penilaian SET 
                       skor = $skor, 
                       catatan = '$catatan', 
                       tanggal_penilaian = '$tanggal_penilaian',
                       auditor_id = $user_id";
        
        if ($bukti != '') {
            $update_sql .= ", bukti = '$bukti'";
        }
        
        $update_sql .= " WHERE id = " . $existing['id'];
        
        error_log("Update SQL: $update_sql");
        
        if (mysqli_query($koneksi, $update_sql)) {
            log_aktivitas($user_id, 'Update penilaian indikator ID: ' . $indikator_id, 'penilaian', $existing['id']);
            $_SESSION['success'] = 'Penilaian berhasil diupdate!';
            error_log("Update SUCCESS");
        } else {
            $_SESSION['error'] = 'Gagal update: ' . mysqli_error($koneksi);
            error_log("Update ERROR: " . mysqli_error($koneksi));
        }
    } else {
        // Insert new
        $insert_sql = "INSERT INTO penilaian 
                       (jadwal_audit_id, indikator_id, skor, catatan, bukti, auditor_id, tanggal_penilaian, created_at) 
                       VALUES 
                       ($jadwal_id, $indikator_id, $skor, '$catatan', '$bukti', $user_id, '$tanggal_penilaian', NOW())";
        
        error_log("Insert SQL: $insert_sql");
        
        if (mysqli_query($koneksi, $insert_sql)) {
            $new_id = mysqli_insert_id($koneksi);
            log_aktivitas($user_id, 'Tambah penilaian indikator ID: ' . $indikator_id, 'penilaian', $new_id);
            $_SESSION['success'] = 'Penilaian berhasil disimpan! (ID: ' . $new_id . ')';
            error_log("Insert SUCCESS - New ID: $new_id");
        } else {
            $_SESSION['error'] = 'Gagal simpan: ' . mysqli_error($koneksi);
            error_log("Insert ERROR: " . mysqli_error($koneksi));
        }
    }
    
    header("Location: penilaian.php?id=$jadwal_id");
    exit();
}

// Get all standar with indikator
$query_standar = "SELECT s.* 
                  FROM standar s 
                  WHERE s.status='aktif' 
                  AND EXISTS (SELECT 1 FROM indikator WHERE standar_id=s.id AND status='aktif')
                  ORDER BY s.urutan ASC";
$standar = mysqli_query($koneksi, $query_standar);

// Get existing penilaian
$query_penilaian = "SELECT * FROM penilaian WHERE jadwal_audit_id=$jadwal_id";
$result_penilaian = mysqli_query($koneksi, $query_penilaian);
$penilaian_data = [];
while ($p = mysqli_fetch_assoc($result_penilaian)) {
    $penilaian_data[$p['indikator_id']] = $p;
}

// Calculate progress
$total_indikator = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM indikator WHERE status='aktif'"))['total'];
$jumlah_penilaian = count($penilaian_data);
$progress = $total_indikator > 0 ? ($jumlah_penilaian / $total_indikator) * 100 : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penilaian Audit - <?php echo $jadwal['kode_audit']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>
        .indikator-card {
            transition: all 0.3s;
            cursor: pointer;
            border: 2px solid #e9ecef;
        }
        .indikator-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
            border-color: #667eea;
        }
        .indikator-scored {
            border-left: 5px solid #198754 !important;
            background-color: #f0fdf4;
        }
        .rubrik-option {
            border: 2px solid #e9ecef;
            padding: 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 10px;
        }
        .rubrik-option:hover {
            border-color: #667eea;
            background-color: #f8f9fa;
        }
        .rubrik-option.selected {
            border-color: #667eea;
            background-color: #e7f1ff;
            border-width: 3px;
        }
        .score-badge {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
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
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Penilaian Audit</li>
                    </ol>
                </nav>

                <!-- Alert -->
                <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Info Audit -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4 class="mb-2"><?php echo $jadwal['kode_audit']; ?></h4>
                                <p class="mb-2">
                                    <span class="badge bg-info"><?php echo $unit['kode_unit']; ?></span>
                                    <strong><?php echo $unit['nama_unit']; ?></strong>
                                </p>
                                <p class="mb-0 text-muted">
                                    <i class="bi bi-calendar"></i> <?php echo $jadwal['tahun_akademik']; ?> - <?php echo ucfirst($jadwal['semester']); ?>
                                </p>
                            </div>
                            <div class="col-md-4">
                                <div class="text-end">
                                    <h6 class="text-muted mb-2">Progress Penilaian</h6>
                                    <h3 class="mb-2"><?php echo number_format($progress, 1); ?>%</h3>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $progress; ?>%"></div>
                                    </div>
                                    <small class="text-muted">
                                        <strong><?php echo $jumlah_penilaian; ?></strong> dari <?php echo $total_indikator; ?> indikator
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Accordion Standar -->
                <div class="accordion" id="accordionStandar">
                    <?php 
                    $standar_no = 0;
                    while ($std = mysqli_fetch_assoc($standar)): 
                        $standar_no++;
                        
                        // Get indikator for this standar
                        $query_indikator = "SELECT * FROM indikator 
                                           WHERE standar_id=" . $std['id'] . " 
                                           AND status='aktif' 
                                           ORDER BY urutan ASC";
                        $result_indikator = mysqli_query($koneksi, $query_indikator);
                        $temp_indikator = [];
                        
                        // Count scored indikator
                        $scored_count = 0;
                        while ($ind = mysqli_fetch_assoc($result_indikator)) {
                            $temp_indikator[] = $ind;
                            if (isset($penilaian_data[$ind['id']])) {
                                $scored_count++;
                            }
                        }
                        
                        if (count($temp_indikator) == 0) continue;
                    ?>
                    <div class="accordion-item mb-3 border-0 shadow-sm">
                        <h2 class="accordion-header">
                            <button class="accordion-button <?php echo $standar_no > 1 ? 'collapsed' : ''; ?>" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#collapse<?php echo $standar_no; ?>">
                                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                    <div>
                                        <strong class="text-primary"><?php echo $std['kode']; ?></strong> - <?php echo $std['nama_standar']; ?>
                                    </div>
                                    <span class="badge bg-<?php echo $scored_count == count($temp_indikator) ? 'success' : ($scored_count > 0 ? 'warning' : 'secondary'); ?> fs-6">
                                        <i class="bi bi-check-circle"></i> <?php echo $scored_count; ?> / <?php echo count($temp_indikator); ?>
                                    </span>
                                </div>
                            </button>
                        </h2>
                        <div id="collapse<?php echo $standar_no; ?>" 
                             class="accordion-collapse collapse <?php echo $standar_no == 1 ? 'show' : ''; ?>" 
                             data-bs-parent="#accordionStandar">
                            <div class="accordion-body">
                                <div class="row g-3">
                                    <?php foreach ($temp_indikator as $ind): 
                                        $is_scored = isset($penilaian_data[$ind['id']]);
                                        $penilaian_detail = $is_scored ? $penilaian_data[$ind['id']] : null;
                                    ?>
                                    <div class="col-lg-6">
                                        <div class="card indikator-card h-100 <?php echo $is_scored ? 'indikator-scored' : ''; ?>" 
                                             onclick='openModal(<?php echo json_encode($ind); ?>, <?php echo $penilaian_detail ? json_encode($penilaian_detail) : 'null'; ?>)'>
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <span class="badge bg-dark"><?php echo $ind['kode']; ?></span>
                                                    <?php if ($is_scored): ?>
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle-fill"></i> Skor: <?php echo $penilaian_detail['skor']; ?>
                                                    </span>
                                                    <?php else: ?>
                                                    <span class="badge bg-secondary">
                                                        <i class="bi bi-dash-circle"></i> Belum dinilai
                                                    </span>
                                                    <?php endif; ?>
                                                </div>
                                                <p class="mb-0"><?php echo $ind['nama_indikator']; ?></p>
                                                <?php if ($is_scored && $penilaian_detail['catatan']): ?>
                                                <small class="text-muted d-block mt-2">
                                                    <i class="bi bi-chat-left-text"></i> <?php echo substr($penilaian_detail['catatan'], 0, 50); ?>...
                                                </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

            </div>
        </div>
        <?php include 'footer.php'; ?>
    </div>

    <!-- Modal Penilaian -->
    <div class="modal fade" id="penilaianModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Form Penilaian Indikator</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" id="formPenilaian">
                    <input type="hidden" name="submit_penilaian" value="1">
                    <div class="modal-body">
                        <input type="hidden" name="indikator_id" id="indikator_id">
                        
                        <div class="alert alert-info">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-info-circle fs-4 me-3"></i>
                                <div>
                                    <h6 id="indikator_kode" class="mb-1"></h6>
                                    <p id="indikator_nama" class="mb-0"></p>
                                </div>
                            </div>
                        </div>

                        <h6 class="mb-3"><i class="bi bi-star"></i> Pilih Skor Penilaian:</h6>

                        <div id="rubrik_container"></div>

                        <hr class="my-4">

                        <div class="mb-3">
                            <label class="form-label"><i class="bi bi-chat-left-text"></i> Catatan/Keterangan</label>
                            <textarea class="form-control" name="catatan" id="catatan" rows="3" 
                                      placeholder="Tambahkan catatan atau keterangan penilaian (opsional)..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><i class="bi bi-paperclip"></i> Upload Bukti/Dokumen Pendukung (Opsional)</label>
                            <input type="file" class="form-control" name="bukti" id="bukti" 
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls">
                            <small class="text-muted">Format: PDF, Word, Excel, atau Gambar (Max 5MB)</small>
                            <div id="existing_bukti"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Penilaian
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openModal(indikator, existing) {
            console.log('Indikator:', indikator);
            console.log('Existing:', existing);
            
            document.getElementById('indikator_id').value = indikator.id;
            document.getElementById('indikator_kode').textContent = indikator.kode;
            document.getElementById('indikator_nama').textContent = indikator.nama_indikator;
            
            // Build rubrik
            const rubrik = [
                { skor: 0, desc: indikator.rubrik_0, color: 'danger', label: 'Tidak Memenuhi' },
                { skor: 1, desc: indikator.rubrik_1, color: 'warning', label: 'Kurang' },
                { skor: 2, desc: indikator.rubrik_2, color: 'info', label: 'Cukup' },
                { skor: 3, desc: indikator.rubrik_3, color: 'primary', label: 'Baik' },
                { skor: 4, desc: indikator.rubrik_4, color: 'success', label: 'Sangat Baik' }
            ];
            
            let html = '';
            rubrik.forEach(r => {
                const checked = existing && existing.skor == r.skor ? 'checked' : '';
                const selected = existing && existing.skor == r.skor ? 'selected' : '';
                html += `
                    <div class="rubrik-option ${selected}" onclick="selectRubrik(this, ${r.skor})">
                        <div class="d-flex align-items-start">
                            <input class="form-check-input mt-1 me-3" type="radio" name="skor" value="${r.skor}" id="skor${r.skor}" ${checked} required>
                            <label class="flex-grow-1" for="skor${r.skor}" style="cursor:pointer;">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="score-badge bg-${r.color} text-white me-3">${r.skor}</span>
                                    <strong class="fs-6">${r.label}</strong>
                                </div>
                                <p class="mb-0 text-muted small">${r.desc}</p>
                            </label>
                        </div>
                    </div>
                `;
            });
            
            document.getElementById('rubrik_container').innerHTML = html;
            document.getElementById('catatan').value = existing ? (existing.catatan || '') : '';
            
            if (existing && existing.bukti) {
                document.getElementById('existing_bukti').innerHTML = `
                    <div class="alert alert-success mt-2">
                        <i class="bi bi-file-earmark-check"></i> File: <strong>${existing.bukti}</strong>
                    </div>
                `;
            } else {
                document.getElementById('existing_bukti').innerHTML = '';
            }
            
            new bootstrap.Modal(document.getElementById('penilaianModal')).show();
        }
        
        function selectRubrik(el, skor) {
            document.querySelectorAll('.rubrik-option').forEach(e => e.classList.remove('selected'));
            el.classList.add('selected');
            document.getElementById('skor' + skor).checked = true;
        }
        
        // Form submit handler
        document.getElementById('formPenilaian').addEventListener('submit', function(e) {
            const selectedSkor = document.querySelector('input[name="skor"]:checked');
            if (!selectedSkor) {
                e.preventDefault();
                alert('Pilih skor penilaian terlebih dahulu!');
                return false;
            }
            console.log('Submitting skor:', selectedSkor.value);
        });
    </script>
</body>
</html>