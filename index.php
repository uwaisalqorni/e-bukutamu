<?php
include 'db.php';

$id_lokasi = isset($_GET['id_lokasi']) ? intval($_GET['id_lokasi']) : 0;
$lokasi_row = $conn->query("SELECT nama_lokasi FROM lokasi WHERE id=$id_lokasi")->fetch_assoc();
$nama_lokasi = $lokasi_row ? $lokasi_row['nama_lokasi'] : "Lokasi Tidak Dikenal";
$acara_result = $conn->query("SELECT * FROM acara ORDER BY nama_acara ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buku Tamu - <?php echo $nama_lokasi; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow p-4">
            <h2 class="text-center mb-3">Buku Tamu</h2>
            <p class="text-center text-muted">Lokasi: <strong><?php echo $nama_lokasi; ?></strong></p>
            
            <form action="checkin.php" method="POST">
                <input type="hidden" name="id_lokasi" value="<?php echo $id_lokasi; ?>">

                <!-- Nama Lengkap -->
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" id="nama" name="nama" required>
                </div>

                <!-- NIK -->
                <div class="mb-3">
                    <label for="nik" class="form-label">NIK</label>
                    <input type="text" class="form-control" id="nik" name="nik" required>
                </div>

                <!-- Dropdown Acara -->
                <div class="mb-3">
                    <label for="acara" class="form-label">Acara / Rapat</label>
                    <select name="id_acara" id="acara" class="form-select" required>
                        <option value="">-- Pilih Acara --</option>
                        <?php
                        while ($row = $acara_result->fetch_assoc()) {
                            echo "<option value='".$row['id']."'>".$row['nama_acara']."</option>";
                        }
                        ?>
                    </select>

                </div>

                <button type="submit" class="btn btn-success w-100">Check-In</button>
            </form>
            <hr>
            <h5 class="text-center text-muted mt-4">Atau Check-Out jika pulang:</h5>
            <form action="checkout.php" method="POST">
                <input type="hidden" name="id_lokasi" value="<?php echo $id_lokasi; ?>">
                <div class="mb-3">
                    <label for="nik_out" class="form-label">NIK</label>
                    <input type="text" class="form-control" id="nik_out" name="nik_out" placeholder="Masukkan NIK" required>
                </div>
                <button type="submit" class="btn btn-danger w-100">Check-Out</button>
            </form>
        </div>
    </div>
</body>
</html>