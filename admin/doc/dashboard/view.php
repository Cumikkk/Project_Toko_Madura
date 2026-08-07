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

// Outlet berdasarkan Omzet
$topOutlets = $db->query("
    SELECT o.id_outlet, o.nama_outlet, u_out.kecamatan, u_out.alamat as alamat_outlet, SUM(l.omzet) as total_omzet,
           u_inv.nama_lengkap as nama_investor, u_inv.kecamatan as kecamatan_investor, u_inv.alamat as alamat_investor
    FROM laporan_omzet l
    JOIN outlet o ON l.id_outlet = o.id_outlet
    LEFT JOIN users u_out ON (u_out.id_users = o.id_users)
    LEFT JOIN investor inv ON (inv.id_investor = o.id_investor)
    LEFT JOIN users u_inv ON (u_inv.id_users = inv.id_users)
    GROUP BY l.id_outlet
    ORDER BY total_omzet DESC
");

// Request Outlet Terbaru (Khusus Pending)
$recentRequests = $db->query("
    SELECT o.*, u_inv.nama_lengkap as nama_investor, u_inv.kecamatan as kecamatan_investor, u_inv.alamat as alamat_investor
    FROM outlet o
    LEFT JOIN investor inv ON (inv.id_investor = o.id_investor)
    LEFT JOIN users u_inv ON (u_inv.id_users = inv.id_users)
    WHERE o.status = 'pending'
    ORDER BY o.id_outlet DESC
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

<!-- Row Summary Tables -->
<style>
#table-top-omzet th.sorting_desc::after,
#table-top-omzet th.sorting_desc::before {
    content: "\f0de" !important;
    opacity: 0.9 !important;
}
</style>
<div class="row row-sm d-flex align-items-stretch">
    <!-- OUTLET DENGAN OMZET TERTINGGI -->
    <div class="col-lg-6 mb-4 d-flex">
        <div class="card custom-card overflow-hidden w-100 d-flex flex-column mb-0">
            <div class="card-header">
                <div class="d-flex justify-content-between mb-2">
                    <h5 class="card-title">Outlet dengan Omzet Tertinggi</h5>
                    <a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard" class="btn btn-primary btn-sm"><i class="fas fa-list me-1"></i> Lihat Semua</a>
                </div>
            </div>
            <div class="card-body d-flex flex-column flex-grow-1">
                <div class="table-responsive flex-grow-1">
                    <table class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100 align-middle mb-0" id="table-top-omzet">
                        <thead>
                            <tr class="text-center">
                                <th class="text-center" style="width: 8%;">No</th>
                                <th class="text-center">Nama Outlet</th>
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
                                            <?php if (!empty($row['kecamatan'])) : ?>
                                                <br><small class="text-muted"><i class="fa fa-map-marker me-1"></i><?= htmlspecialchars($row['kecamatan']) ?></small>
                                            <?php endif; ?>
                                            <?php if (!empty($row['alamat_outlet'])) : ?>
                                                <button type="button" class="btn btn-outline-info btn-xs ms-1 btn-detail-alamat-outlet" 
                                                        data-nama="<?= htmlspecialchars($row['nama_outlet']) ?>" 
                                                        data-alamat="<?= htmlspecialchars($row['alamat_outlet']) ?>" 
                                                        title="Lihat Alamat Lengkap Outlet">
                                                    <i class="fa fa-info-circle"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-start">
                                            <span><?= htmlspecialchars($row['nama_investor'] ?? '-') ?></span>
                                            <?php if (!empty($row['kecamatan_investor'])) : ?>
                                                <br><small class="text-muted"><i class="fa fa-map-marker me-1"></i><?= htmlspecialchars($row['kecamatan_investor']) ?></small>
                                            <?php endif; ?>
                                            <?php if (!empty($row['alamat_investor'])) : ?>
                                                <button type="button" class="btn btn-outline-info btn-xs ms-1 btn-detail-alamat-investor" 
                                                        data-nama="<?= htmlspecialchars($row['nama_investor'] ?? '-') ?>" 
                                                        data-alamat="<?= htmlspecialchars($row['alamat_investor']) ?>" 
                                                        title="Lihat Alamat Lengkap Investor">
                                                    <i class="fa fa-info-circle"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end fw-bold text-success" data-order="<?= (float)$row['total_omzet'] ?>">Rp <?= number_format($row['total_omzet'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- REQUEST OUTLET TERBARU -->
    <div class="col-lg-6 mb-4 d-flex">
        <div class="card custom-card overflow-hidden w-100 d-flex flex-column mb-0">
            <div class="card-header">
                <div class="d-flex justify-content-between mb-2">
                    <h5 class="card-title">Request Outlet Terbaru</h5>
                    <a href="<?= SystemInfo::app('ADMIN_URL') ?>/outlet/view?tab=pending" class="btn btn-primary btn-sm"><i class="fas fa-list me-1"></i> Lihat Semua</a>
                </div>
            </div>
            <div class="card-body d-flex flex-column flex-grow-1">
                <div class="table-responsive flex-grow-1">
                    <table class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100 align-middle mb-0" id="table-recent-requests">
                        <thead>
                            <tr class="text-center">
                                <th class="text-center" style="width: 8%;">No</th>
                                <th class="text-center">Tanggal Request</th>
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
                                        <td class="text-center">
                                            <?= !empty($row['tanggal_request']) ? date('d/m/Y H:i', strtotime($row['tanggal_request'])) : '-' ?>
                                        </td>
                                        <td class="text-start">
                                            <strong class="text-primary"><?= htmlspecialchars($row['nama_outlet']) ?></strong>
                                            <?php if (!empty($row['kecamatan'])) : ?>
                                                <br><small class="text-muted"><i class="fa fa-map-marker me-1"></i><?= htmlspecialchars($row['kecamatan']) ?></small>
                                            <?php endif; ?>
                                            <?php if (!empty($row['alamat_outlet'])) : ?>
                                                <button type="button" class="btn btn-outline-info btn-xs ms-1 btn-detail-alamat-outlet" 
                                                        data-nama="<?= htmlspecialchars($row['nama_outlet']) ?>" 
                                                        data-alamat="<?= htmlspecialchars($row['alamat_outlet']) ?>" 
                                                        title="Lihat Alamat Lengkap Outlet">
                                                    <i class="fa fa-info-circle"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-start">
                                            <span><?= htmlspecialchars($row['nama_investor'] ?? '-') ?></span>
                                            <?php if (!empty($row['kecamatan_investor'])) : ?>
                                                <br><small class="text-muted"><i class="fa fa-map-marker me-1"></i><?= htmlspecialchars($row['kecamatan_investor']) ?></small>
                                            <?php endif; ?>
                                            <?php if (!empty($row['alamat_investor'])) : ?>
                                                <button type="button" class="btn btn-outline-info btn-xs ms-1 btn-detail-alamat-investor" 
                                                        data-nama="<?= htmlspecialchars($row['nama_investor'] ?? '-') ?>" 
                                                        data-alamat="<?= htmlspecialchars($row['alamat_investor']) ?>" 
                                                        title="Lihat Alamat Lengkap Investor">
                                                    <i class="fa fa-info-circle"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
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
    if ($.fn.DataTable) {
        if (!$.fn.DataTable.isDataTable('#table-top-omzet')) {
            $('#table-top-omzet').DataTable({
                processing: true,
                deferRender: true,
                scrollX: true,
                pageLength: 5,
                lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
                language: {
                    searchPlaceholder: 'Cari omzet...',
                    sSearch: '',
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    paginate: { first: 'First', last: 'Last', next: 'Next', previous: 'Previous' },
                    emptyTable: 'Belum ada data omzet.'
                },
                order: [[3, 'desc']]
            });
        }

        if (!$.fn.DataTable.isDataTable('#table-recent-requests')) {
            $('#table-recent-requests').DataTable({
                processing: true,
                deferRender: true,
                scrollX: true,
                pageLength: 5,
                lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
                language: {
                    searchPlaceholder: 'Cari request...',
                    sSearch: '',
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    paginate: { first: 'First', last: 'Last', next: 'Next', previous: 'Previous' },
                    emptyTable: 'Belum ada request outlet.'
                },
                order: [[1, 'desc']]
            });
        }

        if ($.fn.select2) {
            setTimeout(function() {
                $('#table-top-omzet_wrapper .dataTables_length select, #table-recent-requests_wrapper .dataTables_length select').select2({
                    minimumResultsForSearch: Infinity,
                    width: 'auto'
                });
            }, 50);
        }
    }

    $('.btn-detail-alamat-outlet').on('click', function() {
        let nama = $(this).data('nama');
        let alamat = $(this).data('alamat');
        let queryStr = encodeURIComponent(nama + ' ' + alamat);
        let mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' + queryStr;

        Swal.fire({
            title: 'Alamat Lengkap Outlet',
            html: '<p class="text-start mb-2"><strong>Outlet:</strong> ' + nama + '</p>' +
                  '<div class="p-3 bg-light rounded text-start border">' +
                    '<i class="fa fa-map-marker-alt me-2 text-danger"></i>' +
                    '<a href="' + mapsUrl + '" target="_blank" class="text-primary text-decoration-underline fw-semibold" title="Klik untuk membuka Geotag Google Maps">' +
                      alamat + ' <i class="fas fa-external-link-alt ms-1 text-muted" style="font-size: 11px;"></i>' +
                    '</a>' +
                  '</div>' +
                  '<small class="text-muted d-block text-start mt-2"><i class="fas fa-info-circle me-1"></i>Klik teks alamat di atas untuk membuka lokasi di Google Maps</small>',
            icon: 'info',
            confirmButtonText: 'Tutup'
        });
    });

    $('.btn-detail-alamat-investor').on('click', function() {
        let nama = $(this).data('nama');
        let alamat = $(this).data('alamat');
        let queryStr = encodeURIComponent(alamat);
        let mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' + queryStr;

        Swal.fire({
            title: 'Alamat Lengkap Investor',
            html: '<p class="text-start mb-2"><strong>Investor:</strong> ' + nama + '</p>' +
                  '<div class="p-3 bg-light rounded text-start border">' +
                    '<i class="fa fa-map-marker-alt me-2 text-danger"></i>' +
                    '<a href="' + mapsUrl + '" target="_blank" class="text-primary text-decoration-underline fw-semibold" title="Klik untuk membuka Geotag Google Maps">' +
                      alamat + ' <i class="fas fa-external-link-alt ms-1 text-muted" style="font-size: 11px;"></i>' +
                    '</a>' +
                  '</div>' +
                  '<small class="text-muted d-block text-start mt-2"><i class="fas fa-info-circle me-1"></i>Klik teks alamat di atas untuk membuka lokasi di Google Maps</small>',
            icon: 'info',
            confirmButtonText: 'Tutup'
        });
    });
});
</script>

