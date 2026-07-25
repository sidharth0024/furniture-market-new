<?php

declare(strict_types=1);
session_start();
if (isset($_SESSION['adminId'])) {


    require __DIR__ . '/includes/db_conn.php';
    require __DIR__ . '/config/helpers.php';

    [$errors, $old] = clear_flash();

    $editId  = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: null;
    $isEdit  = $editId !== null;
    $product = null;
    $specs   = [];
    $images  = [];

    if ($isEdit) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute([':id' => $editId]);
        $product = $stmt->fetch();

        if (!$product) {
            flash('error', 'Product not found.');
            header('Location: add_products.php');
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM product_specifications WHERE product_id = :id");
        $stmt->execute([':id' => $editId]);
        $specs = $stmt->fetchAll();

        $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = :id ORDER BY sort_order ASC");
        $stmt->execute([':id' => $editId]);
        $images = $stmt->fetchAll();
    }

    /** val() reads: flashed old input first, then existing product row (edit), then a default. */
    function val(string $field, $product, array $old, $default = '')
    {
        if (array_key_exists($field, $old)) {
            return $old[$field];
        }
        if ($product && array_key_exists($field, $product)) {
            return $product[$field];
        }
        return $default;
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

    $specRows = $old['specification_name'] ?? array_column($specs, 'specification_name');
    $specVals = $old['specification_value'] ?? array_column($specs, 'specification_value');
    if (empty($specRows)) {
        $specRows = [''];
        $specVals = [''];
    }

    include './layouts/header.php';
?>
    <style>
        .product-image-box {
            border: 2px dashed #ccc;
            border-radius: .5rem;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            display: block
        }

        .product-image-box:hover {
            border-color: #6c757d
        }

        .invalid-feedback {
            display: block
        }

        .img-thumb {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: .375rem;
            margin: 4px;
            border: 1px solid #dee2e6
        }
    </style>


    <div class="container-fluid px-3 px-lg-4 py-4">

        <h4 class="mb-3"><?= $isEdit ? 'Edit Product' : 'Add Product' ?></h4>

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

        <form action="save_product.php" method="post" enctype="multipart/form-data" id="productForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= (int)$editId ?>">
            <?php endif; ?>

            <div class="row">
                <!-- LEFT -->
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
                                            <option value="<?= (int)$cat['id'] ?>" <?= $selectedCategory === (int)$cat['id'] ? 'selected' : '' ?>>
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
                                            <option value="<?= (int)$sub['id'] ?>" <?= $selectedSubcategory === (int)$sub['id'] ? 'selected' : '' ?>>
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

                    <div class="row mt-4">
                        <div class="col-lg-4 col-md-4 col-12">
                            <div class="card mb-4 h-100">
                                <div class="card-header">Product Status</div>
                                <div class="card-body">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" value="1" name="featured_product" id="featured_product" <?= val('is_featured', $product, $old, 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="featured_product">Featured Product</label>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" value="1" name="best_seller" id="best_seller" <?= val('is_best_seller', $product, $old, 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="best_seller">Best Seller</label>
                                    </div>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" value="1" name="new_arrival" id="new_arrival" <?= val('is_new_arrival', $product, $old, 0) ? 'checked' : '' ?>>
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

                <!-- RIGHT -->
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
                            <input class="form-control mb-3 <?= has_form_error('stock_quantity') ? 'is-invalid' : '' ?>" placeholder="Stock Quantity" name="stock_quantity"
                                value="<?= e(val('stock_quantity', $product, $old, '0')) ?>" inputmode="numeric">
                            <?php if (has_form_error('stock_quantity')): ?><div class="invalid-feedback"><?= e(form_error('stock_quantity')) ?></div><?php endif; ?>

                            <label class="form-label">Minimum Order Qty</label>
                            <input class="form-control <?= has_form_error('minimum_order_qty') ? 'is-invalid' : '' ?>" placeholder="Minimum Order Qty" name="minimum_order_qty"
                                value="<?= e(val('minimum_order_qty', $product, $old, '1')) ?>" inputmode="numeric">
                            <?php if (has_form_error('minimum_order_qty')): ?><div class="invalid-feedback"><?= e(form_error('minimum_order_qty')) ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">Product Images</div>
                        <div class="card-body">
                            <label class="product-image-box w-100">
                                <i class="bi bi-cloud-upload fs-1"></i>
                                <h6 class="mt-3">Click to Upload Images</h6>
                                <small class="text-muted d-block">JPG, PNG, WEBP — max 2MB each</small>
                                <input type="file" multiple hidden name="product_image[]" id="product_image" accept="image/png,image/jpeg,image/webp">
                            </label>
                            <?php if (has_form_error('product_image')): ?><div class="invalid-feedback"><?= e(form_error('product_image')) ?></div><?php endif; ?>
                            <div id="imagePreview" class="d-flex flex-wrap mt-2"></div>

                            <?php if ($images): ?>
                                <hr>
                                <p class="mb-1 small fw-bold">Existing Images</p>
                                <div class="d-flex flex-wrap">
                                    <?php foreach ($images as $img): ?>
                                        <div class="text-center me-2 mb-2">
                                            <img src="<?= e($img['image']) ?>" class="img-thumb">
                                            <div class="form-check form-check-inline small">
                                                <input class="form-check-input" type="checkbox" name="delete_images[]" value="<?= (int)$img['id'] ?>" id="delimg<?= (int)$img['id'] ?>">
                                                <label class="form-check-label text-danger" for="delimg<?= (int)$img['id'] ?>">Remove</label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 save-btn">
                        <i class="bi bi-check-circle"></i>
                        <?= $isEdit ? 'Update Product' : 'Save Product' ?>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <?php
    include "./layouts/footer.php"; ?>
    <script>
        // --- Load subcategories dynamically when category changes ---
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

        // On page load (edit mode), preload subcategories for the already-selected category
        document.addEventListener('DOMContentLoaded', () => {
            const preselected = subcategorySelect.dataset.selected;
            if (categorySelect.value && preselected && preselected !== '0') {
                loadSubcategories(categorySelect.value, preselected);
            }
        });

        // --- Auto-generate slug from product name (only if slug field is empty) ---
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

        // --- Discount % display (regular vs sale price) ---
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

        // --- Specification repeater ---
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

        // --- Image preview ---
        const imageInput = document.getElementById('product_image');
        const imagePreview = document.getElementById('imagePreview');
        imageInput.addEventListener('change', () => {
            imagePreview.innerHTML = '';
            [...imageInput.files].forEach(file => {
                if (!file.type.startsWith('image/')) return;
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'img-thumb';
                    imagePreview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        });

        // --- Basic client-side validation (server re-validates everything) ---
        document.getElementById('productForm').addEventListener('submit', (e) => {
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