<?php
require_once '../inc/inc_koneksi.php';
require_once '../inc/inc_fungsi.php';

cek_role(['admin']);

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Cek apakah ada indikator yang terkait
    $check = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM indikator WHERE standar_id=$id");
    $count = mysqli_fetch_assoc($check)['total'];
    
    if ($count > 0) {
        $_SESSION['error'] = 'Tidak dapat menghapus standar karena masih memiliki ' . $count . ' indikator terkait!';
    } else {
        $query = "UPDATE standar SET status='nonaktif' WHERE id=$id";
        if (mysqli_query($koneksi, $query)) {
            log_aktivitas($_SESSION['user_id'], 'Menonaktifkan standar ID: ' . $id, 'standar', $id);
            $_SESSION['success'] = 'Standar berhasil dinonaktifkan';
        }
    }
    header("Location: standar.php");
    exit();
}

// Handle Form Submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $kode = esc($_POST['kode']);
    $kategori = esc($_POST['kategori']);
    $nama_standar = esc($_POST['nama_standar']);
    $deskripsi = esc($_POST['deskripsi']);
    $bobot = (int)$_POST['bobot'];
    $urutan = (int)$_POST['urutan'];
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : 'NULL';
    $status = esc($_POST['status']);
    
    if ($id > 0) {
        // Update
        $sql = "UPDATE standar SET 
                kode='$kode', 
                kategori='$kategori', 
                nama_standar='$nama_standar', 
                deskripsi='$deskripsi', 
                bobot=$bobot, 
                urutan=$urutan, 
                parent_id=$parent_id, 
                status='$status'
                WHERE id=$id";
        
        if (mysqli_query($koneksi, $sql)) {
            log_aktivitas($_SESSION['user_id'], 'Mengupdate standar: ' . $nama_standar, 'standar', $id);
            $_SESSION['success'] = 'Standar berhasil diupdate';
        }
    } else {
        // Insert
        $sql = "INSERT INTO standar (kode, kategori, nama_standar, deskripsi, bobot, urutan, parent_id, status) 
                VALUES ('$kode', '$kategori', '$nama_standar', '$deskripsi', $bobot, $urutan, $parent_id, '$status')";
        
        if (mysqli_query($koneksi, $sql)) {
            log_aktivitas($_SESSION['user_id'], 'Menambah standar baru: ' . $nama_standar, 'standar', mysqli_insert_id($koneksi));
            $_SESSION['success'] = 'Standar berhasil ditambahkan';
        }
    }
    
    header("Location: standar.php");
    exit();
}

// Ambil data standar
$query = "SELECT s.*, 
          (SELECT COUNT(*) FROM indikator WHERE standar_id=s.id AND status='aktif') as jumlah_indikator,
          p.nama_standar as parent_name
          FROM standar s 
          LEFT JOIN standar p ON s.parent_id = p.id
          ORDER BY s.urutan ASC, s.kategori ASC";
$standar = mysqli_query($koneksi, $query);

// Ambil data untuk dropdown parent
$query_parent = "SELECT * FROM standar WHERE status='aktif' AND parent_id IS NULL ORDER BY urutan ASC";
$parent_options = mysqli_query($koneksi, $query_parent);

