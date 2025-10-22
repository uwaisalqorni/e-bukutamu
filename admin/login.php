<?php session_start(); 
    include '../db.php'; 
    if (isset($_POST['login'])) {
        $username = $conn->real_escape_string($_POST['username']);
        $password = $_POST['password']; // plaintext user input
    
        // Ambil data admin
        $result = $conn->query("SELECT * FROM admin WHERE username='$username'");
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            // Verifikasi password
            if (password_verify($password, $row['password'])) {
                $_SESSION['admin'] = $username;
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "❌ Password salah!";
            }
        } else {
            $error = "❌ Username tidak ditemukan!";
        }// proses login
    }
    // jika sudah login → redirect
?>
<!DOCTYPE html>
<html>
    <head>
  <!-- Head sama seperti AdminLTE auth page -->
  <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <img src="/bukutamu/assets/images/logo.png"
          alt="Logo Rumah Sakit"
          class="img-circle elevation-2"
          style="height:80px;width:80px;object-fit:contain;margin-bottom:8px;background:#fff;">
          <div class = "font-size:50px "> <strong><b><h2>Buku Tamu Rsig </h2></strong></div>
    </div>
    <div class="card-body">
      <p class="login-box-msg">🔐 Login Admin</p>
      <?php if(isset($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
      <form method="POST">
        <div class="input-group mb-3">
          <input type="text" name="username" class="form-control" placeholder="Username" required>
          <div class="input-group-append"><div class="input-group-text"><span class="fas fa-user"></span></div></div>
        </div>
        <div class="input-group mb-3">
          <input type="password" name="password" class="form-control" placeholder="Password" required>
          <div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div>
        </div>
        <div class="row">
          <div class="col-8"><!-- kosong --></div>
          <div class="col-4"><button type="submit" name="login" class="btn btn-primary btn-block">Masuk</button></div>
        </div>
      </form>
    </div>
  </div>
</div>
<script src="assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/dist/js/adminlte.min.js"></script>
</body>
</html>
