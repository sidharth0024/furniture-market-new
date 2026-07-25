<?php
session_start();

include './includes/db_conn.php';

define('SUBCATEGORY_UPLOAD_DIR', __DIR__ . '/uploads/subcategory/');
define('SUBCATEGORY_UPLOAD_URL', 'uploads/subcategory/');
define('SUBCATEGORY_ALLOWED_EXT', ['jpg', 'jpeg', 'png', 'gif']);
define('SUBCATEGORY_MAX_SIZE', 2 * 1024 * 1024);

if (!is_dir(SUBCATEGORY_UPLOAD_DIR)) {
  mkdir(SUBCATEGORY_UPLOAD_DIR, 0755, true);
}

function generateSlug(string $text): string
{
  $slug = strtolower(trim($text));
  $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
  $slug = trim($slug, '-');

  return $slug;
}

function uploadSubcategoryImage(array $file): array
{
  if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
    return ['success' => true, 'filename' => null];
  }

  if ($file['error'] !== UPLOAD_ERR_OK) {
    return ['success' => false, 'message' => 'Image upload failed.'];
  }

  if ($file['size'] > SUBCATEGORY_MAX_SIZE) {
    return ['success' => false, 'message' => 'Image size must be less than 2MB.'];
  }

  $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

  if (!in_array($extension, SUBCATEGORY_ALLOWED_EXT, true)) {
    return ['success' => false, 'message' => 'Only JPG, JPEG, PNG and GIF images are allowed.'];
  }

  $newFileName = uniqid('subcat_', true) . '.' . $extension;
  $destination = SUBCATEGORY_UPLOAD_DIR . $newFileName;

  if (!move_uploaded_file($file['tmp_name'], $destination)) {
    return ['success' => false, 'message' => 'Failed to save the uploaded image.'];
  }

  return ['success' => true, 'filename' => $newFileName];
}

function deleteSubcategoryImage(?string $filename): void
{
  if (!empty($filename) && file_exists(SUBCATEGORY_UPLOAD_DIR . $filename)) {
    unlink(SUBCATEGORY_UPLOAD_DIR . $filename);
  }
}

$successMessage = '';
$errorMessage   = '';

if (isset($_POST['save_subcategory'])) {

  $category_id      = isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0;
  $subcategory_name = trim($_POST['subcategory_name'] ?? '');
  $description      = trim($_POST['description'] ?? '');
  $status           = trim($_POST['status'] ?? '');

  if ($category_id <= 0 || $subcategory_name === '' || $description === '' || $status === '') {

    $_SESSION['error_message'] = 'All fields are required.';
  } else {

    $slug = generateSlug($subcategory_name);

    $checkSql  = "SELECT id FROM subcategories
                      WHERE subcategory_name = :subcategory_name
                      AND category_id = :category_id";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([
      ':subcategory_name' => $subcategory_name,
      ':category_id'      => $category_id,
    ]);

    if ($checkStmt->fetch()) {

      $_SESSION['error_message'] = 'This sub category already exists under the selected category.';
    } else {

      $uploadResult = uploadSubcategoryImage($_FILES['subcategory_image'] ?? []);

      if (!$uploadResult['success']) {

        $_SESSION['error_message'] = $uploadResult['message'];
      } else {

        $insertSql = "INSERT INTO subcategories
                              (category_id, subcategory_name, slug, description, image, status, created_at, updated_at)
                              VALUES
                              (:category_id, :subcategory_name, :slug, :description, :image, :status, NOW(), NOW())";

        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->execute([
          ':category_id'      => $category_id,
          ':subcategory_name' => $subcategory_name,
          ':slug'             => $slug,
          ':description'      => $description,
          ':image'            => $uploadResult['filename'],
          ':status'           => $status,
        ]);

        $_SESSION['success_message'] = 'Sub Category added successfully!';
      }
    }
  }

  header('Location: all_sub_categories.php');
  exit;
}

