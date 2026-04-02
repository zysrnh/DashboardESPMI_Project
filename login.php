<?php
require_once 'inc/inc_koneksi.php';
require_once 'inc/inc_fungsi.php';  // ✅ TAMBAHKAN INI

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = esc($_POST['username']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM users WHERE username = '$username' AND status = 'aktif'";
    $result = mysqli_query($koneksi, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['unit_kerja'] = $user['unit_kerja'];
            
            // Setelah login berhasil, tambahkan:
            log_aktivitas($user['id'], "Login ke sistem", 'users', $user['id']);
            
            // Redirect berdasarkan role
            switch ($user['role']) {
                case 'admin':
                    header("Location: admin/index.php");
                    break;
                case 'auditor':
                    header("Location: auditor/index.php");
                    break;
                case 'auditee':
                    header("Location: auditee/index.php");
                    break;
                default:
                    header("Location: index.php");
            }
            exit();
        } else {
            $error = 'Password salah!';
        }
    } else {
        $error = 'Username tidak ditemukan atau akun tidak aktif!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-SPMI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            max-width: 450px;
            margin: 0 auto;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 30px;
            text-align: center;
        }
        .card-body {
            padding: 40px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-login:hover {
            opacity: 0.9;
        }
        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
        }
        .form-control {
            border-left: none;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #ced4da;
        }
        .input-group:focus-within .input-group-text {
            border-color: #667eea;
        }
        .input-group:focus-within .form-control {
            border-color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0"><i class="bi bi-shield-check"></i> E-SPMI</h3>
                    <p class="mb-0 mt-2">Sistem Penjaminan Mutu Internal</p>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" name="username" placeholder="Masukkan username" required autofocus>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" name="password" id="password" placeholder="Masukkan password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="bi bi-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-login w-100">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </button>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center text-muted small">
                        <p class="mb-1"><strong>Akun Demo:</strong></p>
                        <div class="row text-start">
                            <div class="col-6">
                                <small>
                                    <strong>Admin:</strong><br>
                                    Username: <code>admin</code><br>
                                    Password: <code>password</code>
                                </small>
                            </div>
                            <div class="col-6">
                                <small>
                                    <strong>Auditor:</strong><br>
                                    Username: <code>auditor1</code><br>
                                    Password: <code>password</code>
                                </small>
                            </div>
                            <div class="col-6">
                                <small>
                                    <strong>Auditee:</strong><br>
                                    Username: <code>auditee1</code><br>
                                    Password: <code>password</code>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4 text-white">
                <small>&copy; 2025 E-SPMI. All rights reserved.</small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (password.type === 'password') {
                password.type = 'text';
                eyeIcon.classList.remove('bi-eye');
                eyeIcon.classList.add('bi-eye-slash');
            } else {
                password.type = 'password';
                eyeIcon.classList.remove('bi-eye-slash');
                eyeIcon.classList.add('bi-eye');
            }
        });
    </script>
</body>
</html>