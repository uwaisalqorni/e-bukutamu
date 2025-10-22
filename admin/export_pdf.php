<?php
require '../vendor/autoload.php';
include '../db.php';

// ========== Ambil & Normalisasi Filter ==========
$start = isset($_GET['start']) ? $_GET['start'] : '';
$end   = isset($_GET['end'])   ? $_GET['end']   : '';
$tgl   = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';
$acara = isset($_GET['acara']) ? $_GET['acara'] : '';

// Backward-compat: jika hanya ?tanggal= ada
if ($start === '' && $end === '' && $tgl !== '') { $start = $tgl; $end = $tgl; }
// Lengkapi salah satu yang kosong
if ($start === '' && $end !== '') $start = $end;
if ($end === '' && $start !== '') $end = $start;
// Default: hari ini
if ($start === '' && $end === '') { $start = date('Y-m-d'); $end = $start; }
// Jika kebalik, tukar
if (strtotime($start) > strtotime($end)) { $tmp = $start; $start = $end; $end = $tmp; }

// Label periode
$periodeLabel = ($start === $end)
  ? date('d/m/Y', strtotime($start))
  : date('d/m/Y', strtotime($start)) . ' s.d. ' . date('d/m/Y', strtotime($end));

// Label acara (default)
$acaraLabel = 'Semua Acara';

// ========== Query Data (Prepared) ==========
$sql = "SELECT bt.*, l.nama_lokasi, a.nama_acara
        FROM buku_tamu bt
        JOIN lokasi l ON bt.id_lokasi = l.id
        JOIN acara  a ON bt.id_acara  = a.id
        WHERE DATE(bt.waktu_masuk) BETWEEN ? AND ?";

$params = [$start, $end];
$types  = 'ss';

// Filter acara jika ada (hanya angka)
if ($acara !== '' && ctype_digit((string)$acara)) {
  $sql .= " AND bt.id_acara = ?";
  $params[] = (int)$acara;
  $types   .= 'i';

  // Ambil nama acara untuk header
  if ($stmtA = $conn->prepare("SELECT nama_acara FROM acara WHERE id = ?")) {
    $stmtA->bind_param('i', $params[count($params)-1]);
    $stmtA->execute();
    $resA = $stmtA->get_result();
    if ($rowA = $resA->fetch_assoc()) { $acaraLabel = $rowA['nama_acara']; }
    $stmtA->close();
  }
}

$sql .= " ORDER BY bt.waktu_masuk DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
  // Jika gagal prepare, beri pesan singkat
  die("Terjadi kesalahan saat menyiapkan data PDF.");
}
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// ========== Siapkan TCPDF ==========
if (ob_get_length()) { @ob_end_clean(); } // bersihkan buffer agar header bersih

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetTitle('Laporan Buku Tamu');
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 15);
$pdf->AddPage();

// Identitas instansi (silakan sesuaikan)
$logo_path        = '../assets/images/logo.png'; // opsional
$nama_instansi    = "RUMAH SAKIT ISLAM GONDANGLEGI";
$alamat_instansi  = "Jl. Hayam Wuruk No.66, Gondanglegi, Malang";

// Header (logo + instansi)
$headerHtml  = '<table width="100%"><tr>';
$headerHtml .= '<td width="15%" align="left">';
if (file_exists($logo_path)) {
  // TCPDF mendukung <img> di HTML
  $headerHtml .= '<img src="'.$logo_path.'" height="45">';
}
$headerHtml .= '</td>';
$headerHtml .= '<td width="70%" align="center" style="line-height:1.4">';
$headerHtml .= '<span style="font-weight:bold; font-size:16px;">'.htmlspecialchars($nama_instansi).'</span><br>';
$headerHtml .= '<span style="font-size:10px;">'.htmlspecialchars($alamat_instansi).'</span>';
$headerHtml .= '</td>';
$headerHtml .= '<td width="15%"></td>';
$headerHtml .= '</tr></table>';

$pdf->SetFont('helvetica', '', 11);
$pdf->writeHTML($headerHtml, true, false, true, false, '');
$pdf->Ln(2);

// Judul & subjudul
$pdf->SetFont('helvetica', 'B', 13);
$pdf->Cell(0, 8, 'LAPORAN BUKU TAMU', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 6, "Periode: ".$periodeLabel, 0, 1, 'C');
$pdf->Cell(0, 6, "Acara: ".htmlspecialchars($acaraLabel), 0, 1, 'C');
$pdf->Ln(3);

// ========== Tabel Data ==========
$pdf->SetFont('helvetica', '', 9);

$table  = '<style>
  table { border-collapse: collapse; width: 100%; }
  th, td { border: 0.6px solid #000; padding: 3px; line-height: 1.25; }
  th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
  td.center { text-align:center; }
  td.top { vertical-align: top; }
  td.nowrap { white-space: nowrap; }
</style>';

// Lebar kolom (total 100%):
// No 4% | Nama 22% | NIK 10% | Acara 26% | Lokasi 15% | Masuk 9% | Keluar 9% | Status 5%
$table .= '
<table>
  <thead>
    <tr>
      <th width="4%">No</th>
      <th width="22%">Nama</th>
      <th width="10%">NIK</th>
      <th width="26%">Acara</th>
      <th width="15%">Lokasi</th>
      <th width="9%">Masuk</th>
      <th width="9%">Keluar</th>
      <th width="5%">Status</th>
    </tr>
  </thead>
  <tbody>';

$no = 1;
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $nama        = htmlspecialchars($row['nama']);
    $nik         = htmlspecialchars($row['nik']);
    $nama_acara  = nl2br(htmlspecialchars($row['nama_acara']));
    $nama_lokasi = nl2br(htmlspecialchars($row['nama_lokasi']));

    // format tanggal sesuai contoh (YYYY-mm-dd HH:ii:ss) dan tidak terpotong (nowrap)
    $wmasuk  = $row['waktu_masuk']  ? date('Y-m-d H:i:s', strtotime($row['waktu_masuk']))  : '-';
    // pakai titik "." untuk kosong seperti di contohmu
    $wkeluar = $row['waktu_keluar'] ? date('Y-m-d H:i:s', strtotime($row['waktu_keluar'])) : '.';

    $status = $row['waktu_keluar'] ? 'Selesai' : 'Masih di lokasi';

    $table .= '
      <tr>
        <td class="center top">'.($no++).'</td>
        <td class="top">'.$nama.'</td>
        <td class="center top">'.$nik.'</td>
        <td class="top">'.$nama_acara.'</td>
        <td class="top">'.$nama_lokasi.'</td>
        <td class="center nowrap top">'.$wmasuk.'</td>
        <td class="center nowrap top">'.$wkeluar.'</td>
        <td class="center top">'.$status.'</td>
      </tr>';
  }
} else {
  $table .= '<tr><td colspan="8" class="center" style="font-style:italic;">Data tidak ditemukan untuk filter tersebut.</td></tr>';
}

$table .= '</tbody></table>';

// render tabel
$pdf->writeHTML($table, true, false, true, false, '');


// ========== Output ==========
if (ob_get_length()) { @ob_end_clean(); } // pastikan buffer bersih sebelum kirim
$namaFile = "laporan-buku-tamu_{$start}_sd_{$end}".($acara!=='' ? "_acara-{$acara}" : "").".pdf";
$pdf->Output($namaFile, 'D'); // 'D' = force download, ganti 'I' jika ingin inline preview
exit;