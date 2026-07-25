<?php
/**
 * header.php — Universal page header
 * Opens <html> + <head>, loads CSS. navbar.php closes </head> and opens <body>.
 */
require_once __DIR__ . '/../admin_panel/includes/db_conn.php';
require_once __DIR__ . '/../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Project CSS -->
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/pages.css">
  <link rel="stylesheet" href="css/fixes.css">
