<?php
require_once "./layouts/header.php";
?>

<!-- ===== CONTACT HERO SECTION ===== -->
<section class="contact-hero-section">
    <div class="contact-hero-overlay">
        <div class="container-fluid px-4 h-100">
            <div class="row h-100 align-items-end pb-5">
                <div class="col-12">
                    <p class="root-navigation mb-1">
                        <a href="index.php" style="color:var(--primary);text-decoration:none;">Home</a>
                        <span class="mx-1">&rsaquo;</span>
                        <span style="color:var(--accent);">Contact Us</span>
                    </p>
                    <h1 class="contact-hero-title">
                        Let's Build Something<br>
                        <span class="contact-hero-title-accent">Beautiful Together.</span>
                    </h1>
                    <p class="contact-hero-desc">
                        Have a question, need expert advice, or looking for a custom solution?<br>
                        Our team is here to help you furnish your space with the best.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== CONTACT CARDS ROW ===== -->
<section class="contact-cards-section section-gap bg-light-surface">
    <div class="container-fluid px-4">
        <div class="row g-4">

            <!-- Call Us -->
            <div class="col-lg-3 col-md-6">
                <div class="contact-method-card">
                    <div class="contact-method-icon">
                        <i class="bi bi-telephone-fill"></i>
                    </div>
                    <h5 class="contact-method-title">Call Us</h5>
                    <p class="contact-method-desc">Speak to our furniture expert directly.</p>
                    <a href="tel:18001234567" class="contact-method-value">1800 123 4567</a>
                    <p class="contact-method-hours">Mon – Sat: 9:00 AM – 7:00 PM</p>
                </div>
            </div>

            <!-- WhatsApp Us -->
            <div class="col-lg-3 col-md-6">
                <div class="contact-method-card">
                    <div class="contact-method-icon contact-method-icon-wa">
                        <i class="bi bi-whatsapp"></i>
                    </div>
                    <h5 class="contact-method-title">WhatsApp Us</h5>
                    <p class="contact-method-desc">Share your requirements and get quick answers.</p>
                    <a href="https://wa.me/919876543210" class="contact-method-value">+91 98765 43210</a>
                    <p class="contact-method-hours">Reply within 15 mins</p>
                </div>
            </div>

            <!-- Email Us -->
            <div class="col-lg-3 col-md-6">
                <div class="contact-method-card">
                    <div class="contact-method-icon contact-method-icon-email">
                        <i class="bi bi-envelope-fill"></i>
                    </div>
                    <h5 class="contact-method-title">Email Us</h5>
                    <p class="contact-method-desc">Drop us an email anytime, we'll get back to you.</p>
                    <a href="mailto:info@furnituremarket.in" class="contact-method-value">info@furnituremarket.in</a>
                    <p class="contact-method-hours">We reply within 24 hrs</p>
                </div>
            </div>

            <!-- Visit Our Showroom -->
            <div class="col-lg-3 col-md-6">
                <div class="contact-method-card">
                    <div class="contact-method-icon contact-method-icon-map">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <h5 class="contact-method-title">Visit Our Showroom</h5>
                    <p class="contact-method-desc">Explore our collections in person.</p>
                    <a href="#map-section" class="contact-method-value">View Address &rsaquo;</a>
                    <p class="contact-method-hours">Get directions on map</p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ===== INQUIRY FORM + CUSTOM FURNITURE ===== -->