if (isset($_POST['update_subcategory'])) {

  $id                = isset($_POST['id']) ? (int) $_POST['id'] : 0;
  $category_id       = isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0;
  $subcategory_name  = trim($_POST['subcategory_name'] ?? '');
  $description       = trim($_POST['description'] ?? '');
  $status            = trim($_POST['status'] ?? '');

  if ($id <= 0 || $category_id <= 0 || $subcategory_name === '' || $description === '' || $status === '') {

    $_SESSION['error_message'] = 'All fields are required.';
  } else {

    $slug = generateSlug($subcategory_name);

    $checkSql  = "SELECT id FROM subcategories
                      WHERE subcategory_name = :subcategory_name
                      AND category_id = :category_id
                      AND id != :id";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([
      ':subcategory_name' => $subcategory_name,
      ':category_id'      => $category_id,
      ':id'               => $id,
    ]);

    if ($checkStmt->fetch()) {

      $_SESSION['error_message'] = 'This sub category already exists under the selected category.';
    } else {

      $uploadResult = uploadSubcategoryImage($_FILES['subcategory_image'] ?? []);

      if (!$uploadResult['success']) {

        $_SESSION['error_message'] = $uploadResult['message'];
      } else {

        $existingStmt = $pdo->prepare("SELECT image FROM subcategories WHERE id = :id");
        $existingStmt->execute([':id' => $id]);
        $existingRow = $existingStmt->fetch();

        $imageToSave = $existingRow ? $existingRow['image'] : null;

        if ($uploadResult['filename'] !== null) {
          deleteSubcategoryImage($imageToSave);
          $imageToSave = $uploadResult['filename'];
        }

        $updateSql = "UPDATE subcategories
                              SET category_id = :category_id,
                                  subcategory_name = :subcategory_name,
                                  slug = :slug,
                                  description = :description,
                                  image = :image,
                                  status = :status,
                                  updated_at = NOW()
                              WHERE id = :id";

        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([
          ':category_id'      => $category_id,
          ':subcategory_name' => $subcategory_name,
          ':slug'             => $slug,
          ':description'      => $description,
          ':image'            => $imageToSave,
          ':status'           => $status,
          ':id'               => $id,
        ]);

        $_SESSION['success_message'] = 'Sub Category updated successfully!';
      }
    }
  }

  header('Location: all_sub_categories.php');
  exit;
}

if (isset($_POST['delete_subcategory'])) {

  $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

  if ($id > 0) {

    $existingStmt = $pdo->prepare("SELECT image FROM subcategories WHERE id = :id");
    $existingStmt->execute([':id' => $id]);
    $existingRow = $existingStmt->fetch();

    $deleteSql  = "DELETE FROM subcategories WHERE id = :id";
    $deleteStmt = $pdo->prepare($deleteSql);
    $deleteStmt->execute([':id' => $id]);

    if ($existingRow) {
      deleteSubcategoryImage($existingRow['image']);
    }

    $_SESSION['success_message'] = 'Sub Category deleted successfully!';
  } else {
    $_SESSION['error_message'] = 'Invalid sub category selected.';
  }

  header('Location: all_sub_categories.php');
  exit;
}

if (!empty($_SESSION['success_message'])) {
  $successMessage = $_SESSION['success_message'];
  unset($_SESSION['success_message']);
}

if (!empty($_SESSION['error_message'])) {
  $errorMessage = $_SESSION['error_message'];
  unset($_SESSION['error_message']);
}

$categoriesStmt = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC");
$categoriesList = $categoriesStmt->fetchAll();

$subCategoriesSql = "SELECT
        subcategories.id,
        subcategories.category_id,
        subcategories.subcategory_name,
        subcategories.slug,
        subcategories.description,
        subcategories.image,
        subcategories.status,
        subcategories.created_at,
        categories.category_name
    FROM subcategories
    INNER JOIN categories ON categories.id = subcategories.category_id
    ORDER BY subcategories.id DESC";

