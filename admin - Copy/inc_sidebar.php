<?php
$current_page = basename($_SERVER['PHP_SELF']);

$logo          = '';
$nama_app      = 'E-SPMI';
$nama_institusi = 'Universitas/Institusi';
$sidebar_color = '#0f172a';
$accent_color  = '#4f46e5';
$sidebar_users = [];

if (isset($koneksi)) {
    // 1 query untuk semua settings sekaligus
    $sq = mysqli_query($koneksi,
        "SELECT nama_setting, nilai FROM pengaturan
         WHERE nama_setting IN ('logo_institusi','nama_aplikasi','nama_institusi','warna_sidebar','warna_aksen')"
    );
    if ($sq) {
        while ($row = mysqli_fetch_assoc($sq)) {
            switch ($row['nama_setting']) {
                case 'logo_institusi': $logo           = $row['nilai']; break;
                case 'nama_aplikasi':  $nama_app       = $row['nilai']; break;
                case 'nama_institusi': $nama_institusi = $row['nilai']; break;
                case 'warna_sidebar':  $sidebar_color  = $row['nilai']; break;
                case 'warna_aksen':    $accent_color   = $row['nilai']; break;
            }
        }
    }

    // Users untuk dropdown (tetap terpisah karena tabel berbeda)
    $uq = mysqli_query($koneksi,
        "SELECT id, username, nama_lengkap, role FROM users
         WHERE status='aktif' ORDER BY role, nama_lengkap LIMIT 20"
    );
    if ($uq) {
        while ($u = mysqli_fetch_assoc($uq)) $sidebar_users[] = $u;
    }
}
?>
<style>
    :root {
        --sidebar-bg: <?php echo htmlspecialchars($sidebar_color); ?>;
        --primary: <?php echo htmlspecialchars($accent_color); ?>;
    }
</style>

