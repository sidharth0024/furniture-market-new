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
 * Schema helper: only write to columns that actually exist.
 *
 * The `products` table's real column set can differ between
 * environments (e.g. a migration applied locally but not yet run on
 * production, or vice versa). Rather than hard-coding a column list
 * that silently breaks the whole INSERT/UPDATE with an "Unknown
 * column" SQL error whenever it's out of sync, this reads the actual
 * columns at request time and only writes fields that exist.
 * ------------------------------------------------------------- */
function table_columns(PDO $pdo, string $table): array
{
    static $cache = [];
    if (isset($cache[$table])) {
        return $cache[$table];
    }
    $cols = [];
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM `{$table}`");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $cols[] = $row['Field'];
        }
    } catch (Throwable $e) {
        error_log("table_columns({$table}) failed: " . $e->getMessage());
    }
    $cache[$table] = $cols;
    return $cols;
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
 *
 * MIME detection no longer depends solely on the `fileinfo`
 * extension. If it's missing/disabled on a given host,
 * finfo_open()/finfo_file() silently return false, $mime never
 * matches $allowedMime, and every upload gets rejected — which,
 * combined with "a new product requires an image," blocks every
 * new product from saving. getimagesize() is bundled with core PHP
 * and used as a fallback.
 * ------------------------------------------------------------- */
