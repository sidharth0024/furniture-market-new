<?php

/**
 * footer.php — Testimonials, FAQ, Site Footer.
 * Requires: $pdo (from header.php), $_testimonials (from index.php or fetched here)
 */

// Fetch testimonials if not already fetched by calling page
if (!isset($_testimonials)) {
  $_testimonials = $pdo->query(
    "SELECT id, name, company, review, stars, image
           FROM testimonials WHERE status=1 ORDER BY sort_order ASC, id ASC"
  )->fetchAll();
}

// Fetch categories for footer links
$_footerCats = $pdo->query(
  "SELECT category_name, slug FROM categories WHERE status='Active' ORDER BY id ASC LIMIT 6"
)->fetchAll();

// Fetch contact details for footer
$_footerContacts = $pdo->query(
  "SELECT type, value, label, address_line, city, state, pincode
       FROM contact_details WHERE status=1 ORDER BY sort_order ASC"
)->fetchAll();

$_footerPhone   = '';
$_footerEmail   = '';
$_footerAddress = '';
foreach ($_footerContacts as $_fc) {
  if ($_fc['type'] === 'mobile'  && $_footerPhone   === '') $_footerPhone   = $_fc['value'];
  if ($_fc['type'] === 'email'   && $_footerEmail   === '') $_footerEmail   = $_fc['value'];
  if ($_fc['type'] === 'address' && $_footerAddress === '') {
    $parts = array_filter([
      trim($_fc['address_line'] ?? ''),
      trim($_fc['city'] ?? ''),
      trim($_fc['state'] ?? ''),
      trim($_fc['pincode'] ?? ''),
    ]);
    $_footerAddress = implode(', ', $parts);
  }
}
?>

<!-- TESTIMONIALS -->
<section class="section-testimonials section-gap">
  <div class="container px-4">
    <div class="section-header-centered mb-5">
      <p class="section-label">TRUSTED BY BUYERS</p>
      <h2 class="section-title">What Customers Say</h2>
      <p class="section-subtitle">Real projects, real feedback from the people who furnished with us.</p>
    </div>

    <div class="testimonial-slider-wrap position-relative">
      <div class="testimonial-slider" id="testimonialSlider">
        <?php if (!empty($_testimonials)): ?>
          <?php foreach ($_testimonials as $t):
            // Image: could be a local upload path or URL
            if (!empty($t['image'])) {
              $tImg = str_starts_with($t['image'], 'http')
                ? $t['image']
                : $t['image']; // relative path as stored in DB
            } else {
              $tImg = 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=400&h=280&fit=crop';
            }
            $tStars = (int)($t['stars'] ?? 5);
          ?>
            <div class="testimonial-card">
              <div class="testimonial-project-img">
                <img src="<?= htmlspecialchars($tImg) ?>" alt="Project by <?= htmlspecialchars($t['name']) ?>">
              </div>
              <div class="testimonial-body">
                <div class="testimonial-stars mb-2">
                  <?php for ($s = 0; $s < 5; $s++): ?>
                    <i class="bi bi-star-fill<?= $s >= $tStars ? ' text-muted' : '' ?>"></i>
                  <?php endfor; ?>
                </div>
                <p class="testimonial-review">"<?= htmlspecialchars(trim($t['review'], '"')) ?>"</p>
                <div class="testimonial-author d-flex align-items-center gap-2 mt-3">
                  <div class="testimonial-avatar">
                    <?= htmlspecialchars(strtoupper(substr($t['name'], 0, 1))) ?>
                  </div>
                  <div>
                    <strong class="d-block"><?= htmlspecialchars($t['name']) ?></strong>
                    <small><?= htmlspecialchars($t['company'] ?? '') ?></small>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-muted py-4">No testimonials available yet.</p>
        <?php endif; ?>
      </div>
      <button class="slider-arrow slider-arrow-prev" id="testimonialPrev"><i class="bi bi-arrow-left"></i></button>
      <button class="slider-arrow slider-arrow-next" id="testimonialNext"><i class="bi bi-arrow-right"></i></button>
    </div>
  </div>
</section>

