<h2>📊 Dashboard Tamu Hadir</h2>
<p class="text-muted">Filter data tamu & auto-refresh setiap 5 detik</p>

<!-- Filter Form -->
<form id="filterForm" class="row g-3 mb-3">
    <div class="col-12 col-md-4">
        <input type="date" id="filterTanggal" name="tanggal" class="form-control" value="<?php echo date('Y-m-d'); ?>">
    </div>
    <div class="col-12 col-md-5">
        <select id="filterAcara" name="acara" class="form-select">
            <option value="">📅 Semua Acara</option>
            <?php
            $acaraResult = $conn->query("SELECT * FROM acara ORDER BY nama_acara ASC");
            while ($row = $acaraResult->fetch_assoc()) {
                echo "<option value='".$row['id']."'>".$row['nama_acara']."</option>";
            }
            ?>
        </select>
    </div>
    <div class="col-12 col-md-3">
        <button type="button" onclick="loadData()" class="btn btn-primary w-100">🔍 Filter</button>
    </div>
</form>

<!-- Tabel Data -->
<div class="card shadow">
    <div class="card-body table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>NIK</th>
                    <th>Acara</th>
                    <th>Lokasi</th>
                    <th>Masuk</th>
                    <th>Keluar</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="data-tamu"></tbody>
        </table>
    </div>
</div>

<script>
function loadData() {
    let tanggal = $("#filterTanggal").val();
    let acara = $("#filterAcara").val();
    $("#data-tamu").load("data_tamu.php?tanggal=" + tanggal + "&acara=" + acara);
}
loadData();
setInterval(loadData, 5000);
</script>