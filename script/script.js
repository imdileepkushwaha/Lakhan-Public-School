document.addEventListener('DOMContentLoaded', () => {
    
    // --- Custom Cursor Logic ---
    const cursor = document.querySelector('.cursor');
    const follower = document.querySelector('.cursor-follower');
    
    let mouseX = 0, mouseY = 0;
    let cursorX = 0, cursorY = 0;
    let followerX = 0, followerY = 0;
    
    document.addEventListener('mousemove', (e) => {
        mouseX = e.clientX;
        mouseY = e.clientY;
        
        // Instant cursor update
        cursor.style.left = mouseX + 'px';
        cursor.style.top = mouseY + 'px';
    });
    
    // Smooth trailing effect for follower
    function animateCursor() {
        followerX += (mouseX - followerX) * 0.15;
        followerY += (mouseY - followerY) * 0.15;
        
        follower.style.left = followerX + 'px';
        follower.style.top = followerY + 'px';
        
        requestAnimationFrame(animateCursor);
    }
    animateCursor();
    
    // Cursor Hover Effect on links and buttons
    const interactiveElements = document.querySelectorAll('a, button, .feature-card, .stat-card');
    
    interactiveElements.forEach(el => {
        el.addEventListener('mouseenter', () => {
            cursor.classList.add('hovering');
            follower.classList.add('hovering');
            // Adding a school related emoji inside the cursor
            if (!cursor.innerHTML) {
                cursor.innerHTML = '<span style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); font-size:24px; color:white;">✏️</span>';
                cursor.style.backgroundColor = 'transparent';
            }
        });
        
        el.addEventListener('mouseleave', () => {
            cursor.classList.remove('hovering');
            follower.classList.remove('hovering');
            cursor.innerHTML = '';
            cursor.style.backgroundColor = 'var(--primary)';
        });
    });

    // --- Navbar Scroll Effect ---
    const navbar = document.querySelector('.navbar');
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // --- Mobile Menu Toggle ---
    const hamburger = document.querySelector('.hamburger');
    const navLinks = document.querySelector('.nav-links');
    const mobileOverlay = document.querySelector('.mobile-menu-overlay');
    
    hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('active');
        navLinks.classList.toggle('active');
        if (mobileOverlay) mobileOverlay.classList.toggle('active');
    });

    const mobileCloseBtn = document.querySelector('.mobile-close-btn');
    if (mobileCloseBtn) {
        mobileCloseBtn.addEventListener('click', () => {
            hamburger.classList.remove('active');
            navLinks.classList.remove('active');
            if (mobileOverlay) mobileOverlay.classList.remove('active');
        });
    }
    
    if (mobileOverlay) {
        mobileOverlay.addEventListener('click', () => {
            hamburger.classList.remove('active');
            navLinks.classList.remove('active');
            mobileOverlay.classList.remove('active');
        });
    }

    // Close mobile menu on link click
    document.querySelectorAll('.nav-link').forEach(n => n.addEventListener('click', () => {
        hamburger.classList.remove('active');
        navLinks.classList.remove('active');
        if (mobileOverlay) mobileOverlay.classList.remove('active');
    }));

    // --- Scroll Reveal Animations ---
    const reveals = document.querySelectorAll('.reveal');

    function revealOnScroll() {
        const windowHeight = window.innerHeight;
        const elementVisible = 100;
        
        reveals.forEach((reveal) => {
            const elementTop = reveal.getBoundingClientRect().top;
            
            if (elementTop < windowHeight - elementVisible) {
                reveal.classList.add('active');
            }
        });
    }

    // Trigger once on load
    revealOnScroll();
    
    // Trigger on scroll
    window.addEventListener('scroll', revealOnScroll);

    // --- Hero Swiper Slider ---
    if(document.querySelector('.heroSwiper')) {
        new Swiper('.heroSwiper', {
            loop: true,
            grabCursor: true,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            effect: 'slide',
        });
    }

    // --- Fancybox Initialization ---
    if(typeof Fancybox !== "undefined") {
        Fancybox.bind("[data-fancybox]", {
            // Options
        });
    }
});

/* =========================================
   Global Floating School Icons
   ========================================= */
document.addEventListener('DOMContentLoaded', function() {
    const emojis = ['📚', '🎓', '⚗️', '🌍', '✏️', '🔬', '🎨', '💻', '⚛️', '🔢', '🎒', '🚌'];
    const sections = document.querySelectorAll('section');
    
    sections.forEach(section => {
        // Skip hero section if it already has its own background styling/slider
        if(section.id === 'home' || section.classList.contains('hero')) return;
        
        if(getComputedStyle(section).position === 'static') {
            section.style.position = 'relative';
        }
        section.style.overflow = 'hidden';
        
        // Add 4-7 random icons per section to ensure visibility
        const numIcons = Math.floor(Math.random() * 4) + 4;
        for(let i=0; i<numIcons; i++) {
            const iconEl = document.createElement('span');
            const randomEmoji = emojis[Math.floor(Math.random() * emojis.length)];
            iconEl.className = 'section-floating-icon';
            iconEl.textContent = randomEmoji;
            
            // Randomize position, size, animation duration
            const size = Math.floor(Math.random() * 50) + 30; // 30px to 80px (emojis look bigger)
            const top = Math.floor(Math.random() * 90); // 0% to 90%
            const left = Math.floor(Math.random() * 90); // 0% to 90%
            const duration = Math.floor(Math.random() * 20) + 20; // 20s to 40s (slow float)
            const delay = Math.floor(Math.random() * 10);
            
            iconEl.style.fontSize = size + 'px';
            iconEl.style.top = top + '%';
            iconEl.style.left = left + '%';
            iconEl.style.animationDuration = duration + 's';
            iconEl.style.animationDelay = '-' + delay + 's';
            
            // Some icons float in reverse direction for variety
            if (Math.random() > 0.5) {
                iconEl.style.animationDirection = 'reverse';
            }
            
            section.appendChild(iconEl);
        }
    });
});

/* =========================================
   Captcha Reload Function
   ========================================= */
function reloadCaptcha(btn) {
    // Add spinning animation to the icon
    btn.classList.add('fa-spin');
    const form = btn.closest('form');
    
    fetch('ajax_captcha.php')
        .then(response => response.json())
        .then(data => {
            if(data && data.hash) {
                form.querySelector('.captcha-question').textContent = data.num1 + ' + ' + data.num2;
                form.querySelector('.captcha_hash').value = data.hash;
                form.querySelector('.captcha_input').value = '';
            }
        })
        .catch(error => console.error('Error reloading captcha:', error))
        .finally(() => {
            // Remove spinning animation
            setTimeout(() => {
                btn.classList.remove('fa-spin');
            }, 500); // 500ms delay to make it feel smooth
        });
}
