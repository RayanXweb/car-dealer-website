<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin();

$order_id = intval($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];

$order = getOrder($order_id);

if (!$order || $order['user_id'] != $user_id) {
    header('Location: orders.php');
    exit();
}

$items = getOrderItems($order_id);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<section class="py-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Detail Pesanan</h2>
            <a href="orders.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <span class="fw-bold"><?php echo $order['order_number']; ?></span>
                        <span><?php echo getStatusBadge($order['status']); ?></span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Tanggal:</strong> <?php echo formatDate($order['created_at']); ?></p>
                                <p class="mb-1"><strong>Pelanggan:</strong> <?php echo $order['customer_name']; ?></p>
                                <p class="mb-1"><strong>No. HP:</strong> <?php echo $order['customer_phone']; ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Email:</strong> <?php echo $order['customer_email'] ?? '-'; ?></p>
                                <p class="mb-1"><strong>Alamat:</strong> <?php echo $order['customer_address'] ?? '-'; ?></p>
                                <p class="mb-1"><strong>Catatan:</strong> <?php echo $order['notes'] ?? '-'; ?></p>
                            </div>
                        </div>
                        
                        <h6 class="fw-bold">Item Pesanan</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produk</th>
                                        <th>Jumlah</th>
                                        <th>Harga</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="../uploads/<?php echo $item['image'] ?? 'default.jpg'; ?>" 
                                                     style="width:40px;height:40px;object-fit:cover;border-radius:4px;">
                                                <span class="ms-2"><?php echo $item['name']; ?></span>
                                            </div>
                                        </td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><?php echo formatCurrency($item['price']); ?></td>
                                        <td><?php echo formatCurrency($item['subtotal']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold">
                                        <td colspan="3" class="text-end">Total</td>
                                        <td><?php echo formatCurrency($order['final_amount']); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="fw-bold">Status Pesanan</h6>
                        <div class="mt-3">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-<?php echo $order['status'] === 'pending' ? 'warning' : 'success'; ?> me-2">
                                    <?php echo $order['status'] === 'pending' ? '⏳' : '✅'; ?>
                                </span>
                                <span>Pesanan Dibuat</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-<?php echo in_array($order['status'], ['confirmed', 'processing', 'shipping', 'completed']) ? 'success' : 'secondary'; ?> me-2">
                                    <?php echo in_array($order['status'], ['confirmed', 'processing', 'shipping', 'completed']) ? '✅' : '⏳'; ?>
                                </span>
                                <span>Dikonfirmasi</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-<?php echo in_array($order['status'], ['processing', 'shipping', 'completed']) ? 'success' : 'secondary'; ?> me-2">
                                    <?php echo in_array($order['status'], ['processing', 'shipping', 'completed']) ? '✅' : '⏳'; ?>
                                </span>
                                <span>Diproses</span>
                            </div>
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-<?php echo in_array($order['status'], ['shipping', 'completed']) ? 'success' : 'secondary'; ?> me-2">
                                    <?php echo in_array($order['status'], ['shipping', 'completed']) ? '✅' : '⏳'; ?>
                                </span>
                                <span>Dikirim</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-<?php echo $order['status'] === 'completed' ? 'success' : 'secondary'; ?> me-2">
                                    <?php echo $order['status'] === 'completed' ? '✅' : '⏳'; ?>
                                </span>
                                <span>Selesai</span>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <a href="https://wa.me/<?php echo WHATSAPP_NUMBER; ?>?text=Halo%20saya%20ingin%20menanyakan%20status%20pesanan%20<?php echo $order['order_number']; ?>" 
                           target="_blank" class="btn btn-success w-100">
                            <i class="fab fa-whatsapp"></i> Tanya Status via WA
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
</body>
</html>
