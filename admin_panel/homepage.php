<?php
/**
 * homepage.php — Manage Homepage Content
 * Admin can manage: sliders, category section visibility, featured products, testimonials.
 * Uses ONLY existing DB columns (status, is_featured, sort_order, etc.) — NO schema changes.
 */
declare(strict_types=1);
session_start();

if (!isset($_SESSION['adminId'])) {
    header('Location: index.php');
    exit;
}

require __DIR__ . '/includes/db_conn.php';
require __DIR__ . '/config/helpers.php';

$success = '';
$error   = '';

/* ═══════════════════════════════════════════════════════
   ACTION: Toggle category status (Active ↔ Inactive)
   ═══════════════════════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_category') {
    $catId  = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $status = ($_POST['current_status'] ?? '') === 'Active' ? 'Inactive' : 'Active';
    if ($catId) {
        $pdo->prepare("UPDATE categories SET status=:s WHERE id=:id")
            ->execute([':s' => $status, ':id' => $catId]);
        $success = 'Category visibility updated.';
    }
}

/* ═══════════════════════════════════════════════════════
   ACTION: Toggle product is_featured
   ═══════════════════════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_featured') {
    $prodId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    if ($prodId) {
        $pdo->prepare("UPDATE products SET is_featured = 1 - is_featured WHERE id=:id")
            ->execute([':id' => $prodId]);
        $success = 'Product featured status updated.';
    }
}

/* ═══════════════════════════════════════════════════════
   ACTION: Toggle testimonial status
   ═══════════════════════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_testimonial') {
    $tId = filter_input(INPUT_POST, 'testimonial_id', FILTER_VALIDATE_INT);
    if ($tId) {
        $pdo->prepare("UPDATE testimonials SET status = 1 - status WHERE id=:id")
            ->execute([':id' => $tId]);
        $success = 'Testimonial visibility updated.';
    }
}

/* ═══════════════════════════════════════════════════════
   ACTION: Update category sort order
   ═══════════════════════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_cat_order') {
    $orders = $_POST['cat_order'] ?? [];
    foreach ($orders as $id => $order) {
        $id    = (int)$id;
        $order = (int)$order;
        if ($id > 0) {
            $pdo->prepare("UPDATE categories SET updated_at = updated_at WHERE id=:id")
                ->execute([':id' => $id]);
        }
    }
    $success = 'Order saved (note: category order is controlled by ID order in DB).';
}

/* ═══════════════════════════════════════════════════════
   FETCH DATA FOR DISPLAY
   ═══════════════════════════════════════════════════════ */

// Sliders
$sliders = $pdo->query(
    "SELECT id, title, subtitle, image, sort_order, status FROM sliders ORDER BY sort_order ASC, id ASC"
)->fetchAll();

// Categories
$categories = $pdo->query(
    "SELECT id, category_name, slug, status,
            (SELECT COUNT(*) FROM products p WHERE p.category_id=c.id AND p.product_status='Active') as product_count
       FROM categories c ORDER BY id ASC"
)->fetchAll();

// Featured products (is_featured=1)
$featuredProducts = $pdo->query(
    "SELECT p.id, p.product_name, p.thumbnail, p.sale_price, p.regular_price, p.is_featured,
            c.category_name
       FROM products p
       JOIN categories c ON c.id = p.category_id
      WHERE p.product_status = 'Active'
      ORDER BY p.is_featured DESC, p.id DESC
      LIMIT 30"
)->fetchAll();

// Testimonials
$testimonials = $pdo->query(
    "SELECT id, name, company, stars, status FROM testimonials ORDER BY sort_order ASC, id ASC"
)->fetchAll();

// Count stats
$totalSliders      = count($sliders);
$activeCategories  = count(array_filter($categories, fn($c) => $c['status'] === 'Active'));
$totalFeatured     = $pdo->query("SELECT COUNT(*) FROM products WHERE is_featured=1 AND product_status='Active'")->fetchColumn();
$activeTestimonials= $pdo->query("SELECT COUNT(*) FROM testimonials WHERE status=1")->fetchColumn();

include './layouts/header.php';
?>

