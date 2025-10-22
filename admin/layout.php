<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Panel - Buku Tamu</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Google Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- AdminLTE CSS -->
  <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="dashboard.php" class="nav-link">📊 Dashboard</a>
      </li>
    </ul>
  </nav>
  <!-- Sidebar -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="dashboard.php" class="brand-link">
      <!-- <i class="fas fa-book"></i> -->
      <img src="/bukutamu/assets/images/logo.png"
          alt="Logo Rumah Sakit"
          class="img-circle elevation-2"
          style="height:50px;width:50px;object-fit:contain;margin-bottom:8px;background:#fff;">
      <span class="brand-text font-weight-light">Buku Tamu Admin</span>
    </a>
    <div class="sidebar">
      <nav>
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview">
          <li class="nav-item">
            <a href="dashboard.php" class="nav-link">
              <i class="nav-icon fas fa-home"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="lokasi.php" class="nav-link">
              <i class="nav-icon fas fa-map-marker-alt"></i>
              <p>Manajemen Lokasi</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="acara.php" class="nav-link">
              <i class="nav-icon fas fa-calendar-alt"></i>
              <p>Manajemen Acara</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="ganti_password.php" class="nav-link">
              <i class="nav-icon fas fa-key"></i>
              <p>Ganti Password</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="logout.php" class="nav-link">
              <i class="nav-icon fas fa-sign-out-alt"></i>
              <p>Logout</p>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </aside>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
