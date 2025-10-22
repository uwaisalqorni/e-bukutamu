<?php
// checkin.php – versi dengan tanda tangan digital (SignaturePad)
include 'db.php';

// -------- Helper kecil --------
function rand_hex($len = 6) {
  if (function_exists('random_bytes')) {
    return bin2hex(random_bytes($len/2));
  }
  return bin2hex(openssl_random_pseudo_bytes($len/2));
}

// -------- Ambil & validasi input --------
$id_lokasi = isset($_POST['id_lokasi']) ? (int)$_POST['id_lokasi'] : 0;
$id_acara  = isset($_POST['id_acara'])  ? (int)$_POST['id_acara']  : 0;
$nik       = trim($_POST['nik']  ?? '');
$nama      = trim($_POST['nama'] ?? '');
$ttdData   = $_POST['ttd'] ?? ''; // data URL: "data:image/png;base64,..."

// validasi minimal
if ($id_lokasi <= 0 || $nama === '' || $nik === '') {
  echo "<script>alert('Data belum lengkap. Pastikan lokasi, nama, dan NIK terisi.'); history.back();</script>";
  exit;
}

// TTD diwajibkan (kalau ingin opsional, hapus blok validasi ini)
if (empty($ttdData) || strpos($ttdData, 'data:image') !== 0) {
  echo "<script>alert('Tanda tangan wajib diisi.'); history.back();</script>";
  exit;
}

// -------- Simpan file tanda tangan --------
$ttdPath = null;
if (!empty($ttdData) && strpos($ttdData, 'data:image') === 0) {
  // contoh: data:image/png;base64,XXXXX
  $parts = explode(',', $ttdData, 2);
  if (count($parts) === 2) {
    $meta = $parts[0];             // "data:image/png;base64"
    $b64  = $parts[1];
    // batasi ukuran maksimal ~3MB agar aman
    if (strlen($b64) > 3 * 1024 * 1024) {
      echo "<script>alert('Tanda tangan terlalu besar. Coba ulangi.'); history.back();</script>";
      exit;
    }
    $bin = base64_decode($b64, true);
    if ($bin === false) {
      echo "<script>alert('Format tanda tangan tidak valid.'); history.back();</script>";
      exit;
    }

    // pastikan folder ada
    $dir = __DIR__ . '/uploads/ttd';
    if (!is_dir($dir)) { @mkdir($dir, 0775, true); }

    // nama file unik
    $fname = 'ttd_' . $id_lokasi . '_' . date('Ymd_His') . '_' . rand_hex(6) . '.png';
    $full  = $dir . '/' . $fname;

    if (file_put_contents($full, $bin) === false) {
      echo "<script>alert('Gagal menyimpan tanda tangan di server.'); history.back();</script>";
      exit;
    }
    // path relatif untuk disimpan ke DB
    $ttdPath = 'uploads/ttd/' . $fname;
  }
}

// -------- Insert ke database (prepared) --------
// NOTE: pastikan di tabel `buku_tamu` sudah ada kolom `ttd_path` (VARCHAR 255) & `waktu_masuk` (DATETIME).
// Jika belum, jalankan sekali:
//   ALTER TABLE buku_tamu ADD COLUMN ttd_path VARCHAR(255) NULL AFTER waktu_keluar;
//   ALTER TABLE buku_tamu ADD COLUMN waktu_masuk DATETIME NULL;

$idAcaraNullable = $id_acara > 0 ? $id_acara : null;

$sql = "INSERT INTO buku_tamu (id_lokasi, id_acara, nama, nik, waktu_masuk, ttd_path)
        VALUES (?, ?, ?, ?, NOW(), ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
  // bersihkan file ttd jika insert gagal disiapkan
  if ($ttdPath && is_file(__DIR__ . '/' . $ttdPath)) { @unlink(__DIR__ . '/' . $ttdPath); }
  die("Terjadi kesalahan saat menyiapkan query: " . $conn->error);
}

$stmt->bind_param('iisss', $id_lokasi, $idAcaraNullable, $nama, $nik, $ttdPath);
$ok = $stmt->execute();
$err = $stmt->error;
$stmt->close();

if ($ok) {
  // sukses
  echo "<script>alert('Check-In berhasil!'); window.location='index.php?id_lokasi={$id_lokasi}';</script>";
} else {
  // hapus file bila insert gagal
  if ($ttdPath && is_file(__DIR__ . '/' . $ttdPath)) { @unlink(__DIR__ . '/' . $ttdPath); }
  echo "Error saat menyimpan data: " . htmlspecialchars($err);
}

$conn->close();