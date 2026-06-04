<?php require_once __DIR__ . '/get_settings.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lakhan Public Highschool | Modern Education</title>
    <meta name="description" content="Welcome to Lakhan Public High School. To get best education please visit on Lakhan Public Highschool.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <!-- Fancybox CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Preloader -->
    <div id="preloader">
        <div class="loader-content">
            <div class="book-loader">
                <div class="book-page book-left-page"></div>
                <div class="book-page book-right-page"></div>
                <div class="book-page book-flipping-page book-flip-1"></div>
                <div class="book-page book-flipping-page book-flip-2"></div>
                <div class="book-page book-flipping-page book-flip-3"></div>
            </div>
            <div class="loader-text">Loading LPS...</div>
        </div>
    </div>
    <!-- Custom Cursor -->
    <div class="cursor"></div>
    <div class="cursor-follower"></div>

    <!-- Main Header -->
    <header class="main-header">
        <!-- Topbar -->
        <div class="topbar">
            <div class="container topbar-container">
                <div class="topbar-left">
                    <a href="mailto:<?= htmlspecialchars(get_setting('contact_email')) ?>"><i class="fa-regular fa-envelope"></i> <?= htmlspecialchars(get_setting('contact_email')) ?></a>
                    <a href="tel:<?= preg_replace('/[^0-9+]/', '', get_setting('contact_phone1')) ?>"><i class="fa-solid fa-phone-volume"></i> <?= htmlspecialchars(get_setting('contact_phone1')) ?></a>
                </div>
                <div class="topbar-right">
                    <span class="follow-text">Follow Us On:</span>
                    <div class="topbar-social">
                        <?php if(get_setting('social_facebook') && get_setting('social_facebook') !== '#'): ?>
                            <a href="<?= htmlspecialchars(get_setting('social_facebook')) ?>" target="_blank"><i class="fa-brands fa-facebook-f"></i></a>
                        <?php endif; ?>
                        
                        <?php if(get_setting('social_twitter') && get_setting('social_twitter') !== '#'): ?>
                            <a href="<?= htmlspecialchars(get_setting('social_twitter')) ?>" target="_blank"><i class="fa-brands fa-twitter"></i></a>
                        <?php endif; ?>
                        
                        <?php if(get_setting('social_instagram') && get_setting('social_instagram') !== '#'): ?>
                            <a href="<?= htmlspecialchars(get_setting('social_instagram')) ?>" target="_blank"><i class="fa-brands fa-instagram"></i></a>
                        <?php endif; ?>
                        
                        <?php if(get_setting('social_youtube') && get_setting('social_youtube') !== '#'): ?>
                            <a href="<?= htmlspecialchars(get_setting('social_youtube')) ?>" target="_blank"><i class="fa-brands fa-youtube"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="navbar">
            <div class="mobile-menu-overlay"></div>
            <div class="container nav-container">
                <div class="logo">
                    <a href="index.php">
                        <img src="<?= htmlspecialchars(get_site_logo_url()) ?>" alt="Lakhan Public School Logo" class="logo-img">
                    </a>
                </div>
                
                <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
                <ul class="nav-links">
                    <div class="mobile-menu-header">
                        <img src="<?= htmlspecialchars(get_site_logo_url()) ?>" alt="Lakhan Public School">
                        <div class="mobile-close-btn"><i class="fa-solid fa-xmark"></i></div>
                    </div>
                    
                    <li><a href="index.php" class="nav-link <?= ($current_page == 'index.php' || $current_page == '') ? 'active' : '' ?>">Home </a></li>
                    <li><a href="about.php" class="nav-link <?= ($current_page == 'about.php') ? 'active' : '' ?>">About</a></li>
                    <li><a href="gallery.php" class="nav-link <?= ($current_page == 'gallery.php') ? 'active' : '' ?>">Gallery </a></li>
                    <li><a href="contact.php" class="nav-link <?= ($current_page == 'contact.php') ? 'active' : '' ?>">Contact</a></li>
                    
                    <div class="mobile-menu-footer">
                        <a href="tel:<?= preg_replace('/[^0-9+]/', '', get_setting('contact_phone1')) ?>"><i class="fa-solid fa-phone"></i> <?= htmlspecialchars(get_setting('contact_phone1')) ?></a>
                        <a href="mailto:<?= htmlspecialchars(get_setting('contact_email')) ?>"><i class="fa-regular fa-envelope"></i> <?= htmlspecialchars(get_setting('contact_email')) ?></a>
                    </div>
                </ul>
                
                <div class="nav-right">
                    <a href="index.php#contact" class="btn btn-primary cta-btn">Enroll Now <i class="fa-solid fa-arrow-right"></i></a>
                    <div class="hamburger">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        </nav>
    </header>