// Statistik
$stats = [
    'total' => mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM standar WHERE status='aktif'"))['total'],
    'kategori' => mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(DISTINCT kategori) as total FROM standar WHERE status='aktif'"))['total'],
    'indikator' => mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM indikator WHERE status='aktif'"))['total']
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Standar SPMI - E-SPMI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>
        .standar-card {
            transition: all 0.3s;
            border-left: 4px solid #667eea;
        }
        .standar-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .kategori-badge {
            font-size: 0.75rem;
            padding: 5px 10px;
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
                        <h2 class="mb-0">Kelola Standar SPMI</h2>
                        <p class="text-muted">Manajemen standar penjaminan mutu internal</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#standarModal" onclick="resetForm()">
                        <i class="bi bi-plus-circle"></i> Tambah Standar
                    </button>
                </div>

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

                <!-- Statistik -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-2">Total Standar</h6>
                                        <h3 class="mb-0"><?php echo $stats['total']; ?></h3>
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
                                        <h6 class="text-muted mb-2">Kategori</h6>
                                        <h3 class="mb-0"><?php echo $stats['kategori']; ?></h3>
                                    </div>
                                    <div class="stat-icon bg-success">
                                        <i class="bi bi-folder"></i>
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
                                        <h6 class="text-muted mb-2">Total Indikator</h6>
                                        <h3 class="mb-0"><?php echo $stats['indikator']; ?></h3>
                                    </div>
                                    <div class="stat-icon bg-warning">
                                        <i class="bi bi-list-check"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Filter Kategori</label>
                                <select class="form-select" id="filterKategori">
                                    <option value="">Semua Kategori</option>
                                    <option value="Standar Nasional Pendidikan">Standar Nasional Pendidikan</option>
                                    <option value="Standar Penelitian">Standar Penelitian</option>
                                    <option value="Standar Pengabdian Kepada Masyarakat">Standar Pengabdian</option>
                                    <option value="Sistem Penjaminan Mutu Internal">SPMI</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Filter Status</label>
                                <select class="form-select" id="filterStatus">
                                    <option value="">Semua Status</option>
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-secondary w-100" onclick="resetFilter()">
                                    <i class="bi bi-arrow-clockwise"></i> Reset Filter
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Standar -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="standarTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="8%">Kode</th>
                                        <th width="15%">Kategori</th>
                                        <th width="25%">Nama Standar</th>
                                        <th width="12%">Parent</th>
                                        <th width="8%">Bobot</th>
                                        <th width="8%">Indikator</th>
                                        <th width="7%">Urutan</th>
                                        <th width="7%">Status</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    mysqli_data_seek($standar, 0);
                                    while ($row = mysqli_fetch_assoc($standar)): 
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><span class="badge bg-dark"><?php echo $row['kode']; ?></span></td>
                                        <td><span class="kategori-badge badge bg-info"><?php echo $row['kategori']; ?></span></td>
                                        <td>
                                            <strong><?php echo $row['nama_standar']; ?></strong>
                                            <?php if ($row['deskripsi']): ?>
                                            <br><small class="text-muted"><?php echo substr($row['deskripsi'], 0, 100); ?>...</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($row['parent_name']): ?>
                                            <span class="badge bg-secondary"><?php echo $row['parent_name']; ?></span>
                                            <?php else: ?>
                                            <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge bg-primary"><?php echo $row['bobot']; ?></span></td>
                                        <td>
                                            <a href="indikator.php?standar_id=<?php echo $row['id']; ?>" class="badge bg-success text-decoration-none">
                                                <?php echo $row['jumlah_indikator']; ?> item
                                            </a>
                                        </td>
                                        <td><?php echo $row['urutan']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $row['status'] == 'aktif' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" onclick='editStandar(<?php echo json_encode($row); ?>)' title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <a href="indikator.php?standar_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info" title="Kelola Indikator">
                                                <i class="bi bi-list-check"></i>
                                            </a>
                                            <a href="?delete=<?php echo $row['id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Yakin ingin menonaktifkan standar ini?')"
                                               title="Nonaktifkan">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <?php include 'footer.php'; ?>
    </div>

    <!-- Modal Form Standar -->
    <div class="modal fade" id="standarModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">Tambah Standar</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="standarId">
                        
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Kode <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="kode" id="kode" required placeholder="Contoh: A.1">
                                <small class="text-muted">Kode unik standar</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori <span class="text-danger">*</span></label>
                                <select class="form-select" name="kategori" id="kategori" required>
                                    <option value="">Pilih Kategori</option>
                                    <option value="Standar Nasional Pendidikan">Standar Nasional Pendidikan</option>
                                    <option value="Standar Penelitian">Standar Penelitian</option>
                                    <option value="Standar Pengabdian Kepada Masyarakat">Standar Pengabdian Kepada Masyarakat</option>
                                    <option value="Sistem Penjaminan Mutu Internal">Sistem Penjaminan Mutu Internal</option>
                                    <option value="Standar Pendidikan Tinggi PT">Standar Pendidikan Tinggi PT</option>
                                    <option value="Pangkalan Data Pendidikan Tinggi">Pangkalan Data Pendidikan Tinggi</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" name="status" id="status" required>
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Standar <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama_standar" id="nama_standar" required 
                                   placeholder="Contoh: Standar Kompetensi Lulusan">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" id="deskripsi" rows="3" 
                                      placeholder="Deskripsi singkat tentang standar ini..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Parent Standar</label>
                                <select class="form-select" name="parent_id" id="parent_id">
                                    <option value="">Tidak Ada (Standar Utama)</option>
                                    <?php 
                                    mysqli_data_seek($parent_options, 0);
                                    while ($parent = mysqli_fetch_assoc($parent_options)): 
                                    ?>
                                    <option value="<?php echo $parent['id']; ?>">
                                        <?php echo $parent['kode'] . ' - ' . $parent['nama_standar']; ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                                <small class="text-muted">Pilih jika ini adalah sub-standar</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Bobot Skor Maksimal <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="bobot" id="bobot" value="4" required min="1" max="100">
                                <small class="text-muted">Default: 4 (skala 0-4)</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Urutan <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="urutan" id="urutan" required min="1" value="1">
                                <small class="text-muted">Urutan tampilan standar</small>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> <strong>Catatan:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Kode standar harus unik dan mudah diingat</li>
                                <li>Gunakan parent standar untuk membuat hierarki (misalnya: A → A.1 → A.1.1)</li>
                                <li>Bobot menentukan skor maksimal yang bisa dicapai</li>
                                <li>Urutan menentukan posisi standar saat ditampilkan</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        let table;
        
        $(document).ready(function() {
            table = $('#standarTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                },
                order: [[7, 'asc']], // Sort by urutan
                pageLength: 25
            });

            // Filter Kategori
            $('#filterKategori').on('change', function() {
                table.column(2).search(this.value).draw();
            });

            // Filter Status
            $('#filterStatus').on('change', function() {
                table.column(8).search(this.value).draw();
            });
        });

        function resetFilter() {
            $('#filterKategori').val('');
            $('#filterStatus').val('');
            table.search('').columns().search('').draw();
        }

        function resetForm() {
            document.getElementById('modalTitle').innerHTML = '<i class="bi bi-plus-circle"></i> Tambah Standar';
            document.getElementById('standarId').value = '';
            document.getElementById('kode').value = '';
            document.getElementById('kategori').value = '';
            document.getElementById('nama_standar').value = '';
            document.getElementById('deskripsi').value = '';
            document.getElementById('parent_id').value = '';
            document.getElementById('bobot').value = '4';
            document.getElementById('urutan').value = '1';
            document.getElementById('status').value = 'aktif';
        }

        function editStandar(standar) {
            document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil"></i> Edit Standar';
            document.getElementById('standarId').value = standar.id;
            document.getElementById('kode').value = standar.kode;
            document.getElementById('kategori').value = standar.kategori;
            document.getElementById('nama_standar').value = standar.nama_standar;
            document.getElementById('deskripsi').value = standar.deskripsi || '';
            document.getElementById('parent_id').value = standar.parent_id || '';
            document.getElementById('bobot').value = standar.bobot;
            document.getElementById('urutan').value = standar.urutan;
            document.getElementById('status').value = standar.status;
            
            var modal = new bootstrap.Modal(document.getElementById('standarModal'));
            modal.show();
        }
    </script>
    <script src="../assets/js/sidebar-toggle.js"></script>
</body>
</html>