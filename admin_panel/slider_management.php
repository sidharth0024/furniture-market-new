<?php

declare(strict_types=1);
session_start();
require __DIR__ . '/includes/db_conn.php';
require __DIR__ . '/config/helpers.php';
if (isset($_SESSION['adminId'])) {
    include './layouts/header.php';


    /* =================================================================
                * CONFIG
                * ================================================================= */
    $uploadDir   = __DIR__ . '/uploads/sliders/';
    $uploadWeb   = 'uploads/sliders/';
    $allowedMime = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $maxSize     = 3 * 1024 * 1024; // 3MB

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    /* =================================================================
                * ACTION: DELETE  (?action=delete&id=)
                * ================================================================= */
    if (isset($_GET['action']) && $_GET['action'] === 'delete') {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id || !csrf_verify($_GET['csrf_token'] ?? null)) {
            flash('error', 'Invalid delete request.');
        } else {
            $stmt = $pdo->prepare("SELECT image FROM sliders WHERE id = :id");
            $stmt->execute([':id' => $id]);
            if ($row = $stmt->fetch()) {
                $pdo->prepare("DELETE FROM sliders WHERE id = :id")->execute([':id' => $id]);
                $path = __DIR__ . '/' . ltrim($row['image'], '/');
                if (is_file($path)) @unlink($path);
                flash('success', 'Slider deleted successfully.');
            } else {
                flash('error', 'Slider not found.');
            }
        }
        echo "<script>alert('Slider delete successfully.');window.location.href = slider_management.php</script>";
        // header('Location: slider_management.php');
        exit;
    }

    /* =================================================================
                * ACTION: TOGGLE STATUS  (?action=toggle&id=)
                * ================================================================= */
    if (isset($_GET['action']) && $_GET['action'] === 'toggle') {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($id && csrf_verify($_GET['csrf_token'] ?? null)) {
            $pdo->prepare("UPDATE sliders SET status = 1 - status WHERE id = :id")->execute([':id' => $id]);
        }
        echo "<script>alert('Slider added successfully.');window.location.href = slider_management.php</script>";
        // header('Location: slider_management.php');
        exit;
    }

    /* =================================================================
                * ACTION: CREATE / UPDATE  (POST)
                * ================================================================= */
    $formErrors = [];
    $old = [];
    $openModalOnLoad = false;
    $editIdOnError = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
        $old = $_POST;
        $editId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null;
        $isEdit = $editId !== null;
        $editIdOnError = $editId;
        $openModalOnLoad = true;

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            $formErrors['general'] = 'Your session expired. Please try again.';
        }

        $title       = trim((string)($_POST['title'] ?? ''));
        $subtitle    = trim((string)($_POST['subtitle'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $sortOrder   = $_POST['sort_order'] ?? '0';
        $status      = isset($_POST['status']) ? 1 : 0;

        if ($title === '') {
            $formErrors['title'] = 'Title is required.';
        } elseif (mb_strlen($title) > 255) {
            $formErrors['title'] = 'Title must be under 255 characters.';
        }

        if ($subtitle !== '' && mb_strlen($subtitle) > 255) {
            $formErrors['subtitle'] = 'Subtitle must be under 255 characters.';
        }

        if (!is_numeric($sortOrder) || (int)$sortOrder < 0) {
            $formErrors['sort_order'] = 'Sort order must be a non-negative number.';
        }

        // --- Image handling ---
        $newImagePath = null;
        $hasFile = isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE;

        if ($hasFile) {
            if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                $formErrors['image'] = 'Image failed to upload. Please try again.';
            } elseif ($_FILES['image']['size'] > $maxSize) {
                $formErrors['image'] = 'Image must be 3MB or smaller.';
            } else {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
                finfo_close($finfo);

                if (!isset($allowedMime[$mime])) {
                    $formErrors['image'] = 'Only JPG, PNG, and WEBP images are allowed.';
                } else {
                    $filename = 'slider_' . bin2hex(random_bytes(8)) . '.' . $allowedMime[$mime];
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                        $formErrors['image'] = 'Could not save the uploaded image.';
                    } else {
                        $newImagePath = $uploadWeb . $filename;
                    }
                }
            }
        } elseif (!$isEdit) {
            $formErrors['image'] = 'Please upload a slider image.';
        }

        if (empty($formErrors)) {
            try {
                if ($isEdit) {
                    if ($newImagePath) {
                        // Remove the old image file once the new one is confirmed saved.
                        $stmt = $pdo->prepare("SELECT image FROM sliders WHERE id = :id");
                        $stmt->execute([':id' => $editId]);
                        if ($old_row = $stmt->fetch()) {
                            $oldPath = __DIR__ . '/' . ltrim($old_row['image'], '/');
                            if (is_file($oldPath)) @unlink($oldPath);
                        }
                        $sql = "UPDATE sliders SET title=:title, subtitle=:subtitle, description=:description,
                                image=:image, sort_order=:sort_order, status=:status WHERE id=:id";
                        $params = [
                            ':title' => $title,
                            ':subtitle' => $subtitle,
                            ':description' => $description,
                            ':image' => $newImagePath,
                            ':sort_order' => (int)$sortOrder,
                            ':status' => $status,
                            ':id' => $editId,
                        ];
                    } else {
                        $sql = "UPDATE sliders SET title=:title, subtitle=:subtitle, description=:description,
                                sort_order=:sort_order, status=:status WHERE id=:id";
                        $params = [
                            ':title' => $title,
                            ':subtitle' => $subtitle,
                            ':description' => $description,
                            ':sort_order' => (int)$sortOrder,
                            ':status' => $status,
                            ':id' => $editId,
                        ];
                    }
                    $pdo->prepare($sql)->execute($params);
                    flash('success', 'Slider updated successfully.');
                    echo "<script>alert('Slider Updated successfully.');window.location.href = slider_management.php</script>";
                } else {
                    $sql = "INSERT INTO sliders (title, subtitle, description, image, sort_order, status)
                        VALUES (:title, :subtitle, :description, :image, :sort_order, :status)";
                    $pdo->prepare($sql)->execute([
                        ':title' => $title,
                        ':subtitle' => $subtitle,
                        ':description' => $description,
                        ':image' => $newImagePath,
                        ':sort_order' => (int)$sortOrder,
                        ':status' => $status,
                    ]);
                    flash('success', 'Slider added successfully.');
                }
                echo "<script>alert('Slider added successfully.');window.location.href = slider_management.php</script>";
                exit;
            } catch (Throwable $e) {
                error_log('Slider save failed: ' . $e->getMessage());
                $formErrors['general'] = 'Something went wrong while saving. Please try again.';
            }
        }
    }

    /* =================================================================
            * FETCH LIST
            * ================================================================= */
    $sliders = $pdo->query("SELECT * FROM sliders ORDER BY sort_order ASC, id DESC")->fetchAll();
    $csrfToken = csrf_token();
