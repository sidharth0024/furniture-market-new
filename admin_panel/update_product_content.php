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

if (!csrf_verify($_POST['csrf_token'] ?? '')) {
    flash('error', 'Your session expired, please try again.');
    header('Location: edit_product_content.php?id=' . $editId);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM products WHERE id = :id");
$stmt->execute([':id' => $editId]);
if (!$stmt->fetch()) {
    flash('error', 'Product not found.');
    header('Location: products.php');
    exit;
}

// ---- Fields this form owns. Summernote content is rich HTML from a trusted
// ---- admin editor, so it is stored as-is (not escaped) — same as the original page.
$input = [
    'description'       => (string) ($_POST['description'] ?? ''),
    'features'           => (string) ($_POST['features'] ?? ''),
    'specifications'      => (string) ($_POST['specifications'] ?? ''),
    'care_instruction'    => (string) ($_POST['care_instruction'] ?? ''),
    'shipping_details'    => (string) ($_POST['shipping_details'] ?? ''),
    'warranty_details'    => (string) ($_POST['warranty_details'] ?? ''),
];

$specNames = $_POST['specification_name'] ?? [];
$specValues = $_POST['specification_value'] ?? [];

$errors = [];
// Content fields are optional free text; no hard validation required here.
// If you need e.g. a max length, add checks per-field and repopulate $input on failure.

if (!empty($errors)) {
    $old = $input;
    $old['specification_name'] = $specNames;
    $old['specification_value'] = $specValues;
    // set_validation_errors($errors, $old);
    header('Location: edit_product_content.php?id=' . $editId);
    exit;
}

$pdo->beginTransaction();
try {
    // ---- Update ONLY the content columns; basic details, pricing, images, etc. untouched ----
    $sql = "UPDATE products SET
                description = :description,
                features = :features,
                specifications = :specifications,
                care_instruction = :care_instruction,
                shipping_details = :shipping_details,
                warranty_details = :warranty_details,
                updated_at = NOW()
            WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':description'       => $input['description'],
        ':features'           => $input['features'],
        ':specifications'      => $input['specifications'],
        ':care_instruction'    => $input['care_instruction'],
        ':shipping_details'    => $input['shipping_details'],
        ':warranty_details'    => $input['warranty_details'],
        ':id'                  => $editId,
    ]);

    // ---- Rebuild structured specifications: this table is owned entirely by
    // ---- this form, so it's safe to replace the full set for this product.
    $del = $pdo->prepare("DELETE FROM product_specifications WHERE product_id = :id");
    $del->execute([':id' => $editId]);

    $insert = $pdo->prepare(
        "INSERT INTO product_specifications (product_id, specification_name, specification_value) VALUES (:pid, :name, :val)"
    );
    foreach ($specNames as $i => $name) {
        $name = trim((string) $name);
        $value = trim((string) ($specValues[$i] ?? ''));
        if ($name === '' && $value === '') {
            continue; // skip fully-empty rows
        }
        $insert->execute([
            ':pid'  => $editId,
            ':name' => $name,
            ':val'  => $value,
        ]);
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    flash('error', 'Could not save content changes. Please try again.');
    header('Location: edit_product_content.php?id=' . $editId);
    exit;
}

flash('success', 'Product content updated.');
header('Location: edit_product_content.php?id=' . $editId);
exit;