<section class="section-gap" style="background:var(--bg);">
    <div class="container-fluid px-4">
        <div class="row g-5">

            <!-- LEFT: Send Us an Inquiry -->
            <div class="col-lg-6">
                <div class="inquiry-form-wrap">
                    <h2 class="section-title mb-1">Send Us an Inquiry</h2>
                    <p class="section-subtitle mb-4">Fill in the details below and our team will contact you shortly.</p>

                    <form class="inquiry-form" action="#" method="post" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control inquiry-input" name="name" placeholder="Your Name*" required>
                            </div>
                            <div class="col-md-6">
                                <input type="tel" class="form-control inquiry-input" name="phone" placeholder="Phone Number*" required>
                            </div>
                            <div class="col-md-6">
                                <input type="email" class="form-control inquiry-input" name="email" placeholder="Email Address*" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control inquiry-input" name="city" placeholder="City*">
                            </div>
                            <div class="col-12">
                                <select class="form-select inquiry-input" name="subject">
                                    <option value="" disabled selected>Subject</option>
                                    <option>General Inquiry</option>
                                    <option>Custom Furniture</option>
                                    <option>Bulk Order</option>
                                    <option>Office Furniture</option>
                                    <option>Home Furniture</option>
                                    <option>Restaurant &amp; Cafe</option>
                                    <option>Outdoor Furniture</option>
                                    <option>After Sales Support</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <textarea class="form-control inquiry-input" name="message" rows="5" placeholder="Your Message / Requirements*&#10;Tell us about your requirements, space, budget, etc."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="inquiry-upload-label">
                                    <i class="bi bi-upload me-2"></i>
                                    <span>Click to upload or drag and drop</span><br>
                                    <small>JPG, PNG, PDF up to 10MB</small>
                                    <input type="file" name="reference" class="d-none" accept=".jpg,.jpeg,.png,.pdf">
                                </label>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-accent w-100 py-3">
                                    Send Inquiry &nbsp;<i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- RIGHT: Custom Furniture -->
            <div class="col-lg-6">
                <div class="custom-furniture-wrap">
                    <p class="section-label mb-1">TAILORED FOR YOU</p>
                    <h2 class="section-title mb-1">Looking for<br><span style="color:var(--accent);">Custom Furniture?</span></h2>
                    <p class="section-subtitle mb-4">We create furniture tailored to your space, style and requirements.</p>

                    <ul class="custom-check-list mb-4">
                        <li><i class="bi bi-check-circle-fill"></i> Custom sizes &amp; designs</li>
                        <li><i class="bi bi-check-circle-fill"></i> Wide range of materials &amp; finishes</li>
                        <li><i class="bi bi-check-circle-fill"></i> Perfect for homes, offices, restaurants &amp; more</li>
                        <li><i class="bi bi-check-circle-fill"></i> Expert guidance from our team</li>
                    </ul>

                    <div class="custom-furniture-img mb-4">
                        <img src="https://images.unsplash.com/photo-1617806118233-18e1de247200?w=700&h=300&fit=crop" alt="Custom Furniture" class="w-100" style="border-radius:var(--radius);object-fit:cover;height:240px;">
                    </div>

                    <a href="#inquiry-form" class="btn btn-outline-fm">
                        Request Custom Design &nbsp;<i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ===== WHY CHOOSE US ===== -->
<section class="section-gap bg-light-surface">
    <div class="container-fluid px-4">
        <div class="section-header-centered mb-5">
            <h2 class="section-title">Why Choose Us?</h2>
        </div>
        <div class="row g-4">
            <?php
            $whyChoose = [
                ['bi-building',         'Factory Direct Pricing',      'Best prices guaranteed'],
                ['bi-gem',              'Premium Quality Materials',    'Built to last'],
                ['bi-tools',            'Custom Solutions',             'Made for your space'],
                ['bi-truck',            'Pan India Delivery',           'Safe &amp; on-time delivery'],
                ['bi-box-seam',         'Bulk Orders Welcome',          'Best deals for large requirements'],
            ];
            foreach ($whyChoose as $w): ?>
            <div class="col-lg col-md-4 col-6">
                <div class="why-choose-card text-center">
                    <div class="why-choose-icon mb-3">
                        <i class="bi <?= $w[0] ?>"></i>
                    </div>
                    <h6 class="why-choose-title"><?= $w[1] ?></h6>
                    <p class="why-choose-desc"><?= $w[2] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== OUR ORDER PROCESS ===== -->
<section class="section-gap" style="background:var(--bg);">
    <div class="container-fluid px-4">
        <div class="section-header-centered mb-5">
            <h2 class="section-title">Our Order Process</h2>
        </div>
        <div class="row g-0 justify-content-center">
            <?php
            $steps = [
                ['bi-chat-dots',            'Share Your<br>Requirement'],
                ['bi-clipboard-check',       'Discuss &amp;<br>Finalise Details'],
                ['bi-file-earmark-text',     'Receive<br>Quotation'],
                ['bi-gear',                  'Production<br>Starts'],
                ['bi-eye',                   'Quality<br>Inspection'],
                ['bi-box-arrow-right',       'Safe Delivery<br>&amp; Installation'],
            ];
            foreach ($steps as $i => $step): ?>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="order-step-card text-center">
                    <div class="order-step-icon">
                        <i class="bi <?= $step[0] ?>"></i>
                    </div>
                    <?php if ($i < count($steps) - 1): ?>
                    <div class="order-step-arrow d-none d-lg-block"><i class="bi bi-arrow-right"></i></div>
                    <?php endif; ?>
                    <p class="order-step-label mt-3"><?= $step[1] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== CONTACT PAGE FAQ ===== -->
