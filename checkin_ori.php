<?php
include 'db.php';

$id_lokasi = intval($_POST['id_lokasi']);
$nama      = $_POST['nama'];
$nik       = $_POST['nik'];
$id_acara  = intval($_POST['id_acara']);

$sql = "INSERT INTO buku_tamu (id_lokasi, id_acara, nama, nik) 
        VALUES ('$id_lokasi', '$id_acara', '$nama', '$nik')";

if ($conn->query($sql) === TRUE) {
    echo "<script>alert('Check-In berhasil!'); window.location='index.php?id_lokasi=$id_lokasi';</script>";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>