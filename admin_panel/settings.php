<?php

declare(strict_types=1);
session_start();
require __DIR__ . '/includes/db_conn.php';
require __DIR__ . '/config/helpers.php';
if (isset($_SESSION['adminId'])) {
  include './layouts/header.php';

  $VALID_TYPES = ['mobile', 'landline', 'email', 'address'];
  $ADDRESS_TYPES = ['Main Office', 'Branch', 'Godown'];

  /* =================================================================
 * ACTION: DELETE  (?action=delete&id=)
 * ================================================================= */
  if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id || !csrf_verify($_GET['csrf_token'] ?? null)) {
      flash('error', 'Invalid delete request.');
    } else {
      $stmt = $pdo->prepare("DELETE FROM contact_details WHERE id = :id");
      $stmt->execute([':id' => $id]);
      flash('success', $stmt->rowCount() ? 'Contact detail deleted successfully.' : 'Contact detail not found.');
    }
      echo "<script>
      alert('Contact detail deleted successfully.');
      window.location.href='settings.php';
      </script>";
      exit;
  }

  /* =================================================================
 * ACTION: TOGGLE STATUS  (?action=toggle&id=)
 * ================================================================= */
  if (isset($_GET['action']) && $_GET['action'] === 'toggle') {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id && csrf_verify($_GET['csrf_token'] ?? null)) {
      $pdo->prepare("UPDATE contact_details SET status = 1 - status WHERE id = :id")->execute([':id' => $id]);
    }
    echo "<script>
      window.location.href='settings.php';
      </script>";
      exit;
  }

  /* =================================================================
 * ACTION: CREATE / UPDATE  (POST)
 * ================================================================= */
  $formErrors = [];
  $old = [];
  $openModalOnLoad = false;

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $old = $_POST;
    $editId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null;
    $isEdit = $editId !== null;
    $openModalOnLoad = true;

    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
      $formErrors['general'] = 'Your session expired. Please try again.';
    }

    $type       = $_POST['type'] ?? '';
    $label      = trim((string)($_POST['label'] ?? ''));
    $value      = trim((string)($_POST['value'] ?? ''));
    $addressType = $_POST['address_type'] ?? '';
    $addressLine = trim((string)($_POST['address_line'] ?? ''));
    $city       = trim((string)($_POST['city'] ?? ''));
    $state      = trim((string)($_POST['state'] ?? ''));
    $pincode    = trim((string)($_POST['pincode'] ?? ''));
    $sortOrder  = $_POST['sort_order'] ?? '0';
    $status     = isset($_POST['status']) ? 1 : 0;

    if (!in_array($type, $VALID_TYPES, true)) {
      $formErrors['type'] = 'Please select a valid contact type.';
    }

    if (!is_numeric($sortOrder) || (int)$sortOrder < 0) {
      $formErrors['sort_order'] = 'Sort order must be a non-negative number.';
    }

    // --- Type-specific validation ---
    if ($type === 'mobile') {
      $digits = preg_replace('/[\s\-]/', '', $value);
      if ($value === '') {
        $formErrors['value'] = 'Mobile number is required.';
      } elseif (!preg_match('/^\+?[0-9]{10,15}$/', $digits)) {
        $formErrors['value'] = 'Enter a valid mobile number (10–15 digits, optional +country code).';
      }
    } elseif ($type === 'landline') {
      $digits = preg_replace('/[\s\-]/', '', $value);
      if ($value === '') {
        $formErrors['value'] = 'Landline number is required.';
      } elseif (!preg_match('/^\+?[0-9]{6,15}$/', $digits)) {
        $formErrors['value'] = 'Enter a valid landline number (include STD/area code).';
      }
    } elseif ($type === 'email') {
      if ($value === '') {
        $formErrors['value'] = 'Email address is required.';
      } elseif (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
        $formErrors['value'] = 'Enter a valid email address.';
      }
    } elseif ($type === 'address') {
      if (!in_array($addressType, $ADDRESS_TYPES, true)) {
        $formErrors['address_type'] = 'Please select an address type.';
      }
      if ($addressLine === '') {
        $formErrors['address_line'] = 'Address line is required.';
      } elseif (mb_strlen($addressLine) > 500) {
        $formErrors['address_line'] = 'Address must be under 500 characters.';
      }
      if ($pincode !== '' && !preg_match('/^[0-9]{4,10}$/', $pincode)) {
        $formErrors['pincode'] = 'Enter a valid pincode/ZIP.';
      }
      if ($label === '') {
        $label = $addressType; // sensible default so the list stays readable
      }
    }

    if (empty($formErrors)) {
      try {
        $params = [
          ':type'         => $type,
          ':label'        => $label !== '' ? $label : null,
          ':value'        => in_array($type, ['mobile', 'landline', 'email'], true) ? $value : null,
          ':address_type' => $type === 'address' ? $addressType : null,
          ':address_line' => $type === 'address' ? $addressLine : null,
          ':city'         => $type === 'address' ? ($city ?: null) : null,
          ':state'        => $type === 'address' ? ($state ?: null) : null,
          ':pincode'      => $type === 'address' ? ($pincode ?: null) : null,
          ':sort_order'   => (int)$sortOrder,
          ':status'       => $status,
        ];

        if ($isEdit) {
          $params[':id'] = $editId;
          $sql = "UPDATE contact_details SET
                            type=:type, label=:label, value=:value,
                            address_type=:address_type, address_line=:address_line,
                            city=:city, state=:state, pincode=:pincode,
                            sort_order=:sort_order, status=:status
                        WHERE id=:id";
          $pdo->prepare($sql)->execute($params);
          flash('success', 'Contact detail updated successfully.');
          echo "<script>
          alert('Contact detail updated successfully.');
          window.location.href='settings.php';
          </script>";
          exit;
        } else {
          $sql = "INSERT INTO contact_details
                            (type, label, value, address_type, address_line, city, state, pincode, sort_order, status)
                        VALUES
                            (:type, :label, :value, :address_type, :address_line, :city, :state, :pincode, :sort_order, :status)";
          $pdo->prepare($sql)->execute($params);
          flash('success', 'Contact detail added successfully.');
          echo "<script>
            alert('Contact detail added successfully.');
            window.location.href='settings.php';
            </script>";
            exit;
        }
        exit;
      } catch (Throwable $e) {
        error_log('Contact detail save failed: ' . $e->getMessage());
        $formErrors['general'] = 'Something went wrong while saving. Please try again.';
      }
    }
  }

  /* =================================================================
 * FETCH LIST
 * ================================================================= */
  $contacts = $pdo->query("SELECT * FROM contact_details ORDER BY FIELD(type,'mobile','landline','email','address'), sort_order ASC, id DESC")->fetchAll();
  $csrfToken = csrf_token();

  function typeIcon(string $type): string
  {
    return match ($type) {
      'mobile'   => 'bi-phone',
      'landline' => 'bi-telephone',
      'email'    => 'bi-envelope',
      'address'  => 'bi-geo-alt',
      default    => 'bi-dot',
    };
  }
  function typeBadgeClass(string $type): string
  {
    return match ($type) {
      'mobile'   => 'bg-primary',
      'landline' => 'bg-info text-dark',
      'email'    => 'bg-warning text-dark',
      'address'  => 'bg-success',
      default    => 'bg-secondary',
    };
  }