<section class="section-gap bg-light-surface">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between mb-5 flex-wrap gap-3">
            <h2 class="section-title mb-0">Frequently Asked Questions</h2>
            <a href="#" class="contact-view-all-link">View All FAQs &nbsp;<i class="bi bi-arrow-right"></i></a>
        </div>

        <div class="row g-3">
            <?php
            $contactFaqs = [
                ['Can I customize the furniture size and design?',   'Yes, we offer full customization in size, material, finish, and fabric. Share your measurements and preferences and our design team will assist you.'],
                ['What is the minimum order quantity?',               'There is no minimum order quantity for retail customers. For bulk/project orders, special pricing is available from 5 units.'],
                ['Do you deliver across India?',                      'Yes, we deliver PAN India through our trusted logistics network with real-time tracking and installation support.'],
                ['Do you provide installation service?',              'Yes, professional installation is included for all furniture items at no extra cost in major cities. Please confirm availability at checkout.'],
                ['What is the production time?',                      'Custom furniture typically takes 15–25 working days. Standard in-stock items ship within 3–7 working days.'],
                ['What kind of warranty do you offer?',               'All products carry a minimum 1-year warranty. Premium collections come with up to 5 years coverage against manufacturing defects.'],
            ];
            $faqCount = 0;
            foreach ($contactFaqs as $faq):
                $faqCount++;
            ?>
            <div class="col-md-6">
                <div class="accordion-item contact-faq-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button faq-btn collapsed" type="button"
                            data-bs-toggle="collapse" data-bs-target="#cfaq<?= $faqCount ?>">
                            <?= htmlspecialchars($faq[0]) ?>
                        </button>
                    </h2>
                    <div id="cfaq<?= $faqCount ?>" class="accordion-collapse collapse">
                        <div class="accordion-body faq-answer">
                            <?= htmlspecialchars($faq[1]) ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== MAP + BUSINESS INFO ===== -->
<section class="section-gap" id="map-section" style="background:var(--bg);">
    <div class="container-fluid px-4">
        <div class="row g-5 align-items-stretch">

            <!-- LEFT: Map -->
            <div class="col-lg-6">
                <h5 class="contact-map-title mb-3">Furniture Market Showroom</h5>
                <p class="contact-map-address mb-3">
                    <i class="bi bi-geo-alt-fill me-2" style="color:var(--accent);"></i>
                    Plot No. 12, Furniture Market Lane, MIDC Industrial Area, Pune – 411026, Maharashtra
                </p>
                <div class="contact-map-embed mb-3">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3782.265588856342!2d73.85674!3d18.52043!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTjCsDMxJzEzLjYiTiA3M8KwNTEnMjQuMyJF!5e0!3m2!1sen!2sin!4v1600000000000"
                        width="100%" height="280" style="border:0;border-radius:var(--radius);" allowfullscreen="" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
                <a href="https://maps.google.com" target="_blank" class="btn btn-accent">
                    <i class="bi bi-geo-alt me-2"></i>Get Directions
                </a>
            </div>

            <!-- RIGHT: Business Information -->
            <div class="col-lg-6">
                <h5 class="contact-map-title mb-4">Business Information</h5>
                <ul class="contact-biz-info-list">
                    <li>
                        <i class="bi bi-telephone-fill"></i>
                        <div>
                            <strong>Phone</strong>
                            <span>1800 123 4567</span>
                        </div>
                    </li>
                    <li>
                        <i class="bi bi-envelope-fill"></i>
                        <div>
                            <strong>Email</strong>
                            <span>info@furnituremarket.in</span>
                        </div>
                    </li>
                    <li>
                        <i class="bi bi-geo-alt-fill"></i>
                        <div>
                            <strong>Showroom Address</strong>
                            <span>Plot No. 12, Furniture Market Lane, MIDC Industrial Area, Pune – 411026</span>
                        </div>
                    </li>
                    <li>
                        <i class="bi bi-clock-fill"></i>
                        <div>
                            <strong>Business Hours</strong>
                            <span>Mon – Sat: 9:00 AM – 7:00 PM<br>Sunday: Closed</span>
                        </div>
                    </li>
                </ul>
                <div class="d-flex gap-3 mt-4">
                    <a href="#" class="social-icon"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="social-icon contact-social-icon"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="social-icon contact-social-icon"><i class="bi bi-pinterest"></i></a>
                    <a href="#" class="social-icon contact-social-icon"><i class="bi bi-youtube"></i></a>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ===== CTA BAR ===== -->
<section class="contact-cta-bar">
    <div class="container-fluid px-4">
        <div class="row align-items-center gy-4">
            <div class="col-lg-7">
                <h3 class="contact-cta-title mb-2">Ready to Furnish<br><span style="color:var(--accent);">Your Space?</span></h3>
                <p class="contact-cta-desc mb-0">Talk to our furniture specialists today and get personalized recommendations for your home, office or business.</p>
            </div>
            <div class="col-lg-5 text-lg-end">
                <div class="d-flex gap-3 justify-content-lg-end flex-wrap">
                    <a href="tel:18001234567" class="btn btn-accent px-4 py-3">
                        <i class="bi bi-telephone me-2"></i>Call Now
                    </a>
                    <a href="https://wa.me/919876543210" target="_blank" class="btn contact-cta-wa-btn px-4 py-3">
                        <i class="bi bi-whatsapp me-2"></i>WhatsApp Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
require_once("./layouts/footer.php");
?>
