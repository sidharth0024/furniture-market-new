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

    /** val() reads: flashed old input first (on validation error), then the existing product row, then a default. */
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
    function is_checked(string $postField, string $dbField, array $product, array $old): bool
    {
        if (!empty($old)) {
            return !empty($old[$postField] ?? null);
        }
        return !empty($product[$dbField] ?? null);
    }

    $categories = $pdo->query("SELECT id, category_name as name FROM categories WHERE status = 'Active' ORDER BY category_name")->fetchAll();

    $selectedCategory = (int) val('category_id', $product, $old, 0);
    $subcategories = [];
    if ($selectedCategory) {
        $stmt = $pdo->prepare("SELECT id, subcategory_name AS name FROM subcategories WHERE category_id = :cid AND status = 'Active' ORDER BY subcategory_name");
        $stmt->execute([':cid' => $selectedCategory]);
        $subcategories = $stmt->fetchAll();
    }
    $selectedSubcategory = (int) val('subcategory_id', $product, $old, 0);

    $activeTab = 'basic';
    include './layouts/header.php';
?>

    <style>
        .invalid-feedback {
            display: block
        }
    </style>

    <div class="container-fluid px-3 px-lg-2 pt-3 pb-5">

        <h4 class="mb-3">Edit Product &mdash; <?= e($product['product_name']) ?></h4>

        <?php // include __DIR__ . '/includes/edit_product_tabs.php'; 
        ?>

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

        <form action="update_product_basic.php" method="post" id="basicForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= (int) $editId ?>">

            <div class="row">
                <div class="col-lg-9">
                    <div class="card mb-4">
                        <div class="card-header">General Information</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Category <span class="text-danger">*</span></label>
                                    <select class="form-select <?= has_form_error('category_id') ? 'is-invalid' : '' ?>" name="category_id" id="category_id" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= (int) $cat['id'] ?>" <?= $selectedCategory === (int) $cat['id'] ? 'selected' : '' ?>>
                                                <?= e($cat['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (has_form_error('category_id')): ?><div class="invalid-feedback"><?= e(form_error('category_id')) ?></div><?php endif; ?>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Sub Category</label>
                                    <select class="form-select" name="subcategory_id" id="subcategory_id" data-selected="<?= $selectedSubcategory ?>">
                                        <option value="">Select</option>
                                        <?php foreach ($subcategories as $sub): ?>
                                            <option value="<?= (int) $sub['id'] ?>" <?= $selectedSubcategory === (int) $sub['id'] ? 'selected' : '' ?>>
                                                <?= e($sub['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Product Status</label>
                                    <select class="form-select" name="product_status">
                                        <?php foreach (['Active', 'Inactive', 'Draft'] as $status): ?>
                                            <option value="<?= $status ?>" <?= val('product_status', $product, $old, 'Active') === $status ? 'selected' : '' ?>>
                                                <?= $status ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Product Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?= has_form_error('product_name') ? 'is-invalid' : '' ?>"
                                        placeholder="Product Name" name="product_name" id="product_name"
                                        value="<?= e(val('product_name', $product, $old)) ?>" required maxlength="255">
                                    <?php if (has_form_error('product_name')): ?><div class="invalid-feedback"><?= e(form_error('product_name')) ?></div><?php endif; ?>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">SKU <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?= has_form_error('sku') ? 'is-invalid' : '' ?>"
                                        placeholder="SKU" name="sku" value="<?= e(val('sku', $product, $old)) ?>" required maxlength="100">
                                    <?php if (has_form_error('sku')): ?><div class="invalid-feedback"><?= e(form_error('sku')) ?></div><?php endif; ?>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Slug</label>
                                    <input type="text" class="form-control <?= has_form_error('slug') ? 'is-invalid' : '' ?>"
                                        placeholder="Slug (auto-generated if left blank)" name="slug" id="slug"
                                        value="<?= e(val('slug', $product, $old)) ?>" maxlength="255">
                                    <?php if (has_form_error('slug')): ?><div class="invalid-feedback"><?= e(form_error('slug')) ?></div><?php endif; ?>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Short Description</label>
                                    <textarea class="form-control" rows="3" placeholder="Enter Short Description" name="short_description"><?= e(val('short_description', $product, $old)) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4 col-md-4 col-12">
                            <div class="card mb-4 h-100">
                                <div class="card-header">Product Status Flags</div>
                                <div class="card-body">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" value="1" name="featured_product" id="featured_product" <?= is_checked('featured_product', 'is_featured', $product, $old) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="featured_product">Featured Product</label>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" value="1" name="best_seller" id="best_seller" <?= is_checked('best_seller', 'is_best_seller', $product, $old) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="best_seller">Best Seller</label>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" value="1" name="new_arrival" id="new_arrival" <?= is_checked('new_arrival', 'is_new_arrival', $product, $old) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="new_arrival">New Arrival</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8 col-md-8 col-12">
                            <div class="card mb-4 h-100">
                                <div class="card-header">SEO</div>
                                <div class="card-body">
                                    <input class="form-control mb-3" placeholder="SEO Title" name="seo_title" maxlength="255" value="<?= e(val('seo_title', $product, $old)) ?>">
                                    <textarea class="form-control mb-3" rows="3" placeholder="SEO Description" name="seo_description"><?= e(val('seo_description', $product, $old)) ?></textarea>
                                    <textarea class="form-control" rows="3" placeholder="SEO Keywords" name="seo_keywords"><?= e(val('seo_keywords', $product, $old)) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="card mb-4">
                        <div class="card-header">Pricing</div>
                        <div class="card-body">
                            <label class="form-label">Regular Price <span class="text-danger">*</span></label>
                            <input class="form-control mb-3 <?= has_form_error('regular_price') ? 'is-invalid' : '' ?>" placeholder="Regular Price" name="regular_price" id="regular_price"
                                value="<?= e(val('regular_price', $product, $old)) ?>" required inputmode="decimal">
                            <?php if (has_form_error('regular_price')): ?><div class="invalid-feedback"><?= e(form_error('regular_price')) ?></div><?php endif; ?>

                            <label class="form-label">Sale Price</label>
                            <input class="form-control mb-3 <?= has_form_error('sale_price') ? 'is-invalid' : '' ?>" placeholder="Sale Price" name="sale_price" id="sale_price"
                                value="<?= e(val('sale_price', $product, $old)) ?>" inputmode="decimal">
                            <?php if (has_form_error('sale_price')): ?><div class="invalid-feedback"><?= e(form_error('sale_price')) ?></div><?php endif; ?>

                            <label class="form-label">Cost Price</label>
                            <input class="form-control mb-3" placeholder="Cost Price" name="cost_price" value="<?= e(val('cost_price', $product, $old)) ?>" inputmode="decimal">

                            <label class="form-label">Discount</label>
                            <input class="form-control mb-3" id="discount_display" placeholder="Auto-calculated" readonly>

                            <label class="form-label">GST % (Incl.) <span class="text-danger">*</span></label>
                            <input class="form-control <?= has_form_error('gst_percentage') ? 'is-invalid' : '' ?>" placeholder="GST %" name="gst_percentage"
                                value="<?= e(val('gst_percentage', $product, $old, '18.00')) ?>" required inputmode="decimal">
                            <?php if (has_form_error('gst_percentage')): ?><div class="invalid-feedback"><?= e(form_error('gst_percentage')) ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">Inventory</div>
                        <div class="card-body">
                            <label class="form-label">Stock Quantity</label>
                            <input type="number" class="form-control mb-3 " placeholder="Stock Quantity" name="stock_quantity"
                                value="<?= $product['stock_quantity'] ?>"
                                inputmode="numeric">
                            <!-- <?php if (has_form_error('stock_quantity')): ?><div class="invalid-feedback"><?= e(form_error('stock_quantity')) ?></div><?php endif; ?> -->

                            <label class="form-label">Minimum Order Qty</label>
                            <input type="number" class="form-control " placeholder="Minimum Order Qty" name="minimum_order_qty"
                                value="<?= $product['minimum_order_qty'] ?>" inputmode="numeric">
                            <!-- <?php if (has_form_error('minimum_order_qty')): ?><div class="invalid-feedback"><?= e(form_error('minimum_order_qty')) ?></div><?php endif; ?> -->
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 save-btn">
                        <i class="bi bi-check-circle"></i>
                        Save & Continue to Images
                    </button>
                </div>
            </div>
        </form>
    </div>

    <?php
    include "./layouts/footer.php"; ?>
    <script>
        const categorySelect = document.getElementById('category_id');
        const subcategorySelect = document.getElementById('subcategory_id');

        function loadSubcategories(categoryId, preselect) {
            subcategorySelect.innerHTML = '<option value="">Select</option>';
            if (!categoryId) return;
            fetch('get_subcategories.php?category_id=' + encodeURIComponent(categoryId))
                .then(res => res.json())
                .then(data => {
                    data.forEach(sub => {
                        const opt = document.createElement('option');
                        opt.value = sub.id;
                        opt.textContent = sub.name;
                        if (preselect && String(preselect) === String(sub.id)) opt.selected = true;
                        subcategorySelect.appendChild(opt);
                    });
                })
                .catch(() => console.error('Could not load subcategories.'));
        }

        categorySelect.addEventListener('change', () => loadSubcategories(categorySelect.value, null));

        document.addEventListener('DOMContentLoaded', () => {
            const preselected = subcategorySelect.dataset.selected;
            if (categorySelect.value && preselected && preselected !== '0') {
                loadSubcategories(categorySelect.value, preselected);
            }
        });

        const nameInput = document.getElementById('product_name');
        const slugInput = document.getElementById('slug');
        nameInput.addEventListener('input', () => {
            if (slugInput.dataset.touched === '1') return;
            slugInput.value = nameInput.value
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/(^-|-$)/g, '');
        });
        slugInput.addEventListener('input', () => {
            slugInput.dataset.touched = '1';
        });

        const regularPrice = document.getElementById('regular_price');
        const salePrice = document.getElementById('sale_price');
        const discountDisplay = document.getElementById('discount_display');

        function updateDiscount() {
            const r = parseFloat(regularPrice.value);
            const s = parseFloat(salePrice.value);
            if (r > 0 && s >= 0 && s <= r) {
                discountDisplay.value = (((r - s) / r) * 100).toFixed(2) + '%';
            } else {
                discountDisplay.value = '';
            }
        }
        regularPrice.addEventListener('input', updateDiscount);
        salePrice.addEventListener('input', updateDiscount);
        updateDiscount();

        document.getElementById('basicForm').addEventListener('submit', (e) => {
            const r = parseFloat(regularPrice.value);
            const s = salePrice.value === '' ? null : parseFloat(salePrice.value);
            if (s !== null && s > r) {
                e.preventDefault();
                alert('Sale price cannot be greater than regular price.');
                salePrice.focus();
            }
        });
    </script>
<?php
} else {
    header("Location:index.php");
}
?>