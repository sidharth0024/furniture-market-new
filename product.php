<?php
/**
 * product.php — Dynamic Product Details Page
 * Furniture Market Frontend
 *
 * URL formats:
 *   product.php?slug=modern-office-chair
 *   product.php?id=25
 *
 * Uses: layouts/header.php, layouts/navbar.php, layouts/footer.php
 */

require_once 'layouts/header.php';

// ── Resolve product ──────────────────────────────────────────────────────────
$product = null;

if (!empty($_GET['slug'])) {
    $stmt = $pdo->prepare(
        "SELECT p.*, c.category_name, c.slug AS cat_slug,
                sc.subcategory_name, sc.slug AS sub_slug
           FROM products p
           JOIN categories c ON c.id = p.category_id
           LEFT JOIN subcategories sc ON sc.id = p.subcategory_id
          WHERE p.slug = :slug AND p.product_status = 'Active'
          LIMIT 1"
    );
    $stmt->execute([':slug' => trim($_GET['slug'])]);
    $product = $stmt->fetch();
} elseif (!empty($_GET['id']) && ctype_digit((string)$_GET['id'])) {
    $stmt = $pdo->prepare(
        "SELECT p.*, c.category_name, c.slug AS cat_slug,
                sc.subcategory_name, sc.slug AS sub_slug
           FROM products p
           JOIN categories c ON c.id = p.category_id
           LEFT JOIN subcategories sc ON sc.id = p.subcategory_id
          WHERE p.id = :id AND p.product_status = 'Active'
          LIMIT 1"
    );
    $stmt->execute([':id' => (int)$_GET['id']]);
    $product = $stmt->fetch();
}

// ── 404 guard ────────────────────────────────────────────────────────────────
if (!$product) {
    http_response_code(404);
    echo '<title>Product Not Found – Furniture Market</title>';
    require_once 'layouts/navbar.php';
    echo '
    <div class="container py-5 text-center" style="min-height:40vh;display:flex;flex-direction:column;align-items:center;justify-content:center;">
      <i class="bi bi-exclamation-circle" style="font-size:52px;color:var(--accent);margin-bottom:16px;"></i>
      <h2 style="color:var(--heading);">Product Not Found</h2>
      <p class="text-muted">The product you are looking for does not exist or is no longer available.</p>
      <a href="index.php" class="btn btn-primary-fm mt-3">Back to Home</a>
    </div>';
    require_once 'layouts/footer.php';
    exit;
}

$productId = (int)$product['id'];

// ── Fetch related data ───────────────────────────────────────────────────────
// Product gallery images (up to 10)
$imgStmt = $pdo->prepare(
    "SELECT image, alt_text, sort_order, is_thumbnail
       FROM product_images
      WHERE product_id = :pid
      ORDER BY is_thumbnail DESC, sort_order ASC, id ASC
      LIMIT 10"
);
$imgStmt->execute([':pid' => $productId]);
$productImages = $imgStmt->fetchAll();

// Build gallery array; fall back to thumbnail column
$galleryImages = [];
foreach ($productImages as $img) {
    if (!empty($img['image'])) {
        $galleryImages[] = [
            'src' => imgSrc($img['image'], 'assets/img/product-sofa.jpg'),
            'alt' => !empty($img['alt_text']) ? $img['alt_text'] : $product['product_name'],
        ];
    }
}
if (empty($galleryImages) && !empty($product['thumbnail'])) {
    $galleryImages[] = [
        'src' => imgSrc($product['thumbnail'], 'assets/img/product-sofa.jpg'),
        'alt' => $product['product_name'],
    ];
}
if (empty($galleryImages)) {
    $galleryImages[] = [
        'src' => 'assets/img/product-sofa.jpg',
        'alt' => $product['product_name'],
    ];
}

// Specifications
$specStmt = $pdo->prepare(
    "SELECT specification_name, specification_value
       FROM product_specifications
      WHERE product_id = :pid
      ORDER BY id ASC"
);
$specStmt->execute([':pid' => $productId]);
$specifications = $specStmt->fetchAll();

// Reviews (product_reviews table — created by updated SQL)
try {
    $revStmt = $pdo->prepare(
        "SELECT * FROM product_reviews
          WHERE product_id = :pid AND status = 1
          ORDER BY sort_order ASC, id DESC
          LIMIT 20"
    );
    $revStmt->execute([':pid' => $productId]);
    $reviews = $revStmt->fetchAll();
} catch (PDOException $e) {
    $reviews = [];
}

// FAQs (product_faqs table — common for all products)
try {
    $faqRows = $pdo->query(
        "SELECT question, answer FROM product_faqs
          WHERE status = 1
          ORDER BY sort_order ASC, id ASC"
    )->fetchAll();
} catch (PDOException $e) {
    $faqRows = [];
}
// Static fallback FAQs if table empty / not yet created
if (empty($faqRows)) {
    $faqRows = [
        ['question' => 'What is the weight capacity of this product?',
         'answer'   => 'Each product is designed to meet standard weight requirements. Please refer to the specifications section for exact details, or contact our team for clarification.'],
        ['question' => 'Is assembly required?',
         'answer'   => 'Minimal assembly is required for most products. All necessary hardware and a step-by-step instruction manual are included in the package.'],
        ['question' => 'Can I customise the colour or fabric?',
         'answer'   => 'Yes. We offer customisation in size, material, fabric, and finish. Share your requirements and our design team will assist you within 24 hours.'],
        ['question' => 'What is the warranty period?',
         'answer'   => 'All our products carry a minimum 1-year warranty against manufacturing defects. Premium collections include extended coverage of up to 5 years.'],
        ['question' => 'How do I clean and maintain the furniture?',
         'answer'   => 'Wipe with a clean, dry cloth. Avoid harsh chemicals or abrasive cleaners. For upholstered products, use a soft brush vacuum attachment and spot-clean stains immediately.'],
        ['question' => 'Do you offer bulk / corporate pricing?',
         'answer'   => 'Yes. Special pricing and dedicated project managers are available for bulk orders of 5+ pieces. Contact our Bulk Orders team for a custom quote.'],
    ];
}

