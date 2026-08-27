<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';

$title = 'Chery Mobil Official - Dealer Mobil Terpercaya';

// Get products
$featuredProducts = getFeaturedProducts(6);
$newProducts = getNewProducts(4);
$allProducts = getProducts(8);

// Get settings
$heroTitle = getSetting('hero_title') ?: 'CHERY OMODA';
$heroSubtitle = getSetting('hero_subtitle') ?: 'Mobil Impian, Harga Terjangkau';
$heroDescription = getSetting('hero_description') ?: 'Dapatkan mobil Chery favorit Anda dengan promo spesial dan layanan terbaik dari dealer resmi kami.';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <meta name="description" content="<?php echo getSetting('meta_description') ?: 'Dealer resmi Chery mobil di Indonesia. Temukan mobil impian Anda dengan promo menarik.'; ?>">
    <meta name="keywords" content="<?php echo getSetting('meta_keywords') ?: 'Chery, OMODA, mobil baru, dealer mobil, Chery Indonesia'; ?>">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../includes/header.php'; ?>

<!-- ===== HERO SECTION ===== -->
<section class="hero-premium">
    <div class="hero-bg-glow"></div>
    <div class="hero-bg-glow-2"></div>
    
    <div class="container">
        <div class="row align-items-center min-vh-100">
            <div class="col-lg-6 hero-content">
                <div class="hero-badge">
                    <span class="dot"></span>
                    Dealer Resmi Chery Indonesia
                </div>
                
                <h1>
                    <span class="highlight">CHERY</span><br>
                    OMODA
                </h1>
                <div class="hero-subtitle"><?php echo $heroSubtitle; ?></div>
                
                <p class="hero-desc"><?php echo $heroDescription; ?></p>
                
                <div class="promo-tags">
                    <div class="promo-tag">
                        <i class="fas fa-gem" style="color: #FFD700;"></i>
                        <span>GRATIS <span class="highlight-text">BIAYA PERAWATAN</span></span>
                    </div>
                    <div class="promo-tag">
                        <i class="fas fa-percent" style="color: #00E676;"></i>
                        <span>DP MULAI <span class="highlight-text">15%</span></span>
                    </div>
                    <div class="promo-tag">
                        <i class="fas fa-wallet" style="color: #64B5F6;"></i>
                        <span><span class="highlight-text">CASHBACK</span> SPESIAL</span>
                    </div>
                </div>
                
                <div class="hero-buttons">
                    <a href="<?php echo PAGES_PATH; ?>catalog.php" class="btn-hero btn-hero-primary">
                        <i class="fas fa-car"></i> Lihat Katalog
                    </a>
                    <a href="https://wa.me/<?php echo WHATSAPP_NUMBER; ?>" target="_blank" class="btn-hero btn-hero-outline">
                        <i class="fab fa-whatsapp"></i> Hubungi Kami
                    </a>
                </div>
            </div>
            
            <div class="col-lg-6 hero-image">
                <img src="../assets/images/hero-car.png" alt="Chery OMODA" class="img-fluid">
                
                <!-- Floating Cards -->
                <div class="floating-card card-1">
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="text">
                        <div class="label">Garansi</div>
                        <div class="value">5 Tahun</div>
                    </div>
                </div>
                <div class="floating-card card-2">
                    <div class="icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="text">
                        <div class="label">Rating</div>
                        <div class="value">4.9/5.0</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== FEATURED PRODUCTS ===== -->
