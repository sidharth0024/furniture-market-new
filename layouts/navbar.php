<?php
/**
 * navbar.php — Utility bar, main header, primary nav with dynamic mega menus.
 * Requires: $pdo (via header.php), includes/fetch-categories.php
 */
require_once __DIR__ . '/../includes/fetch-categories.php';
?>
</head>
<body>

<!-- UTILITY BAR -->
<div class="utility-bar" id="utilityBar">
  <div class="container-fluid px-4">
    <div class="row align-items-center" style="height:38px;">
      <div class="col-auto">
        <div class="utility-left d-flex align-items-center gap-3">
          <span class="utility-item">
            <i class="bi bi-telephone me-1"></i>
            Call us: <?= htmlspecialchars($utilityBar['phone_display']) ?>
          </span>
          <span class="utility-sep">|</span>
          <a href="<?= htmlspecialchars($utilityBar['contact_us_url']) ?>" class="utility-link d-none d-md-inline">Contact Us</a>
          <span class="utility-sep">|</span>
        </div>
      </div>
      <div class="col ms-auto text-end">
        <div class="utility-right d-flex align-items-center justify-content-end gap-3">
          <span class="utility-sep d-none d-lg-inline">|</span>
          <a href="<?= htmlspecialchars($utilityBar['track_order_url']) ?>" class="utility-link d-none d-md-inline">Track Order</a>
          <span class="utility-sep d-none d-md-inline">|</span>
          <a href="<?= htmlspecialchars($utilityBar['bulk_orders_url']) ?>" class="utility-link d-none d-md-inline">Bulk Orders</a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- MAIN HEADER -->
<header class="main-header" id="mainHeader">
  <div class="container-fluid px-4">
    <div class="row align-items-center" style="height:72px;">
      <div class="col-auto">
        <a href="index.php" class="brand-logo d-flex align-items-center text-decoration-none gap-2">
          <div class="brand-icon"><i class="bi bi-house-door-fill"></i></div>
          <div class="brand-text">
            <span class="brand-name">FURNITURE</span>
            <span class="brand-sub">MARKET</span>
          </div>
        </a>
      </div>
      <div class="col d-none d-lg-block px-4">
        <form class="search-form d-flex" role="search" method="get" action="search.php">
          <input type="text" name="q" class="form-control search-input" placeholder="Search for products, categories, brands...">
          <select name="category" class="form-select search-category">
            <option value="">All Categories</option>
            <?php foreach ($navCategories as $cat): ?>
              <option value="<?= htmlspecialchars($cat['slug']) ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
            <?php endforeach; ?>
          </select>
          <button class="btn search-btn" type="submit"><i class="bi bi-search"></i></button>
        </form>
      </div>
      <div class="col-auto ms-auto ms-lg-0">
        <div class="header-actions d-flex align-items-center gap-3">
          <a href="#" class="header-action d-none d-md-flex flex-column align-items-center">
            <i class="bi bi-heart"></i><span>Wishlist</span>
          </a>
          <a href="aboutus.php" class="header-action d-flex flex-column align-items-center">
            <i class="bi bi-person"></i><span>About Us</span>
          </a>
          <button class="btn d-lg-none mobile-search-btn" type="button" data-bs-toggle="collapse" data-bs-target="#mobileSearch">
            <i class="bi bi-search"></i>
          </button>
        </div>
      </div>
    </div>
    <div class="collapse" id="mobileSearch">
      <div class="pb-3">
        <form class="search-form d-flex" role="search" method="get" action="search.php">
          <input type="text" name="q" class="form-control search-input" placeholder="Search products...">
          <button class="btn search-btn" type="submit"><i class="bi bi-search"></i></button>
        </form>
      </div>
    </div>
  </div>
</header>

