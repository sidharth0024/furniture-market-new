<?php
require_once 'layouts/header.php';

?>
<title></title>
<meta name="description" content="">
<meta name="keywords" content="">
<link rel="stylesheet" href="css/product.css">
<?php require_once 'layouts/navbar.php'; ?>

<?php


$PRODUCT_DETAIL_URL = 'product.php';

// How many products per page
$PER_PAGE = 12;

// =====================================================================
// 2. CONNECT TO DATABASE (fatal only if we truly cannot connect)
// =====================================================================
$fatalError = '';

/**
 * Runs a query safely. On ANY failure (missing table, bad column,
 * connection hiccup, etc.) it just returns an empty array instead of
 * throwing, so the page keeps rendering.
 */
function safeQuery($pdo, $sql, $params = [])
{
    if (!$pdo) return [];
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

/** Small helper to escape output safely */
function h($value)
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/** Turns a slug into a readable fallback title, e.g. executive-tables -> Executive Tables */
function slugToTitle($slug)
{
    return ucwords(str_replace(['-', '_'], ' ', $slug));
}

// =====================================================================
// 3. READ FILTERS FROM QUERY STRING
// =====================================================================
$categorySlug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$subSlug      = isset($_GET['sub'])  ? trim($_GET['sub'])  : '';
$page         = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$sort         = isset($_GET['sort']) ? trim($_GET['sort']) : '';


$category    = null;
$subcategory = null;
$products    = [];
$totalProducts = 0;
$totalPages  = 1;

if (!$fatalError) {

    // -------------------------------------------------------------
    // 3a. Resolve category by slug
    // -------------------------------------------------------------
    if ($categorySlug !== '') {
        $rows = safeQuery(
            $pdo,
            "SELECT id, category_name, slug, description, image, status, created_at, updated_at
             FROM categories WHERE slug = ? LIMIT 1",
            [$categorySlug]
        );
        if (!empty($rows)) {
            $category = $rows[0];
        }
    }


    // -------------------------------------------------------------
    // 3b. Resolve subcategory by slug (scoped to category if found)
    // -------------------------------------------------------------
    if ($subSlug !== '') {
        $sql = "SELECT id, category_id, subcategory_name, slug, description, image, status, created_at, updated_at
                FROM subcategories WHERE ";
        $params = [];
        if ($category) {
            $sql .= " category_id = ?";
            $params[] = $category['id'];
        }
        $sql .= " LIMIT 1";
        $rows = safeQuery($pdo, $sql, $params);
        if (!empty($rows)) {
            $subcategory = $rows[0];
        }
    }

    // -------------------------------------------------------------
    // 3c. Build the main products query
    // -------------------------------------------------------------
    $where  = ['1=1'];
    $params = [];
    if ($category) {
        $where[]  = 'category_id = ?';
        $params[] = $category['id'];
    } elseif ($categorySlug !== '') {
        $where[] = '1=0';
    }

    if ($subcategory) {
        $where[]  = 'subcategory_id = ?';
        $params[] = $subcategory['id'];
    } elseif ($subSlug !== '') {
        $where[] = '1=0';
    }

    $orderBy = 'id DESC';
    if ($sort === 'price_low')  $orderBy = "COALESCE(NULLIF(sale_price,0), regular_price) ASC";
    if ($sort === 'price_high') $orderBy = "COALESCE(NULLIF(sale_price,0), regular_price) DESC";
    if ($sort === 'newest')     $orderBy = 'created_at DESC';
    if ($sort === 'name_asc')   $orderBy = 'product_name ASC';

    $sql = "SELECT id, category_id, subcategory_id, product_name, slug, sku, short_description,
                   description, specifications, regular_price, sale_price, gst_percentage,
                   stock_quantity, thumbnail, product_status, is_featured, created_at, updated_at,
                   width, height, depth, seat_height, dimension_image, features,
                   seo_title, seo_description, seo_keywords, care_instruction,
                   shipping_details, warranty_details FROM products WHERE " . implode(' AND ', $where) . " ORDER BY {$orderBy}";
    $allProducts   = safeQuery($pdo, $sql, $params);
    $totalProducts = count($allProducts);
    $totalPages    = max(1, (int)ceil($totalProducts / $PER_PAGE));
    $page          = min($page, $totalPages);
    $offset        = ($page - 1) * $PER_PAGE;
    $products      = array_slice($allProducts, $offset, $PER_PAGE);
}

// =====================================================================
// 4. ENRICH THE CURRENT PAGE OF PRODUCTS WITH RELATED DATA
//    (images, specifications, reviews, variants) - all optional
// =====================================================================
$imagesByProduct  = [];
$specsByProduct   = [];
$reviewsByProduct = [];
$variantsByProduct = [];

if (!$fatalError && !empty($products)) {
    $productIds   = array_column($products, 'id');
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));

    $imgRows = safeQuery(
        $pdo,
        "SELECT id, product_id, image, alt_text, sort_order, is_thumbnail
         FROM product_images WHERE product_id IN ($placeholders) ORDER BY sort_order ASC",
        $productIds
    );
    foreach ($imgRows as $row) {
        $imagesByProduct[$row['product_id']][] = $row;
    }

    $specRows = safeQuery(
        $pdo,
        "SELECT id, product_id, specification_name, specification_value
         FROM product_specifications WHERE product_id IN ($placeholders)",
        $productIds
    );
    foreach ($specRows as $row) {
        $specsByProduct[$row['product_id']][] = $row;
    }

    $reviewRows = safeQuery(
        $pdo,
        "SELECT id, product_id, reviewer_name, review_text, stars, review_images, avatar,
                status, sort_order, created_at
         FROM product_reviews WHERE product_id IN ($placeholders)",
        $productIds
    );
    foreach ($reviewRows as $row) {
        $reviewsByProduct[$row['product_id']][] = $row;
    }

    $variantRows = safeQuery(
        $pdo,
        "SELECT id, product_id, variant_name, variant_value, sku, price, stock, image, status, created_at
         FROM product_variants WHERE product_id IN ($placeholders)",
        $productIds
    );
    foreach ($variantRows as $row) {
        $variantsByProduct[$row['product_id']][] = $row;
    }
}

