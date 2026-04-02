<div class="sidebar">
    <div class="sidebar-header text-center">
        <?php
        // Get settings dari database
        $logo = '';
        $nama_app = 'E-SPMI';
        $nama_institusi = 'Universitas/Institusi';
        
        if (isset($koneksi)) {
            // Get logo
            $logo_query = mysqli_query($koneksi, "SELECT nilai FROM pengaturan WHERE nama_setting='logo_institusi'");
            if ($logo_query && mysqli_num_rows($logo_query) > 0) {
                $logo_row = mysqli_fetch_assoc($logo_query);
                $logo = $logo_row['nilai'] ?? '';
            }
            
            // Get nama aplikasi
            $app_query = mysqli_query($koneksi, "SELECT nilai FROM pengaturan WHERE nama_setting='nama_aplikasi'");
            if ($app_query && mysqli_num_rows($app_query) > 0) {
                $app_row = mysqli_fetch_assoc($app_query);
                $nama_app = $app_row['nilai'] ?? 'E-SPMI';
            }
            
            // Get nama institusi
            $institusi_query = mysqli_query($koneksi, "SELECT nilai FROM pengaturan WHERE nama_setting='nama_institusi'");
            if ($institusi_query && mysqli_num_rows($institusi_query) > 0) {
                $institusi_row = mysqli_fetch_assoc($institusi_query);
                $nama_institusi = $institusi_row['nilai'] ?? 'Universitas/Institusi';
            }
        }
        
        // Tampilkan logo jika ada
        if (!empty($logo) && file_exists('../assets/uploads/' . $logo)):
        ?>
        <div class="mb-3">
            <img src="../assets/uploads/<?php echo $logo; ?>" alt="Logo" class="img-fluid" style="max-height: 60px;">
        </div>
        <?php endif; ?>
        
        <h4 class="mb-1">
            <i></i> <?php echo $nama_app; ?>
        </h4>
        <p class="mb-0" style="font-size: 0.85rem; opacity: 0.9;"><?php echo $nama_institusi; ?></p>
        <p class="mb-0" style="font-size: 0.75rem; opacity: 0.8;">Administrator</p>
    </div>
    
    <ul class="sidebar-menu">
        <li class="menu-item active">
            <a href="index.php">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        
        <li class="menu-header">MASTER DATA</li>
        
        <li class="menu-item">
            <a href="users.php">
                <i class="bi bi-people"></i>
                <span>Pengguna</span>
            </a>
        </li>
        
        <li class="menu-item">
            <a href="unit.php">
                <i class="bi bi-building"></i>
                <span>Unit Audit</span>
            </a>
        </li>
        
        <li class="menu-item">
            <a href="standar.php">
                <i class="bi bi-clipboard-check"></i>
                <span>Standar SPMI</span>
            </a>
        </li>
        
        <li class="menu-item">
            <a href="indikator.php">
                <i class="bi bi-list-check"></i>
                <span>Indikator</span>
            </a>
        </li>
        
        <li class="menu-header">AUDIT</li>
        
        <li class="menu-item">
            <a href="jadwal.php">
                <i class="bi bi-calendar-event"></i>
                <span>Jadwal Audit</span>
            </a>
        </li>
        
        <li class="menu-item">
            <a href="laporan.php">
                <i class="bi bi-file-earmark-text"></i>
                <span>Laporan Audit</span>
            </a>
        </li>

        <li class="menu-item">
            <a href="riwayat.php">
                <i class="bi bi-clock-history"></i>
                <span>Riwayat Audit</span>
            </a>
        </li>

        <li class="menu-item">
            <a href="analytics.php">
                <i class="bi bi-graph-up"></i>
                <span>Analytics</span>
            </a>
        </li>
        
        <li class="menu-header">SISTEM</li>

        <li class="menu-item">
            <a href="log.php">
                <i class="bi bi-activity"></i>
                <span>Log Aktivitas</span>
            </a>
        </li>
        
        <li class="menu-item">
            <a href="pengaturan.php">
                <i class="bi bi-gear"></i>
                <span>Pengaturan</span>
            </a>
        </li>
    </ul>
</div>