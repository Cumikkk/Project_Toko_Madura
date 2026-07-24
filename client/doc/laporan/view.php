<?php
use Config\Core\Database;
use App\Models\User;
use Config\Core\SystemInfo;

$user = User::user();
$db = Database::connect();
$userId = (int)($user['MBR_ID'] ?? $user['id_users'] ?? 0);
$role = strtolower($user['role'] ?? 'investor');

// Array nama bulan Bahasa Indonesia
$bulanIndo = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Separate Month & Year Filter
$selectedBulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('n');
$selectedTahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

$availableYears = [];
$laporanList = [];
$totalOmzet = 0;
$totalPotongan = 0;
$totalLaporan = 0;

if ($role === 'investor') {
    // Get Investor ID
    $resInv = $db->query("SELECT id_investor FROM investor WHERE id_users = {$userId} LIMIT 1");
    $investorId = ($resInv && $resInv->num_rows > 0) ? (int)$resInv->fetch_assoc()['id_investor'] : 0;

    // Fetch distinct years
    $resYears = $db->query("SELECT DISTINCT YEAR(l.periode_laporan) as y_periode FROM laporan_omzet l JOIN outlet o ON l.id_outlet = o.id_outlet WHERE o.id_investor = {$investorId} ORDER BY y_periode DESC");
    if ($resYears) {
        while ($yRow = $resYears->fetch_assoc()) {
            $availableYears[] = (int)$yRow['y_periode'];
        }
    }
    if (!in_array((int)date('Y'), $availableYears)) {
        array_unshift($availableYears, (int)date('Y'));
    }

    // Build WHERE clause
    $whereConditions = ["o.id_investor = {$investorId}"];
    if ($selectedBulan > 0) $whereConditions[] = "MONTH(l.periode_laporan) = {$selectedBulan}";
    if ($selectedTahun > 0) $whereConditions[] = "YEAR(l.periode_laporan) = {$selectedTahun}";
    $whereSql = implode(" AND ", $whereConditions);

    $sql = "SELECT l.*, o.nama_outlet, o.kode_outlet FROM laporan_omzet l JOIN outlet o ON l.id_outlet = o.id_outlet WHERE {$whereSql} ORDER BY l.periode_laporan DESC, l.id_laporan DESC";
    $res = $db->query($sql);
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $laporanList[] = $r;
            $totalOmzet += (float)$r['omzet'];
            $totalPotongan += (float)$r['nominal_potongan'];
        }
    }
} else {
    // Outlet role
    $resOut = $db->query("SELECT id_outlet, nama_outlet, kode_outlet FROM outlet WHERE id_users = {$userId} LIMIT 1");
    $outlet = ($resOut && $resOut->num_rows > 0) ? $resOut->fetch_assoc() : null;
    $outletId = $outlet ? (int)$outlet['id_outlet'] : 0;

    $resYears = $db->query("SELECT DISTINCT YEAR(periode_laporan) as y_periode FROM laporan_omzet WHERE id_outlet = {$outletId} ORDER BY y_periode DESC");
    if ($resYears) {
        while ($yRow = $resYears->fetch_assoc()) {
            $availableYears[] = (int)$yRow['y_periode'];
        }
    }
    if (!in_array((int)date('Y'), $availableYears)) {
        array_unshift($availableYears, (int)date('Y'));
    }

    $whereConditions = ["id_outlet = {$outletId}"];
    if ($selectedBulan > 0) $whereConditions[] = "MONTH(periode_laporan) = {$selectedBulan}";
    if ($selectedTahun > 0) $whereConditions[] = "YEAR(periode_laporan) = {$selectedTahun}";
    $whereSql = implode(" AND ", $whereConditions);

    $sql = "SELECT * FROM laporan_omzet WHERE {$whereSql} ORDER BY periode_laporan DESC, id_laporan DESC";
    $res = $db->query($sql);
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            if ($outlet) {
                $r['nama_outlet'] = $outlet['nama_outlet'];
                $r['kode_outlet'] = $outlet['kode_outlet'];
            }
            $laporanList[] = $r;
            $totalOmzet += (float)$r['omzet'];
            $totalPotongan += (float)$r['nominal_potongan'];
        }
    }
}

