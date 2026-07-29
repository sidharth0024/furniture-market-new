<?php
require_once __DIR__ . '/../includes/fetch-categories.php';
?>
</head>

<body>

  <!-- UTILITY BAR -->
  <div class="utility-bar" id="utilityBar">
    <div class="container-fluid px-4">
      <div class="row align-items-center utility-bar-row">
        <div class="col-auto">
          <div class="utility-left d-flex align-items-center gap-3">
            <span class="utility-item">
              <i class="bi bi-telephone me-1"></i> <?= htmlspecialchars($utilityBar['phone_display']) ?>
            </span>
            <span class="utility-sep">|</span>
            <span class="utility-item">
              <i class="bi bi-envelope me-1"></i> <?= htmlspecialchars($utilityBar['email_display']) ?>
            </span>
            <span class="utility-sep">|</span>
          </div>
        </div>
        <div class="col ms-auto text-end">
          <div class="utility-right d-flex align-items-center justify-content-end gap-3">
            <span class="utility-sep d-lg-inline">|</span>
            <a href="./aboutus.php" class="utility-link d-md-inline">About</a>
            <span class="utility-sep d-md-inline">|</span>
            <a href="./contactus.php" class="utility-link d-md-inline">Contact Us</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- MAIN HEADER -->
  <header class="main-header" id="mainHeader">
    <div class="container-fluid px-4">
      <div class="row align-items-center justify-content-between" style="height:85px;">
        <div class="col-auto">
          <a href="index.php" class="brand-logo gap-2">
            <img src="./assets/img/logo.jpeg" width="200" alt="">
            <p class="mb-0">#CostomizeYourFurniture</p>
          </a>
        </div>
        <div class="col">
          <nav class="primary-nav" id="primaryNav">
            <ul class="nav-list d-none d-lg-flex align-items-center mb-0 ps-0 justify-content-end">
              <?php foreach ($navCategories as $cat):
                $subColumns = splitIntoColumns($cat['subcategories'], 3);
                $hasSubs    = !empty($subColumns);
                if (!empty($cat['image'])) {
                  $raw = $cat['image'];
                  $bannerImg = (str_starts_with($raw, 'http')) ? $raw : 'admin_panel/uploads/category/' . $raw;
                } else {
                  $bannerImg = 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=300&h=200&fit=crop';
                }
              ?>
                <li class="nav-item has-mega">
                  <a class="nav-link-item" href="all_products.php?slug=<?= htmlspecialchars($cat['slug']) ?>">
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
                                  <li><a href="all_products.php?slug=<?= htmlspecialchars($cat['slug']) ?>&sub=<?= htmlspecialchars($sub['slug']) ?>"><?= htmlspecialchars($sub['name']) ?></a></li>
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
                          <a href="all_products.php?slug=<?= htmlspecialchars($cat['slug']) ?>" class="mega-view-all">
                            View All <?= htmlspecialchars($cat['category_name']) ?> &rarr;
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>

            <div class="d-flex align-items-center gap-3 justify-content-end">
              <button class="btn d-lg-none mobile-nav-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav">
                <i class="bi bi-list fs-4"></i>
              </button>
            </div>
          </nav>
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


  <!-- MOBILE OFFCANVAS -->
  <div class="offcanvas offcanvas-start mobile-offcanvas" tabindex="-1" id="mobileNav">
    <div class="offcanvas-header">
      <div class="brand-logo d-flex align-items-center gap-2">
        <img src="./assets/img/logo.jpeg" width="200" alt="">
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
      <div class="accordion accordion-flush" id="mobileNavAccordion">
        <div class="accordion-item border-0 border-bottom">
          <h2 class="accordion-header">
            <a class="accordion-button mobile-accordion-button collapsed px-0 py-3" type="button" href="aboutus.php">
              About
            </a>
          </h2>
        </div>
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
                    <a href="all_products.php?slug=<?= htmlspecialchars($cat['slug']) ?>&sub=<?= htmlspecialchars($sub['slug']) ?>" class="mobile-sub-link d-block py-2"><?= htmlspecialchars($sub['name']) ?></a>
                  <?php endforeach; ?>
                <?php else: ?>
                  <span class="mobile-sub-link d-block py-2 text-muted">No subcategories available yet.</span>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
        <div class="accordion-item border-0 border-bottom">
          <h2 class="accordion-header">
            <a class="accordion-button mobile-accordion-button collapsed px-0 py-3" type="button" href="contactus.php">
              Contact Us
            </a>
          </h2>
        </div>
      </div>

    </div>
  </div>