<?php
require_once 'inc/inc_koneksi.php';
require_once 'inc/inc_fungsi.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = esc($_POST['username']);
    $password = $_POST['password'];

    $query  = "SELECT * FROM users WHERE username = '$username' AND status = 'aktif'";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id']     = $user['id'];
            $_SESSION['username']    = $user['username'];
            $_SESSION['nama_lengkap']= $user['nama_lengkap'];
            $_SESSION['email']       = $user['email'];
            $_SESSION['role']        = $user['role'];
            $_SESSION['unit_kerja']  = $user['unit_kerja'];
            log_aktivitas($user['id'], "Login ke sistem", 'users', $user['id']);
            switch ($user['role']) {
                case 'admin':   header("Location: admin/index.php");   break;
                case 'auditor': header("Location: auditor/index.php"); break;
                case 'auditee': header("Location: auditee/index.php"); break;
                default:        header("Location: index.php");
            }
            exit();
        } else {
            $error = 'Password salah!';
        }
    } else {
        $error = 'Username tidak ditemukan atau akun tidak aktif!';
    }
}

// Get brand settings
$nama_app      = 'E-SPMI';
$nama_institusi = 'Sistem Penjaminan Mutu Internal';
$accent_color  = '#4f46e5';

if (isset($koneksi)) {
    $sq = mysqli_query($koneksi, "SELECT nama_setting, nilai FROM pengaturan WHERE nama_setting IN ('nama_aplikasi','nama_institusi','warna_aksen')");
    while ($row = mysqli_fetch_assoc($sq)) {
        if ($row['nama_setting'] === 'nama_aplikasi')  $nama_app       = $row['nilai'];
        if ($row['nama_setting'] === 'nama_institusi') $nama_institusi = $row['nilai'];
        if ($row['nama_setting'] === 'warna_aksen')    $accent_color   = $row['nilai'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login <?php echo htmlspecialchars($nama_app); ?> - <?php echo htmlspecialchars($nama_institusi); ?>">
    <title>Login - <?php echo htmlspecialchars($nama_app); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --accent: <?php echo htmlspecialchars($accent_color); ?>; }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: #f4f6f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            -webkit-font-smoothing: antialiased;
        }

        /* Outer wrapper — split panel on large screen */
        .login-wrap {
            width: 100%;
            max-width: 420px;
        }

        /* Brand header */
        .login-brand {
            text-align: center;
            margin-bottom: 2rem;
        }

        .brand-icon {
            width: 52px;
            height: 52px;
            border-radius: 10px;
            background: var(--accent);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
        }

        .brand-icon svg {
            width: 26px;
            height: 26px;
            fill: white;
        }

        .brand-name {
            font-size: 1.5rem;
            font-weight: 800;
            color: #111827;
            letter-spacing: -0.5px;
            margin: 0 0 4px;
        }

        .brand-sub {
            font-size: 0.8rem;
            color: #6b7280;
            margin: 0;
        }

        /* Card */
        .login-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .login-card-title {
            font-size: 1rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }

        .login-card-sub {
            font-size: 0.8rem;
            color: #6b7280;
            margin-bottom: 1.5rem;
        }

        /* Form elements */
        .form-label {
            font-size: 0.73rem;
            font-weight: 700;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 5px;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 0.9rem;
            pointer-events: none;
            z-index: 2;
        }

        .input-wrap input {
            width: 100%;
            padding: 9px 12px 9px 34px;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            font-size: 0.875rem;
            font-family: inherit;
            color: #111827;
            background: white;
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .input-wrap input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(79,70,229,0.10);
        }

        .input-wrap input::placeholder { color: #9ca3af; }

        /* Password toggle */
        .input-wrap .pw-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 2px;
            font-size: 0.9rem;
            transition: color 0.15s;
            z-index: 2;
        }

        .input-wrap .pw-toggle:hover { color: #4b5563; }

        /* Error */
        .login-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-left: 3px solid #dc2626;
            border-radius: 5px;
            padding: 10px 12px;
            font-size: 0.82rem;
            color: #991b1b;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Submit button */
        .btn-login {
            width: 100%;
            padding: 10px;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 5px;
            font-family: inherit;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s ease, transform 0.1s ease, box-shadow 0.15s ease;
            margin-top: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
        }

        .btn-login:hover {
            filter: brightness(0.92);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79,70,229,0.25);
        }

        .btn-login:active { transform: none; filter: brightness(0.85); }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 1.25rem 0 1rem;
            color: #d1d5db;
            font-size: 0.72rem;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }

        .divider span { color: #9ca3af; white-space: nowrap; font-weight: 500; }

        /* Demo accounts */
        .demo-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 6px;
        }

        .demo-item {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 5px;
            padding: 7px 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .demo-item:hover {
            border-color: var(--accent);
            background: #fafbff;
        }

        .demo-role {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 3px;
        }

        .demo-creds {
            font-size: 0.68rem;
            color: #6b7280;
            line-height: 1.4;
        }

        /* Demo role colors */
        .demo-item.admin  .demo-role { color: #b91c1c; }
        .demo-item.auditor .demo-role { color: #1d4ed8; }
        .demo-item.auditee .demo-role { color: #15803d; }

        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.73rem;
            color: #9ca3af;
        }

        @media (max-width: 480px) {
            .login-card { padding: 1.5rem; }
            .demo-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="login-wrap">

    <!-- Brand -->
    <div class="login-brand">
        <div class="brand-icon">
            <!-- Shield SVG icon -->
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2L4 6v6c0 5.25 3.4 10.15 8 11.25C16.6 22.15 20 17.25 20 12V6l-8-4zm-1 13.07l-2.83-2.83 1.41-1.41L11 12.24l4.42-4.42 1.41 1.41L11 15.07z"/>
            </svg>
        </div>
        <h1 class="brand-name"><?php echo htmlspecialchars($nama_app); ?></h1>
        <p class="brand-sub"><?php echo htmlspecialchars($nama_institusi); ?></p>
    </div>

    <!-- Login Card -->
    <div class="login-card">
        <p class="login-card-title">Masuk ke Akun</p>
        <p class="login-card-sub">Gunakan kredensial Anda untuk mengakses sistem</p>

        <?php if ($error): ?>
        <div class="login-error">
            <i class="bi bi-exclamation-circle-fill"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="" novalidate>
            <div class="mb-3">
                <label class="form-label" for="username">Username</label>
                <div class="input-wrap">
                    <i class="bi bi-person input-icon"></i>
                    <input type="text" id="username" name="username"
                           placeholder="Masukkan username" required autofocus
                           autocomplete="username">
                </div>
            </div>

            <div class="mb-1">
                <label class="form-label" for="password">Password</label>
                <div class="input-wrap">
                    <i class="bi bi-lock input-icon"></i>
                    <input type="password" id="password" name="password"
                           placeholder="Masukkan password" required
                           autocomplete="current-password"
                           style="padding-right: 36px;">
                    <button type="button" class="pw-toggle" id="togglePw" tabindex="-1">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M11 7L9.6 8.4l2.6 2.6H2v2h10.2l-2.6 2.6L11 17l5-5-5-5zm9 12h-8v2h8c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-8v2h8v14z"/>
                </svg>
                Masuk
            </button>
        </form>

        <!-- Demo accounts -->
        <div class="divider"><span>Akun Demo</span></div>
        <div class="demo-grid">
            <div class="demo-item admin" onclick="fillLogin('admin','password')">
                <div class="demo-role">Admin</div>
                <div class="demo-creds">admin<br>password</div>
            </div>
            <div class="demo-item auditor" onclick="fillLogin('auditor1','password')">
                <div class="demo-role">Auditor</div>
                <div class="demo-creds">auditor1<br>password</div>
            </div>
            <div class="demo-item auditee" onclick="fillLogin('auditee1','password')">
                <div class="demo-role">Auditee</div>
                <div class="demo-creds">auditee1<br>password</div>
            </div>
        </div>
    </div>

    <div class="login-footer">
        &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($nama_app); ?>. All rights reserved.
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle password
    document.getElementById('togglePw').addEventListener('click', function () {
        const pw  = document.getElementById('password');
        const ico = document.getElementById('eyeIcon');
        if (pw.type === 'password') {
            pw.type = 'text';
            ico.className = 'bi bi-eye-slash';
        } else {
            pw.type = 'password';
            ico.className = 'bi bi-eye';
        }
    });

    // Fill demo credentials (tanpa auto-submit)
    function fillLogin(user, pass) {
        document.getElementById('username').value = user;
        document.getElementById('password').value = pass;
        document.getElementById('username').focus();
    }
</script>
</body>
</html>