// Contact details for action buttons
$contactRows = $pdo->query(
    "SELECT type, value FROM contact_details WHERE status = 1 ORDER BY sort_order ASC"
)->fetchAll();
$phone = '';
foreach ($contactRows as $cr) {
    if ($cr['type'] === 'mobile' && $phone === '') $phone = $cr['value'];
}
$whatsappNum = preg_replace('/[^0-9]/', '', $phone); // digits only for wa.me link

// ── Pricing maths ─────────────────────────────────────────────────────────────
$salePrice = (float)($product['sale_price'] ?? 0);
$regPrice  = (float)($product['regular_price'] ?? 0);
$price     = ($salePrice > 0 && $salePrice < $regPrice) ? $salePrice : $regPrice;
$oldPrice  = ($salePrice > 0 && $salePrice < $regPrice) ? $regPrice : null;
$discount  = null;
if ($oldPrice && $price < $oldPrice) {
    $discount = (int)round((($oldPrice - $price) / $oldPrice) * 100);
}
$emiMonthly = $price > 0 ? (int)round($price / 12) : 0;

// ── Product tags ──────────────────────────────────────────────────────────────
$tags = [];
if (!empty($product['is_best_seller'])) $tags[] = ['label' => 'Best Seller', 'cls' => 'tag-best-seller'];
if (!empty($product['is_featured']))    $tags[] = ['label' => 'Premium',     'cls' => 'tag-premium'];
if (!empty($product['is_new_arrival'])) $tags[] = ['label' => 'New Arrival', 'cls' => 'tag-new-arrival'];

// ── Average rating ────────────────────────────────────────────────────────────
$totalRev  = count($reviews);
$avgRating = 0;
if ($totalRev > 0) {
    $avgRating = round(array_sum(array_column($reviews, 'stars')) / $totalRev, 1);
}

// ── Dimension columns (added by updated SQL; safe fallback) ───────────────────
$dimWidth  = $product['width']       ?? null;
$dimHeight = $product['height']      ?? null;
$dimDepth  = $product['depth']       ?? null;
$dimSeatH  = $product['seat_height'] ?? null;
$dimImage  = $product['dimension_image'] ?? null;
$hasDims   = ($dimWidth || $dimHeight || $dimDepth || $dimSeatH);

// ── Offer cards ───────────────────────────────────────────────────────────────
$offerCards = [
    ['icon' => 'bi-truck',             'title' => 'Free Delivery',    'desc' => 'Free delivery on all orders above ₹10,000',           'note' => 'T&C Apply'],
    ['icon' => 'bi-percent',           'title' => 'Flat 10% Off',     'desc' => 'Flat 10% off on all prepaid orders',                 'note' => 'T&C Apply'],
    ['icon' => 'bi-shield-check',      'title' => '5 Years Warranty', 'desc' => 'Comes with 5 years manufacturing warranty',          'note' => 'T&C Apply'],
    ['icon' => 'bi-arrow-return-left', 'title' => 'Easy Returns',     'desc' => 'Hassle-free returns within 7 days',                 'note' => 'T&C Apply'],
    ['icon' => 'bi-credit-card',       'title' => 'Easy EMI',         'desc' => 'No-cost EMI starting ₹' . number_format($emiMonthly) . '/month', 'note' => 'T&C Apply'],
    ['icon' => 'bi-people',            'title' => 'Bulk Order',       'desc' => 'Special pricing for bulk & corporate orders',        'note' => 'T&C Apply'],
];

// ── WhatsApp message ──────────────────────────────────────────────────────────
$waMsg = urlencode('Hi, I am interested in: ' . $product['product_name']
    . (!empty($product['sku']) ? ' (SKU: ' . $product['sku'] . ')' : '')
    . '. Please share more details.');
?>
<title><?= htmlspecialchars(!empty($product['seo_title']) ? $product['seo_title'] : $product['product_name'] . ' – Furniture Market') ?></title>
<meta name="description" content="<?= htmlspecialchars(!empty($product['seo_description']) ? $product['seo_description'] : ($product['short_description'] ?? $product['product_name'])) ?>">
<?php if (!empty($product['seo_keywords'])): ?>
<meta name="keywords" content="<?= htmlspecialchars($product['seo_keywords']) ?>">
<?php endif; ?>
<link rel="stylesheet" href="css/product.css">
<?php require_once 'layouts/navbar.php'; ?>

<!-- ═══════════════════════════════════════════════════════
     LIGHTBOX OVERLAY
     ═══════════════════════════════════════════════════════ -->