$subCategoriesStmt = $pdo->query($subCategoriesSql);
$subCategoriesList = $subCategoriesStmt->fetchAll();

include './layouts/header.php';

?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h2 class="fw-bold">All sub Categories</h2>
    <p class="text-muted">Manage Office Furniture Categories</p>
  </div>

</div>

<?php if ($successMessage !== ''): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($successMessage); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<?php if ($errorMessage !== ''): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($errorMessage); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<div class="d-flex justify-content-start mb-3">
  <button type="button"
    class="btn btn-primary"
    data-bs-toggle="modal"
    data-bs-target="#addCategoryModal">
    <i class="bi bi-plus-circle"></i> Add sub Category
  </button>
</div>

<div class="table-responsive">
  <table class="table table-bordered table-hover align-middle text-center">

    <thead class="table-dark">
      <tr>
        <th>Sr No</th>
        <th>Image</th>
        <th>Category</th>
        <th>Sub Category</th>
        <th>Description</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>

    <tbody>

      <?php if (count($subCategoriesList) > 0): ?>

        <?php $sr = 1; ?>
        <?php foreach ($subCategoriesList as $subCategory): ?>

          <tr>
            <td><?php echo $sr++; ?></td>
            <td>
              <?php if (!empty($subCategory['image'])): ?>
                <img src="<?php echo SUBCATEGORY_UPLOAD_URL . htmlspecialchars($subCategory['image']); ?>"
                  alt="<?php echo htmlspecialchars($subCategory['subcategory_name']); ?>"
                  style="width:50px;height:50px;object-fit:cover;border-radius:4px;">
              <?php else: ?>
                <span class="text-muted">No Image</span>
              <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($subCategory['category_name']); ?></td>
            <td><?php echo htmlspecialchars($subCategory['subcategory_name']); ?></td>
            <td><?php echo htmlspecialchars($subCategory['description']); ?></td>
            <td>
              <?php if ($subCategory['status'] === 'Active'): ?>
                <span class="badge bg-success">Active</span>
              <?php else: ?>
                <span class="badge bg-secondary">Inactive</span>
              <?php endif; ?>
            </td>
            <td class="text-center">
              <button type="button"
                class="btn btn-sm btn-outline-primary edit-subcategory-btn"
                data-bs-toggle="modal"
                data-bs-target="#editSubCategoryModal"
                data-id="<?php echo htmlspecialchars($subCategory['id']); ?>"
                data-category-id="<?php echo htmlspecialchars($subCategory['category_id']); ?>"
                data-subcategory-name="<?php echo htmlspecialchars($subCategory['subcategory_name']); ?>"
                data-description="<?php echo htmlspecialchars($subCategory['description']); ?>"
                data-status="<?php echo htmlspecialchars($subCategory['status']); ?>"
                data-image="<?php echo $subCategory['image'] ? SUBCATEGORY_UPLOAD_URL . htmlspecialchars($subCategory['image']) : ''; ?>"
                title="Edit Sub Category">
                <i class="bi bi-pencil-square"></i>
              </button>

              <button type="button"
                class="btn btn-sm btn-outline-danger delete-subcategory-btn"
                data-bs-toggle="modal"
                data-bs-target="#deleteSubCategoryModal"
                data-id="<?php echo htmlspecialchars($subCategory['id']); ?>"
                title="Delete Sub Category">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>

        <?php endforeach; ?>

      <?php else: ?>

        <tr>
          <td colspan="7">No sub categories found.</td>
        </tr>

      <?php endif; ?>

    </tbody>

  </table>
</div>


