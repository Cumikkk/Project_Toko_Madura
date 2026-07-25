<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();

$loggedInLevel = intval($user['ADM_LEVEL'] ?? 1);
$loggedInId    = intval($user['ADM_ID'] ?? 1);
$isMasterOwner = ($loggedInLevel == 2);

if ($isMasterOwner) {
    // -------------------------------------------------------------------------
    // DASHBOARD MASTER OWNER (NON-KEUANGAN KHUSUS HANYA UNTUK MASTER OWNER)
    // -------------------------------------------------------------------------
    
    // Total Investor milik Master ini
    $investorCount = $db->query("SELECT COUNT(*) as total FROM investor WHERE id_master = {$loggedInId}")->fetch_assoc()['total'] ?? 0;
    
    // Total Outlet milik Investor dari Master ini
    $outletCount = $db->query("
        SELECT COUNT(o.id_outlet) as total 
        FROM outlet o 
        JOIN investor i ON o.id_investor = i.id_investor 
        WHERE i.id_master = {$loggedInId}
    ")->fetch_assoc()['total'] ?? 0;

    // Total Pengelola/Kasir terdaftar di outlet milik Master ini
    $kasirCount = $db->query("
        SELECT COUNT(DISTINCT o.id_users) as total 
        FROM outlet o 
        JOIN investor i ON o.id_investor = i.id_investor 
        WHERE i.id_master = {$loggedInId} AND o.id_users IS NOT NULL AND o.id_users > 0
    ")->fetch_assoc()['total'] ?? 0;

    // Fetch List Investor milik Master ini (Non-Keuangan)
    $myInvestors = $db->query("
        SELECT i.*, u.nama_lengkap, u.username, u.email, u.no_hp,
               (SELECT COUNT(*) FROM outlet o WHERE o.id_investor = i.id_investor) as total_outlet
        FROM investor i
        JOIN users u ON (u.id_users = i.id_users)
        WHERE i.id_master = {$loggedInId}
        ORDER BY u.nama_lengkap ASC
    ");

    // Fetch List Outlet milik Master ini (Non-Keuangan)
    $myOutlets = $db->query("
        SELECT o.*, u.nama_lengkap as pengelola_toko, u.no_hp as no_hp_toko, 
               inv_user.nama_lengkap as nama_investor
        FROM outlet o
        LEFT JOIN users u ON (u.id_users = o.id_users)
        JOIN investor inv ON (inv.id_investor = o.id_investor)
        JOIN users inv_user ON (inv_user.id_users = inv.id_users)
        WHERE inv.id_master = {$loggedInId}
        ORDER BY o.nama_outlet ASC
    ");
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Dashboard Master Owner</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
        </ol>
    </div>
</div>

<!-- STATS ROW FOR MASTER OWNER (NON-FINANCIAL) -->
<div class="row row-sm">
    <div class="col-sm-12 col-md-4 col-lg-4">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-item">
                    <div class="card-item-icon card-icon">
                        <i class="ti-user text-primary" style="font-size: 28px;"></i>
                    </div>
                    <div class="card-item-title double-line-height">
                        <label class="main-content-label tx-13 mg-b-2">Total Investor Saya</label>
                        <span class="d-block tx-12 text-muted">Pemodal Terdaftar</span>
                    </div>
                    <div class="card-item-number ms-auto">
                        <h2 class="font-weight-bold text-primary"><?= $investorCount ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-md-4 col-lg-4">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-item">
                    <div class="card-item-icon card-icon">
                        <i class="ti-shopping-cart text-success" style="font-size: 28px;"></i>
                    </div>
                    <div class="card-item-title double-line-height">
                        <label class="main-content-label tx-13 mg-b-2">Total Outlet Cabang</label>
                        <span class="d-block tx-12 text-muted">Toko Madura Terhubung</span>
                    </div>
                    <div class="card-item-number ms-auto">
                        <h2 class="font-weight-bold text-success"><?= $outletCount ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-md-4 col-lg-4">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-item">
                    <div class="card-item-icon card-icon">
                        <i class="ti-id-badge text-info" style="font-size: 28px;"></i>
                    </div>
                    <div class="card-item-title double-line-height">
                        <label class="main-content-label tx-13 mg-b-2">Total Pengelola Toko</label>
                        <span class="d-block tx-12 text-muted">Kasir / Pengelola Active</span>
                    </div>
                    <div class="card-item-number ms-auto">
                        <h2 class="font-weight-bold text-info"><?= $kasirCount ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TABLES ROW FOR MASTER OWNER (NON-FINANCIAL) -->
<div class="row row-sm">
    <!-- LIST INVESTOR -->
    <div class="col-lg-6 col-md-12 mb-3">
        <div class="card custom-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="main-content-label mb-1">Daftar Investor Pemodal</h6>
                    <p class="text-muted card-sub-title mb-0">Investor terdaftar di bawah jaringan Anda.</p>
                </div>
                <a href="<?= SystemInfo::app('ADMIN_URL') ?>/investor/view" class="btn btn-outline-primary btn-sm">Lihat Semua</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Investor</th>
                                <th>Username</th>
                                <th class="text-center">Jumlah Outlet</th>
                                <th class="text-center">Porsi (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($myInvestors && $myInvestors->num_rows > 0) : ?>
                                <?php $no = 1; while ($row = $myInvestors->fetch_assoc()) : ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td><strong><?= htmlspecialchars($row['nama_lengkap']) ?></strong></td>
                                        <td><code><?= htmlspecialchars($row['username']) ?></code></td>
                                        <td class="text-center"><span class="badge bg-secondary"><?= $row['total_outlet'] ?> Toko</span></td>
                                        <td class="text-center"><span class="badge bg-primary"><?= number_format($row['persen_bagian_investor'], 0) ?>%</span></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada investor terdaftar.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- LIST OUTLET -->
    <div class="col-lg-6 col-md-12 mb-3">
        <div class="card custom-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="main-content-label mb-1">Daftar Cabang Outlet</h6>
                    <p class="text-muted card-sub-title mb-0">Cabang Toko Madura yang dikelola.</p>
                </div>
                <a href="<?= SystemInfo::app('ADMIN_URL') ?>/outlet/view" class="btn btn-outline-primary btn-sm">Lihat Semua</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Nama Toko</th>
                                <th>Pengelola</th>
                                <th>Investor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($myOutlets && $myOutlets->num_rows > 0) : ?>
                                <?php $no = 1; while ($row = $myOutlets->fetch_assoc()) : ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($row['kode_outlet']) ?></span></td>
                                        <td><strong class="text-primary"><?= htmlspecialchars($row['nama_outlet']) ?></strong></td>
                                        <td><?= htmlspecialchars($row['pengelola_toko'] ?? '-') ?></td>
                                        <td><span class="badge bg-info"><?= htmlspecialchars($row['nama_investor']) ?></span></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada cabang outlet terdaftar.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
} else {
    // -------------------------------------------------------------------------
    // DASHBOARD GENERAL / PROGRAMMER / ADMIN STAF
    // -------------------------------------------------------------------------
    $investorCount = $db->query("SELECT COUNT(*) as total FROM investor")->fetch_assoc()['total'] ?? 0;
    $outletCount   = $db->query("SELECT COUNT(*) as total FROM outlet")->fetch_assoc()['total'] ?? 0;

    $omzetSumResult = $db->query("SELECT SUM(omzet) as total FROM laporan_omzet")->fetch_assoc();
    $totalOmzet     = $omzetSumResult['total'] ?? 0;

    // Hitung total potongan 10% dari omzet
    $potonganSumResult = $db->query("SELECT SUM(nominal_potongan) as total FROM laporan_omzet")->fetch_assoc();
    $totalPotongan     = $potonganSumResult['total'] ?? 0;

    $recentOmzet = $db->query("
        SELECT o.nama_outlet, o.kode_outlet, lo.periode_laporan, lo.omzet, lo.waktu_input 
        FROM laporan_omzet lo
        JOIN outlet o ON (o.id_outlet = lo.id_outlet)
        ORDER BY lo.waktu_input DESC LIMIT 5
    ");
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Dashboard Admin</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
        </ol>
    </div>
</div>

<!-- ROW-1 -->
<div class="row row-sm">
    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-item">
                    <div class="card-item-icon card-icon">
                        <i class="ti-user text-primary" style="font-size: 24px;"></i>
                    </div>
                    <div class="card-item-title double-line-height">
                        <label class="main-content-label tx-13 mg-b-2">Total Investor</label>
                        <span class="d-block tx-12 text-muted">Investor Terdaftar</span>
                    </div>
                    <div class="card-item-number ms-auto">
                        <h2 class="font-weight-bold text-primary"><?= $investorCount ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-item">
                    <div class="card-item-icon card-icon">
                        <i class="ti-shopping-cart text-success" style="font-size: 24px;"></i>
                    </div>
                    <div class="card-item-title double-line-height">
                        <label class="main-content-label tx-13 mg-b-2">Total Outlet</label>
                        <span class="d-block tx-12 text-muted">Outlet Toko Madura</span>
                    </div>
                    <div class="card-item-number ms-auto">
                        <h2 class="font-weight-bold text-success"><?= $outletCount ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-item">
                    <div class="card-item-icon card-icon">
                        <i class="ti-money text-warning" style="font-size: 24px;"></i>
                    </div>
                    <div class="card-item-title double-line-height">
                        <label class="main-content-label tx-13 mg-b-2">Total Omzet Nasional</label>
                        <span class="d-block tx-12 text-muted">Seluruh Periode</span>
                    </div>
                    <div class="card-item-number ms-auto">
                        <h5 class="font-weight-bold text-warning">Rp <?= number_format($totalOmzet, 0, ',', '.') ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-item">
                    <div class="card-item-icon card-icon">
                        <i class="ti-stats-up text-info" style="font-size: 24px;"></i>
                    </div>
                    <div class="card-item-title double-line-height">
                        <label class="main-content-label tx-13 mg-b-2">Potongan Platform</label>
                        <span class="d-block tx-12 text-muted">Akumulasi System</span>
                    </div>
                    <div class="card-item-number ms-auto">
                        <h5 class="font-weight-bold text-info">Rp <?= number_format($totalPotongan, 0, ',', '.') ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END ROW-1 -->

<!-- ROW-2 -->
<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card custom-card">
            <div class="card-header">
                <h6 class="main-content-label mb-1">Input Omzet Terbaru</h6>
                <p class="text-muted card-sub-title">Daftar 5 omzet terakhir yang dimasukkan oleh outlet.</p>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Nama Outlet</th>
                                <th>Periode Laporan</th>
                                <th>Omzet</th>
                                <th>Waktu Input</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recentOmzet && $recentOmzet->num_rows > 0) : ?>
                                <?php $no = 1; while ($row = $recentOmzet->fetch_assoc()) : ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($row['kode_outlet']) ?></span></td>
                                        <td><strong class="text-primary"><?= htmlspecialchars($row['nama_outlet']) ?></strong></td>
                                        <td><?= date("d M Y", strtotime($row['periode_laporan'])) ?></td>
                                        <td class="fw-bold text-success">Rp <?= number_format($row['omzet'], 0, ',', '.') ?></td>
                                        <td><?= date("d/m/Y H:i", strtotime($row['waktu_input'])) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Belum ada data laporan omzet masuk.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END ROW-2 -->
<?php } ?>
