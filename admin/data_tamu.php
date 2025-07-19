<?php
include '../db.php';

$result = $conn->query("
    SELECT buku_tamu.*, lokasi.nama_lokasi, acara.nama_acara
    FROM buku_tamu
    JOIN lokasi ON buku_tamu.id_lokasi = lokasi.id
    JOIN acara ON buku_tamu.id_acara = acara.id
    ORDER BY waktu_masuk DESC
");

if ($result->num_rows > 0) {
    $no = 1;
    while($row = $result->fetch_assoc()) {
        // Status check-in/check-out
        if ($row['waktu_keluar']) {
            $status = "<span class='badge bg-success'>✅ Selesai</span>";
            $aksi = "-";
        } else {
            $status = "<span class='badge bg-warning text-dark'>⏳ Masih di lokasi</span>";
            $aksi = "<a href='manual_checkout.php?id=".$row['id']."' class='btn btn-sm btn-danger' onclick='return confirm(\"Yakin Check-Out tamu ini?\")'>Check-Out</a>";
        }
        echo "<tr>
            <td>".$no++."</td>
            <td>".$row['nama']."</td>
            <td>".$row['nik']."</td>
            <td>".$row['nama_acara']."</td>
            <td>".$row['nama_lokasi']."</td>
            <td>".$row['waktu_masuk']."</td>
            <td>".($row['waktu_keluar'] ?? "-")."</td>
            <td>$status</td>
            <td>$aksi</td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='9' class='text-center'>Belum ada data tamu</td></tr>";
}
?>
