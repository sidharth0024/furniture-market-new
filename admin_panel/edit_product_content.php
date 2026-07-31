<?php

declare(strict_types=1);
session_start();
if (isset($_SESSION['adminId'])) {

    require __DIR__ . '/includes/db_conn.php';
    require __DIR__ . '/config/helpers.php';

    [$errors, $old] = clear_flash();

    $editId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: null;
    if (!$editId) {
        flash('error', 'No product specified.');
        header('Location: products.php');
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute([':id' => $editId]);
    $product = $stmt->fetch();

    if (!$product) {
        flash('error', 'Product not found.');
        header('Location: products.php');
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM product_specifications WHERE product_id = :id ORDER BY id ASC");
    $stmt->execute([':id' => $editId]);
    $specs = $stmt->fetchAll();

    function val(string $field, array $product, array $old, $default = '')
    {
        if (array_key_exists($field, $old)) {
            return $old[$field];
        }
        if (array_key_exists($field, $product)) {
            return $product[$field];
        }
        return $default;
    }

    $specRows = $old['specification_name'] ?? array_column($specs, 'specification_name');
    $specVals = $old['specification_value'] ?? array_column($specs, 'specification_value');
    if (empty($specRows)) {
        $specRows = [''];
        $specVals = [''];
    }

    $activeTab = 'content';
    include './layouts/header.php';
?>

    <style>
        .invalid-feedback { display: block }
    </style>

    <div class="container-fluid px-3 px-lg-2 py-3">

        <h4 class="mb-3">Edit Product &mdash; <?= e($product['product_name']) ?></h4>

        <?php //include __DIR__ . '/includes/edit_product_tabs.php'; ?>

        <?php if ($msg = get_flash('success')): ?>
            <div class="alert alert-success"><?= e($msg) ?></div>
        <?php endif; ?>
        <?php if ($msg = get_flash('error')): ?>
            <div class="alert alert-danger"><?= e($msg) ?></div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <strong>Please fix the following:</strong>
                <ul class="mb-0">
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="update_product_content.php" method="post" id="contentForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= (int) $editId ?>">

            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="editorTabs">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#description">Description</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#features">Features</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#specification">Specifications</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#care">Care</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#shipping">Shipping</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#warranty">Warranty</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="description">
                            <textarea rows="6" id="editor1" name="description" placeholder="Full description"><?= e(val('description', $product, $old)) ?></textarea>
                        </div>
                        <div class="tab-pane fade" id="features">
                            <textarea rows="6" id="editor2" name="features" placeholder="One feature per line"><?= e(val('features', $product, $old)) ?></textarea>
                        </div>
                        <div class="tab-pane fade" id="specification">
                            <textarea class="mb-3" rows="4" id="editor3" name="specifications" placeholder="General specification notes (optional)"><?= e(val('specifications', $product, $old)) ?></textarea>

                            <label class="form-label fw-bold">Structured Specifications</label>
                            <div id="specRepeater">
                                <?php foreach ($specRows as $i => $name): ?>
                                    <div class="row g-2 mb-2 spec-row">
                                        <div class="col-5">
                                            <input type="text" class="form-control" name="specification_name[]" placeholder="e.g. Material" value="<?= e($name) ?>">
                                        </div>
                                        <div class="col-5">
                                            <input type="text" class="form-control" name="specification_value[]" placeholder="e.g. Stainless Steel" value="<?= e($specVals[$i] ?? '') ?>">
                                        </div>
                                        <div class="col-2">
                                            <button type="button" class="btn btn-outline-danger w-100 removeSpecRow"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" id="addSpecRow" class="btn btn-sm btn-outline-primary mt-1">
                                <i class="bi bi-plus-circle"></i> Add Specification
                            </button>
                        </div>
                        <div class="tab-pane fade" id="care">
                            <textarea rows="6" id="editor4" name="care_instruction" placeholder="Care instructions"><?= e(val('care_instruction', $product, $old)) ?></textarea>
                        </div>
                        <div class="tab-pane fade" id="shipping">
                            <textarea rows="6" id="editor5" name="shipping_details" placeholder="Shipping details"><?= e(val('shipping_details', $product, $old)) ?></textarea>
                        </div>
                        <div class="tab-pane fade" id="warranty">
                            <textarea rows="6" id="editor6" name="warranty_details" placeholder="Warranty details"><?= e(val('warranty_details', $product, $old)) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3 save-btn">
                <i class="bi bi-check-circle"></i>
                Save Content
            </button>
        </form>
    </div>

    <?php
    include "./layouts/footer.php"; ?>
    <script>
        document.getElementById('addSpecRow').addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'row g-2 mb-2 spec-row';
            row.innerHTML = `
        <div class="col-5"><input type="text" class="form-control" name="specification_name[]" placeholder="e.g. Material"></div>
        <div class="col-5"><input type="text" class="form-control" name="specification_value[]" placeholder="e.g. Stainless Steel"></div>
        <div class="col-2"><button type="button" class="btn btn-outline-danger w-100 removeSpecRow"><i class="bi bi-trash"></i></button></div>`;
            document.getElementById('specRepeater').appendChild(row);
        });
        document.getElementById('specRepeater').addEventListener('click', (e) => {
            const btn = e.target.closest('.removeSpecRow');
            if (!btn) return;
            const rows = document.querySelectorAll('.spec-row');
            if (rows.length > 1) btn.closest('.spec-row').remove();
        });

        // --- Summernote: lazy-init per tab (avoids 0-width editors on hidden panes) ---
        $(document).ready(function() {
            const tabEditorMap = {
                '#description': '#editor1',
                '#features': '#editor2',
                '#specification': '#editor3',
                '#care': '#editor4',
                '#shipping': '#editor5',
                '#warranty': '#editor6'
            };

            function initEditor(id) {
                const $el = $(id);
                if ($el.length && !$el.data('summernote-inited')) {
                    $el.summernote({ height: 300 });
                    $el.data('summernote-inited', true);
                }
            }

            initEditor(tabEditorMap['#description']);

            $('#editorTabs a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                const target = $(e.target).attr('href');
                if (tabEditorMap[target]) {
                    initEditor(tabEditorMap[target]);
                }
            });
        });
    </script>
<?php
} else {
    header("Location:index.php");
}
?>
