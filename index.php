<?php
include 'db.php';

// Validasi ID Lokasi
$id_lokasi = isset($_GET['id_lokasi']) ? intval($_GET['id_lokasi']) : 0;
$lokasi_row = $conn->query("SELECT nama_lokasi FROM lokasi WHERE id=$id_lokasi")->fetch_assoc();
if (!$lokasi_row) {
    die("<h3 class='text-danger text-center mt-5'>❌ Lokasi tidak valid! Silakan scan QR Code terbaru atau hubungi admin.</h3>");
}
$nama_lokasi = $lokasi_row['nama_lokasi'];

// Ambil daftar acara
$acara_result = $conn->query("SELECT * FROM acara ORDER BY nama_acara ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buku Tamu - <?php echo htmlspecialchars($nama_lokasi); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow p-4">
            <h2 class="text-center mb-3">📋 Buku Tamu</h2>
            <p class="text-center text-muted">Lokasi: <strong><?php echo htmlspecialchars($nama_lokasi); ?></strong></p>
            
            <!-- Form Check-In -->
            <form action="checkin.php" method="POST" id="formTamu">
                <input type="hidden" name="id_lokasi" value="<?php echo $id_lokasi; ?>">

                <!-- NIK -->
                <div class="mb-3">
                    <label for="nik" class="form-label">NIK</label>
                    <input type="text" class="form-control" id="nik" name="nik" placeholder="Cari NIK atau Nama" required>
                </div>

                <!-- Nama Pegawai (readonly) -->
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" id="nama" name="nama" readonly required>
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
                </div>

                <button type="submit" class="btn btn-success w-100">✅ Check-In</button>
            </form>

            <!-- Script jQuery -->
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script>
            $('#nik').on('blur', function() {
                let nik = $(this).val();
                if (nik.length > 0) {
                    $.ajax({
                        url: 'Api/get_pegawai.php',
                        type: 'POST',
                        data: { nik: nik },
                        success: function(response) {
                            console.log(response); // Debug response

                            if (response.status === 'success') {
                                $('#nama').val(response.data.nama);
                            } else {
                                alert('⚠️ ' + response.message);
                                $('#nama').val('');
                            }
                        },
                        error: function(xhr, status, error) {
                            alert('❌ Gagal menghubungi API SIMRS. ' + error);
                            $('#nama').val('');
                        }
                    });
                } else {
                    $('#nama').val('');
                }
            });


            </script>

            <hr class="my-4">

            <!-- Form Check-Out -->
            <h5 class="text-center text-muted mt-4">Atau Check-Out jika pulang:</h5>
            <form action="checkout.php" method="POST" id="formCheckout">
                <input type="hidden" name="id_lokasi" value="<?php echo $id_lokasi; ?>">
                <div class="mb-3">
                    <label for="nik_out" class="form-label">NIK</label>
                    <input type="text" class="form-control" id="nik_out" name="nik_out" placeholder="Masukkan NIK Anda" required>
                    <div id="checkinStatus" class="form-text"></div>
                </div>
                <button type="submit" class="btn btn-danger w-100">⏹️ Check-Out</button>
            </form>

            <script>
            $('#nik_out').on('blur', function() {
                let nik = $(this).val();
                if (nik.length > 0) {
                    $.ajax({
                        url: 'validate_checkout.php',
                        type: 'POST',
                        data: { nik: nik, id_lokasi: <?php echo $id_lokasi; ?> },
                        success: function(response) {
                            if (response.status === 'success') {
                                $('#checkinStatus').text('✅ NIK ditemukan. Siap Check-Out.').css('color', 'green');
                            } else {
                                $('#checkinStatus').text('❌ ' + response.message).css('color', 'red');
                            }
                        },
                        error: function() {
                            $('#checkinStatus').text('❌ Gagal mengecek NIK.').css('color', 'red');
                        }
                    });
                } else {
                    $('#checkinStatus').text('');
                }
            });
            </script>
        </div>
    </div>
</body>
</html>