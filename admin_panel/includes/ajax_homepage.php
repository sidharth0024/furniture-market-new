<?php
/**
 * ajax_homepage.php — AJAX endpoint for homepage management actions.
 * Called from homepage.php via fetch().
 */
header('Content-Type: application/json; charset=utf-8');
session_start();

if (!isset($_SESSION['adminId'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require __DIR__ . '/../includes/db_conn.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    /* Toggle category Active ↔ Inactive */
    case 'toggle_category':
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id) { echo json_encode(['success' => false, 'message' => 'Invalid ID']); exit; }
        $stmt = $pdo->prepare("SELECT status FROM categories WHERE id=:id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) { echo json_encode(['success' => false, 'message' => 'Not found']); exit; }
        $newStatus = $row['status'] === 'Active' ? 'Inactive' : 'Active';
        $pdo->prepare("UPDATE categories SET status=:s WHERE id=:id")
            ->execute([':s' => $newStatus, ':id' => $id]);
        echo json_encode(['success' => true, 'new_status' => $newStatus]);
        break;

    /* Toggle product is_featured */
    case 'toggle_featured':
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id) { echo json_encode(['success' => false, 'message' => 'Invalid ID']); exit; }
        $pdo->prepare("UPDATE products SET is_featured = 1 - is_featured WHERE id=:id")
            ->execute([':id' => $id]);
        $newVal = (int)$pdo->query("SELECT is_featured FROM products WHERE id={$id}")->fetchColumn();
        echo json_encode(['success' => true, 'is_featured' => $newVal]);
        break;

    /* Toggle testimonial status */
    case 'toggle_testimonial':
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id) { echo json_encode(['success' => false, 'message' => 'Invalid ID']); exit; }
        $pdo->prepare("UPDATE testimonials SET status = 1 - status WHERE id=:id")
            ->execute([':id' => $id]);
        $newVal = (int)$pdo->query("SELECT status FROM testimonials WHERE id={$id}")->fetchColumn();
        echo json_encode(['success' => true, 'status' => $newVal]);
        break;

    /* Toggle slider status */
    case 'toggle_slider':
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id) { echo json_encode(['success' => false, 'message' => 'Invalid ID']); exit; }
        $pdo->prepare("UPDATE sliders SET status = 1 - status WHERE id=:id")
            ->execute([':id' => $id]);
        $newVal = (int)$pdo->query("SELECT status FROM sliders WHERE id={$id}")->fetchColumn();
        echo json_encode(['success' => true, 'status' => $newVal]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
}
