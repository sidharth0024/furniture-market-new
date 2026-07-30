<?php
session_start();

include './includes/db_conn.php';

define('UPLOAD_DIR', __DIR__ . '/uploads/category/');
define('UPLOAD_URL', 'uploads/category/');
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);
define('MAX_FILE_SIZE', 2 * 1024 * 1024);

if (!is_dir(UPLOAD_DIR)) {
  mkdir(UPLOAD_DIR, 0755, true);
}

function generateUniqueSlug(PDO $pdo, string $categoryName, ?int $ignoreId = null): string
{
  $baseSlug = strtolower(trim($categoryName));
  $baseSlug = preg_replace('/[^a-z0-9]+/', '-', $baseSlug);
  $baseSlug = trim($baseSlug, '-');

  if ($baseSlug === '') {
    $baseSlug = 'category';
  }

  $slug = $baseSlug;
  $suffix = 1;

  while (true) {
    if ($ignoreId !== null) {
      $stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = :slug AND id != :id LIMIT 1");
      $stmt->execute([':slug' => $slug, ':id' => $ignoreId]);
    } else {
      $stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = :slug LIMIT 1");
      $stmt->execute([':slug' => $slug]);
    }

    if ($stmt->fetch() === false) {
      break;
    }

    $suffix++;
    $slug = $baseSlug . '-' . $suffix;
  }

  return $slug;
}

function handleImageUpload(array $file): ?string
{
  if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
    return null;
  }

  if ($file['error'] !== UPLOAD_ERR_OK) {
    throw new Exception('Image upload failed. Please try again.');
  }

  $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

  if (!in_array($extension, ALLOWED_EXTENSIONS, true)) {
    throw new Exception('Invalid image format. Allowed formats: JPG, JPEG, PNG, WEBP.');
  }

  if ($file['size'] > MAX_FILE_SIZE) {
    throw new Exception('Image size must not exceed 2MB.');
  }

  if (@getimagesize($file['tmp_name']) === false) {
    throw new Exception('The uploaded file is not a valid image.');
  }

  $newFileName = 'cat_' . time() . '_' . uniqid() . '.' . $extension;
  $destination = UPLOAD_DIR . $newFileName;

  if (!move_uploaded_file($file['tmp_name'], $destination)) {
    throw new Exception('Could not save the uploaded image.');
  }

  return $newFileName;
}

function deleteImageFile(?string $fileName): void
{
  if (!empty($fileName) && file_exists(UPLOAD_DIR . $fileName)) {
    unlink(UPLOAD_DIR . $fileName);
  }
}

