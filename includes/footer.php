<!-- ===== FOOTER PREMIUM ===== -->
<footer class="footer-premium">
    <div class="container">
        <div class="row">
            <!-- Brand -->
            <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                <div class="brand-footer">
                    <span>CHERY</span>
                    <span style="font-weight:300;color:rgba(255,255,255,0.3);">|</span>
                    <span style="font-weight:300;color:rgba(255,255,255,0.5);">OMODA</span>
                </div>
                <p class="footer-desc">
                    Dealer resmi Chery mobil di Indonesia. 
                    Menyediakan mobil berkualitas dengan harga terbaik 
                    dan layanan purna jual terpercaya.
                </p>
                <div class="social-links">
                    <a href="<?php echo getSetting('social_facebook') ?: '#'; ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <a href="<?php echo getSetting('social_instagram') ?: '#'; ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                    <a href="<?php echo getSetting('social_youtube') ?: '#'; ?>" target="_blank"><i class="fab fa-youtube"></i></a>
                    <a href="<?php echo getSetting('social_tiktok') ?: '#'; ?>" target="_blank"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>
            
            <!-- Links -->
            <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
                <h5>Tautan</h5>
                <ul>
                    <li><a href="<?php echo SITE_URL; ?>">Beranda</a></li>
                    <li><a href="<?php echo PAGES_PATH; ?>catalog.php">Katalog</a></li>
                    <li><a href="<?php echo PAGES_PATH; ?>about.php">Tentang Kami</a></li>
                    <li><a href="<?php echo PAGES_PATH; ?>contact.php">Kontak</a></li>
                </ul>
            </div>
            
            <!-- Support -->
            <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
                <h5>Layanan</h5>
                <ul>
                    <li><a href="<?php echo PAGES_PATH; ?>terms.php">Syarat & Ketentuan</a></li>
                    <li><a href="<?php echo PAGES_PATH; ?>privacy.php">Kebijakan Privasi</a></li>
                    <li><a href="<?php echo PAGES_PATH; ?>faq.php">FAQ</a></li>
                    <li><a href="<?php echo PAGES_PATH; ?>testimonial.php">Testimonial</a></li>
                </ul>
            </div>
            
            <!-- Contact -->
            <div class="col-lg-4 col-md-6">
                <h5>Hubungi Kami</h5>
                <ul class="contact-info">
                    <li>
                        <i class="fas fa-phone" style="color:var(--primary);width:20px;"></i>
                        <a href="tel:<?php echo getSetting('contact_phone') ?: WHATSAPP_NUMBER; ?>">
                            <?php echo getSetting('contact_phone') ?: WHATSAPP_NUMBER; ?>
                        </a>
                    </li>
                    <li>
                        <i class="fas fa-envelope" style="color:var(--primary);width:20px;"></i>
                        <a href="mailto:<?php echo getSetting('contact_email') ?: ADMIN_EMAIL; ?>">
                            <?php echo getSetting('contact_email') ?: ADMIN_EMAIL; ?>
                        </a>
                    </li>
                    <li>
                        <i class="fas fa-map-marker-alt" style="color:var(--primary);width:20px;"></i>
                        <span><?php echo getSetting('address') ?: 'Jl. Raya Mobil No. 123, Jakarta'; ?></span>
                    </li>
                    <li>
                        <i class="fab fa-whatsapp" style="color:#25D366;width:20px;"></i>
                        <a href="https://wa.me/<?php echo getSetting('whatsapp_number') ?: WHATSAPP_NUMBER; ?>" target="_blank">
                            WhatsApp
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo getSetting('site_name') ?: SITE_NAME; ?>. 
            All rights reserved. Powered by Chery Mobil Official.</p>
        </div>
    </div>
</footer>

<!-- ===== SCRIPTS ===== -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo ASSETS_PATH; ?>js/main.js"></script>
</body>
</html>
