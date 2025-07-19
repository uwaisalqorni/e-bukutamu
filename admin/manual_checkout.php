<?php
include '../db.php';

$id = intval($_GET['id']);
$conn->query("UPDATE buku_tamu SET waktu_keluar = NOW() WHERE id=$id AND waktu_keluar IS NULL");

header("Location: dashboard.php");
exit();
?>