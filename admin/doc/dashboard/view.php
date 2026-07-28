<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();

// -------------------------------------------------------------------------
// DASHBOARD GENERAL PROGRAMMER (ADMIN PORTAL)
// -------------------------------------------------------------------------

// Counts per Role & Entity
$adminCount    = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'programmer'")->fetch_assoc()['total'] ?? 0;
$masterCount   = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'master'")->fetch_assoc()['total'] ?? 0;
$investorCount = $db->query("SELECT COUNT(*) as total FROM investor")->fetch_assoc()['total'] ?? 0;
$outletCount   = $db->query("SELECT COUNT(*) as total FROM outlet")->fetch_assoc()['total'] ?? 0;

// Top 5 Outlet berdasarkan Omzet
$topOutlets = $db->query("
    SELECT o.id_outlet, o.nama_outlet, o.kecamatan, o.alamat_outlet, SUM(l.omzet) as total_omzet,
           u_inv.nama_lengkap as nama_investor
    FROM laporan_omzet l
    JOIN outlet o ON l.id_outlet = o.id_outlet
    LEFT JOIN investor inv ON (inv.id_investor = o.id_investor)
    LEFT JOIN users u_inv ON (u_inv.id_users = inv.id_users)
    GROUP BY l.id_outlet
    ORDER BY total_omzet DESC
    LIMIT 5
");

// 5 Request Outlet Terbaru
$recentRequests = $db->query("
    SELECT o.*, u_inv.nama_lengkap as nama_investor
    FROM outlet o
    LEFT JOIN investor inv ON (inv.id_investor = o.id_investor)
    LEFT JOIN users u_inv ON (u_inv.id_users = inv.id_users)
    ORDER BY CASE WHEN o.status = 'pending' THEN 1 ELSE 2 END, o.id_outlet DESC
    LIMIT 5
");
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Dashboard Administrator</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
        </ol>
    </div>
</div>

<!-- Row Stat Cards (RRFX Default Template Style: Total Admin, Master, Investor, Outlet) -->
<div class="row row-sm">
    <div class="col-sm-6 col-lg-3">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-order-reviews">
                    <h6 class="mb-3 text-muted">Total Admin</h6>
                    <h3 class="text-end mb-0"><i class="fa fa-user-secret icon-size float-start text-primary"></i><span><?= number_format($adminCount) ?></span></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-order-reviews">
                    <h6 class="mb-3 text-muted">Total Master</h6>
                    <h3 class="text-end mb-0"><i class="fa fa-user-circle icon-size float-start text-info"></i><span><?= number_format($masterCount) ?></span></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-order-reviews">
                    <h6 class="mb-3 text-muted">Total Investor</h6>
                    <h3 class="text-end mb-0"><i class="fa fa-handshake-o icon-size float-start text-warning"></i><span><?= number_format($investorCount) ?></span></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card custom-card">
            <div class="card-body">
                <div class="card-order-reviews">
                    <h6 class="mb-3 text-muted">Total Outlet</h6>
                    <h3 class="text-end mb-0"><i class="fa fa-building icon-size float-start text-success"></i><span><?= number_format($outletCount) ?></span></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .table-dashboard-summary th, 
    .table-dashboard-summary td {
        border: 1px solid #e1e6f1 !important;
    }
</style>

<!-- Row Summary Tables -->
<div class="row row-sm">
    <!-- OUTLET DENGAN OMZET TERTINGGI -->
    <div class="col-lg-6 mb-4">
        <div class="card custom-card overflow-hidden">
            <div class="card-header border-bottom d-flex align-items-center justify-content-between px-3" style="padding-top: 10px; padding-bottom: 10px; min-height: 48px;">
                <h6 class="main-content-label mb-0" style="line-height: 1.2; margin: 0; padding: 0;">Outlet dengan Omzet Tertinggi</h6>
                <a href="<?= SystemInfo::app('ADMIN_URL') ?>/omzet/view" class="btn btn-outline-primary btn-sm py-1 px-2" style="line-height: 1.2;">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-dashboard-summary table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 8%;">No</th>
                                <th class="text-center">Nama Outlet</th>
                                <th class="text-center">Kecamatan</th>
                                <th class="text-center">Investor</th>
                                <th class="text-center">Total Omzet</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($topOutlets && $topOutlets->num_rows > 0) : ?>
                                <?php $no = 1; while ($row = $topOutlets->fetch_assoc()) : ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td class="text-start">
                                            <strong class="text-primary"><?= htmlspecialchars($row['nama_outlet']) ?></strong>
                                        </td>
                                        <td class="text-center">
                                            <?= htmlspecialchars($row['kecamatan'] ?? '-') ?>
                                            <?php if (!empty($row['alamat_outlet'])) : ?>
                                                <button type="button" class="btn btn-outline-info btn-xs ms-1 btn-detail-alamat-outlet" 
                                                        data-nama="<?= htmlspecialchars($row['nama_outlet']) ?>" 
                                                        data-alamat="<?= htmlspecialchars($row['alamat_outlet']) ?>" 
                                                        title="Lihat Alamat Lengkap">
                                                    <i class="fa fa-info-circle"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-start"><?= htmlspecialchars($row['nama_investor'] ?? '-') ?></td>
                                        <td class="text-end fw-bold text-success">Rp <?= number_format($row['total_omzet'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada data omzet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- REQUEST OUTLET TERBARU -->
    <div class="col-lg-6 mb-4">
        <div class="card custom-card overflow-hidden">
            <div class="card-header border-bottom d-flex align-items-center justify-content-between px-3" style="padding-top: 10px; padding-bottom: 10px; min-height: 48px;">
                <h6 class="main-content-label mb-0" style="line-height: 1.2; margin: 0; padding: 0;">Request Outlet Terbaru</h6>
                <a href="<?= SystemInfo::app('ADMIN_URL') ?>/request-outlet/view" class="btn btn-outline-primary btn-sm py-1 px-2" style="line-height: 1.2;">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-dashboard-summary table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 10%;">No</th>
                                <th class="text-center">Nama Outlet</th>
                                <th class="text-center">Investor</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recentRequests && $recentRequests->num_rows > 0) : ?>
                                <?php $noReq = 1; while ($row = $recentRequests->fetch_assoc()) : ?>
                                    <tr>
                                        <td class="text-center"><?= $noReq++ ?></td>
                                        <td class="text-start">
                                            <strong class="text-primary"><?= htmlspecialchars($row['nama_outlet']) ?></strong>
                                            <?php if(!empty($row['kecamatan'])) : ?>
                                                <br><small class="text-muted"><i class="fa fa-map-marker me-1"></i><?= htmlspecialchars($row['kecamatan']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-start"><?= htmlspecialchars($row['nama_investor'] ?? '-') ?></td>
                                        <td class="text-center">
                                            <?php if ($row['status'] === 'pending') : ?>
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            <?php elseif ($row['status'] === 'active') : ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else : ?>
                                                <span class="badge bg-danger">Reject</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Belum ada request outlet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $('.btn-detail-alamat-outlet').on('click', function() {
        let nama = $(this).data('nama');
        let alamat = $(this).data('alamat');
        Swal.fire({
            title: 'Alamat Lengkap Outlet',
            html: '<p class="text-start mb-1"><strong>Outlet:</strong> ' + nama + '</p><div class="p-3 bg-light rounded text-start"><i class="fa fa-map-marker me-2 text-danger"></i>' + alamat + '</div>',
            icon: 'info',
            confirmButtonText: 'Tutup'
        });
    });
});
</script>
