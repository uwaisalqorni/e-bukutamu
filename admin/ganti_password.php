<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include '../db.php';

if (isset($_POST['ganti'])) {
    $username = $_SESSION['admin'];
    $old_pass = $_POST['old_pass'];
    $new_pass = $_POST['new_pass'];

    // Cek password lama
    $result = $conn->query("SELECT * FROM admin WHERE username='$username'");
    $row = $result->fetch_assoc();

    if (password_verify($old_pass, $row['password'])) {
        $hash_new = password_hash($new_pass, PASSWORD_DEFAULT);
        $conn->query("UPDATE admin SET password='$hash_new' WHERE username='$username'");
        $success = "✅ Password berhasil diubah!";
    } else {
        $error = "❌ Password lama salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ganti Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h3>🔒 Ganti Password</h3>
    <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form method="POST">
        <div class="mb-3">
            <label>Password Lama</label>
            <input type="password" name="old_pass" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Password Baru</label>
            <input type="password" name="new_pass" class="form-control" required>
        </div>
        <button type="submit" name="ganti" class="btn btn-primary">Simpan Perubahan</button>
    </form>
</div>
</body>
</html>
