<?php
require_once 'layouts/header.php';
$categories = $pdo->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();

?>
<title></title>
<meta name="description" content="">
<meta name="keywords" content="">
<link rel="stylesheet" href="css/product.css">
<?php require_once 'layouts/navbar.php'; ?>


<!-- ===== CONTACT HERO SECTION ===== -->
<section class="contact-hero-section">
    <div class="contact-hero-overlay">
        <div class="container px-4 h-100">
            <div class="row h-100 align-items-end pb-5">
                <div class="col-lg-6 col-md-6 col-12">
                    <p class="root-navigation mb-1">
                        <a href="index.php" style="color:var(--primary);text-decoration:none;">Home</a>
                        <span class="mx-1">&rsaquo;</span>
                        <span style="color:var(--accent);">Contact Us</span>
                    </p>
                    <h1 class="contact-hero-title">
                        Let's Build Something<br>
                        <span class="contact-hero-title-accent text-dark">Beautiful Together.</span>
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

                    <form action="save_inquiry.php" method="POST">

                        <div class="row g-3">

                            <div class="col-md-6">
                                <input
                                    type="text"
                                    class="form-control inquiry-input"
                                    name="name"
                                    placeholder="Your Name *"
                                    required>
                            </div>

                            <div class="col-md-6">
                                <input
                                    type="tel"
                                    class="form-control inquiry-input"
                                    name="phone"
                                    placeholder="Phone Number *"
                                    required>
                            </div>

                            <div class="col-md-6">
                                <input
                                    type="email"
                                    class="form-control inquiry-input"
                                    name="email"
                                    placeholder="Email Address *"
                                    required>
                            </div>

                            <div class="col-md-6">
                                <input
                                    type="text"
                                    class="form-control inquiry-input"
                                    name="city"
                                    placeholder="City">
                            </div>

                            <div class="col-12">
                                <select
                                    class="form-select inquiry-input"
                                    name="category_id">

                                    <option value="">Select Category</option>

                                    <?php foreach ($categories as $category): ?>

                                        <option value="<?= $category['id'] ?>">
                                            <?= htmlspecialchars($category['category_name']) ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>
                            </div>

                            <div class="col-12">
                                <textarea
                                    class="form-control inquiry-input"
                                    rows="5"
                                    name="message"
                                    placeholder="Your Message / Requirements"
                                    required></textarea>
                            </div>

                            <div class="col-12">
                                <button
                                    type="submit"
                                    name="send_inquiry"
                                    class="btn btn-accent w-100 py-3">
                                    Send Inquiry
                                    <i class="bi bi-arrow-right"></i>
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

<!-- ===== MAP + BUSINESS INFO ===== -->
<section class="section-gap" id="map-section" style="background:var(--bg);">
    <div class="container-fluid px-4">
        <div class="row g-5 align-items-stretch">

            <!-- LEFT: Map -->
            <div class="col-lg-6">
                <h5 class="contact-map-title mb-3">Furniture Shoppers Showroom</h5>
                <p class="contact-map-address mb-3">
                    <i class="bi bi-geo-alt-fill me-2" style="color:var(--accent);"></i>
                    Plot No. 12, Furniture Shoppers Lane, MIDC Industrial Area, Pune – 411026, Maharashtra
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
                            <span>Plot No. 12, Furniture Shoppers Lane, MIDC Industrial Area, Pune – 411026</span>
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