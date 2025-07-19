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
    <title>Admin Panel - Buku Tamu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { background-color: #f8f9fa; }
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">📋 Buku Tamu</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarAdmin">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">📊 Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="lokasi.php">📍 Lokasi</a></li>
                <li class="nav-item"><a class="nav-link" href="acara.php">📅 Acara</a></li>
                <li class="nav-item"><a class="nav-link" href="../index.php">↩️ Buku Tamu</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">🚪 Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Content -->
<div class="container mt-4">
    <h2>📊 Dashboard Tamu Hadir</h2>
    <p class="text-muted">Filter data tamu & auto-refresh setiap 5 detik</p>

    <!-- Filter Form -->
    <form id="filterForm" class="row g-3 mb-3">
        <div class="col-12 col-md-4">
            <input type="date" id="filterTanggal" name="tanggal" class="form-control" value="<?php echo date('Y-m-d'); ?>">
        </div>
        <div class="col-12 col-md-5">
            <select id="filterAcara" name="acara" class="form-select">
                <option value="">📅 Semua Acara</option>
                <?php
                $acaraResult = $conn->query("SELECT * FROM acara ORDER BY nama_acara ASC");
                while ($row = $acaraResult->fetch_assoc()) {
                    echo "<option value='".$row['id']."'>".$row['nama_acara']."</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-12 col-md-3">
            <button type="button" onclick="loadData()" class="btn btn-primary w-100">🔍 Filter</button>
        </div>
        <div>
            <a href="export_excel.php?tanggal=<?php echo date('Y-m-d'); ?>" class="btn btn-success btn-sm">📥 Export Excel</a>
            <a href="export_pdf.php?tanggal=<?php echo date('Y-m-d'); ?>" class="btn btn-danger btn-sm">📥 Export PDF</a>
        </div>
    </div>
    </form>
    
    <!-- Tabel Data -->
    <div class="card shadow">
        <div class="card-body table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Auto-load data dengan filter
    function loadData() {
        let tanggal = $("#filterTanggal").val();
        let acara = $("#filterAcara").val();

        $("#data-tamu").load("data_tamu.php?tanggal=" + tanggal + "&acara=" + acara);
    }

    // Load pertama kali
    loadData();

    // Auto-refresh tiap 5 detik
    setInterval(loadData, 5000);
</script>
</body>
</html>
