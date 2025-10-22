<?php 
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include '../db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - Buku Tamu</title>
    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="assets/plugins/fontawesome-free/css/all.min.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="assets/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="dashboard.php" class="nav-link active">📊 Dashboard</a>
            </li>
        </ul>
    </nav>
    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="dashboard.php" class="brand-link">
            <i class="fas fa-book"></i>
            <span class="brand-text font-weight-light">Buku Tamu</span>
        </a>
        <div class="sidebar">
            <nav>
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link active">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
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
    <!-- Content -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <h1 class="m-0">📊 Dashboard Tamu Hadir</h1>
                <p class="text-muted">Filter data tamu & auto-refresh setiap 5 detik</p>
            </div>
        </div>
        <div class="content">
            <div class="container-fluid">
                <!-- Filter Form -->
                <div class="card card-primary card-outline">
                    <div class="card-body">
                        <form id="filterForm" class="row">
                            <div class="col-md-3">
                                <input type="date" id="filterTanggal" name="tanggal" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-4">
                                <select id="filterAcara" name="acara" class="form-control">
                                    <option value="">📅 Semua Acara</option>
                                    <?php
                                    $acaraResult = $conn->query("SELECT * FROM acara ORDER BY nama_acara ASC");
                                    while ($row = $acaraResult->fetch_assoc()) {
                                        echo "<option value='".$row['id']."'>".$row['nama_acara']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="button" onclick="loadData()" class="btn btn-primary btn-block">🔍 Filter</button>
                            </div>
                            <div class="col-md-2 text-right">
                                <a href="export_excel.php?tanggal=<?php echo date('Y-m-d'); ?>" class="btn btn-success btn-sm">📥 Export Excel</a>
                                <a href="export_pdf.php?tanggal=<?php echo date('Y-m-d'); ?>" class="btn btn-danger btn-sm">📥 Export PDF</a>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Tabel Data -->
                <div class="card">
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover text-nowrap">
                            <thead class="bg-primary">
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>NIK</th>
                                    <th>Acara</th>
                                    <th>Lokasi</th>
                                    <th>Masuk</th>
                                    <th>Keluar</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="data-tamu">
                                <!-- Data dimuat via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="assets/plugins/jquery/jquery.min.js"></script>
<script src="assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<script src="assets/dist/js/adminlte.min.js"></script>
<script>
    function loadData() {
        let tanggal = $("#filterTanggal").val();
        let acara = $("#filterAcara").val();
        $("#data-tamu").load("data_tamu.php?tanggal=" + tanggal + "&acara=" + acara);
    }
    loadData();
    setInterval(loadData, 5000);
</script>
</body>
</html>