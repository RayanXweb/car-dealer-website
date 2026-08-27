<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';

$id = intval($_GET['id'] ?? 0);
$product = getProduct($id);

if (!$product) {
    header('Location: catalog.php');
    exit();
}

// Increment views
$db = db();
$db->query("UPDATE products SET views = views + 1 WHERE id = $id");

$title = $product['name'] . ' - Chery Mobil Official';
$relatedProducts = getRelatedProducts($id, $product['brand']);
$isInWishlist = isLoggedIn() ? isInWishlist($_SESSION['user_id'], $id) : false;
$isInCart = isLoggedIn() ? !empty(getCart($_SESSION['user_id'])) : false;

// Handle offer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_offer'])) {
    $offerData = [
        'user_id' => isLoggedIn() ? $_SESSION['user_id'] : null,
        'product_id' => $id,
        'customer_name' => sanitize($_POST['customer_name']),
        'customer_phone' => sanitize($_POST['customer_phone']),
        'customer_email' => sanitize($_POST['customer_email']),
        'message' => sanitize($_POST['message']),
        'offer_amount' => floatval($_POST['offer_amount'])
    ];
    
    if (createOffer($offerData)) {
        $waMessage = generateOfferWhatsAppMessage($product, $offerData);
        $waLink = generateWhatsAppLink(WHATSAPP_NUMBER, $waMessage);
        header("Location: $waLink");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <meta name="description" content="<?php echo truncateText($product['description'], 160); ?>">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .product-gallery .main-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 12px;
        }
        .product-gallery .thumb-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        .product-gallery .thumb-image:hover,
        .product-gallery .thumb-image.active {
            border-color: var(--primary);
        }
        .spec-item {
            padding: 10px 0;
            border-bottom: 1px solid var(--gray-200);
        }
        .spec-item:last-child {
            border-bottom: none;
        }
        .spec-label {
            color: var(--gray-400);
            font-size: 0.85rem;
        }
        .spec-value {
            font-weight: 500;
        }
        @media (max-width: 768px) {
            .product-gallery .main-image {
                height: 250px;
            }
        }
    </style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<!-- ===== PRODUCT DETAIL ===== -->
