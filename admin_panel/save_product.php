<?php
declare(strict_types=1);
require __DIR__ . '/includes/db_conn.php';
require __DIR__ . '/config/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products.php');
    exit;
}

$editId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null;
$isEdit = $editId !== null;

// Preserve everything so the form can be re-populated if validation fails.
$old = $_POST;

/* ---------------------------------------------------------------
 * CSRF check
 * ------------------------------------------------------------- */
if (!csrf_verify($_POST['csrf_token'] ?? null)) {
    fail_validation(['Your session expired. Please try again.'], $old, $editId);
}

/* ---------------------------------------------------------------
 * Collect + trim input
 * ------------------------------------------------------------- */
$categoryId       = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
$subcategoryId    = filter_input(INPUT_POST, 'subcategory_id', FILTER_VALIDATE_INT) ?: null;
$productName      = trim((string)($_POST['product_name'] ?? ''));
$sku              = trim((string)($_POST['sku'] ?? ''));
$slug             = trim((string)($_POST['slug'] ?? ''));
$shortDescription = trim((string)($_POST['short_description'] ?? ''));
$description      = trim((string)($_POST['description'] ?? ''));
$features         = trim((string)($_POST['features'] ?? ''));
$specifications   = trim((string)($_POST['specifications'] ?? ''));
$care             = trim((string)($_POST['care_instruction'] ?? ''));
$shipping         = trim((string)($_POST['shipping_details'] ?? ''));
$warranty         = trim((string)($_POST['warranty_details'] ?? ''));
$seoTitle         = trim((string)($_POST['seo_title'] ?? ''));
$seoDescription   = trim((string)($_POST['seo_description'] ?? ''));
$seoKeywords      = trim((string)($_POST['seo_keywords'] ?? ''));
$productStatus    = $_POST['product_status'] ?? 'Active';

$regularPrice     = $_POST['regular_price'] ?? '';
$salePrice        = trim((string)($_POST['sale_price'] ?? ''));
$costPrice        = trim((string)($_POST['cost_price'] ?? ''));
$gst              = $_POST['gst_percentage'] ?? '';
$stockQty         = $_POST['stock_quantity'] ?? '0';
$minOrderQty      = $_POST['minimum_order_qty'] ?? '1';

$isFeatured   = isset($_POST['featured_product']) ? 1 : 0;
$isBestSeller = isset($_POST['best_seller']) ? 1 : 0;
$isNewArrival = isset($_POST['new_arrival']) ? 1 : 0;

$specNames  = $_POST['specification_name'] ?? [];
$specValues = $_POST['specification_value'] ?? [];

/* ---------------------------------------------------------------
 * Validation
 * ------------------------------------------------------------- */
$errors = [];
$fieldErrors = []; // keyed by field name, shown next to the input

if (!$categoryId) {
    $fieldErrors['category_id'] = 'Please select a category.';
} else {
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE id = :id");
    $stmt->execute([':id' => $categoryId]);
    if (!$stmt->fetch()) {
        $fieldErrors['category_id'] = 'Selected category does not exist.';
    }
}

if ($subcategoryId) {
    $stmt = $pdo->prepare("SELECT id FROM subcategories WHERE id = :id AND category_id = :cid");
    $stmt->execute([':id' => $subcategoryId, ':cid' => $categoryId]);
    if (!$stmt->fetch()) {
        $fieldErrors['subcategory_id'] = 'Selected sub-category is invalid for this category.';
    }
}

if ($productName === '') {
    $fieldErrors['product_name'] = 'Product name is required.';
} elseif (mb_strlen($productName) > 255) {
    $fieldErrors['product_name'] = 'Product name must be under 255 characters.';
}

if ($sku === '') {
    $fieldErrors['sku'] = 'SKU is required.';
} elseif (mb_strlen($sku) > 100) {
    $fieldErrors['sku'] = 'SKU must be under 100 characters.';
} else {
    $sql = "SELECT id FROM products WHERE sku = :sku" . ($isEdit ? " AND id != :id" : "");
    $params = [':sku' => $sku];
    if ($isEdit) $params[':id'] = $editId;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    if ($stmt->fetch()) {
        $fieldErrors['sku'] = 'This SKU is already in use.';
    }
}

if ($slug === '') {
    $slug = slugify($productName !== '' ? $productName : $sku);
} else {
    $slug = slugify($slug);
}
if ($slug !== '') {
    $sql = "SELECT id FROM products WHERE slug = :slug" . ($isEdit ? " AND id != :id" : "");
    $params = [':slug' => $slug];
    if ($isEdit) $params[':id'] = $editId;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    if ($stmt->fetch()) {
        // Make it unique automatically rather than rejecting outright.
        $slug .= '-' . substr(bin2hex(random_bytes(3)), 0, 5);
    }
}

if ($regularPrice === '' || !is_numeric($regularPrice) || (float)$regularPrice <= 0) {
    $fieldErrors['regular_price'] = 'Enter a valid regular price greater than 0.';
}

