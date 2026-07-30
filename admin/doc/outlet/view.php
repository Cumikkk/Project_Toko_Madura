<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();

// 1. Fetch counts for stats cards
$activeCount = 0;
$resActive = $db->query("SELECT COUNT(*) as total FROM outlet WHERE status = 'active' AND (tgl_jatuh_tempo IS NULL OR DATE(tgl_jatuh_tempo) >= CURRENT_DATE())");
if ($resActive && $resActive->num_rows > 0) {
    $activeCount = (int)$resActive->fetch_assoc()['total'];
}

$expiredCount = 0;
$resExpired = $db->query("SELECT COUNT(*) as total FROM outlet WHERE (status = 'active' AND DATE(tgl_jatuh_tempo) < CURRENT_DATE()) OR status = 'inactive'");
if ($resExpired && $resExpired->num_rows > 0) {
    $expiredCount = (int)$resExpired->fetch_assoc()['total'];
}

$pendingCount = 0;
$resPending = $db->query("SELECT COUNT(*) as total FROM outlet WHERE status = 'pending'");
if ($resPending && $resPending->num_rows > 0) {
    $pendingCount = (int)$resPending->fetch_assoc()['total'];
}

$rejectCount = 0;
$resReject = $db->query("SELECT COUNT(*) as total FROM outlet WHERE status = 'reject'");
if ($resReject && $resReject->num_rows > 0) {
    $rejectCount = (int)$resReject->fetch_assoc()['total'];
}

// 2. Fetch Active Outlets (not expired)
$sqlActive = "
    SELECT o.*, u_kasir.nama_lengkap as pengelola_toko, u_kasir.no_hp as no_hp_toko,
           u_inv.nama_lengkap as nama_investor, inv.persen_bagian_investor
    FROM outlet o
    LEFT JOIN users u_kasir ON u_kasir.id_users = o.id_users
    LEFT JOIN investor inv ON inv.id_investor = o.id_investor
    LEFT JOIN users u_inv ON u_inv.id_users = inv.id_users
    WHERE o.status = 'active' AND (o.tgl_jatuh_tempo IS NULL OR DATE(o.tgl_jatuh_tempo) >= CURRENT_DATE())
    ORDER BY o.nama_outlet ASC
";
$activeOutlets = $db->query($sqlActive);

// 3. Fetch Expired Outlets
$sqlExpired = "
    SELECT o.*, u_kasir.nama_lengkap as pengelola_toko, u_kasir.no_hp as no_hp_toko,
           u_inv.nama_lengkap as nama_investor, inv.persen_bagian_investor
    FROM outlet o
    LEFT JOIN users u_kasir ON u_kasir.id_users = o.id_users
    LEFT JOIN investor inv ON inv.id_investor = o.id_investor
    LEFT JOIN users u_inv ON u_inv.id_users = inv.id_users
    WHERE (o.status = 'active' AND DATE(o.tgl_jatuh_tempo) < CURRENT_DATE()) OR o.status = 'inactive'
    ORDER BY o.tgl_jatuh_tempo DESC, o.nama_outlet ASC
";
$expiredOutlets = $db->query($sqlExpired);

// 4. Fetch Pending Outlets (Request Outlet)
$sqlPending = "
    SELECT o.*, u_inv.nama_lengkap as nama_investor, u_inv.no_hp as no_hp_investor
    FROM outlet o
    LEFT JOIN investor inv ON inv.id_investor = o.id_investor
    LEFT JOIN users u_inv ON u_inv.id_users = inv.id_users
    WHERE o.status = 'pending'
    ORDER BY o.id_outlet DESC
";
$pendingOutlets = $db->query($sqlPending);

// 5. Fetch Rejected Outlets
$sqlReject = "
    SELECT o.*, u_inv.nama_lengkap as nama_investor, u_inv.no_hp as no_hp_investor
    FROM outlet o
    LEFT JOIN investor inv ON inv.id_investor = o.id_investor
    LEFT JOIN users u_inv ON u_inv.id_users = inv.id_users
    WHERE o.status = 'reject'
    ORDER BY o.id_outlet DESC
";
$rejectedOutlets = $db->query($sqlReject);

// Helper: safely encode alamat for JS variable
function safeJsonAlamat($str) {
    return json_encode(trim(preg_replace('/\s+/', ' ', $str ?? '')));
}

// Build reliable client base URL from DOCUMENT_ROOT
$_protocol   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$_host       = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_docRoot    = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
$_curDir     = rtrim(str_replace('\\', '/', __DIR__), '/');
$_relDir     = str_replace($_docRoot, '', $_curDir);
$_parts      = array_values(array_filter(explode('/', $_relDir)));
$_projectDir = count($_parts) > 0 ? '/' . $_parts[0] : '';
$clientBaseUrl = $_protocol . $_host . $_projectDir . '/client';
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Daftar Outlet Toko Madura</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Outlet</li>
        </ol>
    </div>
