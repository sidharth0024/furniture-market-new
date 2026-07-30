<?php
session_start();
include_once("./includes/db_conn.php");

if (isset($_POST['login'])) {

  $email = trim($_POST['email']);
  $password = trim($_POST['password']);

  if (empty($email) || empty($password)) {
    echo "<script>
            alert('Email and Password are required.');
            window.location.href='index.php';
        </script>";
    exit;
  }

  $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = :email LIMIT 1");
  $stmt->bindParam(':email', $email, PDO::PARAM_STR);
  $stmt->execute();

  $admin = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($admin && $password == $admin['password']) {

    // Regenerate Session ID
    session_regenerate_id(true);

    $_SESSION['adminId']    = $admin['id'];
    $_SESSION['adminName']  = $admin['full_name'];
    $_SESSION['adminEmail'] = $admin['email'];

    echo "<script>
            alert('Admin logged in successfully');
            window.location.href='admin_dashboard.php';
        </script>";
    exit;
  } else {

    echo "<script>
            alert('Invalid Email or Password');
            window.location.href='index.php';
        </script>";
    exit;
  }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="adminHMD authentication page">
  <title>Login | adminHMD</title>

  <link rel="stylesheet" href="./assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="./assets/vendors/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="./assets/css/style.css">
</head>

<body class="auth-body">
  <button class="icon-button theme-toggle auth-theme-toggle" type="button" data-theme-toggle aria-label="Switch color theme" title="Switch color theme">
    <i class="bi bi-moon-stars" data-theme-icon aria-hidden="true"></i>
  </button>
  <main class="auth-page">
    <section class="auth-card">
      <form action="" method="post" class="needs-validation" novalidate>
        <div class="mb-4">
          <p class="eyebrow mb-1">Secure Access</p>
          <h1 class="h3 mb-1">Login</h1>
          <p class="text-muted mb-0">Sign in to your admin workspace.</p>
        </div>
        <div class="mb-3">
          <label class="form-label" for="loginEmail">Email address</label>
          <input class="form-control" id="loginEmail" type="email" name="email" required placeholder="Enter Email">
        </div>
        <div class="mb-3">
          <label class="form-label" for="loginPassword">Password</label>
          <input class="form-control" id="loginPassword" type="password" name="password" minlength="6" required placeholder="Enter Password">
        </div>
        <div class="form-check mb-4">
          <input class="form-check-input" type="checkbox" id="rememberMe">
          <label class="form-check-label" for="rememberMe">Remember me</label>
        </div>
        <button class="btn btn-primary w-100" type="submit" name="login"><i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> Sign In</button>
      </form>

    </section>
  </main>

  <script src="./assets/js/bootstrap.bundle.min.js"></script>
  <script src="./assets/js/main.js"></script>
</body>

</html>