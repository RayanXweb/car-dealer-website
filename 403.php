<?php
require_once 'includes/session.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="py-5 text-center">
        <div class="container">
            <div class="py-5">
                <h1 class="display-1 fw-bold text-danger">403</h1>
                <h2 class="fw-bold">Akses Ditolak</h2>
                <p class="text-muted">Anda tidak memiliki izin untuk mengakses halaman ini.</p>
                <a href="<?php echo SITE_URL; ?>" class="btn btn-primary-custom mt-3">
                    <i class="fas fa-home"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </section>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