<div class="sidebar" id="mainSidebar">
    <!-- Header / Brand -->
    <div class="sidebar-header">
        <a class="sidebar-brand" href="index.php">
            <div class="sidebar-brand-icon">
                <?php if (!empty($logo) && file_exists('../assets/uploads/' . $logo)): ?>
                    <img src="../assets/uploads/<?php echo htmlspecialchars($logo); ?>" alt="Logo" style="width:24px;height:24px;object-fit:contain;">
                <?php else: ?>
                    <i class="bi bi-shield-check"></i>
                <?php endif; ?>
            </div>
            <div class="sidebar-brand-text">
                <h4><?php echo htmlspecialchars($nama_app); ?></h4>
                <p><?php echo htmlspecialchars($nama_institusi); ?></p>
            </div>
        </a>
    </div>

    <!-- Nav -->
    <nav class="sidebar-nav">
        <ul class="sidebar-menu">

            <!-- Dashboard -->
            <li class="menu-item <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                <a href="index.php">
                    <span class="menu-icon"><i class="bi bi-grid-1x2-fill"></i></span>
                    <span class="menu-label">Dashboard</span>
                </a>
            </li>

            <!-- MASTER DATA GROUP -->
            <li class="menu-section-title">Master Data</li>

            <!-- Pengguna (dengan Dropdown) -->
            <li class="menu-item has-dropdown <?php echo $current_page == 'users.php' ? 'active open' : ''; ?>" id="menuPengguna">
                <a href="javascript:void(0)" onclick="toggleDropdown('dropdownPengguna', 'menuPengguna')">
                    <span class="menu-icon"><i class="bi bi-people-fill"></i></span>
                    <span class="menu-label">Pengguna</span>
                    <i class="bi bi-chevron-right menu-arrow"></i>
                </a>
                <ul class="dropdown-nav <?php echo $current_page == 'users.php' ? 'open' : ''; ?>" id="dropdownPengguna">

                    <!-- Kelola Pengguna link utama -->
                    <li class="dropdown-nav-item <?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
                        <a href="users.php">
                            <span>Kelola Semua Pengguna</span>
                            <span class="item-role-badge" style="background:#e0e7ff;color:#4338ca;">Manajemen</span>
                        </a>
                    </li>

                    <!-- Divider label -->
                    <li style="padding: 6px 12px 2px 20px;">
                        <small style="font-size:0.65rem;text-transform:uppercase;letter-spacing:0.08em;color:#475569;font-weight:700;">Daftar Pengguna Aktif</small>
                    </li>

                    <!-- List users dari DB -->
                    <?php if (!empty($sidebar_users)): ?>
                        <?php foreach ($sidebar_users as $su):
                            $role_colors = [
                                'admin'   => ['bg' => '#fef2f2', 'text' => '#dc2626', 'badge_bg' => '#fee2e2'],
                                'auditor' => ['bg' => '#eff6ff', 'text' => '#2563eb', 'badge_bg' => '#dbeafe'],
                                'auditee' => ['bg' => '#ecfdf5', 'text' => '#059669', 'badge_bg' => '#d1fae5'],
                            ];
                            $rc = $role_colors[$su['role']] ?? ['bg' => '#f8fafc', 'text' => '#475569', 'badge_bg' => '#f1f5f9'];
                            $initials = strtoupper(substr($su['nama_lengkap'], 0, 1));
                            $avatar_bg_map = ['admin' => '#7c3aed', 'auditor' => '#2563eb', 'auditee' => '#059669'];
                            $avatar_bg = $avatar_bg_map[$su['role']] ?? '#64748b';
                        ?>
                        <li class="dropdown-nav-item" style="padding: 2px 8px 2px 12px;">
                            <a href="users.php?filter_role=<?php echo $su['role']; ?>" class="dropdown-user-card" style="--card-accent:<?php echo $rc['text']; ?>;">
                                <div class="user-avatar-placeholder" style="background:<?php echo $avatar_bg; ?>; font-size:0.7rem;">
                                    <?php echo $initials; ?>
                                </div>
                                <div class="user-info-sm">
                                    <span class="user-name"><?php echo htmlspecialchars($su['username']); ?></span>
                                    <span class="user-role"><?php echo htmlspecialchars($su['nama_lengkap']); ?></span>
                                </div>
                                <span class="item-role-badge ms-auto" style="background:<?php echo $rc['badge_bg']; ?>;color:<?php echo $rc['text']; ?>;">
                                    <?php echo ucfirst($su['role']); ?>
                                </span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li style="padding: 8px 12px 8px 20px;">
                            <small style="color:#64748b;font-size:0.78rem;">Belum ada pengguna aktif</small>
                        </li>
                    <?php endif; ?>

                </ul>
            </li>

            <!-- Unit Audit -->
            <li class="menu-item <?php echo $current_page == 'unit.php' ? 'active' : ''; ?>">
                <a href="unit.php">
                    <span class="menu-icon"><i class="bi bi-building-fill"></i></span>
                    <span class="menu-label">Unit Audit</span>
                </a>
            </li>

            <!-- Standar SPMI -->
            <li class="menu-item <?php echo $current_page == 'standar.php' ? 'active' : ''; ?>">
                <a href="standar.php">
                    <span class="menu-icon"><i class="bi bi-clipboard2-check-fill"></i></span>
                    <span class="menu-label">Standar SPMI</span>
                </a>
            </li>

            <!-- Indikator -->
            <li class="menu-item <?php echo $current_page == 'indikator.php' ? 'active' : ''; ?>">
                <a href="indikator.php">
                    <span class="menu-icon"><i class="bi bi-list-check"></i></span>
                    <span class="menu-label">Indikator</span>
                </a>
            </li>

            <!-- AUDIT SYSTEM GROUP -->
            <li class="menu-section-title">Audit System</li>

            <!-- Jadwal Audit -->
            <li class="menu-item <?php echo $current_page == 'jadwal.php' ? 'active' : ''; ?>">
                <a href="jadwal.php">
                    <span class="menu-icon"><i class="bi bi-calendar-week-fill"></i></span>
                    <span class="menu-label">Jadwal Audit</span>
                </a>
            </li>

            <!-- Laporan Audit -->
            <li class="menu-item <?php echo $current_page == 'laporan.php' ? 'active' : ''; ?>">
                <a href="laporan.php">
                    <span class="menu-icon"><i class="bi bi-file-earmark-text-fill"></i></span>
                    <span class="menu-label">Laporan Audit</span>
                </a>
            </li>

            <!-- Riwayat Audit -->
            <li class="menu-item <?php echo $current_page == 'riwayat.php' ? 'active' : ''; ?>">
                <a href="riwayat.php">
                    <span class="menu-icon"><i class="bi bi-clock-history"></i></span>
                    <span class="menu-label">Riwayat Audit</span>
                </a>
            </li>

            <!-- Analytics -->
            <li class="menu-item <?php echo $current_page == 'analytics.php' ? 'active' : ''; ?>">
                <a href="analytics.php">
                    <span class="menu-icon"><i class="bi bi-bar-chart-line-fill"></i></span>
                    <span class="menu-label">Analytics</span>
                </a>
            </li>

            <!-- KONFIGURASI GROUP -->
            <li class="menu-section-title">Konfigurasi</li>

            <!-- Log Aktivitas -->
            <li class="menu-item <?php echo $current_page == 'log.php' ? 'active' : ''; ?>">
                <a href="log.php">
                    <span class="menu-icon"><i class="bi bi-terminal-fill"></i></span>
                    <span class="menu-label">Log Aktivitas</span>
                </a>
            </li>

            <!-- Pengaturan -->
            <li class="menu-item <?php echo $current_page == 'pengaturan.php' ? 'active' : ''; ?>">
                <a href="pengaturan.php">
                    <span class="menu-icon"><i class="bi bi-sliders2"></i></span>
                    <span class="menu-label">Pengaturan</span>
                </a>
            </li>

        </ul>
    </nav>

    <!-- Sidebar Footer (User mini) -->
    <div class="sidebar-footer">
        <a href="profil.php" class="sidebar-user-mini">
            <?php
            // Generate CSS-only avatar (tanpa request eksternal)
            $fn = $_SESSION['nama_lengkap'] ?? 'Admin';
            $words = explode(' ', trim($fn));
            $initials_footer = strtoupper(substr($words[0], 0, 1));
            if (count($words) > 1) $initials_footer .= strtoupper(substr(end($words), 0, 1));
            ?>
            <div style="width:32px;height:32px;border-radius:5px;background:<?php echo htmlspecialchars($accent_color); ?>;display:flex;align-items:center;justify-content:center;font-size:0.72rem;font-weight:700;color:white;flex-shrink:0;">
                <?php echo $initials_footer; ?>
            </div>
            <div>
                <div class="user-name-mini"><?php echo htmlspecialchars($fn); ?></div>
                <div class="user-status"><?php echo ucfirst($_SESSION['role'] ?? 'admin'); ?></div>
            </div>
            <i class="bi bi-box-arrow-right logout-icon"></i>
        </a>
    </div>
</div>
<div class="sidebar-overlay" id="sidebarOverlay"></div>