<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/includes/db_conn.php';
require __DIR__ . '/config/helpers.php';
if (isset($_SESSION['adminId'])) {

include './layouts/header.php';

// ── Status toggle ─────────────────────────────────────────────
if (!empty($_GET['toggle_id'])) {
    $pdo->prepare("UPDATE product_faqs SET status = 1 - status WHERE id = :id")
        ->execute([':id' => (int)$_GET['toggle_id']]);
    header('Location: product_faqs.php?msg=Status+updated&type=success');
    exit;
}
// ── Delete ────────────────────────────────────────────────────
if (!empty($_GET['delete_id'])) {
    $pdo->prepare("DELETE FROM product_faqs WHERE id = :id")
        ->execute([':id' => (int)$_GET['delete_id']]);
    header('Location: product_faqs.php?msg=FAQ+deleted&type=success');
    exit;
}

$msg     = '';
$msgType = 'success';
if (!empty($_GET['msg'])) {
    $msg     = htmlspecialchars($_GET['msg']);
    $msgType = ($_GET['type'] ?? 'success') === 'success' ? 'success' : 'danger';
}

// ── Fetch all FAQs ────────────────────────────────────────────
$faqs = $pdo->query(
    "SELECT * FROM product_faqs ORDER BY sort_order ASC, id ASC"
)->fetchAll();

// FAQ being edited
$editFaq = null;
if (!empty($_GET['edit_id'])) {
    $es = $pdo->prepare("SELECT * FROM product_faqs WHERE id = :id");
    $es->execute([':id' => (int)$_GET['edit_id']]);
    $editFaq = $es->fetch() ?: null;
}

?>
<main>
<div class="container-fluid py-4">

  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
      <h2 class="mb-0 fw-bold"><i class="bi bi-question-circle me-2 text-info"></i>Product FAQs</h2>
      <p class="text-muted small mb-0 mt-1">These FAQs are shown on <strong>every</strong> product detail page.</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#faqModal">
      <i class="bi bi-plus-lg me-1"></i> Add FAQ
    </button>
  </div>

  <?php if ($msg): ?>
    <div class="alert alert-<?= $msgType ?> alert-dismissible fade show">
      <?= $msg ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
          <thead class="table-light">
            <tr>
              <th style="width:50px;">#</th>
              <th>Question</th>
              <th>Answer (excerpt)</th>
              <th class="text-center" style="width:80px;">Order</th>
              <th class="text-center" style="width:90px;">Status</th>
              <th class="text-center" style="width:110px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($faqs)): ?>
              <tr>
                <td colspan="6" class="text-center text-muted py-5">
                  No FAQs yet. Click <strong>Add FAQ</strong> to get started.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($faqs as $fq): ?>
                <tr>
                  <td><?= $fq['id'] ?></td>
                  <td class="fw-semibold" style="max-width:280px;">
                    <?= htmlspecialchars(mb_strimwidth($fq['question'], 0, 90, '…')) ?>
                  </td>
                  <td style="max-width:360px;color:#555;">
                    <?= htmlspecialchars(mb_strimwidth($fq['answer'], 0, 100, '…')) ?>
                  </td>
                  <td class="text-center"><?= (int)$fq['sort_order'] ?></td>
                  <td class="text-center">
                    <a href="product_faqs.php?toggle_id=<?= $fq['id'] ?>"
                       class="badge <?= $fq['status'] ? 'bg-success' : 'bg-danger' ?> text-decoration-none">
                      <?= $fq['status'] ? 'Active' : 'Inactive' ?>
                    </a>
                  </td>
                  <td class="text-center">
                    <a href="product_faqs.php?edit_id=<?= $fq['id'] ?>"
                       class="btn btn-sm btn-outline-primary me-1" title="Edit">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <a href="product_faqs.php?delete_id=<?= $fq['id'] ?>"
                       class="btn btn-sm btn-outline-danger"
                       onclick="return confirm('Delete this FAQ?')" title="Delete">
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

  <p class="text-muted small mt-3">
    <i class="bi bi-info-circle me-1"></i>
    FAQs are ordered by <strong>Sort Order</strong> (ascending), then by <strong>ID</strong>. Lower numbers appear first.
  </p>

</div><!-- /container -->
</main>

<!-- ══════════════════════════════════════════════════════
     ADD / EDIT FAQ MODAL
     ══════════════════════════════════════════════════════ -->
<div class="modal fade" id="faqModal" tabindex="-1"
     aria-labelledby="faqModalLabel" aria-hidden="true"
     <?= $editFaq ? 'data-auto-open="true"' : '' ?>>
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="faqModalLabel">
          <i class="bi bi-question-circle me-2"></i><?= $editFaq ? 'Edit FAQ' : 'Add FAQ' ?>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form method="POST" action="save_faq.php">
          <?php if ($editFaq): ?>
            <input type="hidden" name="faq_id" value="<?= $editFaq['id'] ?>">
          <?php endif; ?>

          <div class="mb-3">
            <label class="form-label fw-semibold">Question <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="question" required maxlength="500"
                   placeholder="e.g. What is the weight capacity of this product?"
                   value="<?= htmlspecialchars($editFaq['question'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Answer <span class="text-danger">*</span></label>
            <textarea class="form-control" name="answer" rows="5" required
                      placeholder="Provide a clear, helpful answer..."><?= htmlspecialchars($editFaq['answer'] ?? '') ?></textarea>
          </div>
          <div class="row g-3">
            <div class="col-sm-6">
              <label class="form-label fw-semibold">Sort Order</label>
              <input type="number" class="form-control" name="sort_order" min="0"
                     value="<?= (int)($editFaq['sort_order'] ?? 0) ?>">
              <div class="form-text">Lower = displayed first.</div>
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold">Status</label>
              <select class="form-select" name="status">
                <option value="1" <?= (!$editFaq || $editFaq['status']) ? 'selected' : '' ?>>Active</option>
                <option value="0" <?= ($editFaq && !$editFaq['status'])  ? 'selected' : '' ?>>Inactive</option>
              </select>
            </div>
          </div>

          <div class="mt-4 d-flex gap-2 justify-content-end">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-info text-white">
              <i class="bi bi-save me-1"></i><?= $editFaq ? 'Update FAQ' : 'Save FAQ' ?>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var m = document.getElementById('faqModal');
    if (m && m.dataset.autoOpen === 'true') { new bootstrap.Modal(m).show(); }
});
</script>

<?php
    include './layouts/footer.php';
} else {
    header('Location: index.php');
    exit;
}
