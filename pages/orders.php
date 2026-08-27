<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$db = db();

$orders = getUserOrders($user_id);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<section class="py-4">
    <div class="container">
        <h2 class="fw-bold mb-4">Pesanan Saya</h2>
        
        <?php if (empty($orders)): ?>
            <div class="text-center py-5">
                <i class="fas fa-box fa-4x text-muted mb-3"></i>
                <h4>Belum ada pesanan</h4>
                <p class="text-muted">Mulai belanja mobil impian Anda sekarang!</p>
                <a href="<?php echo PAGES_PATH; ?>catalog.php" class="btn-primary-custom mt-3">
                    <i class="fas fa-car"></i> Belanja Sekarang
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($orders as $order): ?>
                <div class="col-12 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-bold"><?php echo $order['order_number']; ?></span>
                                <span class="text-muted ms-2"><?php echo formatDate($order['created_at']); ?></span>
                            </div>
                            <span><?php echo getStatusBadge($order['status']); ?></span>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <p class="mb-1"><strong>Pelanggan:</strong> <?php echo $order['customer_name']; ?></p>
                                    <p class="mb-1"><strong>No. HP:</strong> <?php echo $order['customer_phone']; ?></p>
                                    <?php if ($order['customer_address']): ?>
                                        <p class="mb-1"><strong>Alamat:</strong> <?php echo $order['customer_address']; ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4 text-end">
                                    <h5 class="text-danger fw-bold"><?php echo formatCurrency($order['final_amount']); ?></h5>
                                    <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                    <?php if ($order['status'] === 'pending' || $order['status'] === 'confirmed'): ?>
                                        <a href="https://wa.me/<?php echo WHATSAPP_NUMBER; ?>?text=Halo%20saya%20ingin%20menanyakan%20status%20pesanan%20<?php echo $order['order_number']; ?>" 
                                           target="_blank" class="btn btn-sm btn-success">
                                            <i class="fab fa-whatsapp"></i> Tanya Status
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
</body>
</html>
