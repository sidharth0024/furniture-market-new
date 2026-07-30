<?php
/**
 * admin_dashboard.php — Dynamic dashboard with live DB stats.
 */
declare(strict_types=1);
session_start();

if (!isset($_SESSION['adminId'])) {
    header('Location: index.php');
    exit;
}

require __DIR__ . '/includes/db_conn.php';

// ── Live Stats ──────────────────────────────────────────────
$totalCategories  = (int)$pdo->query("SELECT COUNT(*) FROM categories WHERE status='Active'")->fetchColumn();
$totalProducts    = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE product_status='Active'")->fetchColumn();
$totalSubcats     = (int)$pdo->query("SELECT COUNT(*) FROM subcategories WHERE status='Active'")->fetchColumn();
$totalSliders     = (int)$pdo->query("SELECT COUNT(*) FROM sliders WHERE status=1")->fetchColumn();
$totalTestimonials= (int)$pdo->query("SELECT COUNT(*) FROM testimonials WHERE status=1")->fetchColumn();
$featuredProducts = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE is_featured=1 AND product_status='Active'")->fetchColumn();

// Recent products
$recentProducts = $pdo->query(
    "SELECT p.id, p.product_name, p.sale_price, p.regular_price, p.product_status, p.is_featured,
            c.category_name
       FROM products p
       JOIN categories c ON c.id = p.category_id
      ORDER BY p.id DESC LIMIT 8"
)->fetchAll();

// Category breakdown
$catBreakdown = $pdo->query(
    "SELECT c.category_name,
            COUNT(p.id) as total,
            SUM(CASE WHEN p.product_status='Active' THEN 1 ELSE 0 END) as active
       FROM categories c
       LEFT JOIN products p ON p.category_id = c.id
      GROUP BY c.id, c.category_name
      ORDER BY total DESC"
)->fetchAll();

include './layouts/header.php';
?>

