<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$db = db();
$user = getCurrentUser();

$cartItems = getCart($user_id);
if (empty($cartItems)) {
    header('Location: cart.php');
    exit();
}

$total = getCartTotal($user_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'customer_name' => sanitize($_POST['customer_name']),
        'customer_phone' => sanitize($_POST['customer_phone']),
        'customer_email' => sanitize($_POST['customer_email']),
        'customer_address' => sanitize($_POST['customer_address']),
        'notes' => sanitize($_POST['notes'] ?? ''),
        'total' => $total,
        'discount' => 0,
        'items' => array_map(function($item) {
            return [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price']
            ];
        }, $cartItems)
    ];
    
    $result = createOrder($user_id, $data);
    
    if ($result['success']) {
        $order = getOrder($result['order_id']);
        $items = getOrderItems($result['order_id']);
        $waMessage = generateOrderWhatsAppMessage($order, $items);
        $waLink = generateWhatsAppLink(WHATSAPP_NUMBER, $waMessage);
        header("Location: $waLink");
        exit();
    } else {
        $_SESSION['error'] = $result['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<section class="py-4">
    <div class="container">
        <h2 class="fw-bold mb-4">Checkout</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold">Informasi Pemesanan</h5>
                        <form method="POST" action="">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Nama Lengkap *</label>
                                    <input type="text" name="customer_name" class="form-control" 
                                           value="<?php echo $user['full_name'] ?? ''; ?>" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">No. HP *</label>
                                    <input type="text" name="customer_phone" class="form-control" 
                                           value="<?php echo $user['phone'] ?? ''; ?>" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="customer_email" class="form-control" 
                                           value="<?php echo $user['email'] ?? ''; ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Alamat Pengiriman</label>
                                    <textarea name="customer_address" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Catatan</label>
                                    <textarea name="notes" class="form-control" rows="2"></textarea>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-success btn-lg w-100" style="border-radius:50px;">
                                    <i class="fab fa-whatsapp"></i> Konfirmasi & Kirim via WhatsApp
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-5">
                <div class="card shadow-sm sticky-top" style="top:80px;">
                    <div class="card-body">
                        <h5 class="fw-bold">Ringkasan Pesanan</h5>
                        <hr>
                        
                        <?php foreach ($cartItems as $item): ?>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span>
                                <?php echo $item['name']; ?> 
                                <span class="text-muted">x<?php echo $item['quantity']; ?></span>
                            </span>
                            <span><?php echo formatCurrency($item['price'] * $item['quantity']); ?></span>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="d-flex justify-content-between fw-bold pt-2 fs-5">
                            <span>Total</span>
                            <span class="text-danger"><?php echo formatCurrency($total); ?></span>
                        </div>
                        
                        <hr>
                        <p class="text-muted small mb-0">
                            <i class="fas fa-info-circle"></i> 
                            Pesanan akan dikirim melalui WhatsApp untuk konfirmasi
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