</div>

<!-- Summary Metrics Cards (Gaya Dashboard RRFX: Header text-muted + H3 icon & count) -->
<div class="row row-sm mb-3">
    <div class="col-sm-3 col-lg-3 mb-2">
        <div class="card custom-card outlet-stat-card active-card" id="card-active" onclick="switchOutletTab('active')" style="cursor:pointer;">
            <div class="card-body">
                <div class="card-order-reviews">
                    <h6 class="mb-3 text-muted">Outlet Aktif</h6>
                    <h3 class="text-end mb-0"><i class="fa fa-building icon-size float-start text-success"></i><span><?= number_format($activeCount) ?></span></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-3 col-lg-3 mb-2">
        <div class="card custom-card outlet-stat-card" id="card-expired" onclick="switchOutletTab('expired')" style="cursor:pointer;">
            <div class="card-body">
                <div class="card-order-reviews">
                    <h6 class="mb-3 text-muted">Expired / Non-Aktif</h6>
                    <h3 class="text-end mb-0"><i class="fa fa-exclamation-triangle icon-size float-start text-danger"></i><span><?= number_format($expiredCount) ?></span></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-3 col-lg-3 mb-2">
        <div class="card custom-card outlet-stat-card" id="card-pending" onclick="switchOutletTab('pending')" style="cursor:pointer;">
            <div class="card-body">
                <div class="card-order-reviews">
                    <h6 class="mb-3 text-muted">Request Masuk</h6>
                    <h3 class="text-end mb-0"><i class="fa fa-clock-o icon-size float-start text-warning"></i><span><?= number_format($pendingCount) ?></span></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-3 col-lg-3 mb-2">
        <div class="card custom-card outlet-stat-card" id="card-reject" onclick="switchOutletTab('reject')" style="cursor:pointer;">
            <div class="card-body">
                <div class="card-order-reviews">
                    <h6 class="mb-3 text-muted">Request Ditolak</h6>
                    <h3 class="text-end mb-0"><i class="fa fa-times-circle icon-size float-start text-muted"></i><span><?= number_format($rejectCount) ?></span></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Table Card -->
