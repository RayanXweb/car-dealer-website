<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin();

$user = getCurrentUser();
$db = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'full_name' => sanitize($_POST['full_name']),
        'phone' => sanitize($_POST['phone']),
        'email' => sanitize($_POST['email'])
    ];
    
    if (!empty($_POST['password'])) {
        if (strlen($_POST['password']) >= 8) {
            $data['password'] = $_POST['password'];
        } else {
            $error = 'Password minimal 8 karakter';
        }
    }
    
    if (!isset($error)) {
        if (updateUser($user['id'], $data)) {
            logActivity($user['id'], 'update_profile', 'Profile updated');
            setFlash('success', 'Profil berhasil diupdate');
            header('Location: profile.php');
            exit();
        } else {
            $error = 'Gagal update profil';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<section class="py-4">
    <div class="container">
        <h2 class="fw-bold mb-4">Profil Saya</h2>
        
        <?php $flash = getFlash(); if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
                <?php echo $flash['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-lg-4">
                <div class="card shadow-sm text-center">
                    <div class="card-body">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto" 
                             style="width:120px;height:120px;font-size:3rem;">
                            <?php echo strtoupper(substr($user['full_name'] ?? $user['username'], 0, 1)); ?>
                        </div>
                        <h5 class="mt-3 fw-bold"><?php echo $user['full_name'] ?? $user['username']; ?></h5>
                        <p class="text-muted">@<?php echo $user['username']; ?></p>
                        <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'secondary'; ?>">
                            <?php echo $user['role'] === 'admin' ? 'Admin' : 'Member'; ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold">Edit Profil</h5>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="full_name" class="form-control" 
                                       value="<?php echo $user['full_name'] ?? ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" value="<?php echo $user['username']; ?>" disabled>
                                <small class="text-muted">Username tidak dapat diubah</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo $user['email']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">No. HP</label>
                                <input type="text" name="phone" class="form-control" 
                                       value="<?php echo $user['phone'] ?? ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password Baru (kosongkan jika tidak diubah)</label>
                                <input type="password" name="password" class="form-control" placeholder="Minimal 8 karakter">
                            </div>
                            <button type="submit" class="btn btn-primary-custom">Simpan Perubahan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
</body>
</html>
