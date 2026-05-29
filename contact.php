<?php include 'shared/header.php'; ?>

<!-- Page Title -->
<section class="page-title-section">
    <div class="container">
        <h1>Contact Us</h1>
        <p>We'd love to hear from you. Get in touch with us.</p>
    </div>
</section>

<!-- Contact Section (Reused styling from index) -->
<section class="contact contact-section-padding bg-white">
    <div class="container contact-container">
        <div class="contact-info reveal">
            <div class="section-badge badge-left">Keep in touch</div>
            <h2 class="section-title contact-title-margin">Get In Touch</h2>
            <p class="section-desc">Welcome to Lakhan Public High School. For any queries, please feel free to reach out to us using the details below.</p>
            
            <ul class="contact-list contact-list-margin">
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
        
        <div class="contact-form-wrapper glass-panel reveal contact-form-box">
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
                    <input type="hidden" name="source" value="contact.php">
                </div>
                <button type="submit" class="btn btn-primary form-submit btn-full-width">Send Message</button>
            </form>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="map-section reveal map-section-padding">
    <div class="container">
        <div class="map-wrapper">
            <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d14247.775190278531!2d80.9383007!3d26.7780614!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0xbf88204fee0b9665!2sLakhan+Public+High+School!5e0!3m2!1sen!2sin!4v1519338147694" width="100%" height="450" frameborder="0" style="border:0" allowfullscreen></iframe>
        </div>
    </div>
</section>

<?php include 'shared/footer.php'; ?>
