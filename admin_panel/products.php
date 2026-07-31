<?php

declare(strict_types=1);
session_start();
if (isset($_SESSION['adminId'])) {


  require __DIR__ . '/includes/db_conn.php';
  require __DIR__ . '/config/helpers.php';

  // Handle Delete Request
  if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $product_id = $_GET['delete'];
    try {
      // Start transaction
      $pdo->beginTransaction();

      // Delete product (cascading will handle related tables)
      $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
      $stmt->execute([$product_id]);

      $pdo->commit();
      echo "<script>
      alert('Product deleted successfully!');window.location.href = 'products.php'</script>";
    } catch (PDOException $e) {
      $pdo->rollBack();
      $error_message = "Error deleting product: " . $e->getMessage();
    }
  }

  // Fetch all products with category and subcategory names
  $query = "SELECT p.*, 
      c.category_name as category_name, 
      s.subcategory_name as subcategory_name
      FROM products p
      LEFT JOIN categories c ON p.category_id = c.id
      LEFT JOIN subcategories s ON p.subcategory_id = s.id
      ORDER BY p.id DESC";

  $stmt = $pdo->prepare($query);
  $stmt->execute();
  $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Fetch single product details for modal (AJAX)
  if (isset($_GET['get_product']) && is_numeric($_GET['get_product'])) {
    $product_id = $_GET['get_product'];

    // Fetch product details
    $productQuery = "SELECT p.*, 
                 c.category_name as category_name, 
                 s.subcategory_name as subcategory_name
                 FROM products p
                 LEFT JOIN categories c ON p.category_id = c.id
                 LEFT JOIN subcategories s ON p.subcategory_id = s.id
                 WHERE p.id = ?";
    $stmt = $pdo->prepare($productQuery);
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch product variants
    $variantQuery = "SELECT * FROM product_variants WHERE product_id = ?";
    $stmt = $pdo->prepare($variantQuery);
    $stmt->execute([$product_id]);
    $variants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch product images
    $imageQuery = "SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order";
    $stmt = $pdo->prepare($imageQuery);
    $stmt->execute([$product_id]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch product specifications
    $specQuery = "SELECT * FROM product_specifications WHERE product_id = ?";
    $stmt = $pdo->prepare($specQuery);
    $stmt->execute([$product_id]);
    $specifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
      'product' => $product,
      'variants' => $variants,
      'images' => $images,
      'specifications' => $specifications
    ]);
    exit;
  }
  $tabs = [
    'basic' => [
      'label' => 'Basic Details',
      'short' => 'Edit BD',
      'href'  => 'edit_product_basic.php'
    ],
    'images' => [
      'label' => 'Product Images',
      'short' => 'Edit Images',
      'href'  => 'edit_product_images.php'
    ],
    'content' => [
      'label' => 'Content & Specs',
      'short' => 'Edit Content',
      'href'  => 'edit_product_content.php'
    ],
  ];

  include './layouts/header.php';