if ($salePrice !== '') {
    if (!is_numeric($salePrice) || (float)$salePrice < 0) {
        $fieldErrors['sale_price'] = 'Enter a valid sale price.';
    } elseif (is_numeric($regularPrice) && (float)$salePrice > (float)$regularPrice) {
        $fieldErrors['sale_price'] = 'Sale price cannot be greater than the regular price.';
    }
} else {
    $salePrice = null;
}

if ($costPrice !== '' && (!is_numeric($costPrice) || (float)$costPrice < 0)) {
    $fieldErrors['cost_price'] = 'Enter a valid cost price.';
} elseif ($costPrice === '') {
    $costPrice = null;
}

if ($gst === '' || !is_numeric($gst) || (float)$gst < 0 || (float)$gst > 100) {
    $fieldErrors['gst_percentage'] = 'GST must be a number between 0 and 100.';
}

if (!is_numeric($stockQty) || (int)$stockQty < 0) {
    $fieldErrors['stock_quantity'] = 'Stock quantity must be a non-negative whole number.';
}

if (!is_numeric($minOrderQty) || (int)$minOrderQty < 1) {
    $fieldErrors['minimum_order_qty'] = 'Minimum order quantity must be at least 1.';
}

if (!in_array($productStatus, ['Active', 'Inactive', 'Draft'], true)) {
    $productStatus = 'Active';
}

if ($seoTitle !== '' && mb_strlen($seoTitle) > 255) {
    $fieldErrors['seo_title'] = 'SEO title must be under 255 characters.';
}

/* ---------------------------------------------------------------
 * Image upload validation
 * ------------------------------------------------------------- */
$uploadDir = __DIR__ . '/uploads/products/';
$allowedMime = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
$maxSize = 2 * 1024 * 1024; // 2MB
$newImages = []; // [tmp_name, ext]

if (!empty($_FILES['product_image']['name'][0])) {
    $count = count($_FILES['product_image']['name']);
    for ($i = 0; $i < $count; $i++) {
        if ($_FILES['product_image']['error'][$i] === UPLOAD_ERR_NO_FILE) continue;

        if ($_FILES['product_image']['error'][$i] !== UPLOAD_ERR_OK) {
            $fieldErrors['product_image'] = 'One of the images failed to upload.';
            break;
        }
        $tmpName = $_FILES['product_image']['tmp_name'][$i];
        $size = $_FILES['product_image']['size'][$i];

        if ($size > $maxSize) {
            $fieldErrors['product_image'] = 'Each image must be 2MB or smaller.';
            break;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmpName);
        finfo_close($finfo);

        if (!isset($allowedMime[$mime])) {
            $fieldErrors['product_image'] = 'Only JPG, PNG, and WEBP images are allowed.';
            break;
        }

        $newImages[] = ['tmp' => $tmpName, 'ext' => $allowedMime[$mime]];
    }
}

// A brand-new product needs at least one image; an edit can rely on existing ones.
if (!$isEdit && empty($newImages)) {
    $fieldErrors['product_image'] = 'Please upload at least one product image.';
}

/* ---------------------------------------------------------------
 * Bail out if anything failed
 * ------------------------------------------------------------- */
if (!empty($fieldErrors)) {
    fail_validation($fieldErrors, $old, $editId);
}

/* ---------------------------------------------------------------
 * Persist (transaction: products + specifications + images)
 * ------------------------------------------------------------- */