<div class="lightbox-overlay" id="lightbox" role="dialog" aria-modal="true" aria-label="Product image lightbox">
  <button class="lightbox-close" id="lightboxClose" aria-label="Close lightbox">&times;</button>
  <button class="lightbox-nav lightbox-nav-prev" id="lightboxPrev" aria-label="Previous image"><i class="bi bi-chevron-left"></i></button>
  <img src="" alt="" class="lightbox-img" id="lightboxImg">
  <button class="lightbox-nav lightbox-nav-next" id="lightboxNext" aria-label="Next image"><i class="bi bi-chevron-right"></i></button>
</div>

<!-- ═══════════════════════════════════════════════════════
     ENQUIRY MODAL
     ═══════════════════════════════════════════════════════ -->
<div class="modal fade" id="enquiryModal" tabindex="-1" aria-labelledby="enquiryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content enquiry-modal-content">
      <div class="modal-header enquiry-modal-header">
        <h5 class="modal-title" id="enquiryModalLabel">
          <i class="bi bi-envelope me-2"></i>Enquire About This Product
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="enquiry-product-ref mb-4">
          <img src="<?= htmlspecialchars($galleryImages[0]['src']) ?>"
               alt="<?= htmlspecialchars($product['product_name']) ?>"
               class="enquiry-product-img">
          <div>
            <p class="enquiry-product-name"><?= htmlspecialchars($product['product_name']) ?></p>
            <?php if (!empty($product['sku'])): ?>
              <p class="enquiry-product-sku">SKU: <?= htmlspecialchars($product['sku']) ?></p>
            <?php endif; ?>
          </div>
        </div>
        <form id="enquiryForm">
          <input type="hidden" name="product_id" value="<?= $productId ?>">
          <input type="hidden" name="product_name" value="<?= htmlspecialchars($product['product_name']) ?>">
          <div class="mb-3">
            <input type="text" class="form-control inquiry-input" name="name" placeholder="Your Name *" required>
          </div>
          <div class="mb-3">
            <input type="tel" class="form-control inquiry-input" name="phone" placeholder="Phone Number *" required>
          </div>
          <div class="mb-3">
            <input type="email" class="form-control inquiry-input" name="email" placeholder="Email Address">
          </div>
          <div class="mb-4">
            <textarea class="form-control inquiry-input" name="message" rows="3"
                      placeholder="Your Message"><?= htmlspecialchars('I am interested in: ' . $product['product_name']) ?></textarea>
          </div>
          <button type="submit" class="btn btn-accent w-100 py-3" id="enquirySubmitBtn">
            <i class="bi bi-send me-2"></i>Send Enquiry
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     PRODUCT PAGE
     ═══════════════════════════════════════════════════════ -->
