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
    header('Location: edit_product_images.php?id=' . $editId);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM products WHERE id = :id");
$stmt->execute([':id' => $editId]);
if (!$stmt->fetch()) {
    flash('error', 'Product not found.');
    header('Location: products.php');
    exit;
}

$errors = [];
$uploadDir = __DIR__ . '/uploads/products/';           // actual filesystem folder files are written to
$publicPath = 'admin_panel/uploads/products/';         // the path stored in DB / used in <img src>
$maxBytes = 2 * 1024 * 1024; // 2MB
$allowedMime = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

// ---- 1) Handle deletions first, so we don't waste work updating/uploading
// ----    rows that are about to disappear anyway. ----
$deleteIds = array_filter(array_map('intval', $_POST['delete_images'] ?? []));
if ($deleteIds) {
    $in = implode(',', array_fill(0, count($deleteIds), '?'));
    $sel = $pdo->prepare("SELECT id, image FROM product_images WHERE product_id = ? AND id IN ($in)");
    $sel->execute(array_merge([$editId], $deleteIds));
    $rowsToDelete = $sel->fetchAll();

    $del = $pdo->prepare("DELETE FROM product_images WHERE product_id = ? AND id IN ($in)");
    $del->execute(array_merge([$editId], $deleteIds));

    // Delete the physical file for each row we just removed from the table.
    // We deliberately do NOT try to rebuild a filesystem path from the stored
    // `image` value (that value is a public URL/relative path — e.g.
    // "admin_panel/uploads/products/xxx.jpg" — and its prefix depends on how
    // your app serves files, which may not match the real folder structure on
    // disk). Instead we take just the filename and join it to $uploadDir,
    // which IS the real, known filesystem folder these files were saved into.
    foreach ($rowsToDelete as $row) {
        $filename = basename((string) $row['image']);
        if ($filename === '') {
            continue;
        }
        $filePath = $uploadDir . $filename;
        if (is_file($filePath)) {
            @unlink($filePath);
        }
    }
}

// ---- 2) Update existing (non-deleted) rows: alt text, sort order, thumbnail flag ----
// Mirrors: UPDATE product_images SET id=..., product_id=..., image=..., alt_text=...,
//          sort_order=..., is_thumbnail=... WHERE id = ...
// `id` and `image` for an existing row are never changed here (id is the row's own PK;
// the file path only changes on a fresh upload) — they're just re-supplied as-is so
// the statement matches the full column set you specified.
$thumbnailId = filter_input(INPUT_POST, 'thumbnail_id', FILTER_VALIDATE_INT) ?: null;
$altTextInput = $_POST['image_alt_text'] ?? [];    // [imageId => alt text]
$sortOrderInput = $_POST['image_sort_order'] ?? []; // [imageId => sort order]

$existingStmt = $pdo->prepare("SELECT id, product_id, image FROM product_images WHERE product_id = :pid");
$existingStmt->execute([':pid' => $editId]);
$existingImages = $existingStmt->fetchAll();

$updateStmt = $pdo->prepare(
    "UPDATE product_images
     SET id = :id,
         product_id = :product_id,
         image = :image,
         alt_text = :alt_text,
         sort_order = :sort_order,
         is_thumbnail = :is_thumbnail
     WHERE id = :where_id"
);

$updated = 0;
foreach ($existingImages as $row) {
    $imgId = (int) $row['id'];
    if (in_array($imgId, $deleteIds, true)) {
        continue; // already removed above
    }

    $altText = trim((string) ($altTextInput[$imgId] ?? ''));
    $sortOrder = isset($sortOrderInput[$imgId]) && $sortOrderInput[$imgId] !== ''
        ? (int) $sortOrderInput[$imgId]
        : 0;
    $isThumbnail = ($thumbnailId !== null && $thumbnailId === $imgId) ? 1 : 0;

    $updateStmt->execute([
        ':id'           => $imgId,
        ':product_id'   => $editId,
        ':image'        => $row['image'],
        ':alt_text'     => $altText,
        ':sort_order'   => $sortOrder,
        ':is_thumbnail' => $isThumbnail,
        ':where_id'     => $imgId,
    ]);
    $updated++;
}

// ---- 3) Handle new uploads ----
$uploaded = 0;
if (!empty($_FILES['product_image']['name'][0])) {
    $count = count($_FILES['product_image']['name']);

    // New images append after current max sort_order.
    $sortStmt = $pdo->prepare("SELECT COALESCE(MAX(sort_order), 0) AS m FROM product_images WHERE product_id = ?");
    $sortStmt->execute([$editId]);
    $nextSort = (int) $sortStmt->fetch()['m'] + 1;

    // If no image is currently marked (or being marked) as thumbnail, the first
    // successfully uploaded file in this batch becomes the thumbnail automatically.
    $needsThumbnail = ($thumbnailId === null);
    if (!$needsThumbnail) {
        // The chosen thumbnail_id might belong to an image being deleted in this
        // same request; if so, fall back to auto-assigning the first new upload.
        $needsThumbnail = in_array($thumbnailId, $deleteIds, true);
    }

    $insert = $pdo->prepare(
        "INSERT INTO product_images (product_id, image, alt_text, sort_order, is_thumbnail)
         VALUES (:pid, :image, :alt_text, :sort, :is_thumbnail)"
    );

    for ($i = 0; $i < $count; $i++) {
        if ($_FILES['product_image']['error'][$i] === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        if ($_FILES['product_image']['error'][$i] !== UPLOAD_ERR_OK) {
            $errors['product_image'] = 'One or more files failed to upload. Please try again.';
            continue;
        }

        $tmpName = $_FILES['product_image']['tmp_name'][$i];
        $size = $_FILES['product_image']['size'][$i];
        $mime = mime_content_type($tmpName) ?: '';

        if ($size > $maxBytes) {
            $errors['product_image'] = 'Each image must be 2MB or smaller.';
            continue;
        }
        if (!isset($allowedMime[$mime])) {
            $errors['product_image'] = 'Only JPG, PNG, and WEBP images are allowed.';
            continue;
        }

        $ext = $allowedMime[$mime];
        $filename = 'prod_' . $editId . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $destination = $uploadDir . $filename;

        if (move_uploaded_file($tmpName, $destination)) {
            $makeThumbnail = $needsThumbnail ? 1 : 0;
            $insert->execute([
                ':pid'          => $editId,
                ':image'        => $publicPath . $filename,
                ':alt_text'     => '',
                ':sort'         => $nextSort++,
                ':is_thumbnail' => $makeThumbnail,
            ]);
            if ($makeThumbnail) {
                $needsThumbnail = false; // only the first new upload gets it
            }
            $uploaded++;
        } else {
            $errors['product_image'] = 'Could not save one of the uploaded files.';
        }
    }
}

if (!empty($errors)) {
    // Deletions/updates/uploads that already succeeded are kept (each was its own
    // DB write); we just report what failed so the admin can retry the failed file(s).
    if (function_exists('set_validation_errors')) {
        set_validation_errors($errors, []);
    } else {
        flash('error', implode(' ', $errors));
    }
    header('Location: edit_product_images.php?id=' . $editId);
    exit;
}

$parts = [];
if ($deleteIds) {
    $parts[] = count($deleteIds) . ' image(s) removed';
}
if ($updated) {
    $parts[] = $updated . ' image(s) updated';
}
if ($uploaded) {
    $parts[] = $uploaded . ' image(s) uploaded';
}
flash('success', $parts ? implode(', ', $parts) . '.' : 'No changes made.');
header('Location: edit_product_images.php?id=' . $editId);
exit;