<div class="modal fade" id="addCategoryModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <form method="POST" action="all_sub_categories.php" enctype="multipart/form-data">

        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Add Sub Category</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <div class="mb-3">
            <label class="form-label">Category</label>
            <select class="form-select" name="category_id" required>
              <option value="" selected disabled>Select Category</option>
              <?php foreach ($categoriesList as $category): ?>
                <option value="<?php echo htmlspecialchars($category['id']); ?>">
                  <?php echo htmlspecialchars($category['category_name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Sub Category Name</label>
            <input type="text" class="form-control" name="subcategory_name" placeholder="Enter Sub Category Name" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="3" placeholder="Enter Description" required></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Status</label>
            <select class="form-select" name="status" required>
              <option value="" selected disabled>Select Status</option>
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Sub Category Image</label>
            <input type="file" class="form-control" name="subcategory_image" accept=".jpg,.jpeg,.png,.gif">
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="save_subcategory" class="btn btn-primary">Save Sub Category</button>
        </div>

      </form>

    </div>
  </div>
</div>

<div class="modal fade" id="editSubCategoryModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <form method="POST" action="all_sub_categories.php" id="editSubCategoryForm" enctype="multipart/form-data">

        <input type="hidden" name="id" id="edit_id">

        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title">Edit Sub Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <div class="mb-3">
            <label class="form-label">Category</label>
            <select class="form-select" name="category_id" id="edit_category_id" required>
              <?php foreach ($categoriesList as $category): ?>
                <option value="<?php echo htmlspecialchars($category['id']); ?>">
                  <?php echo htmlspecialchars($category['category_name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Sub Category Name</label>
            <input type="text" class="form-control" name="subcategory_name" id="edit_subcategory_name" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" id="edit_description" rows="3" required></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Status</label>
            <select class="form-select" name="status" id="edit_status" required>
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Current Image</label>
            <div>
              <img id="edit_current_image" src="" alt="" style="width:70px;height:70px;object-fit:cover;border-radius:4px;display:none;">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Replace Image</label>
            <input type="file" class="form-control" name="subcategory_image" accept=".jpg,.jpeg,.png,.gif">
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="update_subcategory" class="btn btn-warning">Update</button>
        </div>

      </form>

    </div>
  </div>
</div>

<div class="modal fade" id="deleteSubCategoryModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <form method="POST" action="all_sub_categories.php" id="deleteSubCategoryForm">

        <input type="hidden" name="id" id="delete_id">

        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title">Delete Sub Category</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body text-center">
          <i class="bi bi-trash-fill text-danger" style="font-size:60px;"></i>

          <h4 class="mt-3">Are you sure?</h4>

          <p>This sub category will be permanently deleted.</p>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="delete_subcategory" class="btn btn-danger">Delete</button>
        </div>

      </form>

    </div>
  </div>
</div>

<script src="./assets/js/bootstrap.bundle.min.js"></script>
<script src="./assets/js/main.js"></script>

<script>
  document.addEventListener('DOMContentLoaded', function() {

    var editModal = document.getElementById('editSubCategoryModal');
    editModal.addEventListener('show.bs.modal', function(event) {
      var triggerButton = event.relatedTarget;

      document.getElementById('edit_id').value = triggerButton.getAttribute('data-id');
      document.getElementById('edit_category_id').value = triggerButton.getAttribute('data-category-id');
      document.getElementById('edit_subcategory_name').value = triggerButton.getAttribute('data-subcategory-name');
      document.getElementById('edit_description').value = triggerButton.getAttribute('data-description');
      document.getElementById('edit_status').value = triggerButton.getAttribute('data-status');

      var imageUrl = triggerButton.getAttribute('data-image');
      var imageEl = document.getElementById('edit_current_image');

      if (imageUrl) {
        imageEl.src = imageUrl;
        imageEl.style.display = 'inline-block';
      } else {
        imageEl.src = '';
        imageEl.style.display = 'none';
      }
    });

    var deleteModal = document.getElementById('deleteSubCategoryModal');
    deleteModal.addEventListener('show.bs.modal', function(event) {
      var triggerButton = event.relatedTarget;

      document.getElementById('delete_id').value = triggerButton.getAttribute('data-id');
    });

  });
</script>

<?php include './layouts/footer.php'; ?>
</body>

</html>