function redirectWithMessage(string $type, string $message): void
{
  $_SESSION[$type] = $message;
  header('Location: Categories.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  if (isset($_POST['add_category'])) {
    try {
      $categoryName = trim($_POST['category_name'] ?? '');
      $description  = trim($_POST['description'] ?? '');
      $status       = ($_POST['status'] ?? 'Active') === 'Inactive' ? 'Inactive' : 'Active';

      if ($categoryName === '') {
        throw new Exception('Category name is required.');
      }

      $dupStmt = $pdo->prepare("SELECT id FROM categories WHERE category_name = :name LIMIT 1");
      $dupStmt->execute([':name' => $categoryName]);
      if ($dupStmt->fetch() !== false) {
        throw new Exception('This category name already exists.');
      }

      $slug = generateUniqueSlug($pdo, $categoryName);

      $imageName = null;
      if (!empty($_FILES['image']['name'])) {
        $imageName = handleImageUpload($_FILES['image']);
      }

      $insertStmt = $pdo->prepare(
        "INSERT INTO categories (category_name, slug, description, image, status, created_at, updated_at)
                     VALUES (:category_name, :slug, :description, :image, :status, NOW(), NOW())"
      );
      $insertStmt->execute([
        ':category_name' => $categoryName,
        ':slug'          => $slug,
        ':description'   => $description !== '' ? $description : null,
        ':image'         => $imageName,
        ':status'        => $status,
      ]);

      redirectWithMessage('success', 'Category added successfully.');
    } catch (Exception $e) {
      redirectWithMessage('error', $e->getMessage());
    }
  }

  if (isset($_POST['edit_category'])) {
    try {

      $categoryId   = (int)($_POST['category_id'] ?? 0);
      $categoryName = trim($_POST['category_name'] ?? '');
      $description  = trim($_POST['description'] ?? '');
      $status       = ($_POST['status'] ?? 'Active') === 'Inactive' ? 'Inactive' : 'Active';

      if ($categoryId <= 0) {
        throw new Exception('Invalid category selected.');
      }

      if ($categoryName === '') {
        throw new Exception('Category name is required.');
      }

      $existingStmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id LIMIT 1");
      $existingStmt->execute([':id' => $categoryId]);
      $existingCategory = $existingStmt->fetch(PDO::FETCH_ASSOC);

      if (!$existingCategory) {
        throw new Exception('Category not found.');
      }

      $dupStmt = $pdo->prepare("SELECT id FROM categories WHERE category_name = :name AND id != :id LIMIT 1");
      $dupStmt->execute([
        ':name' => $categoryName,
        ':id'   => $categoryId
      ]);

      if ($dupStmt->fetch()) {
        throw new Exception('This category name already exists.');
      }

      if (strcasecmp($categoryName, $existingCategory['category_name']) !== 0) {
        $slug = generateUniqueSlug($pdo, $categoryName, $categoryId);
      } else {
        $slug = $existingCategory['slug'];
      }

      $imageName = $existingCategory['image'];

      if (!empty($_FILES['image']['name'])) {

        $newImage = handleImageUpload($_FILES['image']);

        if ($newImage !== null) {
          deleteImageFile($existingCategory['image']);
          $imageName = $newImage;
        }
      }

      $updateStmt = $pdo->prepare("
                UPDATE categories
                SET category_name = :category_name,
                    slug = :slug,
                    description = :description,
                    image = :image,
                    status = :status,
                    updated_at = NOW()
                WHERE id = :id
            ");

      $updateStmt->execute([
        ':category_name' => $categoryName,
        ':slug'          => $slug,
        ':description'   => $description !== '' ? $description : null,
        ':image'         => $imageName,
        ':status'        => $status,
        ':id'            => $categoryId
      ]);

      echo "<script>
                    alert('Category updated successfully.');
                    window.location.href='categories.php';
                  </script>";
      exit;
    } catch (Exception $e) {

      echo "<script>
                    alert(" . json_encode($e->getMessage()) . ");
                    window.location.href='categories.php';
                  </script>";
      exit;
    }
  }

  if (isset($_POST['delete_category'])) {
    try {

      $categoryId = (int)($_POST['delete_id'] ?? 0);

      if ($categoryId <= 0) {
        throw new Exception('Invalid category selected.');
      }

      $findStmt = $pdo->prepare("SELECT image FROM categories WHERE id = :id LIMIT 1");
      $findStmt->execute([':id' => $categoryId]);
      $categoryToDelete = $findStmt->fetch(PDO::FETCH_ASSOC);

      if (!$categoryToDelete) {
        throw new Exception('Category not found.');
      }

      $deleteStmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
      $deleteStmt->execute([':id' => $categoryId]);

      deleteImageFile($categoryToDelete['image']);

      echo "<script>
                    alert('Category deleted successfully.');
                    window.location.href='categories.php';
                  </script>";
      exit;
    } catch (Exception $e) {

      echo "<script>
                    alert(" . json_encode($e->getMessage()) . ");
                    window.location.href='categories.php';
                  </script>";
      exit;
    }
  }
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll();

$successMessage = $_SESSION['success'] ?? null;
$errorMessage    = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
include './layouts/header.php';

?>

<main class="admin-content">
  <div class="container-fluid px-3 px-lg-2 py-3">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-1">Categories</h4>
        <p class="text-muted mb-0">Manage your office furniture categories.</p>
      </div>
      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <i class="bi bi-plus-lg me-1"></i> Add Category
      </button>
    </div>

    <?php if ($successMessage): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($successMessage); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($errorMessage); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-striped table-hover align-middle w-100" id="example">
            <thead>
              <tr>
                <th scope="col">Sr. No.</th>
                <th scope="col">Image</th>
                <th scope="col">Category Name</th>
                <th scope="col">Description</th>
                <th scope="col">Status</th>
                <th scope="col">Created Date</th>
                <th scope="col" class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($categories) === 0): ?>
                <tr>
                  <td colspan="7" class="text-center text-muted py-4">No categories found.</td>
                </tr>
              <?php else: ?>
                <?php $srNo = 1; ?>
                <?php foreach ($categories as $category): ?>
                  <tr>
                    <td><?php echo $srNo++; ?></td>
                    <td>
                      <?php if (!empty($category['image']) && file_exists(UPLOAD_DIR . $category['image'])): ?>
                        <img src="<?php echo UPLOAD_URL . htmlspecialchars($category['image']); ?>"
                          alt="<?php echo htmlspecialchars($category['category_name']); ?>"
                          style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px;">
                      <?php else: ?>
                        <span class="text-muted small">No Image</span>
                      <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                    <td>
                      <?php
                      $desc = $category['description'] ?? '';
                      echo htmlspecialchars(mb_strimwidth($desc, 0, 70, '...'));
                      ?>
                    </td>
                    <td>
                      <?php if ($category['status'] === 'Active'): ?>
                        <span class="badge bg-success">Active</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">Inactive</span>
                      <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars(date('d M Y', strtotime($category['created_at']))); ?></td>
                    <td class="">
                      <button type="button"
                        class="btn btn-sm btn-outline-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#editCategoryModal<?= $category['id']; ?>">
                        <i class="bi bi-pencil-square"></i>
                      </button>

                      <div class="modal fade" id="editCategoryModal<?= $category['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                          <div class="modal-content">

                            <form method="POST" action="" enctype="multipart/form-data">

                              <input type="hidden" name="category_id" value="<?= $category['id']; ?>">

                              <div class="modal-header">
                                <h5>Edit Category</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                              </div>

                              <div class="modal-body">

                                <div class="mb-3">
                                  <label>Category Name</label>
                                  <input type="text"
                                    class="form-control"
                                    name="category_name"
                                    value="<?= htmlspecialchars($category['category_name']); ?>">
                                </div>

                                <div class="mb-3">
                                  <label>Description</label>
                                  <textarea class="form-control"
                                    name="description"><?= htmlspecialchars($category['description']); ?></textarea>
                                </div>

                                <div class="mb-3">
                                  <label>Status</label>

                                  <select class="form-select" name="status">

                                    <option value="Active"
                                      <?= $category['status'] == 'Active' ? 'selected' : ''; ?>>
                                      Active
                                    </option>

                                    <option value="Inactive"
                                      <?= $category['status'] == 'Inactive' ? 'selected' : ''; ?>>
                                      Inactive
                                    </option>

                                  </select>

                                </div>

                                <?php if (!empty($category['image'])): ?>
                                  <img src="<?= UPLOAD_URL . $category['image']; ?>" width="80">
                                <?php endif; ?>

                                <input type="file"
                                  class="form-control mt-2"
                                  name="image">

                              </div>

                              <div class="modal-footer">

                                <button type="submit"
                                  name="edit_category"
                                  class="btn btn-primary">
                                  Update
                                </button>

                              </div>

                            </form>

                          </div>
                        </div>
                      </div>
                      <form method="POST" onsubmit="return confirmDelete();">
                        <input type="hidden" name="delete_id" value="<?= (int)$category['id']; ?>">

                        <button type="submit"
                          name="delete_category"
                          class="btn btn-sm btn-outline-danger">
                          <i class="bi bi-trash"></i>
                        </button>
                      </form>

                      <script>
                        function confirmDelete() {
                          return confirm("Are you sure you want to delete this category?");
                        }
                      </script>

                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</main>


<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="Categories.php" method="POST" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="addCategoryModalLabel">Add Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label for="addCategoryName" class="form-label">Category Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="addCategoryName" name="category_name" placeholder="Enter category name" required>
          </div>
          <div class="mb-2">
            <label for="addCategoryDescription" class="form-label">Description</label>
            <textarea class="form-control" id="addCategoryDescription" name="description" rows="3" placeholder="Enter description"></textarea>
          </div>
          <div class="row g-3">
            <div class="col-lg-8 col-md-8 col-12 mb-2">
              <label for="addCategoryImage" class="form-label">Image</label>
              <input type="file" class="form-control" id="addCategoryImage" name="image" accept=".jpg,.jpeg,.png,">
              <div class="form-text">Allowed formats: JPG, JPEG, PNG, Max size: 1MB.</div>
              <img id="addImagePreview" src="" alt="Preview" class="mt-2 d-none" style="width: 90px; height: 90px; object-fit: cover; border-radius: 6px;">
            </div>
            <div class="col-lg-4 col-md-4 col-12 mb-2">
              <label for="addCategoryStatus" class="form-label">Status</label>
              <select class="form-select" id="addCategoryStatus" name="status">
                <option value="Active" selected>Active</option>
                <option value="Inactive">Inactive</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="add_category" class="btn btn-primary">Save Category</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="Categories.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteCategoryModalLabel">Delete Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="delete_id" id="deleteCategoryId">
          <p class="mb-0">Are you sure you want to delete this category?</p>
          <p class="text-muted small mb-0">This action cannot be undone and the associated image will be permanently removed.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="delete_category" class="btn btn-danger">Yes, Delete</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include './layouts/footer.php'; ?>
<script>
  document.addEventListener('DOMContentLoaded', function() {


    var deleteButtons = document.querySelectorAll('.delete-category-btn');
    deleteButtons.forEach(function(button) {
      button.addEventListener('click', function() {
        var id = button.getAttribute('data-id');
        document.getElementById('deleteCategoryId').value = id;
      });
    });

    var addImageInput = document.getElementById('addCategoryImage');
    var addImagePreview = document.getElementById('addImagePreview');
    addImageInput.addEventListener('change', function() {
      previewSelectedImage(addImageInput, addImagePreview);
    });

    var editImageInput = document.getElementById('editCategoryImage');
    var editImagePreview = document.getElementById('editImagePreview');
    editImageInput.addEventListener('change', function() {
      previewSelectedImage(editImageInput, editImagePreview);
    });

    function previewSelectedImage(inputEl, previewEl) {
      var file = inputEl.files && inputEl.files[0];
      if (!file) {
        previewEl.src = '';
        previewEl.classList.add('d-none');
        return;
      }
      var reader = new FileReader();
      reader.onload = function(e) {
        previewEl.src = e.target.result;
        previewEl.classList.remove('d-none');
      };
      reader.readAsDataURL(file);
    }

    var addModalEl = document.getElementById('addCategoryModal');
    addModalEl.addEventListener('hidden.bs.modal', function() {
      addModalEl.querySelector('form').reset();
      addImagePreview.src = '';
      addImagePreview.classList.add('d-none');
    });
  });
</script>