<!-- FAQ -->
<section class="section-faq section-gap bg-light-surface">
  <div class="container">
    <div class="section-header-centered mb-5">
      <p class="section-label">NEED TO KNOW</p>
      <h2 class="section-title">Frequently Asked Questions</h2>
    </div>
    <div class="faq-content-wrap">
      <?php
      $faqGroups = [
        'Ordering' => [
          ['How do I place an order?', 'You can browse our catalogue, add items to cart, and complete checkout online. Alternatively, contact our support team for assisted ordering.'],
          ['Can I order in bulk for a project?', 'Yes. We offer special pricing and dedicated project managers for bulk orders. Contact our Bulk Orders team for a custom quote.'],
        ],
        'Delivery' => [
          ['Do you deliver across India?', 'Yes, we deliver PAN India through our trusted logistics partners with real-time tracking.'],
          ['How long does delivery take?', 'Standard delivery takes 7–14 working days. Express delivery (select cities) is available in 3–5 working days.'],
        ],
        'Customization' => [
          ['Can furniture be customized?', 'Absolutely. We offer customization in size, material, fabric, and finish. Share your requirements and our design team will assist you.'],
        ],
        'Warranty' => [
          ['Is there a warranty on products?', 'All products carry a minimum 1-year warranty against manufacturing defects. Premium collections come with up to 5 years coverage.'],
        ],
        'Support' => [
          ['How can I contact support?', 'Reach us via our toll-free number, email, or live chat. We are available Monday–Saturday, 9 AM to 7 PM.'],
        ],
      ];
      $q = 0;
      foreach ($faqGroups as $group => $items):
      ?>
        <div class="faq-group mb-4">
          <p class="faq-group-label"><?= strtoupper(htmlspecialchars($group)) ?></p>
          <div class="accordion" id="faq<?= htmlspecialchars($group) ?>">
            <?php foreach ($items as $item): $q++; ?>
              <div class="accordion-item faq-item">
                <h2 class="accordion-header">
                  <button class="accordion-button collapsed faq-btn" type="button"
                    data-bs-toggle="collapse" data-bs-target="#faqQ<?= $q ?>">
                    <?= htmlspecialchars($item[0]) ?>
                  </button>
                </h2>
                <div id="faqQ<?= $q ?>" class="accordion-collapse collapse">
                  <div class="accordion-body faq-answer"><?= htmlspecialchars($item[1]) ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer class="site-footer">
  <div class="footer-top">
    <div class="container-fluid px-4">
      <div class="row g-5">

        <!-- Brand + Social -->
        <div class="col-lg-3 col-md-6">
          <div class="footer-brand d-flex align-items-center gap-2 mb-3">
            <img src="./assets/img/logo.jpeg" width="100%" alt="">
          </div>
          <p class="footer-desc">A curated marketplace for premium office, home, and contract furniture — connecting architects, designers, and businesses with trusted manufacturers across the country.</p>
          <div class="footer-social d-flex gap-3 mt-4">
            <a href="#" class="social-icon"><i class="bi bi-facebook"></i></a>
            <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
            <a href="#" class="social-icon"><i class="bi bi-linkedin"></i></a>
            <a href="#" class="social-icon"><i class="bi bi-youtube"></i></a>
          </div>
        </div>

        <!-- Company -->
        <div class="col-lg-2 col-md-6 col-6">
          <h6 class="footer-heading">Company</h6>
          <ul class="footer-links">
            <li><a href="aboutus.php">About Us</a></li>
            <li><a href="#">Our Projects</a></li>
            <li><a href="#">Careers</a></li>
            <li><a href="#">Sellers on Furniture Shoppers</a></li>
            <li><a href="contactus.php">Contact</a></li>
          </ul>
        </div>

        <!-- Categories (dynamic) -->
        <div class="col-lg-2 col-md-6 col-6">
          <h6 class="footer-heading">Categories</h6>
          <ul class="footer-links">
            <?php foreach ($_footerCats as $_fc): ?>
              <li><a href="category.php?slug=<?= htmlspecialchars($_fc['slug']) ?>"><?= htmlspecialchars($_fc['category_name']) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>

        <!-- Customer Support -->
        <div class="col-lg-2 col-md-6 col-6">
          <h6 class="footer-heading">Customer Support</h6>
          <ul class="footer-links">
            <li><a href="contactus.php">Contact Us</a></li>
            <li><a href="#">Track Enquiry</a></li>
            <li><a href="#">Delivery Information</a></li>
            <li><a href="#">Warranty</a></li>
            <li><a href="#">FAQs</a></li>
          </ul>
        </div>

        <!-- Contact Info -->
        <div class="col-lg-3 col-md-6 col-6">
          <h6 class="footer-heading">Quick Links</h6>
          <ul class="footer-links mb-4">
            <li><a href="#">Wishlist</a></li>
            <li><a href="#">Shop by Material</a></li>
            <li><a href="#">Offers</a></li>
          </ul>
          <div class="footer-contact">
            <?php if (!empty($_footerAddress)): ?>
              <p class="footer-contact-item">
                <i class="bi bi-geo-alt me-2"></i>
                <?= htmlspecialchars($_footerAddress) ?>
              </p>
            <?php endif; ?>
            <?php if (!empty($_footerPhone)): ?>
              <p class="footer-contact-item">
                <i class="bi bi-telephone me-2"></i>
                <?= htmlspecialchars($_footerPhone) ?>
              </p>
            <?php endif; ?>
            <?php if (!empty($_footerEmail)): ?>
              <p class="footer-contact-item">
                <i class="bi bi-envelope me-2"></i>
                <?= htmlspecialchars($_footerEmail) ?>
              </p>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Footer Bottom -->
  <div class="footer-bottom">
    <div class="container-fluid px-4">
      <div class="row align-items-center">
        <div class="col-md-6">
          <p class="footer-copy mb-0">&copy; <?= date('Y') ?> Furniture Shoppers. All rights reserved.</p>
        </div>
        <div class="col-md-6 text-md-end mt-2 mt-md-0">
          <a href="#" class="footer-legal-link">Privacy Policy</a>
          <span class="mx-2">&middot;</span>
          <a href="#" class="footer-legal-link">Terms of Service</a>
          <span class="mx-2">&middot;</span>
          <a href="#" class="footer-legal-link">Sitemap</a>
        </div>
      </div>
    </div>
  </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Project JS -->
<script src="js/main.js"></script>
<script src="js/pages.js"></script>
</body>

</html>