<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/includes/db_conn.php';
require __DIR__ . '/config/helpers.php';
if (isset($_SESSION['adminId'])) {

include './layouts/header.php';

// ── Quick status toggle ───────────────────────────────────────
if (!empty($_GET['toggle_id'])) {
    $pdo->prepare("UPDATE product_reviews SET status = 1 - status WHERE id = :id")
        ->execute([':id' => (int)$_GET['toggle_id']]);
    header('Location: product_reviews.php?msg=Status+updated&type=success');
    exit;
}
// ── Delete ────────────────────────────────────────────────────
if (!empty($_GET['delete_id'])) {
    $did = (int)$_GET['delete_id'];
    $rv  = $pdo->prepare("SELECT review_images, avatar FROM product_reviews WHERE id = :id");
    $rv->execute([':id' => $did]);
    $imgRow = $rv->fetch();
    if ($imgRow) {
        if (!empty($imgRow['review_images'])) {
            foreach (explode(',', $imgRow['review_images']) as $f) {
                $f = trim($f);
                if ($f && file_exists(__DIR__ . '/../' . $f)) @unlink(__DIR__ . '/../' . $f);
            }
        }
        if (!empty($imgRow['avatar']) && file_exists(__DIR__ . '/../' . $imgRow['avatar'])) {
            @unlink(__DIR__ . '/../' . $imgRow['avatar']);
        }
    }
    $pdo->prepare("DELETE FROM product_reviews WHERE id = :id")->execute([':id' => $did]);
    header('Location: product_reviews.php?msg=Review+deleted&type=success');
    exit;
}

$msg     = '';
$msgType = 'success';
if (!empty($_GET['msg'])) {
    $msg     = htmlspecialchars($_GET['msg']);
    $msgType = ($_GET['type'] ?? 'success') === 'success' ? 'success' : 'danger';
}

// ── Filtering / Pagination ────────────────────────────────────
$filterProduct = (int)($_GET['product_id'] ?? 0);
$page          = max(1, (int)($_GET['page'] ?? 1));
$perPage       = 20;
$offset        = ($page - 1) * $perPage;

$where  = $filterProduct ? " WHERE r.product_id = :pid " : "";
$params = $filterProduct ? [':pid' => $filterProduct] : [];

$cntStmt = $pdo->prepare("SELECT COUNT(*) FROM product_reviews r $where");
$cntStmt->execute($params);
$totalRows  = (int)$cntStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $perPage));

$sql = "SELECT r.*, p.product_name
          FROM product_reviews r
          JOIN products p ON p.id = r.product_id
          $where
         ORDER BY r.sort_order ASC, r.id DESC
         LIMIT :lim OFFSET :off";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset,  PDO::PARAM_INT);
$stmt->execute();
$reviews = $stmt->fetchAll();

// Product list for filter dropdown
$products = $pdo->query(
    "SELECT id, product_name FROM products WHERE product_status='Active' ORDER BY product_name ASC"
)->fetchAll();

// Review being edited
$editReview = null;
if (!empty($_GET['edit_id'])) {
    $es = $pdo->prepare("SELECT * FROM product_reviews WHERE id = :id");
    $es->execute([':id' => (int)$_GET['edit_id']]);
    $editReview = $es->fetch() ?: null;
}

?>
<main>
<div class="container-fluid py-4">

  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h2 class="mb-0 fw-bold"><i class="bi bi-star-half me-2 text-warning"></i>Product Reviews</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reviewModal">
      <i class="bi bi-plus-lg me-1"></i> Add Review
    </button>
  </div>

  <?php if ($msg): ?>
    <div class="alert alert-<?= $msgType ?> alert-dismissible fade show">
      <?= $msg ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Filter -->
  <form class="row g-2 mb-4" method="GET">
    <div class="col-md-4 col-sm-6">
      <select class="form-select form-select-sm" name="product_id" onchange="this.form.submit()">
        <option value="">— All Products —</option>
        <?php foreach ($products as $pr): ?>
          <option value="<?= $pr['id'] ?>" <?= $filterProduct === (int)$pr['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($pr['product_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-auto">
      <a href="product_reviews.php" class="btn btn-sm btn-outline-secondary">Clear</a>
    </div>
  </form>

  <!-- Table -->
  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Product</th>
              <th>Reviewer</th>
              <th class="text-center">Stars</th>
              <th>Review (excerpt)</th>
              <th class="text-center">Order</th>
              <th class="text-center">Status</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($reviews)): ?>
              <tr><td colspan="8" class="text-center text-muted py-4">No reviews found.</td></tr>
            <?php else: ?>
              <?php foreach ($reviews as $rv): ?>
                <tr>
                  <td><?= $rv['id'] ?></td>
                  <td><?= htmlspecialchars(mb_strimwidth($rv['product_name'] ?? '', 0, 40, '…')) ?></td>
                  <td><?= htmlspecialchars($rv['reviewer_name']) ?></td>
                  <td class="text-center">
                    <?php for ($s = 1; $s <= 5; $s++): ?>
                      <i class="bi bi-star<?= $s <= (int)$rv['stars'] ? '-fill text-warning' : ' text-secondary' ?>" style="font-size:11px;"></i>
                    <?php endfor; ?>
                    <span class="ms-1 text-muted">(<?= (int)$rv['stars'] ?>)</span>
                  </td>
                  <td><?= htmlspecialchars(mb_strimwidth($rv['review_text'] ?? '', 0, 70, '…')) ?></td>
                  <td class="text-center"><?= (int)$rv['sort_order'] ?></td>
                  <td class="text-center">
                    <a href="product_reviews.php?toggle_id=<?= $rv['id'] ?>&product_id=<?= $filterProduct ?>&page=<?= $page ?>"
                       class="badge <?= $rv['status'] ? 'bg-success' : 'bg-danger' ?> text-decoration-none">
                      <?= $rv['status'] ? 'Active' : 'Inactive' ?>
                    </a>
                  </td>
                  <td class="text-center">
                    <a href="product_reviews.php?edit_id=<?= $rv['id'] ?>&product_id=<?= $filterProduct ?>&page=<?= $page ?>"
                       class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>
                    <a href="product_reviews.php?delete_id=<?= $rv['id'] ?>&product_id=<?= $filterProduct ?>&page=<?= $page ?>"
                       class="btn btn-sm btn-outline-danger"
                       onclick="return confirm('Delete this review? This cannot be undone.')">
                      <i class="bi bi-trash"></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
    <nav class="mt-3">
      <ul class="pagination pagination-sm justify-content-center flex-wrap mb-0">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
          <li class="page-item <?= $p === $page ? 'active' : '' ?>">
            <a class="page-link" href="?product_id=<?= $filterProduct ?>&page=<?= $p ?>"><?= $p ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
  <?php endif; ?>

