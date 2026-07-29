<?php
require_once 'layouts/header.php';

?>
<title></title>
<meta name="description" content="">
<meta name="keywords" content="">
<link rel="stylesheet" href="css/product.css">
<?php require_once 'layouts/navbar.php'; ?>


<!-- ===== ABOUT HERO SECTION ===== -->
<section class="about-hero-section">
    <div class="about-hero-overlay">
        <div class="container px-4 h-100">
            <div class="row h-100 align-items-end pb-5">
                <div class="col-lg-6 col-md-6 col-12">
                    <p class="root-navigation mb-1">
                        <a href="index.php" style="color:#fff;text-decoration:none;">Home</a>
                        <span class="mx-1">&rsaquo;</span>
                        <span style="color:#fff;">About Us</span>
                    </p>
                    <h1 class="contact-hero-title">
                        Crafting Spaces That<br>
                        <span class="contact-hero-title-accent">Tell Your Story.</span>
                    </h1>
                    <p class="contact-hero-desc">
                        We are India's trusted furniture marketplace — connecting architects, designers, and
                        businesses with premium furniture manufacturers across the country.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== WHO WE ARE ===== -->
<section class="section-gap bg-light-surface">
    <div class="container px-4">
        <div class="row g-5 align-items-center">

            <div class="col-lg-6">
                <p class="section-label mb-2">OUR STORY</p>
                <h2 class="section-title mb-3">Who We Are</h2>
                <p class="about-body-text mb-3">
                    Furniture Shoppers was founded with a single mission — to make premium, factory-direct furniture accessible to every home, office, and hospitality space across India. We bridge the gap between quality manufacturers and discerning buyers, eliminating unnecessary middlemen and inflated costs.
                </p>
                <p class="about-body-text mb-4">
                    From a single showroom in Pune to a nationwide platform serving thousands of satisfied customers, we have grown steadily by putting quality, transparency, and service at the core of everything we do. Every piece in our catalogue is sourced from trusted manufacturers, rigorously quality-checked, and delivered with professional care.
                </p>
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="about-stat-card">
                            <span class="about-stat-number">10,000<span class="about-stat-plus">+</span></span>
                            <span class="about-stat-label">Happy Customers</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="about-stat-card">
                            <span class="about-stat-number">500<span class="about-stat-plus">+</span></span>
                            <span class="about-stat-label">Products Listed</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="about-stat-card">
                            <span class="about-stat-number">50<span class="about-stat-plus">+</span></span>
                            <span class="about-stat-label">Cities Delivered</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="about-stat-card">
                            <span class="about-stat-number">15<span class="about-stat-plus">+</span></span>
                            <span class="about-stat-label">Years of Experience</span>
                        </div>
                    </div>
                </div>
                <a href="contactus.php" class="btn btn-accent">
                    Get in Touch &nbsp;<i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="col-lg-6">
                <div class="about-img-grid">
                    <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?w=700&h=460&fit=crop"
                        alt="Furniture Shoppers Showroom"
                        class="w-100"
                        style="border-radius:var(--radius);object-fit:cover;height:400px;box-shadow:var(--shadow-hover);">
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ===== OUR MISSION & VISION ===== -->
<section class="section-gap" style="background:var(--bg);">
    <div class="container px-4">
        <div class="section-header-centered mb-5">
            <p class="section-label">WHAT DRIVES US</p>
            <h2 class="section-title">Mission &amp; Vision</h2>
        </div>
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="about-mv-card">
                    <div class="about-mv-icon mb-3">
                        <i class="bi bi-bullseye"></i>
                    </div>
                    <h4 class="about-mv-heading mb-3">Our Mission</h4>
                    <p class="about-body-text mb-0">
                        To democratise access to premium furniture by connecting buyers directly with verified manufacturers — delivering quality, transparency, and value at every step. We believe every home, office, and hospitality space deserves furniture that is thoughtfully designed and built to last.
                    </p>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="about-mv-card">
                    <div class="about-mv-icon mb-3">
                        <i class="bi bi-eye"></i>
                    </div>
                    <h4 class="about-mv-heading mb-3">Our Vision</h4>
                    <p class="about-body-text mb-0">
                        To become India's most trusted marketplace for premium furniture — a destination where architects, designers, and businesses find everything they need to create beautiful, functional spaces with confidence. We envision a future where great furniture is not a luxury, but a standard.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== OUR VALUES ===== -->
