<?php
// Autoload composer untuk Endroid QR Code
require '../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Lokasi link QR yang akan dibuat
$id_lokasi = isset($_GET['id_lokasi']) ? intval($_GET['id_lokasi']) : 0;
if ($id_lokasi == 0) {
    die("❌ ID Lokasi tidak valid!");
}

// URL tujuan QR Code (ubah domain ke domain kamu)
$link = "http://192.168.10.51/bukutamu/index.php?id_lokasi=" . $id_lokasi;

// Buat QR Code
$qr = QrCode::create($link);
$writer = new PngWriter();
$result = $writer->write($qr);

// Simpan QR Code ke file
$file_name = "../qrcode/lokasi_" . $id_lokasi . ".png";
file_put_contents($file_name, $result->getString());

// Tampilkan hasil
echo "<h3>✅ QR Code berhasil dibuat!</h3>";
echo "<p>Link: <a href='$link'>$link</a></p>";
echo "<img src='$file_name' alt='QR Code Lokasi' style='width:200px;'>";
?>
