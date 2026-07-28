<?php

/**
 * fetch-categories.php
 * Fetches $navCategories (categories + subcategories) and $utilityBar data.
 * Required by: layouts/navbar.php
 * Requires: $pdo from Admin_panel/includes/db_conn.php
 */

// ── Utility Bar: pull primary mobile from contact_details ─────────────────
$_cdStmt = $pdo->query(
    "SELECT type, value FROM contact_details WHERE status = 1 ORDER BY sort_order ASC"
);
$_cdRows = $_cdStmt->fetchAll();
$_emailValue = "";
$_phoneValue = '';
foreach ($_cdRows as $_row) {
    if ($_row['type'] === 'mobile' && $_phoneValue === '') {
        $_phoneValue = $_row['value'];
    }
    if ($_row['type'] === '_emailValue' && $_emailValue === '') {
        $_emailValue = $_row['value'];
    }
}
$utilityBar = [
    'phone_display'   => $_phoneValue ?: '1800-123-4567',
    'email_display'   => $_emailValue ?: 'info@furnitureshoper.com',
    'contact_us_url'  => 'contactus.php',
    'track_order_url' => '#track',
    'bulk_orders_url' => 'contactus.php',
];

// ── splitIntoColumns helper ────────────────────────────────────────────────
if (!function_exists('splitIntoColumns')) {
    function splitIntoColumns(array $items, int $cols): array
    {
        if (empty($items) || $cols <= 0) return [];
        $chunkSize = (int) ceil(count($items) / $cols);
        if ($chunkSize < 1) $chunkSize = 1;
        return array_chunk($items, $chunkSize);
    }
}

// ── Fetch active categories with subcategories ────────────────────────────
$_catStmt = $pdo->query(
    "SELECT id, category_name, slug, description, image
       FROM categories
      WHERE status = 'Active'
      ORDER BY id ASC"
);
$_categories = $_catStmt->fetchAll();

$navCategories = [];
foreach ($_categories as $_cat) {
    $_subStmt = $pdo->prepare(
        "SELECT id, subcategory_name AS name, description, image,
                LOWER(REPLACE(TRIM(subcategory_name),' ','-')) AS slug
           FROM subcategories
          WHERE category_id = :cid AND status = 'Active'
          ORDER BY id ASC"
    );
    $_subStmt->execute([':cid' => $_cat['id']]);
    $_cat['subcategories'] = $_subStmt->fetchAll();
    $navCategories[] = $_cat;
}
