<?php 
require_once 'config/db.php';
include 'shared/header.php'; 
?>

    <!-- Hero Section -->
    <section id="home" class="hero full-width-hero">
        <!-- Background Slider -->
        <div class="swiper heroSwiper hero-bg-slider">
            <div class="swiper-wrapper">
                <div class="swiper-slide"><img src="images/main-slider/image-4.jpg" alt="Lakhan Public School" class="hero-img"></div>
                <div class="swiper-slide"><img src="images/main-slider/image-1.jpg" alt="Lakhan Public School" class="hero-img"></div>
                <div class="swiper-slide"><img src="images/main-slider/image-2.jpg" alt="Lakhan Public School" class="hero-img"></div>
                <div class="swiper-slide"><img src="images/main-slider/image-3.jpg" alt="Lakhan Public School" class="hero-img"></div>
            </div>
            
            <!-- Navigation Arrows -->
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            
            <!-- Overlay -->
            <div class="hero-overlay"></div>
        </div>

        <!-- Abstract Shapes -->
        <div class="hero-shape shape-1"></div>
        <div class="hero-shape shape-2"></div>
        
        <div class="container hero-container centered-hero">
            <div class="hero-content reveal">
                <div class="section-badge badge-light">🌟 Empowering Future Generations</div>
                <h1 class="hero-title text-white">Welcome to <br> <span class="highlight text-white-glow">Lakhan Public</span> Highschool</h1>
                <p class="hero-subtitle text-light">Today is the day to learn something new. Get the best education with our modern approach to holistic learning.</p>
                <div class="hero-buttons">
                    <a href="#contact" class="btn btn-primary">Enroll Now</a>
                    <a href="#about" class="btn btn-secondary btn-light-outline">Read More</a>
                </div>
                <div class="hero-stats-mini">
                    <div class="h-stat stat-glass">
                        <strong>500+</strong><span>Students</span>
                    </div>
                    <div class="h-stat stat-glass">
                        <strong>50+</strong><span>Teachers</span>
                    </div>
                    <div class="h-stat stat-glass">
                        <strong>#1</strong><span>in Telibagh</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container about-container">
            <?php
            // Fetch announcements
            $stmt = $pdo->query("SELECT * FROM announcements WHERE status = 'active' ORDER BY created_at DESC");
            $announcements = $stmt->fetchAll();
            ?>
            <div class="announcements-section reveal">
                <div class="announcements-header">
                    <h3><i class="fa-solid fa-bullhorn"></i> Latest Announcements</h3>
                </div>
                <div class="announcements-list">
                    <?php if(empty($announcements)): ?>
                        <div class="empty-announcements">No new announcements at this time.</div>
                    <?php else: ?>
                        <marquee direction="up" scrollamount="4" onmouseover="this.stop();" onmouseout="this.start();" height="100%">
                            <div class="announcements-ticker">
                                <?php foreach($announcements as $ann): ?>
                                <div class="announcement-card">
                                    <div class="ann-content">
                                        <div class="ann-date"><i class="fa-regular fa-calendar-alt"></i> <?= date('d M Y', strtotime($ann['created_at'])) ?></div>
                                        <h4 class="ann-title"><?= htmlspecialchars($ann['title']) ?></h4>
                                        <p class="ann-desc"><?= nl2br(htmlspecialchars($ann['description'])) ?></p>
                                    </div>
                                    <?php if($ann['attachment_filename']): ?>
                                    <div class="ann-action">
                                        <a href="uploads/announcements/<?= htmlspecialchars($ann['attachment_filename']) ?>" target="_blank" class="ann-attachment">
                                            <i class="fa-solid fa-paperclip"></i> View Attachment
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </marquee>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="about-text reveal" style="transition-delay: 0.2s;">
                <div class="section-badge">About Us</div>
                <h2 class="section-title">Nurturing the  Leaders of Tomorrow</h2>
                <p class="section-desc">We aim at providing an environment which motivates children to become confident and well versed in their respective fields. We lay the foundation for right habits, attitude and education.</p>
                
                <ul class="about-features">
                    <li>
                        <div class="check-icon">✓</div>
                        <div class="feature-text"><strong>Holistic Development</strong>  Focusing on both mental and physical fitness.</div>
                    </li>
                    <li>
                        <div class="check-icon">✓</div>
                        <div class="feature-text"><strong>Expert Teachers</strong>  A team of highly qualified and experienced professionals.</div>
                    </li>
                    <li>
                        <div class="check-icon">✓</div>
                        <div class="feature-text"><strong>Interactive Learning</strong>  Encouraging children to become keen observers and explorers.</div>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-redesign section-padding bg-white">
        <div class="container">
            <div class="section-header reveal header-center">
                <div class="section-badge badge-center">Why Choose Us</div>
                <h2 class="section-title">Salient Features</h2>
                <p class="section-desc mt-3">Every child has to follow all the disciplinary rules of the institution to maintain the school decorum.</p>
            </div>
            
            <div class="features-list-grid">
                <!-- Feature 1 -->
                <div class="feature-item reveal">
                    <div class="f-icon">
                        <i class="fa-solid fa-face-smile"></i>
                    </div>
                    <div class="f-content">
                        <h3>Expert Teachers</h3>
                        <p>We have a team of child education professionals, each with more than a decade of experience.</p>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="feature-item reveal" style="transition-delay: 0.1s;">
                    <div class="f-icon">
                        <i class="fa-solid fa-calculator"></i>
                    </div>
                    <div class="f-content">
                        <h3>Active Learning</h3>
                        <p>If you want your child to catch up or get ahead, give Luckhan Public Highschool a call!</p>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="feature-item reveal" style="transition-delay: 0.2s;">
                    <div class="f-icon">
                        <i class="fa-solid fa-language"></i>
                    </div>
                    <div class="f-content">
                        <h3>English Medium</h3>
                        <p>English is the primary language of communication on campus, with special focus on speaking skills.</p>
                    </div>
                </div>

                <!-- Feature 4 -->
                <div class="feature-item reveal" style="transition-delay: 0.3s;">
                    <div class="f-icon">
                        <i class="fa-solid fa-dumbbell"></i>
                    </div>
                    <div class="f-content">
                        <h3>Fullday Programs</h3>
                        <p>To provide a high-quality education that prepares all students to achieve their full potential</p>
                    </div>
                </div>

                <!-- Feature 5 -->
                <div class="feature-item reveal" style="transition-delay: 0.4s;">
                    <div class="f-icon">
                        <i class="fa-solid fa-seedling"></i>
                    </div>
                    <div class="f-content">
                        <h3>Clear Approach</h3>
                        <p>All children can reach their learning potential and they can achieve everything.</p>
                    </div>
                </div>

                <!-- Feature 6 -->
                <div class="feature-item reveal" style="transition-delay: 0.5s;">
                    <div class="f-icon">
                        <i class="fa-solid fa-hands-holding-child"></i>
                    </div>
                    <div class="f-content">
                        <h3>Social Upliftment</h3>
                        <p>We focus on upliftment of marginalized and less privileged students.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Results Section -->
    <section id="results" class="results section-padding bg-light">
        <div class="container">
            <div class="section-header reveal header-center">
                <div class="section-badge badge-center">Our Pride</div>
                <h2 class="section-title">Outstanding Results</h2>
                <p class="section-desc mt-3">We are proud of our top-performing students who have excelled in their academics and brought glory to the school.</p>
            </div>
            
            <?php
            $stmt_res = $pdo->query("SELECT * FROM student_results WHERE is_visible = 1 ORDER BY created_at DESC LIMIT 8");
            $student_results = $stmt_res->fetchAll();
            ?>
            <div class="result-grid">
                <?php if (empty($student_results)): ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: rgba(0,0,0,0.02); border-radius: 16px;">
                        <i class="fa-solid fa-trophy" style="font-size: 3rem; color: #e2e8f0; margin-bottom: 15px; display: block;"></i>
                        <h3 style="color: #64748b;">Results will be updated soon!</h3>
                    </div>
                <?php else: ?>
                    <?php $delay = 0; foreach($student_results as $s_res): ?>
                        <div class="result-poster reveal" style="transition-delay: <?= $delay ?>s;">
                            <div class="poster-congrats">
                                <span class="cursive">Congratulations</span>
                                <span class="block">TO OUR TOPPER</span>
                            </div>
                            <div class="poster-avatar-wrapper">
                                <div class="poster-avatar">
                                    <?php if($s_res['image_path'] && file_exists('images/results/' . $s_res['image_path'])): ?>
                                        <img src="images/results/<?= htmlspecialchars($s_res['image_path']) ?>" alt="<?= htmlspecialchars($s_res['student_name']) ?>">
                                    <?php else: ?>
                                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($s_res['student_name']) ?>&background=D81B60&color=fff&size=150" alt="Avatar">
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="poster-ribbon">
                                <?= strtoupper(htmlspecialchars($s_res['student_name'])) ?>
                            </div>
                            <div class="poster-score-box">
                                <span class="class-label"><?= htmlspecialchars($s_res['class_name']) ?></span>
                                <span class="score-dash">-</span>
                                <span class="score-percent"><?= htmlspecialchars($s_res['percentage']) ?></span>
                            </div>
                        </div>
                    <?php $delay += 0.1; endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- General Rules Section -->
    <section id="rules" class="features bg-white">
        <div class="container">
            <div class="section-header reveal">
                <div class="section-badge">Guidelines</div>
                <h2 class="section-title">General Rules</h2>
            </div>
            
            <div class="features-grid">
                <div class="feature-card glass-panel reveal">
                    <div class="feature-icon icon-blue"><i class="fa-solid fa-clock"></i></div>
                    <h3>Punctuality</h3>
                    <p>Punctual and regular attendance is strictly insisted upon. Students must arrive at school before the morning assembly.</p>
                </div>
                <div class="feature-card glass-panel reveal" style="transition-delay: 0.1s;">
                    <div class="feature-icon icon-blue"><i class="fa-solid fa-shirt"></i></div>
                    <h3>Uniform Code</h3>
                    <p>Students must wear the prescribed clean and neat school uniform daily. Strict action will be taken for non-compliance.</p>
                </div>
                <div class="feature-card glass-panel reveal" style="transition-delay: 0.2s;">
                    <div class="feature-icon icon-blue"><i class="fa-solid fa-scale-balanced"></i></div>
                    <h3>Discipline</h3>
                    <p>Every student must possess willingness to comply with school rules and maintain decorum within the campus.</p>
                </div>
                <div class="feature-card glass-panel reveal" style="transition-delay: 0.3s;">
                    <div class="feature-icon icon-blue"><i class="fa-solid fa-book-open"></i></div>
                    <h3>Assignments</h3>
                    <p>Earnestness in home assignments and projects is required. Parents must monitor their child's daily progress.</p>
                </div>
            </div>
        </div>
    </section>

    

    <!-- Gallery Section -->
    <section id="gallery" class="gallery section-padding bg-light">
        <div class="container">
            <div class="section-header header-split">
                <div class="reveal">
                    <div class="section-badge badge-left">Our Gallery</div>
                    <h2 class="section-title">School Memories</h2>
                </div>
                <div class="reveal">
                    <a href="gallery.php" class="btn btn-secondary">View All <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>
            
            <div class="gallery-grid">
                <?php
                $stmt = $pdo->query("SELECT * FROM gallery ORDER BY created_at DESC LIMIT 4");
                $latest_images = $stmt->fetchAll();
                
                if (!empty($latest_images)):
                    $delay = 0.1;
                    foreach ($latest_images as $img):
                ?>
                <a href="images/gallery/<?= htmlspecialchars($img['image_filename']) ?>" data-fancybox="gallery" class="gallery-item reveal" style="transition-delay: <?= $delay ?>s;">
                    <img src="images/gallery/<?= htmlspecialchars($img['image_filename']) ?>" alt="<?= htmlspecialchars($img['title']) ?>">
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.6); color: white; padding: 10px; font-size: 0.9rem;">
                        <?= htmlspecialchars($img['title']) ?>
                    </div>
                </a>
                <?php
                    $delay += 0.1;
                    endforeach;
                else:
                ?>
                <p style="grid-column: 1/-1; text-align: center; color: #888;">Gallery coming soon...</p>
                <?php endif; ?>
            </div>
        </div>
    </section>


    <!-- Call to Action Banner -->
    <section class="cta-banner">
        <div class="cta-bg"></div>
        <div class="container cta-container reveal">
            <div class="cta-text">
                <h2>Enroll your Child to a Class</h2>
                <p>We will provide the perfect education for your child every day. Join our community and watch your child grow.</p>
            </div>
            <div class="cta-btn-wrapper">
                <a href="#contact" class="btn btn-primary cta-btn">Join Now</a>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container contact-container">
            <div class="contact-info reveal">
                <div class="section-badge">Contact Us</div>
                <h2 class="section-title">Keep in touch</h2>
                <p class="section-desc">Welcome to Lakhan Public High School. For any queries, please feel free to reach out to us using the details below.</p>
                
                <ul class="contact-list">
                    <li>
                        <div class="contact-icon">📍</div>
                        <div class="contact-detail">
                            <h4>Address</h4>
                            <p><?= nl2br(htmlspecialchars(get_setting('contact_address'))) ?></p>
                        </div>
                    </li>
                    <li>
                        <div class="contact-icon">📞</div>
                        <div class="contact-detail">
                            <h4>Phone</h4>
                            <p>
                                <?= htmlspecialchars(get_setting('contact_phone1')) ?><br>
                                <?php if(get_setting('contact_phone2')): ?>
                                    <?= htmlspecialchars(get_setting('contact_phone2')) ?><br>
                                <?php endif; ?>
                                <?php if(get_setting('contact_landline')): ?>
                                    <?= htmlspecialchars(get_setting('contact_landline')) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </li>
                    <li>
                        <div class="contact-icon">✉️</div>
                        <div class="contact-detail">
                            <h4>Email</h4>
                            <p><?= htmlspecialchars(get_setting('contact_email')) ?></p>
                        </div>
                    </li>
                </ul>
            </div>
            
            <div class="contact-form-wrapper glass-panel reveal">
                <?php if (isset($_GET['success'])): ?>
                    <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: 600;">
                        Thank you for contacting us! We will get back to you soon.
                    </div>
                <?php elseif (isset($_GET['error'])): ?>
                    <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-weight: 600;">
                        <?php 
                        if($_GET['error'] == 'captcha') echo "Incorrect Math Captcha answer. Please try again.";
                        elseif($_GET['error'] == 'phone') echo "Please enter a valid 10-digit mobile number.";
                        elseif($_GET['error'] == 'email') echo "Please enter a valid email address.";
                        elseif($_GET['error'] == 'empty') echo "Please fill all the required fields.";
                        else echo "Error submitting your enquiry. Please try again later.";
                        ?>
                    </div>
                <?php endif; ?>

                <?php
                    $num1 = rand(1, 9);
                    $num2 = rand(1, 9);
                    $captcha_hash = md5(($num1 + $num2) . 'LPS_SECRET_SALT');
                ?>
                <form class="contact-form" action="process_enquiry.php" method="POST">
                    <div class="form-group">
                        <input type="text" id="name" name="name" required placeholder=" ">
                        <label for="name">Full Name</label>
                    </div>
                    <div class="form-group">
                        <input type="email" id="email" name="email" required placeholder=" ">
                        <label for="email">Email Address</label>
                    </div>
                    <div class="form-group">
                        <input type="tel" id="phone" name="phone" required pattern="[0-9]{10}" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);" title="Please enter a valid 10-digit mobile number" placeholder=" ">
                        <label for="phone">Mobile Number</label>
                    </div>
                    <div class="form-group">
                        <textarea id="message" name="message" placeholder=" " rows="3"></textarea>
                        <label for="message">Your Message (Optional)</label>
                    </div>
                    <div class="form-group" style="display: flex; gap: 15px; align-items: center;">
                        <span style="font-weight: 700; background: var(--bg-dark); padding: 14px 20px; border-radius: 8px; border: 1px solid #e2e8f0; color: var(--primary); font-size: 1.1rem; white-space: nowrap;">
                            <span class="captcha-question"><?= $num1 ?> + <?= $num2 ?></span> = ?
                            <i class="fa-solid fa-rotate-right captcha-reload" style="cursor: pointer; margin-left: 10px; color: #5a5a5a;" onclick="reloadCaptcha(this)" title="Reload Captcha"></i>
                        </span>
                        <div style="flex: 1; position: relative; margin-bottom: 0;">
                            <input type="number" id="captcha" name="captcha" class="captcha_input" required placeholder=" ">
                            <label for="captcha">Answer</label>
                        </div>
                        <input type="hidden" name="captcha_hash" class="captcha_hash" value="<?= $captcha_hash ?>">
                        <input type="hidden" name="source" value="index.php">
                    </div>
                    <button type="submit" class="btn btn-primary form-submit">Send Message</button>
                </form>
            </div>
        </div>
    </section>

<?php
    $popup_active = get_setting('popup_active');
    $popup_image = get_setting('popup_image');
    if ($popup_active == '1' && !empty($popup_image)): 
?>
    <!-- Promotional Popup -->
    <div id="promoPopup" class="promo-popup-overlay">
        <div class="promo-popup-content">
            <button class="promo-popup-close" id="closePromoPopup"><i class="fa-solid fa-xmark"></i></button>
            <img src="images/popup/<?= htmlspecialchars($popup_image) ?>" alt="School Promotion">
        </div>
    </div>

  

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Show popup on every refresh after 1.5 seconds
            setTimeout(function() {
                document.getElementById('promoPopup').classList.add('show');
            }, 1500);

            // Close button click
            document.getElementById('closePromoPopup').addEventListener('click', function() {
                document.getElementById('promoPopup').classList.remove('show');
            });

            // Click outside to close
            document.getElementById('promoPopup').addEventListener('click', function(e) {
                if (e.target === this) {
                    document.getElementById('promoPopup').classList.remove('show');
                }
            });
        });
    </script>
<?php endif; ?>

<?php include 'shared/footer.php'; ?>
