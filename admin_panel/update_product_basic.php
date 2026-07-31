<?php

declare(strict_types=1);
session_start();
if (!isset($_SESSION['adminId'])) {
    header('Location: index.php');
    exit;
}

require __DIR__ . '/includes/db_conn.php';
require __DIR__ . '/config/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products.php');
    exit;
}

$editId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null;
if (!$editId) {
    flash('error', 'No product specified.');
    header('Location: products.php');
    exit;
}

// Confirm CSRF and confirm the product actually exists before doing anything else.
if (!csrf_verify($_POST['csrf_token'] ?? '')) {
    flash('error', 'Your session expired, please try again.');
    header('Location: edit_product_basic.php?id=' . $editId);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM products WHERE id = :id");
$stmt->execute([':id' => $editId]);
if (!$stmt->fetch()) {
    flash('error', 'Product not found.');
    header('Location: products.php');
    exit;
}

// ---- Collect ONLY the fields this form owns. Everything else in the products
// ---- row (description, features, specs, images, etc.) is left untouched. ----
$input = [
    'category_id'        => filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT) ?: null,
    'subcategory_id'      => filter_input(INPUT_POST, 'subcategory_id', FILTER_VALIDATE_INT) ?: null,
    'product_status'      => trim((string) ($_POST['product_status'] ?? 'Active')),
    'product_name'        => trim((string) ($_POST['product_name'] ?? '')),
    'sku'                 => trim((string) ($_POST['sku'] ?? '')),
    'slug'                => trim((string) ($_POST['slug'] ?? '')),
    'short_description'   => trim((string) ($_POST['short_description'] ?? '')),
    'regular_price'       => trim((string) ($_POST['regular_price'] ?? '')),
    'sale_price'          => trim((string) ($_POST['sale_price'] ?? '')),
    'cost_price'          => trim((string) ($_POST['cost_price'] ?? '')),
    'gst_percentage'      => trim((string) ($_POST['gst_percentage'] ?? '')),
    'stock_quantity'      => trim((string) ($_POST['stock_quantity'] ?? '0')),
    'minimum_order_qty'   => trim((string) ($_POST['minimum_order_qty'] ?? '1')),
    'seo_title'           => trim((string) ($_POST['seo_title'] ?? '')),
    'seo_description'     => trim((string) ($_POST['seo_description'] ?? '')),
    'seo_keywords'        => trim((string) ($_POST['seo_keywords'] ?? '')),
    // Checkboxes: absent from $_POST means "unchecked". We still record the key
    // so edit_product_basic.php's is_checked() can tell "unchecked" apart from
    // "no data submitted at all" (which never happens for this form).
    'featured_product'    => isset($_POST['featured_product']) ? 1 : 0,
    'best_seller'         => isset($_POST['best_seller']) ? 1 : 0,
    'new_arrival'         => isset($_POST['new_arrival']) ? 1 : 0,
];

$errors = [];

if ($input['category_id'] === null) {
    $errors['category_id'] = 'Please select a category.';
}
if ($input['product_name'] === '') {
    $errors['product_name'] = 'Product name is required.';
} elseif (mb_strlen($input['product_name']) > 255) {
    $errors['product_name'] = 'Product name must be 255 characters or fewer.';
}
if ($input['sku'] === '') {
    $errors['sku'] = 'SKU is required.';
} else {
    // SKU must stay unique across products, excluding this product itself.
    $dup = $pdo->prepare("SELECT id FROM products WHERE sku = :sku AND id != :id");
    $dup->execute([':sku' => $input['sku'], ':id' => $editId]);
    if ($dup->fetch()) {
        $errors['sku'] = 'This SKU is already used by another product.';
    }
}
if ($input['regular_price'] === '' || !is_numeric($input['regular_price']) || (float) $input['regular_price'] < 0) {
    $errors['regular_price'] = 'Enter a valid regular price.';
}
if ($input['sale_price'] !== '' && (!is_numeric($input['sale_price']) || (float) $input['sale_price'] < 0)) {
    $errors['sale_price'] = 'Enter a valid sale price.';
} elseif (
    $input['sale_price'] !== '' && $input['regular_price'] !== '' && is_numeric($input['regular_price'])
    && (float) $input['sale_price'] > (float) $input['regular_price']
) {
    $errors['sale_price'] = 'Sale price cannot exceed regular price.';
}
if ($input['gst_percentage'] === '' || !is_numeric($input['gst_percentage'])) {
    $errors['gst_percentage'] = 'Enter a valid GST percentage.';
}
if ($input['stock_quantity'] !== '' && !ctype_digit($input['stock_quantity'])) {
    $errors['stock_quantity'] = 'Stock quantity must be a whole number.';
}
if ($input['minimum_order_qty'] !== '' && !ctype_digit($input['minimum_order_qty'])) {
    $errors['minimum_order_qty'] = 'Minimum order qty must be a whole number.';
}

