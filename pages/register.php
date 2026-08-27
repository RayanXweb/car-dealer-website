<?php
require_once '../includes/session.php';
require_once '../includes/auth.php';
requireGuest();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username' => sanitize($_POST['username'] ?? ''),
        'email' => sanitize($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'full_name' => sanitize($_POST['full_name'] ?? ''),
        'phone' => sanitize($_POST['phone'] ?? '')
    ];
    
    if (strlen($data['password']) < 8) {
        $error = 'Password minimal 8 karakter';
    } else {
        $result = register($data);
        if ($result['success']) {
            $success = 'Registrasi berhasil! Silakan login.';
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
    <title>Daftar - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%);
            padding: 20px 0;
        }
        .register-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            margin: 0 auto;
        }
        .register-card .brand {
            text-align: center;
            margin-bottom: 30px;
        }
        .register-card .brand h1 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
        }
        .register-card .brand p {
            color: #999;
        }
        .register-card .form-control {
            border-radius: 12px;
            padding: 12px 16px;
            border: 2px solid #eee;
        }
        .register-card .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(214,28,28,0.1);
        }
        .register-card .btn-register {
            border-radius: 12px;
            padding: 14px;
            font-weight: 600;
            background: var(--primary);
            color: white;
            border: none;
            width: 100%;
            transition: all 0.3s ease;
        }
        .register-card .btn-register:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<div class="register-container">
    <div class="register-card">
        <div class="brand">
            <h1>CHERY</h1>
            <p>Buat akun baru</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="full_name" class="form-control" 
                       placeholder="Masukkan nama lengkap" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" 
                       placeholder="Masukkan username" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" 
                       placeholder="Masukkan email" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">No. HP</label>
                <input type="text" name="phone" class="form-control" 
                       placeholder="Masukkan nomor HP">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" 
                       placeholder="Minimal 8 karakter" required minlength="8">
                <small class="text-muted">Password minimal 8 karakter</small>
            </div>
            
            <button type="submit" class="btn-register">Daftar</button>
        </form>
        
        <hr>
        <p class="text-center mb-0">
            Sudah punya akun? <a href="login.php" class="text-danger fw-bold">Login</a>
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
