<?php

if (!function_exists('productCard')) {
    function productCard(array $p): string
    {
        $swatches = '';
        if (!empty($p['colors']) && is_array($p['colors'])) {
            $swatches = '<div class="product-card-swatches">';
            foreach ($p['colors'] as $i => $c) {
                $active   = ($i === 0) ? ' active' : '';
                $swatches .= '<span class="swatch' . $active . '" style="background:'
                    . htmlspecialchars($c, ENT_QUOTES) . '" title="'
                    . htmlspecialchars($c, ENT_QUOTES) . '"></span>';
            }
            $swatches .= '</div>';
        }

        $badge     = !empty($p['discount'])
            ? '<span class="product-card-badge">Save ' . (int)$p['discount'] . '%</span>' : '';
        $oldPrice  = !empty($p['old_price'])
            ? '<span class="product-card-price-old">&#8377;' . number_format((float)$p['old_price']) . '</span>' : '';
        $discBadge = !empty($p['discount'])
            ? '<span class="product-card-discount">Save ' . (int)$p['discount'] . '%</span>' : '';
        $emiLine   = !empty($p['emi'])
            ? '<p class="product-card-emi">or &#8377;' . htmlspecialchars((string)$p['emi'], ENT_QUOTES) . '/Month</p>' : '';

        // Resolve image: absolute URL passes through; relative path served from project root
        $rawImg = $p['image'] ?? '';
        if (empty($rawImg)) {
            $imgSrc = 'assets/img/product-sofa.jpg';
        } elseif (str_starts_with($rawImg, 'http://') || str_starts_with($rawImg, 'https://')) {
            $imgSrc = $rawImg;
        } else {
            // Strip any leading slash
            $imgSrc = ltrim($rawImg, '/');
        }
        return '
    <div class="product-card-col">
      <div class="product-card">
        <div class="product-card-img-wrap">
          <img src="' . htmlspecialchars($imgSrc, ENT_QUOTES) . '"
               alt="' . htmlspecialchars($p['name'] ?? '', ENT_QUOTES) . '"
               loading="lazy"
               onerror="this.src=\'assets/img/product-sofa.jpg\'">
          ' . $badge . '
          <button class="product-card-wishlist" aria-label="Add to Wishlist" type="button"><i class="bi bi-heart"></i></button>
          ' . $swatches . '
        </div>
        <div class="product-card-body">
          <p class="product-card-category">' . htmlspecialchars($p['category'] ?? '', ENT_QUOTES) . '</p>
          <a href="product.php?id=' . htmlspecialchars($p['prod_id'] ?? '', ENT_QUOTES) . '"><h3 class="product-card-name">' . htmlspecialchars($p['name'] ?? '', ENT_QUOTES) . '</h3></a>
          <p class="product-card-gst">Inclusive of 18% GST, Delivery &amp; Installation</p>
          <div class="product-card-enquiry">
          <button class="btn btn-enquiry  whatsapp-enquiry" type="button"
          data-name="' . htmlspecialchars($p['name'], ENT_QUOTES) . '"
          data-category="' . htmlspecialchars($p['category'], ENT_QUOTES) . '"
          data-price="' . number_format((float)($p['price'] ?? 0)) . '"
          data-old-price="' . number_format((float)($p['old_price'] ?? 0)) . '"
          data-image="' . htmlspecialchars($imgSrc, ENT_QUOTES) . '">Enquire Now</button>
          </div>
          </div>
          </div>
          </div>';
    }
}
//   ' . $emiLine . '
//   <div class="product-card-price">
//     <span class="product-card-price-current">&#8377;' . number_format((float)($p['price'] ?? 0)) . '</span>
//     ' . $oldPrice . '
//     ' . $discBadge . '
//   </div>

if (!function_exists('fetchCategoryProducts')) {
    function fetchCategoryProducts(PDO $pdo, int $categoryId, int $limit = 12): array
    {
        $stmt = $pdo->prepare(
            "SELECT p.id as prod_id, p.product_name, p.regular_price, p.sale_price,
                    p.thumbnail, c.category_name, sc.subcategory_name
               FROM products p
               JOIN categories c   ON c.id = p.category_id
               LEFT JOIN subcategories sc ON sc.id = p.subcategory_id
              WHERE p.category_id = :cid AND p.product_status = 'Active'
              ORDER BY p.is_featured DESC, p.id DESC
              LIMIT :lim"
        );
        $stmt->bindValue(':cid', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit,      PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $products = [];
        foreach ($rows as $row) {
            $price    = (float)($row['sale_price'] ?: $row['regular_price']);
            $oldPrice = $row['sale_price'] ? (float)$row['regular_price'] : null;
            $discount = null;
            if ($oldPrice && $price < $oldPrice) {
                $discount = (int)round((($oldPrice - $price) / $oldPrice) * 100);
            }
            $emi = $price > 0 ? number_format($price / 12, 0, '.', '') : null;
            $catLabel = !empty($row['subcategory_name'])
                ? strtoupper($row['subcategory_name'])
                : strtoupper($row['category_name']);
            $products[] = [
                'prod_id'      => $row['prod_id'],
                'name'      => $row['product_name'],
                'category'  => $catLabel,
                'price'     => $price,
                'old_price' => $oldPrice,
                'discount'  => $discount,
                'emi'       => $emi,
                'image'     => $row['thumbnail'] ?? '',
            ];
        }
        return $products;
    }
}

if (!function_exists('imgSrc')) {
    function imgSrc(?string $path, string $fallback = ''): string
    {
        if (empty($path)) return $fallback;
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) return $path;
        return ltrim($path, '/');
    }
}