// Auto-generate the slug from the name if left blank, then guarantee uniqueness.
if ($input['slug'] === '') {
    $base = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $input['product_name']), '-'));
    $input['slug'] = $base !== '' ? $base : 'product-' . $editId;
}
if (empty($errors)) {
    $slugCheck = $pdo->prepare("SELECT id FROM products WHERE slug = :slug AND id != :id");
    $candidate = $input['slug'];
    $suffix = 1;
    do {
        $slugCheck->execute([':slug' => $candidate, ':id' => $editId]);
        if ($slugCheck->fetch()) {
            $candidate = $input['slug'] . '-' . (++$suffix);
        } else {
            break;
        }
    } while (true);
    $input['slug'] = $candidate;
}

if (!empty($errors)) {
    // set_validation_errors($errors, $input);
    header('Location: edit_product_basic.php?id=' . $editId);
    exit;
}

// ---- Update ONLY the columns this form owns. Any column not listed here is
// ---- untouched, so content (part 3) and images (part 2) data survives. ----
$sql = "UPDATE products SET
            category_id = :category_id,
            subcategory_id = :subcategory_id,
            product_status = :product_status,
            product_name = :product_name,
            sku = :sku,
            slug = :slug,
            short_description = :short_description,
            regular_price = :regular_price,
            sale_price = :sale_price,
            cost_price = :cost_price,
            gst_percentage = :gst_percentage,
            stock_quantity = :stock_quantity,
            minimum_order_qty = :minimum_order_qty,
            seo_title = :seo_title,
            seo_description = :seo_description,
            seo_keywords = :seo_keywords,
            is_featured = :is_featured,
            is_best_seller = :is_best_seller,
            is_new_arrival = :is_new_arrival,
            updated_at = NOW()
        WHERE id = :id";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':category_id'        => $input['category_id'],
    ':subcategory_id'      => $input['subcategory_id'],
    ':product_status'      => $input['product_status'],
    ':product_name'        => $input['product_name'],
    ':sku'                 => $input['sku'],
    ':slug'                => $input['slug'],
    ':short_description'   => $input['short_description'],
    ':regular_price'       => $input['regular_price'],
    ':sale_price'          => $input['sale_price'] === '' ? null : $input['sale_price'],
    ':cost_price'          => $input['cost_price'] === '' ? null : $input['cost_price'],
    ':gst_percentage'      => $input['gst_percentage'],
    ':stock_quantity'      => $input['stock_quantity'] === '' ? 0 : $input['stock_quantity'],
    ':minimum_order_qty'   => $input['minimum_order_qty'] === '' ? 1 : $input['minimum_order_qty'],
    ':seo_title'           => $input['seo_title'],
    ':seo_description'     => $input['seo_description'],
    ':seo_keywords'        => $input['seo_keywords'],
    ':is_featured'         => $input['featured_product'],
    ':is_best_seller'      => $input['best_seller'],
    ':is_new_arrival'      => $input['new_arrival'],
    ':id'                  => $editId,
]);

flash('success', 'Basic details updated.');
header('Location: edit_product_basic.php?id=' . $editId);
exit;
