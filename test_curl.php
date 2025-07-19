<?php
$nik = "180051";
$api_url = "http://192.168.10.51/ci3-api-bot/index.php/api/pegawai?nik=" . urlencode($nik);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10); // timeout 10 detik

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo "❌ CURL Error: " . curl_error($ch);
} else {
    echo "<pre>";
    print_r(json_decode($response, true));
    echo "</pre>";
}

curl_close($ch);
?>
