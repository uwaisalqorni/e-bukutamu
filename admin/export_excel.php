<?php
require '../vendor/autoload.php';
include '../db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

// Ambil filter
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
$acara   = isset($_GET['acara']) ? intval($_GET['acara']) : '';

// Query data
$sql = "SELECT buku_tamu.*, lokasi.nama_lokasi, acara.nama_acara 
        FROM buku_tamu 
        JOIN lokasi ON buku_tamu.id_lokasi = lokasi.id 
        JOIN acara ON buku_tamu.id_acara = acara.id
        WHERE DATE(waktu_masuk) = '$tanggal'";
if ($acara != '') {
    $sql .= " AND buku_tamu.id_acara = $acara";
}
$sql .= " ORDER BY waktu_masuk DESC";
$result = $conn->query($sql);

// Buat Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Report Buku Tamu');

// Logo Instansi
$logo = new Drawing();
$logo->setName('Logo');
$logo->setDescription('Logo');
$logo->setPath('../assets/images/logo.png'); // path logo
$logo->setHeight(50);
$logo->setCoordinates('A1');
$logo->setWorksheet($sheet);

// Nama & Alamat Instansi
$nama_instansi = "RUMAH SAKIT ISLAM GONDANGLEGI";
$alamat_instansi = "Jl. Hayam Wuruk No.66, Gondanglegi, Malang";

$sheet->mergeCells('B1:H1');
$sheet->setCellValue('B1', $nama_instansi);
$sheet->getStyle('B1')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('B1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->mergeCells('B2:H2');
$sheet->setCellValue('B2', $alamat_instansi);
$sheet->getStyle('B2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Judul Laporan
$sheet->mergeCells('A4:H4');
$sheet->setCellValue('A4', 'LAPORAN BUKU TAMU');
$sheet->getStyle('A4')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Header Tabel
$headers = ['No', 'Nama', 'NIK', 'Acara', 'Lokasi', 'Masuk', 'Keluar', 'Status'];
$sheet->fromArray($headers, NULL, 'A6');

// Isi Data
$rowNum = 7;
$no = 1;
while ($row = $result->fetch_assoc()) {
    $status = $row['waktu_keluar'] ? "Selesai" : "Masih di lokasi";
    $sheet->fromArray([
        $no++,
        $row['nama'],
        $row['nik'],
        $row['nama_acara'],
        $row['nama_lokasi'],
        $row['waktu_masuk'],
        $row['waktu_keluar'] ?: '-',
        $status
    ], NULL, "A$rowNum");
    $rowNum++;
}

// Style Header
$sheet->getStyle('A6:H6')->getFont()->setBold(true);
$sheet->getStyle('A6:H6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A6:H'.($rowNum-1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

// Auto Width Kolom
foreach (range('A', 'H') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Download file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="report-buku-tamu.xlsx"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>