<main class="dashboard-content">
  <div class="container-fluid px-3 px-lg-2 py-3">

    <!-- Page heading -->
    <div class="page-heading mb-4">
      <div class="page-heading-copy">
        <span class="page-icon"><i class="bi bi-speedometer2" aria-hidden="true"></i></span>
        <div>
          <p class="eyebrow mb-1">Overview</p>
          <h1 class="h3 mb-1">Furniture Shoppers Dashboard</h1>
          <p class="text-muted mb-0">Live stats — manage Categories, Products, Sliders and Homepage.</p>
        </div>
      </div>
      <div class="heading-actions">
        <a href="../index.php" target="_blank" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-eye me-1"></i>View Site
        </a>
        <a href="homepage.php" class="btn btn-primary btn-sm ms-2">
          <i class="bi bi-house-door me-1"></i>Homepage
        </a>
      </div>
    </div>

    <!-- Metric cards (live DB data) -->
    <section class="row g-3 mt-1" aria-label="Dashboard metrics">
      <div class="col-12 col-sm-6 col-xl-3">
        <article class="metric-card metric-primary">
          <div class="metric-top">
            <span class="metric-label">Active Categories</span>
            <span class="metric-icon"><i class="bi bi-grid" aria-hidden="true"></i></span>
          </div>
          <div class="metric-value"><?= $totalCategories ?></div>
          <div class="metric-meta">
            <span><?= $totalSubcats ?> subcategories</span>
          </div>
        </article>
      </div>

      <div class="col-12 col-sm-6 col-xl-3">
        <article class="metric-card metric-success">
          <div class="metric-top">
            <span class="metric-label">Active Products</span>
            <span class="metric-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span>
          </div>
          <div class="metric-value"><?= number_format($totalProducts) ?></div>
          <div class="metric-meta">
            <span class="text-warning"><i class="bi bi-star-fill"></i> <?= $featuredProducts ?> featured</span>
          </div>
        </article>
      </div>

      <div class="col-12 col-sm-6 col-xl-3">
        <article class="metric-card metric-warning">
          <div class="metric-top">
            <span class="metric-label">Hero Sliders</span>
            <span class="metric-icon"><i class="bi bi-images" aria-hidden="true"></i></span>
          </div>
          <div class="metric-value"><?= $totalSliders ?></div>
          <div class="metric-meta">
            <a href="slider_management.php">Manage sliders &rarr;</a>
          </div>
        </article>
      </div>

      <div class="col-12 col-sm-6 col-xl-3">
        <article class="metric-card metric-danger">
          <div class="metric-top">
            <span class="metric-label">Testimonials</span>
            <span class="metric-icon"><i class="bi bi-chat-quote" aria-hidden="true"></i></span>
          </div>
          <div class="metric-value"><?= $totalTestimonials ?></div>
          <div class="metric-meta">
            <a href="homepage.php">Manage homepage &rarr;</a>
          </div>
        </article>
      </div>
    </section>

    <!-- Recent Products + Category Breakdown -->
    <section class="row g-3 mt-1">

      <!-- Recent Products -->
      <div class="col-12 col-xl-7">
        <div class="panel">
          <div class="panel-header">
            <div>
              <h2 class="h5 mb-1"><i class="bi bi-clock-history me-2"></i>Recent Products</h2>
              <p class="text-muted mb-0">Latest products added to the catalog.</p>
            </div>
            <a class="btn btn-light btn-sm" href="products.php">View All</a>
          </div>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Product</th>
                  <th>Category</th>
                  <th>Price</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentProducts as $rp): ?>
                  <tr>
                    <td><?= (int)$rp['id'] ?></td>
                    <td>
                      <?= htmlspecialchars(mb_strimwidth($rp['product_name'], 0, 40, '…')) ?>
                      <?php if ($rp['is_featured']): ?>
                        <i class="bi bi-star-fill text-warning ms-1" title="Featured"></i>
                      <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($rp['category_name']) ?></td>
                    <td>
                      <?php $price = (float)($rp['sale_price'] ?: $rp['regular_price']); ?>
                      ₹<?= number_format($price) ?>
                    </td>
                    <td>
                      <span class="badge <?= $rp['product_status'] === 'Active' ? 'bg-success' : 'bg-secondary' ?>">
                        <?= htmlspecialchars($rp['product_status']) ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Category Breakdown -->
      <div class="col-12 col-xl-5">
        <div class="panel h-100">
          <div class="panel-header">
            <div>
              <h2 class="h5 mb-1"><i class="bi bi-bar-chart me-2"></i>Products by Category</h2>
            </div>
            <a class="btn btn-light btn-sm" href="categories.php">Manage</a>
          </div>
          <div class="px-2">
            <?php foreach ($catBreakdown as $cb):
              $pct = $totalProducts > 0 ? round(($cb['active'] / $totalProducts) * 100) : 0;
            ?>
              <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                  <span class="small fw-500"><?= htmlspecialchars($cb['category_name']) ?></span>
                  <span class="small text-muted"><?= (int)$cb['active'] ?> / <?= (int)$cb['total'] ?></span>
                </div>
                <div class="progress" style="height:6px;">
                  <div class="progress-bar" role="progressbar"
                       style="width:<?= $pct ?>%"
                       aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

    </section>

    <!-- Quick Links -->
    <section class="row g-3 mt-1">
      <div class="col-12">
        <div class="panel">
          <div class="panel-header">
            <h2 class="h5 mb-0"><i class="bi bi-lightning-charge me-2"></i>Quick Actions</h2>
          </div>
          <div class="d-flex flex-wrap gap-3 p-2">
            <a href="homepage.php" class="btn btn-outline-primary">
              <i class="bi bi-house-door me-2"></i>Manage Homepage
            </a>
            <a href="slider_management.php" class="btn btn-outline-primary">
              <i class="bi bi-images me-2"></i>Hero Sliders
            </a>
            <a href="products_forms.php" class="btn btn-outline-success">
              <i class="bi bi-plus-circle me-2"></i>Add Product
            </a>
            <a href="categories.php" class="btn btn-outline-secondary">
              <i class="bi bi-grid me-2"></i>Categories
            </a>
            <a href="all_sub_categories.php" class="btn btn-outline-secondary">
              <i class="bi bi-diagram-3 me-2"></i>Subcategories
            </a>
            <a href="settings.php" class="btn btn-outline-secondary">
              <i class="bi bi-gear me-2"></i>Settings
            </a>
          </div>
        </div>
      </div>
    </section>

  </div>
</main>

<?php include './layouts/footer.php'; ?>
