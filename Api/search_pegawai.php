<?php
$keyword = isset($_GET['term']) ? $_GET['term'] : '';
$data = [];

if (!empty($keyword)) {
    $api_url = "http://192.168.10.51/ci3-api-bot/index.php/api/pegawai";

    // Ambil semua data pegawai dari API SIMRS
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $result = json_decode($response, true);

        if ($result['status'] == 'success') {
            foreach ($result['data'] as $pegawai) {
                if (
                    stripos($pegawai['nik'], $keyword) !== false || 
                    stripos($pegawai['nama'], $keyword) !== false
                ) {
                    $data[] = [
                        'label' => $pegawai['nik'] . " - " . $pegawai['nama'],
                        'value' => $pegawai['nik'], // yang dimasukkan ke input
                        'nama' => $pegawai['nama'] // untuk input Nama
                    ];
                }
            }
        }
    }
}

echo json_encode($data);
?>