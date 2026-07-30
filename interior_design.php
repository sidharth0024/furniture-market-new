<?php
require_once 'layouts/header.php';

?>
<title>Interior Design</title>
<meta name="description" content="">
<meta name="keywords" content="">
<link rel="stylesheet" href="./css/interior.css">
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
                        <span style="color:#fff;">Interior Design</span>
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


<!-- HERO -->
<header class="hero-section" id="top">
    <div class="container">
        <div class="row align-items-center gy-5">
            <div class="col-lg-6">
                <div class="eyebrow mb-3">Residential &amp; Commercial Interiors</div>
                <h1 class="hero-heading">Transform your home with beautiful <span class="italic-accent">interior designs</span></h1>
                <p class="hero-sub">Create a home that reflects your personality with thoughtfully designed interiors. Whether you're furnishing a new home or upgrading your existing space, our expert team helps you achieve stylish, functional, and comfortable living spaces — room by room.</p>
                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a href="#contact" class="btn-walnut">Get Free Consultation</a>
                    <a href="#services" class="btn-outline-walnut">Explore Services</a>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="plan-wrap">
                    <img src="./assets/img/interior_design.jpeg" width="100%" alt="">
                </div>
            </div>
        </div>
    </div>
</header>

<!-- SERVICES -->
<section class="section" id="services">
    <div class="container">
        <div class="section-head">
            <div class="eyebrow">What We Design</div>
            <div class="section-rule"></div>
            <h2 class="section-title">Our interior design services</h2>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="service-card">
                    <h3>Living Room Design</h3>
                    <p>Create a welcoming living space with premium sofas, TV units, coffee tables, accent chairs, and elegant décor.</p>
                    <ul class="service-list">
                        <li>Luxury Sofa Sets</li>
                        <li>TV Units</li>
                        <li>Coffee &amp; Side Tables</li>
                        <li>Decorative Lighting</li>
                        <li>Wall Décor</li>
                    </ul>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="service-card">
                    <h3>Bedroom Interior Design</h3>
                    <p>Relax in a beautifully designed bedroom featuring modern wardrobes, beds, dressing tables, and bedside units.</p>
                    <ul class="service-list">
                        <li>Master Bedrooms</li>
                        <li>Guest Bedrooms</li>
                        <li>Kids' Rooms</li>
                        <li>Studio Apartments</li>
                    </ul>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="service-card">
                    <h3>Modular Kitchen Design</h3>
                    <p>Functional kitchens designed for efficient cooking with stylish cabinets and premium finishes.</p>
                    <ul class="service-list">
                        <li>Modular Cabinets</li>
                        <li>Tall Units &amp; Pantry Storage</li>
                        <li>Soft Close Drawers</li>
                        <li>Quartz &amp; Granite Countertops</li>
                    </ul>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="service-card">
                    <h3>Dining Room Design</h3>
                    <p>Elegant dining spaces that bring families together with beautiful dining tables, chairs, crockery units, and lighting.</p>
                    <ul class="service-list">
                        <li>Dining Tables &amp; Chairs</li>
                        <li>Crockery Units</li>
                        <li>Statement Lighting</li>
                    </ul>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="service-card">
                    <h3>Office &amp; Workspace Interiors</h3>
                    <p>Productive workspaces designed with ergonomic furniture and smart storage.</p>
                    <ul class="service-list">
                        <li>Home Offices</li>
                        <li>Corporate Offices</li>
                        <li>Reception Areas</li>
                        <li>Conference Rooms</li>
                    </ul>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="service-card" style="background:var(--walnut); border-color:var(--walnut);">
                    <div class="service-code" style="color:var(--ochre-soft);">+ More</div>
                    <h3 style="color:var(--ivory);">Every Room, Considered</h3>
                    <p style="color:#E3D5BF;">From foyers to balconies, we tailor design solutions to every corner of your home or workspace.</p>
                    <a href="#contact" class="btn-outline-walnut d-inline-block mt-2" style="border-color:#fff; color:#fff;">Discuss Your Space →</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- WHY CHOOSE US -->
