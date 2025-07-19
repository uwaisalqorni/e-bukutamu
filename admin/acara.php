<?php
include '../db.php';

// Tambah acara
if (isset($_POST['add'])) {
    $nama_acara = $conn->real_escape_string($_POST['nama_acara']);
    $conn->query("INSERT INTO acara (nama_acara) VALUES ('$nama_acara')");
    header("Location: acara.php");
    exit();
}

// Update acara
if (isset($_POST['edit'])) {
    $id = intval($_POST['id']);
    $nama_acara = $conn->real_escape_string($_POST['nama_acara']);
    $conn->query("UPDATE acara SET nama_acara='$nama_acara' WHERE id=$id");
    header("Location: acara.php");
    exit();
}

// Hapus acara
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM acara WHERE id=$id");
    header("Location: acara.php");
    exit();
}

// Ambil semua acara
$acara = $conn->query("SELECT * FROM acara ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manajemen Acara</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2>📅 Manajemen Acara</h2>
    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-6">
            <input type="text" name="nama_acara" class="form-control" placeholder="Nama Acara" required>
        </div>
        <div class="col-md-2">
            <button type="submit" name="add" class="btn btn-success w-100">Tambah</button>
        </div>
        <div class="col-md-2">
            <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
    <div class="card shadow">
        <div class="card-body">
            <h5 class="card-title">Daftar Acara</h5>
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama Acara</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while($row = $acara->fetch_assoc()) {
                        echo "<tr>
                            <td>".$no++."</td>
                            <td>".$row['nama_acara']."</td>
                            <td>
                                <!-- Tombol Edit -->
                                <button class='btn btn-sm btn-warning' data-bs-toggle='modal' data-bs-target='#editModal".$row['id']."'>Edit</button>
                                <!-- Tombol Hapus -->
                                <a href='?delete=".$row['id']."' onclick='return confirm(\"Hapus acara ini?\")' class='btn btn-danger btn-sm'>Hapus</a>
                            </td>
                        </tr>";

                        // Modal Edit
                        echo "
                        <div class='modal fade' id='editModal".$row['id']."' tabindex='-1'>
                          <div class='modal-dialog'>
                            <div class='modal-content'>
                              <div class='modal-header'>
                                <h5 class='modal-title'>Edit Acara</h5>
                                <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                              </div>
                              <div class='modal-body'>
                                <form method='POST'>
                                    <input type='hidden' name='id' value='".$row['id']."'>
                                    <div class='mb-3'>
                                        <label class='form-label'>Nama Acara</label>
                                        <input type='text' name='nama_acara' class='form-control' value='".$row['nama_acara']."' required>
                                    </div>
                                    <button type='submit' name='edit' class='btn btn-primary'>Simpan Perubahan</button>
                                </form>
                              </div>
                            </div>
                          </div>
                        </div>
                        ";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
