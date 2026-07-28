<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();

// Get global potongan setting
$resSet = $db->query("SELECT nilai FROM pengaturan_sistem WHERE nama_pengaturan = 'potongan_global' LIMIT 1");
$potonganGlobal = 10.00;
if ($resSet && $resSet->num_rows > 0) {
    $potonganGlobal = (float)$resSet->fetch_assoc()['nilai'];
}

// Nama bulan Bahasa Indonesia
$bulanIndo = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Filter bulan & tahun
$selectedBulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : 0;
$selectedTahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

// Ambil daftar tahun yang tersedia
$resYears = $db->query("SELECT DISTINCT YEAR(periode_laporan) as y FROM laporan_omzet ORDER BY y DESC");
$availableYears = [];
if ($resYears) {
    while ($y = $resYears->fetch_assoc()) {
        $availableYears[] = (int)$y['y'];
    }
}
if (!in_array((int)date('Y'), $availableYears)) {
    array_unshift($availableYears, (int)date('Y'));
}

// Build WHERE
$whereConditions = [];
if ($selectedBulan > 0) {
    $whereConditions[] = "MONTH(l.periode_laporan) = {$selectedBulan}";
}
if ($selectedTahun > 0) {
    $whereConditions[] = "YEAR(l.periode_laporan) = {$selectedTahun}";
}
$whereSql = !empty($whereConditions) ? "AND " . implode(" AND ", $whereConditions) : "";

// Query: hitung bagi hasil per outlet dari laporan_omzet langsung
// Programmer melihat SEMUA outlet & semua investor
$sqlBagiHasil = "
    SELECT
        o.id_outlet,
        o.nama_outlet,
        u_kasir.nama_lengkap as pengelola,
        u_inv.nama_lengkap as nama_investor,
        inv.persen_bagian_investor,
        o.persentase_potongan,
        IFNULL(SUM(l.omzet), 0) as total_omzet,
        IFNULL(SUM(l.nominal_potongan), 0) as total_potongan_db
    FROM outlet o
    LEFT JOIN users u_kasir ON u_kasir.id_users = o.id_users
    LEFT JOIN investor inv ON inv.id_investor = o.id_investor
    LEFT JOIN users u_inv ON u_inv.id_users = inv.id_users
    LEFT JOIN laporan_omzet l ON l.id_outlet = o.id_outlet {$whereSql}
    GROUP BY o.id_outlet, o.nama_outlet, u_kasir.nama_lengkap,
             u_inv.nama_lengkap, inv.persen_bagian_investor, o.persentase_potongan
    ORDER BY u_inv.nama_lengkap ASC, o.nama_outlet ASC
";

$resBagiHasil = $db->query($sqlBagiHasil);

$rows = [];
$totOmzet = 0;
$totPotongan = 0;
$totHakInvestor = 0;
$totHakOutlet = 0;

if ($resBagiHasil) {
    while ($row = $resBagiHasil->fetch_assoc()) {
        $omzet = (float)$row['total_omzet'];
        $persen = (float)($row['persen_bagian_investor'] ?? 50.00);
        $persenOutlet = 100.00 - $persen;

        $potonganOutlet = (float)($row['persentase_potongan'] ?? $potonganGlobal);
        $potongan = round($omzet * ($potonganOutlet / 100.0), 2);
        $hakInvestor = round($potongan * ($persen / 100.0), 2);
        $hakOutlet   = round($potongan * ($persenOutlet / 100.0), 2);

        $row['potongan_hitung'] = $potongan;
        $row['hak_investor']    = $hakInvestor;
        $row['hak_outlet']      = $hakOutlet;

        $totOmzet       += $omzet;
        $totPotongan    += $potongan;
        $totHakInvestor += $hakInvestor;
        $totHakOutlet   += $hakOutlet;

        $rows[] = $row;
    }
}

$periodeLabel = ($selectedBulan > 0 ? ($bulanIndo[$selectedBulan] ?? '-') . ' ' : 'Semua Bulan ') . $selectedTahun;
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Rekapitulasi Bagi Hasil</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Bagi Hasil</li>
        </ol>
    </div>
</div>

