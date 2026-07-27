<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();

// -------------------------------------------------------------------------
// DASHBOARD GENERAL PROGRAMMER / ADMIN STAF (ADMIN PORTAL)
// -------------------------------------------------------------------------
$investorCount = $db->query("SELECT COUNT(*) as total FROM investor")->fetch_assoc()['total'] ?? 0;
$outletCount   = $db->query("SELECT COUNT(*) as total FROM outlet")->fetch_assoc()['total'] ?? 0;

$omzetSumResult = $db->query("SELECT SUM(omzet) as total FROM laporan_omzet")->fetch_assoc();
$totalOmzet     = $omzetSumResult['total'] ?? 0;

// Hitung total potongan 10% dari omzet
$potonganSumResult = $db->query("SELECT SUM(nominal_potongan) as total FROM laporan_omzet")->fetch_assoc();
$totalPotongan     = $potonganSumResult['total'] ?? 0;

$omzetBersih  = $totalOmzet - $totalPotongan;
$hakInvestor  = $omzetBersih * 0.50;
$hakOutlet    = $omzetBersih * 0.50;

// Top 5 Outlet berdasarkan Omzet
$topOutlets = $db->query("
    SELECT o.nama_outlet, o.kode_outlet, SUM(l.omzet) as total_omzet
    FROM laporan_omzet l
    JOIN outlet o ON l.id_outlet = o.id_outlet
    GROUP BY l.id_outlet
    ORDER BY total_omzet DESC
    LIMIT 5
");

// 5 Transaksi Omzet Terbaru
$recentOmzet = $db->query("
    SELECT l.*, o.nama_outlet, o.kode_outlet
    FROM laporan_omzet l
    JOIN outlet o ON l.id_outlet = o.id_outlet
    ORDER BY l.waktu_input DESC
    LIMIT 5
");
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Dashboard Administrator</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
        </ol>
    </div>
</div>

<!-- Row Stat Cards -->
<div class="row row-sm">
    <div class="col-sm-6 col-lg-3">
        <div class="card custom-card bg-primary text-white">
            <div class="card-body text-center">
                <p class="mb-1 fs-12 fw-bold opacity-75">Total Investor</p>
                <h3 class="mb-0 fw-bold"><?= number_format($investorCount) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card custom-card bg-success text-white">
            <div class="card-body text-center">
                <p class="mb-1 fs-12 fw-bold opacity-75">Total Outlet Cabang</p>
                <h3 class="mb-0 fw-bold"><?= number_format($outletCount) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card custom-card bg-warning text-white">
            <div class="card-body text-center">
                <p class="mb-1 fs-12 fw-bold opacity-75">Akumulasi Omzet Kotor</p>
                <h4 class="mb-0 fw-bold">Rp <?= number_format($totalOmzet, 0, ',', '.') ?></h4>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card custom-card bg-info text-white">
            <div class="card-body text-center">
                <p class="mb-1 fs-12 fw-bold opacity-75">Total Potongan (10%)</p>
                <h4 class="mb-0 fw-bold">Rp <?= number_format($totalPotongan, 0, ',', '.') ?></h4>
            </div>
        </div>
    </div>
</div>

<!-- Row Summary Tables -->
<div class="row row-sm">
    <div class="col-lg-6">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="main-content-label mb-0">Top 5 Outlet Omzet Tertinggi</h6>
                <a href="<?= SystemInfo::app('ADMIN_URL') ?>/omzet/view" class="btn btn-outline-primary btn-sm">Lihat Semua</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Outlet</th>
                                <th class="text-end">Total Omzet</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($topOutlets && $topOutlets->num_rows > 0) : ?>
                                <?php $no = 1; while ($row = $topOutlets->fetch_assoc()) : ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td>
                                            <strong class="text-primary"><?= htmlspecialchars($row['nama_outlet']) ?></strong>
                                            <br><small class="text-muted"><?= htmlspecialchars($row['kode_outlet']) ?></small>
                                        </td>
                                        <td class="text-end fw-bold text-success">Rp <?= number_format($row['total_omzet'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">Belum ada data omzet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="main-content-label mb-0">Transaksi Omzet Terbaru</h6>
                <a href="<?= SystemInfo::app('ADMIN_URL') ?>/omzet/view" class="btn btn-outline-primary btn-sm">Lihat Semua</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Outlet</th>
                                <th>Periode</th>
                                <th class="text-end">Omzet</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recentOmzet && $recentOmzet->num_rows > 0) : ?>
                                <?php while ($row = $recentOmzet->fetch_assoc()) : ?>
                                    <tr>
                                        <td>
                                            <strong class="text-primary"><?= htmlspecialchars($row['nama_outlet']) ?></strong>
                                        </td>
                                        <td><?= date("M Y", strtotime($row['periode_laporan'])) ?></td>
                                        <td class="text-end fw-bold">Rp <?= number_format($row['omzet'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">Belum ada transaksi omzet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