<!-- PRIMARY NAV with MEGA MENUS -->
<nav class="primary-nav" id="primaryNav">
  <div class="container-fluid px-4">
    <div class="d-flex align-items-center justify-content-between">

      <ul class="nav-list d-none d-lg-flex align-items-center mb-0 ps-0">
        <?php foreach ($navCategories as $cat):
          $subColumns = splitIntoColumns($cat['subcategories'], 3);
          $hasSubs    = !empty($subColumns);
          if (!empty($cat['image'])) {
            $raw = $cat['image'];
            $bannerImg = (str_starts_with($raw,'http')) ? $raw : 'admin_panel/uploads/category/' . $raw;
          } else {
            $bannerImg = 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=300&h=200&fit=crop';
          }
        ?>
          <li class="nav-item has-mega">
            <a class="nav-link-item" href="category.php?slug=<?= htmlspecialchars($cat['slug']) ?>">
              <?= htmlspecialchars($cat['category_name']) ?> <i class="bi bi-chevron-down"></i>
            </a>
            <div class="mega-menu">
              <div class="container-fluid px-4">
                <div class="row py-4">
                  <?php if ($hasSubs): ?>
                    <?php foreach ($subColumns as $column): ?>
                      <div class="col-lg-3 mega-col">
                        <h6 class="mega-heading"><?= htmlspecialchars($cat['category_name']) ?></h6>
                        <ul class="mega-list">
                          <?php foreach ($column as $sub): ?>
                            <li><a href="category.php?slug=<?= htmlspecialchars($cat['slug']) ?>&sub=<?= htmlspecialchars($sub['slug']) ?>"><?= htmlspecialchars($sub['name']) ?></a></li>
                          <?php endforeach; ?>
                        </ul>
                      </div>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <div class="col-lg-9 mega-col">
                      <p class="mb-0 text-muted">No subcategories available yet.</p>
                    </div>
                  <?php endif; ?>
                  <div class="col-lg-3">
                    <div class="mega-banner">
                      <img src="<?= htmlspecialchars($bannerImg) ?>" alt="<?= htmlspecialchars($cat['category_name']) ?>">
                      <div class="mega-banner-text">
                        <span>Trending in</span>
                        <strong><?= htmlspecialchars($cat['category_name']) ?></strong>
                      </div>
                    </div>
                    <a href="category.php?slug=<?= htmlspecialchars($cat['slug']) ?>" class="mega-view-all">
                      View All <?= htmlspecialchars($cat['category_name']) ?> &rarr;
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>

      <div class="d-flex align-items-center gap-3">
        <a href="contactus.php" class="btn btn-offers d-none d-lg-inline-flex">
          <i class="bi bi-tag-fill me-1"></i> Offers
        </a>
        <button class="btn d-lg-none mobile-nav-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav">
          <i class="bi bi-list fs-4"></i>
        </button>
      </div>

    </div>
  </div>
</nav>

<!-- MOBILE OFFCANVAS -->
<div class="offcanvas offcanvas-start mobile-offcanvas" tabindex="-1" id="mobileNav">
  <div class="offcanvas-header">
    <div class="brand-logo d-flex align-items-center gap-2">
      <div class="brand-icon"><i class="bi bi-house-door-fill"></i></div>
      <div class="brand-text"><span class="brand-name">FURNITURE</span><span class="brand-sub">MARKET</span></div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <div class="accordion accordion-flush" id="mobileNavAccordion">
      <?php foreach ($navCategories as $cat): ?>
        <div class="accordion-item border-0 border-bottom">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed px-0 py-3" type="button"
                    data-bs-toggle="collapse" data-bs-target="#mob<?= (int)$cat['id'] ?>">
              <?= htmlspecialchars($cat['category_name']) ?>
            </button>
          </h2>
          <div id="mob<?= (int)$cat['id'] ?>" class="accordion-collapse collapse">
            <div class="accordion-body pt-0 px-0">
              <?php if (!empty($cat['subcategories'])): ?>
                <?php foreach ($cat['subcategories'] as $sub): ?>
                  <a href="category.php?slug=<?= htmlspecialchars($cat['slug']) ?>&sub=<?= htmlspecialchars($sub['slug']) ?>" class="mobile-sub-link d-block py-2"><?= htmlspecialchars($sub['name']) ?></a>
                <?php endforeach; ?>
              <?php else: ?>
                <span class="mobile-sub-link d-block py-2 text-muted">No subcategories available yet.</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <a href="contactus.php" class="btn btn-offers w-100 mt-3"><i class="bi bi-tag-fill me-1"></i> View All Offers</a>
  </div>
</div>