$totalLaporan = count($laporanList);
$periodeLabelStr = ($selectedBulan > 0 ? ($bulanIndo[$selectedBulan] ?? '') . ' ' : '') . ($selectedTahun > 0 ? $selectedTahun : '');
if ($selectedBulan === 0 && $selectedTahun === 0) {
    $periodeLabelStr = 'Semua Periode';
}
?>

<div class="main-content-inner py-3 py-md-4">
    <!-- Header Title & Filters -->
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-3 mb-md-4 gap-3">
        <div>
            <h3 class="fw-bold text-body-emphasis mb-1">
                <i class="fa-solid fa-file-invoice-dollar me-2 text-danger"></i>Laporan Omzet & Rekapitulasi
            </h3>
            <p class="text-body-secondary mb-0">Arsip data laporan omzet toko dan rekapan potongan global</p>
        </div>

        <!-- Filter Bulan & Tahun Terpisah -->
        <div class="d-flex align-items-center gap-2 flex-wrap w-100 w-md-auto">
            <span class="text-body-secondary small fw-semibold d-none d-sm-inline"><i class="fa-light fa-filter me-1 text-danger"></i>Filter Periode:</span>
            <div class="d-flex gap-2 w-100 w-md-auto">
                <select class="form-select form-select-sm rounded-pill border-danger-subtle fw-semibold text-body shadow-sm bg-body" id="filterBulanLaporan" style="min-width: 130px;">
                    <option value="0" <?= ($selectedBulan === 0) ? 'selected' : ''; ?>>Semua Bulan</option>
                    <?php foreach ($bulanIndo as $mNum => $mName) : ?>
                        <option value="<?= $mNum; ?>" <?= ($selectedBulan === $mNum) ? 'selected' : ''; ?>>
                            <?= $mName; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select class="form-select form-select-sm rounded-pill border-danger-subtle fw-semibold text-body shadow-sm bg-body" id="filterTahunLaporan" style="min-width: 100px;">
                    <option value="0" <?= ($selectedTahun === 0) ? 'selected' : ''; ?>>Semua Tahun</option>
                    <?php foreach ($availableYears as $yVal) : ?>
                        <option value="<?= $yVal; ?>" <?= ($selectedTahun === $yVal) ? 'selected' : ''; ?>>
                            <?= $yVal; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Summary Metrics Cards -->
    <div class="row g-2 g-md-3 mb-4">
        <div class="col-6 col-xl-4">
            <div class="card border border-body-subtle shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body p-3 d-flex align-items-center gap-2 gap-md-3">
                    <div class="rounded-3 p-2 p-md-3 text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                        <i class="fa-light fa-file-lines fs-5"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-body-secondary small fw-semibold">Total Entry Laporan</div>
                        <div class="fs-5 fs-md-4 fw-bold text-body-emphasis mb-0"><?= number_format($totalLaporan, 0, ',', '.'); ?> <span class="fs-6 fw-normal text-body-secondary">Data</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-4">
            <div class="card border border-body-subtle shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body p-3 d-flex align-items-center gap-2 gap-md-3">
                    <div class="rounded-3 p-2 p-md-3 text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; background: linear-gradient(135deg, #198754 0%, #0d5132 100%);">
                        <i class="fa-light fa-money-bill-trend-up fs-5"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-body-secondary small fw-semibold">Total Omzet Penjualan</div>
                        <div class="fs-6 fs-md-5 fw-bold text-success mb-0">Rp <?= number_format($totalOmzet, 0, ',', '.'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card border border-body-subtle shadow-sm h-100" style="border-radius: 14px;">
                <div class="card-body p-3 d-flex align-items-center gap-2 gap-md-3">
                    <div class="rounded-3 p-2 p-md-3 text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; background: linear-gradient(135deg, #ffc107 0%, #d39e00 100%);">
                        <i class="fa-light fa-wallet fs-5 text-dark"></i>
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-body-secondary small fw-semibold">Total Bersih Toko</div>
                        <div class="fs-6 fs-md-5 fw-bold text-body-emphasis mb-0">Rp <?= number_format($totalOmzet - $totalPotongan, 0, ',', '.'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card border border-body-subtle shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-body py-3 px-3 px-md-4 d-flex align-items-center justify-content-between border-bottom border-body-subtle flex-wrap gap-2">
            <div>
                <h5 class="fw-bold text-body-emphasis mb-0 fs-6">
                    <i class="fa-solid fa-list me-2 text-danger"></i>Daftar Laporan Omzet (<?= htmlspecialchars($periodeLabelStr); ?>)
                </h5>
                <p class="text-body-secondary small mb-0">Data transaksi laporan omzet terdaftar di sistem</p>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 w-100">
                    <thead class="table-group-divider bg-body-secondary">
                        <tr class="text-uppercase small text-body-secondary">
                            <th class="py-3 ps-3 text-center fw-bold" style="width: 40px;">No</th>
                            <th class="py-3 px-3 fw-bold">Tanggal Omzet</th>
                            <th class="py-3 px-3 fw-bold">Outlet</th>
                            <th class="py-3 px-3 text-end fw-bold">Nominal Omzet</th>
                            <th class="py-3 px-3 text-end fw-bold text-danger">Potongan (10%)</th>
                            <th class="py-3 px-3 text-end fw-bold text-success">Bersih Outlet</th>
                        </tr>
                    </thead>
                    <tbody class="border-0">
                        <?php if (!empty($laporanList)) : ?>
                            <?php $no = 1; foreach ($laporanList as $r) : ?>
                                <?php 
                                    $omz = (float)$r['omzet'];
                                    $pot = (float)$r['nominal_potongan'];
                                    $bersih = $omz - $pot;
                                    $t = strtotime($r['periode_laporan']);
                                ?>
                                <tr>
                                    <td class="py-3 ps-3 text-center text-body-secondary fw-bold"><?= $no++; ?></td>
                                    <td class="py-3 px-3">
                                        <div class="fw-bold text-body-emphasis fs-6">
                                            <i class="fa-solid fa-calendar-days text-danger me-1"></i>
                                            <?= date('d M Y', $t); ?>
                                        </div>
                                        <small class="text-body-secondary"><?= date('H:i', strtotime($r['waktu_input'])); ?> WIB</small>
                                    </td>
                                    <td class="py-3 px-3">
                                        <div class="fw-semibold text-body-emphasis"><?= htmlspecialchars($r['nama_outlet'] ?? 'Outlet'); ?></div>
                                        <small class="text-body-secondary font-monospace"><?= htmlspecialchars($r['kode_outlet'] ?? '-'); ?></small>
                                    </td>
                                    <td class="py-3 px-3 text-end fw-bold text-body-emphasis">Rp <?= number_format($omz, 0, ',', '.'); ?></td>
                                    <td class="py-3 px-3 text-end fw-bold text-danger">Rp <?= number_format($pot, 0, ',', '.'); ?></td>
                                    <td class="py-3 px-3 text-end fw-extrabold text-success fs-6">Rp <?= number_format($bersih, 0, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6" class="text-center text-body-secondary py-5">
                                    <div class="py-3">
                                        <i class="fa-light fa-file-invoice-dollar text-body-secondary mb-3" style="font-size: 50px; opacity: 0.5;"></i>
                                        <h5 class="fw-bold text-body-secondary mb-1">Belum Ada Data Laporan</h5>
                                        <p class="text-body-secondary small mb-0">Belum ada omzet yang terdaftar pada periode pelaporan ini.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    function applyLaporanFilters() {
        const bVal = $('#filterBulanLaporan').val();
        const yVal = $('#filterTahunLaporan').val();
        window.location.href = '<?= SystemInfo::app('CLIENT_URL'); ?>/laporan?bulan=' + bVal + '&tahun=' + yVal;
    }

    $('#filterBulanLaporan, #filterTahunLaporan').on('change', function() {
        applyLaporanFilters();
    });
});
</script>