<section class="section section-alt">
    <div class="container">
        <div class="row g-5 align-items-start">
            <div class="col-lg-5">
                <div class="eyebrow">The Difference</div>
                <div class="section-rule"></div>
                <h2 class="section-title mb-3">Why choose our interior design services?</h2>
                <p style="color:var(--ink-soft); font-size:0.95rem; line-height:1.7;">From the first sketch to the final install, every step is handled by our own team — no guesswork, no unreliable subcontractors, no surprises.</p>
            </div>
            <div class="col-lg-7">
                <ul class="spec-list row row-cols-1 row-cols-md-2 g-0">
                    <li class="col"><i class="bi bi-check2"></i>Customized Design Solutions</li>
                    <li class="col"><i class="bi bi-check2"></i>Space Planning &amp; Optimization</li>
                    <li class="col"><i class="bi bi-check2"></i>3D Design Visualization</li>
                    <li class="col"><i class="bi bi-check2"></i>Premium Quality Furniture</li>
                    <li class="col"><i class="bi bi-check2"></i>Modern &amp; Classic Design Styles</li>
                    <li class="col"><i class="bi bi-check2"></i>Affordable Packages</li>
                    <li class="col"><i class="bi bi-check2"></i>End-to-End Project Execution</li>
                    <li class="col"><i class="bi bi-check2"></i>Professional Installation</li>
                    <li class="col"><i class="bi bi-check2"></i>On-Time Delivery</li>
                    <li class="col"><i class="bi bi-check2"></i>After-Sales Support</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- PROCESS -->
<section class="section" id="process">
    <div class="container">
        <div class="row">
            <div class="col-lg-7">
                <div class="section-head">
                    <div class="eyebrow">How It Works</div>
                    <div class="section-rule"></div>
                    <h2 class="section-title">Our design process</h2>
                </div>

                <div class="process-row">
                    <div class="process-num">01</div>
                    <div>
                        <div class="process-title">Free Consultation</div>
                        <p class="process-desc">Understand your lifestyle, space, and design preferences.</p>
                    </div>
                </div>
                <div class="process-row">
                    <div class="process-num">02</div>
                    <div>
                        <div class="process-title">Site Measurement</div>
                        <p class="process-desc">Accurate measurements ensure a perfect fit.</p>
                    </div>
                </div>
                <div class="process-row">
                    <div class="process-num">03</div>
                    <div>
                        <div class="process-title">Design &amp; 3D Visualization</div>
                        <p class="process-desc">Preview your interiors before execution.</p>
                    </div>
                </div>
                <div class="process-row">
                    <div class="process-num">04</div>
                    <div>
                        <div class="process-title">Material Selection</div>
                        <p class="process-desc">Choose from premium wood, laminates, fabrics, finishes, and accessories.</p>
                    </div>
                </div>
                <div class="process-row">
                    <div class="process-num">05</div>
                    <div>
                        <div class="process-title">Furniture Manufacturing</div>
                        <p class="process-desc">High-quality furniture crafted with precision.</p>
                    </div>
                </div>
                <div class="process-row">
                    <div class="process-num">06</div>
                    <div>
                        <div class="process-title">Delivery &amp; Installation</div>
                        <p class="process-desc">Professional installation with attention to every detail.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <img src="./assets/img/How-It-Works.png" width="100%" alt="">
            </div>
        </div>
    </div>
</section>

