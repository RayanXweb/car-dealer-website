<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$db = db();

// Handle actions
if (isset($_GET['add'])) {
    $product_id = intval($_GET['add']);
    addToCart($product_id, 1, $user_id);
    header('Location: cart.php');
    exit();
}

if (isset($_GET['remove'])) {
    $cart_id = intval($_GET['remove']);
    removeFromCart($cart_id, $user_id);
    header('Location: cart.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    foreach ($_POST['quantity'] as $id => $qty) {
        if ($qty > 0) {
            updateCartQuantity($id, $qty, $user_id);
        } else {
            removeFromCart($id, $user_id);
        }
    }
    header('Location: cart.php');
    exit();
}

$cartItems = getCart($user_id);
$total = getCartTotal($user_id);
$count = getCartCount($user_id);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<section class="py-4">
    <div class="container">
        <h2 class="fw-bold mb-4">Keranjang Belanja</h2>
        
        <?php if (empty($cartItems)): ?>
            <div class="text-center py-5">
                <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
                <h4>Keranjang kosong</h4>
                <p class="text-muted">Mulai belanja mobil impian Anda sekarang!</p>
                <a href="<?php echo PAGES_PATH; ?>catalog.php" class="btn-primary-custom mt-3">
                    <i class="fas fa-car"></i> Belanja Sekarang
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-8">
                    <form method="POST" action="">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <thead>
                                            <tr>
                                                <th>Produk</th>
                                                <th>Harga</th>
                                                <th>Jumlah</th>
                                                <th>Subtotal</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($cartItems as $item): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="../uploads/<?php echo $item['image'] ?? 'default.jpg'; ?>" 
                                                             style="width:60px;height:60px;object-fit:cover;border-radius:8px;">
                                                        <div class="ms-3">
                                                            <div class="fw-bold"><?php echo $item['name']; ?></div>
                                                            <small class="text-muted">Stok: <?php echo $item['stock'] ?? 0; ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo formatCurrency($item['price']); ?></td>
                                                <td>
                                                    <input type="number" name="quantity[<?php echo $item['id']; ?>]" 
                                                           value="<?php echo $item['quantity']; ?>" min="1" 
                                                           max="<?php echo $item['stock'] ?? 99; ?>"
                                                           class="form-control" style="width:70px;">
                                                </td>
                                                <td><?php echo formatCurrency($item['price'] * $item['quantity']); ?></td>
                                                <td>
                                                    <a href="cart.php?remove=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    <button type="submit" name="update" class="btn btn-secondary">
                                        <i class="fas fa-sync"></i> Update Keranjang
                                    </button>
                                    <a href="<?php echo PAGES_PATH; ?>catalog.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left"></i> Lanjut Belanja
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Summary -->
                <div class="col-lg-4">
                    <div class="card shadow-sm sticky-top" style="top:80px;">
                        <div class="card-body">
                            <h5 class="fw-bold">Ringkasan</h5>
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Jumlah Item</span>
                                <span><?php echo $count; ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total</span>
                                <span class="fw-bold text-danger fs-5"><?php echo formatCurrency($total); ?></span>
                            </div>
                            
                            <hr>
                            <a href="<?php echo PAGES_PATH; ?>checkout.php" class="btn-primary-custom w-100 justify-content-center" style="padding:14px;">
                                <i class="fas fa-credit-card"></i> Checkout
                            </a>
                            <a href="https://wa.me/<?php echo WHATSAPP_NUMBER; ?>?text=Halo%20saya%20ingin%20memesan%20keranjang%20saya" 
                               target="_blank" class="btn btn-success w-100 mt-2" style="border-radius:50px;padding:14px;">
                                <i class="fab fa-whatsapp"></i> Pesan via WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