<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="card-title mb-0" id="table-card-title">List Outlet Aktif</h5>
                    <?php if($adminPermissionCore->isHavePermission($moduleId, "create")) : ?>
                        <a href="<?= SystemInfo::app('ADMIN_URL') ?>/outlet/create" id="btn-tambah-outlet" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Tambah Outlet</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <!-- TAB 1: OUTLET AKTIF -->
                <div id="tab-active" class="outlet-tab-section">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100 align-middle" id="table-outlet-active">
                            <thead>
                                <tr class="text-center">
                                    <th class="text-center" style="width: 5%;">No</th>
                                    <th class="text-center">Nama Toko</th>
                                    <th class="text-center">Nama Pengelola Toko</th>
                                    <th class="text-center">No. HP</th>
                                    <th class="text-center">Kecamatan</th>
                                    <th class="text-center">Investor</th>
                                    <th class="text-center">Tanggal Disetujui</th>
                                    <th class="text-center">Jatuh Tempo Langganan</th>
                                    <th class="text-center" style="width: 15%;">#</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($activeOutlets && $activeOutlets->num_rows > 0) : ?>
                                    <?php $no = 1; while ($row = $activeOutlets->fetch_assoc()) : ?>
                                        <tr>
                                            <td class="text-center"><?= $no++ ?></td>
                                            <td class="text-start"><strong class="text-primary"><?= htmlspecialchars($row['nama_outlet']) ?></strong></td>
                                            <td class="text-start"><?= htmlspecialchars($row['pengelola_toko'] ?? '-') ?></td>
                                            <td class="text-center"><?= htmlspecialchars($row['no_hp_toko'] ?? '-') ?></td>
                                            <td class="text-center">
                                                <?= htmlspecialchars($row['kecamatan'] ?? '-') ?>
                                                <?php if (!empty($row['alamat_outlet'])) : ?>
                                                    <button type="button" class="btn btn-outline-info btn-xs ms-1" 
                                                            onclick='showAlamat(<?= safeJsonAlamat($row['nama_outlet']) ?>, <?= safeJsonAlamat($row['alamat_outlet']) ?>)'
                                                            title="Lihat Alamat Lengkap">
                                                        <i class="fa fa-info-circle"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if (!empty($row['nama_investor'])) : ?>
                                                    <span class="badge bg-info"><?= htmlspecialchars($row['nama_investor']) ?> (<?= number_format($row['persen_bagian_investor'], 0) ?>%)</span>
                                                <?php else : ?>
                                                    <span class="badge bg-warning text-dark">Belum Ada Investor</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center"><?= !empty($row['tanggal_disetujui']) ? date("d/m/Y H:i", strtotime($row['tanggal_disetujui'])) : (!empty($row['tanggal_bergabung']) ? date("d/m/Y H:i", strtotime($row['tanggal_bergabung'])) : '-') ?></td>
                                            <td class="text-center">
                                                <?php
                                                if (!empty($row['tgl_jatuh_tempo'])) {
                                                    $jt = date('Y-m-d', strtotime($row['tgl_jatuh_tempo']));
                                                    $today = date('Y-m-d');
                                                    if ($today > $jt) {
                                                        echo '<span class="badge bg-danger" title="Masa langganan telah berakhir"><i class="fas fa-exclamation-triangle me-1"></i>' . date("d/m/Y", strtotime($jt)) . ' (Expired)</span>';
                                                    } else {
                                                        echo '<span class="badge bg-success"><i class="fas fa-calendar-check me-1"></i>' . date("d/m/Y", strtotime($jt)) . '</span>';
                                                    }
                                                } else {
                                                    echo '<span class="text-muted">-</span>';
                                                }
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="action d-flex justify-content-center gap-2">
                                                    <?php if($adminPermissionCore->isHavePermission($moduleId, "update")) : ?>
                                                        <a href="<?= SystemInfo::app('ADMIN_URL') ?>/outlet/create?id=<?= $row['id_outlet'] ?>" class="btn btn-success btn-sm text-white btn-edit" title="Edit Toko"><i class="fas fa-edit"></i></a>
                                                    <?php endif; ?>
                                                    <?php if($adminPermissionCore->isHavePermission($moduleId, "delete")) : ?>
                                                        <button type="button" class="btn btn-danger btn-sm text-white btn-delete" title="Hapus Toko" onclick="deleteOutlet(<?= $row['id_outlet'] ?>, '<?= htmlspecialchars($row['nama_outlet'], ENT_QUOTES, 'UTF-8') ?>')"><i class="fas fa-trash"></i></button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else : ?>
                                    <tr><td colspan="8" class="text-center text-muted py-4">Belum ada data toko aktif.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 2: EXPIRED / NON-AKTIF -->
                <div id="tab-expired" class="outlet-tab-section" style="display:none;">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100 align-middle" id="table-outlet-expired">
                            <thead>
                                <tr class="text-center">
                                    <th class="text-center" style="width: 5%;">No</th>
                                    <th class="text-center">Nama Toko</th>
                                    <th class="text-center">Nama Pengelola Toko</th>
                                    <th class="text-center">No. HP</th>
                                    <th class="text-center">Kecamatan</th>
                                    <th class="text-center">Investor</th>
                                    <th class="text-center">Tanggal Disetujui</th>
                                    <th class="text-center">Jatuh Tempo Langganan</th>
                                    <th class="text-center" style="width: 15%;">#</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($expiredOutlets && $expiredOutlets->num_rows > 0) : ?>
                                    <?php $no = 1; while ($row = $expiredOutlets->fetch_assoc()) : ?>
                                        <tr>
                                            <td class="text-center"><?= $no++ ?></td>
                                            <td class="text-start"><strong class="text-danger"><?= htmlspecialchars($row['nama_outlet']) ?></strong></td>
                                            <td class="text-start"><?= htmlspecialchars($row['pengelola_toko'] ?? '-') ?></td>
                                            <td class="text-center"><?= htmlspecialchars($row['no_hp_toko'] ?? '-') ?></td>
                                            <td class="text-center">
                                                <?= htmlspecialchars($row['kecamatan'] ?? '-') ?>
                                                <?php if (!empty($row['alamat_outlet'])) : ?>
                                                    <button type="button" class="btn btn-outline-info btn-xs ms-1" 
                                                            onclick='showAlamat(<?= safeJsonAlamat($row['nama_outlet']) ?>, <?= safeJsonAlamat($row['alamat_outlet']) ?>)'
                                                            title="Lihat Alamat Lengkap">
                                                        <i class="fa fa-info-circle"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if (!empty($row['nama_investor'])) : ?>
                                                    <span class="badge bg-info"><?= htmlspecialchars($row['nama_investor']) ?> (<?= number_format($row['persen_bagian_investor'], 0) ?>%)</span>
                                                <?php else : ?>
                                                    <span class="badge bg-warning text-dark">Belum Ada Investor</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center"><?= !empty($row['tanggal_disetujui']) ? date("d/m/Y H:i", strtotime($row['tanggal_disetujui'])) : (!empty($row['tanggal_bergabung']) ? date("d/m/Y H:i", strtotime($row['tanggal_bergabung'])) : '-') ?></td>
                                            <td class="text-center">
                                                <?php
                                                if (!empty($row['tgl_jatuh_tempo'])) {
                                                    $jt = date('Y-m-d', strtotime($row['tgl_jatuh_tempo']));
                                                    echo '<span class="badge bg-danger" title="Masa langganan telah berakhir"><i class="fas fa-exclamation-triangle me-1"></i>' . date("d/m/Y", strtotime($jt)) . ' (Expired)</span>';
                                                } else {
                                                    echo '<span class="badge bg-secondary">Non-Aktif</span>';
                                                }
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="action d-flex justify-content-center gap-2">
                                                    <?php if($adminPermissionCore->isHavePermission($moduleId, "update")) : ?>
                                                        <a href="<?= SystemInfo::app('ADMIN_URL') ?>/outlet/create?id=<?= $row['id_outlet'] ?>" class="btn btn-success btn-sm text-white btn-edit" title="Edit Toko"><i class="fas fa-edit"></i></a>
                                                    <?php endif; ?>
                                                    <?php if($adminPermissionCore->isHavePermission($moduleId, "delete")) : ?>
                                                        <button type="button" class="btn btn-danger btn-sm text-white btn-delete" title="Hapus Toko" onclick="deleteOutlet(<?= $row['id_outlet'] ?>, '<?= htmlspecialchars($row['nama_outlet'], ENT_QUOTES, 'UTF-8') ?>')"><i class="fas fa-trash"></i></button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else : ?>
                                    <tr><td colspan="9" class="text-center text-muted py-4">Belum ada data outlet expired / non-aktif.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 3: REQUEST OUTLET (PENDING) -->
                <div id="tab-pending" class="outlet-tab-section" style="display:none;">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100 align-middle" id="table-outlet-pending">
                            <thead>
                                <tr class="text-center">
                                    <th class="text-center" style="width: 5%;">No</th>
                                    <th class="text-center">Nama Outlet</th>
                                    <th class="text-center">Kecamatan</th>
                                    <th class="text-center">Investor</th>
                                    <th class="text-center">Biaya Langganan</th>
                                    <th class="text-center">Bukti Bayar</th>
                                    <th class="text-center">Tanggal Request</th>
                                    <th class="text-center" style="width: 15%;">#</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($pendingOutlets && $pendingOutlets->num_rows > 0) : ?>
                                    <?php $no = 1; while ($row = $pendingOutlets->fetch_assoc()) : ?>
                                        <tr>
                                            <td class="text-center"><?= $no++ ?></td>
                                            <td class="text-start">
                                                <strong class="text-primary"><?= htmlspecialchars($row['nama_outlet']) ?></strong>
                                                <?php if (($row['tipe_request'] ?? 'baru') === 'perpanjangan') : ?>
                                                    <span class="badge bg-warning text-dark ms-1" style="font-size: 10px;"><i class="fas fa-sync-alt me-1"></i>Perpanjangan</span>
                                                <?php else : ?>
                                                    <span class="badge bg-info text-white ms-1" style="font-size: 10px;"><i class="fas fa-plus-circle me-1"></i>Pendaftaran Baru</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?= htmlspecialchars($row['kecamatan'] ?? '-') ?>
                                                <?php if (!empty($row['alamat_outlet'])) : ?>
                                                    <button type="button" class="btn btn-outline-info btn-xs ms-1" 
                                                            onclick='showAlamat(<?= safeJsonAlamat($row['nama_outlet']) ?>, <?= safeJsonAlamat($row['alamat_outlet']) ?>)'
                                                            title="Lihat Alamat Lengkap">
                                                        <i class="fa fa-info-circle"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-start">
                                                <strong><?= htmlspecialchars($row['nama_investor'] ?? '-') ?></strong>
                                                <?php if (!empty($row['no_hp_investor'])) : ?>
                                                    <br><small class="text-muted"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($row['no_hp_investor']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end fw-bold text-success">
                                                Rp <?= number_format($row['nominal_biaya'], 0, ',', '.') ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if (!empty($row['bukti_pembayaran'])) : ?>
                                                    <button type="button" class="btn btn-outline-info btn-sm" onclick="previewBukti('<?= htmlspecialchars($row['bukti_pembayaran'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['nama_outlet'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['nama_investor'] ?? '-', ENT_QUOTES) ?>', '<?= number_format($row['nominal_biaya'] ?? 0, 0, ',', '.') ?>', <?= $row['id_outlet'] ?>)">
                                                        <i class="fas fa-image me-1"></i> Lihat Bukti
                                                    </button>
                                                <?php else : ?>
                                                    <span class="badge bg-light text-dark">Belum ada</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?= !empty($row['tanggal_request']) ? date("d/m/Y H:i", strtotime($row['tanggal_request'])) : (!empty($row['tanggal_bergabung']) ? date("d/m/Y H:i", strtotime($row['tanggal_bergabung'])) : '-') ?>
                                            </td>
                                            <td class="text-center">
                                                 <div class="d-flex justify-content-center gap-1">
                                                     <button type="button" class="btn btn-success btn-sm btn-accept" onclick="approveOutlet(<?= $row['id_outlet'] ?>, '<?= htmlspecialchars($row['nama_outlet'], ENT_QUOTES, 'UTF-8') ?>')">
                                                         <i class="fas fa-check me-1"></i> Setujui
                                                     </button>
                                                     <button type="button" class="btn btn-danger btn-sm btn-reject" onclick="rejectOutlet(<?= $row['id_outlet'] ?>, '<?= htmlspecialchars($row['nama_outlet'], ENT_QUOTES, 'UTF-8') ?>')">
                                                         <i class="fas fa-times me-1"></i> Tolak
                                                     </button>
                                                 </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else : ?>
                                    <tr><td colspan="8" class="text-center text-muted py-4">Belum ada request outlet pending.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 3: DITOLAK (REJECT) -->
                <div id="tab-reject" class="outlet-tab-section" style="display:none;">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100 align-middle" id="table-outlet-reject">
                            <thead>
                                <tr class="text-center">
                                    <th class="text-center" style="width: 5%;">No</th>
                                    <th class="text-center">Nama Outlet</th>
                                    <th class="text-center">Kecamatan</th>
                                    <th class="text-center">Investor</th>
                                    <th class="text-center">Biaya Langganan</th>
                                    <th class="text-center">Alasan Penolakan</th>
                                    <th class="text-center">Tanggal Request</th>
                                    <th class="text-center">Tanggal Ditolak</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($rejectedOutlets && $rejectedOutlets->num_rows > 0) : ?>
                                    <?php $no = 1; while ($row = $rejectedOutlets->fetch_assoc()) : ?>
                                        <tr>
                                            <td class="text-center"><?= $no++ ?></td>
                                            <td class="text-start">
                                                <strong class="text-danger"><?= htmlspecialchars($row['nama_outlet']) ?></strong>
                                                <?php if (($row['tipe_request'] ?? 'baru') === 'perpanjangan') : ?>
                                                    <span class="badge bg-warning text-dark ms-1" style="font-size: 10px;"><i class="fas fa-sync-alt me-1"></i>Perpanjangan</span>
                                                <?php else : ?>
                                                    <span class="badge bg-info text-white ms-1" style="font-size: 10px;"><i class="fas fa-plus-circle me-1"></i>Pendaftaran Baru</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?= htmlspecialchars($row['kecamatan'] ?? '-') ?>
                                                <?php if (!empty($row['alamat_outlet'])) : ?>
                                                    <button type="button" class="btn btn-outline-info btn-xs ms-1" 
                                                            onclick='showAlamat(<?= safeJsonAlamat($row['nama_outlet']) ?>, <?= safeJsonAlamat($row['alamat_outlet']) ?>)'
                                                            title="Lihat Alamat Lengkap">
                                                        <i class="fa fa-info-circle"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-start">
                                                <strong><?= htmlspecialchars($row['nama_investor'] ?? '-') ?></strong>
                                                <?php if (!empty($row['no_hp_investor'])) : ?>
                                                    <br><small class="text-muted"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($row['no_hp_investor']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end text-muted">
                                                Rp <?= number_format($row['nominal_biaya'], 0, ',', '.') ?>
                                            </td>
                                            <td class="text-start">
                                                <span class="text-danger"><i class="fas fa-exclamation-circle me-1"></i><?= htmlspecialchars($row['alasan_penolakan'] ?? 'Tidak ada catatan') ?></span>
                                            </td>
                                            <td class="text-center">
                                                <?= !empty($row['tanggal_request']) ? date("d/m/Y H:i", strtotime($row['tanggal_request'])) : (!empty($row['tanggal_bergabung']) ? date("d/m/Y H:i", strtotime($row['tanggal_bergabung'])) : '-') ?>
                                            </td>
                                            <td class="text-center">
                                                <?= !empty($row['tanggal_ditolak']) ? date("d/m/Y H:i", strtotime($row['tanggal_ditolak'])) : '-' ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else : ?>
                                    <tr><td colspan="8" class="text-center text-muted py-4">Belum ada request outlet yang ditolak.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    </div>
</div>

<script type="text/javascript">
// ============================================================
// Show Alamat popup – called directly via onclick (no data-attr)
// ============================================================
function showAlamat(nama, alamat) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Alamat Lengkap Outlet',
            html: '<p class="text-start mb-1"><strong>Outlet:</strong> ' + nama + '</p><div class="p-3 bg-light rounded text-start"><i class="fa fa-map-marker me-2 text-danger"></i>' + alamat + '</div>',
            icon: 'info',
            confirmButtonText: 'Tutup'
        });
    } else {
        alert('Outlet: ' + nama + '\nAlamat: ' + alamat);
    }
}