try {
    $pdo->beginTransaction();

    $productData = [
        ':category_id'        => $categoryId,
        ':subcategory_id'     => $subcategoryId,
        ':product_name'       => $productName,
        ':slug'               => $slug,
        ':sku'                => $sku,
        ':short_description'  => $shortDescription,
        ':description'        => $description,
        ':specifications'     => $specifications,
        ':features'           => $features,
        ':care_instruction'   => $care,
        ':warranty_details'   => $warranty,
        ':shipping_details'   => $shipping,
        ':regular_price'      => $regularPrice,
        ':sale_price'         => $salePrice,
        ':cost_price'         => $costPrice,
        ':gst_percentage'     => $gst,
        ':stock_quantity'     => (int)$stockQty,
        ':minimum_order_qty'  => (int)$minOrderQty,
        ':product_status'     => $productStatus,
        ':is_featured'        => $isFeatured,
        ':is_best_seller'     => $isBestSeller,
        ':is_new_arrival'     => $isNewArrival,
        ':seo_title'          => $seoTitle,
        ':seo_keywords'       => $seoKeywords,
        ':seo_description'    => $seoDescription,
    ];

    if ($isEdit) {
        $sql = "UPDATE products SET
                    category_id = :category_id, subcategory_id = :subcategory_id,
                    product_name = :product_name, slug = :slug, sku = :sku,
                    short_description = :short_description, description = :description,
                    specifications = :specifications, features = :features,
                    care_instruction = :care_instruction, warranty_details = :warranty_details,
                    shipping_details = :shipping_details,
                    regular_price = :regular_price, sale_price = :sale_price, cost_price = :cost_price,
                    gst_percentage = :gst_percentage, stock_quantity = :stock_quantity,
                    minimum_order_qty = :minimum_order_qty, product_status = :product_status,
                    is_featured = :is_featured, is_best_seller = :is_best_seller, is_new_arrival = :is_new_arrival,
                    seo_title = :seo_title, seo_keywords = :seo_keywords, seo_description = :seo_description
                WHERE id = :id";
        $productData[':id'] = $editId;
        $pdo->prepare($sql)->execute($productData);
        $productId = $editId;
    } else {
        $sql = "INSERT INTO products (
                    category_id, subcategory_id, product_name, slug, sku,
                    short_description, description, specifications, features,
                    care_instruction, warranty_details, shipping_details,
                    regular_price, sale_price, cost_price, gst_percentage,
                    stock_quantity, minimum_order_qty, product_status,
                    is_featured, is_best_seller, is_new_arrival,
                    seo_title, seo_keywords, seo_description
                ) VALUES (
                    :category_id, :subcategory_id, :product_name, :slug, :sku,
                    :short_description, :description, :specifications, :features,
                    :care_instruction, :warranty_details, :shipping_details,
                    :regular_price, :sale_price, :cost_price, :gst_percentage,
                    :stock_quantity, :minimum_order_qty, :product_status,
                    :is_featured, :is_best_seller, :is_new_arrival,
                    :seo_title, :seo_keywords, :seo_description
                )";
        $pdo->prepare($sql)->execute($productData);
        $productId = (int)$pdo->lastInsertId();
    }

    // --- Specifications: wipe and re-insert (simplest way to keep in sync with the repeater UI) ---
    $pdo->prepare("DELETE FROM product_specifications WHERE product_id = :id")->execute([':id' => $productId]);
    $specStmt = $pdo->prepare(
        "INSERT INTO product_specifications (product_id, specification_name, specification_value) VALUES (:pid, :name, :value)"
    );
    foreach ($specNames as $i => $name) {
        $name = trim((string)$name);
        $value = trim((string)($specValues[$i] ?? ''));
        if ($name === '' && $value === '') continue;
        $specStmt->execute([':pid' => $productId, ':name' => $name, ':value' => $value]);
    }

    // --- Remove any images the user checked for deletion ---
    if (!empty($_POST['delete_images']) && is_array($_POST['delete_images'])) {
        $ids = array_filter(array_map('intval', $_POST['delete_images']));
        if ($ids) {
            $stmt = $pdo->prepare("SELECT id, image FROM product_images WHERE id = ? AND product_id = ?");
            foreach ($ids as $imgId) {
                $stmt->execute([$imgId, $productId]);
                if ($row = $stmt->fetch()) {
                    $path = __DIR__ . '/' . ltrim($row['image'], '/');
                    if (is_file($path)) @unlink($path);
                }
            }
            $in = implode(',', array_fill(0, count($ids), '?'));
            $pdo->prepare("DELETE FROM product_images WHERE id IN ($in) AND product_id = ?")
                ->execute([...$ids, $productId]);
        }
    }

    // --- Save newly uploaded images ---
    if (!empty($newImages)) {
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $hasThumb = (bool) $pdo->prepare("SELECT id FROM product_images WHERE product_id = :id AND is_thumbnail = 1")
            ->execute([':id' => $productId]);
        $checkThumb = $pdo->prepare("SELECT COUNT(*) c FROM product_images WHERE product_id = :id AND is_thumbnail = 1");
        $checkThumb->execute([':id' => $productId]);
        $thumbExists = (int)$checkThumb->fetch()['c'] > 0;

        $imgStmt = $pdo->prepare(
            "INSERT INTO product_images (product_id, image, alt_text, sort_order, is_thumbnail) VALUES (:pid, :image, :alt, :sort, :thumb)"
        );
        $sortStmt = $pdo->prepare("SELECT COALESCE(MAX(sort_order), -1) m FROM product_images WHERE product_id = :id");
        $sortStmt->execute([':id' => $productId]);
        $nextSort = (int)$sortStmt->fetch()['m'] + 1;

        foreach ($newImages as $img) {
            $filename = 'prod_' . $productId . '_' . bin2hex(random_bytes(6)) . '.' . $img['ext'];
            move_uploaded_file($img['tmp'], $uploadDir . $filename);
            $imgStmt->execute([
                ':pid'   => $productId,
                ':image' => 'uploads/' . $filename,
                ':alt'   => $productName,
                ':sort'  => $nextSort++,
                ':thumb' => $thumbExists ? 0 : 1,
            ]);
            $thumbExists = true; // only the very first uploaded image (ever) becomes the thumbnail
        }
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    error_log('Product save failed: ' . $e->getMessage());
    flash('error', 'Something went wrong while saving the product. Please try again.');
    header('Location: products.php' . ($isEdit ? "?id={$editId}" : ''));
    exit;
}

flash('success', $isEdit ? 'Product updated successfully.' : 'Product added successfully.');
header('Location: products.php?id=' . $productId);
exit;
