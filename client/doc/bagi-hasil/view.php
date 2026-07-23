<?php
use Config\Core\Database;
use App\Models\User;

$user = User::user();
$db = Database::connect();
$userId = (int)($user['MBR_ID'] ?? $user['id_users'] ?? 0);

// Get Investor ID & percentage for logged in investor
$resInv = $db->query("SELECT id_investor, persen_bagian_investor FROM investor WHERE id_users = {$userId} LIMIT 1");
$investorId = 0;
$persenInvestor = 50.00;
if ($resInv && $resInv->num_rows > 0) {
    $rowInv = $resInv->fetch_assoc();
    $investorId = (int)$rowInv['id_investor'];
    $persenInvestor = (float)$rowInv['persen_bagian_investor'];
}

// Get global platform deduction percentage
$resSet = $db->query("SELECT nilai FROM pengaturan_sistem WHERE nama_pengaturan = 'potongan_global' LIMIT 1");
$potonganGlobal = 10.00;
if ($resSet && $resSet->num_rows > 0) {
    $potonganGlobal = (float)$resSet->fetch_assoc()['nilai'];
}

// Fetch breakdown per outlet for this investor
$sqlBagiHasil = "
    SELECT 
        o.id_outlet,
        o.kode_outlet,
        o.nama_outlet,
        IFNULL(SUM(l.omzet), 0) as total_omzet,
        IFNULL(SUM(l.nominal_potongan), 0) as total_potongan
    FROM outlet o
    LEFT JOIN laporan_omzet l ON o.id_outlet = l.id_outlet
    WHERE o.id_investor = {$investorId}
    GROUP BY o.id_outlet
    ORDER BY o.id_outlet DESC
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
        $potongan = (float)$row['total_potongan'];
        $omzetBersih = $omzet - $potongan;
        $hakInvestor = $omzetBersih * ($persenInvestor / 100.0);
        $hakOutlet = $omzetBersih - $hakInvestor;

        $row['omzet_bersih'] = $omzetBersih;
        $row['hak_investor'] = $hakInvestor;
        $row['hak_outlet'] = $hakOutlet;

        $totOmzet += $omzet;
        $totPotongan += $potongan;
        $totHakInvestor += $hakInvestor;
        $totHakOutlet += $hakOutlet;

        $rows[] = $row;
    }
}
$persenOutlet = 100.00 - $persenInvestor;
?>

