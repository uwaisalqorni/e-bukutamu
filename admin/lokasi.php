<?php
include 'layout.php';
include '../db.php';
include '../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

/* =========================
   Helper: buat/cek QR Code
========================= */
function ensure_qr_for($id_lokasi, $link_qr) {
  $qrPath = "../qrcode/{$id_lokasi}.png";
  if (!file_exists($qrPath)) {
    $qr = QrCode::create($link_qr);
    $writer = new PngWriter();
    $result = $writer->write($qr);
    if (!is_dir(dirname($qrPath))) {
      @mkdir(dirname($qrPath), 0775, true);
    }
    file_put_contents($qrPath, $result->getString());
  }
  return $qrPath;
}

/* =========================
   Tambah lokasi
========================= */
if (isset($_POST['add'])) {
    $nama_lokasi = trim($_POST['nama_lokasi']);

    if ($nama_lokasi !== '') {
        // insert lokasi (is_deleted=0)
        $stmt = $conn->prepare("INSERT INTO lokasi (nama_lokasi, link_qr, is_deleted, deleted_at) VALUES (?, '', 0, NULL)");
        $stmt->bind_param('s', $nama_lokasi);
        $stmt->execute();
        $id_lokasi = $stmt->insert_id;
        $stmt->close();

        // generate link QR (silakan sesuaikan base URL sesuai servermu)
        $baseUrl = "https://bible-kong-sam-use.trycloudflare.com/bukutamu/index.php";
        $link_qr = $baseUrl . "?id_lokasi=" . $id_lokasi;

        // update link_qr
        $stmtU = $conn->prepare("UPDATE lokasi SET link_qr=? WHERE id=?");
        $stmtU->bind_param('si', $link_qr, $id_lokasi);
        $stmtU->execute();
        $stmtU->close();

        // pastikan file QR tersedia
        ensure_qr_for($id_lokasi, $link_qr);
    }

    header("Location: lokasi.php");
    exit();
}