<section class="product-grid">
    <div class="container">
        <div class="section-header">
            <span class="tag">Pilihan Terbaik</span>
            <h2>Mobil <span class="highlight">Unggulan</span></h2>
            <p>Pilih mobil impian Anda dari koleksi terbaik kami</p>
        </div>
        
        <div class="row">
            <?php if (empty($featuredProducts)): ?>
                <div class="col-12 text-center py-5">
                    <h4>Belum ada produk unggulan</h4>
                </div>
            <?php else: ?>
                <?php foreach ($featuredProducts as $product): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="product-card animate-in">
                        <div class="card-image">
                            <img src="../uploads/<?php echo $product['image'] ?? 'default.jpg'; ?>" alt="<?php echo $product['name']; ?>">
                            <div class="image-overlay"></div>
                            <div class="card-badges">
                                <?php if ($product['is_new']): ?>
                                    <span class="card-badge badge-new">Baru</span>
                                <?php endif; ?>
                                <?php if ($product['is_hot']): ?>
                                    <span class="card-badge badge-hot">HOT</span>
                                <?php endif; ?>
                                <?php if ($product['price_old'] && $product['price_old'] > $product['price']): ?>
                                    <span class="card-badge badge-hot">Diskon</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-actions">
                                <button onclick="addToWishlist(<?php echo $product['id']; ?>)" title="Wishlist">
                                    <i class="far fa-heart"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="card-meta">
                                <span class="brand"><?php echo $product['brand']; ?></span>
                                <span class="divider"></span>
                                <span class="type"><?php echo $product['type'] ?? 'Mobil'; ?></span>
                            </div>
                            <h5 class="card-title"><?php echo $product['name']; ?></h5>
                            <div class="card-price">
                                <span class="current"><?php echo formatCurrency($product['price']); ?></span>
                                <?php if ($product['price_old'] && $product['price_old'] > $product['price']): ?>
                                    <span class="old"><?php echo formatCurrency($product['price_old']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer">
                                <a href="<?php echo PAGES_PATH; ?>detail.php?id=<?php echo $product['id']; ?>" class="btn-primary-custom">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                                <a href="<?php echo PAGES_PATH; ?>cart.php?add=<?php echo $product['id']; ?>" class="btn-primary-custom" style="background:var(--primary);">
                                    <i class="fas fa-shopping-bag"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="<?php echo PAGES_PATH; ?>catalog.php" class="btn-primary-custom" style="padding:14px 50px;">
                Lihat Semua Mobil <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- ===== NEW PRODUCTS ===== -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="section-header">
            <span class="tag">Terbaru</span>
            <h2>Mobil <span class="highlight">Terbaru</span></h2>
            <p>Dapatkan mobil terbaru dari Chery dengan teknologi terkini</p>
        </div>
        
        <div class="row">
            <?php foreach ($newProducts as $product): ?>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="product-card animate-in delay-<?php echo $loop->index + 1; ?>">
                    <div class="card-image">
                        <img src="../uploads/<?php echo $product['image'] ?? 'default.jpg'; ?>" alt="<?php echo $product['name']; ?>">
                        <div class="image-overlay"></div>
                        <div class="card-badges">
                            <span class="card-badge badge-new">Baru</span>
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
    </div>
</section>

<!-- ===== PROMO BANNER ===== -->
<section class="promo-banner">
    <div class="container">
        <div class="content">
            <div class="icon">🚗</div>
            <h3>Dapatkan Penawaran Spesial!</h3>
            <p>Gratis biaya perawatan service + DP mulai 15% untuk semua model Chery.</p>
            <a href="https://wa.me/<?php echo WHATSAPP_NUMBER; ?>" target="_blank" class="btn-white">
                <i class="fab fa-whatsapp"></i> Hubungi Kami Sekarang
            </a>
        </div>
    </div>
</section>

<!-- ===== ALL PRODUCTS ===== -->
<section class="product-grid" style="background:var(--white);padding-top:60px;">
    <div class="container">
        <div class="section-header">
            <span class="tag">Koleksi</span>
            <h2>Semua <span class="highlight">Mobil</span></h2>
            <p>Temukan berbagai pilihan mobil Chery dan OMODA</p>
        </div>
        
        <div class="row">
            <?php foreach ($allProducts as $product): ?>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="product-card animate-in">
                    <div class="card-image">
                        <img src="../uploads/<?php echo $product['image'] ?? 'default.jpg'; ?>" alt="<?php echo $product['name']; ?>">
                        <div class="image-overlay"></div>
                        <div class="card-badges">
                            <?php if ($product['status'] === 'sold'): ?>
                                <span class="card-badge badge-sold">Terjual</span>
                            <?php elseif ($product['status'] === 'coming'): ?>
                                <span class="card-badge" style="background:#FF9800;color:white;">Segera</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="card-meta">
                            <span class="brand"><?php echo $product['brand']; ?></span>
                        </div>
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
        
        <div class="text-center mt-4">
            <a href="<?php echo PAGES_PATH; ?>catalog.php" class="btn-primary-custom" style="padding:14px 50px;">
                Lihat Semua <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- ===== TESTIMONIALS ===== -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="section-header">
            <span class="tag">Testimonial</span>
            <h2>Apa Kata <span class="highlight">Pelanggan</span></h2>
            <p>Pengalaman nyata dari pelanggan yang sudah memiliki mobil Chery</p>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-warning mb-3">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="card-text">"Mobil Chery sangat nyaman dan irit bahan bakar. Pelayanan dealer juga sangat memuaskan!"</p>
                        <div class="d-flex align-items-center mt-3">
                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                                <span class="fw-bold">A</span>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-0">Andi Wijaya</h6>
                                <small class="text-muted">Pemilik TIGGO CROSS CSH</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-warning mb-3">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="card-text">"OMODA 5 benar-benar luar biasa! Desainnya modern dan performanya sangat responsif."</p>
                        <div class="d-flex align-items-center mt-3">
                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                                <span class="fw-bold">S</span>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-0">Sarah Putri</h6>
                                <small class="text-muted">Pemilik OMODA 5</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-warning mb-3">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="card-text">"Proses pembelian sangat mudah dan cepat. Dealer Chery sangat profesional dan ramah."</p>
                        <div class="d-flex align-items-center mt-3">
                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                                <span class="fw-bold">B</span>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-0">Budi Santoso</h6>
                                <small class="text-muted">Pemilik J6 T</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

<script>
// Animation on scroll
document.addEventListener('DOMContentLoaded', function() {
    const animatedElements = document.querySelectorAll('.animate-in');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });
    
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'all 0.6s ease';
        observer.observe(el);
    });
});

function addToWishlist(productId) {
    <?php if (!isLoggedIn()): ?>
        window.location.href = '<?php echo PAGES_PATH; ?>login.php';
        return;
    <?php endif; ?>
    
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
            const icon = document.querySelector(`[onclick="addToWishlist(${productId})"] i`);
            if (icon) {
                icon.classList.toggle('far');
                icon.classList.toggle('fas');
            }
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
