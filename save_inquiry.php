<?php
require_once __DIR__ . '/admin_panel/includes/db_conn.php';

if (isset($_POST['send_inquiry'])) {

    $name        = trim($_POST['name']);
    $phone       = trim($_POST['phone']);
    $email       = trim($_POST['email']);
    $city        = trim($_POST['city']);
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $message     = trim($_POST['message']);

    $subject = '';

    if ($category_id) {

        $stmt = $pdo->prepare("SELECT category_name FROM categories WHERE id=?");
        $stmt->execute([$category_id]);

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $subject = $row['category_name'];
        }
    }

    $stmt = $pdo->prepare("
        INSERT INTO inquiries
        (
            name,
            phone,
            email,
            city,
            category_id,
            subject,
            message,
            ip_address,
            user_agent
        ) VALUES (?,?,?,?,?,?,?,?,?)
    ");

    $stmt->execute([
        $name,
        $phone,
        $email,
        $city,
        $category_id,
        $subject,
        $message,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT']
    ]);

    echo "<script>
            alert('Inquiry submitted successfully.');
            window.location.href='index.php';
          </script>";
}