/** Average star rating for a product, or null if no reviews */
function avgRating($reviews)
{
    if (empty($reviews)) return null;
    $sum = 0;
    $count = 0;
    foreach ($reviews as $r) {
        if (isset($r['stars']) && is_numeric($r['stars'])) {
            $sum += (float)$r['stars'];
            $count++;
        }
    }
    return $count > 0 ? round($sum / $count, 1) : null;
}

/** Picks a thumbnail: product.thumbnail -> first product_images row -> placeholder */
function resolveThumbnail($product, $images)
{
    if (!empty($product['thumbnail'])) {
        return $product['thumbnail'];
    }
    if (!empty($images)) {
        foreach ($images as $img) {
            if (!empty($img['is_thumbnail'])) {
                return $img['image'];
            }
        }
        return $images[0]['image'];
    }
    return null; // caller will show a placeholder box
}

// Page heading / SEO text (graceful fallbacks if lookups failed)
$pageTitle = 'All Products';
if ($subcategory) {
    $pageTitle = $subcategory['subcategory_name'];
} elseif ($subSlug !== '') {
    $pageTitle = slugToTitle($subSlug);
} elseif ($category) {
    $pageTitle = $category['category_name'];
} elseif ($categorySlug !== '') {
    $pageTitle = slugToTitle($categorySlug);
}

$breadcrumbCategory    = $category ? $category['category_name'] : ($categorySlug !== '' ? slugToTitle($categorySlug) : '');
$breadcrumbSubcategory = $subcategory ? $subcategory['subcategory_name'] : ($subSlug !== '' ? slugToTitle($subSlug) : '');

