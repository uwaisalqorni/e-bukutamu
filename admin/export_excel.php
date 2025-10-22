<?php
require '../vendor/autoload.php';
include '../db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

/*
  Filter yang didukung:
  - start (YYYY-mm-dd)
  - end   (YYYY-mm-dd)
  - acara (id acara)
  Backward-compat:
  - tanggal (jika ada & start/end kosong, maka start=end=tanggal)
*/

// Ambil parameter
$start = isset($_GET['start']) ? $_GET['start'] : '';
$end   = isset($_GET['end'])   ? $_GET['end']   : '';
$tgl   = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';
$acara = isset($_GET['acara']) ? $_GET['acara'] : '';

// Fallback tanggal lama
if ($start === '' && $end === '' && $tgl !== '') {
  $start = $tgl; $end = $tgl;
}
if ($start === '' && $end !== '') $start = $end;
if ($end === '' && $start !== '') $end = $start;
if ($start === '' && $end === '') {
  $start = date('Y-m-d'); $end = $start;
}

// Tukar jika kebalik
if (strtotime($start) > strtotime($end)) { $tmp=$start; $start=$end; $end=$tmp; }

// Siapkan label periode & acara (untuk header laporan)
$periodeLabel = $start === $end
  ? date('d/m/Y', strtotime($start))
  : date('d/m/Y', strtotime($start)) . ' s.d. ' . date('d/m/Y', strtotime($end));

// $acaraLabel = 'Semua Acara';
$acaraLabel = ($rowA['nama_acara'] ?? 'Acara (terhapus)');


// Query data (prepared statement)
$sql = "SELECT bt.*, l.nama_lokasi, a.nama_acara
        FROM buku_tamu bt
        LEFT JOIN lokasi l ON bt.id_lokasi = l.id
        LEFT JOIN acara  a ON bt.id_acara  = a.id
        WHERE DATE(bt.waktu_masuk) BETWEEN ? AND ?";


$params = [$start, $end];
$types  = 'ss';

if ($acara !== '' && ctype_digit((string)$acara)) {
  $sql .= " AND bt.id_acara = ?";
  $params[] = (int)$acara;
  $types   .= 'i';

  // Ambil nama acara untuk label
  $stmtA = $conn->prepare("SELECT nama_acara FROM acara WHERE id = ?");
  if ($stmtA) {
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
  die("Gagal mempersiapkan query export.");
}
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// ===== Spreadsheet =====
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Report Buku Tamu');

// Logo (opsional)
if (file_exists('../assets/images/logo.png')) {
  $logo = new Drawing();
  $logo->setName('Logo');
  $logo->setDescription('Logo');
  $logo->setPath('../assets/images/logo.png');
  $logo->setHeight(50);
  $logo->setCoordinates('A1');
  $logo->setWorksheet($sheet);
}

// Identitas instansi (silakan sesuaikan)
$nama_instansi   = "RUMAH SAKIT ISLAM GONDANGLEGI";
$alamat_instansi = "Jl. Hayam Wuruk No.66, Gondanglegi, Malang";

// Header instansi
$sheet->mergeCells('B1:H1');
$sheet->setCellValue('B1', $nama_instansi);
$sheet->getStyle('B1')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('B1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->mergeCells('B2:H2');
$sheet->setCellValue('B2', $alamat_instansi);
$sheet->getStyle('B2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Judul + Periode + Acara
$sheet->mergeCells('A4:H4');
$sheet->setCellValue('A4', 'LAPORAN BUKU TAMU');
$sheet->getStyle('A4')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->mergeCells('A5:H5');
$sheet->setCellValue('A5', "Periode: $periodeLabel");
$sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->mergeCells('A6:H6');
$sheet->setCellValue('A6', "Acara: $acaraLabel");
$sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Header Tabel
$startRow = 8;
$headers = ['No', 'Nama', 'NIK', 'Acara', 'Lokasi', 'Masuk', 'Keluar', 'Status'];
$sheet->fromArray($headers, NULL, 'A'.$startRow);

// Isi Data
$rowNum = $startRow + 1;
$no = 1;

if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $status = $row['waktu_keluar'] ? "Selesai" : "Masih di lokasi";
    $sheet->fromArray([
        $no++,
        $row['nama'],
        $row['nik'],
        ($row['nama_acara']  ?? 'Acara (terhapus)'),
        ($row['nama_lokasi'] ?? 'Lokasi (terhapus)'),
        $row['waktu_masuk'],
        $row['waktu_keluar'] ?: '-',
        $row['waktu_keluar'] ? 'Selesai' : 'Masih di lokasi'
    ], NULL, "A$rowNum");
    
    $rowNum++;
  }
} else {
  // Tampilkan 1 baris info jika kosong
  $sheet->mergeCells("A".($startRow+1).":H".($startRow+1));
  $sheet->setCellValue("A".($startRow+1), "Data tidak ditemukan untuk filter tersebut.");
  $sheet->getStyle("A".($startRow+1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
  $rowNum = $startRow + 2;
}

// Styling header & border tabel
$sheet->getStyle("A{$startRow}:H{$startRow}")->getFont()->setBold(true);
$sheet->getStyle("A{$startRow}:H{$startRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A{$startRow}:H".($rowNum-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

// Auto width
foreach (range('A', 'H') as $col) {
  $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Output
$namaFile = "report-buku-tamu_{$start}_sd_{$end}".($acara!=='' ? "_acara-{$acara}" : "").".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$namaFile.'"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;