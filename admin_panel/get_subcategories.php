<?php
declare(strict_types=1);
require __DIR__ . '/includes/db_conn.php';

header('Content-Type: application/json');

$category_id = filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT);

if (!$category_id) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare(
    "SELECT id, subcategory_name AS name FROM subcategories WHERE category_id = :cid AND status = 'Active' ORDER BY subcategory_name ASC"
);
$stmt->execute([':cid' => $category_id]);

echo json_encode($stmt->fetchAll());
