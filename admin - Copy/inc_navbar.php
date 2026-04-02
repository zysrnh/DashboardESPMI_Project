<nav class="navbar d-flex align-items-center justify-content-between" id="mainNavbar">
    <!-- Left: Toggle + Search -->
    <div class="d-flex align-items-center gap-3">
        <button class="navbar-toggler-custom" id="sidebarToggle" type="button" title="Toggle Sidebar">
            <i class="bi bi-list fs-5"></i>
        </button>

        <div class="navbar-search d-none d-md-flex">
            <i class="bi bi-search navbar-search-icon"></i>
            <input type="text" class="navbar-search-input" placeholder="Cari menu, halaman..." id="navSearchInput">
        </div>
    </div>

    <!-- Right: Actions -->
    <div class="d-flex align-items-center gap-2">

        <!-- Quick Action -->
        <a href="jadwal.php" class="nav-icon-btn d-none d-sm-flex" title="Jadwal Audit">
            <i class="bi bi-calendar-plus"></i>
        </a>

        <!-- Notifications -->
        <div class="dropdown">
            <a class="nav-icon-btn" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell"></i>
                <span class="nav-badge">3</span>
            </a>
            <div class="dropdown-menu navbar-dropdown dropdown-menu-end p-0" style="min-width:300px;">
                <div class="navbar-dropdown-header d-flex align-items-center justify-content-between">
                    <span style="font-size:0.85rem;font-weight:700;color:#111827;">Notifikasi</span>
                    <a href="#" style="font-size:0.75rem;color:#4f46e5;text-decoration:none;font-weight:600;">Tandai semua dibaca</a>
                </div>
                <div class="px-1 py-1">
                    <div class="notif-item">
                        <div class="notif-dot"></div>
                        <div>
                            <div class="notif-text">Jadwal audit baru untuk <strong>Fakultas Teknik</strong></div>
                            <div class="notif-time">2 menit lalu</div>
                        </div>
                    </div>
                    <div class="notif-item">
                        <div class="notif-dot" style="background:#d97706;"></div>
                        <div>
                            <div class="notif-text">Laporan <strong>AUD-2025-001</strong> menunggu review</div>
                            <div class="notif-time">1 jam lalu</div>
                        </div>
                    </div>
                    <div class="notif-item">
                        <div class="notif-dot" style="background:#059669;"></div>
                        <div>
                            <div class="notif-text">Audit <strong>Prodi Informatika</strong> selesai</div>
                            <div class="notif-time">3 jam lalu</div>
                        </div>
                    </div>
                </div>
                <div style="padding:10px 12px;border-top:1px solid #f3f4f6;">
                    <a href="log.php" style="font-size:0.8rem;color:#4f46e5;text-decoration:none;font-weight:600;display:block;text-align:center;">
                        Lihat semua <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- User Profile Dropdown -->
        <?php
        $nav_name  = $_SESSION['nama_lengkap'] ?? 'Admin';
        $nav_role  = ucfirst($_SESSION['role'] ?? 'admin');
        $nav_words = explode(' ', trim($nav_name));
        $nav_init  = strtoupper(substr($nav_words[0], 0, 1));
        if (count($nav_words) > 1) $nav_init .= strtoupper(substr(end($nav_words), 0, 1));
        $rb = ['admin'=>'background:#fef2f2;color:#b91c1c;','auditor'=>'background:#eff6ff;color:#1d4ed8;','auditee'=>'background:#f0fdf4;color:#15803d;'];
        $rb_style = $rb[$_SESSION['role'] ?? 'admin'] ?? 'background:#f1f5f9;color:#475569;';
        ?>
        <div class="dropdown">
            <a class="navbar-user-btn" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <!-- CSS-only avatar: no external request -->
                <div class="navbar-user-avatar-init"><?php echo $nav_init; ?></div>
                <div class="d-none d-md-block">
                    <div class="navbar-user-name"><?php echo htmlspecialchars($nav_name); ?></div>
                    <div class="navbar-user-role"><?php echo $nav_role; ?></div>
                </div>
                <i class="bi bi-chevron-down navbar-caret d-none d-md-block"></i>
            </a>
            <div class="dropdown-menu navbar-dropdown dropdown-menu-end p-0" style="min-width:240px;">
                <div class="navbar-dropdown-header">
                    <div class="navbar-dropdown-user-card">
                        <div class="navbar-avatar-lg"><?php echo $nav_init; ?></div>
                        <div style="min-width:0;">
                            <p class="navbar-dropdown-username"><?php echo htmlspecialchars($nav_name); ?></p>
                            <p class="navbar-dropdown-email text-truncate"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></p>
                        </div>
                        <span class="navbar-dropdown-role-badge" style="<?php echo $rb_style; ?>"><?php echo $nav_role; ?></span>
                    </div>
                </div>

                <div class="px-1 py-1">
                    <a href="profil.php" class="dropdown-item-custom">
                        <span class="item-icon"><i class="bi bi-person"></i></span>
                        Profil Saya
                    </a>
                    <a href="pengaturan.php" class="dropdown-item-custom">
                        <span class="item-icon"><i class="bi bi-sliders2"></i></span>
                        Pengaturan
                    </a>
                    <a href="log.php" class="dropdown-item-custom">
                        <span class="item-icon"><i class="bi bi-clock-history"></i></span>
                        Log Aktivitas
                    </a>
                </div>

                <div class="dropdown-divider-custom mx-2"></div>

                <div class="px-1 pb-1">
                    <a href="../logout.php" class="dropdown-item-custom danger">
                        <span class="item-icon"><i class="bi bi-box-arrow-right"></i></span>
                        Keluar
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>