<?php
include 'layout.php';
include '../db.php';

// Ambil username admin dari session
$username = $_SESSION['admin'];

if (isset($_POST['update'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Ambil data admin
    $result = $conn->query("SELECT * FROM admin WHERE username='$username'");
    $row = $result->fetch_assoc();

    // Validasi password lama
    if (!password_verify($current_password, $row['password'])) {
        $error = "❌ Password lama salah!";
    } elseif ($new_password !== $confirm_password) {
        $error = "❌ Password baru dan konfirmasi tidak cocok!";
    } else {
        // Hash password baru
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $conn->query("UPDATE admin SET password='$hashed_password' WHERE username='$username'");
        $success = "✅ Password berhasil diperbarui!";
    }
}
?>

<!-- Content Header -->
<div class="content-header">
  <div class="container-fluid">
    <h1 class="m-0">🔑 Ganti Password</h1>
  </div>
</div>

<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary">
      <div class="card-header"><h3 class="card-title">Form Ganti Password</h3></div>
      <div class="card-body">
        <?php if (isset($error)): ?>
          <div class="alert alert-danger"><?= $error ?></div>
        <?php elseif (isset($success)): ?>
          <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <form method="POST">
          <div class="mb-3">
            <label>Password Lama</label>
            <input type="password" name="current_password" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Password Baru</label>
            <input type="password" name="new_password" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Konfirmasi Password Baru</label>
            <input type="password" name="confirm_password" class="form-control" required>
          </div>
          <button type="submit" name="update" class="btn btn-primary">💾 Simpan Perubahan</button>
          <a href="dashboard.php" class="btn btn-secondary">⬅️ Kembali ke Dashboard</a>
        </form>
      </div>
    </div>
  </div>
</section>

<?php include 'footer-scripts.php'; ?>