function buildQuery($overrides = [])
{
    $params = array_merge($_GET, $overrides);
    return '?' . http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?> | Products</title>
    <meta name="description" content="<?= h($pageTitle) ?> - browse our full range.">
    <style>
        :root {
            --clr-bg: #f7f7f8;
            --clr-card: #ffffff;
            --clr-border: #e6e6e9;
            --clr-text: #1f2328;
            --clr-muted: #6b7280;
            --clr-accent: #b8862f;
            --clr-accent-dark: #93691f;
            --clr-danger: #c0392b;
            --radius: 10px;
            --shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: var(--clr-bg);
            color: var(--clr-text);
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        .container {
            max-width: 1240px;
            margin: 0 auto;
            padding: 0 20px;
        }

        header.pl-header {
            background: var(--clr-card);
            border-bottom: 1px solid var(--clr-border);
            padding: 22px 0;
        }

        .breadcrumb {
            font-size: 13px;
            color: var(--clr-muted);
            margin-bottom: 6px;
        }

        .breadcrumb a {
            color: var(--clr-muted);
        }

        .breadcrumb a:hover {
            color: var(--clr-accent);
        }

        .breadcrumb .sep {
            margin: 0 6px;
        }

        .pl-title {
            font-size: 26px;
            font-weight: 700;
            margin: 0;
        }

        .pl-count {
            font-size: 14px;
            color: var(--clr-muted);
            margin-top: 4px;
        }

        .toolbar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            margin: 18px 0;
        }

        .toolbar select {
            padding: 8px 12px;
            border: 1px solid var(--clr-border);
            border-radius: 8px;
            background: #fff;
            font-size: 14px;
            color: var(--clr-text);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 22px;
            margin: 10px 0 40px;
        }

        .card {
            background: var(--clr-card);
            border: 1px solid var(--clr-border);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
            transition: transform .15s ease, box-shadow .15s ease;
            position: relative;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.09);
        }

        .card .thumb-wrap {
            aspect-ratio: 1/1;
            background: #f0f0f1;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .card .thumb-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .thumb-placeholder {
            color: var(--clr-muted);
            font-size: 13px;
        }

        .badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: var(--clr-accent);
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 9px;
            border-radius: 20px;
            letter-spacing: .3px;
        }

        .badge.out {
            background: var(--clr-danger);
        }

        .card-body {
            padding: 14px 16px 16px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .card-body .cat-name {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--clr-muted);
            margin-bottom: 4px;
        }

        .card-body h3 {
            font-size: 15.5px;
            margin: 0 0 6px;
            line-height: 1.35;
            font-weight: 600;
        }

        .card-body p.short-desc {
            font-size: 13px;
            color: var(--clr-muted);
            margin: 0 0 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .rating {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12.5px;
            color: var(--clr-muted);
            margin-bottom: 8px;
        }

        .rating .stars {
            color: #f0a500;
            letter-spacing: 1px;
        }

        .price-row {
            margin-top: auto;
            display: flex;
            align-items: baseline;
            gap: 8px;
        }

        .price-sale {
            font-size: 17px;
            font-weight: 700;
            color: var(--clr-accent-dark);
        }

        .price-regular {
            font-size: 13px;
            color: var(--clr-muted);
            text-decoration: line-through;
        }

        .price-only {
            font-size: 17px;
            font-weight: 700;
            color: var(--clr-text);
        }

        .variant-note {
            font-size: 11.5px;
            color: var(--clr-muted);
            margin-top: 4px;
        }

        .view-btn {
            display: block;
            text-align: center;
            margin-top: 12px;
            padding: 9px 10px;
            border-radius: 8px;
            background: var(--clr-text);
            color: #fff;
            font-size: 13.5px;
            font-weight: 600;
        }

        .view-btn:hover {
            background: var(--clr-accent-dark);
        }

        .empty-state {
            text-align: center;
            padding: 70px 20px;
            color: var(--clr-muted);
        }

        .empty-state h2 {
            color: var(--clr-text);
            margin-bottom: 8px;
        }

        .fatal {
            max-width: 600px;
            margin: 80px auto;
            text-align: center;
            background: #fff;
            border: 1px solid var(--clr-border);
            border-radius: var(--radius);
            padding: 40px 30px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 6px;
            margin: 30px 0 60px;
            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span {
            padding: 8px 13px;
            border: 1px solid var(--clr-border);
            border-radius: 8px;
            font-size: 13.5px;
            background: #fff;
        }

        .pagination a:hover {
            border-color: var(--clr-accent);
            color: var(--clr-accent-dark);
        }

        .pagination .current {
            background: var(--clr-text);
            color: #fff;
            border-color: var(--clr-text);
        }

        @media(max-width:480px) {
            .toolbar {
                justify-content: flex-start;
            }
        }
    </style>
</head>

<body>

    <?php if ($fatalError): ?>

        <div class="fatal">
            <h2>Something went wrong</h2>
            <p><?= h($fatalError) ?></p>
        </div>

    <?php else: ?>

        <header class="pl-header">
            <div class="container">
                <div class="breadcrumb">
                    <a href="/">Home</a>
                    <?php if ($breadcrumbCategory): ?>
                        <span class="sep">/</span>
                        <a href="<?= h(buildQuery(['sub' => null])) ?>"><?= h($breadcrumbCategory) ?></a>
                    <?php endif; ?>
                    <?php if ($breadcrumbSubcategory): ?>
                        <span class="sep">/</span>
                        <span><?= h($breadcrumbSubcategory) ?></span>
                    <?php endif; ?>
                </div>
                <h1 class="pl-title"><?= h($pageTitle) ?></h1>
                <div class="pl-count">
                    <?= (int)$totalProducts ?> product<?= $totalProducts === 1 ? '' : 's' ?> found
                </div>
            </div>
        </header>

        <div class="container">

            <?php if (!empty($products)): ?>
                <div class="toolbar">
                    <form method="get" id="sortForm">
                        <input type="hidden" name="slug" value="<?= h($categorySlug) ?>">
                        <input type="hidden" name="sub" value="<?= h($subSlug) ?>">
                        <select name="sort" onchange="document.getElementById('sortForm').submit()">
                            <option value="" <?= $sort === '' ? 'selected' : '' ?>>Featured</option>
                            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
                            <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                            <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Name: A-Z</option>
                        </select>
                    </form>
                </div>
            <?php endif; ?>

            <?php if (empty($products)): ?>

                <div class="empty-state">
                    <h2>No products found</h2>
                    <p>Try browsing a different category or check back later.</p>
                </div>

            <?php else: ?>

                <div class="grid">
                    <?php foreach ($products as $product):
                        $pid      = $product['id'];
                        $images   = $imagesByProduct[$pid]  ?? [];
                        $reviews  = $reviewsByProduct[$pid] ?? [];
                        $variants = $variantsByProduct[$pid] ?? [];
                        $thumb    = resolveThumbnail($product, $images);
                        $rating   = avgRating($reviews);

                        $regular = is_numeric($product['regular_price']) ? (float)$product['regular_price'] : null;
                        $sale    = is_numeric($product['sale_price']) && (float)$product['sale_price'] > 0 ? (float)$product['sale_price'] : null;
                        $hasSale = $sale !== null && $regular !== null && $sale < $regular;

                        $outOfStock = isset($product['stock_quantity']) && (int)$product['stock_quantity'] <= 0;
                        $isFeatured = !empty($product['is_featured']) && !$outOfStock;

                        $detailLink = $PRODUCT_DETAIL_URL . '?slug=' . urlencode($product['slug'] ?? '');
                    ?>
                        <div class="card">
                            <?php if ($outOfStock): ?>
                                <span class="badge out">Out of stock</span>
                            <?php elseif ($isFeatured): ?>
                                <span class="badge">Featured</span>
                            <?php endif; ?>

                            <a href="<?= h($detailLink) ?>" class="thumb-wrap">
                                <?php if ($thumb): ?>
                                    <img src="<?= h($thumb) ?>" alt="<?= h($product['product_name']) ?>" loading="lazy">
                                <?php else: ?>
                                    <span class="thumb-placeholder">No image available</span>
                                <?php endif; ?>
                            </a>

                            <div class="card-body">
                                <?php if ($breadcrumbSubcategory || $breadcrumbCategory): ?>
                                    <div class="cat-name"><?= h($breadcrumbSubcategory ?: $breadcrumbCategory) ?></div>
                                <?php endif; ?>

                                <h3><a href="<?= h($detailLink) ?>"><?= h($product['product_name']) ?></a></h3>

                                <?php if (!empty($product['short_description'])): ?>
                                    <p class="short-desc"><?= h($product['short_description']) ?></p>
                                <?php endif; ?>

                                <?php if ($rating !== null): ?>
                                    <div class="rating">
                                        <span class="stars"><?= str_repeat('★', (int)round($rating)) . str_repeat('☆', 5 - (int)round($rating)) ?></span>
                                        <span><?= h($rating) ?> (<?= count($reviews) ?>)</span>
                                    </div>
                                <?php endif; ?>

                                <div class="price-row">
                                    <?php if ($hasSale): ?>
                                        <span class="price-sale">₹<?= number_format($sale, 2) ?></span>
                                        <span class="price-regular">₹<?= number_format($regular, 2) ?></span>
                                    <?php elseif ($regular !== null): ?>
                                        <span class="price-only">₹<?= number_format($regular, 2) ?></span>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($variants)): ?>
                                    <div class="variant-note"><?= count($variants) ?> option<?= count($variants) === 1 ? '' : 's' ?> available</div>
                                <?php endif; ?>

                                <a href="<?= h($detailLink) ?>" class="view-btn"><?= $outOfStock ? 'View Details' : 'View Product' ?></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="<?= h(buildQuery(['page' => $page - 1])) ?>">&laquo; Prev</a>
                        <?php endif; ?>

                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <?php if ($p == $page): ?>
                                <span class="current"><?= $p ?></span>
                            <?php else: ?>
                                <a href="<?= h(buildQuery(['page' => $p])) ?>"><?= $p ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="<?= h(buildQuery(['page' => $page + 1])) ?>">Next &raquo;</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php endif; ?>

        </div>

    <?php endif; ?>

</body>

</html>