<div class="product-page">
  <div class="container-fluid px-4">

    <!-- BREADCRUMB ─────────────────────────────────────── -->
    <nav class="product-breadcrumb" aria-label="breadcrumb">
      <a href="index.php">Home</a>
      <span class="bc-sep">›</span>
      <a href="category.php?slug=<?= htmlspecialchars($product['cat_slug']) ?>"><?= htmlspecialchars($product['category_name']) ?></a>
      <?php if (!empty($product['subcategory_name'])): ?>
        <span class="bc-sep">›</span>
        <a href="category.php?slug=<?= htmlspecialchars($product['cat_slug']) ?>&sub=<?= htmlspecialchars($product['sub_slug'] ?? '') ?>">
          <?= htmlspecialchars($product['subcategory_name']) ?>
        </a>
      <?php endif; ?>
      <span class="bc-sep">›</span>
      <span class="bc-current"><?= htmlspecialchars($product['product_name']) ?></span>
    </nav>

    <!-- MAIN PRODUCT SECTION ───────────────────────────── -->
    <div class="row g-4 align-items-start mb-0">

      <!-- LEFT: GALLERY -->
      <div class="col-lg-6 col-md-12">
        <div class="product-gallery-wrap">

          <!-- Thumbnail column -->
          <div class="gallery-thumb-col" id="galleryThumbCol">
            <button class="gallery-thumb-nav-btn" id="thumbScrollUp" aria-label="Scroll up">
              <i class="bi bi-chevron-up"></i>
            </button>
            <div class="gallery-thumbs-viewport">
              <div class="gallery-thumbs" id="galleryThumbs">
                <?php foreach ($galleryImages as $gi => $gImg): ?>
                  <button class="gallery-thumb <?= $gi === 0 ? 'active' : '' ?>"
                          data-index="<?= $gi ?>"
                          data-src="<?= htmlspecialchars($gImg['src']) ?>"
                          aria-label="View image <?= $gi + 1 ?>">
                    <img src="<?= htmlspecialchars($gImg['src']) ?>"
                         alt="<?= htmlspecialchars($gImg['alt']) ?>"
                         loading="<?= $gi < 3 ? 'eager' : 'lazy' ?>">
                  </button>
                <?php endforeach; ?>
              </div>
            </div>
            <button class="gallery-thumb-nav-btn" id="thumbScrollDown" aria-label="Scroll down">
              <i class="bi bi-chevron-down"></i>
            </button>
          </div>

          <!-- Main image -->
          <div class="gallery-main-wrap">
            <div class="gallery-main" id="galleryMain">
              <img src="<?= htmlspecialchars($galleryImages[0]['src']) ?>"
                   alt="<?= htmlspecialchars($galleryImages[0]['alt']) ?>"
                   id="galleryMainImg"
                   loading="eager">
              <button class="gallery-zoom-btn" id="galleryZoomBtn" aria-label="Zoom image">
                <i class="bi bi-zoom-in"></i>
              </button>
              <?php if (count($galleryImages) > 1): ?>
                <button class="gallery-main-nav gallery-main-prev" id="galleryPrev" aria-label="Previous image">
                  <i class="bi bi-chevron-left"></i>
                </button>
                <button class="gallery-main-nav gallery-main-next" id="galleryNext" aria-label="Next image">
                  <i class="bi bi-chevron-right"></i>
                </button>
              <?php endif; ?>
            </div>
            <p class="gallery-hint d-none d-md-block">Click image to zoom &middot; <?= count($galleryImages) ?> photo<?= count($galleryImages) !== 1 ? 's' : '' ?></p>
          </div>

        </div>
      </div>

      <!-- RIGHT: PRODUCT INFO -->
      <div class="col-lg-6 col-md-12">
        <div class="product-info">

          <!-- Tags -->
          <?php if (!empty($tags)): ?>
            <div class="product-tags mb-3">
              <?php foreach ($tags as $tag): ?>
                <span class="product-tag <?= $tag['cls'] ?>"><?= htmlspecialchars($tag['label']) ?></span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <!-- Title -->
          <h1 class="product-title"><?= htmlspecialchars($product['product_name']) ?></h1>

          <!-- Rating -->
          <?php if ($totalRev > 0): ?>
            <div class="product-rating mb-2">
              <div class="rating-stars">
                <?php for ($s = 1; $s <= 5; $s++):
                  if ($s <= floor($avgRating)) $starClass = 'bi-star-fill';
                  elseif ($s - 0.5 <= $avgRating) $starClass = 'bi-star-half';
                  else $starClass = 'bi-star';
                ?>
                  <i class="bi <?= $starClass ?>"></i>
                <?php endfor; ?>
              </div>
              <span class="rating-score"><?= $avgRating ?></span>
              <a href="#section-reviews" class="rating-count">(<?= $totalRev ?> Review<?= $totalRev !== 1 ? 's' : '' ?>)</a>
            </div>
          <?php endif; ?>

          <!-- SKU / Brand line -->
          <p class="product-meta-line mb-3">
            <?php if (!empty($product['sku'])): ?>
              <span class="pm-item">SKU: <strong><?= htmlspecialchars($product['sku']) ?></strong></span>
              <span class="pm-sep">|</span>
            <?php endif; ?>
            <span class="pm-item">Brand: <strong>Furniture Market</strong></span>
          </p>

          <!-- Price block -->
          <div class="product-price-block mb-1">
            <span class="price-current">&#8377;<?= number_format($price) ?></span>
            <?php if ($oldPrice): ?>
              <span class="price-old">&#8377;<?= number_format((int)$oldPrice) ?></span>
              <?php if ($discount): ?>
                <span class="price-off-badge"><?= $discount ?>% OFF</span>
              <?php endif; ?>
            <?php endif; ?>
          </div>
          <p class="price-gst-note">Inclusive of all taxes</p>

          <!-- Stock status -->
          <?php
          $stock = (int)($product['stock_quantity'] ?? 0);
          if ($stock > 5)       { $stockDot = 'dot-green'; $stockTxt = 'In Stock';     $stockExtra = ''; }
          elseif ($stock > 0)   { $stockDot = 'dot-amber'; $stockTxt = 'In Stock';     $stockExtra = 'Only ' . $stock . ' left'; }
          else                  { $stockDot = 'dot-red';   $stockTxt = 'Out of Stock'; $stockExtra = ''; }
          ?>
          <div class="product-stock-row mb-3">
            <span class="stock-dot <?= $stockDot ?>"></span>
            <span class="stock-label"><?= $stockTxt ?></span>
            <?php if ($stockExtra): ?>
              <span class="stock-extra">— <?= $stockExtra ?></span>
            <?php endif; ?>
          </div>

          <!-- Short description -->
          <?php if (!empty($product['short_description'])): ?>
            <p class="product-short-desc mb-3"><?= nl2br(htmlspecialchars($product['short_description'])) ?></p>
          <?php endif; ?>

          <!-- Product key attributes -->
          <div class="product-attrs mb-3">
            <?php if (!empty($product['category_name'])): ?>
              <div class="attr-row">
                <span class="attr-label">Category:</span>
                <a href="category.php?slug=<?= htmlspecialchars($product['cat_slug']) ?>" class="attr-value attr-link">
                  <?= htmlspecialchars($product['category_name']) ?>
                </a>
              </div>
            <?php endif; ?>
            <?php if (!empty($product['subcategory_name'])): ?>
              <div class="attr-row">
                <span class="attr-label">Type:</span>
                <a href="category.php?slug=<?= htmlspecialchars($product['cat_slug']) ?>&sub=<?= htmlspecialchars($product['sub_slug'] ?? '') ?>" class="attr-value attr-link">
                  <?= htmlspecialchars($product['subcategory_name']) ?>
                </a>
              </div>
            <?php endif; ?>
            <?php
            // Show key specs as product attributes
            $highlightSpecs = ['seating capacity','frame material','upholstery material','foam type','leg material','leg color','style','assembly required','assembly provided','warranty','suitable for'];
            foreach ($specifications as $sp):
                if (!empty($sp['specification_name']) && !empty($sp['specification_value'])) {
                    $nameLC = strtolower(trim($sp['specification_name']));
                    if (in_array($nameLC, $highlightSpecs)):
            ?>
              <div class="attr-row">
                <span class="attr-label"><?= htmlspecialchars($sp['specification_name']) ?>:</span>
                <span class="attr-value"><?= htmlspecialchars($sp['specification_value']) ?></span>
              </div>
            <?php   endif; } endforeach; ?>
          </div>

          <!-- Action Buttons -->
          <div class="product-actions mb-4">
            <button class="btn-action btn-enquire" data-bs-toggle="modal" data-bs-target="#enquiryModal">
              <i class="bi bi-envelope"></i> Enquiry
            </button>
            <?php if ($whatsappNum): ?>
              <a href="https://wa.me/91<?= $whatsappNum ?>?text=<?= $waMsg ?>"
                 class="btn-action btn-whatsapp" target="_blank" rel="noopener noreferrer">
                <i class="bi bi-whatsapp"></i> WhatsApp
              </a>
            <?php endif; ?>
            <?php if ($phone): ?>
              <a href="tel:<?= preg_replace('/[^0-9+]/', '', $phone) ?>" class="btn-action btn-callnow">
                <i class="bi bi-telephone-fill"></i> Call Now
              </a>
            <?php endif; ?>
          </div>

        </div><!-- /product-info -->
      </div><!-- /col -->
    </div><!-- /row -->

  </div><!-- /container -->

  <!-- ═══════════════════════════════════════════════════════
       TRUST STRIP
       ═══════════════════════════════════════════════════════ -->
  <div class="product-trust-strip">
    <div class="container-fluid px-4">
      <div class="row g-3">
        <?php
        $trustItems = [
            ['bi-truck',            'Free Delivery',     '3-7 Business Days'],
            ['bi-arrow-return-left','Easy Returns',      'Within 7 Days'],
            ['bi-shield-check',     '5 Years Warranty',  'On Manufacturing Defects'],
            ['bi-credit-card',      'Secure Payments',   '100% Protected'],
            ['bi-headset',          'Dedicated Support', '24/7 Customer Support'],
        ];
        foreach ($trustItems as $ti): ?>
          <div class="col-6 col-md-4 col-lg">
            <div class="ts-item">
              <div class="ts-icon"><i class="bi <?= $ti[0] ?>"></i></div>
              <div>
                <span class="ts-label"><?= $ti[1] ?></span>
                <span class="ts-sub"><?= $ti[2] ?></span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- ═══════════════════════════════════════════════════════
       EXCLUSIVE OFFERS
       ═══════════════════════════════════════════════════════ -->
  <section class="section-excl-offers">
    <div class="container-fluid px-4">
      <h2 class="section-title mb-4">Exclusive Offers</h2>
      <div class="excl-offers-outer">
        <button class="slider-arrow excl-arrow excl-arrow-prev" id="offersPrev" aria-label="Previous offers">
          <i class="bi bi-arrow-left"></i>
        </button>
        <div class="excl-offers-viewport">
          <div class="excl-offers-track" id="offersTrack">
            <?php foreach ($offerCards as $oc): ?>
              <div class="excl-offer-card">
                <i class="bi <?= $oc['icon'] ?> excl-offer-icon"></i>
                <h5 class="excl-offer-title"><?= htmlspecialchars($oc['title']) ?></h5>
                <p class="excl-offer-desc"><?= htmlspecialchars($oc['desc']) ?></p>
                <span class="excl-offer-note"><?= htmlspecialchars($oc['note']) ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <button class="slider-arrow excl-arrow excl-arrow-next" id="offersNext" aria-label="Next offers">
          <i class="bi bi-arrow-right"></i>
        </button>
      </div>
    </div>
  </section>

  <!-- ═══════════════════════════════════════════════════════
       SPECIFICATIONS + DIMENSIONS (6 + 6)
       ═══════════════════════════════════════════════════════ -->
  <section class="section-spec-dims">
    <div class="container-fluid px-4">
      <div class="row g-5">

        <!-- Specifications (col-6) -->
        <div class="col-lg-6">
          <h3 class="spec-dims-heading">Specifications</h3>
          <?php if (!empty($specifications)): ?>
            <table class="spec-table">
              <tbody>
                <?php foreach ($specifications as $sp):
                  if (empty($sp['specification_name']) || empty($sp['specification_value'])) continue;
                ?>
                  <tr>
                    <td class="spec-label"><?= htmlspecialchars($sp['specification_name']) ?></td>
                    <td class="spec-value"><?= htmlspecialchars($sp['specification_value']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php else: ?>
            <p class="text-muted" style="font-size:13.5px;">Detailed specifications will be updated soon. Please contact us for more information.</p>
          <?php endif; ?>
        </div>

        <!-- Dimensions (col-6) -->
        <div class="col-lg-6">
          <h3 class="spec-dims-heading">Dimensions</h3>
          <?php if ($hasDims || $dimImage): ?>
            <!-- Dimension diagram -->
            <div class="dim-diagram-box">
              <?php if ($dimImage): ?>
                <img src="<?= htmlspecialchars(imgSrc($dimImage)) ?>"
                     alt="<?= htmlspecialchars($product['product_name']) ?> dimensions"
                     class="dim-diagram-img">
              <?php else: ?>
                <!-- Inline SVG placeholder diagram -->
                <div class="dim-svg-wrap">
                  <svg viewBox="0 0 320 160" xmlns="http://www.w3.org/2000/svg" class="dim-svg">
                    <!-- Sofa silhouette -->
                    <rect x="20" y="70" width="280" height="70" rx="6" fill="#f0ede8" stroke="#c4b89a" stroke-width="1.5"/>
                    <rect x="20" y="45" width="280" height="30" rx="5" fill="#e8e0d0" stroke="#c4b89a" stroke-width="1.5"/>
                    <rect x="20" y="55" width="28" height="85" rx="5" fill="#dfd6c4" stroke="#c4b89a" stroke-width="1.5"/>
                    <rect x="272" y="55" width="28" height="85" rx="5" fill="#dfd6c4" stroke="#c4b89a" stroke-width="1.5"/>
                    <!-- Width arrow -->
                    <?php if ($dimWidth): ?>
                      <line x1="20" y1="148" x2="300" y2="148" stroke="#b67c43" stroke-width="1.2" marker-start="url(#arr)" marker-end="url(#arr)"/>
                      <text x="160" y="158" text-anchor="middle" font-size="10" fill="#b67c43" font-family="sans-serif"><?= $dimWidth ?> cm</text>
                    <?php endif; ?>
                    <!-- Height arrow -->
                    <?php if ($dimHeight): ?>
                      <line x1="308" y1="45" x2="308" y2="140" stroke="#b67c43" stroke-width="1.2"/>
                      <text x="316" y="95" text-anchor="middle" font-size="10" fill="#b67c43" font-family="sans-serif" transform="rotate(90,316,95)"><?= $dimHeight ?> cm</text>
                    <?php endif; ?>
                    <defs>
                      <marker id="arr" markerWidth="6" markerHeight="6" refX="3" refY="3" orient="auto">
                        <path d="M0,0 L6,3 L0,6 Z" fill="#b67c43"/>
                      </marker>
                    </defs>
                  </svg>
                </div>
              <?php endif; ?>
            </div>
            <!-- Measurement boxes -->
            <div class="dim-boxes">
              <?php if ($dimWidth): ?>
                <div class="dim-box">
                  <span class="dim-box-lbl">Width</span>
                  <span class="dim-box-val"><?= htmlspecialchars((string)$dimWidth) ?> cm</span>
                </div>
              <?php endif; ?>
              <?php if ($dimDepth): ?>
                <div class="dim-box">
                  <span class="dim-box-lbl">Depth</span>
                  <span class="dim-box-val"><?= htmlspecialchars((string)$dimDepth) ?> cm</span>
                </div>
              <?php endif; ?>
              <?php if ($dimHeight): ?>
                <div class="dim-box">
                  <span class="dim-box-lbl">Height</span>
                  <span class="dim-box-val"><?= htmlspecialchars((string)$dimHeight) ?> cm</span>
                </div>
              <?php endif; ?>
              <?php if ($dimSeatH): ?>
                <div class="dim-box">
                  <span class="dim-box-lbl">Seat Height</span>
                  <span class="dim-box-val"><?= htmlspecialchars((string)$dimSeatH) ?> cm</span>
                </div>
              <?php endif; ?>
            </div>
            <div class="dim-notes mt-2">
              <p><i class="bi bi-info-circle me-1"></i>All dimensions are in centimetres (cm)</p>
              <p><i class="bi bi-info-circle me-1"></i>Dimensions may vary by ±2–3 cm</p>
            </div>
          <?php else: ?>
            <p class="text-muted" style="font-size:13.5px;">Dimension information will be updated soon. Please contact us for exact measurements.</p>
          <?php endif; ?>
        </div>

      </div><!-- /row -->
    </div>
  </section>

  <!-- ═══════════════════════════════════════════════════════
       ABOUT PRODUCT (col-12)
       ═══════════════════════════════════════════════════════ -->
  <?php if (!empty($product['description']) || !empty($product['features'])): ?>
  <section class="section-about-product">
    <div class="container-fluid px-4">
      <div class="row">
        <div class="col-12">
          <h3 class="spec-dims-heading mb-4">About This Product</h3>
          <?php if (!empty($product['description'])): ?>
            <div class="about-desc"><?= $product['description'] ?></div>
          <?php endif; ?>
          <?php
          if (!empty($product['features'])) {
              $featureLines = array_filter(array_map('trim', preg_split('/\r?\n/', $product['features'])));
              if (!empty($featureLines)):
          ?>
            <ul class="about-checklist mt-3">
              <?php foreach ($featureLines as $fl): ?>
                <li><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($fl) ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; } ?>
        </div>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- ═══════════════════════════════════════════════════════
       CUSTOMER REVIEWS
       ═══════════════════════════════════════════════════════ -->
  <section class="section-reviews" id="section-reviews">
    <div class="container-fluid px-4">
      <div class="reviews-top mb-4">
        <h3 class="section-title mb-0">Customer Reviews</h3>
        <?php if ($totalRev > 0): ?>
          <div class="reviews-summary">
            <span class="reviews-avg-big"><?= $avgRating ?></span>
            <div>
              <div class="rating-stars reviews-avg-stars">
                <?php for ($s = 1; $s <= 5; $s++): ?>
                  <i class="bi bi-star<?= $s <= floor($avgRating) ? '-fill' : ($s - 0.5 <= $avgRating ? '-half' : '') ?>"></i>
                <?php endfor; ?>
              </div>
              <span style="font-size:12.5px;color:var(--body);"><?= $totalRev ?> review<?= $totalRev !== 1 ? 's' : '' ?></span>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <?php if (!empty($reviews)): ?>
        <div class="reviews-slider-outer">
          <button class="slider-arrow reviews-arrow-btn reviews-prev" id="reviewsPrev" aria-label="Previous reviews">
            <i class="bi bi-arrow-left"></i>
          </button>
          <div class="reviews-viewport">
            <div class="reviews-track" id="reviewsTrack">
              <?php foreach ($reviews as $rv):
                $rvStars  = max(1, min(5, (int)($rv['stars'] ?? 5)));
                $rvAvatar = !empty($rv['avatar']) ? imgSrc($rv['avatar']) : '';
                $rvInitial = strtoupper(substr($rv['reviewer_name'] ?? 'A', 0, 1));
                // Review images stored as comma-separated paths
                $rvImgs = [];
                if (!empty($rv['review_images'])) {
                    $rvImgs = array_filter(array_map('trim', explode(',', $rv['review_images'])));
                }
              ?>
                <div class="review-card">
                  <div class="review-author-row">
                    <?php if ($rvAvatar): ?>
                      <img src="<?= htmlspecialchars($rvAvatar) ?>" alt="<?= htmlspecialchars($rv['reviewer_name'] ?? '') ?>" class="review-avatar-img" loading="lazy">
                    <?php else: ?>
                      <div class="review-avatar-initial"><?= $rvInitial ?></div>
                    <?php endif; ?>
                    <div>
                      <div class="review-name"><?= htmlspecialchars($rv['reviewer_name'] ?? 'Customer') ?></div>
                      <div class="review-verified"><i class="bi bi-patch-check-fill me-1"></i>Verified Buyer</div>
                    </div>
                  </div>
                  <div class="review-stars-row">
                    <?php for ($s = 1; $s <= 5; $s++): ?>
                      <i class="bi bi-star<?= $s <= $rvStars ? '-fill' : '' ?>"></i>
                    <?php endfor; ?>
                  </div>
                  <?php if (!empty($rv['review_text'])): ?>
                    <p class="review-text"><?= htmlspecialchars($rv['review_text']) ?></p>
                  <?php endif; ?>
                  <?php if (!empty($rvImgs)): ?>
                    <div class="review-imgs">
                      <?php foreach (array_slice($rvImgs, 0, 4) as $ri): ?>
                        <img src="<?= htmlspecialchars(imgSrc($ri)) ?>" alt="Review photo" loading="lazy">
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
          <button class="slider-arrow reviews-arrow-btn reviews-next" id="reviewsNext" aria-label="Next reviews">
            <i class="bi bi-arrow-right"></i>
          </button>
        </div>
      <?php else: ?>
        <div class="reviews-empty">
          <i class="bi bi-chat-square-text"></i>
          <p>No reviews yet for this product.</p>
          <button class="btn-action btn-enquire" data-bs-toggle="modal" data-bs-target="#enquiryModal">
            <i class="bi bi-envelope me-2"></i>Enquire About This Product
          </button>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- ═══════════════════════════════════════════════════════
       FREQUENTLY ASKED QUESTIONS (common for all products)
       ═══════════════════════════════════════════════════════ -->
  <section class="section-product-faq">
    <div class="container-fluid px-4">
      <div class="faq-top mb-4">
        <h3 class="section-title mb-0">Frequently Asked Questions</h3>
      </div>
      <div class="accordion product-faq-accordion" id="productFaqAcc">
        <?php foreach ($faqRows as $fi => $faq):
          if (empty($faq['question'])) continue;
        ?>
          <div class="accordion-item faq-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed faq-btn" type="button"
                      data-bs-toggle="collapse"
                      data-bs-target="#pfaq<?= $fi ?>"
                      aria-expanded="false">
                <?= htmlspecialchars($faq['question']) ?>
              </button>
            </h2>
            <div id="pfaq<?= $fi ?>" class="accordion-collapse collapse" data-bs-parent="#productFaqAcc">
              <div class="accordion-body faq-answer">
                <?= nl2br(htmlspecialchars($faq['answer'])) ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

</div><!-- /product-page -->

<?php require_once 'layouts/footer.php'; ?>

<script>
/* ════════════════════════════════════════════════════════════
   product.php — inline JS: Gallery, Sliders, Lightbox
   ════════════════════════════════════════════════════════════ */
(function () {
'use strict';

/* ── Gallery data from PHP ──────────────────────────────── */
var IMAGES = <?= json_encode(array_values(array_map(function($g){ return $g['src']; }, $galleryImages)), JSON_UNESCAPED_SLASHES) ?>;
var activeIdx = 0;

var mainImg  = document.getElementById('galleryMainImg');
var thumbBtns = document.querySelectorAll('.gallery-thumb');

function setImage(idx) {
    if (idx < 0 || idx >= IMAGES.length) return;
    activeIdx = idx;
    mainImg.style.opacity = '0';
    setTimeout(function () {
        mainImg.src = IMAGES[idx];
        mainImg.style.opacity = '1';
    }, 140);
    thumbBtns.forEach(function (th, i) {
        th.classList.toggle('active', i === idx);
        // scroll thumb into view
        if (i === idx) { th.scrollIntoView({ behavior:'smooth', block:'nearest' }); }
    });
}

thumbBtns.forEach(function (th) {
    th.addEventListener('click', function () { setImage(parseInt(this.dataset.index)); });
});

// Main image prev/next arrows
var gPrev = document.getElementById('galleryPrev');
var gNext = document.getElementById('galleryNext');
if (gPrev) gPrev.addEventListener('click', function () { setImage((activeIdx - 1 + IMAGES.length) % IMAGES.length); });
if (gNext) gNext.addEventListener('click', function () { setImage((activeIdx + 1) % IMAGES.length); });

// Thumb column scroll
var tUp   = document.getElementById('thumbScrollUp');
var tDown = document.getElementById('thumbScrollDown');
var tvp   = document.querySelector('.gallery-thumbs-viewport');
if (tUp && tvp)   tUp.addEventListener('click',   function () { tvp.scrollBy({ top: -80, behavior:'smooth' }); });
if (tDown && tvp) tDown.addEventListener('click', function () { tvp.scrollBy({ top:  80, behavior:'smooth' }); });

// Mobile swipe on main image
(function () {
    var el = document.getElementById('galleryMain');
    if (!el) return;
    var sx = null;
    el.addEventListener('touchstart', function (e) { sx = e.touches[0].clientX; }, { passive: true });
    el.addEventListener('touchend',   function (e) {
        if (sx === null) return;
        var dx = e.changedTouches[0].clientX - sx;
        if (Math.abs(dx) > 50) { dx < 0 ? setImage((activeIdx + 1) % IMAGES.length) : setImage((activeIdx - 1 + IMAGES.length) % IMAGES.length); }
        sx = null;
    }, { passive: true });
})();

/* ── Lightbox ────────────────────────────────────────────── */
var lightbox   = document.getElementById('lightbox');
var lbImg      = document.getElementById('lightboxImg');
var lbClose    = document.getElementById('lightboxClose');
var lbPrev     = document.getElementById('lightboxPrev');
var lbNext     = document.getElementById('lightboxNext');
var zoomBtn    = document.getElementById('galleryZoomBtn');

function openLightbox(idx) {
    activeIdx = idx;
    lbImg.src = IMAGES[idx];
    lightbox.classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeLightbox() {
    lightbox.classList.remove('active');
    document.body.style.overflow = '';
}

if (zoomBtn) zoomBtn.addEventListener('click', function () { openLightbox(activeIdx); });
if (lbClose) lbClose.addEventListener('click', closeLightbox);
if (lightbox) lightbox.addEventListener('click', function (e) { if (e.target === lightbox) closeLightbox(); });
if (lbPrev) lbPrev.addEventListener('click', function () { openLightbox((activeIdx - 1 + IMAGES.length) % IMAGES.length); });
if (lbNext) lbNext.addEventListener('click', function () { openLightbox((activeIdx + 1) % IMAGES.length); });
document.addEventListener('keydown', function (e) {
    if (!lightbox.classList.contains('active')) return;
    if (e.key === 'Escape')      closeLightbox();
    if (e.key === 'ArrowLeft')   openLightbox((activeIdx - 1 + IMAGES.length) % IMAGES.length);
    if (e.key === 'ArrowRight')  openLightbox((activeIdx + 1) % IMAGES.length);
});

/* ── Generic horizontal slider ────────────────────────────── */
function makeHorizSlider(trackId, prevId, nextId) {
    var track = document.getElementById(trackId);
    var prev  = document.getElementById(prevId);
    var next  = document.getElementById(nextId);
    if (!track) return;
    var idx = 0;

    function cardW() {
        var c = track.querySelector('[class*="card"]') || track.firstElementChild;
        if (!c) return 220;
        return c.getBoundingClientRect().width + (parseFloat(getComputedStyle(track).columnGap) || 16);
    }
    function maxIdx() {
        var vp = track.closest('[class*="viewport"]') || track.parentElement;
        if (!vp) return 0;
        return Math.max(0, Math.ceil((track.scrollWidth - vp.clientWidth) / cardW()));
    }
    function slide() {
        idx = Math.max(0, Math.min(idx, maxIdx()));
        var offset = idx * cardW();
        var vp = track.closest('[class*="viewport"]') || track.parentElement;
        if (vp) offset = Math.min(offset, Math.max(0, track.scrollWidth - vp.clientWidth));
        track.style.transform = 'translate3d(-' + offset + 'px,0,0)';
    }
    if (prev) prev.addEventListener('click', function () { idx = Math.max(0, idx - 1); slide(); });
    if (next) next.addEventListener('click', function () { idx = Math.min(maxIdx(), idx + 1); slide(); });

    // Touch swipe
    var sx = null;
    track.addEventListener('touchstart', function (e) { sx = e.touches[0].clientX; }, { passive: true });
    track.addEventListener('touchend',   function (e) {
        if (sx === null) return;
        var dx = e.changedTouches[0].clientX - sx;
        if (Math.abs(dx) > 50) { dx < 0 ? (idx = Math.min(maxIdx(), idx + 1)) : (idx = Math.max(0, idx - 1)); slide(); }
        sx = null;
    }, { passive: true });
}

makeHorizSlider('offersTrack',  'offersPrev',  'offersNext');
makeHorizSlider('reviewsTrack', 'reviewsPrev', 'reviewsNext');

/* ── Enquiry form ────────────────────────────────────────── */
var enquiryForm = document.getElementById('enquiryForm');
if (enquiryForm) {
    enquiryForm.addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = document.getElementById('enquirySubmitBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Enquiry Sent! We\'ll contact you soon.';
        btn.classList.remove('btn-accent');
        btn.classList.add('btn-success');
    });
}

/* ── Smooth scroll for rating link ─────────────────────── */
document.querySelectorAll('a[href="#section-reviews"]').forEach(function (a) {
    a.addEventListener('click', function (e) {
        e.preventDefault();
        var el = document.getElementById('section-reviews');
        if (el) el.scrollIntoView({ behavior:'smooth' });
    });
});

})();
</script>
