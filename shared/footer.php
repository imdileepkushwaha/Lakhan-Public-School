    <!-- Modern Premium Footer -->
    <footer class="modern-footer">
        <div class="footer-particles"></div>
        <div class="container relative-z">
            <div class="footer-top-grid">
                <!-- Column 1: Brand -->
                <div class="footer-widget brand-widget">
                    <div class="footer-logo-wrapper">
                        <img src="https://lakhanpublicschool.com/images/gallery/logomain.png" alt="Lakhan Public School Logo">
                        <h3 class="brand-text">Lakhan Public <span>School</span></h3>
                    </div>
                    <p class="brand-desc">Providing high-quality education that prepares all students to achieve their full potential and become the leaders of tomorrow.</p>
                    <div class="footer-socials">
                        <?php if(get_setting('social_facebook') && get_setting('social_facebook') !== '#'): ?>
                            <a href="<?= htmlspecialchars(get_setting('social_facebook')) ?>" aria-label="Facebook" target="_blank"><i class="fa-brands fa-facebook-f"></i></a>
                        <?php endif; ?>
                        
                        <?php if(get_setting('social_instagram') && get_setting('social_instagram') !== '#'): ?>
                            <a href="<?= htmlspecialchars(get_setting('social_instagram')) ?>" aria-label="Instagram" target="_blank"><i class="fa-brands fa-instagram"></i></a>
                        <?php endif; ?>
                        
                        <?php if(get_setting('social_twitter') && get_setting('social_twitter') !== '#'): ?>
                            <a href="<?= htmlspecialchars(get_setting('social_twitter')) ?>" aria-label="Twitter / X" target="_blank"><i class="fa-brands fa-twitter"></i></a>
                        <?php endif; ?>
                        
                        <?php if(get_setting('social_youtube') && get_setting('social_youtube') !== '#'): ?>
                            <a href="<?= htmlspecialchars(get_setting('social_youtube')) ?>" aria-label="YouTube" target="_blank"><i class="fa-brands fa-youtube"></i></a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Column 2: Quick Links -->
                <div class="footer-widget links-widget">
                    <h4 class="widget-title">Quick Links</h4>
                    <ul class="footer-link-list">
                        <li><a href="index.php"><i class="fa-solid fa-angle-right"></i> Home</a></li>
                        <li><a href="about.php"><i class="fa-solid fa-angle-right"></i> About Us</a></li>
                        <li><a href="gallery.php"><i class="fa-solid fa-angle-right"></i> School Gallery</a></li>
                        <li><a href="contact.php"><i class="fa-solid fa-angle-right"></i> Contact Us</a></li>
                    </ul>
                </div>

                <!-- Column 3: Academics -->
                <div class="footer-widget links-widget">
                    <h4 class="widget-title">Academics</h4>
                    <ul class="footer-link-list">
                        <li><a href="index.php#features"><i class="fa-solid fa-angle-right"></i> Salient Features</a></li>
                        <li><a href="index.php#rules"><i class="fa-solid fa-angle-right"></i> General Rules</a></li>
                        <li><a href="index.php#results"><i class="fa-solid fa-angle-right"></i> Outstanding Results</a></li>
                        <li><a href="#"><i class="fa-solid fa-angle-right"></i> Admission Process</a></li>
                    </ul>
                </div>

                <!-- Column 4: Contact -->
                <div class="footer-widget contact-widget">
                    <h4 class="widget-title">Get in Touch</h4>
                    <div class="contact-item">
                        <div class="c-icon"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="c-text"><?= nl2br(htmlspecialchars(get_setting('contact_address'))) ?></div>
                    </div>
                    <div class="contact-item">
                        <div class="c-icon"><i class="fa-solid fa-phone"></i></div>
                        <div class="c-text">
                            <a href="tel:<?= preg_replace('/[^0-9+]/', '', get_setting('contact_phone1')) ?>"><?= htmlspecialchars(get_setting('contact_phone1')) ?></a><br>
                            <?php if(get_setting('contact_landline')): ?>
                            <a href="tel:<?= preg_replace('/[^0-9+]/', '', get_setting('contact_landline')) ?>"><?= htmlspecialchars(get_setting('contact_landline')) ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="c-icon"><i class="fa-solid fa-envelope"></i></div>
                        <div class="c-text">
                            <a href="mailto:<?= htmlspecialchars(get_setting('contact_email')) ?>"><?= htmlspecialchars(get_setting('contact_email')) ?></a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="copyright">
                    &copy; 2026 <strong>Lakhan Public High School</strong>. All rights reserved.
                </div>
                <div class="footer-bottom-links">
                    <a href="#">Privacy Policy</a>
                    <span class="sep">|</span>
                    <a href="#">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>


    <?php
    $raw_phone = get_setting('contact_phone1');
    $wa_phone = preg_replace('/[^0-9]/', '', $raw_phone);
    if(strlen($wa_phone) == 10) $wa_phone = '91' . $wa_phone;
    if(!empty($raw_phone)):
    ?>
    <!-- Floating Call Button -->
    <a href="tel:<?= htmlspecialchars($raw_phone) ?>" class="floating-call" title="Call Us Now">
        <i class="fa-solid fa-phone"></i>
    </a>
    
    <!-- Floating WhatsApp Button -->
    <a href="https://wa.me/<?= $wa_phone ?>?text=Hello%20Lakhan%20Public%20School," target="_blank" class="floating-whatsapp" title="Chat with us on WhatsApp">
        <i class="fa-brands fa-whatsapp"></i>
    </a>
    <?php endif; ?>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <!-- Fancybox JS -->
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
    <!-- Custom JS -->
    <script src="script/script.js"></script>
</body>
</html>
