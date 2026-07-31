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

    $stmt = $pdo->prepare("SELECT id, product_name FROM products WHERE id = :id");
    $stmt->execute([':id' => $editId]);
    $product = $stmt->fetch();

    if (!$product) {
        flash('error', 'Product not found.');
        header('Location: products.php');
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = :id ORDER BY sort_order ASC");
    $stmt->execute([':id' => $editId]);
    $images = $stmt->fetchAll();

    $activeTab = 'images';
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
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: .375rem;
            margin: 4px;
            border: 1px solid #dee2e6
        }
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

        <div class="row">
            <div class="col-lg-6">
                <form action="update_product_images.php" method="post" enctype="multipart/form-data" id="imagesForm" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="id" value="<?= (int) $editId ?>">

                    <div class="card mb-4">
                        <div class="card-header">Upload New Images</div>
                        <div class="card-body">
                            <label class="product-image-box w-100">
                                <i class="bi bi-cloud-upload fs-1"></i>
                                <h6 class="mt-3">Click to Upload Images</h6>
                                <small class="text-muted d-block">JPG, PNG, WEBP &mdash; max 2MB each</small>
                                <input type="file" multiple hidden name="product_image[]" id="product_image" accept="image/png,image/jpeg,image/webp">
                            </label>
                            <?php if (has_form_error('product_image')): ?><div class="invalid-feedback"><?= e(form_error('product_image')) ?></div><?php endif; ?>
                            <div id="imagePreview" class="d-flex flex-wrap mt-2"></div>

                            <?php if ($images): ?>
                                <hr>
                                <p class="mb-2 small fw-bold">Existing Images</p>
                                <div class="table-responsive">
                                    <table class="table align-middle">
                                        <thead>
                                            <tr class="small text-muted">
                                                <th>Image</th>
                                                <th>Alt Text</th>
                                                <th style="width:100px">Sort</th>
                                                <th style="width:90px">Thumbnail</th>
                                                <th style="width:70px">Remove</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($images as $img): ?>
                                                <tr>
                                                    <td><img src="../<?= e($img['image']) ?>" class="img-thumb"></td>
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm"
                                                            name="image_alt_text[<?= (int) $img['id'] ?>]"
                                                            placeholder="Describe this image"
                                                            value="<?= e($img['alt_text'] ?? '') ?>">
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control form-control-sm"
                                                            name="image_sort_order[<?= (int) $img['id'] ?>]"
                                                            value="<?= (int) $img['sort_order'] ?>" min="0">
                                                    </td>
                                                    <td class="text-center">
                                                        <input class="form-check-input" type="radio" name="thumbnail_id"
                                                            value="<?= (int) $img['id'] ?>"
                                                            id="thumb<?= (int) $img['id'] ?>"
                                                            <?= !empty($img['is_thumbnail']) ? 'checked' : '' ?>>
                                                        <label class="form-check-label small d-block" for="thumb<?= (int) $img['id'] ?>">Use</label>
                                                    </td>
                                                    <td class="text-center">
                                                        <input class="form-check-input" type="checkbox" name="delete_images[]"
                                                            value="<?= (int) $img['id'] ?>" id="delimg<?= (int) $img['id'] ?>">
                                                        <label class="form-check-label text-danger small d-block" for="delimg<?= (int) $img['id'] ?>">Remove</label>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <p class="small text-muted mb-0">Pick "Use" to set which image shows as the product thumbnail. Leave it unset to keep the current thumbnail.</p>
                            <?php else: ?>
                                <hr>
                                <p class="mb-0 small text-muted">No images uploaded yet for this product.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary save-btn">
                        <i class="bi bi-check-circle"></i>
                        Save & Continue to Content
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php
    include "./layouts/footer.php"; ?>
    <script>
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
    </script>
<?php
} else {
    header("Location:index.php");
}
?>