?>
    <main class="dashboard-content">
        <div class="container-fluid px-3 px-lg-2 py-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Slider Management</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sliderModal" onclick="resetForm()">
                    <i class="bi bi-plus-circle"></i> Add Slider
                </button>
            </div>

            <?php if ($msg = get_flash('success')): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= e($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if ($msg = get_flash('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= e($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="sort-badge">#</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$sliders): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No sliders yet. Click "Add Slider" to create one.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($sliders as $s): ?>
                                <tr>
                                    <td><span class="badge bg-secondary"><?= (int)$s['sort_order'] ?></span></td>
                                    <td><img src="../<?= e($s['image']) ?>" class="slider-thumb" alt="<?= e($s['title']) ?>"></td>
                                    <td>
                                        <div class="fw-semibold"><?= e($s['title']) ?></div>
                                        <?php if ($s['subtitle']): ?><div class="small text-muted"><?= e($s['subtitle']) ?></div><?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="slider_management.php?action=toggle&id=<?= (int)$s['id'] ?>&csrf_token=<?= e($csrfToken) ?>"
                                            class="badge text-decoration-none <?= $s['status'] ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= $s['status'] ? 'Active' : 'Inactive' ?>
                                        </a>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary edit-btn"
                                            data-id="<?= (int)$s['id'] ?>"
                                            data-title="<?= e($s['title']) ?>"
                                            data-subtitle="<?= e($s['subtitle']) ?>"
                                            data-description="<?= e($s['description']) ?>"
                                            data-image="<?= e($s['image']) ?>"
                                            data-sort_order="<?= (int)$s['sort_order'] ?>"
                                            data-status="<?= (int)$s['status'] ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="javascript:void(0)" class="btn btn-sm btn-outline-danger delete-btn"
                                            data-id="<?= (int)$s['id'] ?>" data-title="<?= e($s['title']) ?>">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add / Edit Modal -->
        <div class="modal fade" id="sliderModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="slider_management.php" method="post" enctype="multipart/form-data" id="sliderForm">
                        <input type="hidden" name="action" value="save">
                        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                        <input type="hidden" name="id" id="f_id" value="<?= $editIdOnError ? (int)$editIdOnError : '' ?>">

                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTitle">Add Slider</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <?php if (!empty($formErrors['general'])): ?>
                                <div class="alert alert-danger"><?= e($formErrors['general']) ?></div>
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-7 mb-3">
                                    <label class="form-label">Title <span class="text-danger">*</span></label>
                                    <input type="text" name="title" id="f_title" maxlength="255"
                                        class="form-control <?= isset($formErrors['title']) ? 'is-invalid' : '' ?>"
                                        value="<?= e($old['title'] ?? '') ?>" placeholder="Enter Title" required>
                                    <?php if (isset($formErrors['title'])): ?><div class="invalid-feedback"><?= e($formErrors['title']) ?></div><?php endif; ?>
                                </div>
                                <div class="col-md-5 mb-3">
                                    <label class="form-label">Subtitle</label>
                                    <input type="text" name="subtitle" id="f_subtitle" maxlength="255"
                                        class="form-control <?= isset($formErrors['subtitle']) ? 'is-invalid' : '' ?>"
                                        value="<?= e($old['subtitle'] ?? '') ?>" placeholder="Enter Subtitle">
                                    <?php if (isset($formErrors['subtitle'])): ?><div class="invalid-feedback"><?= e($formErrors['subtitle']) ?></div><?php endif; ?>
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" id="f_description" rows="3" class="form-control" placeholder="Description"><?= e($old['description'] ?? '') ?></textarea>
                                </div>

                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Sort Order</label>
                                    <input type="number" name="sort_order" id="f_sort_order" min="0"
                                        class="form-control <?= isset($formErrors['sort_order']) ? 'is-invalid' : '' ?>"
                                        value="<?= e($old['sort_order'] ?? '0') ?>">
                                    <?php if (isset($formErrors['sort_order'])): ?><div class="invalid-feedback"><?= e($formErrors['sort_order']) ?></div><?php endif; ?>
                                    <div class="form-text">Lower numbers appear first.</div>
                                </div>
                                <div class="col-md-4 mb-3 d-flex align-items-center">
                                    <div class="form-check form-switch ">
                                        <input class="form-check-input" type="checkbox" name="status" id="f_status" <?= (($old['status'] ?? '1') ? 'checked' : '') ?>>
                                        <label class="form-check-label" for="f_status">Active</label>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Slider Image <span class="text-danger" id="imgRequiredMark">*</span></label>
                                    <label class="drop-zone w-100">
                                        <i class="bi bi-cloud-upload fs-2"></i>
                                        <div>Click to upload (JPG, PNG, WEBP — max 3MB, recommended 1920x800)</div>
                                        <input type="file" name="image" id="f_image" hidden accept="image/png,image/jpeg,image/webp">
                                    </label>
                                    <?php if (isset($formErrors['image'])): ?><div class="invalid-feedback"><?= e($formErrors['image']) ?></div><?php endif; ?>
                                    <img id="previewImg" src="">
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save Slider</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Confirm Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Slider</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete "<span id="deleteTitle" class="fw-semibold"></span>"? This cannot be undone.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <a href="javascript:void(0)" id="confirmDeleteLink" class="btn btn-danger">Delete</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php
    include "./layouts/footer.php";
    ?>
    <script>
        const csrfToken = <?= json_encode($csrfToken) ?>;
        const sliderModalEl = document.getElementById('sliderModal');
        const sliderModal = new bootstrap.Modal(sliderModalEl);

        function resetForm() {
            document.getElementById('sliderForm').reset();
            document.getElementById('f_id').value = '';
            document.getElementById('modalTitle').textContent = 'Add Slider';
            document.getElementById('imgRequiredMark').style.display = 'inline';
            document.getElementById('previewImg').style.display = 'none';
            document.getElementById('f_image').required = true;
        }

        // Populate + open modal for editing
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const d = btn.dataset;
                document.getElementById('sliderForm').reset();
                document.getElementById('f_id').value = d.id;
                document.getElementById('f_title').value = d.title;
                document.getElementById('f_subtitle').value = d.subtitle;
                document.getElementById('f_description').value = d.description;
                document.getElementById('f_sort_order').value = d.sort_order;
                document.getElementById('f_status').checked = d.status === '1';
                document.getElementById('modalTitle').textContent = 'Edit Slider';
                document.getElementById('imgRequiredMark').style.display = 'none';
                document.getElementById('f_image').required = false;

                const preview = document.getElementById('previewImg');
                preview.src = d.image;
                preview.style.display = 'block';

                sliderModal.show();
            });
        });

        // Image preview on new upload
        document.getElementById('f_image').addEventListener('change', function() {
            const file = this.files[0];
            const preview = document.getElementById('previewImg');
            if (!file) {
                return;
            }
            const reader = new FileReader();
            reader.onload = e => {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });

        // Delete confirmation
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('deleteTitle').textContent = btn.dataset.title;
                document.getElementById('confirmDeleteLink').href =
                    'slider_management.php?action=delete&id=' + btn.dataset.id + '&csrf_token=' + encodeURIComponent(csrfToken);
                new bootstrap.Modal(document.getElementById('deleteModal')).show();
            });
        });

        <?php if ($openModalOnLoad): ?>
            // Re-open the modal automatically if the last submit had validation errors
            document.addEventListener('DOMContentLoaded', () => {
                <?php if (!empty($old['id'])): ?>
                    document.getElementById('modalTitle').textContent = 'Edit Slider';
                    document.getElementById('imgRequiredMark').style.display = 'none';
                    document.getElementById('f_image').required = false;
                <?php endif; ?>
                sliderModal.show();
            });
        <?php endif; ?>
    </script>
<?php
} else {
    header("Location:index.php");
}
?>