// ============================================================
// Track which DataTables have been initialized
// ============================================================
var dtInitialized = { active: false, expired: false, pending: false, reject: false };

function initDataTable(tabKey) {
    var tableId = '#table-outlet-' + tabKey;
    if ($.fn.DataTable) {
        if (!dtInitialized[tabKey] && !$.fn.DataTable.isDataTable(tableId)) {
            $(tableId).DataTable({
                processing: true,
                deferRender: true,
                scrollX: true,
                lengthMenu: [[10, 50, 100, -1], [10, 50, 100, "All"]],
                language: {
                    searchPlaceholder: 'Cari outlet...',
                    sSearch: '',
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    paginate: { first: 'First', last: 'Last', next: 'Next', previous: 'Previous' }
                },
                order: [[0, 'asc']]
            });
            dtInitialized[tabKey] = true;

            if ($.fn.select2) {
                setTimeout(function() {
                    $(tableId + '_wrapper .dataTables_length select').select2({
                        minimumResultsForSearch: Infinity,
                        width: 'auto'
                    });
                }, 50);
            }
        } else if ($.fn.DataTable.isDataTable(tableId)) {
            var dt = $(tableId).DataTable();
            dt.columns.adjust().draw(false);
        }
    }
}

// ============================================================
// Switch Tab — pure display:none/block
// ============================================================
window.approveOutlet = function(id, nama) {
    if (!id) {
        Swal.fire('Error', 'ID Outlet tidak ditemukan', 'error');
        return;
    }
    Swal.fire({
        title: 'Setujui Request Outlet?',
        text: "Persetujuan ini akan mengaktifkan outlet " + (nama || '') + " secara resmi.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Setujui (Active)',
        cancelButtonText: 'Batal'
    }).then(function(result) {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses...',
                text: 'Mengaktifkan outlet...',
                allowOutsideClick: false,
                didOpen: function() { Swal.showLoading(); }
            });

            $.ajax({
                url: '<?= SystemInfo::app("ADMIN_URL") ?>/ajax/post/request-outlet/accept',
                type: 'POST',
                data: { id_outlet: id },
                dataType: 'json',
                success: function(resp) {
                    if (resp.success) {
                        Swal.fire('Berhasil!', resp.message || 'Request outlet berhasil disetujui.', 'success').then(function() { location.reload(); });
                    } else {
                        Swal.fire('Gagal!', resp.message || 'Gagal mengaktifkan outlet', 'error');
                    }
                },
                error: function(xhr, status, err) {
                    $.ajax({
                        url: './../ajax/post/request-outlet/accept',
                        type: 'POST',
                        data: { id_outlet: id },
                        dataType: 'json',
                        success: function(resp) {
                            if (resp.success) {
                                Swal.fire('Berhasil!', resp.message || 'Request outlet berhasil disetujui.', 'success').then(function() { location.reload(); });
                            } else {
                                Swal.fire('Gagal!', resp.message || 'Gagal mengaktifkan outlet', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error!', 'Gagal terhubung ke server (' + (err || status) + ')', 'error');
                        }
                    });
                }
            });
        }
    });
};

