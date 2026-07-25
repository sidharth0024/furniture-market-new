<?php
/**
 * ajax_tab_products.php
 * AJAX endpoint: returns JSON with product card HTML for a given category_id.
 * Used by Featured Products tab section on homepage.
 */
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../admin_panel/includes/db_conn.php';
require_once __DIR__ . '/functions.php';

$categoryId = (int)($_GET['category_id'] ?? 0);
if ($categoryId <= 0) {
    echo json_encode(['error' => 'Invalid category_id']);
    exit;
}

$products = fetchCategoryProducts($pdo, $categoryId, 12);
$cards    = [];
foreach ($products as $p) {
    $cards[] = productCard($p);
}

echo json_encode(['html' => implode('', $cards)]);