/* =========================
   Arsipkan (soft delete)
========================= */
if (isset($_GET['archive'])) {
    $id = (int)$_GET['archive'];
    $stmt = $conn->prepare("UPDATE lokasi SET is_deleted=1, deleted_at=NOW() WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    header("Location: lokasi.php");
    exit();
}

/* =========================
   Pulihkan dari arsip
========================= */
if (isset($_GET['restore'])) {
    $id = (int)$_GET['restore'];
    $stmt = $conn->prepare("UPDATE lokasi SET is_deleted=0, deleted_at=NULL WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    header("Location: lokasi.php?show=archived");
    exit();
}
?>

<!-- Content Header -->
<div class="content-header">
  <div class="container-fluid">
    <h1 class="m-0">📍 Manajemen Lokasi & QR Code</h1>
    <p class="text-muted">Penghapusan kini memakai <b>arsip</b> agar data tamu tetap aman.</p>
  </div>
</div>

<!-- Main content -->
<section class="content">
  <div class="container-fluid">

    <!-- Form Tambah Lokasi -->
    <div class="card card-primary">
      <div class="card-header"><h3 class="card-title">Tambah Lokasi Baru</h3></div>
      <div class="card-body">
        <form method="POST" class="row g-3">
          <div class="col-md-8">
            <input type="text" name="nama_lokasi" class="form-control" placeholder="Nama Lokasi" required>
          </div>
          <div class="col-md-4">
            <button type="submit" name="add" class="btn btn-success w-100">➕ Tambah Lokasi</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Daftar Lokasi Aktif -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Daftar Lokasi Aktif</h3>
        <a href="?show=archived" class="btn btn-outline-secondary btn-sm">🗂️ Lihat Arsip</a>
      </div>
      <div class="card-body table-responsive">
        <table class="table table-striped table-hover">
          <thead class="bg-primary text-white">
            <tr>
              <th>No</th>
              <th>Nama Lokasi</th>
              <th>QR Code</th>
              <th>Link</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = 1;
            $res = $conn->query("SELECT id, nama_lokasi, link_qr FROM lokasi WHERE COALESCE(is_deleted,0)=0 ORDER BY id DESC");
            while ($row = $res->fetch_assoc()):
              // pastikan file QR ada (kalau hilang, buat lagi)
              ensure_qr_for((int)$row['id'], $row['link_qr']);
            ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= htmlspecialchars($row['nama_lokasi']) ?></td>
              <td>
                <img src="../qrcode/<?= (int)$row['id'] ?>.png" width="80" class="mb-2"><br>
                <a href="../qrcode/<?= (int)$row['id'] ?>.png" download class="btn btn-sm btn-outline-secondary">⬇️ Download QR</a>
                <button class="btn btn-sm btn-outline-info" onclick="viewQr('../qrcode/<?= (int)$row['id'] ?>.png')">👁️ View QR</button>
              </td>
              <td>
                <input type="text" class="form-control mb-2" id="link-<?= (int)$row['id'] ?>" value="<?= htmlspecialchars($row['link_qr']) ?>" readonly>
                <button class="btn btn-sm btn-outline-primary" onclick="copyLink('<?= (int)$row['id'] ?>')">📋 Copy Link</button>
              </td>
              <td>
                <a href="?archive=<?= (int)$row['id'] ?>"
                   onclick="return confirm('Arsipkan lokasi ini? Data tamu tetap aman dan masih bisa dilihat di laporan.')"
                   class="btn btn-warning btn-sm mb-2">🗄️ Arsipkan</a><br>
                <a href="https://wa.me/?text=Scan QR atau klik link: <?= urlencode($row['link_qr']) ?>" target="_blank" class="btn btn-success btn-sm mb-1">📤 Share WhatsApp</a><br>
                <a href="https://t.me/share/url?url=<?= urlencode($row['link_qr']) ?>&text=Scan QR atau klik link ini" target="_blank" class="btn btn-info btn-sm">📤 Share Telegram</a>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Daftar Lokasi Terarsip -->
    <?php if (isset($_GET['show']) && $_GET['show']==='archived'): ?>
    <div class="card mt-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Arsip Lokasi (Soft-deleted)</h3>
        <a href="lokasi.php" class="btn btn-outline-secondary btn-sm">⬅️ Kembali ke Aktif</a>
      </div>
      <div class="card-body table-responsive">
        <table class="table table-striped table-hover">
          <thead class="bg-secondary text-white">
            <tr>
              <th>No</th>
              <th>Nama Lokasi</th>
              <th>Diarsipkan Pada</th>
              <th>Link</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = 1;
            $sqlArsipLok = "
              SELECT l.id, l.nama_lokasi, l.link_qr, l.deleted_at,
                    (SELECT COUNT(*) FROM buku_tamu bt WHERE bt.id_lokasi = l.id) AS usages
              FROM lokasi l
              WHERE COALESCE(l.is_deleted,0)=1
              ORDER BY l.deleted_at DESC, l.id DESC";

            if ($resA = $conn->query($sqlArsipLok)) {
              if ($resA->num_rows > 0) {
                while ($row = $resA->fetch_assoc()) {
                  $id         = (int)$row['id'];
                  $nama       = htmlspecialchars($row['nama_lokasi'] ?? '');
                  $linkQr     = htmlspecialchars($row['link_qr'] ?? '');
                  $deletedAt  = $row['deleted_at'] ? date('d-m-Y H:i', strtotime($row['deleted_at'])) : '-';
                  $usages     = (int)($row['usages'] ?? 0);

                  // state tombol hapus permanen
                  $disabledAttr = $usages > 0 ? 'disabled aria-disabled="true"' : '';
                  $title        = $usages > 0
                    ? "Tidak bisa dihapus permanen: dipakai di {$usages} data tamu"
                    : "Hapus permanen data ini (file QR juga dihapus)";

                  echo '<tr>';
                  echo '  <td>'.($no++).'</td>';
                  echo '  <td>'.$nama.'</td>';
                  echo '  <td>'.htmlspecialchars($deletedAt).'</td>';
                  echo '  <td><small>'.$linkQr.'</small></td>';
                  echo '  <td>';
                  echo '    <a href="?restore='.$id.'" class="btn btn-success btn-sm mb-1">♻️ Pulihkan</a> ';

                  // tombol hapus permanen (double confirm jika boleh)
                  if ($usages > 0) {
                    echo '    <button type="button" class="btn btn-danger btn-sm" '.$disabledAttr.' title="'.htmlspecialchars($title).'">🗑️ Hapus Permanen</button>';
                  } else {
                    echo '    <a href="?permadelete='.$id.'" ';
                    echo '       class="btn btn-danger btn-sm" ';
                    echo '       title="'.htmlspecialchars($title).'" ';
                    echo '       onclick="return confirm(\'Peringatan keras! Data lokasi & file QR akan dihapus permanen dan tidak dapat dikembalikan. Yakin?\') && confirm(\'Terakhir, benar-benar hapus permanen?\')">';
                    echo '       🗑️ Hapus Permanen';
                    echo '    </a>';
                  }

                  echo '  </td>';
                  echo '</tr>';
                }
              } else {
                echo '<tr><td colspan="5" class="text-center text-muted py-4">Tidak ada lokasi dalam arsip.</td></tr>';
              }
              $resA->close();
            } else {
              echo '<tr><td colspan="5" class="text-danger">Query error: '.htmlspecialchars($conn->error).'</td></tr>';
            }
            ?>
            </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

  </div>
</section>

<!-- Modal Zoom QR -->
<div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center">
        <img src="" id="qrImage" class="img-fluid">
      </div>
      <div class="modal-footer justify-content-center">
        <button class="btn btn-secondary" data-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
function copyLink(id) {
  let copyText = document.getElementById("link-" + id);
  copyText.select();
  document.execCommand("copy");
  alert("📋 Link berhasil disalin!");
}
function viewQr(src) {
  document.getElementById('qrImage').src = src;
  $('#qrModal').modal('show');
}
</script>

<?php include 'footer-scripts.php'; ?>