<?php
require_once '../includes/session.php';
require_once '../includes/auth.php';
requireGuest();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi';
    } else {
        $result = login($username, $password);
        if ($result['success']) {
            if ($remember) {
                setcookie('remember_token', $result['token'], time() + 86400 * 30, '/');
            }
            $redirect = $_SESSION['redirect'] ?? SITE_URL;
            unset($_SESSION['redirect']);
            header("Location: $redirect");
            exit();
        } else {
            $error = $result['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%);
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 420px;
            width: 100%;
            margin: 0 auto;
        }
        .login-card .brand {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-card .brand h1 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
        }
        .login-card .brand p {
            color: #999;
        }
        .login-card .form-control {
            border-radius: 12px;
            padding: 12px 16px;
            border: 2px solid #eee;
        }
        .login-card .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(214,28,28,0.1);
        }
        .login-card .btn-login {
            border-radius: 12px;
            padding: 14px;
            font-weight: 600;
            background: var(--primary);
            color: white;
            border: none;
            width: 100%;
            transition: all 0.3s ease;
        }
        .login-card .btn-login:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        .login-card .divider {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 20px 0;
        }
        .login-card .divider::before,
        .login-card .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #eee;
        }
        .login-card .divider span {
            color: #999;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="brand">
            <h1>CHERY</h1>
            <p>Masuk ke akun Anda</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Username atau Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-user text-muted"></i>
                    </span>
                    <input type="text" name="username" class="form-control border-start-0" 
                           placeholder="Masukkan username atau email" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-lock text-muted"></i>
                    </span>
                    <input type="password" name="password" class="form-control border-start-0" 
                           placeholder="Masukkan password" required>
                </div>
            </div>
            
            <div class="d-flex justify-content-between mb-3">
                <div class="form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Ingat saya</label>
                </div>
                <a href="forgot-password.php" class="text-decoration-none text-danger">Lupa password?</a>
            </div>
            
            <button type="submit" class="btn-login">Masuk</button>
        </form>
        
        <div class="divider">
            <span>atau</span>
        </div>
        
        <p class="text-center mb-0">
            Belum punya akun? <a href="register.php" class="text-danger fw-bold">Daftar</a>
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
