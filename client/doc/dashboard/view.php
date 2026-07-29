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
    // DASHBOARD MASTER OWNER (3 STAT CARDS + DETAILED TABLES)
    // -------------------------------------------------------------
    $countInvestor = $db->query("SELECT COUNT(*) as total FROM investor WHERE id_master = {$userId} OR id_master IS NULL")->fetch_assoc()['total'] ?? 0;
    $countOutlet   = $db->query("
        SELECT COUNT(*) as total FROM outlet o 
        JOIN investor i ON i.id_investor = o.id_investor 
        WHERE i.id_master = {$userId} OR i.id_master IS NULL
    ")->fetch_assoc()['total'] ?? 0;

    $resKeuntunganMaster = $db->query("
        SELECT IFNULL(SUM((lo.omzet - lo.nominal_potongan) * (IFNULL(i.persen_bagian_master, 5.00) / 100.0)), 0) as total_keuntungan
        FROM laporan_omzet lo
        JOIN outlet o ON o.id_outlet = lo.id_outlet
        JOIN investor i ON i.id_investor = o.id_investor
        WHERE i.id_master = {$userId} OR i.id_master IS NULL
    ")->fetch_assoc();
    $totalKeuntunganMaster = (float)($resKeuntunganMaster['total_keuntungan'] ?? 0);

    // Fetch Investors (Limit 5)
    $listInvestors = $db->query("
        SELECT u.nama_lengkap, u.no_hp, i.kecamatan, i.alamat_investor, i.id_investor
        FROM investor i
        JOIN users u ON u.id_users = i.id_users
        WHERE i.id_master = {$userId} OR i.id_master IS NULL
        ORDER BY i.id_investor DESC LIMIT 5
    ");

    // Fetch Outlets (Limit 5)
    $listOutlets = $db->query("
        SELECT o.id_outlet, o.nama_outlet, o.kecamatan as kecamatan_outlet, o.alamat_outlet, o.tanggal_bergabung,
               u_inv.nama_lengkap as nama_investor, i.kecamatan as kecamatan_investor, i.alamat_investor
        FROM outlet o
        JOIN investor i ON i.id_investor = o.id_investor
        JOIN users u_inv ON u_inv.id_users = i.id_users
        WHERE i.id_master = {$userId} OR i.id_master IS NULL
        ORDER BY o.id_outlet DESC LIMIT 5
    ");
?>
<div class="row row-sm mb-4">
    <div class="col-12">
        <div class="card custom-card text-white shadow-sm border-0" style="background: linear-gradient(135deg, #7D0A0A 0%, #4D0709 100%); border-radius: 16px;">
            <div class="card-body p-4 p-md-5">
                <h3 class="fw-bold mb-1 text-white">Selamat Datang, <?= htmlspecialchars($user['MBR_NAME'] ?? 'Master Owner') ?>!</h3>
                <p class="mb-0 text-white-50 fs-14">Portal Pemantauan Master Owner Toko Madura</p>
            </div>
        </div>
    </div>
</div>

<!-- 3 CARD INDIKATOR UTAMA MASTER OWNER -->
<div class="row row-sm mb-4 g-3">
    <div class="col-md-4">
        <div class="card custom-card border-0 shadow-sm h-100" style="border-left: 5px solid #7D0A0A !important; border-radius: 14px;">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <span class="text-body-secondary fw-bold text-uppercase fs-12">Total Investor Saya</span>
                    <h2 class="fw-bold text-danger mb-0 mt-1"><?= $countInvestor ?></h2>
                    <small class="text-body-secondary">Investor Pemodal Terdaftar</small>
                </div>
                <div class="rounded-circle bg-danger-subtle p-3 text-danger">
                    <i class="fa-light fa-users fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card custom-card border-0 shadow-sm h-100" style="border-left: 5px solid #198754 !important; border-radius: 14px;">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <span class="text-body-secondary fw-bold text-uppercase fs-12">Total Outlet</span>
                    <h2 class="fw-bold text-success mb-0 mt-1"><?= $countOutlet ?></h2>
                    <small class="text-body-secondary">Cabang Outlet Terdaftar</small>
                </div>
                <div class="rounded-circle bg-success-subtle p-3 text-success">
                    <i class="fa-light fa-store fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card custom-card border-0 shadow-sm h-100" style="border-left: 5px solid #ffc107 !important; border-radius: 14px;">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <span class="text-body-secondary fw-bold text-uppercase fs-12">Total Keuntungan Master</span>
                    <h2 class="fw-bold text-warning mb-0 mt-1">Rp <?= number_format($totalKeuntunganMaster, 0, ',', '.') ?></h2>
                    <small class="text-body-secondary">Akumulasi Bagian Master</small>
                </div>
                <div class="rounded-circle bg-warning-subtle p-3 text-warning">
                    <i class="fa-light fa-chart-line-up fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TABEL RINGKASAN INVESTOR & OUTLET -->
<div class="row row-sm">
    <!-- TABLE INVESTOR -->
    <div class="col-lg-6 mb-4">
        <div class="card custom-card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-header bg-body py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                <h6 class="fw-bold mb-0 text-danger"><i class="fa-solid fa-users me-2"></i>List Investor</h6>
                <a href="<?= SystemInfo::app('CLIENT_URL') ?>/investor" class="btn btn-outline-danger btn-sm rounded-pill px-3">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-body-secondary">
                            <tr class="small text-uppercase text-body-secondary">
                                <th>No</th>
                                <th>Nama Investor</th>
                                <th>Kecamatan & Detail Alamat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($listInvestors && $listInvestors->num_rows > 0) : ?>
                                <?php $no = 1; while ($inv = $listInvestors->fetch_assoc()) : ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td>
                                            <strong class="text-body-emphasis"><?= htmlspecialchars($inv['nama_lengkap']) ?></strong>
                                            <br><small class="text-body-secondary"><i class="fa-light fa-phone me-1"></i><?= htmlspecialchars($inv['no_hp'] ?: '-') ?></small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <span class="badge bg-light text-body-emphasis border"><i class="fa-light fa-location-dot me-1 text-danger"></i><?= htmlspecialchars($inv['kecamatan'] ?: 'Kecamatan N/A') ?></span>
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-detail-alamat-investor rounded-pill px-2 py-0" style="font-size: 11px;"
                                                        data-nama="<?= htmlspecialchars($inv['nama_lengkap']) ?>"
                                                        data-kecamatan="<?= htmlspecialchars($inv['kecamatan'] ?: '-') ?>"
                                                        data-alamat="<?= htmlspecialchars($inv['alamat_investor'] ?: '-') ?>">
                                                    <i class="fa-light fa-eye me-1"></i> Detail
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr><td colspan="3" class="text-center py-3 text-body-secondary">Belum ada investor.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLE OUTLET -->
    <div class="col-lg-6 mb-4">
        <div class="card custom-card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-header bg-body py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
                <h6 class="fw-bold mb-0 text-success"><i class="fa-solid fa-store me-2"></i>List Outlet</h6>
                <a href="<?= SystemInfo::app('CLIENT_URL') ?>/outlet" class="btn btn-outline-success btn-sm rounded-pill px-3">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-body-secondary">
                            <tr class="small text-uppercase text-body-secondary">
                                <th>No</th>
                                <th>Nama Outlet & Alamat</th>
                                <th>Investor & Alamat</th>
                                <th>Tanggal Bergabung</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($listOutlets && $listOutlets->num_rows > 0) : ?>
                                <?php $no = 1; while ($out = $listOutlets->fetch_assoc()) : ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td>
                                            <strong class="text-body-emphasis"><?= htmlspecialchars($out['nama_outlet']) ?></strong>
                                            <br>
                                            <div class="d-flex align-items-center gap-1 mt-1">
                                                <span class="badge bg-light text-body-secondary border" style="font-size: 10px;"><i class="fa-light fa-location-dot me-1 text-success"></i><?= htmlspecialchars($out['kecamatan_outlet'] ?: 'Kecamatan N/A') ?></span>
                                                <button type="button" class="btn btn-sm btn-outline-success btn-detail-alamat-outlet rounded-pill px-2 py-0" style="font-size: 10px;"
                                                        data-nama="<?= htmlspecialchars($out['nama_outlet']) ?>"
                                                        data-kecamatan="<?= htmlspecialchars($out['kecamatan_outlet'] ?: '-') ?>"
                                                        data-alamat="<?= htmlspecialchars($out['alamat_outlet'] ?: '-') ?>">
                                                    Detail
                                                </button>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-body-emphasis"><?= htmlspecialchars($out['nama_investor']) ?></span>
                                            <br>
                                            <div class="d-flex align-items-center gap-1 mt-1">
                                                <span class="badge bg-light text-body-secondary border" style="font-size: 10px;"><i class="fa-light fa-location-dot me-1 text-danger"></i><?= htmlspecialchars($out['kecamatan_investor'] ?: 'Kecamatan N/A') ?></span>
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-detail-alamat-investor rounded-pill px-2 py-0" style="font-size: 10px;"
                                                        data-nama="<?= htmlspecialchars($out['nama_investor']) ?>"
                                                        data-kecamatan="<?= htmlspecialchars($out['kecamatan_investor'] ?: '-') ?>"
                                                        data-alamat="<?= htmlspecialchars($out['alamat_investor'] ?: '-') ?>">
                                                    Detail
                                                </button>
                                            </div>
                                        </td>
                                        <td class="small text-body-secondary">
                                            <?= !empty($out['tanggal_bergabung']) ? date('d M Y', strtotime($out['tanggal_bergabung'])) : '-' ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill fw-semibold" style="font-size: 11px;">
                                                <i class="fa-solid fa-circle me-1" style="font-size: 7px;"></i>Aktif
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr><td colspan="5" class="text-center py-3 text-body-secondary">Belum ada outlet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Modal Detail Alamat Investor
    $(document).on('click', '.btn-detail-alamat-investor', function() {
        const nama = $(this).data('nama');
        const kec = $(this).data('kecamatan');
        const alamat = $(this).data('alamat');

        let html = `
            <div class="text-start fs-14">
                <div class="bg-body-tertiary p-3 rounded-3 border mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-body-secondary"><i class="fa-solid fa-user-tie text-danger me-2"></i>Nama Investor</span>
                        <span class="fw-bold text-body-emphasis">${nama}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-body-secondary"><i class="fa-solid fa-map-location-dot text-primary me-2"></i>Kecamatan</span>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3">${kec}</span>
                    </div>
                    <div class="pt-1">
                        <span class="text-body-secondary d-block mb-1"><i class="fa-solid fa-location-dot text-danger me-2"></i>Detail Alamat Lengkap:</span>
                        <p class="fw-semibold text-body-emphasis mb-0 bg-body p-2 rounded border">${alamat}</p>
                    </div>
                </div>
            </div>
        `;

        Swal.fire({
            title: 'Detail Alamat Investor',
            html: html,
            icon: 'info',
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#7D0A0A'
        });
    });

    // Modal Detail Alamat Outlet
    $(document).on('click', '.btn-detail-alamat-outlet', function() {
        const nama = $(this).data('nama');
        const kec = $(this).data('kecamatan');
        const alamat = $(this).data('alamat');

        let html = `
            <div class="text-start fs-14">
                <div class="bg-body-tertiary p-3 rounded-3 border mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-body-secondary"><i class="fa-solid fa-store text-success me-2"></i>Nama Outlet</span>
                        <span class="fw-bold text-body-emphasis">${nama}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-body-secondary"><i class="fa-solid fa-map-location-dot text-primary me-2"></i>Kecamatan</span>
                        <span class="badge bg-success-subtle text-success rounded-pill px-3">${kec}</span>
                    </div>
                    <div class="pt-1">
                        <span class="text-body-secondary d-block mb-1"><i class="fa-solid fa-location-dot text-danger me-2"></i>Detail Alamat Lengkap:</span>
                        <p class="fw-semibold text-body-emphasis mb-0 bg-body p-2 rounded border">${alamat}</p>
                    </div>
                </div>
            </div>
        `;

        Swal.fire({
            title: 'Detail Alamat Outlet',
            html: html,
            icon: 'info',
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#198754'
        });
    });
});
</script>
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
        SELECT o.nama_outlet, lo.periode_laporan, lo.omzet, lo.nominal_potongan, lo.waktu_input
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
