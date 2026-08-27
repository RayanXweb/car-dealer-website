<?php
$cartCount = isLoggedIn() ? getCartCount($_SESSION['user_id']) : 0;
$currentPage = basename($_SERVER['PHP_SELF']);
$isAdmin = isAdmin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>

<!-- ===== NAVBAR PREMIUM ===== -->
<nav class="navbar-premium" id="mainNav">
    <div class="container">
        <!-- Brand -->
        <a href="<?php echo SITE_URL; ?>" class="brand">
            <span>CHERY</span>
            <span style="font-weight:400;color:#999;">|</span>
            <span style="font-weight:400;color:#333;">OMODA</span>
        </a>
        
        <!-- Mobile Toggle -->
        <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Nav Links -->
        <ul class="nav-links" id="navLinks">
            <li><a href="<?php echo SITE_URL; ?>" class="<?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">Beranda</a></li>
            <li><a href="<?php echo PAGES_PATH; ?>catalog.php" class="<?php echo $currentPage === 'catalog.php' ? 'active' : ''; ?>">Katalog</a></li>
            <?php if (isLoggedIn()): ?>
                <li><a href="<?php echo PAGES_PATH; ?>orders.php" class="<?php echo $currentPage === 'orders.php' ? 'active' : ''; ?>">Pesanan Saya</a></li>
                <?php if ($isAdmin): ?>
                    <li><a href="<?php echo ADMIN_PATH; ?>dashboard.php" style="color:var(--primary) !important;">
                        <i class="fas fa-user-shield"></i> Admin
                    </a></li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>
        
        <!-- Nav Actions -->
        <div class="nav-actions">
            <a href="<?php echo PAGES_PATH; ?>cart.php" class="cart-btn" title="Keranjang">
                <i class="fas fa-shopping-bag"></i>
                <?php if ($cartCount > 0): ?>
                    <span class="cart-badge"><?php echo $cartCount; ?></span>
                <?php endif; ?>
            </a>
            
            <?php if (isLoggedIn()): ?>
                <div class="dropdown d-inline-block">
                    <button class="btn btn-link text-dark text-decoration-none dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle fa-lg"></i>
                        <span class="d-none d-md-inline"><?php echo $_SESSION['user_name'] ?? $_SESSION['username']; ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?php echo PAGES_PATH; ?>profile.php"><i class="fas fa-user me-2"></i>Profil</a></li>
                        <li><a class="dropdown-item" href="<?php echo PAGES_PATH; ?>orders.php"><i class="fas fa-box me-2"></i>Pesanan</a></li>
                        <li><a class="dropdown-item" href="<?php echo PAGES_PATH; ?>wishlist.php"><i class="fas fa-heart me-2"></i>Wishlist</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?php echo PAGES_PATH; ?>logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="<?php echo PAGES_PATH; ?>login.php" class="btn-primary-custom" style="padding:8px 20px;font-size:0.85rem;">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <a href="<?php echo PAGES_PATH; ?>register.php" class="btn-outline-custom" style="padding:8px 20px;font-size:0.85rem;color:var(--secondary);border-color:var(--gray-200);">
                    Daftar
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile toggle
    const toggle = document.getElementById('mobileToggle');
    const navLinks = document.getElementById('navLinks');
    if (toggle && navLinks) {
        toggle.addEventListener('click', function() {
            navLinks.classList.toggle('open');
        });
    }
    
    // Navbar scroll effect
    const nav = document.getElementById('mainNav');
    let lastScroll = 0;
    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;
        if (currentScroll > 50) {
            nav.classList.add('scrolled');
        } else {
            nav.classList.remove('scrolled');
        }
        lastScroll = currentScroll;
    });
});
</script>
