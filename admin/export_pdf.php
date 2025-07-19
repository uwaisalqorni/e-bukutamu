<?php
require '../vendor/autoload.php';
include '../db.php';

ob_clean(); // Bersihkan output buffer sebelum TCPDF

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

// Buat PDF
$pdf = new TCPDF();
$pdf->SetTitle('Laporan Buku Tamu');

// Tambahkan halaman
$pdf->AddPage();

// Logo & Header
$logo = '../assets/images/logo.png'; // ganti dengan path logo instansi
$nama_instansi = "RUMAH SAKIT ISLAM GONDANGLEGI";
$alamat_instansi = "Jl. Hayam Wuruk No.66, Gondanglegi, Malang";

$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, $nama_instansi, 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, $alamat_instansi, 0, 1, 'C');
$pdf->Image($logo, 15, 10, 20); // Logo kiri atas

// Judul Laporan
$pdf->Ln(10); // Spasi
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'LAPORAN BUKU TAMU', 0, 1, 'C');

// Tanggal Filter
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 6, "Tanggal: ".date('d-m-Y', strtotime($tanggal)), 0, 1, 'C');
$pdf->Ln(5);

// Tabel Data
$html = '
<style>
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #000; padding: 5px; font-size: 10px; }
    th { background-color: #f2f2f2; }
</style>
<table>
<thead>
<tr>
<th width="5%">No</th>
<th width="15%">Nama</th>
<th width="12%">NIK</th>
<th width="20%">Acara</th>
<th width="18%">Lokasi</th>
<th width="15%">Masuk</th>
<th width="15%">Keluar</th>
</tr>
</thead>
<tbody>';

$no = 1;
while ($row = $result->fetch_assoc()) {
    $html .= '<tr>
    <td align="center">'.$no++.'</td>
    <td>'.$row['nama'].'</td>
    <td align="center">'.$row['nik'].'</td>
    <td>'.$row['nama_acara'].'</td>
    <td>'.$row['nama_lokasi'].'</td>
    <td align="center">'.$row['waktu_masuk'].'</td>
    <td align="center">'.($row['waktu_keluar'] ?: '-').'</td>
    </tr>';
}

$html .= '</tbody></table>';

// Tambahkan tabel ke PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Output PDF ke browser
ob_clean();
$pdf->Output('laporan-buku-tamu.pdf', 'D');
exit;
?>