<section class="section-gap bg-light-surface">
    <div class="container px-4">
        <div class="section-header-centered mb-5">
            <p class="section-label">WHAT WE STAND FOR</p>
            <h2 class="section-title">Our Core Values</h2>
            <p class="section-subtitle">The principles that guide every decision we make — from sourcing to delivery.</p>
        </div>
        <div class="row g-4">
            <?php
            $values = [
                ['bi-shield-check',     'Quality First',          'Every product in our catalogue goes through rigorous quality checks before it reaches our platform. We do not compromise on materials, finish, or durability.'],
                ['bi-transparency',     'Transparency',           'No hidden costs, no misleading specs. What you see is exactly what you get — honest pricing, clear product details, and straightforward policies.'],
                ['bi-people',           'Customer Focus',         'Our team is dedicated to making your buying experience seamless, from first inquiry to post-delivery support. Your satisfaction drives everything we do.'],
                ['bi-lightbulb',        'Innovation',             'We continuously explore new materials, designs, and technologies to bring you furniture that is not just beautiful today, but relevant for tomorrow.'],
                ['bi-award',            'Craftsmanship',          'We partner with skilled artisans and manufacturers who take pride in their work. Every joint, finish, and upholstery reflects attention to detail and genuine craft.'],
                ['bi-recycle',          'Sustainability',         'We are committed to responsible sourcing — partnering with manufacturers who use sustainably harvested timber and eco-conscious production processes.'],
            ];
            foreach ($values as $v): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="about-value-card">
                        <div class="about-value-icon mb-3">
                            <i class="bi <?= $v[0] ?>"></i>
                        </div>
                        <h5 class="about-value-heading mb-2"><?= $v[1] ?></h5>
                        <p class="about-body-text mb-0"><?= $v[2] ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== CATEGORIES WE SERVE ===== -->
<section class="section-gap" style="background:var(--bg);">
    <div class="container px-4">
        <div class="section-header-centered mb-5">
            <p class="section-label">OUR CATEGORIES</p>
            <h2 class="section-title">Furniture for Every Space</h2>
            <p class="section-subtitle">From home living rooms to corporate boardrooms — we have the right furniture for every setting.</p>
        </div>
        <div class="row g-4">
            <?php
            $categories = [
                ['assets/img/category-office.jpg',      'Office Furniture',      'Ergonomic chairs, executive desks, workstations, and conference furniture for modern workplaces.'],
                ['assets/img/category-home.jpg',        'Home Furniture',        'Sofas, beds, dining sets, wardrobes, and more — crafted for everyday comfort.'],
                ['assets/img/category-restaurant.jpg',  'Restaurant &amp; Cafe', 'Durable, design-forward seating and tables built for high-footfall hospitality spaces.'],
                ['assets/img/category-outdoor.jpg',     'Outdoor Furniture',     'Weather-resistant patio and garden furniture built to last through every season.'],
                ['assets/img/category-educational.jpg', 'Educational Furniture', 'Ergonomic furniture for classrooms, libraries, and campus common areas.'],
                ['assets/img/category-healthcare.jpg',  'Healthcare Furniture',  'Comfortable, easy-to-maintain furniture for clinics and healthcare facilities.'],
            ];
            foreach ($categories as $cat): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="about-category-card">
                        <div class="about-category-img">
                            <img src="<?= $cat[0] ?>" alt="<?= strip_tags($cat[1]) ?>">
                        </div>
                        <div class="about-category-body">
                            <h5 class="about-category-title"><?= $cat[1] ?></h5>
                            <p class="about-body-text mb-3"><?= $cat[2] ?></p>
                            <a href="#" class="offer-know-more">Explore &rarr;</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== WHY CHOOSE US ===== -->
<section class="section-gap bg-light-surface">
    <div class="container px-4">
        <div class="section-header-centered mb-5">
            <p class="section-label">WHY Furniture Shoppers</p>
            <h2 class="section-title">The Furniture Shoppers Difference</h2>
            <p class="section-subtitle">What sets us apart from the rest — and why thousands of customers choose us again and again.</p>
        </div>
        <div class="row g-4">
            <?php
            $differentiators = [
                ['bi-building',             'Factory Direct',           'We work directly with manufacturers — no middlemen, no markups. Pure value passed on to you.'],
                ['bi-gem',                  'Premium Quality',          'Rigorous quality checks ensure every product meets our high standards before it ships.'],
                ['bi-tools',                'Full Customization',       'Bespoke furniture in your size, material, finish, and colour. We make it exactly the way you want.'],
                ['bi-truck',                'Pan India Delivery',       'Reliable, trackable delivery to 50+ cities, with professional installation included.'],
                ['bi-headset',              'Expert Support',           'Our furniture specialists are available 6 days a week to guide you from selection to installation.'],
                ['bi-arrow-return-left',    'Easy Returns',             'Hassle-free returns within 7 days if you are not completely satisfied — no questions asked.'],
            ];
            foreach ($differentiators as $d): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="trust-card">
                        <div class="trust-card-icon">
                            <i class="bi <?= $d[0] ?>"></i>
                        </div>
                        <h5><?= $d[1] ?></h5>
                        <p><?= $d[2] ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== CTA BAR ===== -->
<section class="contact-cta-bar">
    <div class="container px-4">
        <div class="row align-items-center gy-4">
            <div class="col-lg-7">
                <h3 class="contact-cta-title mb-2">Let's Furnish Your<br><span style="color:var(--accent);">Dream Space Together.</span></h3>
                <p class="contact-cta-desc mb-0">Talk to our furniture specialists today and get personalized recommendations for your home, office or business.</p>
            </div>
            <div class="col-lg-5 text-lg-end">
                <div class="d-flex gap-3 justify-content-lg-end flex-wrap">
                    <a href="contactus.php" class="btn btn-accent px-4 py-3">
                        <i class="bi bi-envelope me-2"></i>Contact Us
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