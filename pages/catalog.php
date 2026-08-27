<?php
require_once '../includes/session.php';
require_once '../includes/functions.php';

$title = 'Katalog Mobil - Chery Mobil Official';

// Get filters
$search = $_GET['search'] ?? '';
$brand = $_GET['brand'] ?? '';
$type = $_GET['type'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = PRODUCTS_PER_PAGE;
$offset = ($page - 1) * $perPage;

// Build filters
$filters = [
    'search' => $search,
    'brand' => $brand,
    'type' => $type,
    'sort' => $sort
];

// Get products
$products = getProducts($perPage, $offset, $filters);
$totalProducts = getTotalProducts($filters);
$totalPages = ceil($totalProducts / $perPage);

// Get filter options
$brands = getProductBrands();
$types = getProductTypes();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<!-- ===== CATALOG HEADER ===== -->
<section class="py-4 bg-light">
    <div class="container">
        <h2 class="fw-bold mb-2">Katalog Mobil</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>" class="text-decoration-none text-dark">Beranda</a></li>
                <li class="breadcrumb-item active">Katalog</li>
            </ol>
        </nav>
    </div>
</section>

<!-- ===== FILTERS ===== -->
<section class="py-4">
    <div class="container">
        <div class="row g-3">
            <div class="col-lg-8">
                <form method="GET" action="" class="d-flex flex-wrap gap-2">
                    <div class="flex-grow-1">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0" 
                                   placeholder="Cari mobil..." value="<?php echo $search; ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary-custom">Cari</button>
                    <?php if ($search || $brand || $type): ?>
                        <a href="<?php echo PAGES_PATH; ?>catalog.php" class="btn btn-outline-secondary">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="col-lg-4">
                <div class="d-flex gap-2">
                    <select name="brand" class="form-select" onchange="this.form.submit()" form="filterForm">
                        <option value="">Semua Brand</option>
                        <?php foreach ($brands as $b): ?>
                            <option value="<?php echo $b['brand']; ?>" <?php echo $brand === $b['brand'] ? 'selected' : ''; ?>>
                                <?php echo $b['brand']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select name="sort" class="form-select" onchange="this.form.submit()" form="filterForm" style="width:auto;">
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Terbaru</option>
                        <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Harga Rendah</option>
                        <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Harga Tinggi</option>
                        <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Populer</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Hidden filter form -->
        <form id="filterForm" method="GET" action="" style="display:none;">
            <?php if ($search): ?>
                <input type="hidden" name="search" value="<?php echo $search; ?>">
            <?php endif; ?>
            <?php if ($type): ?>
                <input type="hidden" name="type" value="<?php echo $type; ?>">
            <?php endif; ?>
        </form>
    </div>
</section>

<!-- ===== PRODUCTS ===== -->
<section class="py-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <span class="text-muted">Menampilkan <?php echo count($products); ?> dari <?php echo $totalProducts; ?> produk</span>
        </div>
        
        <?php if (empty($products)): ?>
            <div class="text-center py-5">
                <i class="fas fa-car fa-4x text-muted mb-3"></i>
                <h4>Tidak ada produk ditemukan</h4>
                <p class="text-muted">Coba ubah filter pencarian Anda</p>
                <a href="<?php echo PAGES_PATH; ?>catalog.php" class="btn btn-primary-custom">Reset Filter</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($products as $product): ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="product-card">
                        <div class="card-image">
                            <img src="../uploads/<?php echo $product['image'] ?? 'default.jpg'; ?>" alt="<?php echo $product['name']; ?>">
                            <div class="image-overlay"></div>
                            <div class="card-badges">
                                <?php if ($product['is_new']): ?>
                                    <span class="card-badge badge-new">Baru</span>
                                <?php endif; ?>
                                <?php if ($product['status'] === 'sold'): ?>
                                    <span class="card-badge badge-sold">Terjual</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="card-meta">
                                <span class="brand"><?php echo $product['brand']; ?></span>
                                <span class="divider"></span>
                                <span class="type"><?php echo $product['type'] ?? 'Mobil'; ?></span>
                            </div>
                            <h6 class="card-title"><?php echo $product['name']; ?></h6>
                            <div class="card-price">
                                <span class="current"><?php echo formatCurrency($product['price']); ?></span>
                                <?php if ($product['price_old'] && $product['price_old'] > $product['price']): ?>
                                    <span class="old"><?php echo formatCurrency($product['price_old']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer">
                                <a href="<?php echo PAGES_PATH; ?>detail.php?id=<?php echo $product['id']; ?>" class="btn-primary-custom w-100 justify-content-center" style="font-size:0.85rem;padding:10px;">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>&brand=<?php echo $brand; ?>&type=<?php echo $type; ?>&sort=<?php echo $sort; ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&brand=<?php echo $brand; ?>&type=<?php echo $type; ?>&sort=<?php echo $sort; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>&brand=<?php echo $brand; ?>&type=<?php echo $type; ?>&sort=<?php echo $sort; ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