window.rejectOutlet = function(id, nama) {
    if (!id) {
        Swal.fire('Error', 'ID Outlet tidak ditemukan', 'error');
        return;
    }
    Swal.fire({
        title: 'Tolak Request Outlet',
        html: '<p class="text-muted mb-2" style="font-size:14px;">Apakah Anda yakin ingin menolak request pembukaan outlet <strong class="text-dark">' + (nama || '') + '</strong>?</p>',
        input: 'textarea',
        inputLabel: 'Alasan Penolakan',
        inputPlaceholder: 'Masukkan alasan penolakan untuk investor...',
        inputAttributes: {
            'aria-label': 'Masukkan alasan penolakan untuk investor...'
        },
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-times me-1"></i> Proses Penolakan',
        cancelButtonText: 'Batal',
        scrollbarPadding: false,
        heightAuto: false,
        inputValidator: function(value) {
            if (!value || !value.trim()) {
                return 'Alasan penolakan wajib diisi!';
            }
        }
    }).then(function(result) {
        if (result.isConfirmed) {
            var alasan = result.value;
            Swal.fire({
                title: 'Memproses...',
                text: 'Sedang memproses penolakan request outlet',
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '<?= SystemInfo::app("ADMIN_URL") ?>/ajax/post/request-outlet/reject',
                type: 'POST',
                data: { id_outlet: id, alasan_penolakan: alasan, alasan: alasan },
                dataType: 'json',
                success: function(resp) {
                    if (resp.success) {
                        Swal.fire('Berhasil!', resp.message || 'Request outlet berhasil ditolak.', 'success').then(function() { location.reload(); });
                    } else {
                        Swal.fire('Gagal!', resp.message || 'Gagal menolak request outlet', 'error');
                    }
                },
                error: function(xhr, status, err) {
                    $.ajax({
                        url: './../ajax/post/request-outlet/reject',
                        type: 'POST',
                        data: { id_outlet: id, alasan_penolakan: alasan, alasan: alasan },
                        dataType: 'json',
                        success: function(resp) {
                            if (resp.success) {
                                Swal.fire('Berhasil!', resp.message || 'Request outlet berhasil ditolak.', 'success').then(function() { location.reload(); });
                            } else {
                                Swal.fire('Gagal!', resp.message || 'Gagal menolak request outlet', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error!', 'Gagal terhubung ke server', 'error');
                        }
                    });
                }
            });
        }
    });
};

function previewBukti(filePath, namaOutlet, namaInvestor, biayaLangganan, idOutlet) {
    if (!filePath) {
        Swal.fire('Informasi', 'Bukti pembayaran belum diunggah.', 'info');
        return;
    }
    var adminUrl = '<?= SystemInfo::app("ADMIN_URL") ?>';
    var proxyUrl = adminUrl + '/image-proxy.php?file=' + encodeURIComponent(filePath);
    var ext = filePath.split('.').pop().toLowerCase();
    if (ext === 'pdf') {
        window.open(proxyUrl, '_blank');
        return;
    }

    var infoHtml = '<div class="text-start bg-light p-3 rounded mb-3" style="font-size:13.5px; border:1px solid #e9ecef;">'
        + '<div class="d-flex align-items-center mb-2">'
        + '  <i class="fa fa-building text-primary me-2" style="width:20px; text-align:center;"></i>'
        + '  <span style="min-width:140px;" class="fw-bold">Nama Outlet:</span>'
        + '  <span class="text-dark fw-semibold">' + namaOutlet + '</span>'
        + '</div>'
        + '<div class="d-flex align-items-center mb-2">'
        + '  <i class="fa fa-handshake-o text-success me-2" style="width:20px; text-align:center;"></i>'
        + '  <span style="min-width:140px;" class="fw-bold">Nama Investor:</span>'
        + '  <span class="text-dark">' + (namaInvestor || '-') + '</span>'
        + '</div>'
        + '<div class="d-flex align-items-center">'
        + '  <i class="fa fa-money text-warning me-2" style="width:20px; text-align:center;"></i>'
        + '  <span style="min-width:140px;" class="fw-bold">Biaya Langganan:</span>'
        + '  <span class="text-success fw-bold">Rp ' + (biayaLangganan || '0') + '</span>'
        + '</div>'
        + '</div>';

    var swalOpts = {
        title: '<i class="fa fa-file-text-o me-2 text-info"></i>Bukti Pembayaran Pendaftaran Outlet',
        html: infoHtml
            + '<img src="' + proxyUrl + '" '
            + 'style="max-width:100%;max-height:60vh;border-radius:8px;border:1px solid #dee2e6;object-fit:contain;" '
            + 'onerror="this.outerHTML=\'<p class=\\\'text-danger mt-2\\\'><i class=\\\'fa fa-exclamation-triangle me-1\\\'></i> Gambar gagal dimuat</p>\'">',
        showCloseButton: true,
        scrollbarPadding: false,
        heightAuto: false,
        width: 640
    };

    if (idOutlet) {
        swalOpts.showConfirmButton = true;
        swalOpts.confirmButtonText = '<i class="fas fa-check me-1"></i> Setujui Request Outlet (Active)';
        swalOpts.confirmButtonColor = '#28a745';
        swalOpts.showCancelButton = true;
        swalOpts.cancelButtonText = 'Tutup';
    } else {
        swalOpts.showConfirmButton = false;
    }

    Swal.fire(swalOpts).then(function(res) {
        if (res.isConfirmed && idOutlet) {
            approveOutlet(idOutlet, namaOutlet);
        }
    });
}

function switchOutletTab(tabKey) {
    // 1. Update card highlight
    $('.outlet-stat-card').removeClass('active-card');
    $('#card-' + tabKey).addClass('active-card');

    // 2. Hide all tab sections, show selected one
    $('.outlet-tab-section').hide();
    $('#tab-' + tabKey).show();

    // 3. Toggle "Tambah Outlet" button (ONLY visible in active tab)
    if (tabKey === 'active') {
        $('#btn-tambah-outlet').show();
    } else {
        $('#btn-tambah-outlet').hide();
    }

    // 4. Update title without icon (matches Investor view header style)
    var titles = {
        active:  'List Outlet Aktif',
        expired: 'List Outlet Expired / Non-Aktif',
        pending: 'List Request Outlet (Pending)',
        reject:  'List Request Outlet (Ditolak)'
    };
    $('#table-card-title').text(titles[tabKey] || titles.active);

    // 5. Initialize or recalculate DataTable AFTER section is displayed
    setTimeout(function() {
        initDataTable(tabKey);
    }, 50);
}

$(document).ready(function() {
    // Initialize default visible tab (active) on load
    initDataTable('active');

    // Auto switch tab from URL param
    var urlParams = new URLSearchParams(window.location.search);
    var tabParam = urlParams.get('tab');
    if (tabParam === 'expired' || window.location.hash === '#expired') {
        switchOutletTab('expired');
    } else if (tabParam === 'pending' || window.location.hash === '#pending') {
        switchOutletTab('pending');
    } else if (tabParam === 'reject' || window.location.hash === '#reject') {
        switchOutletTab('reject');
    }

    // Handle Accept & Reject Request Click
    $(document).on('click', '.btn-accept', function() {
        var id   = $(this).data('id') || $(this).attr('data-id');
        var nama = $(this).data('nama') || $(this).attr('data-nama');
        approveOutlet(id, nama);
    });

    $(document).on('click', '.btn-reject', function() {
        var id   = $(this).data('id') || $(this).attr('data-id');
        var nama = $(this).data('nama') || $(this).attr('data-nama');
        rejectOutlet(id, nama);
    });
});

function deleteOutlet(id, nama) {
    let alertHtml = `
        <div class="text-start fs-14">
            <p class="text-muted mb-3">Tindakan ini akan menghapus outlet <strong class="text-dark">${nama}</strong> beserta seluruh data yang terikat di bawahnya:</p>
            
            <div class="bg-light p-3 rounded-3 border mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                    <span class="text-dark"><i class="fa fa-building text-warning me-2 fs-16"></i>Outlet (${nama})</span>
                    <span class="badge bg-warning text-dark rounded-pill px-3">Outlet</span>
                </div>
                <div class="d-flex align-items-center mb-2 pb-2 border-bottom">
                    <i class="fa fa-money text-danger me-2 fs-16"></i>
                    <span class="text-dark">Riwayat Laporan Omzet Penjualan</span>
                </div>
                <div class="d-flex align-items-center">
                    <i class="fa fa-user-times text-danger me-2 fs-16"></i>
                    <span class="text-dark">Akun Pengguna Kasir Outlet</span>
                </div>
            </div>
            
            <p class="text-danger small mb-0 fw-semibold"><i class="fa fa-exclamation-triangle me-1"></i> Data yang dihapus bersifat permanen dan tidak dapat dikembalikan.</p>
        </div>
    `;

    Swal.fire({
        title: 'Hapus Outlet?',
        html: alertHtml,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus Outlet',
        cancelButtonText: 'Batal',
        customClass: {
            confirmButton: 'px-4 py-2',
            cancelButton: 'px-4 py-2'
        }
    }).then(function(result) {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses...',
                text: 'Sedang menghapus outlet & omzet terkait',
                allowOutsideClick: false,
                didOpen: function() { Swal.showLoading(); }
            });

            $.post("<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/outlet/delete", { id_outlet: id, id: id }, function(resp) {
                if (resp.success) {
                    Swal.fire('Berhasil!', resp.message, 'success').then(function() { location.reload(); });
                } else {
                    Swal.fire('Gagal!', resp.message || 'Gagal menghapus outlet', 'error');
                }
            }, 'json').fail(function() {
                Swal.fire('Error!', 'Gagal terhubung ke server', 'error');
            });
        }
    });
}
</script>
