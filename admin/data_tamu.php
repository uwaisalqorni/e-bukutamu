<?php
include '../db.php';

/*
  Parameter yang diterima:
  - start: YYYY-mm-dd (opsional)
  - end  : YYYY-mm-dd (opsional)
  - acara: id acara (opsional)
  Backward-compat:
  - tanggal: YYYY-mm-dd (jika ada & start/end kosong, pakai tanggal sebagai start=end)
*/

// Ambil parameter
$start  = isset($_GET['start'])  ? $_GET['start']  : '';
$end    = isset($_GET['end'])    ? $_GET['end']    : '';
$tgl    = isset($_GET['tanggal'])? $_GET['tanggal']: '';
$acara  = isset($_GET['acara'])  ? $_GET['acara']  : '';

// Fallback logika tanggal
if ($start === '' && $end === '' && $tgl !== '') {
  $start = $tgl;
  $end   = $tgl;
}
if ($start === '' && $end !== '') $start = $end;
if ($end === '' && $start !== '') $end = $start;
if ($start === '' && $end === '') {
  $start = date('Y-m-d');
  $end   = $start;
}

// Jaga-jaga kalau start > end, ditukar
if (strtotime($start) > strtotime($end)) {
  $tmp = $start; $start = $end; $end = $tmp;
}

// Siapkan query dasar
$sql = "
SELECT
  bt.*,
  l.nama_lokasi,
  a.nama_acara
FROM buku_tamu bt
LEFT JOIN lokasi l ON bt.id_lokasi = l.id
LEFT JOIN acara  a ON bt.id_acara  = a.id
WHERE DATE(bt.waktu_masuk) BETWEEN ? AND ?
";

// Build param
$params = [$start, $end];
$types  = "ss";

// Filter acara (jika ada & numeric)
if ($acara !== '' && ctype_digit((string)$acara)) {
  $sql   .= " AND bt.id_acara = ? ";
  $params[] = (int)$acara;
  $types  .= "i";
}

// Urutkan
$sql .= " ORDER BY bt.waktu_masuk DESC";

// Eksekusi prepared statement
$stmt = $conn->prepare($sql);
if (!$stmt) {
  http_response_code(500);
  echo "<tr><td colspan='9' class='text-center'>Terjadi kesalahan: gagal mempersiapkan query.</td></tr>";
  exit;
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
  $no = 1;
  while ($row = $result->fetch_assoc()) {

    $nama_acara  = $row['nama_acara']  ?? '— Acara (terhapus) —';
$nama_lokasi = $row['nama_lokasi'] ?? '— Lokasi (terhapus) —';

// status & aksi (tetap seperti sebelumnya)
if (!empty($row['waktu_keluar'])) {
  $status = "<span class='badge bg-success'>✅ Selesai</span>";
  $aksi   = "-";
} else {
  $status = "<span class='badge bg-warning text-dark'>⏳ Masih di lokasi</span>";
  $aksi   = "<a href='manual_checkout.php?id=".(int)$row['id']."' class='btn btn-sm btn-danger' onclick='return confirm(\"Yakin Check-Out tamu ini?\")'>Check-Out</a>";
}

// siapkan TTD
$ttdCell = '-';
if (!empty($row['ttd_path'])) {
  $src = '../' . ltrim($row['ttd_path'], '/'); // path relatif dari folder admin
  $thumb = "<img src='{$src}' alt='TTD' style='height:40px;max-width:100px;border:1px solid #ddd;border-radius:4px;background:#fff'>";
  $ttdCell = "<a href='javascript:void(0)' onclick=\"previewTTD('{$src}')\" title='Klik untuk perbesar'>{$thumb}</a>";
}

echo "<tr>
  <td>".($no++)."</td>
  <td>".htmlspecialchars($row['nama'])."</td>
  <td>".htmlspecialchars($row['nik'])."</td>
  <td>".htmlspecialchars($nama_acara)."</td>
  <td>".htmlspecialchars($nama_lokasi)."</td>
  <td>".htmlspecialchars($row['waktu_masuk'])."</td>
  <td>".(!empty($row['waktu_keluar']) ? htmlspecialchars($row['waktu_keluar']) : '-')."</td>
  <td>{$ttdCell}</td>  <!-- ⬅️ kolom TTD baru -->
  <td>$status</td>
  <td>$aksi</td>
</tr>";

  }
} else {
    echo "<tr><td colspan='10' class='text-center'>⚠️ Data tidak ditemukan</td></tr>";
}

$stmt->close();
$conn->close();