function detect_image_mime(string $path): ?string
{
    if (function_exists('finfo_open')) {
        $finfo = @finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mime = finfo_file($finfo, $path);
            finfo_close($finfo);
            if ($mime) {
                return $mime;
            }
        }
    }
    $info = @getimagesize($path);
    return $info['mime'] ?? null;
}

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
            error_log('Product image upload error code: ' . $_FILES['product_image']['error'][$i]);
            break;
        }
        $tmpName = $_FILES['product_image']['tmp_name'][$i];
        $size = $_FILES['product_image']['size'][$i];

        if ($size > $maxSize) {
            $fieldErrors['product_image'] = 'Each image must be 2MB or smaller.';
            break;
        }

        $mime = detect_image_mime($tmpName);

        if (!$mime || !isset($allowedMime[$mime])) {
            $fieldErrors['product_image'] = 'Only JPG, PNG, and WEBP images are allowed.';
            error_log('Product image rejected, detected mime: ' . var_export($mime, true));
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

    // Every field we'd *like* to save, keyed by column name.
    $desiredProductData = [
        'category_id'        => $categoryId,
        'subcategory_id'     => $subcategoryId,
        'product_name'       => $productName,
        'slug'               => $slug,
        'sku'                => $sku,
        'short_description'  => $shortDescription,
        'description'        => $description,
        'specifications'     => $specifications,
        'features'           => $features,
        'care_instruction'   => $care,
        'warranty_details'   => $warranty,
        'shipping_details'   => $shipping,
        'regular_price'      => $regularPrice,
        'sale_price'         => $salePrice,
        'cost_price'         => $costPrice,
        'gst_percentage'     => $gst,
        'stock_quantity'     => (int)$stockQty,
        'minimum_order_qty'  => (int)$minOrderQty,
        'product_status'     => $productStatus,
        'is_featured'        => $isFeatured,
        'is_best_seller'     => $isBestSeller,
        'is_new_arrival'     => $isNewArrival,
        'seo_title'          => $seoTitle,
        'seo_keywords'       => $seoKeywords,
        'seo_description'    => $seoDescription,
    ];

    $productCols = table_columns($pdo, 'products');

    // Keep only fields that actually exist as columns on THIS database.
    // Anything dropped here is logged so it's easy to spot "why didn't
    // my SEO title save" style reports and trace them back to a
    // missing migration rather than a code bug.
    $productData = [];
    foreach ($desiredProductData as $col => $value) {
        if (in_array($col, $productCols, true)) {
            $productData[":{$col}"] = $value;
        }
    }
    $droppedCols = array_diff(array_keys($desiredProductData), $productCols);
    if (!empty($droppedCols)) {
        error_log('products table is missing columns (values submitted but NOT saved): ' . implode(', ', $droppedCols));
    }

    if (empty($productData)) {
        throw new RuntimeException('products table has none of the expected columns — check the database connection/schema.');
    }

    if ($isEdit) {
        $setSql = implode(', ', array_map(
            static fn(string $ph) => '`' . ltrim($ph, ':') . '` = ' . $ph,
            array_keys($productData)
        ));
        $sql = "UPDATE products SET {$setSql} WHERE id = :id";
        $productData[':id'] = $editId;
        $pdo->prepare($sql)->execute($productData);
        $productId = $editId;
    } else {
        $colSql = implode(', ', array_map(
            static fn(string $ph) => '`' . ltrim($ph, ':') . '`',
            array_keys($productData)
        ));
        $phSql = implode(', ', array_keys($productData));
        $sql = "INSERT INTO products ({$colSql}) VALUES ({$phSql})";
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
            // array_merge() rather than [...$ids, $productId]: the spread
            // form needs PHP 7.4+ and causes a parse error — which fails
            // the WHOLE file, for every request — on older PHP builds.
            $pdo->prepare("DELETE FROM product_images WHERE id IN ($in) AND product_id = ?")
                ->execute(array_merge($ids, [$productId]));
        }
    }

    // --- Save newly uploaded images ---
    if (!empty($newImages)) {
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                throw new RuntimeException("Could not create upload directory: {$uploadDir}");
            }
        }
        if (!is_writable($uploadDir)) {
            throw new RuntimeException("Upload directory is not writable: {$uploadDir}");
        }

        $imgStmt = $pdo->prepare(
            "INSERT INTO product_images (product_id, image, alt_text, sort_order, is_thumbnail) VALUES (:pid, :image, :alt, :sort, 0)"
        );
        $sortStmt = $pdo->prepare("SELECT COALESCE(MAX(sort_order), -1) m FROM product_images WHERE product_id = :id");
        $sortStmt->execute([':id' => $productId]);
        $nextSort = (int)$sortStmt->fetch()['m'] + 1;

        foreach ($newImages as $img) {
            $filename = 'prod_' . $productId . '_' . bin2hex(random_bytes(6)) . '.' . $img['ext'];
            $destination = $uploadDir . $filename;

            if (!move_uploaded_file($img['tmp'], $destination)) {
                throw new RuntimeException("Failed to move uploaded file to {$destination}. Check directory permissions.");
            }

            $imgStmt->execute([
                ':pid'   => $productId,
                ':image' => 'admin_panel/uploads/products/' . $filename,
                ':alt'   => $productName,
                ':sort'  => $nextSort++,
            ]);
        }
    }

    // --- Keep exactly one image flagged as the thumbnail, and mirror it
    //     onto products.thumbnail (when that column exists), regardless
    //     of whether this request added images, deleted images, both,
    //     or neither. ---
    $thumbStmt = $pdo->prepare(
        "SELECT id, image FROM product_images WHERE product_id = :id ORDER BY is_thumbnail DESC, sort_order ASC, id ASC"
    );
    $thumbStmt->execute([':id' => $productId]);
    $remainingImages = $thumbStmt->fetchAll();

    if (empty($remainingImages)) {
        if (in_array('thumbnail', $productCols, true)) {
            $pdo->prepare("UPDATE products SET thumbnail = NULL WHERE id = :id")->execute([':id' => $productId]);
        }
    } else {
        $chosenThumb = $remainingImages[0];
        $pdo->prepare("UPDATE product_images SET is_thumbnail = 0 WHERE product_id = :id")->execute([':id' => $productId]);
        $pdo->prepare("UPDATE product_images SET is_thumbnail = 1 WHERE id = :img_id")->execute([':img_id' => $chosenThumb['id']]);

        if (in_array('thumbnail', $productCols, true)) {
            $pdo->prepare("UPDATE products SET thumbnail = :thumb WHERE id = :id")
                ->execute([':thumb' => $chosenThumb['image'], ':id' => $productId]);
        }
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    error_log(sprintf(
        'Product save failed: %s in %s:%d',
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    ));
    flash('error', 'Something went wrong while saving the product. Please try again.');
    header('Location: products.php' . ($isEdit ? "?id={$editId}" : ''));
    exit;
}

flash('success', $isEdit ? 'Product updated successfully.' : 'Product added successfully.');
header('Location: products.php?id=' . $productId);
exit;