?>

  <main class="admin-content">
    <div class="container-fluid px-3 px-lg-2 py-3">

      <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
        <h4>Product Management</h4>
        <a href="./products_forms.php" class="btn btn-primary">
          <i class="bi bi-plus-lg me-1"></i>Add New Product
        </a>
      </div>

      <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="bi bi-check-circle me-2"></i><?php echo $success_message; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="bi bi-exclamation-circle me-2"></i><?php echo $error_message; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <div class="card shadow">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle w-100" id="example">
              <thead class="">
                <tr>
                  <th>ID</th>
                  <th>Product Name</th>
                  <th>SKU</th>
                  <th>Category</th>
                  <th>Subcategory</th>
                  <th>Price</th>
                  <th>Stock</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (count($products) > 0): ?>
                  <?php foreach ($products as $product): ?>
                    <tr>
                      <td><?php echo $product['id']; ?></td>
                      <td>
                        <?php echo htmlspecialchars($product['product_name']); ?>
                        <?php if ($product['is_featured']): ?>
                          <span class="badge bg-warning ms-1"><i class="bi bi-star-fill"></i></span>
                        <?php endif; ?>
                      </td>
                      <td><code><?php echo htmlspecialchars($product['sku'] ?: 'N/A'); ?></code></td>
                      <td><?php echo htmlspecialchars($product['category_name'] ?: 'N/A'); ?></td>
                      <td><?php echo htmlspecialchars($product['subcategory_name'] ?: 'N/A'); ?></td>
                      <td>
                        <?php if ($product['sale_price'] && (float)$product['sale_price'] < (float)$product['regular_price']): ?>
                          <small class="text-decoration-line-through text-muted">₹<?php echo number_format((float)$product['regular_price'], 2); ?></small><br>
                          <small class="text-danger fw-bold">₹<?php echo number_format((float)$product['sale_price'], 2); ?></small>
                        <?php else: ?>
                          <small class="fw-bold">₹<?php echo number_format((float)$product['regular_price'], 2); ?></small>
                        <?php endif; ?>
                      </td>
                      <td>
                        <span class="badge <?php echo $product['stock_quantity'] > 10 ? 'bg-success' : ($product['stock_quantity'] > 0 ? 'bg-warning' : 'bg-danger'); ?>">
                          <?php echo $product['stock_quantity']; ?>
                        </span>
                      </td>
                      <td>
                        <span class="badge <?php echo $product['product_status'] == 'Active' ? 'bg-success' : ($product['product_status'] == 'Draft' ? 'bg-secondary' : 'bg-danger'); ?> status-badge">
                          <?php echo $product['product_status']; ?>
                        </span>
                      </td>
                      <td class="d-flex">
                        <button class="btn btn-sm btn-primary action-btn view-product m-1"
                          data-product-id="<?php echo $product['id']; ?>"
                          title="View Details">
                          <i class="bi bi-eye"></i>
                        </button>
                        <?php foreach ($tabs as $tab): ?>
                          <a href="<?= e($tab['href']) ?>?id=<?= $product['id'] ?>"
                            class="btn btn-success btn-sm m-1"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="<?= e($tab['label']) ?>">
                            <?= e($tab['short']) ?>
                          </a>
                        <?php endforeach; ?>
                        <button class="btn btn-sm btn-danger action-btn delete-product"
                          data-product-id="<?php echo $product['id']; ?>"
                          data-product-name="<?php echo htmlspecialchars($product['product_name']); ?>"
                          title="Delete">
                          <i class="bi bi-trash"></i>
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="9" class="text-center py-4">
                      <i class="bi bi-box fa-3x text-muted mb-3 d-block"></i>
                      <p class="text-muted">No products found. <a href="add_product.php">Add your first product</a></p>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Product Details Modal -->
    <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="productModalLabel">
              <i class="fas fa-box me-2"></i>Product Details
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" id="productDetails">
            <!-- Content loaded via AJAX -->
            <div class="text-center py-5">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-3">Loading product details...</p>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-warning" id="editFromModal">
              <i class="bi bi-pencil me-1"></i>Edit Product
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Confirm Delete</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to delete the product <strong id="deleteProductName"></strong>?</p>
            <p class="text-danger"><small>This action cannot be undone and will remove all associated data.</small></p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <a href="#" id="confirmDeleteBtn" class="btn btn-danger">
              <i class="bi bi-trash me-1"></i>Delete Product
            </a>
          </div>
        </div>
      </div>
    </div>
  </main>
  <?php
  include "./layouts/footer.php";
  ?>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // View Product Details
      const viewButtons = document.querySelectorAll('.view-product');
      const productModal = new bootstrap.Modal(document.getElementById('productModal'));
      const productDetailsDiv = document.getElementById('productDetails');
      const editFromModalBtn = document.getElementById('editFromModal');
      let currentProductId = null;

      viewButtons.forEach(button => {
        button.addEventListener('click', function() {
          const productId = this.dataset.productId;
          currentProductId = productId;

          // Show loading state
          productDetailsDiv.innerHTML = `
                  <div class="text-center py-5">
                      <div class="spinner-border text-primary" role="status">
                          <span class="visually-hidden">Loading...</span>
                      </div>
                      <p class="mt-3">Loading product details...</p>
                  </div>
              `;

          // Fetch product details
          fetch(`products.php?get_product=${productId}`)
            .then(response => response.json())
            .then(data => {
              if (data.product) {
                displayProductDetails(data);
              } else {
                productDetailsDiv.innerHTML = '<div class="alert alert-danger">Error loading product details.</div>';
              }
            })
            .catch(error => {
              console.error('Error:', error);
              productDetailsDiv.innerHTML = '<div class="alert alert-danger">Error loading product details.</div>';
            });

          productModal.show();
        });
      });

      function displayProductDetails(data) {
        const p = data.product;
        const html = `
              <div class="row">
                  <div class="col-md-6">
                      <div class="row">
                          <div class="col-4"><span class="product-detail-label">Product Name:</span></div>
                          <div class="col-8"><span class="product-detail-value">${escapeHtml(p.product_name)}</span></div>
                      </div>
                      <div class="row">
                          <div class="col-4"><span class="product-detail-label">Slug:</span></div>
                          <div class="col-8"><span class="product-detail-value">${escapeHtml(p.slug)}</span></div>
                      </div>
                      <div class="row">
                          <div class="col-4"><span class="product-detail-label">SKU:</span></div>
                          <div class="col-8"><code>${escapeHtml(p.sku || 'N/A')}</code></div>
                      </div>
                      <div class="row">
                          <div class="col-4"><span class="product-detail-label">Category:</span></div>
                          <div class="col-8"><span class="product-detail-value">${escapeHtml(p.category_name || 'N/A')}</span></div>
                      </div>
                      <div class="row">
                          <div class="col-4"><span class="product-detail-label">Subcategory:</span></div>
                          <div class="col-8"><span class="product-detail-value">${escapeHtml(p.subcategory_name || 'N/A')}</span></div>
                      </div>
                      <div class="row">
                          <div class="col-4"><span class="product-detail-label">Regular Price:</span></div>
                          <div class="col-8"><span class="product-detail-value">₹${parseFloat(p.regular_price).toFixed(2)}</span></div>
                      </div>
                      <div class="row">
                          <div class="col-4"><span class="product-detail-label">Sale Price:</span></div>
                          <div class="col-8"><span class="product-detail-value">${p.sale_price ? '₹' + parseFloat(p.sale_price).toFixed(2) : 'N/A'}</span></div>
                      </div>
                      <div class="row">
                          <div class="col-4"><span class="product-detail-label">Cost Price:</span></div>
                          <div class="col-8"><span class="product-detail-value">${p.cost_price ? '₹' + parseFloat(p.cost_price).toFixed(2) : 'N/A'}</span></div>
                      </div>
                      <div class="row">
                          <div class="col-4"><span class="product-detail-label">GST:</span></div>
                          <div class="col-8"><span class="product-detail-value">${p.gst_percentage}%</span></div>
                      </div>
                      <div class="row">
                          <div class="col-4"><span class="product-detail-label">Stock:</span></div>
                          <div class="col-8"><span class="product-detail-value">${p.stock_quantity}</span></div>
                      </div>
                      <div class="row">
                          <div class="col-4"><span class="product-detail-label">Min Order Qty:</span></div>
                          <div class="col-8"><span class="product-detail-value">${p.minimum_order_qty}</span></div>
                      </div>
                      <div class="row">
                          <div class="col-4"><span class="product-detail-label">Status:</span></div>
                          <div class="col-8">
                              <span class="badge ${p.product_status == 'Active' ? 'bg-success' : (p.product_status == 'Draft' ? 'bg-secondary' : 'bg-danger')}">
                                  ${p.product_status}
                              </span>
                          </div>
                      </div>
                  </div>
                  <div class="col-md-6">
                      <div class="row">
                          <div class="col-4"><span class="product-detail-label">Featured:</span></div>
                          <div class="col-8">${p.is_featured ? '<span class="badge bg-warning"><i class="bi bi-star-fill"></i> Yes</span>' : '<span class="badge bg-light text-dark">No</span>'}</div>
                      </div>
                      <div class="row">
                          <div class="col-4"><span class="product-detail-label">New Arrival:</span></div>
                          <div class="col-8">${p.is_new_arrival ? '<span class="badge bg-info">Yes</span>' : '<span class="badge bg-light text-dark">No</span>'}</div>
                      </div>
                      <div class="row">
                          <div class="col-4"><span class="product-detail-label">Best Seller:</span></div>
                          <div class="col-8">${p.is_best_seller ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-light text-dark">No</span>'}</div>
                      </div>
                      <div class="row">
                          <div class="col-4"><span class="product-detail-label">Created:</span></div>
                          <div class="col-8"><span class="product-detail-value">${p.created_at}</span></div>
                      </div>
                      <div class="row">
                          <div class="col-4"><span class="product-detail-label">Updated:</span></div>
                          <div class="col-8"><span class="product-detail-value">${p.updated_at}</span></div>
                      </div>
                  </div>
              </div>
              
              <hr>
              
              <div class="row mt-3">
                  <div class="col-12">
                      <h6><i class="bi bi-text-left me-2"></i>Short Description</h6>
                      <p>${escapeHtml(p.short_description || 'N/A')}</p>
                  </div>
              </div>
              
              <div class="row">
                  <div class="col-12">
                      <h6><i class="bi bi-file-text me-2"></i>Description</h6>
                      <div>${p.description || 'N/A'}</div>
                  </div>
              </div>
              
              ${p.material_details ? `
              <div class="row">
                  <div class="col-12">
                      <h6><i class="bi bi-box2 me-2"></i>Material Details</h6>
                      <p>${escapeHtml(p.material_details)}</p>
                  </div>
              </div>
              ` : ''}
              
              ${p.care_instruction ? `
              <div class="row">
                  <div class="col-12">
                      <h6><i class="bi bi-hand-index me-2"></i>Care Instructions</h6>
                      <p>${escapeHtml(p.care_instruction)}</p>
                  </div>
              </div>
              ` : ''}
              
              ${p.warranty_details ? `
              <div class="row">
                  <div class="col-12">
                      <h6><i class="bi bi-shield-check me-2"></i>Warranty Details</h6>
                      <p>${escapeHtml(p.warranty_details)}</p>
                  </div>
              </div>
              ` : ''}
              
              ${p.package_contents ? `
              <div class="row">
                  <div class="col-12">
                      <h6><i class="bi bi-box me-2"></i>Package Contents</h6>
                      <p>${escapeHtml(p.package_contents)}</p>
                  </div>
              </div>
              ` : ''}
              
              ${p.shipping_details ? `
              <div class="row">
                  <div class="col-12">
                      <h6><i class="bi bi-truck me-2"></i>Shipping Details</h6>
                      <p>${escapeHtml(p.shipping_details)}</p>
                  </div>
              </div>
              ` : ''}
              
              ${p.return_policy ? `
              <div class="row">
                  <div class="col-12">
                      <h6><i class="bi bi-arrow-counterclockwise me-2"></i>Return Policy</h6>
                      <p>${escapeHtml(p.return_policy)}</p>
                  </div>
              </div>
              ` : ''}
              
              ${p.faq ? `
              <div class="row">
                  <div class="col-12">
                      <h6><i class="bi bi-question-circle me-2"></i>FAQ</h6>
                      <p>${escapeHtml(p.faq)}</p>
                  </div>
              </div>
              ` : ''}
              
              <hr>
              
              <div class="row">
                  <div class="col-md-6">
                      <h6><i class="bi bi-tags me-2"></i>Specifications (${data.specifications ? data.specifications.length : 0})</h6>
                      ${data.specifications && data.specifications.length > 0 ? 
                          data.specifications.map(spec => 
                              `<div class="spec-item"><strong>${escapeHtml(spec.specification_name)}</strong>: ${escapeHtml(spec.specification_value)}</div>`
                          ).join('') : 
                          '<p class="text-muted">No specifications</p>'
                      }
                  </div>
                  <div class="col-md-6">
                      <h6><i class="bi bi-diagram-3 me-2"></i>Variants (${data.variants ? data.variants.length : 0})</h6>
                      ${data.variants && data.variants.length > 0 ? 
                          data.variants.map(variant => 
                              `<div class="variant-item">
                                  <strong>${escapeHtml(variant.variant_name)}</strong>: ${escapeHtml(variant.variant_value)} 
                                  (SKU: ${escapeHtml(variant.sku || 'N/A')}) - 
                                  ₹${parseFloat(variant.price).toFixed(2)} | Stock: ${variant.stock}
                                  ${variant.image ? `<img src="${escapeHtml(variant.image)}" class="image-thumbnail ms-2" alt="Variant image">` : ''}
                              </div>`
                          ).join('') : 
                          '<p class="text-muted">No variants</p>'
                      }
                  </div>
              </div>
              
              <hr>
              
              <div class="row">
                  <div class="col-12">
                      <h6><i class="bi bi-images me-2"></i>Product Images (${data.images ? data.images.length : 0})</h6>
                      ${data.images && data.images.length > 0 ? 
                          `<div class="d-flex flex-wrap">
                              ${data.images.map(img => 
                                  `<div class="me-2 mb-2 text-center">
                                      <img src="${escapeHtml(img.image)}" class="image-thumbnail" alt="${escapeHtml(img.alt_text || 'Product image')}" style="max-width:100px; max-height:100px;">
                                      ${img.is_thumbnail ? '<div><span class="badge bg-primary">Thumbnail</span></div>' : ''}
                                      <div><small class="text-muted">Order: ${img.sort_order}</small></div>
                                  </div>`
                              ).join('')}
                          </div>` : 
                          '<p class="text-muted">No images</p>'
                      }
                  </div>
              </div>
              
              <hr>
              
              <div class="row">
                  <div class="col-12">
                      <h6><i class="bi bi-search me-2"></i>SEO Information</h6>
                      <div class="row">
                          <div class="col-4"><span class="product-detail-label">SEO Title:</span></div>
                          <div class="col-8">${escapeHtml(p.seo_title || 'N/A')}</div>
                      </div>
                      <div class="row">
                          <div class="col-4"><span class="product-detail-label">SEO Keywords:</span></div>
                          <div class="col-8">${escapeHtml(p.seo_keywords || 'N/A')}</div>
                      </div>
                      <div class="row">
                          <div class="col-4"><span class="product-detail-label">SEO Description:</span></div>
                          <div class="col-8">${escapeHtml(p.seo_description || 'N/A')}</div>
                      </div>
                  </div>
              </div>
          `;

        productDetailsDiv.innerHTML = html;

        // Set edit button link
        if (currentProductId) {
          editFromModalBtn.href = `edit_product.php?id=${currentProductId}`;
        }
      }

      function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
      }

      // Delete Product
      const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
      const deleteButtons = document.querySelectorAll('.delete-product');
      const deleteProductName = document.getElementById('deleteProductName');
      const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

      deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
          const productId = this.dataset.productId;
          const productName = this.dataset.productName;
          deleteProductName.textContent = productName;
          confirmDeleteBtn.href = `products.php?delete=${productId}`;
          deleteModal.show();
        });
      });
    });
  </script>
<?php
} else {
  header("Location:index.php");
}
?>