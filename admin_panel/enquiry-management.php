<?php

/**
 * admin_dashboard.php — Dynamic dashboard with live DB stats.
 */

declare(strict_types=1);
session_start();

if (!isset($_SESSION['adminId'])) {
    header('Location: index.php');
    exit;
}

require __DIR__ . '/includes/db_conn.php';
require __DIR__ . '/config/helpers.php';
$stmt = $pdo->query("
    SELECT
        i.*,
        c.category_name
    FROM inquiries i
    LEFT JOIN categories c
        ON i.category_id = c.id
    ORDER BY i.id DESC
");

$inquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);
include './layouts/header.php';
?>
<main class="dashboard-content">
    <div class="container-fluid px-3 px-lg-2 py-3">
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="bi bi-chat-left-text"></i>
                    Inquiry Management
                </h4>
                <span class="badge bg-primary text-light">
                    Total : <?= count($inquiries); ?>
                </span>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle" id="example">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>City</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th width="180">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inquiries as $row): ?>
                                <tr>
                                    <td><?= $row['id']; ?></td>
                                    <td>
                                        <?= date('d M Y', strtotime($row['created_at'])); ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['name']); ?></td>
                                    <td><?= htmlspecialchars($row['phone']); ?></td>
                                    <td><?= htmlspecialchars($row['email']); ?></td>
                                    <td><?= htmlspecialchars($row['city']); ?></td>
                                    <td><?= htmlspecialchars($row['category_name']); ?></td>
                                    <td>
                                        <?php

                                        switch ($row['status']) {

                                            case 'New':
                                                echo '<span class="badge bg-success">New</span>';
                                                break;

                                            case 'In Progress':
                                                echo '<span class="badge bg-warning text-dark">In Progress</span>';
                                                break;

                                            case 'Completed':
                                                echo '<span class="badge bg-info">Completed</span>';
                                                break;

                                            default:
                                                echo '<span class="badge bg-danger">Closed</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <button
                                            class="btn btn-sm btn-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#viewModal<?= $row['id']; ?>">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <!-- View Modal -->
                                        <div class="modal fade" id="viewModal<?= $row['id']; ?>" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title" id="viewModalLabel">
                                                            Inquiry Details
                                                        </h5>
                                                        <button
                                                            class="btn-close btn-close-white"
                                                            data-bs-dismiss="modal">
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <table class="table table-bordered">
                                                            <tr>
                                                                <th width="200">Customer Name</th>
                                                                <td><?= htmlspecialchars($row['name']); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Phone</th>
                                                                <td><?= htmlspecialchars($row['phone']); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Email</th>
                                                                <td><?= htmlspecialchars($row['email']); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>City</th>
                                                                <td><?= htmlspecialchars($row['city']); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Category</th>
                                                                <td><?= htmlspecialchars($row['category_name']); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Subject</th>
                                                                <td><?= htmlspecialchars($row['subject']); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Message</th>
                                                                <td><?= nl2br(htmlspecialchars($row['message'])); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Status</th>
                                                                <td><?= htmlspecialchars($row['status']); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>IP Address</th>
                                                                <td><?= htmlspecialchars($row['ip_address']); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <th>Submitted On</th>
                                                                <td><?= date('d M Y h:i A', strtotime($row['created_at'])); ?></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <a
                                            href="delete-inquiry.php?id=<?= $row['id']; ?>"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('Delete this inquiry?');">
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
    </div>
</main>

<?php include './layouts/footer.php'; ?>