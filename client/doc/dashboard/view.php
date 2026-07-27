<?php
use Config\Core\Database;
use App\Models\User;
use Config\Core\SystemInfo;

$user = User::user();
$db = Database::connect();
$role = strtolower($user['role'] ?? 'investor');
$userId = (int)($user['MBR_ID'] ?? $user['id_users'] ?? 0);

if ($role === 'master') {
    // -------------------------------------------------------------
    // DASHBOARD MASTER OWNER (NON-KEUANGAN + 2 CARD + LIMIT TABLES)
    // -------------------------------------------------------------
    $countInvestor = $db->query("SELECT COUNT(*) as total FROM investor WHERE id_master = {$userId} OR id_master IS NULL")->fetch_assoc()['total'] ?? 0;
    $countOutlet   = $db->query("
        SELECT COUNT(*) as total FROM outlet o 
        JOIN investor i ON i.id_investor = o.id_investor 
        WHERE i.id_master = {$userId} OR i.id_master IS NULL
    ")->fetch_assoc()['total'] ?? 0;

    // Fetch Investors (Limit 5)
    $listInvestors = $db->query("
        SELECT u.nama_lengkap, u.no_hp, i.alamat_investor, i.tanggal_bergabung, COUNT(o.id_outlet) as total_outlet
        FROM investor i
        JOIN users u ON u.id_users = i.id_users
        LEFT JOIN outlet o ON o.id_investor = i.id_investor
        WHERE i.id_master = {$userId} OR i.id_master IS NULL
        GROUP BY i.id_investor
        ORDER BY i.id_investor DESC LIMIT 5
    ");

    // Fetch Outlets (Limit 5)
    $listOutlets = $db->query("
        SELECT o.nama_outlet, o.kecamatan, o.alamat_outlet, o.tanggal_bergabung, u_inv.nama_lengkap as nama_investor
        FROM outlet o
        JOIN investor i ON i.id_investor = o.id_investor
        JOIN users u_inv ON u_inv.id_users = i.id_users
        WHERE i.id_master = {$userId} OR i.id_master IS NULL
        ORDER BY o.id_outlet DESC LIMIT 5
    ");
?>
<div class="row row-sm mb-4">
    <div class="col-12">
        <div class="card custom-card bg-primary text-white shadow-sm border-0">
            <div class="card-body p-4">
                <h3 class="fw-bold mb-1">Selamat Datang, <?= htmlspecialchars($user['MBR_NAME'] ?? 'Master Owner') ?>!</h3>
                <p class="mb-0 opacity-75 fs-14">Portal Pemantauan Master Owner Toko Madura (Non-Keuangan & Manajemen Partner)</p>
            </div>
        </div>
    </div>
</div>

<!-- 2 CARD INDIKATOR UTAMA MASTER OWNER -->
<div class="row row-sm mb-4">
    <div class="col-md-6 mb-3 mb-md-0">
        <div class="card custom-card border-0 shadow-sm" style="border-left: 5px solid #0d6efd !important;">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <span class="text-muted fw-bold text-uppercase fs-12">Total Investor Saya</span>
                    <h2 class="fw-bold text-primary mb-0 mt-1"><?= $countInvestor ?></h2>
                    <small class="text-muted">Investor Pemodal Terdaftar</small>
                </div>
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                    <i class="fa-light fa-users fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card custom-card border-0 shadow-sm" style="border-left: 5px solid #198754 !important;">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <span class="text-muted fw-bold text-uppercase fs-12">Total Outlet Cabang</span>
                    <h2 class="fw-bold text-success mb-0 mt-1"><?= $countOutlet ?></h2>
                    <small class="text-muted">Cabang Outlet Terdaftar</small>
                </div>
                <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success">
                    <i class="fa-light fa-store fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TABEL RINGKASAN INVESTOR & OUTLET (WITH LIMIT & PAGINATION VIEW) -->
<div class="row row-sm">
    <!-- TABLE INVESTOR -->
    <div class="col-lg-6 mb-4">
        <div class="card custom-card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="main-content-label mb-0 text-primary"><i class="fa-light fa-users me-2"></i>Ringkasan Investor Pemodal</h6>
                <a href="<?= SystemInfo::app('CLIENT_URL') ?>/investor" class="btn btn-outline-primary btn-sm">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Investor</th>
                                <th>Lokasi Alamat</th>
                                <th class="text-center">Jumlah Outlet</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($listInvestors && $listInvestors->num_rows > 0) : ?>
                                <?php $no = 1; while ($inv = $listInvestors->fetch_assoc()) : ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td>
                                            <strong class="text-dark"><?= htmlspecialchars($inv['nama_lengkap']) ?></strong>
                                            <br><small class="text-muted"><i class="fa-light fa-phone me-1"></i><?= htmlspecialchars($inv['no_hp']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($inv['alamat_investor'] ?? '-') ?></td>
                                        <td class="text-center"><span class="badge bg-primary rounded-pill"><?= $inv['total_outlet'] ?> Outlet</span></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr><td colspan="4" class="text-center py-3 text-muted">Belum ada investor.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLE OUTLET -->
    <div class="col-lg-6 mb-4">
        <div class="card custom-card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="main-content-label mb-0 text-success"><i class="fa-light fa-store me-2"></i>Ringkasan Cabang Outlet</h6>
                <a href="<?= SystemInfo::app('CLIENT_URL') ?>/investor" class="btn btn-outline-success btn-sm">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Outlet</th>
                                <th>Lokasi (Kecamatan)</th>
                                <th>Pemilik (Investor)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($listOutlets && $listOutlets->num_rows > 0) : ?>
                                <?php $no = 1; while ($out = $listOutlets->fetch_assoc()) : ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td>
                                            <strong class="text-dark"><?= htmlspecialchars($out['nama_outlet']) ?></strong>
                                        </td>
                                        <td><?= htmlspecialchars($out['kecamatan'] ?? $out['alamat_outlet'] ?? '-') ?></td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($out['nama_investor']) ?></span></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr><td colspan="4" class="text-center py-3 text-muted">Belum ada outlet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
} else if ($role === 'investor') {
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
        SELECT IFNULL(SUM(lo.omzet), 0) as total_omzet, IFNULL(SUM(lo.nominal_potongan), 0) as total_potongan
        FROM laporan_omzet lo
        JOIN outlet o ON o.id_outlet = lo.id_outlet
        WHERE o.id_investor = {$investorId}
    ")->fetch_assoc();

    $totalOmzet = (float)($resOmzetTot['total_omzet'] ?? 0);
    $totalPotongan = (float)($resOmzetTot['total_potongan'] ?? 0);
    $omzetBersih = $totalOmzet - $totalPotongan;
    $hakInvestor = $omzetBersih * ($persenInvestor / 100.0);

    $resRecent = $db->query("
        SELECT o.nama_outlet, o.kode_outlet, lo.periode_laporan, lo.omzet, lo.nominal_potongan, lo.waktu_input
        FROM laporan_omzet lo
        JOIN outlet o ON o.id_outlet = lo.id_outlet
        WHERE o.id_investor = {$investorId}
        ORDER BY lo.waktu_input DESC LIMIT 5
    ");
?>
<div class="row row-sm mb-4">
    <div class="col-12">
        <div class="card custom-card bg-primary text-white shadow-sm border-0">
            <div class="card-body p-4">
                <h3 class="fw-bold mb-1">Selamat Datang, <?= htmlspecialchars($user['MBR_NAME'] ?? 'Investor') ?>!</h3>
                <p class="mb-0 opacity-75 fs-14">Portal Pemantauan Kinerja & Bagi Hasil Cabang Toko Madura</p>
            </div>
        </div>
    </div>
</div>

<div class="row row-sm mb-4">
    <div class="col-md-4 mb-3 mb-md-0">
        <div class="card custom-card border-0 shadow-sm">
            <div class="card-body p-4">
                <span class="text-muted fw-bold text-uppercase fs-12">Total Cabang Outlet</span>
                <h2 class="fw-bold text-primary mb-0 mt-1"><?= $resOutletCount ?> Outlet</h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3 mb-md-0">
        <div class="card custom-card border-0 shadow-sm">
            <div class="card-body p-4">
                <span class="text-muted fw-bold text-uppercase fs-12">Akumulasi Omzet Kotor</span>
                <h2 class="fw-bold text-dark mb-0 mt-1">Rp <?= number_format($totalOmzet, 0, ',', '.') ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card custom-card border-0 shadow-sm">
            <div class="card-body p-4">
                <span class="text-muted fw-bold text-uppercase fs-12">Estimasi Hak Investor (<?= $persenInvestor ?>%)</span>
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
    $resOut = $db->query("SELECT id_outlet, kode_outlet, nama_outlet FROM outlet WHERE id_users = {$userId} LIMIT 1")->fetch_assoc();
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
        <div class="card custom-card bg-success text-white shadow-sm border-0">
            <div class="card-body p-4">
                <h3 class="fw-bold mb-1">Selamat Datang, <?= htmlspecialchars($resOut['nama_outlet'] ?? 'Outlet') ?>!</h3>
                <p class="mb-0 opacity-75 fs-14">Portal Input & Pelaporan Omzet Toko Madura</p>
            </div>
        </div>
    </div>
</div>

<div class="row row-sm mb-4">
    <div class="col-md-6 mb-3 mb-md-0">
        <div class="card custom-card border-0 shadow-sm">
            <div class="card-body p-4">
                <span class="text-muted fw-bold text-uppercase fs-12">Total Laporan Inisiasi</span>
                <h2 class="fw-bold text-primary mb-0 mt-1"><?= $totalLaporan ?> Laporan</h2>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card custom-card border-0 shadow-sm">
            <div class="card-body p-4">
                <span class="text-muted fw-bold text-uppercase fs-12">Total Omzet Terinput</span>
                <h2 class="fw-bold text-success mb-0 mt-1">Rp <?= number_format($totalOmzet, 0, ',', '.') ?></h2>
            </div>
        </div>
    </div>
</div>
<?php } ?>
