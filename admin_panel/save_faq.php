<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/includes/db_conn.php';

if (!isset($_SESSION['adminId']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$faqId     = !empty($_POST['faq_id'])    ? (int)$_POST['faq_id']   : null;
$question  = trim($_POST['question']     ?? '');
$answer    = trim($_POST['answer']       ?? '');
$status    = (int)($_POST['status']      ?? 1);
$sortOrder = (int)($_POST['sort_order']  ?? 0);

if ($question === '' || $answer === '') {
    header('Location: product_faqs.php?msg=Question+and+answer+are+required&type=danger');
    exit;
}

try {
    if ($faqId) {
        $pdo->prepare(
            "UPDATE product_faqs
                SET question = :q, answer = :a, status = :s, sort_order = :o
              WHERE id = :id"
        )->execute([':q' => $question, ':a' => $answer, ':s' => $status, ':o' => $sortOrder, ':id' => $faqId]);
        $msg = 'FAQ+updated+successfully';
    } else {
        $pdo->prepare(
            "INSERT INTO product_faqs (question, answer, status, sort_order)
             VALUES (:q, :a, :s, :o)"
        )->execute([':q' => $question, ':a' => $answer, ':s' => $status, ':o' => $sortOrder]);
        $msg = 'FAQ+added+successfully';
    }
} catch (PDOException $e) {
    header('Location: product_faqs.php?msg=' . urlencode('DB error: ' . $e->getMessage()) . '&type=danger');
    exit;
}

header('Location: product_faqs.php?msg=' . $msg . '&type=success');
exit;