?>
  <main class="dashboard-content">
    <div class="container-fluid px-3 px-lg-4 py-4">

      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h4 class="mb-0">Settings</h4>
          <div class="text-muted small">Contact Details — mobile, landline, email &amp; office addresses</div>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#contactModal" onclick="resetForm()">
          <i class="bi bi-plus-circle"></i> Add Contact Detail
        </button>
      </div>

      <?php if ($msg = get_flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
          <?= e($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
      <?php if ($msg = get_flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
          <?= e($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <!-- Filter tabs -->
      <ul class="nav nav-pills mb-3" id="filterTabs">
        <li class="nav-item"><button class="nav-link active" data-filter="all">All</button></li>
        <li class="nav-item"><button class="nav-link" data-filter="mobile"><i class="bi bi-phone"></i> Mobile</button></li>
        <li class="nav-item"><button class="nav-link" data-filter="landline"><i class="bi bi-telephone"></i> Landline</button></li>
        <li class="nav-item"><button class="nav-link" data-filter="email"><i class="bi bi-envelope"></i> Email</button></li>
        <li class="nav-item"><button class="nav-link" data-filter="address"><i class="bi bi-geo-alt"></i> Address</button></li>
      </ul>

      <div class="card">
        <div class="card-body p-0">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Type</th>
                <th>Label</th>
                <th>Details</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!$contacts): ?>
                <tr>
                  <td colspan="5" class="text-center text-muted py-4">No contact details yet. Click "Add Contact Detail" to create one.</td>
                </tr>
              <?php endif; ?>
              <?php foreach ($contacts as $c): ?>
                <tr data-type="<?= e($c['type']) ?>">
                  <td>
                    <span class="badge <?= typeBadgeClass($c['type']) ?>">
                      <i class="bi <?= typeIcon($c['type']) ?>"></i> <?= ucfirst($c['type']) ?>
                    </span>
                    <?php if ($c['type'] === 'address'): ?>
                      <div class="small text-muted mt-1"><?= e($c['address_type']) ?></div>
                    <?php endif; ?>
                  </td>
                  <td><?= e($c['label'] ?: '—') ?></td>
                  <td>
                    <?php if ($c['type'] === 'address'): ?>
                      <?= nl2br(e($c['address_line'])) ?>
                      <?php
                      $parts = array_filter([$c['city'], $c['state'], $c['pincode']]);
                      if ($parts) echo '<div class="small text-muted">' . e(implode(', ', $parts)) . '</div>';
                      ?>
                    <?php else: ?>
                      <?= e($c['value']) ?>
                    <?php endif; ?>
                  </td>
                  <td>
                    <a href="settings.php?action=toggle&id=<?= (int)$c['id'] ?>&csrf_token=<?= e($csrfToken) ?>"
                      class="badge text-decoration-none <?= $c['status'] ? 'bg-success' : 'bg-secondary' ?>">
                      <?= $c['status'] ? 'Active' : 'Inactive' ?>
                    </a>
                  </td>
                  <td class="text-end">
                    <button class="btn btn-sm btn-outline-primary edit-btn"
                      data-id="<?= (int)$c['id'] ?>"
                      data-type="<?= e($c['type']) ?>"
                      data-label="<?= e($c['label']) ?>"
                      data-value="<?= e($c['value']) ?>"
                      data-address_type="<?= e($c['address_type']) ?>"
                      data-address_line="<?= e($c['address_line']) ?>"
                      data-city="<?= e($c['city']) ?>"
                      data-state="<?= e($c['state']) ?>"
                      data-pincode="<?= e($c['pincode']) ?>"
                      data-sort_order="<?= (int)$c['sort_order'] ?>"
                      data-status="<?= (int)$c['status'] ?>">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <a href="javascript:void(0)" class="btn btn-sm btn-outline-danger delete-btn"
                      data-id="<?= (int)$c['id'] ?>"
                      data-title="<?= e($c['label'] ?: ucfirst($c['type'])) ?>">
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
    <div class="modal fade" id="contactModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form action="settings.php" method="post" id="contactForm">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <input type="hidden" name="id" id="f_id" value="<?= (!empty($old['id']) ? (int)$old['id'] : '') ?>">

            <div class="modal-header">
              <h5 class="modal-title" id="modalTitle">Add Contact Detail</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
              <?php if (!empty($formErrors['general'])): ?>
                <div class="alert alert-danger"><?= e($formErrors['general']) ?></div>
              <?php endif; ?>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Type <span class="text-danger">*</span></label>
                  <select name="type" id="f_type" class="form-select <?= isset($formErrors['type']) ? 'is-invalid' : '' ?>" required>
                    <option value="">Select Type</option>
                    <option value="mobile" <?= ($old['type'] ?? '') === 'mobile' ? 'selected' : '' ?>>Mobile Number</option>
                    <option value="landline" <?= ($old['type'] ?? '') === 'landline' ? 'selected' : '' ?>>Landline Number</option>
                    <option value="email" <?= ($old['type'] ?? '') === 'email' ? 'selected' : '' ?>>Email</option>
                    <option value="address" <?= ($old['type'] ?? '') === 'address' ? 'selected' : '' ?>>Address</option>
                  </select>
                  <?php if (isset($formErrors['type'])): ?><div class="invalid-feedback"><?= e($formErrors['type']) ?></div><?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Label <span class="text-muted small">(optional, e.g. "Sales", "Support")</span></label>
                  <input type="text" name="label" id="f_label" maxlength="150" class="form-control" value="<?= e($old['label'] ?? '') ?>">
                </div>

                <!-- Mobile / Landline / Email value field -->
                <div class="col-12 mb-3" id="valueGroup">
                  <label class="form-label" id="valueLabel">Value <span class="text-danger">*</span></label>
                  <input type="text" name="value" id="f_value" class="form-control <?= isset($formErrors['value']) ? 'is-invalid' : '' ?>" value="<?= e($old['value'] ?? '') ?>">
                  <?php if (isset($formErrors['value'])): ?><div class="invalid-feedback"><?= e($formErrors['value']) ?></div><?php endif; ?>
                </div>

                <!-- Address-only fields -->
                <div id="addressGroup" class="d-none">
                  <div class="col-12 mb-3">
                    <label class="form-label">Address Type <span class="text-danger">*</span></label>
                    <select name="address_type" id="f_address_type" class="form-select <?= isset($formErrors['address_type']) ? 'is-invalid' : '' ?>">
                      <option value="">Select</option>
                      <?php foreach ($ADDRESS_TYPES as $at): ?>
                        <option value="<?= e($at) ?>" <?= ($old['address_type'] ?? '') === $at ? 'selected' : '' ?>><?= e($at) ?></option>
                      <?php endforeach; ?>
                    </select>
                    <?php if (isset($formErrors['address_type'])): ?><div class="invalid-feedback"><?= e($formErrors['address_type']) ?></div><?php endif; ?>
                  </div>
                  <div class="col-12 mb-3">
                    <label class="form-label">Address Line <span class="text-danger">*</span></label>
                    <textarea name="address_line" id="f_address_line" rows="2" class="form-control <?= isset($formErrors['address_line']) ? 'is-invalid' : '' ?>"><?= e($old['address_line'] ?? '') ?></textarea>
                    <?php if (isset($formErrors['address_line'])): ?><div class="invalid-feedback"><?= e($formErrors['address_line']) ?></div><?php endif; ?>
                  </div>
                  <div class="row">
                    <div class="col-md-4 mb-3">
                      <label class="form-label">City</label>
                      <input type="text" name="city" id="f_city" class="form-control" value="<?= e($old['city'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                      <label class="form-label">State</label>
                      <input type="text" name="state" id="f_state" class="form-control" value="<?= e($old['state'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                      <label class="form-label">Pincode</label>
                      <input type="text" name="pincode" id="f_pincode" class="form-control <?= isset($formErrors['pincode']) ? 'is-invalid' : '' ?>" value="<?= e($old['pincode'] ?? '') ?>">
                      <?php if (isset($formErrors['pincode'])): ?><div class="invalid-feedback"><?= e($formErrors['pincode']) ?></div><?php endif; ?>
                    </div>
                  </div>
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Sort Order</label>
                  <input type="number" name="sort_order" id="f_sort_order" min="0" class="form-control <?= isset($formErrors['sort_order']) ? 'is-invalid' : '' ?>" value="<?= e($old['sort_order'] ?? '0') ?>">
                  <?php if (isset($formErrors['sort_order'])): ?><div class="invalid-feedback"><?= e($formErrors['sort_order']) ?></div><?php endif; ?>
                </div>
                <div class="col-md-6 mb-3 d-flex align-items-end">
                  <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="status" id="f_status" <?= (($old['status'] ?? '1') ? 'checked' : '') ?>>
                    <label class="form-check-label" for="f_status">Active</label>
                  </div>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save</button>
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
            <h5 class="modal-title">Delete Contact Detail</h5>
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

    </div>
  </main>
  <?php
  include "./layouts/footer.php";
  ?>
  <script>
    const csrfToken = <?= json_encode($csrfToken) ?>;
    const contactModalEl = document.getElementById('contactModal');
    const contactModal = new bootstrap.Modal(contactModalEl);

    const valueLabels = {
      mobile: 'Mobile Number',
      landline: 'Landline Number',
      email: 'Email Address'
    };

    function toggleTypeFields() {
      const type = document.getElementById('f_type').value;
      const valueGroup = document.getElementById('valueGroup');
      const addressGroup = document.getElementById('addressGroup');
      const valueInput = document.getElementById('f_value');
      const addressTypeInput = document.getElementById('f_address_type');
      const addressLineInput = document.getElementById('f_address_line');

      if (type === 'address') {
        valueGroup.classList.add('d-none');
        addressGroup.classList.remove('d-none');
        valueInput.required = false;
        addressTypeInput.required = true;
        addressLineInput.required = true;
      } else {
        valueGroup.classList.remove('d-none');
        addressGroup.classList.add('d-none');
        valueInput.required = true;
        addressTypeInput.required = false;
        addressLineInput.required = false;

        document.getElementById('valueLabel').innerHTML =
          (valueLabels[type] || 'Value') + ' <span class="text-danger">*</span>';
        valueInput.type = type === 'email' ? 'email' : 'text';
        valueInput.placeholder = type === 'mobile' ? 'e.g. +91 98765 43210' :
          type === 'landline' ? 'e.g. 020-12345678' :
          type === 'email' ? 'e.g. info@company.com' : '';
      }
    }
    document.getElementById('f_type').addEventListener('change', toggleTypeFields);

    function resetForm() {
      document.getElementById('contactForm').reset();
      document.getElementById('f_id').value = '';
      document.getElementById('modalTitle').textContent = 'Add Contact Detail';
      toggleTypeFields();
    }

    // Populate + open modal for editing
    document.querySelectorAll('.edit-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const d = btn.dataset;
        document.getElementById('contactForm').reset();
        document.getElementById('f_id').value = d.id;
        document.getElementById('f_type').value = d.type;
        document.getElementById('f_label').value = d.label;
        document.getElementById('f_value').value = d.value;
        document.getElementById('f_address_type').value = d.address_type;
        document.getElementById('f_address_line').value = d.address_line;
        document.getElementById('f_city').value = d.city;
        document.getElementById('f_state').value = d.state;
        document.getElementById('f_pincode').value = d.pincode;
        document.getElementById('f_sort_order').value = d.sort_order;
        document.getElementById('f_status').checked = d.status === '1';
        document.getElementById('modalTitle').textContent = 'Edit Contact Detail';
        toggleTypeFields();
        contactModal.show();
      });
    });

    // Delete confirmation
    document.querySelectorAll('.delete-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('deleteTitle').textContent = btn.dataset.title;
        document.getElementById('confirmDeleteLink').href =
          'settings.php?action=delete&id=' + btn.dataset.id + '&csrf_token=' + encodeURIComponent(csrfToken);
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
      });
    });

    // Filter tabs
    document.querySelectorAll('#filterTabs .nav-link').forEach(tab => {
      tab.addEventListener('click', () => {
        document.querySelectorAll('#filterTabs .nav-link').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        const filter = tab.dataset.filter;
        document.querySelectorAll('tbody tr[data-type]').forEach(row => {
          row.style.display = (filter === 'all' || row.dataset.type === filter) ? '' : 'none';
        });
      });
    });

    toggleTypeFields();

    <?php if ($openModalOnLoad): ?>
      document.addEventListener('DOMContentLoaded', () => {
        <?php if (!empty($old['id'])): ?> document.getElementById('modalTitle').textContent = 'Edit Contact Detail';
        <?php endif; ?>
        toggleTypeFields();
        contactModal.show();
      });
    <?php endif; ?>
  </script>
<?php
} else {
  header("Location:index.php");
}
?>