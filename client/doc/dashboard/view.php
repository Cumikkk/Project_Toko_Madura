<?php
use Config\Core\Database;
use App\Models\User;
use Config\Core\SystemInfo;

$user = User::user();
$db = Database::connect();
$role = strtolower($user['role'] ?? 'investor');
$userId = (int)($user['MBR_ID'] ?? $user['id_users'] ?? 0);

if ($role === 'master') {
    echo "<script>window.location.href = '" . SystemInfo::app('CLIENT_URL') . "/investor';</script>";
    exit;
}

if ($role === 'investor') {
    // -------------------------------------------------------------
    // DASHBOARD INVESTOR
    // -------------------------------------------------------------
    $resInv = $db->query("SELECT id_investor, persen_bagian_investor FROM investor WHERE id_users = {$userId} LIMIT 1");
    $investorId = 0;
    $persenInvestor = 50.00;
    if ($resInv && $resInv->num_rows > 0) {
        $rowInv = $resInv->fetch_assoc();
        $investorId = (int)$rowInv['id_investor'];
        $persenInvestor = (float)$rowInv['persen_bagian_investor'];
    }

    $resOutletCount = $db->query("SELECT COUNT(*) as total FROM outlet WHERE id_investor = {$investorId}")->fetch_assoc()['total'] ?? 0;
    $resOmzetTot = $db->query("
        SELECT 
            IFNULL(SUM(lo.omzet), 0) as total_omzet, 
            IFNULL(SUM(lo.nominal_potongan), 0) as total_potongan,
            IFNULL(SUM(lo.nominal_potongan * (o.persen_bagian_investor / 100.0)), 0) as total_hak_investor
        FROM laporan_omzet lo
        JOIN outlet o ON o.id_outlet = lo.id_outlet
        WHERE o.id_investor = {$investorId}
    ")->fetch_assoc();

    $totalOmzet = (float)($resOmzetTot['total_omzet'] ?? 0);
    $totalPotongan = (float)($resOmzetTot['total_potongan'] ?? 0);
    $omzetBersih = $totalOmzet - $totalPotongan;
    $hakInvestor = (float)($resOmzetTot['total_hak_investor'] ?? 0);

    $resRecent = $db->query("
        SELECT o.nama_outlet, lo.periode_laporan, lo.omzet, lo.nominal_potongan, lo.waktu_input
        FROM laporan_omzet lo
        JOIN outlet o ON o.id_outlet = lo.id_outlet
        WHERE o.id_investor = {$investorId}
        ORDER BY lo.waktu_input DESC LIMIT 5
    ");
?>
<div class="row row-sm mb-4">
    <div class="col-12">
        <div class="card custom-card bg-primary text-white shadow-sm border-0" style="border-radius: 16px;">
            <div class="card-body p-4">
                <h3 class="fw-bold mb-1">Selamat Datang, <?= htmlspecialchars($user['MBR_NAME'] ?? 'Investor') ?>!</h3>
                <p class="mb-0 opacity-75 fs-14">Portal Pemantauan Kinerja & Bagi Hasil Cabang Toko Madura</p>
            </div>
        </div>
    </div>
</div>

<div class="row row-sm mb-4">
    <div class="col-md-4 mb-3 mb-md-0">
        <div class="card custom-card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-body p-4">
                <span class="text-muted fw-bold text-uppercase fs-12">Total Cabang Outlet</span>
                <h2 class="fw-bold text-primary mb-0 mt-1"><?= $resOutletCount ?> Outlet</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3 mb-md-0">
        <div class="card custom-card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-body p-4">
                <span class="text-muted fw-bold text-uppercase fs-12">Akumulasi Omzet Kotor</span>
                <h2 class="fw-bold text-dark mb-0 mt-1">Rp <?= number_format($totalOmzet, 0, ',', '.') ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card custom-card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-body p-4">
                <span class="text-muted fw-bold text-uppercase fs-12">Estimasi Hak Investor</span>
                <h2 class="fw-bold text-success mb-0 mt-1">Rp <?= number_format($hakInvestor, 0, ',', '.') ?></h2>
            </div>
        </div>
    </div>
</div>
<?php
} else {
    // -------------------------------------------------------------
    // DASHBOARD OUTLET (KASIR)
    // -------------------------------------------------------------
    $resOut = $db->query("SELECT id_outlet, nama_outlet FROM outlet WHERE id_users = {$userId} LIMIT 1")->fetch_assoc();
    $outletId = (int)($resOut['id_outlet'] ?? 0);

    $resOutletOmzet = $db->query("
        SELECT COUNT(*) as total_laporan, IFNULL(SUM(omzet), 0) as total_omzet, IFNULL(SUM(nominal_potongan), 0) as total_potongan
        FROM laporan_omzet WHERE id_outlet = {$outletId}
    ")->fetch_assoc();

    $totalLaporan = (int)($resOutletOmzet['total_laporan'] ?? 0);
    $totalOmzet = (float)($resOutletOmzet['total_omzet'] ?? 0);
?>
<div class="row row-sm mb-4">
    <div class="col-12">
        <div class="card custom-card bg-success text-white shadow-sm border-0" style="border-radius: 16px;">
            <div class="card-body p-4">
                <h3 class="fw-bold mb-1">Selamat Datang, <?= htmlspecialchars($resOut['nama_outlet'] ?? 'Outlet') ?>!</h3>
                <p class="mb-0 opacity-75 fs-14">Portal Input & Pelaporan Omzet Toko Madura</p>
            </div>
        </div>
    </div>
</div>

<div class="row row-sm mb-4">
    <div class="col-md-6 mb-3 mb-md-0">
        <div class="card custom-card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-body p-4">
                <span class="text-muted fw-bold text-uppercase fs-12">Total Laporan Inisiasi</span>
                <h2 class="fw-bold text-primary mb-0 mt-1"><?= $totalLaporan ?> Laporan</h2>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card custom-card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-body p-4">
                <span class="text-muted fw-bold text-uppercase fs-12">Total Omzet Terinput</span>
                <h2 class="fw-bold text-success mb-0 mt-1">Rp <?= number_format($totalOmzet, 0, ',', '.') ?></h2>
            </div>
        </div>
    </div>
</div>
<?php } ?>
