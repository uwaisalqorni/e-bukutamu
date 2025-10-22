<?php
include 'layout.php';
include '../db.php';
?>
<!-- Content Header -->
<div class="content-header">
  <div class="container-fluid">
    <h1 class="m-0">📊 Dashboard Tamu Hadir</h1>
    <p class="text-muted">Filter data tamu & auto-refresh setiap 5 detik</p>
  </div>
</div>

<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <!-- Filter Form -->
    <!-- Filter Form -->
<div class="card card-primary card-outline">
  <div class="card-body">
    <form id="filterForm" class="row align-items-end">
      <!-- Preset / Mode Tanggal -->
      <div class="col-md-3 mb-2">
        <label class="small text-muted mb-1">Mode Tanggal</label>
        <select id="modeTanggal" class="form-control">
          <option value="today">🟢 Hari ini</option>
          <option value="yesterday">🔵 Kemarin</option>
          <option value="last7">🗓️ 7 Hari Terakhir</option>
          <option value="thismonth">📅 Bulan Ini</option>
          <option value="range">📏 Rentang Tanggal</option>
        </select>
      </div>

      <!-- Rentang: Start -->
      <div class="col-md-3 mb-2 range-only d-none">
        <label class="small text-muted mb-1">Mulai</label>
        <input type="date" id="startDate" class="form-control">
      </div>

      <!-- Rentang: End -->
      <div class="col-md-3 mb-2 range-only d-none">
        <label class="small text-muted mb-1">Selesai</label>
        <input type="date" id="endDate" class="form-control">
      </div>

      <!-- Acara -->
      <div class="col-md-3 mb-2">
        <label class="small text-muted mb-1">Acara</label>
        <select id="filterAcara" name="acara" class="form-control">
          <option value="">📅 Semua Acara</option>
          <?php
          // $acaraResult = $conn->query("SELECT * FROM acara ORDER BY nama_acara ASC");
          $acaraResult = $conn->query("SELECT * FROM acara WHERE COALESCE(is_deleted,0)=0 ORDER BY nama_acara ASC");
          while ($row = $acaraResult->fetch_assoc()) {
              echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['nama_acara']) . "</option>";
          }
          ?>
        </select>
      </div>

      <!-- Tombol -->
      <div class="col-md-3 mb-2">
        <button type="button" onclick="loadData()" class="btn btn-primary w-100">🔍 Terapkan Filter</button>
      </div>

      <!-- Export -->
      <div class="col-md-3 mb-2 text-right">
        <a id="exportExcel" href="#" class="btn btn-success btn-block">📥 Export Excel</a>
        <!-- <a id="exportPdf" href="#" class="btn btn-danger btn-block">📥 Export PDF</a> -->
      </div>
    </form>
  </div>
</div>


    <!-- Tabel Data -->
    <div class="card">
      <div class="card-body table-responsive p-0">
        <table class="table table-striped table-hover">
          <thead class="bg-primary text-white">
            <tr>
              <th>No</th>
              <th>Nama</th>
              <th>NIK</th>
              <th>Acara</th>
              <th>Lokasi</th>
              <th>Masuk</th>
              <th>Keluar</th>
              <th>TTD</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody id="data-tamu">
            <!-- Data dimuat via AJAX -->
          </tbody>
        </table>
      </div>
    </div>
  </div>

</section>
<!-- Modal Preview TTD -->
<div class="modal fade" id="ttdModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-body p-2 text-center">
        <img id="ttdPreviewImg" src="" alt="Tanda Tangan" class="img-fluid" style="max-height:80vh;">
      </div>
      <div class="modal-footer justify-content-between">
        <a id="ttdDownload" class="btn btn-outline-secondary" href="#" download>⬇️ Download</a>
        <button class="btn btn-primary" data-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
  function previewTTD(src){
    const img = document.getElementById('ttdPreviewImg');
    const dl  = document.getElementById('ttdDownload');
    img.src = src;
    dl.href = src;
    $('#ttdModal').modal('show');
  }
</script>

<script>
/* util kecil */
function pad(n){ return n < 10 ? '0' + n : n; }
function fmt(d){ return d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate()); }

/* set nilai default */
(function initDefaults(){
  const now = new Date();
  const startMonth = new Date(now.getFullYear(), now.getMonth(), 1);

  // default: Hari ini
  $('#modeTanggal').val('today');
  // untuk range (kalau dipilih) kita isi dengan today sebagai contoh
  $('#startDate').val(fmt(now));
  $('#endDate').val(fmt(now));
})();

/* toggle input rentang */
$('#modeTanggal').on('change', function(){
  const isRange = $(this).val() === 'range';
  $('.range-only').toggleClass('d-none', !isRange);
});

/* bangun parameter tanggal berdasarkan mode */
function buildDateParams(){
  const mode = $('#modeTanggal').val();
  const today = new Date();
  let start, end;

  if(mode === 'today'){
    start = fmt(today);
    end   = fmt(today);
  } else if(mode === 'yesterday'){
    const y = new Date(today); y.setDate(y.getDate() - 1);
    start = fmt(y); end = fmt(y);
  } else if(mode === 'last7'){
    const s = new Date(today); s.setDate(s.getDate() - 6); // termasuk hari ini
    start = fmt(s); end = fmt(today);
  } else if(mode === 'thismonth'){
    const s = new Date(today.getFullYear(), today.getMonth(), 1);
    const e = new Date(today.getFullYear(), today.getMonth()+1, 0);
    start = fmt(s); end = fmt(e);
  } else if(mode === 'range'){
    start = $('#startDate').val();
    end   = $('#endDate').val();
  }

  // fallback kalau kosong
  if(!start){ start = fmt(today); }
  if(!end){ end = start; }

  return { start, end };
}

/* set href export agar ikut filter */
function updateExportLinks(params){
  const q = `?start=${encodeURIComponent(params.start)}&end=${encodeURIComponent(params.end)}&acara=${encodeURIComponent($('#filterAcara').val()||'')}`;
  $('#exportExcel').attr('href', 'export_excel.php' + q);
  $('#exportPdf').attr('href', 'export_pdf.php' + q);
}

/* load data */
function loadData() {
  const acara = $('#filterAcara').val() || '';
  const { start, end } = buildDateParams();
  updateExportLinks({ start, end });

  const q = `start=${encodeURIComponent(start)}&end=${encodeURIComponent(end)}&acara=${encodeURIComponent(acara)}`;
  $("#data-tamu").load("data_tamu.php?" + q);
}

/* load pertama kali */
loadData();

/* auto-refresh 5 detik */
setInterval(loadData, 5000);
</script>

<?php include 'footer-scripts.php'; ?>
