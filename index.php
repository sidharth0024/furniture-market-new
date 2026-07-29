<?php

require_once 'layouts/header.php';
?>
<title>Furniture Shoppers – Premium Furniture for Every Space</title>
<meta name="description" content="Explore premium office, home, restaurant and outdoor furniture. Free delivery, GST invoice, easy returns.">
<?php require_once 'layouts/navbar.php'; ?>

<?php
// ═══════════════════════════════════════════════════════════════
//  DATA FETCHING — single pass, no N+1
// ═══════════════════════════════════════════════════════════════

// 1. Hero sliders (active, sorted)
$sliders = $pdo->query(
  "SELECT id, title, subtitle, description, image
       FROM sliders WHERE status = 1 ORDER BY sort_order ASC, id ASC"
)->fetchAll();

// 2. All active categories
$allCategories = $pdo->query(
  "SELECT id, category_name, slug, description, image
       FROM categories WHERE status = 'Active' ORDER BY id ASC"
)->fetchAll();

$allCategoriesNew = $pdo->query(
  "SELECT id, category_name, slug, description, image
       FROM categories WHERE status = 'Active' ORDER BY created_at DESC, id DESC"
)->fetchAll();

// 3. Featured tab categories — up to 6 active categories
$tabCategories = $pdo->query(
  "SELECT id, category_name FROM categories WHERE status='Active' ORDER BY id ASC LIMIT 6"
)->fetchAll();

// Fetch products for the first tab upfront (other tabs loaded via AJAX)
$firstTabProducts = [];
if (!empty($tabCategories)) {
  $firstTabProducts = fetchCategoryProducts($pdo, (int)$tabCategories[0]['id'], 12);
}

// 4. Category sections (all categories with their products — limit 8 each)
$categorySections = [];
foreach ($allCategories as $cat) {
  $prods = fetchCategoryProducts($pdo, (int)$cat['id'], 8);
  if (!empty($prods)) {
    $categorySections[] = ['cat' => $cat, 'products' => $prods];
  }
}

// 5. "Shop by Categories" — 6 newest
$shopByCategories = array_slice($allCategoriesNew, 0, 6);

// 6. Subcategory auto-scroll slider items (all active subcats)
$subSliderRows = $pdo->query(
  "SELECT sc.subcategory_name AS name, sc.image,
            c.slug AS cat_slug, c.category_name
       FROM subcategories sc JOIN categories c ON c.id = sc.category_id
      WHERE sc.status = 'Active' AND c.status = 'Active'
      ORDER BY sc.id ASC LIMIT 20"
)->fetchAll();
$subSliderItems = array_merge($subSliderRows, $subSliderRows); // Duplicate for infinite scroll

// 7. Testimonials
$_testimonials = $pdo->query(
  "SELECT id, name, company, review, stars, image
       FROM testimonials WHERE status=1 ORDER BY sort_order ASC, id ASC"
)->fetchAll();

// ── Fallback images ──────────────────────────────────────────────────────────
$subFallbacks = [
  'https://images.unsplash.com/photo-1598300042247-d088f8ab3a91?w=160&h=160&fit=crop',
  'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=160&h=160&fit=crop',
  'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=160&h=160&fit=crop',
  'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=160&h=160&fit=crop',
  'https://images.unsplash.com/photo-1617806118233-18e1de247200?w=160&h=160&fit=crop',
  'https://images.unsplash.com/photo-1497366216548-37526070297c?w=160&h=160&fit=crop',
];

$catBannerFallbacks = [
  'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=900&h=300&fit=crop',
  'https://images.unsplash.com/photo-1497366216548-37526070297c?w=900&h=300&fit=crop',
  'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=900&h=300&fit=crop',
  'https://images.unsplash.com/photo-1617806118233-18e1de247200?w=900&h=300&fit=crop',
];

$comfortImages = [
  'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=500&h=700&fit=crop',
  'https://images.unsplash.com/photo-1618219908412-a29a1bb7b86e?w=500&h=700&fit=crop',
  'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?w=500&h=700&fit=crop',
  'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=500&h=700&fit=crop',
  'https://images.unsplash.com/photo-1617806118233-18e1de247200?w=500&h=700&fit=crop',
  'https://images.unsplash.com/photo-1493663284031-b7e3aefcae8e?w=500&h=700&fit=crop',
];