<!-- STYLES -->
<section class="section section-alt py-5" id="styles">
    <div class="container">

        <div class="section-head text-center mb-5">
            <span class="eyebrow">Design Language</span>
            <h2 class="section-title mt-2">
                Interior Styles We Offer
            </h2>
            <p class="text-muted mb-0">
                Discover inspiring interior themes crafted to match every lifestyle and personality.
            </p>
        </div>

        <div class="row g-4">

            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="style-card">
                    <img src="assets/img/modern_interior.jpeg" alt="">
                    <div class="overlay"></div>
                    <div class="content">
                        <i class="bi bi-house-heart-fill"></i>
                        <h5>Modern Interior</h5>
                        <p>Elegant, clean and functional living spaces.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="style-card">
                    <img src="assets/img/contemporary.jpeg" alt="">
                    <div class="overlay"></div>
                    <div class="content">
                        <i class="bi bi-stars"></i>
                        <h5>Contemporary</h5>
                        <p>Latest design trends with timeless elegance.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="style-card">
                    <img src="assets/img/minimalist.jpeg" alt="">
                    <div class="overlay"></div>
                    <div class="content">
                        <i class="bi bi-grid"></i>
                        <h5>Minimalist</h5>
                        <p>Simple spaces with maximum comfort.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="style-card">
                    <img src="assets/img/luxury_interior.jpeg" alt="">
                    <div class="overlay"></div>
                    <div class="content">
                        <i class="bi bi-gem"></i>
                        <h5>Luxury Interior</h5>
                        <p>Premium furniture with sophisticated finishes.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="style-card">
                    <img src="assets/img/scandinavian.jpg" alt="">
                    <div class="overlay"></div>
                    <div class="content">
                        <i class="bi bi-snow"></i>
                        <h5>Scandinavian</h5>
                        <p>Bright, cozy and naturally beautiful.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="style-card">
                    <img src="assets/img/industrail.jpg" alt="">
                    <div class="overlay"></div>
                    <div class="content">
                        <i class="bi bi-building"></i>
                        <h5>Industrial</h5>
                        <p>Raw textures with urban elegance.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="style-card">
                    <img src="assets/img/traditional_wooden.jpg" alt="">
                    <div class="overlay"></div>
                    <div class="content">
                        <i class="bi bi-tree-fill"></i>
                        <h5>Traditional Wooden</h5>
                        <p>Classic craftsmanship with rich wood finishes.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="style-card">
                    <img src="assets/img/classic_interior.jpg" alt="">
                    <div class="overlay"></div>
                    <div class="content">
                        <i class="bi bi-award-fill"></i>
                        <h5>Classic Interior</h5>
                        <p>Timeless luxury with elegant detailing.</p>
                    </div>
                </div>
            </div>

        </div>

    </div>
</section>

<!-- FURNITURE CATEGORIES -->
<section class="section" id="furniture">
    <div class="container">
        <div class="section-head">
            <div class="eyebrow">Shop the Look</div>
            <div class="section-rule"></div>
            <h2 class="section-title">Furniture categories</h2>
        </div>
        <div>
            <a href="#" class="chip">Sofa Sets</a>
            <a href="#" class="chip">Recliners</a>
            <a href="#" class="chip">Beds</a>
            <a href="#" class="chip">Wardrobes</a>
            <a href="#" class="chip">Dining Tables</a>
            <a href="#" class="chip">Coffee Tables</a>
            <a href="#" class="chip">TV Units</a>
            <a href="#" class="chip">Bookshelves</a>
            <a href="#" class="chip">Office Furniture</a>
            <a href="#" class="chip">Study Tables</a>
            <a href="#" class="chip">Modular Kitchens</a>
            <a href="#" class="chip">Shoe Cabinets</a>
            <a href="#" class="chip">Dressing Tables</a>
            <a href="#" class="chip">Storage Solutions</a>
        </div>
    </div>
</section>

<!-- WHY CUSTOMERS LOVE US -->
<section class="section section-alt">
    <div class="container">
        <div class="section-head text-center mx-auto" style="max-width:38rem;">
            <div class="eyebrow">Trusted by Homeowners</div>
            <div class="section-rule mx-auto"></div>
            <h2 class="section-title">Why customers love us</h2>
        </div>
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="love-item"><i class="bi bi-gem"></i>
                    <p>Premium Materials</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="love-item"><i class="bi bi-tools"></i>
                    <p>Expert Craftsmanship</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="love-item"><i class="bi bi-palette"></i>
                    <p>Elegant Designs</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="love-item"><i class="bi bi-magic"></i>
                    <p>Custom Furniture</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="love-item"><i class="bi bi-tag"></i>
                    <p>Competitive Pricing</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="love-item"><i class="bi bi-truck"></i>
                    <p>Reliable Delivery</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="love-item"><i class="bi bi-person-check"></i>
                    <p>Professional Installation</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="love-item"><i class="bi bi-headset"></i>
                    <p>Excellent Support</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="section" id="contact">
    <div class="container">
        <div class="cta-band d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-4">
            <div>
                <h2 class="mb-2">Ready to design your dream space?</h2>
                <p class="mb-0">Book a free consultation and get a 3D preview of your home before a single piece is built.</p>
            </div>
            <a href="#" class="btn-outline-walnut" style="border-color:var(--ochre-soft); color:var(--ivory); background:transparent; white-space:nowrap;">Book Free Consultation →</a>
        </div>
    </div>
</section>

<?php
require_once("./layouts/footer.php");
?>