<section class="py-4">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>" class="text-decoration-none">Beranda</a></li>
                <li class="breadcrumb-item"><a href="<?php echo PAGES_PATH; ?>catalog.php" class="text-decoration-none">Katalog</a></li>
                <li class="breadcrumb-item active"><?php echo $product['name']; ?></li>
            </ol>
        </nav>
        
        <div class="row mt-3">
            <!-- Gallery -->
            <div class="col-lg-6">
                <div class="product-gallery">
                    <img src="../uploads/<?php echo $product['image'] ?? 'default.jpg'; ?>" 
                         alt="<?php echo $product['name']; ?>" class="main-image" id="mainImage">
                    
                    <div class="d-flex gap-2 mt-3 flex-wrap">
                        <img src="../uploads/<?php echo $product['image'] ?? 'default.jpg'; ?>" 
                             class="thumb-image active" onclick="changeImage(this)">
                        <?php if ($product['images']): ?>
                            <?php foreach (explode(',', $product['images']) as $img): ?>
                                <img src="../uploads/<?php echo trim($img); ?>" 
                                     class="thumb-image" onclick="changeImage(this)">
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Info -->
            <div class="col-lg-6">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-primary"><?php echo $product['brand']; ?></span>
                    <span class="badge bg-secondary"><?php echo $product['type'] ?? 'Mobil'; ?></span>
                    <?php if ($product['is_new']): ?>
                        <span class="badge bg-success">Baru</span>
                    <?php endif; ?>
                </div>
                
                <h2 class="fw-bold"><?php echo $product['name']; ?></h2>
                <p class="text-muted"><?php echo $product['model'] ? $product['brand'] . ' ' . $product['model'] : $product['brand']; ?></p>
                
                <div class="d-flex align-items-center gap-3 mb-3">
                    <h3 class="text-danger fw-bold"><?php echo formatCurrency($product['price']); ?></h3>
                    <?php if ($product['price_old'] && $product['price_old'] > $product['price']): ?>
                        <span class="text-muted text-decoration-line-through"><?php echo formatCurrency($product['price_old']); ?></span>
                        <span class="badge bg-danger">Diskon</span>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <span class="badge bg-<?php echo $product['status'] === 'available' ? 'success' : ($product['status'] === 'sold' ? 'danger' : 'warning'); ?> fs-6">
                        <?php echo $product['status'] === 'available' ? '✓ Tersedia' : ($product['status'] === 'sold' ? 'Terjual' : 'Segera'); ?>
                    </span>
                    <span class="ms-2 text-muted">Stok: <?php echo $product['stock'] > 0 ? $product['stock'] . ' unit' : 'Habis'; ?></span>
                </div>
                
                <p class="mt-3"><?php echo nl2br($product['description']); ?></p>
                
                <!-- Actions -->
                <div class="d-flex flex-wrap gap-2 mt-4">
                    <?php if ($product['status'] === 'available' && $product['stock'] > 0): ?>
                        <a href="<?php echo PAGES_PATH; ?>cart.php?add=<?php echo $product['id']; ?>" class="btn-primary-custom">
                            <i class="fas fa-shopping-bag"></i> Tambah ke Keranjang
                        </a>
                    <?php else: ?>
                        <button class="btn-primary-custom" style="background:#999;cursor:not-allowed;" disabled>
                            <i class="fas fa-times"></i> Tidak Tersedia
                        </button>
                    <?php endif; ?>
                    
                    <a href="https://wa.me/<?php echo WHATSAPP_NUMBER; ?>?text=Halo%20saya%20tertarik%20dengan%20<?php echo urlencode($product['name']); ?>" 
                       target="_blank" class="btn btn-success" style="border-radius:50px;padding:12px 28px;">
                        <i class="fab fa-whatsapp"></i> Tanya via WA
                    </a>
                    
                    <button class="btn btn-outline-danger" style="border-radius:50px;padding:12px 20px;" 
                            onclick="toggleWishlist(<?php echo $product['id']; ?>)">
                        <i class="<?php echo $isInWishlist ? 'fas' : 'far'; ?> fa-heart"></i>
                    </button>
                </div>
                
                <!-- Offer Button -->
                <button class="btn btn-outline-secondary mt-3" data-bs-toggle="collapse" data-bs-target="#offerForm">
                    <i class="fas fa-gavel"></i> Buat Penawaran
                </button>
                
                <div class="collapse mt-3" id="offerForm">
                    <div class="card card-body">
                        <h6>Buat Penawaran</h6>
                        <form method="POST" action="">
                            <input type="hidden" name="submit_offer" value="1">
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="text" name="customer_name" class="form-control" placeholder="Nama Anda" required>
                                </div>
                                <div class="col-6">
                                    <input type="text" name="customer_phone" class="form-control" placeholder="No. HP" required>
                                </div>
                                <div class="col-12">
                                    <input type="email" name="customer_email" class="form-control" placeholder="Email (opsional)">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="offer_amount" class="form-control" placeholder="Harga Penawaran" required>
                                </div>
                                <div class="col-12">
                                    <textarea name="message" class="form-control" rows="2" placeholder="Pesan"></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="fab fa-whatsapp"></i> Kirim Penawaran via WA
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Specs -->
                <?php if ($product['specs']): ?>
                <div class="mt-4">
                    <h6 class="fw-bold">Spesifikasi</h6>
                    <div class="row">
                        <?php 
                        $specs = json_decode($product['specs'], true);
                        if ($specs && is_array($specs)):
                            foreach ($specs as $key => $value):
                        ?>
                        <div class="col-6 spec-item">
                            <div class="spec-label"><?php echo ucfirst($key); ?></div>
                            <div class="spec-value"><?php echo $value; ?></div>
                        </div>
                        <?php 
                            endforeach;
                        endif;
                        ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- ===== RELATED PRODUCTS ===== -->
<?php if ($relatedProducts): ?>
<section class="py-5 bg-light">
    <div class="container">
        <h4 class="fw-bold mb-4">Mobil Lainnya dari <?php echo $product['brand']; ?></h4>
        <div class="row">
            <?php foreach ($relatedProducts as $rel): ?>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="product-card">
                    <div class="card-image">
                        <img src="../uploads/<?php echo $rel['image'] ?? 'default.jpg'; ?>" alt="<?php echo $rel['name']; ?>">
                        <div class="image-overlay"></div>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title"><?php echo $rel['name']; ?></h6>
                        <div class="card-price">
                            <span class="current"><?php echo formatCurrency($rel['price']); ?></span>
                        </div>
                        <a href="<?php echo PAGES_PATH; ?>detail.php?id=<?php echo $rel['id']; ?>" class="btn-primary-custom w-100 justify-content-center" style="font-size:0.85rem;padding:10px;">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>

<script>
function changeImage(element) {
    const mainImage = document.getElementById('mainImage');
    mainImage.src = element.src;
    
    document.querySelectorAll('.thumb-image').forEach(el => {
        el.classList.remove('active');
    });
    element.classList.add('active');
}

function toggleWishlist(productId) {
    <?php if (!isLoggedIn()): ?>
        window.location.href = '<?php echo PAGES_PATH; ?>login.php';
        return;
    <?php endif; ?>
    
    const icon = document.querySelector(`[onclick="toggleWishlist(${productId})"] i`);
    
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
            if (data.action === 'added') {
                icon.className = 'fas fa-heart';
            } else {
                icon.className = 'far fa-heart';
            }
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
