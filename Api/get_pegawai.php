<?php
if (isset($_POST['nik'])) {
    $nik = $_POST['nik'];
    $api_url = "http://192.168.10.51/ci3-api-bot/index.php/api/pegawai?nik=" . urlencode($nik);

    // Kirim request GET ke API SIMRS
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // timeout 10 detik

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo json_encode([
            'status' => 'error',
            'message' => '❌ Tidak bisa menghubungi API SIMRS: '.curl_error($ch)
        ]);
        exit();
    }

    curl_close($ch);

    // Kirim langsung response API ke frontend
    header('Content-Type: application/json');
    echo $response;
    exit();
}
?>