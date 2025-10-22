<?php
include 'layout.php';
include '../db.php';

/* ========= UTIL: pastikan kolom soft-delete ada ========= */
function ensureSoftDeleteColumns(mysqli $conn, string $table) {
  $needIsDeleted = true;
  $needDeletedAt = true;

  if ($res = $conn->query("SHOW COLUMNS FROM `$table` LIKE 'is_deleted'")) {
    if ($res->num_rows > 0) $needIsDeleted = false;
    $res->close();
  }
  if ($res = $conn->query("SHOW COLUMNS FROM `$table` LIKE 'deleted_at'")) {
    if ($res->num_rows > 0) $needDeletedAt = false;
    $res->close();
  }

  if ($needIsDeleted) {
    $conn->query("ALTER TABLE `$table` ADD COLUMN `is_deleted` TINYINT(1) NOT NULL DEFAULT 0");
  }
  if ($needDeletedAt) {
    $conn->query("ALTER TABLE `$table` ADD COLUMN `deleted_at` DATETIME NULL");
  }
}
ensureSoftDeleteColumns($conn, 'acara');

//delete permananen
/* ========= Flash helper ========= */
function flash($text, $type='success'){
  $_SESSION['flash_msg'] = $text;
  $_SESSION['flash_type'] = $type;
}

/* ========= HAPUS PERMANEN (hanya jika TIDAK dipakai di buku_tamu) ========= */
if (isset($_GET['permadelete'])) {
  $id = (int)$_GET['permadelete'];

  // cek pemakaian
  $stmtC = $conn->prepare("SELECT COUNT(*) AS n FROM buku_tamu WHERE id_acara = ?");
  $stmtC->bind_param('i', $id);
  $stmtC->execute();
  $n = $stmtC->get_result()->fetch_assoc()['n'] ?? 0;
  $stmtC->close();

  if ($n > 0) {
    flash("Tidak bisa dihapus permanen: masih dipakai di $n data tamu.", 'warning');
    header("Location: acara.php?show=archived");
    exit;
  }

  // aman: hapus
  $stmtD = $conn->prepare("DELETE FROM acara WHERE id = ?");
  $stmtD->bind_param('i', $id);
  $stmtD->execute();
  $stmtD->close();

  flash("Acara telah dihapus permanen.");
  header("Location: acara.php?show=archived");
  exit;
}


/* ========= CREATE ========= */
if (isset($_POST['add'])) {
  $nama_acara = trim($_POST['nama_acara'] ?? '');
  if ($nama_acara !== '') {
    $stmt = $conn->prepare("INSERT INTO acara (nama_acara, is_deleted, deleted_at) VALUES (?, 0, NULL)");
    $stmt->bind_param('s', $nama_acara);
    $stmt->execute();
    $stmt->close();
  }
  header("Location: acara.php");
  exit;
}

/* ========= SOFT DELETE ========= */
if (isset($_GET['archive'])) {
  $id = (int)$_GET['archive'];
  $stmt = $conn->prepare("UPDATE acara SET is_deleted = 1, deleted_at = NOW() WHERE id = ?");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $stmt->close();
  header("Location: acara.php");
  exit;
}

/* ========= RESTORE ========= */
if (isset($_GET['restore'])) {
  $id = (int)$_GET['restore'];
  $stmt = $conn->prepare("UPDATE acara SET is_deleted = 0, deleted_at = NULL WHERE id = ?");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $stmt->close();
  header("Location: acara.php?show=archived");
  exit;
}
?>
<!-- Content Header -->
<div class="content-header">
  <div class="container-fluid">
    <h1 class="m-0">📅 Manajemen Acara</h1>
    <p class="text-muted mb-0">Penghapusan memakai <b>arsip</b> agar data tamu tetap aman.</p>
  </div>
</div>

<section class="content">
  <div class="container-fluid">
  <?php if (!empty($_SESSION['flash_msg'])): ?>
  <div class="alert alert-<?=$_SESSION['flash_type'] ?? 'success'?> alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($_SESSION['flash_msg']); unset($_SESSION['flash_msg'], $_SESSION['flash_type']); ?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
    </button>
  </div>
