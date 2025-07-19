<?php
include '../db.php';
require '../vendor/autoload.php'; // Autoload composer

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Tambahkan lokasi
if (isset($_POST['add'])) {
    $nama_lokasi = $conn->real_escape_string($_POST['nama_lokasi']);
    
    // Simpan lokasi sementara untuk dapatkan id
    $conn->query("INSERT INTO lokasi (nama_lokasi, link_qr) VALUES ('$nama_lokasi', '')");
    $id_lokasi = $conn->insert_id;

    // Generate link QR pakai id_lokasi
    $link_qr = "http://192.168.0.191/bukutamu/index.php?id_lokasi=" . $id_lokasi;

    // Update link_qr di tabel
    $conn->query("UPDATE lokasi SET link_qr='$link_qr' WHERE id=$id_lokasi");

    // Generate QR Code
    $qr = QrCode::create($link_qr);
    $writer = new PngWriter();
    $result = $writer->write($qr);

    // Simpan QR Code dengan nama id_lokasi.png
    file_put_contents("../qrcode/" . $id_lokasi . ".png", $result->getString());

    header("Location: lokasi.php");
    exit();
}

// Hapus lokasi
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $lokasi = $conn->query("SELECT * FROM lokasi WHERE id=$id")->fetch_assoc();

    if ($lokasi) {
        @unlink("../qrcode/" . $id . ".png");
        $conn->query("DELETE FROM lokasi WHERE id=$id");
    }

    header("Location: lokasi.php");
    exit();
}

// Ambil semua lokasi
$lokasi = $conn->query("SELECT * FROM lokasi ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manajemen Lokasi & QR Code</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2>📌 Manajemen Lokasi & QR Code</h2>
    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-6">
            <input type="text" name="nama_lokasi" class="form-control" placeholder="Nama Lokasi" required>
        </div>
        <div class="col-md-2">
            <button type="submit" name="add" class="btn btn-success w-100">Tambah</button>
            <!-- <a href="dashboard.php" class="btn btn-secondary">Kembali</a> -->
        </div>
        <div class="col-md-2">
            <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
    <div class="card shadow">
        <div class="card-body">
            <h5 class="card-title">Daftar Lokasi</h5>
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama Lokasi</th>
                        <th>QR Code</th>
                        <th>Link</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while($row = $lokasi->fetch_assoc()) {
                        $link = $row['link_qr'];
                        echo "<tr>
                            <td>".$no++."</td>
                            <td>".$row['nama_lokasi']."</td>
                            <td><img src='../qrcode/".$row['id'].".png' width='80'></td>
                            <td>
                                <input type='text' class='form-control form-control-sm mb-1' value='$link' id='link".$row['id']."' readonly>
                                <button class='btn btn-sm btn-primary' onclick='copyLink(".$row['id'].")'>Copy Link</button>
                                <a href='https://wa.me/?text=".urlencode($link)."' target='_blank' class='btn btn-sm btn-success mt-1'>Share WhatsApp</a>
                            </td>
                            <td>
                                <a href='../qrcode/".$row['id'].".png' download class='btn btn-sm btn-info'>Download QR</a>
                                <a href='?delete=".$row['id']."' onclick='return confirm(\"Hapus lokasi?\")' class='btn btn-danger btn-sm mt-1'>Hapus</a>
                            </td>
                        </tr>";
                    }
                    ?>
                </tbody>

            </table>
        </div>
    </div>
</div>
</body>
    <script>
        function copyLink(id) {
            var copyText = document.getElementById("link" + id);
            copyText.select();
            copyText.setSelectionRange(0, 99999); // For mobile devices
            document.execCommand("copy");
            alert("✅ Link berhasil disalin: " + copyText.value);
        }
    </script>
</html>