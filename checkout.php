<?php
include 'db.php';

$id_lokasi = intval($_POST['id_lokasi']);
$nik = $conn->real_escape_string($_POST['nik_out']);

$sql = "UPDATE buku_tamu 
        SET waktu_keluar = NOW() 
        WHERE nik='$nik' AND id_lokasi=$id_lokasi AND waktu_keluar IS NULL
        ORDER BY waktu_masuk DESC LIMIT 1";

if ($conn->query($sql) === TRUE) {
    if ($conn->affected_rows > 0) {
        echo "<script>alert('✅ Check-Out berhasil!'); window.location='index.php?id_lokasi=$id_lokasi';</script>";
    } else {
        echo "<script>alert('⚠️ NIK tidak ditemukan atau sudah Check-Out!'); window.location='index.php?id_lokasi=$id_lokasi';</script>";
    }
} else {
    echo "❌ Error: " . $conn->error;
}
?>