<!-- Filter Card -->
<div class="row row-sm mb-3">
    <div class="col-lg-12">
        <div class="card custom-card">
            <div class="card-header">
                <h6 class="main-content-label mb-0">Filter Periode</h6>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Bulan</label>
                        <select name="bulan" class="form-control">
                            <option value="0">Semua Bulan</option>
                            <?php foreach ($bulanIndo as $num => $nama) : ?>
                                <option value="<?= $num ?>" <?= $selectedBulan == $num ? 'selected' : '' ?>><?= $nama ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Tahun</label>
                        <select name="tahun" class="form-control">
                            <?php foreach ($availableYears as $y) : ?>
                                <option value="<?= $y ?>" <?= $selectedTahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button>
                    </div>
                    <div class="col-md-2">
                        <a href="<?= SystemInfo::app('ADMIN_URL') ?>/bagi-hasil/view" class="btn btn-secondary w-100"><i class="fas fa-redo me-1"></i> Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row row-sm mb-3">
    <div class="col-sm-6 col-lg-3">
        <div class="card custom-card bg-primary text-white">
            <div class="card-body text-center">
                <p class="mb-1 fs-12 fw-bold opacity-75">Total Omzet</p>
                <h5 class="mb-0 fw-bold">Rp <?= number_format($totOmzet, 0, ',', '.') ?></h5>
                <small class="opacity-75"><?= $periodeLabel ?></small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card custom-card bg-danger text-white">
            <div class="card-body text-center">
                <p class="mb-1 fs-12 fw-bold opacity-75">Total Potongan (<?= number_format($potonganGlobal, 0) ?>%)</p>
                <h5 class="mb-0 fw-bold">Rp <?= number_format($totPotongan, 0, ',', '.') ?></h5>
                <small class="opacity-75"><?= $periodeLabel ?></small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card custom-card bg-success text-white">
            <div class="card-body text-center">
                <p class="mb-1 fs-12 fw-bold opacity-75">Total Hak Investor</p>
                <h5 class="mb-0 fw-bold">Rp <?= number_format($totHakInvestor, 0, ',', '.') ?></h5>
                <small class="opacity-75"><?= $periodeLabel ?></small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card custom-card bg-info text-white">
            <div class="card-body text-center">
                <p class="mb-1 fs-12 fw-bold opacity-75">Total Hak Outlet</p>
                <h5 class="mb-0 fw-bold">Rp <?= number_format($totHakOutlet, 0, ',', '.') ?></h5>
                <small class="opacity-75"><?= $periodeLabel ?></small>
            </div>
        </div>
    </div>
</div>

<!-- Main Table -->
<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="main-content-label mb-1">Rincian Bagi Hasil Per Outlet</h6>
                    <p class="text-muted card-sub-title mb-0">
                        Perhitungan hak investor &amp; outlet dari seluruh cabang terdaftar &mdash; periode <strong><?= $periodeLabel ?></strong>.
                        Potongan platform: <strong><?= number_format($potonganGlobal, 0) ?>%</strong>.
                    </p>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100 align-middle" id="bagi-hasil-table">
                        <thead>
                            <tr class="text-center">
                                <th style="width: 5%;">No</th>
                                <th>Nama Outlet</th>
                                <th>Pengelola (Kasir)</th>
                                <th>Investor Pemodal</th>
                                <th>Bagi Hasil (%)</th>
                                <th class="text-end">Total Omzet (Rp)</th>
                                <th class="text-end">Potongan <?= number_format($potonganGlobal, 0) ?>% (Rp)</th>
                                <th class="text-end">Hak Investor (Rp)</th>
                                <th class="text-end">Hak Outlet (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($rows)) : ?>
                                <?php $no = 1; foreach ($rows as $row) : ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td><strong class="text-primary"><?= htmlspecialchars($row['nama_outlet']) ?></strong></td>
                                        <td><?= htmlspecialchars($row['pengelola'] ?? '-') ?></td>
                                        <td>
                                            <?php if (!empty($row['nama_investor'])) : ?>
                                                <span class="badge bg-info"><?= htmlspecialchars($row['nama_investor']) ?></span>
                                            <?php else : ?>
                                                <span class="badge bg-warning">Belum Ada Pemodal</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary fs-6"><?= number_format($row['persen_bagian_investor'] ?? 50, 0) ?>%</span>
                                        </td>
                                        <td class="text-end fw-bold"><?= (float)$row['total_omzet'] > 0 ? 'Rp ' . number_format($row['total_omzet'], 0, ',', '.') : '<span class="text-muted">-</span>' ?></td>
                                        <td class="text-end text-danger"><?= $row['potongan_hitung'] > 0 ? 'Rp ' . number_format($row['potongan_hitung'], 0, ',', '.') : '<span class="text-muted">-</span>' ?></td>
                                        <td class="text-end fw-bold text-success"><?= $row['hak_investor'] > 0 ? 'Rp ' . number_format($row['hak_investor'], 0, ',', '.') : '<span class="text-muted">-</span>' ?></td>
                                        <td class="text-end fw-bold text-info"><?= $row['hak_outlet'] > 0 ? 'Rp ' . number_format($row['hak_outlet'], 0, ',', '.') : '<span class="text-muted">-</span>' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">Belum ada data omzet untuk periode ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if (!empty($rows)) : ?>
                        <tfoot>
                            <tr class="fw-bold bg-light">
                                <td colspan="6" class="text-end">TOTAL:</td>
                                <td class="text-end">Rp <?= number_format($totOmzet, 0, ',', '.') ?></td>
                                <td class="text-end text-danger">Rp <?= number_format($totPotongan, 0, ',', '.') ?></td>
                                <td class="text-end text-success">Rp <?= number_format($totHakInvestor, 0, ',', '.') ?></td>
                                <td class="text-end text-info">Rp <?= number_format($totHakOutlet, 0, ',', '.') ?></td>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#bagi-hasil-table')) {
        $('#bagi-hasil-table').DataTable({
            processing: true,
            deferRender: true,
            scrollX: true,
            lengthMenu: [
                [10, 50, 100, -1],
                [10, 50, 100, "All"]
            ],
            language: {
                searchPlaceholder: 'Cari bagi hasil...',
                sSearch: '',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                paginate: {
                    first: 'First',
                    last: 'Last',
                    next: 'Next',
                    previous: 'Previous'
                }
            },
            order: [[4, 'asc'], [2, 'asc']]
        });
    }
});
</script>
