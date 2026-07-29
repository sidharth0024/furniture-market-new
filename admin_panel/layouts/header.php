<?php

$_adminCurrentPage = strtolower(basename($_SERVER['PHP_SELF']));

function _adminActive(array $pages): string
{
    global $_adminCurrentPage;
    return in_array($_adminCurrentPage, $pages, true) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Furniture Shoppers Admin Panel">
    <title>Furniture Admin Panel</title>

    <link rel="stylesheet" href="./assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="./assets/vendors/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="./assets/css/style.css">

    <!-- Summernote -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-lite.min.css" rel="stylesheet">
</head>

<body>
    <div class="admin-shell">
        <div class="sidebar-backdrop" data-sidebar-close></div>

        <aside class="admin-sidebar" id="adminSidebar" aria-label="Main navigation">
            <div class="sidebar-header">
                <a class="brand-mark" href="index.php" aria-label="Furniture Shoppers Admin">
                    <span class="brand-icon"><i class="bi bi-grid-1x2-fill" aria-hidden="true"></i></span>
                    <span class="brand-copy">
                        <span class="brand-title">Office Furniture</span>
                        <span class="brand-subtitle">Admin Panel</span>
                    </span>
                </a>
            </div>

            <nav class="sidebar-nav">
                <a class="nav-link <?= _adminActive(['index.php', 'admin_dashboard.php']) ?>" href="index.php">
                    <span class="nav-icon"><i class="bi bi-speedometer2" aria-hidden="true"></i></span>
                    <span class="nav-text">Dashboard</span>
                </a>
                <a class="nav-link <?= _adminActive(['categories.php']) ?>" href="categories.php">
                    <span class="nav-icon"><i class="bi bi-grid" aria-hidden="true"></i></span>
                    <span class="nav-text">Categories</span>
                </a>
                <a class="nav-link <?= _adminActive(['all_sub_categories.php']) ?>" href="all_sub_categories.php">
                    <span class="nav-icon"><i class="bi bi-diagram-3" aria-hidden="true"></i></span>
                    <span class="nav-text">All Sub Categories</span>
                </a>
                <a class="nav-link <?= _adminActive(['products.php', 'products_forms.php', 'save_product.php']) ?>" href="products.php">
                    <span class="nav-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span>
                    <span class="nav-text">Products</span>
                </a>
                <a class="nav-link <?= _adminActive(['product_reviews.php', 'save_review.php']) ?>" href="product_reviews.php">
                    <span class="nav-icon"><i class="bi bi-star-half" aria-hidden="true"></i></span>
                    <span class="nav-text">Reviews</span>
                </a>
                <a class="nav-link <?= _adminActive(['product_faqs.php', 'save_faq.php']) ?>" href="product_faqs.php">
                    <span class="nav-icon"><i class="bi bi-question-circle" aria-hidden="true"></i></span>
                    <span class="nav-text">Product FAQs</span>
                </a>
                <a class="nav-link <?= _adminActive(['homepage.php']) ?>" href="homepage.php">
                    <span class="nav-icon"><i class="bi bi-house-door" aria-hidden="true"></i></span>
                    <span class="nav-text">Homepage</span>
                </a>
                <a class="nav-link <?= _adminActive(['slider_management.php']) ?>" href="slider_management.php">
                    <span class="nav-icon"><i class="bi bi-image" aria-hidden="true"></i></span>
                    <span class="nav-text">Slider Management</span>
                </a>
                <a class="nav-link <?= _adminActive(['settings.php']) ?>" href="settings.php">
                    <span class="nav-icon"><i class="bi bi-gear" aria-hidden="true"></i></span>
                    <span class="nav-text">Settings</span>
                </a>
                <a class="nav-link <?= _adminActive(['logout.php']) ?>" href="logout.php">
                    <span class="nav-icon"><i class="bi bi-box-arrow-right" aria-hidden="true"></i></span>
                    <span class="nav-text">Logout</span>
                </a>

                <div class="sidebar-user">
                    <img class="avatar-img avatar-md sidebar-user-avatar" src="./assets/images/avatar/avatar.jpg" alt="">
                    <strong><?= htmlspecialchars($_SESSION['adminName'] ?? 'Admin') ?></strong>
                    <small><?= htmlspecialchars($_SESSION['adminEmail'] ?? '') ?></small>
                </div>
            </nav>
        </aside>

        <div class="admin-main">
            <nav class="navbar admin-navbar navbar-expand bg-white">
                <div class="container-fluid px-3 px-lg-4">
                    <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-controls="adminSidebar" aria-expanded="true" aria-label="Toggle sidebar">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>

                    <form class="d-none d-md-flex ms-3 flex-grow-1" role="search">
                        <input class="form-control search-input" type="search" placeholder="Search" aria-label="Search">
                    </form>

                    <div class="navbar-actions ms-auto">
                        <button class="icon-button theme-toggle" type="button" data-theme-toggle aria-label="Switch color theme" title="Switch color theme">
                            <i class="bi bi-moon-stars" data-theme-icon aria-hidden="true"></i>
                        </button>
                        <div class="dropdown">
                            <button class="icon-button" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                                <span class="notification-dot"></span>
                                <i class="bi bi-bell" aria-hidden="true"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end notification-menu">
                                <div class="dropdown-header fw-bold text-body">Notifications</div>
                                <span class="dropdown-item text-muted small">No new notifications</span>
                            </div>
                        </div>

                        <div class="dropdown">
                            <button class="profile-button dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <img class="avatar-img avatar-sm" src="./assets/images/avatar/avatar.jpg" alt="Admin">
                                <span class="profile-name d-none d-sm-inline"><?= htmlspecialchars($_SESSION['adminName'] ?? 'Admin') ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sign out</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>