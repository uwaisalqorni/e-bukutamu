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
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

<style>
  .sig-box{border:1px dashed #999;border-radius:8px;background:#fff}
  .sig-actions .btn{min-width:120px}
  .sig-hint{font-size:.85rem;color:#6c757d}
  canvas{touch-action: none;}
</style>
<style>
  .sig-wrap{width:100%}
  .sig-box{
    border:1px dashed #9aa0a6; border-radius:12px; background:#fff;
    padding:8px; position:relative;
  }
  .sig-canvas{
    width:100%; height:180px; display:block; border-radius:8px; background:#fff;
    touch-action:none; /* penting di mobile: biar bisa gambar tanpa scroll */
  }
  /* layar kecil: tinggi sedikit lebih besar biar lega */
  @media (max-width: 480px){
    .sig-canvas{ height:210px; }
  }
  .sig-actions{
    display:flex; gap:.5rem; flex-wrap:wrap; margin-top:.5rem;
  }
  .sig-actions .btn{ min-width:120px; }
  .sig-hint{ font-size:.85rem; color:#6c757d; margin-left:.25rem; }
</style>



</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow p-4">
            <h2 class="text-center mb-3">📋 Daftar Hadir</h2>
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
                                          <!-- Tanda Tangan Digital -->
<!-- Tanda Tangan Digital (Responsif) -->
<div class="mb-3 sig-wrap">
  <label class="form-label mb-2">Tanda Tangan <span class="text-danger">*</span></label>

  <div class="sig-box">
    <canvas id="signature" class="sig-canvas"></canvas>
  </div>

  <div class="sig-actions">
    <button type="button" id="sigClear" class="btn btn-outline-secondary">Bersihkan</button>
    <button type="button" id="sigUndo"  class="btn btn-outline-warning">Undo</button>
    <button type="button" id="sigFit"   class="btn btn-outline-info">Sesuaikan Ukuran</button>
    <span class="sig-hint">Gunakan jari/stylus. Putar layar bila perlu.</span>
  </div>

  <!-- base64 PNG dikirim ke server -->
  <input type="hidden" name="ttd" id="ttd">
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

            <!-- Form Check-Out -->
            <h5 class="text-center text-muted mt-3">Atau Check-Out jika pulang:</h5>
            <div class="container mt-2">
                <div class="card shadow p-4">
                    <form action="checkout.php" method="POST" id="formCheckout">
                        <input type="hidden" name="id_lokasi" value="<?php echo $id_lokasi; ?>">
                        <div class="mb-3">
                            <label for="nik_out" class="form-label">NIK</label>
                            <input type="text" class="form-control" id="nik_out" name="nik_out" placeholder="Masukkan NIK Anda" required>
                            <div id="checkinStatus" class="form-text"></div>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">⏹️ Check-Out</button>
                    </form>
                </div>
            </div>
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
          
            // Tanda Tangan Digital
(function(){
  // Pastikan ID ini sesuai di HTML:
  const form   = document.getElementById('formTamu');   // <form id="formTamu" ...>
  const canvas = document.getElementById('signature');  // <canvas id="signature">
  const hidden = document.getElementById('ttd');        // <input type="hidden" id="ttd" name="ttd">

  if (!form || !canvas || !hidden) {
    console.warn('SignaturePad: pastikan id formTamu, signature, dan ttd ada.');
    return;
  }

  let pointerDown = false;
  let hasSigned   = false;   // penanda user sudah gores minimal 1x
  let lastSize    = { w: 0, h: 0 };

  const sigPad = new SignaturePad(canvas, {
    backgroundColor: 'rgba(255,255,255,1)',
    penColor: '#111',
    onBegin: () => { hasSigned = true; pointerDown = true; },
    onEnd:   () => { pointerDown = false; }
  });

  // Resize tajam + preserve coretan
  function resizeCanvas({preserve=true, force=false} = {}){
    // Jangan resize saat user sedang menggambar (mencegah kehilangan coretan)
    if (pointerDown) return;

    const ratio = Math.max(window.devicePixelRatio || 1, 1);

    // Lebar CSS aktual elemen pembungkus
    const rect = canvas.getBoundingClientRect();
    const cssWidth  = Math.max( Math.floor(rect.width),  310 );
    const cssHeight = Math.floor( (window.innerWidth <= 460) ? 200 : 170 );

    const targetW = cssWidth  * ratio;
    const targetH = cssHeight * ratio;

    // Hindari clear jika ukuran tidak berubah
    if (!force && lastSize.w === targetW && lastSize.h === targetH) return;

    const data = (preserve && !sigPad.isEmpty()) ? sigPad.toData() : null;

    canvas.width  = targetW;
    canvas.height = targetH;

    // Scale context biar tidak blur
    const ctx = canvas.getContext('2d');
    ctx.scale(ratio, ratio);
    ctx.fillStyle = '#fff';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    // Tinggi visual kanvas (CSS) – biar area nyaman disentuh
    canvas.style.width  = cssWidth + 'px';
    canvas.style.height = cssHeight + 'px';

    if (data) sigPad.fromData(data);

    lastSize = { w: targetW, h: targetH };
  }

  // Panggil awal
  resizeCanvas({preserve:false, force:true});

  // Debounce helper
  let rezTimer = null;
  function debouncedResize(){
    clearTimeout(rezTimer);
    rezTimer = setTimeout(() => resizeCanvas({preserve:true}), 120);
  }

  window.addEventListener('resize', debouncedResize);
  window.addEventListener('orientationchange', () => {
    // Tunggu layout settle di mobile
    setTimeout(() => resizeCanvas({preserve:true, force:true}), 180);
  });

  // Cegah halaman scroll saat menggambar
  canvas.addEventListener('touchstart', () => { pointerDown = true; document.body.style.overflow='hidden'; }, {passive:false});
  canvas.addEventListener('touchend',   () => { pointerDown = false; document.body.style.overflow='';       }, {passive:false});
  canvas.addEventListener('touchcancel',() => { pointerDown = false; document.body.style.overflow='';       }, {passive:false});

  // Tombol aksi
  const btnClear = document.getElementById('sigClear');
  const btnUndo  = document.getElementById('sigUndo');
  const btnFit   = document.getElementById('sigFit');

  if (btnClear) btnClear.addEventListener('click', () => { sigPad.clear(); hasSigned = false; });
  if (btnUndo)  btnUndo .addEventListener('click', () => {
    const data = sigPad.toData();
    if (data.length) { data.pop(); sigPad.fromData(data); }
    // update hasSigned berdasarkan isi pad
    hasSigned = !sigPad.isEmpty();
  });
  if (btnFit)   btnFit  .addEventListener('click', () => resizeCanvas({preserve:true, force:true}));

  // Validasi submit — pakai dua syarat: hasSigned ATAU canvas tidak empty
  form.addEventListener('submit', function(e){
    // Jika tombol submit bertipe "button" tidak akan submit — pastikan type="submit"
    const signed = hasSigned || !sigPad.isEmpty();
    if (!signed) {
      e.preventDefault();
      alert('Tanda tangan wajib diisi.');
      return false;
    }
    hidden.value = sigPad.toDataURL('image/png');
  });
})();
</script>


        </div>
    </div>
</body>
</html>