$comfortTexts = [
  'Timeless designs built for every living space',
  'Experience comfort that feels effortlessly premium',
  'Crafted for the modern home',
  'Elevate every room with purposeful design',
  'Furniture that works as hard as you do',
  'Where style meets everyday living',
];

// ── Hero right-side images per slider ───────────────────────────────────────
$heroRightImages = [
  'https://images.unsplash.com/photo-1524758631624-e2822e304c36?w=600&h=500&fit=crop',
  'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=600&h=500&fit=crop',
  'https://images.unsplash.com/photo-1618219908412-a29a1bb7b86e?w=600&h=500&fit=crop',
];
?>

<!-- ═══════════════════════════════════════════════════════
     HERO SECTION
     ═══════════════════════════════════════════════════════ -->
<section class="hero-section" id="heroSection">
  <div class="hero-carousel">
    <div class="hero-carousel-inner" id="heroCarouselInner">

      <?php if (!empty($sliders)): ?>
        <?php foreach ($sliders as $si => $slide):
          $parts    = explode('.', $slide['title'], 2);
          $line1    = trim($parts[0]);
          $line2    = isset($parts[1]) ? trim($parts[1]) : '';
          $btnText  = 'Explore Collection &rarr;';
          $btn2Text = 'View Offers';
          $heroRight = $heroRightImages[$si % count($heroRightImages)];

          // Hero background image
          if (!empty($slide['image'])) {
            $heroBg = str_starts_with($slide['image'], 'http')
              ? $slide['image']
              : $slide['image'];
          } else {
            $heroBg = 'https://images.unsplash.com/photo-1497366754035-f200586c4bd4?w=1600&h=600&fit=crop';
          }
        ?>
          <div class="hero-slide">
            <div class="hero-slide-bg">
              <img src="<?= htmlspecialchars($heroBg) ?>" alt="<?= htmlspecialchars($slide['title']) ?>">
            </div>
            <div class="container-fluid px-4">
              <div class="row align-items-center">
                <div class="col-lg-5 col-md-7">
                  <div class="hero-content-left">
                    <span class="hero-label"><?= htmlspecialchars($slide['subtitle'] ?? 'PREMIUM COLLECTION') ?></span>
                    <h1 class="hero-title">
                      <?= htmlspecialchars($line1) ?>
                      <?php if ($line2): ?>
                        <span class="hero-title-accent"><?= htmlspecialchars($line2) ?></span>
                      <?php endif; ?>
                    </h1>
                    <p class="hero-desc"><?= htmlspecialchars($slide['description'] ?? '') ?></p>
                    <div class="hero-actions">
                      <a href="product.php" class="btn btn-primary-fm"><?= $btnText ?></a>
                      <a href="contactus.php" class="btn btn-outline-fm"><?= htmlspecialchars($btn2Text) ?></a>
                    </div>
                  </div>
                </div>
                <div class="col-lg-6 offset-lg-1 col-md-5 d-none d-md-block">
                  <div class="hero-image-right">
                    <img src="<?= htmlspecialchars($heroRight) ?>" alt="<?= htmlspecialchars($slide['title']) ?>">
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="hero-slide">
          <div class="hero-slide-bg">
            <img src="https://images.unsplash.com/photo-1497366754035-f200586c4bd4?w=1600&h=600&fit=crop" alt="">
          </div>
          <div class="container-fluid px-4">
            <div class="row align-items-center">
              <div class="col-lg-5 col-md-7">
                <div class="hero-content-left">
                  <span class="hero-label">PREMIUM COLLECTION</span>
                  <h1 class="hero-title">Design Your Space.<span class="hero-title-accent">Elevate Your Life.</span></h1>
                  <p class="hero-desc">Explore a wide range of premium furniture for every space — office, home, hospitality and beyond.</p>
                  <div class="hero-actions">
                    <a href="product.php" class="btn btn-primary-fm">Explore Collection &rarr;</a>
                    <a href="contactus.php" class="btn btn-outline-fm">View Offers</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>

    </div><!-- /hero-carousel-inner -->

    <!-- Arrow nav buttons -->
    <button class="hero-arrow hero-arrow-prev" id="heroPrev" aria-label="Previous slide">
      <i class="bi bi-chevron-left"></i>
    </button>
    <button class="hero-arrow hero-arrow-next" id="heroNext" aria-label="Next slide">
      <i class="bi bi-chevron-right"></i>
    </button>

    <!-- Dot indicators -->
    <div class="hero-controls">
      <?php $dotCount = !empty($sliders) ? count($sliders) : 1; ?>
      <?php for ($d = 0; $d < $dotCount; $d++): ?>
        <button class="hero-dot <?= $d === 0 ? 'active' : '' ?>" aria-label="Slide <?= $d + 1 ?>"></button>
      <?php endfor; ?>
    </div>

  </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     TRUST BAR
     ═══════════════════════════════════════════════════════ -->