<div class="main-content-inner py-3">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="fa-solid fa-vault me-2" style="color: #701416;"></i>Akumulasi & Rekap Bagi Hasil
            </h3>
            <p class="text-muted mb-0">Hitungan otomatis pembagian hak Investor dan Outlet dari total omzet bersih toko.</p>
        </div>
    </div>

    <!-- Calculation Rule Banner Card -->
    <div class="card border-0 shadow-sm rounded-4 text-white p-4 mb-4" style="background: linear-gradient(135deg, #701416 0%, #3d0a0b 100%);">
        <div class="row align-items-center g-3">
            <div class="col-lg-8 col-12">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill">
                        <i class="fa-solid fa-circle-info me-1"></i> Potongan Global Platform: <?= number_format($potonganGlobal, 2, ',', '.'); ?>%
                    </span>
                </div>
                <h4 class="fw-bold text-white mb-2">Kalkulasi Otomatis Pembagian Hak Investor & Outlet</h4>
                <p class="text-white-50 mb-0">Potongan komisi platform sebesar <?= number_format($potonganGlobal, 2, ',', '.'); ?>% dihitung otomatis dari setiap laporan omzet outlet. Sisa omzet bersih kemudian dibagi antara hak Investor dan hak Outlet.</p>
            </div>
            <div class="col-lg-4 col-12 text-lg-end">
                <div class="bg-white bg-opacity-10 rounded-3 p-3 text-start">
                    <span class="text-white-50 fs-6 d-block">Formula Pembagian Bagi Hasil:</span>
                    <span class="fw-bold fs-6 text-warning">Hak Investor (<?= number_format($persenInvestor, 0); ?>%) : Hak Outlet (<?= number_format($persenOutlet, 0); ?>%)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 3 Summary Breakdown Cards -->
    <div class="row g-3 mb-4">
        <div class="col-lg-4 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100" style="border-left: 4px solid #0d6efd !important;">
                <span class="text-muted fw-semibold fs-6">Total Kumpulan Potongan Platform</span>
                <h3 class="fw-bold text-dark mt-1 mb-1">Rp <?= number_format($totPotongan, 0, ',', '.'); ?></h3>
                <small class="text-muted">Dari akumulasi total omzet Rp <?= number_format($totOmzet, 0, ',', '.'); ?></small>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100" style="border-left: 4px solid #198754 !important;">
                <span class="text-muted fw-semibold fs-6">Hak Bagi Hasil Investor (<?= number_format($persenInvestor, 0); ?>%)</span>
                <h3 class="fw-bold text-success mt-1 mb-1">Rp <?= number_format($totHakInvestor, 0, ',', '.'); ?></h3>
                <small class="text-success"><i class="fa-solid fa-arrow-trend-up me-1"></i>Hak bersih milik Anda</small>
            </div>
        </div>
        <div class="col-lg-4 col-md-12 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100" style="border-left: 4px solid #ffc107 !important;">
                <span class="text-muted fw-semibold fs-6">Hak Bagi Hasil Outlet (<?= number_format($persenOutlet, 0); ?>%)</span>
                <h3 class="fw-bold text-warning mt-1 mb-1">Rp <?= number_format($totHakOutlet, 0, ',', '.'); ?></h3>
                <small class="text-muted">Didistribusikan ke seluruh outlet</small>
            </div>
        </div>
    </div>

    <!-- Breakdown Table Per Outlet -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom">
            <h5 class="fw-bold text-dark mb-0 fs-6">Rincian Bagi Hasil Per Outlet (Periode Juli 2026)</h5>
            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">Status: Terhitung Otomatis</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive table-responsive-mobile">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary border-bottom">
                        <tr>
                            <th class="py-3 px-4 text-center fw-bold text-dark">No</th>
                            <th class="py-3 px-3 fw-bold text-dark">Kode & Nama Outlet</th>
                            <th class="py-3 px-3 text-end fw-bold text-dark">Omzet Reported</th>
                            <th class="py-3 px-3 text-end fw-bold text-dark">Potongan (5%)</th>
                            <th class="py-3 px-3 text-end fw-bold text-success">Hak Investor (50%)</th>
                            <th class="py-3 px-3 text-end fw-bold text-warning">Hak Outlet (50%)</th>
                            <th class="py-3 px-4 text-center fw-bold text-dark">Status</th>
                        </tr>
                    </thead>
                    <tbody class="border-0">
                        <?php if (!empty($rows)) : ?>
                            <?php $no = 1; foreach ($rows as $r) : ?>
                                <tr>
                                    <td class="py-3 px-4 text-center text-muted font-monospace"><?= $no++; ?></td>
                                    <td class="py-3 px-3">
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($r['nama_outlet']); ?></div>
                                        <small class="text-muted font-monospace"><?= htmlspecialchars($r['kode_outlet']); ?></small>
                                    </td>
                                    <td class="py-3 px-3 text-end fw-bold text-dark">Rp <?= number_format($r['total_omzet'], 0, ',', '.'); ?></td>
                                    <td class="py-3 px-3 text-end fw-semibold text-danger">Rp <?= number_format($r['total_potongan'], 0, ',', '.'); ?></td>
                                    <td class="py-3 px-3 text-end fw-bold text-success">Rp <?= number_format($r['hak_investor'], 0, ',', '.'); ?></td>
                                    <td class="py-3 px-3 text-end fw-bold text-warning">Rp <?= number_format($r['hak_outlet'], 0, ',', '.'); ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">Terhitung</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Belum ada data outlet atau omzet yang dapat direkap.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="bg-light fw-bold">
                        <tr>
                            <td colspan="2" class="py-3 px-4 text-end text-dark">TOTAL KESELURUHAN:</td>
                            <td class="py-3 px-3 text-end text-dark">Rp <?= number_format($totOmzet, 0, ',', '.'); ?></td>
                            <td class="py-3 px-3 text-end text-danger">Rp <?= number_format($totPotongan, 0, ',', '.'); ?></td>
                            <td class="py-3 px-3 text-end text-success">Rp <?= number_format($totHakInvestor, 0, ',', '.'); ?></td>
                            <td class="py-3 px-3 text-end text-warning">Rp <?= number_format($totHakOutlet, 0, ',', '.'); ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