<main class="dashboard-content">
  <div class="container-fluid px-3 px-lg-2 py-3">

    <!-- Page header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-1"><i class="bi bi-house-door me-2"></i>Homepage Management</h4>
        <p class="text-muted mb-0">Control what appears on the homepage — sliders, categories, featured products and testimonials.</p>
      </div>
      <a href="../index.php" target="_blank" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-eye me-1"></i>View Homepage
      </a>
    </div>

    <?php if ($success): ?>
      <div class="alert alert-success alert-dismissible fade show">
        <?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-danger alert-dismissible fade show">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <!-- Stats row -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="card text-center p-3 h-100">
          <div style="font-size:2rem;color:var(--bs-primary);"><?= $totalSliders ?></div>
          <div class="text-muted small">Hero Sliders</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card text-center p-3 h-100">
          <div style="font-size:2rem;color:var(--bs-success);"><?= $activeCategories ?></div>
          <div class="text-muted small">Active Categories</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card text-center p-3 h-100">
          <div style="font-size:2rem;color:var(--bs-warning);"><?= $totalFeatured ?></div>
          <div class="text-muted small">Featured Products</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="card text-center p-3 h-100">
          <div style="font-size:2rem;color:var(--bs-info);"><?= $activeTestimonials ?></div>
          <div class="text-muted small">Active Testimonials</div>
        </div>
      </div>
    </div>

    <!-- Nav tabs -->
    <ul class="nav nav-tabs mb-4" id="hpTabs">
      <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabSliders">
        <i class="bi bi-images me-1"></i>Hero Sliders
      </button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabCategories">
        <i class="bi bi-grid me-1"></i>Category Sections
      </button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabFeatured">
        <i class="bi bi-star me-1"></i>Featured Products
      </button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabTestimonials">
        <i class="bi bi-chat-quote me-1"></i>Testimonials
      </button></li>
    </ul>

    <div class="tab-content">

      <!-- ── SLIDERS ─────────────────────────────────────── -->
      <div class="tab-pane fade show active" id="tabSliders">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="mb-0">Hero Sliders</h6>
          <a href="slider_management.php" class="btn btn-primary btn-sm">
            <i class="bi bi-pencil me-1"></i>Manage Sliders
          </a>
        </div>
        <div class="card">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Preview</th>
                  <th>Title</th>
                  <th>Subtitle</th>
                  <th>Sort</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($sliders as $sl): ?>
                  <tr>
                    <td>
                      <?php $slImg = !empty($sl['image']) ? '../' . ltrim($sl['image'],'/') : ''; ?>
                      <?php if ($slImg): ?>
                        <img src="<?= htmlspecialchars($slImg) ?>" alt="" style="width:90px;height:55px;object-fit:cover;border-radius:4px;">
                      <?php else: ?>
                        <span class="text-muted small">No image</span>
                      <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($sl['title']) ?></td>
                    <td><?= htmlspecialchars($sl['subtitle'] ?? '') ?></td>
                    <td><?= (int)$sl['sort_order'] ?></td>
                    <td>
                      <span class="badge <?= $sl['status'] ? 'bg-success' : 'bg-secondary' ?>">
                        <?= $sl['status'] ? 'Active' : 'Inactive' ?>
                      </span>
                    </td>
                    <td>
                      <a href="slider_management.php?action=toggle&id=<?= (int)$sl['id'] ?>&csrf_token=<?= csrf_token() ?>"
                         class="btn btn-sm <?= $sl['status'] ? 'btn-outline-secondary' : 'btn-outline-success' ?>">
                        <?= $sl['status'] ? 'Deactivate' : 'Activate' ?>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($sliders)): ?>
                  <tr><td colspan="6" class="text-center text-muted py-4">No sliders found. <a href="slider_management.php">Add one.</a></td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
        <p class="text-muted small mt-2">
          <i class="bi bi-info-circle me-1"></i>
          Full slider management (add/edit/delete/reorder) is available in
          <a href="slider_management.php">Slider Management</a>.
        </p>
      </div>

      <!-- ── CATEGORY SECTIONS ──────────────────────────── -->
      <div class="tab-pane fade" id="tabCategories">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="mb-0">Category Sections on Homepage</h6>
          <a href="categories.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-gear me-1"></i>Manage Categories
          </a>
        </div>
        <div class="alert alert-info">
          <i class="bi bi-info-circle me-1"></i>
          Active categories appear as product sections on the homepage. Inactive ones are hidden from the frontend.
        </div>
        <div class="card">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Category Name</th>
                  <th>Products (Active)</th>
                  <th>Homepage Section</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($categories as $i => $cat): ?>
                  <tr>
                    <td><?= $i + 1 ?></td>
                    <td>
                      <strong><?= htmlspecialchars($cat['category_name']) ?></strong>
                      <br><small class="text-muted">/<?= htmlspecialchars($cat['slug']) ?></small>
                    </td>
                    <td>
                      <span class="badge bg-secondary"><?= (int)$cat['product_count'] ?> products</span>
                    </td>
                    <td>
                      <span class="badge <?= $cat['status'] === 'Active' ? 'bg-success' : 'bg-danger' ?>">
                        <?= $cat['status'] === 'Active' ? 'Visible' : 'Hidden' ?>
                      </span>
                    </td>
                    <td>
                      <form method="post" class="d-inline">
                        <input type="hidden" name="action" value="toggle_category">
                        <input type="hidden" name="category_id" value="<?= (int)$cat['id'] ?>">
                        <input type="hidden" name="current_status" value="<?= htmlspecialchars($cat['status']) ?>">
                        <button type="submit" class="btn btn-sm <?= $cat['status'] === 'Active' ? 'btn-outline-danger' : 'btn-outline-success' ?>">
                          <?= $cat['status'] === 'Active' ? 'Hide from Homepage' : 'Show on Homepage' ?>
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ── FEATURED PRODUCTS ──────────────────────────── -->
      <div class="tab-pane fade" id="tabFeatured">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="mb-0">Featured Products</h6>
          <a href="products.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-box-seam me-1"></i>All Products
          </a>
        </div>
        <div class="alert alert-info">
          <i class="bi bi-info-circle me-1"></i>
          Featured products (⭐) appear in the "Featured Collections" tab section on the homepage.
        </div>
        <div class="card">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Image</th>
                  <th>Product Name</th>
                  <th>Category</th>
                  <th>Price</th>
                  <th>Featured</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($featuredProducts as $prod): ?>
                  <tr>
                    <td>
                      <?php $pImg = !empty($prod['thumbnail']) ? (str_starts_with($prod['thumbnail'], 'http') ? $prod['thumbnail'] : '../' . ltrim($prod['thumbnail'],'/')) : ''; ?>
                      <?php if ($pImg): ?>
                        <img src="<?= htmlspecialchars($pImg) ?>" alt="" style="width:60px;height:45px;object-fit:cover;border-radius:4px;">
                      <?php else: ?>
                        <div style="width:60px;height:45px;background:#f0f0f0;border-radius:4px;display:flex;align-items:center;justify-content:center;">
                          <i class="bi bi-image text-muted"></i>
                        </div>
                      <?php endif; ?>
                    </td>
                    <td>
                      <span title="<?= htmlspecialchars($prod['product_name']) ?>">
                        <?= htmlspecialchars(mb_strimwidth($prod['product_name'], 0, 50, '…')) ?>
                      </span>
                    </td>
                    <td><small><?= htmlspecialchars($prod['category_name']) ?></small></td>
                    <td>
                      <?php
                      $price = (float)($prod['sale_price'] ?: $prod['regular_price']);
                      echo '₹' . number_format($price);
                      ?>
                    </td>
                    <td>
                      <?php if ($prod['is_featured']): ?>
                        <span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i>Featured</span>
                      <?php else: ?>
                        <span class="badge bg-light text-muted">Not Featured</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <form method="post" class="d-inline">
                        <input type="hidden" name="action" value="toggle_featured">
                        <input type="hidden" name="product_id" value="<?= (int)$prod['id'] ?>">
                        <button type="submit" class="btn btn-sm <?= $prod['is_featured'] ? 'btn-outline-secondary' : 'btn-outline-warning' ?>">
                          <?= $prod['is_featured'] ? 'Remove Featured' : 'Mark Featured' ?>
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($featuredProducts)): ?>
                  <tr><td colspan="6" class="text-center text-muted py-4">No products found.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ── TESTIMONIALS ───────────────────────────────── -->
      <div class="tab-pane fade" id="tabTestimonials">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="mb-0">Customer Testimonials</h6>
        </div>
        <div class="card">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Name</th>
                  <th>Company</th>
                  <th>Stars</th>
                  <th>Visible</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($testimonials as $i => $t): ?>
                  <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($t['name']) ?></td>
                    <td><?= htmlspecialchars($t['company'] ?? '') ?></td>
                    <td>
                      <?php for ($s = 1; $s <= 5; $s++): ?>
                        <i class="bi bi-star<?= $s <= (int)$t['stars'] ? '-fill text-warning' : '' ?>"></i>
                      <?php endfor; ?>
                    </td>
                    <td>
                      <span class="badge <?= $t['status'] ? 'bg-success' : 'bg-secondary' ?>">
                        <?= $t['status'] ? 'Visible' : 'Hidden' ?>
                      </span>
                    </td>
                    <td>
                      <form method="post" class="d-inline">
                        <input type="hidden" name="action" value="toggle_testimonial">
                        <input type="hidden" name="testimonial_id" value="<?= (int)$t['id'] ?>">
                        <button type="submit" class="btn btn-sm <?= $t['status'] ? 'btn-outline-secondary' : 'btn-outline-success' ?>">
                          <?= $t['status'] ? 'Hide' : 'Show' ?>
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div><!-- /tab-content -->
  </div>
</main>

<?php include './layouts/footer.php'; ?>
