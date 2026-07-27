<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/includes/db_conn.php';

if (!isset($_SESSION['adminId']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// ── Input sanitisation ────────────────────────────────────────
$reviewId     = !empty($_POST['review_id'])     ? (int)$_POST['review_id']  : null;
$productId    = !empty($_POST['product_id'])    ? (int)$_POST['product_id'] : null;
$reviewerName = trim($_POST['reviewer_name']    ?? '');
$reviewText   = trim($_POST['review_text']      ?? '');
$stars        = max(1, min(5, (int)($_POST['stars']      ?? 5)));
$status       = (int)($_POST['status']          ?? 1);
$sortOrder    = (int)($_POST['sort_order']      ?? 0);
$redirectPid  = (int)($_POST['redirect_product'] ?? 0);

if (!$productId || !$reviewerName) {
    header('Location: product_reviews.php?msg=Product+and+reviewer+name+are+required&type=danger&product_id=' . $redirectPid);
    exit;
}

// ── Upload helper ─────────────────────────────────────────────
function uploadFile(array $file, string $destDir): string {
    if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) return '';
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','webp'];
    if (!in_array($ext, $allowed, true)) return '';
    if (!is_dir($destDir)) @mkdir($destDir, 0755, true);
    $filename = uniqid('rv_', true) . '.' . $ext;
    $dest     = rtrim($destDir, '/') . '/' . $filename;
    if (move_uploaded_file($file['tmp_name'], $dest)) return $filename;
    return '';
}

$uploadDir    = __DIR__ . '/uploads/reviews/';
$uploadRelDir = 'admin_panel/uploads/reviews/'; // relative to project root

// ── Avatar ────────────────────────────────────────────────────
$avatarRel = '';
if ($reviewId) {
    $old = $pdo->prepare("SELECT avatar FROM product_reviews WHERE id = :id");
    $old->execute([':id' => $reviewId]);
    $oldRow    = $old->fetch();
    $avatarRel = $oldRow['avatar'] ?? '';
}

if (!empty($_FILES['avatar']['tmp_name'])) {
    // Remove old avatar if exists
    if ($avatarRel && file_exists(__DIR__ . '/../' . $avatarRel)) @unlink(__DIR__ . '/../' . $avatarRel);
    $fn = uploadFile($_FILES['avatar'], $uploadDir);
    $avatarRel = $fn ? ($uploadRelDir . $fn) : '';
} elseif (!empty($_POST['remove_avatar']) && $avatarRel) {
    if (file_exists(__DIR__ . '/../' . $avatarRel)) @unlink(__DIR__ . '/../' . $avatarRel);
    $avatarRel = '';
}

// ── Review images ─────────────────────────────────────────────
$existingImgs = [];
if ($reviewId && empty($_POST['remove_images'])) {
    $raw = trim($_POST['existing_images'] ?? '');
    if ($raw) {
        foreach (explode(',', $raw) as $img) { $img = trim($img); if ($img) $existingImgs[] = $img; }
    }
} elseif ($reviewId && !empty($_POST['remove_images'])) {
    // Remove old photos from disk
    $old2 = $pdo->prepare("SELECT review_images FROM product_reviews WHERE id = :id");
    $old2->execute([':id' => $reviewId]);
    $oldRow2 = $old2->fetch();
    if ($oldRow2 && $oldRow2['review_images']) {
        foreach (explode(',', $oldRow2['review_images']) as $oi) {
            $oi = trim($oi);
            if ($oi && file_exists(__DIR__ . '/../' . $oi)) @unlink(__DIR__ . '/../' . $oi);
        }
    }
}

$newImgs = [];
if (!empty($_FILES['review_images']['tmp_name'][0])) {
    foreach ($_FILES['review_images']['tmp_name'] as $idx => $tmpName) {
        if (!$tmpName) continue;
        $fn = uploadFile([
            'name'     => $_FILES['review_images']['name'][$idx],
            'tmp_name' => $tmpName,
        ], $uploadDir);
        if ($fn) $newImgs[] = $uploadRelDir . $fn;
        if (count($newImgs) >= 4) break;
    }
}

$allImgs  = array_slice(array_merge($existingImgs, $newImgs), 0, 4);
$imagesDb = implode(',', $allImgs);

// ── DB save ───────────────────────────────────────────────────
try {
    if ($reviewId) {
        $pdo->prepare(
            "UPDATE product_reviews SET
               product_id    = :pid,
               reviewer_name = :name,
               review_text   = :text,
               stars         = :stars,
               status        = :status,
               sort_order    = :ord,
               avatar        = :avatar,
               review_images = :imgs
             WHERE id = :id"
        )->execute([
            ':pid'    => $productId,
            ':name'   => $reviewerName,
            ':text'   => $reviewText,
            ':stars'  => $stars,
            ':status' => $status,
            ':ord'    => $sortOrder,
            ':avatar' => $avatarRel,
            ':imgs'   => $imagesDb,
            ':id'     => $reviewId,
        ]);
        $msg = 'Review+updated+successfully';
    } else {
        $pdo->prepare(
            "INSERT INTO product_reviews
               (product_id, reviewer_name, review_text, stars, status, sort_order, avatar, review_images)
             VALUES (:pid, :name, :text, :stars, :status, :ord, :avatar, :imgs)"
        )->execute([
            ':pid'    => $productId,
            ':name'   => $reviewerName,
            ':text'   => $reviewText,
            ':stars'  => $stars,
            ':status' => $status,
            ':ord'    => $sortOrder,
            ':avatar' => $avatarRel,
            ':imgs'   => $imagesDb,
        ]);
        $msg = 'Review+added+successfully';
    }
} catch (PDOException $e) {
    header('Location: product_reviews.php?msg=' . urlencode('DB error: ' . $e->getMessage()) . '&type=danger&product_id=' . $redirectPid);
    exit;
}

header('Location: product_reviews.php?msg=' . $msg . '&type=success&product_id=' . $redirectPid);
exit;