<div class="trust-bar">
  <div class="container-fluid px-4">
    <div class="row g-3">
      <?php
      $trust = [
        ['bi-truck',              'Free Delivery',    'On all orders'],
        ['bi-arrow-return-left',  'Easy Returns',     'Within 7 days'],
        ['bi-shield-check',       'Secure Payments',  '100% protected'],
        ['bi-receipt',            'GST Invoice',      'Available'],
        ['bi-hand-thumbs-up',     'Best Price',       'Guaranteed'],
      ];
      foreach ($trust as $t): ?>
        <div class="col-6 col-md-4 col-lg-auto flex-lg-fill">
          <div class="trust-item d-flex align-items-center gap-3 py-1">
            <i class="bi <?= $t[0] ?>" style="font-size:28px;color:var(--accent);flex-shrink:0;"></i>
            <div>
              <strong style="font-size:13.5px;color:var(--heading);display:block;"><?= $t[1] ?></strong>
              <span style="font-size:12px;color:var(--body);"><?= $t[2] ?></span>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     SUBCATEGORY AUTO-SCROLL SLIDER
     ═══════════════════════════════════════════════════════ -->
<?php if (!empty($subSliderItems)): ?>
  <div class="category-slider-section">
    <div class="category-slider-track">
      <?php foreach ($subSliderItems as $si => $sc):
        if (!empty($sc['image'])) {
          $scImg = str_starts_with($sc['image'], 'http') ? $sc['image'] : 'admin_panel/uploads/subcategory/' . $sc['image'];
        } else {
          $scImg = $subFallbacks[$si % count($subFallbacks)];
        }
      ?>
        <a href="product.php?slug=<?= htmlspecialchars($sc['cat_slug']) ?>" class="cat-slide-card text-decoration-none">
          <div class="cat-slide-img">
            <img src="<?= htmlspecialchars($scImg) ?>" alt="<?= htmlspecialchars($sc['name']) ?>" loading="lazy">
          </div>
          <span class="cat-slide-name"><?= htmlspecialchars($sc['name']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════
     SECTION 1 — FEATURED PRODUCTS (Tab-based, AJAX)
     ═══════════════════════════════════════════════════════ -->
<?php if (!empty($tabCategories)): ?>
  <section class="section-products">
    <div class="container-fluid px-4">
      <div class="section-prod-header">
        <div class="section-prod-header-left">
          <p class="section-label mb-1">FEATURED COLLECTIONS</p>
          <h2 class="section-title mb-1">Furniture That Defines Your Space</h2>
          <p class="section-subtitle">Premium, Ergonomic and Timeless Designs for Homes, Offices &amp; Hospitality</p>
        </div>
        <div class="section-prod-tabs" id="featuredTabs">
          <?php foreach ($tabCategories as $ti => $tc): ?>
            <button class="prod-tab-btn <?= $ti === 0 ? 'active' : '' ?>"
              data-tab-id="featTab<?= (int)$tc['id'] ?>"
              data-cat-id="<?= (int)$tc['id'] ?>">
              <?= htmlspecialchars($tc['category_name']) ?>
            </button>
          <?php endforeach; ?>
        </div>
      </div>

      <?php foreach ($tabCategories as $ti => $tc): ?>
        <div class="prod-tab-panel <?= $ti === 0 ? 'active' : '' ?>" id="featTab<?= (int)$tc['id'] ?>">
          <div class="prod-slider-wrap" id="featWrap<?= (int)$tc['id'] ?>">
            <div class="prod-slider" id="featSlider<?= (int)$tc['id'] ?>"
              style="flex-wrap:nowrap;transition:transform 350ms ease;">
              <?php
              if ($ti === 0) {
                foreach ($firstTabProducts as $p) {
                  echo productCard($p);
                }
              }
              ?>
            </div>
            <div class="prod-slider-controls">
              <button class="slider-arrow" data-feat-prev="featSlider<?= (int)$tc['id'] ?>"><i class="bi bi-arrow-left"></i></button>
              <div class="slider-progress-wrap flex-grow-1">
                <div class="slider-progress-bar" id="featProg<?= (int)$tc['id'] ?>" style="width:20%;"></div>
              </div>
              <button class="slider-arrow" data-feat-next="featSlider<?= (int)$tc['id'] ?>"><i class="bi bi-arrow-right"></i></button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════
     SECTIONS — CATEGORY-WISE SHOPPING
     ═══════════════════════════════════════════════════════ -->
<?php
$sectionBg   = ['var(--bg)', 'var(--surface)', 'var(--bg)', 'var(--surface)'];
$sectionIdx  = 0;

foreach ($categorySections as $cs):
  $cat   = $cs['cat'];
  $prods = $cs['products'];

  // Category image (left promo)
  if (!empty($cat['image'])) {
    $catImg = str_starts_with($cat['image'], 'http')
      ? $cat['image']
      : 'uploads/category/' . $cat['image'];
  } else {
    $catImg = $comfortImages[$sectionIdx % count($comfortImages)];
  }

  $promoText = $comfortTexts[$sectionIdx % count($comfortTexts)];
  $bg = $sectionBg[$sectionIdx % count($sectionBg)];
  $trackId  = 'catSlider' . (int)$cat['id'];
  $sectionIdx++;
?>
  <section class="section-comfort" style="background:<?= $bg ?>;">
    <div class="container-fluid px-4">
      <div class="row g-4">

        <!-- Left: category promo image -->
        <div class="col-lg-3 col-md-4">
          <div class="comfort-promo-img">
            <img src="<?= htmlspecialchars($catImg) ?>" alt="<?= htmlspecialchars($cat['category_name']) ?>">
            <div class="comfort-promo-overlay">
              <div class="comfort-promo-text">
                <p><?= htmlspecialchars($promoText) ?></p>
              </div>
            </div>
          </div>
        </div>

        <!-- Right: header + product slider -->
        <div class="col-lg-9 col-md-8">
          <div class="sec2-header">
            <div>
              <p class="section-label mb-1"><?= strtoupper(htmlspecialchars($cat['category_name'])) ?></p>
              <h2 class="section-title mb-1"><?= htmlspecialchars($cat['category_name']) ?></h2>
              <p class="section-subtitle"><?= htmlspecialchars($cat['description'] ?? 'Explore our premium ' . $cat['category_name'] . ' collection.') ?></p>
            </div>
            <a href="product.php?slug=<?= htmlspecialchars($cat['slug']) ?>" class="shop-all-link flex-shrink-0">Shop All Products &rsaquo;</a>
          </div>
          <div class="prod-slider-wrap" id="catWrap<?= (int)$cat['id'] ?>">
            <div class="prod-slider" id="<?= $trackId ?>"
              style="flex-wrap:nowrap;transition:transform 350ms ease;">
              <?php foreach ($prods as $p): echo productCard($p);
              endforeach; ?>
            </div>
            <div class="prod-slider-controls">
              <button class="slider-arrow" data-feat-prev="<?= $trackId ?>"><i class="bi bi-arrow-left"></i></button>
              <div class="slider-progress-wrap flex-grow-1">
                <div class="slider-progress-bar" id="<?= $trackId ?>Prog" style="width:20%;"></div>
              </div>
              <button class="slider-arrow" data-feat-next="<?= $trackId ?>"><i class="bi bi-arrow-right"></i></button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>
<?php endforeach; ?>

<!-- ═══════════════════════════════════════════════════════
     SHOP BY CATEGORIES — latest 6 categories
     ═══════════════════════════════════════════════════════ -->
<?php if (!empty($shopByCategories)): ?>
  <section class="section-style" style="background:var(--bg);">
    <div class="container-fluid px-4">
      <div class="row mb-5">
        <div class="col-lg-6">
          <p class="section-label">BROWSE BY CATEGORY</p>
          <h2 class="section-title">Shop by Categories</h2>
          <p class="section-subtitle">Explore our curated furniture categories — from home living to office spaces.</p>
        </div>
      </div>
      <div class="style-grid">
        <?php foreach ($shopByCategories as $si => $sc):
          if (!empty($sc['image'])) {
            $scImg = str_starts_with($sc['image'], 'http')
              ? $sc['image']
              : 'uploads/category/' . $sc['image'];
          } else {
            $scImg = $catBannerFallbacks[$si % count($catBannerFallbacks)];
          }
        ?>
          <a href="product.php?slug=<?= htmlspecialchars($sc['slug']) ?>"
            class="style-card text-decoration-none <?= ($si === 0 || $si === 3) ? 'style-card-tall' : '' ?>">
            <img src="<?= htmlspecialchars($scImg) ?>" alt="<?= htmlspecialchars($sc['category_name']) ?>" loading="lazy">
            <div class="style-card-overlay">
              <span class="style-card-label"><?= htmlspecialchars($sc['category_name']) ?></span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════
     AVAILABLE OFFERS (static — admin can edit via contact/settings)
     ═══════════════════════════════════════════════════════ -->
<section class="section-offers">
  <div class="container-fluid px-4">
    <div class="row mb-4">
      <div class="col-12 text-center">
        <p class="section-label">EXCLUSIVE DEALS</p>
        <h2 class="section-title">Available Offers</h2>
      </div>
    </div>
    <?php
    $offers = [
      ['bi-percent',       '#e5f0ff', 'Flat 10% Off', 'Use code FIRST10 on your first order. Valid on all products above ₹15,000.', 'FIRST10'],
      ['bi-truck',         '#fff5e5', 'Free Delivery', 'Free delivery and installation on orders above ₹20,000 across India.', 'AUTO'],
      ['bi-credit-card',   '#f0fff4', 'Easy EMI',      'No-cost EMI starting ₹2,999/month on select products via major banks.', 'EMI'],
      ['bi-arrow-clockwise', '#fff0f3', 'Exchange Offer', 'Get up to ₹8,000 off on exchange of your old furniture. T&C apply.', 'EXCHANGE'],
      ['bi-people',        '#f5f0ff', 'Bulk Order',    'Special prices for bulk & corporate orders. 15% off on 5+ pieces.', 'BULK15'],
      ['bi-gift',          '#e5ffe5', 'Festival Sale',  'Seasonal discounts of up to 23% sitewide during festive offers.', 'FESTIVE'],
    ];
    ?>
    <div class="row g-4">
      <?php foreach ($offers as $o): ?>
        <div class="col-lg-4 col-md-6">
          <div class="offer-card">
            <span class="offer-card-badge"><?= $o[4] ?></span>
            <i class="bi <?= $o[0] ?> offer-card-icon"></i>
            <h5><?= $o[2] ?></h5>
            <p><?= $o[3] ?></p>
            <a href="contactus.php" class="offer-know-more">Know More <i class="bi bi-arrow-right"></i></a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     FOR PROJECTS
     ═══════════════════════════════════════════════════════ -->
<section class="section-projects">
  <div class="container-fluid px-4">
    <div class="row mb-5">
      <div class="col-12 text-center">
        <p class="section-label">LARGE-SCALE SOLUTIONS</p>
        <h2 class="section-title">For Projects: Custom Furniture &amp; Interior Fitouts</h2>
      </div>
    </div>
    <?php
    $projects = [
      ['Custom Office Furniture Solutions',   'Connect with us for your project!',                'https://images.unsplash.com/photo-1497366216548-37526070297c?w=600&h=340&fit=crop'],
      ['Tailor-made Home Furniture',          'Connect us for your dream interiors!',              'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?w=600&h=340&fit=crop'],
      ['High-Quality Hotel Furniture',        'Designed for luxury &amp; comfort!',                'https://images.unsplash.com/photo-1564078516393-cf04bd966897?w=600&h=340&fit=crop'],
      ['Modern Commercial Space Furniture',   'Smart solutions to elevate your business spaces!',  'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=600&h=340&fit=crop'],
    ];
    ?>
    <div class="row g-4">
      <?php foreach ($projects as $pr): ?>
        <div class="col-lg-3 col-md-6">
          <div class="project-card">
            <img src="<?= htmlspecialchars($pr[2]) ?>" alt="<?= htmlspecialchars($pr[0]) ?>" loading="lazy">
            <div class="project-card-overlay">
              <h4 class="project-card-title"><?= htmlspecialchars($pr[0]) ?></h4>
              <p class="project-card-sub"><?= $pr[1] ?></p>
              <a href="contactus.php"><span class="project-card-cta">Connect With Us</span></a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     BUILT ON TRUST
     ═══════════════════════════════════════════════════════ -->
<section class="section-trust">
  <div class="container-fluid px-4">
    <div class="row mb-5">
      <div class="col-12">
        <p class="section-label">WHY FURNITURE SHOPPERS</p>
        <h2 class="section-title">Built on Trust</h2>
      </div>
    </div>
    <?php
    $trustCards = [
      ['bi-patch-check', 'Verified Products',   'Every listing is checked for accurate specifications and editorial quality.'],
      ['bi-shop',        'Trusted Marketplace', 'Thousands of buyers source furniture through Furniture Shoppers every month.'],
      ['bi-geo-alt',     'PAN India Delivery',  'Reliable logistics network covering every major city and town.'],
      ['bi-headset',     'Dedicated Support',   'A real team to help you choose, customise, and order with confidence.'],
      ['bi-star',        'Quality Assurance',   'Materials and craftsmanship are reviewed before a product is listed.'],
      ['bi-shield-lock', 'Secure Enquiry',      'Your details stay private — shared only with the seller you contact.'],
    ];
    ?>
    <div class="row g-4">
      <?php foreach ($trustCards as $tc): ?>
        <div class="col-lg-4 col-md-6">
          <div class="trust-card">
            <div class="trust-card-icon"><i class="bi <?= $tc[0] ?>"></i></div>
            <h5><?= $tc[1] ?></h5>
            <p><?= $tc[2] ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Footer (Testimonials + FAQ + Site Footer) -->
<?php require_once 'layouts/footer.php'; ?>

<!-- ═══════════════════════════════════════════════════════
     INLINE SCRIPT — All sliders + AJAX tabs + swipe support
     ═══════════════════════════════════════════════════════ -->
<script>
  (function() {
    'use strict';

    /* ── Touch/swipe helper ─────────────────────────────────── */
    function addSwipe(el, onLeft, onRight) {
      if (!el) return;
      var startX = null,
        startY = null;
      el.addEventListener('touchstart', function(e) {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
      }, {
        passive: true
      });
      el.addEventListener('touchend', function(e) {
        if (startX === null) return;
        var dx = e.changedTouches[0].clientX - startX;
        var dy = e.changedTouches[0].clientY - startY;
        if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 50) {
          if (dx < 0) {
            onLeft();
          } else {
            onRight();
          }
        }
        startX = startY = null;
      }, {
        passive: true
      });
    }

    /* ── Generic slider engine (for dynamic sliders) ─────────── */
    function makeSlider(trackEl, progressEl) {
      var index = 0;
      var wrapEl = trackEl.closest('.prod-slider-wrap') || trackEl.parentElement;

      function getCards() {
        return trackEl.querySelectorAll('.product-card-col');
      }

      /*
       * Use the actual scrollable width. Category sliders can be narrower
       * than the featured slider, so a fixed "visible card count" can move
       * past the last card and expose a clipped/broken card.
       */
      function getMetrics() {
        var cards = getCards();
        if (!cards.length || !wrapEl) {
          return {
            cards: cards,
            step: 0,
            maxScroll: 0,
            maxIndex: 0
          };
        }

        var cardW = cards[0].getBoundingClientRect().width;
        var gap = parseFloat(getComputedStyle(trackEl).columnGap) || 20;
        var viewportW = wrapEl.clientWidth;
        var maxScroll = Math.max(0, trackEl.scrollWidth - viewportW);
        var step = cardW + gap;

        return {
          cards: cards,
          step: step,
          maxScroll: maxScroll,
          maxIndex: step > 0 ? Math.ceil(maxScroll / step) : 0
        };
      }

      function maxIndex() {
        return getMetrics().maxIndex;
      }

      function slide() {
        var metrics = getMetrics();
        if (!metrics.cards.length) {
          trackEl.style.transform = 'translate3d(0, 0, 0)';
          if (progressEl) progressEl.style.width = '100%';
          return;
        }

        index = Math.max(0, Math.min(index, metrics.maxIndex));
        var offset = Math.min(index * metrics.step, metrics.maxScroll);
        trackEl.style.transform = 'translate3d(-' + offset + 'px, 0, 0)';
        if (progressEl) {
          var pct = metrics.maxIndex === 0 ? 100 : (index / metrics.maxIndex * 100);
          progressEl.style.width = pct + '%';
        }
      }

      function goPrev() {
        if (index > 0) {
          index--;
          slide();
        }
      }

      function goNext() {
        if (index < maxIndex()) {
          index++;
          slide();
        }
      }

      window.addEventListener('resize', function() {
        index = Math.min(index, maxIndex());
        window.requestAnimationFrame(slide);
      });
      slide();

      return {
        prev: goPrev,
        next: goNext,
        refresh: function() {
          slide();
        },
        reset: function() {
          index = 0;
          slide();
        },
        slide: slide
      };
    }

    /* ── Wire up all data-feat-prev / data-feat-next buttons ─── */
    var sliders = {};

    function initSlider(trackId) {
      if (sliders[trackId]) return sliders[trackId];
      var track = document.getElementById(trackId);
      if (!track) return null;
      var prog = document.getElementById(trackId + 'Prog');
      sliders[trackId] = makeSlider(track, prog);

      /* Add swipe to the wrapper of this slider */
      var wrap = document.getElementById(trackId.replace('catSlider', 'catWrap')
          .replace('featSlider', 'featWrap')) ||
        track.closest('.prod-slider-wrap');
      if (wrap) {
        addSwipe(wrap,
          function() {
            sliders[trackId].next();
          },
          function() {
            sliders[trackId].prev();
          }
        );
      }
      return sliders[trackId];
    }

    document.querySelectorAll('[data-feat-next]').forEach(function(btn) {
      var trackId = btn.getAttribute('data-feat-next');
      btn.addEventListener('click', function() {
        initSlider(trackId).next();
      });
    });
    document.querySelectorAll('[data-feat-prev]').forEach(function(btn) {
      var trackId = btn.getAttribute('data-feat-prev');
      btn.addEventListener('click', function() {
        initSlider(trackId).prev();
      });
    });

    /* Initialize all sliders that already have products rendered (not AJAX-loaded yet) */
    document.querySelectorAll('.prod-slider').forEach(function(track) {
      if (track.id) initSlider(track.id);
    });

    /* ── Featured Product Tabs — AJAX load ──────────────────── */
    var tabBtns = document.querySelectorAll('#featuredTabs .prod-tab-btn');
    var tabPanels = document.querySelectorAll('.prod-tab-panel');
    var loaded = {};

    /* Mark first tab as already loaded */
    tabBtns.forEach(function(b) {
      if (b.classList.contains('active')) {
        loaded[b.getAttribute('data-tab-id')] = true;
      }
    });

    tabBtns.forEach(function(btn) {
      btn.addEventListener('click', function() {
        var tabId = btn.getAttribute('data-tab-id');
        var catId = btn.getAttribute('data-cat-id');

        /* Switch active button */
        tabBtns.forEach(function(b) {
          b.classList.remove('active');
        });
        btn.classList.add('active');

        /* Switch active panel */
        tabPanels.forEach(function(p) {
          p.classList.remove('active');
        });
        var panel = document.getElementById(tabId);
        if (panel) panel.classList.add('active');

        /* AJAX load if not already loaded */
        if (!loaded[tabId] && catId) {
          var trackEl = document.getElementById('featSlider' + catId);
          if (!trackEl) return;
          trackEl.innerHTML = '<p style="padding:20px;color:#999;text-align:center;">Loading...</p>';

          fetch('includes/ajax_tab_products.php?category_id=' + catId)
            .then(function(r) {
              return r.json();
            })
            .then(function(data) {
              if (data.html) {
                trackEl.innerHTML = data.html;
                loaded[tabId] = true;
                /*
                 * The track was initialized once on page load. Refresh
                 * it after AJAX rather than re-initializing it: a second
                 * initialization would add a second touch listener to
                 * the same wrapper and make one swipe jump twice.
                 */
                var loadedSlider = sliders['featSlider' + catId] ||
                  initSlider('featSlider' + catId);
                if (loadedSlider) loadedSlider.refresh();
              } else {
                trackEl.innerHTML = '<p style="padding:20px;color:#999;text-align:center;">No products found.</p>';
              }
            })
            .catch(function() {
              trackEl.innerHTML = '<p style="padding:20px;color:red;text-align:center;">Failed to load products.</p>';
            });
        } else if (sliders['featSlider' + catId]) {
          sliders['featSlider' + catId].reset();
        }
      });
    });

  })();
</script>
</body>

</html>