</div><!-- /container -->
</main>

<!-- ══════════════════════════════════════════════════════
     ADD / EDIT REVIEW MODAL
     ══════════════════════════════════════════════════════ -->
<div class="modal fade" id="reviewModal" tabindex="-1"
     aria-labelledby="reviewModalLabel" aria-hidden="true"
     <?= $editReview ? 'data-auto-open="true"' : '' ?>>
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="reviewModalLabel">
          <i class="bi bi-star me-2"></i><?= $editReview ? 'Edit Review' : 'Add Review' ?>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="save_review.php" enctype="multipart/form-data">
          <?php if ($editReview): ?>
            <input type="hidden" name="review_id" value="<?= $editReview['id'] ?>">
            <input type="hidden" name="existing_images" value="<?= htmlspecialchars($editReview['review_images'] ?? '') ?>">
          <?php endif; ?>
          <input type="hidden" name="redirect_product" value="<?= $filterProduct ?>">

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Product <span class="text-danger">*</span></label>
              <select class="form-select" name="product_id" required>
                <option value="">— Select Product —</option>
                <?php foreach ($products as $pr): ?>
                  <option value="<?= $pr['id'] ?>"
                    <?= ($editReview && (int)$editReview['product_id'] === (int)$pr['id']) ||
                        (!$editReview && $filterProduct === (int)$pr['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($pr['product_name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Reviewer Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="reviewer_name" required maxlength="150"
                     value="<?= htmlspecialchars($editReview['reviewer_name'] ?? '') ?>"
                     placeholder="e.g. Rahul Sharma">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Star Rating <span class="text-danger">*</span></label>
              <select class="form-select" name="stars" required>
                <?php for ($s = 5; $s >= 1; $s--): ?>
                  <option value="<?= $s ?>"
                    <?= ($editReview && (int)$editReview['stars'] === $s) || (!$editReview && $s === 5) ? 'selected' : '' ?>>
                    <?= $s ?> Star<?= $s !== 1 ? 's' : '' ?>
                  </option>
                <?php endfor; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Sort Order</label>
              <input type="number" class="form-control" name="sort_order" min="0"
                     value="<?= (int)($editReview['sort_order'] ?? 0) ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Status</label>
              <select class="form-select" name="status">
                <option value="1" <?= (!$editReview || $editReview['status']) ? 'selected' : '' ?>>Active</option>
                <option value="0" <?= ($editReview && !$editReview['status'])  ? 'selected' : '' ?>>Inactive</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Review Text</label>
              <textarea class="form-control" name="review_text" rows="4"
                        placeholder="Customer review content..."><?= htmlspecialchars($editReview['review_text'] ?? '') ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Reviewer Avatar <small class="text-muted">(optional)</small></label>
              <?php if (!empty($editReview['avatar'])): ?>
                <div class="mb-2 d-flex align-items-center gap-2">
                  <img src="../<?= htmlspecialchars($editReview['avatar']) ?>"
                       class="img-thumbnail" style="width:56px;height:56px;object-fit:cover;" alt="">
                  <label class="small mb-0"><input type="checkbox" name="remove_avatar" value="1"> Remove avatar</label>
                </div>
              <?php endif; ?>
              <input type="file" class="form-control" name="avatar" accept="image/jpeg,image/png,image/webp">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Review Photos <small class="text-muted">(up to 4)</small></label>
              <?php if (!empty($editReview['review_images'])): ?>
                <div class="d-flex gap-2 flex-wrap mb-2">
                  <?php foreach (array_filter(explode(',', $editReview['review_images'])) as $ri): ?>
                    <img src="../<?= htmlspecialchars(trim($ri)) ?>"
                         class="img-thumbnail" style="width:52px;height:52px;object-fit:cover;" alt="">
                  <?php endforeach; ?>
                </div>
                <label class="small"><input type="checkbox" name="remove_images" value="1"> Remove all existing photos</label><br>
              <?php endif; ?>
              <input type="file" class="form-control mt-1" name="review_images[]"
                     accept="image/jpeg,image/png,image/webp" multiple>
            </div>
          </div>

          <div class="mt-4 d-flex gap-2 justify-content-end">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="bi bi-save me-1"></i><?= $editReview ? 'Update Review' : 'Save Review' ?>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var m = document.getElementById('reviewModal');
    if (m && m.dataset.autoOpen === 'true') { new bootstrap.Modal(m).show(); }
});
</script>

<?php
    include './layouts/footer.php';
} else {
    header('Location: index.php');
    exit;
}
