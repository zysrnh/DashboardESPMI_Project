<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="sidebar-header">
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

            // Get custom colors
            $color_query = mysqli_query($koneksi, "SELECT nama_setting, nilai FROM pengaturan WHERE nama_setting IN ('warna_sidebar', 'warna_aksen')");
            $custom_colors = [];
            while ($c_row = mysqli_fetch_assoc($color_query)) {
                $custom_colors[$c_row['nama_setting']] = $c_row['nilai'];
            }
            $sidebar_color = $custom_colors['warna_sidebar'] ?? '#1e293b';
            $accent_color = $custom_colors['warna_aksen'] ?? '#3b82f6';
        }
        ?>
        <style>
            :root {
                --sidebar-bg: <?php echo $sidebar_color; ?> !important;
                --primary: <?php echo $accent_color; ?> !important;
                --primary-hover: <?php echo adjustBrightness($accent_color, -20); ?> !important;
            }
            .sidebar-header { background: rgba(0,0,0,0.15) !important; }
        </style>
        <div class="d-flex align-items-center">
            <?php if (!empty($logo) && file_exists('../assets/uploads/' . $logo)): ?>
                <img src="../assets/uploads/<?php echo $logo; ?>" alt="Logo" class="me-2" style="height: 32px; width: 32px; object-fit: contain;">
            <?php else: ?>
                <div class="bg-primary rounded-pill me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <i class="bi bi-shield-check text-white"></i>
                </div>
            <?php endif; ?>
            <div>
                <h4 class="mb-0 text-white"><?php echo $nama_app; ?></h4>
                <p class="mb-0 text-truncate" style="max-width: 150px; opacity: 0.7; font-size: 0.75rem;"><?php echo $nama_institusi; ?></p>
            </div>
        </div>
    </div>
    
    <ul class="sidebar-menu">
        <li class="menu-item <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
            <a href="index.php">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li>
        
        <li class="menu-header">Master Data</li>
        
        <li class="menu-item <?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
            <a href="users.php">
                <i class="bi bi-people"></i>
                <span>Pengguna</span>
            </a>
        </li>
        
        <li class="menu-item <?php echo $current_page == 'unit.php' ? 'active' : ''; ?>">
            <a href="unit.php">
                <i class="bi bi-building"></i>
                <span>Unit Audit</span>
            </a>
        </li>
        
        <li class="menu-item <?php echo $current_page == 'standar.php' ? 'active' : ''; ?>">
            <a href="standar.php">
                <i class="bi bi-clipboard-check"></i>
                <span>Standar SPMI</span>
            </a>
        </li>
        
        <li class="menu-item <?php echo $current_page == 'indikator.php' ? 'active' : ''; ?>">
            <a href="indikator.php">
                <i class="bi bi-list-check"></i>
                <span>Indikator</span>
            </a>
        </li>
        
        <li class="menu-header">Audit System</li>
        
        <li class="menu-item <?php echo $current_page == 'jadwal.php' ? 'active' : ''; ?>">
            <a href="jadwal.php">
                <i class="bi bi-calendar-event"></i>
                <span>Jadwal Audit</span>
            </a>
        </li>
        
        <li class="menu-item <?php echo $current_page == 'laporan.php' ? 'active' : ''; ?>">
            <a href="laporan.php">
                <i class="bi bi-file-earmark-text"></i>
                <span>Laporan Audit</span>
            </a>
        </li>

        <li class="menu-item <?php echo $current_page == 'riwayat.php' ? 'active' : ''; ?>">
            <a href="riwayat.php">
                <i class="bi bi-clock-history"></i>
                <span>Riwayat Audit</span>
            </a>
        </li>

        <li class="menu-item <?php echo $current_page == 'analytics.php' ? 'active' : ''; ?>">
            <a href="analytics.php">
                <i class="bi bi-bar-chart"></i>
                <span>Analytics</span>
            </a>
        </li>
        
        <li class="menu-header">Konfigurasi</li>

        <li class="menu-item <?php echo $current_page == 'log.php' ? 'active' : ''; ?>">
            <a href="log.php">
                <i class="bi bi-terminal"></i>
                <span>Log Aktivitas</span>
            </a>
        </li>
        
        <li class="menu-item <?php echo $current_page == 'pengaturan.php' ? 'active' : ''; ?>">
            <a href="pengaturan.php">
                <i class="bi bi-sliders"></i>
                <span>Pengaturan</span>
            </a>
        </li>
    </ul>
</div>
<div class="sidebar-overlay" id="sidebarOverlay"></div>