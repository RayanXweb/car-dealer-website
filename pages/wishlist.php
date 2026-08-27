<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$wishlist = getWishlist($user_id);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<section class="py-4">
    <div class="container">
        <h2 class="fw-bold mb-4">Wishlist</h2>
        
        <?php if (empty($wishlist)): ?>
            <div class="text-center py-5">
                <i class="fas fa-heart fa-4x text-muted mb-3"></i>
                <h4>Wishlist kosong</h4>
                <p class="text-muted">Simpan mobil favorit Anda di sini</p>
                <a href="<?php echo PAGES_PATH; ?>catalog.php" class="btn-primary-custom mt-3">
                    <i class="fas fa-car"></i> Lihat Katalog
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($wishlist as $product): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="product-card">
                        <div class="card-image">
                            <img src="../uploads/<?php echo $product['image'] ?? 'default.jpg'; ?>" alt="<?php echo $product['name']; ?>">
                            <div class="image-overlay"></div>
                            <div class="card-actions">
                                <button onclick="removeWishlist(<?php echo $product['id']; ?>)" class="btn btn-danger btn-sm" title="Hapus dari wishlist">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title"><?php echo $product['name']; ?></h6>
                            <div class="card-price">
                                <span class="current"><?php echo formatCurrency($product['price']); ?></span>
                            </div>
                            <a href="<?php echo PAGES_PATH; ?>detail.php?id=<?php echo $product['id']; ?>" class="btn-primary-custom w-100 justify-content-center" style="font-size:0.85rem;padding:10px;">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

<script>
function removeWishlist(productId) {
    fetch('<?php echo PAGES_PATH; ?>wishlist-toggle.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
</body>
</html>
