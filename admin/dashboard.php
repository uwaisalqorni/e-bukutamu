<?php include '../db.php'; ?>
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
        .sidebar {
            height: 100vh;
            background-color: #343a40;
            color: white;
            padding: 1rem;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            margin: 0.5rem 0;
        }
        .sidebar a:hover {
            background-color: #495057;
            border-radius: 5px;
            padding-left: 5px;
        }
    </style>
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar">
        <h4>📋 Admin Buku Tamu</h4>
        <hr style="border-color: white;">
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="lokasi.php">📍 Manajemen Lokasi</a>
        <a href="acara.php">📅 Manajemen Acara</a>
        <a href="../index.php">↩️ Kembali ke Buku Tamu</a>
    </div>

    <!-- Content -->
    <div class="container mt-4">
        <h2>📊 Dashboard Tamu Hadir</h2>
        <p class="text-muted">Data akan diperbarui otomatis setiap 5 detik</p>

        <div class="card shadow">
            <div class="card-body">
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
                        <!-- Isi tabel akan di-load oleh AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-load data tamu setiap 5 detik
function loadData() {
    $("#data-tamu").load("data_tamu.php");
}

// Panggil pertama kali
loadData();

// Auto-refresh tiap 5 detik
setInterval(loadData, 5000);
</script>
</body>
</html>
