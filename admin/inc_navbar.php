<nav class="navbar navbar-expand navbar-light bg-white border-bottom">
    <div class="container-fluid">
        <button class="btn btn-link" id="sidebarToggle" type="button">
            <i class="bi bi-list fs-4"></i>
        </button>
        
        <div class="ms-auto d-flex align-items-center">
            <!-- User Profile -->
            <div class="dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" 
                   role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nama_lengkap']); ?>&background=667eea&color=fff" 
                         class="rounded-circle me-2" width="35" height="35" alt="Profile">
                    <span class="d-none d-md-inline"><?php echo $_SESSION['nama_lengkap']; ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header"><?php echo $_SESSION['email']; ?></h6></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="profil.php">
                        <i class="bi bi-person"></i> Profil
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="../logout.php">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>