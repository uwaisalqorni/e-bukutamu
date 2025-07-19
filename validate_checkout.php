<?php
include 'db.php';

if (isset($_POST['nik']) && isset($_POST['id_lokasi'])) {
    $nik = $conn->real_escape_string($_POST['nik']);
    $id_lokasi = intval($_POST['id_lokasi']);

    $sql = "SELECT * FROM buku_tamu 
            WHERE nik='$nik' AND id_lokasi=$id_lokasi AND waktu_keluar IS NULL
            ORDER BY waktu_masuk DESC LIMIT 1";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo json_encode([
            'status' => 'success',
            'message' => 'NIK ditemukan. Siap Check-Out.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'NIK tidak ditemukan atau sudah Check-Out.'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Data tidak lengkap.'
    ]);
}
?>