<?php endif; ?>


    <!-- Form Tambah Acara -->
    <div class="card card-primary">
      <div class="card-header"><h3 class="card-title">Tambah Acara Baru</h3></div>
      <div class="card-body">
        <form method="POST" class="row g-3">
          <div class="col-md-8 mb-2">
            <input type="text" name="nama_acara" class="form-control" placeholder="Nama Acara" required>
          </div>
          <div class="col-md-4 mb-2">
            <button type="submit" name="add" class="btn btn-success w-100">➕ Tambah Acara</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Daftar Acara Aktif -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Daftar Acara Aktif</h3>
        <a href="?show=archived" class="btn btn-outline-secondary btn-sm">🗂️ Lihat Arsip</a>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-striped table-hover mb-0">
          <thead class="bg-primary text-white">
            <tr>
              <th style="width:80px">No</th>
              <th>Nama Acara</th>
              <th style="width:220px">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = 1;
            // gunakan COALESCE agar aman jika ada NULL lama
            $sqlAktif = "SELECT id, nama_acara FROM acara WHERE COALESCE(is_deleted,0)=0 ORDER BY id DESC";
            if ($res = $conn->query($sqlAktif)) {
              if ($res->num_rows > 0) {
                while ($row = $res->fetch_assoc()) {
                  echo '<tr>';
                  echo '<td>'.($no++).'</td>';
                  echo '<td>'.htmlspecialchars($row['nama_acara']).'</td>';
                  echo '<td>
                          <a href="?archive='.(int)$row['id'].'" class="btn btn-warning btn-sm"
                             onclick="return confirm(\'Arsipkan acara ini? Data tamu tetap aman.\')">🗄️ Arsipkan</a>
                        </td>';
                  echo '</tr>';
                }
              } else {
                echo '<tr><td colspan="3" class="text-center text-muted py-4">⚠️ Belum ada acara aktif.</td></tr>';
              }
              $res->close();
            } else {
              echo '<tr><td colspan="3" class="text-danger">Query error: '.$conn->error.'</td></tr>';
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Daftar Arsip -->
    <?php if (isset($_GET['show']) && $_GET['show']==='archived'): ?>
    <div class="card mt-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Arsip Acara</h3>
        <a href="acara.php" class="btn btn-outline-secondary btn-sm">⬅️ Kembali ke Aktif</a>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-striped table-hover mb-0">
          <thead class="bg-secondary text-white">
            <tr>
              <th style="width:80px">No</th>
              <th>Nama Acara</th>
              <th style="width:200px">Diarsipkan</th>
              <th style="width:220px">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = 1;
            $sqlArsip = "
              SELECT a.id, a.nama_acara, a.deleted_at,
                    (SELECT COUNT(*) FROM buku_tamu bt WHERE bt.id_acara = a.id) AS usages
              FROM acara a
              WHERE COALESCE(a.is_deleted,0)=1
              ORDER BY a.deleted_at DESC, a.id DESC";

            if ($res = $conn->query($sqlArsip)) {
              if ($res->num_rows > 0) {
                while ($row = $res->fetch_assoc()) {
                  $id         = (int)$row['id'];
                  $nama       = htmlspecialchars($row['nama_acara'] ?? '');
                  $deletedAt  = $row['deleted_at'] ? date('d-m-Y H:i', strtotime($row['deleted_at'])) : '-';
                  $usages     = (int)($row['usages'] ?? 0);

                  // state tombol hapus permanen
                  $disabledAttr = $usages > 0 ? 'disabled aria-disabled="true"' : '';
                  $title        = $usages > 0
                    ? "Tidak bisa dihapus permanen: dipakai di {$usages} data tamu"
                    : "Hapus permanen data ini";

                  echo '<tr>';
                  echo '  <td>'.($no++).'</td>';
                  echo '  <td>'.$nama.'</td>';
                  echo '  <td>'.htmlspecialchars($deletedAt).'</td>';
                  echo '  <td>';
                  echo '    <a href="?restore='.$id.'" class="btn btn-success btn-sm mb-1">♻️ Pulihkan</a> ';

                  // tombol hapus permanen (double confirm jika boleh)
                  if ($usages > 0) {
                    echo '    <button type="button" class="btn btn-danger btn-sm" '.$disabledAttr.' title="'.htmlspecialchars($title).'">🗑️ Hapus Permanen</button>';
                  } else {
                    echo '    <a href="?permadelete='.$id.'" ';
                    echo '       class="btn btn-danger btn-sm" ';
                    echo '       title="'.htmlspecialchars($title).'" ';
                    echo '       onclick="return confirm(\'Peringatan keras! Data akan dihapus permanen dan tidak dapat dikembalikan. Yakin?\') && confirm(\'Terakhir, benar-benar hapus permanen?\')">';
                    echo '       🗑️ Hapus Permanen';
                    echo '    </a>';
                  }

                  echo '  </td>';
                  echo '</tr>';
                }
              } else {
                echo '<tr><td colspan="4" class="text-center text-muted py-4">Tidak ada data arsip.</td></tr>';
              }
              $res->close();
            } else {
              echo '<tr><td colspan="4" class="text-danger">Query error: '.htmlspecialchars($conn->error).'</td></tr>';
            }
            ?>
          </tbody>

        </table>
      </div>
    </div>
    <?php endif; ?>

  </div>
</section>

<?php include 'footer-scripts.php'; ?>