<?php
use Config\Core\SystemInfo;
use App\Models\Dashboard;

// -------------------------------------------------------------------------
// DASHBOARD GENERAL PROGRAMMER (ADMIN PORTAL)
// -------------------------------------------------------------------------

// Counts per Role & Entity
$adminCount    = Dashboard::getRoleCount('admin');
$masterCount   = Dashboard::getRoleCount('master');
$investorCount = Dashboard::getRoleCount('investor');
$outletCount   = Dashboard::getOutletCount();

// Outlet berdasarkan Omzet
$topOutlets = Dashboard::getTopByOmzet();

// Request Outlet Terbaru (Khusus Pending)
$recentRequests = Dashboard::getRecentRequests();
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
                    <h6 class="mb-3 text-muted">Total Outlet Aktif</h6>
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
                    <a href="<?= SystemInfo::app('ADMIN_URL') ?>/outlet/omzet" class="btn btn-primary btn-sm"><i class="fas fa-list me-1"></i> Lihat Semua</a>
                </div>
            </div>
            <div class="card-body d-flex flex-column flex-grow-1">
                <div class="table-responsive flex-grow-1">
                    <table class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100 align-middle mb-0" id="table-top-omzet">
                        <thead>
                            <tr class="text-center">
                                <th class="text-center" style="width: 8%;">NO</th>
                                <th class="text-center">NAMA OUTLET</th>
                                <th class="text-center">INVESTOR</th>
                                <th class="text-center">TOTAL OMZET</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($topOutlets && $topOutlets->num_rows > 0) : ?>
                                <?php $no = 1; while ($row = $topOutlets->fetch_assoc()) : ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td class="text-start">
                                            <strong class="text-primary"><?= htmlspecialchars($row['nama_outlet']) ?></strong>
                                            <?php if (!empty($row['kecamatan_outlet']) && $row['kecamatan_outlet'] !== '-') : ?>
                                                <br>
                                                <?php if (!empty($row['alamat_outlet'])) : ?>
                                                    <span class="badge bg-light text-dark border btn-detail-alamat-outlet shadow-xs mt-1 py-1 px-2" style="cursor: pointer; font-size: 13px; font-weight: 500;" 
                                                          data-nama="<?= htmlspecialchars($row['nama_outlet']) ?>" 
                                                          data-alamat="<?= htmlspecialchars($row['alamat_outlet']) ?>" 
                                                          data-provinsi="<?= htmlspecialchars($row['provinsi_outlet'] ?? '') ?>"
                                                          data-kabupaten="<?= htmlspecialchars($row['kabupaten_outlet'] ?? '') ?>"
                                                          data-kecamatan="<?= htmlspecialchars($row['kecamatan_outlet'] ?? '') ?>"
                                                          data-kelurahan="<?= htmlspecialchars($row['kelurahan_outlet'] ?? '') ?>"
                                                          title="Klik untuk lihat detail alamat">
                                                        <i class="fa fa-map-marker text-danger me-1"></i><?= htmlspecialchars(ucwords(strtolower($row['kelurahan_outlet'] ?? ''))) ?>, Kec. <?= htmlspecialchars(ucwords(strtolower($row['kecamatan_outlet'] ?? ''))) ?>
                                                    </span>
                                                <?php else : ?>
                                                    <span class="text-muted" style="font-size: 13px;"><i class="fa fa-map-marker me-1"></i><?= htmlspecialchars(ucwords(strtolower($row['kelurahan_outlet'] ?? ''))) ?>, Kec. <?= htmlspecialchars(ucwords(strtolower($row['kecamatan_outlet'] ?? ''))) ?></span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-start">
                                            <strong class="text-primary"><?= htmlspecialchars($row['nama_investor'] ?? '-') ?></strong>
                                            <?php if (!empty($row['kecamatan_investor']) && $row['kecamatan_investor'] !== '-') : ?>
                                                <br>
                                                <?php if (!empty($row['alamat_investor'])) : ?>
                                                    <span class="badge bg-light text-dark border btn-detail-alamat-investor shadow-xs mt-1 py-1 px-2" style="cursor: pointer; font-size: 13px; font-weight: 500;" 
                                                          data-nama="<?= htmlspecialchars($row['nama_investor'] ?? '-') ?>" 
                                                          data-alamat="<?= htmlspecialchars($row['alamat_investor']) ?>" 
                                                          data-provinsi="<?= htmlspecialchars($row['provinsi_investor'] ?? '') ?>"
                                                          data-kabupaten="<?= htmlspecialchars($row['kabupaten_investor'] ?? '') ?>"
                                                          data-kecamatan="<?= htmlspecialchars($row['kecamatan_investor'] ?? '') ?>"
                                                          data-kelurahan="<?= htmlspecialchars($row['kelurahan_investor'] ?? '') ?>"
                                                          title="Klik untuk lihat detail alamat">
                                                        <i class="fa fa-map-marker text-danger me-1"></i><?= htmlspecialchars(ucwords(strtolower($row['kelurahan_investor'] ?? ''))) ?>, Kec. <?= htmlspecialchars(ucwords(strtolower($row['kecamatan_investor'] ?? ''))) ?>
                                                    </span>
                                                <?php else : ?>
                                                    <span class="text-muted" style="font-size: 13px;"><i class="fa fa-map-marker me-1"></i><?= htmlspecialchars(ucwords(strtolower($row['kelurahan_investor'] ?? ''))) ?>, Kec. <?= htmlspecialchars(ucwords(strtolower($row['kecamatan_investor'] ?? ''))) ?></span>
                                                <?php endif; ?>
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
                                <th class="text-center" style="width: 8%;">NO</th>
                                <th class="text-center">TANGGAL REQUEST</th>
                                <th class="text-center">NAMA OUTLET</th>
                                <th class="text-center">INVESTOR</th>
                                <th class="text-center">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recentRequests && $recentRequests->num_rows > 0) : ?>
                                <?php $noReq = 1; while ($row = $recentRequests->fetch_assoc()) : ?>
                                    <tr>
                                        <td class="text-center"><?= $noReq++ ?></td>
                                        <td class="text-center">
                                            <?= !empty($row['tgl_request']) ? date('d/m/Y H:i', strtotime($row['tgl_request'])) : '-' ?>
                                        </td>
                                        <td class="text-start">
                                            <strong class="text-primary"><?= htmlspecialchars($row['nama_outlet']) ?></strong>
                                            <?php if (!empty($row['kecamatan_outlet']) && $row['kecamatan_outlet'] !== '-') : ?>
                                                <br>
                                                <?php if (!empty($row['alamat_outlet'])) : ?>
                                                    <span class="badge bg-light text-dark border btn-detail-alamat-outlet shadow-xs mt-1 py-1 px-2" style="cursor: pointer; font-size: 13px; font-weight: 500;" 
                                                          data-nama="<?= htmlspecialchars($row['nama_outlet']) ?>" 
                                                          data-alamat="<?= htmlspecialchars($row['alamat_outlet']) ?>" 
                                                          data-provinsi="<?= htmlspecialchars($row['provinsi_outlet'] ?? '') ?>"
                                                          data-kabupaten="<?= htmlspecialchars($row['kabupaten_outlet'] ?? '') ?>"
                                                          data-kecamatan="<?= htmlspecialchars($row['kecamatan_outlet'] ?? '') ?>"
                                                          data-kelurahan="<?= htmlspecialchars($row['kelurahan_outlet'] ?? '') ?>"
                                                          title="Klik untuk lihat detail alamat">
                                                        <i class="fa fa-map-marker text-danger me-1"></i><?= htmlspecialchars(ucwords(strtolower($row['kelurahan_outlet'] ?? ''))) ?>, Kec. <?= htmlspecialchars(ucwords(strtolower($row['kecamatan_outlet'] ?? ''))) ?>
                                                    </span>
                                                <?php else : ?>
                                                    <span class="text-muted" style="font-size: 13px;"><i class="fa fa-map-marker me-1"></i><?= htmlspecialchars(ucwords(strtolower($row['kelurahan_outlet'] ?? ''))) ?>, Kec. <?= htmlspecialchars(ucwords(strtolower($row['kecamatan_outlet'] ?? ''))) ?></span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-start">
                                            <strong class="text-primary"><?= htmlspecialchars($row['nama_investor'] ?? '-') ?></strong>
                                            <?php if (!empty($row['kecamatan_investor']) && $row['kecamatan_investor'] !== '-') : ?>
                                                <br>
                                                <?php if (!empty($row['alamat_investor'])) : ?>
                                                    <span class="badge bg-light text-dark border btn-detail-alamat-investor shadow-xs mt-1 py-1 px-2" style="cursor: pointer; font-size: 13px; font-weight: 500;" 
                                                          data-nama="<?= htmlspecialchars($row['nama_investor'] ?? '-') ?>" 
                                                          data-alamat="<?= htmlspecialchars($row['alamat_investor']) ?>" 
                                                          data-provinsi="<?= htmlspecialchars($row['provinsi_investor'] ?? '') ?>"
                                                          data-kabupaten="<?= htmlspecialchars($row['kabupaten_investor'] ?? '') ?>"
                                                          data-kecamatan="<?= htmlspecialchars($row['kecamatan_investor'] ?? '') ?>"
                                                          data-kelurahan="<?= htmlspecialchars($row['kelurahan_investor'] ?? '') ?>"
                                                          title="Klik untuk lihat detail alamat">
                                                        <i class="fa fa-map-marker text-danger me-1"></i><?= htmlspecialchars(ucwords(strtolower($row['kelurahan_investor'] ?? ''))) ?>, Kec. <?= htmlspecialchars(ucwords(strtolower($row['kecamatan_investor'] ?? ''))) ?>
                                                    </span>
                                                <?php else : ?>
                                                    <span class="text-muted" style="font-size: 13px;"><i class="fa fa-map-marker me-1"></i><?= htmlspecialchars(ucwords(strtolower($row['kelurahan_investor'] ?? ''))) ?>, Kec. <?= htmlspecialchars(ucwords(strtolower($row['kecamatan_investor'] ?? ''))) ?></span>
                                                <?php endif; ?>
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
                order: [[3, 'desc']],
                columnDefs: [
                    { orderable: false, targets: 0 }
                ],
                drawCallback: function (settings) {
                    var api = this.api();
                    var startIndex = api.context[0]._iDisplayStart;
                    api.column(0, { page: 'current' }).nodes().each(function (cell, i) {
                        cell.innerHTML = startIndex + i + 1;
                    });
                }
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
                order: [[1, 'desc']],
                columnDefs: [
                    { orderable: false, targets: 0 }
                ],
                drawCallback: function (settings) {
                    var api = this.api();
                    var startIndex = api.context[0]._iDisplayStart;
                    api.column(0, { page: 'current' }).nodes().each(function (cell, i) {
                        cell.innerHTML = startIndex + i + 1;
                    });
                }
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

    $(document).on('click', '.btn-detail-alamat-outlet', function() {
        let nama = $(this).data('nama');
        let alamat = $(this).data('alamat');
        let provinsi = $(this).data('provinsi') || '';
        let kabupaten = $(this).data('kabupaten') || '';
        let kecamatan = $(this).data('kecamatan') || '';
        let kelurahan = $(this).data('kelurahan') || '';

        let queryStr = encodeURIComponent(alamat);
        let mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' + queryStr;

        let wilayahStr = '';
        if (kelurahan) wilayahStr += kelurahan.toLowerCase() + ', ';
        if (kecamatan) wilayahStr += 'Kec. ' + kecamatan.toLowerCase() + ', ';
        if (kabupaten) wilayahStr += 'Kab. ' + kabupaten.toLowerCase() + ', ';
        if (provinsi) wilayahStr += 'Prov. ' + provinsi.toLowerCase();
        wilayahStr = wilayahStr.replace(/,\s*$/, '');

        Swal.fire({
            title: 'Alamat Lengkap Outlet',
            html: '<div class="text-start mb-3" style="display: grid; grid-template-columns: max-content auto 1fr; column-gap: 8px; row-gap: 8px; font-size: 15px; line-height: 1.6;">' +
                    '<div class="fw-bold text-dark">Outlet</div>' +
                    '<div class="fw-bold text-dark">:</div>' +
                    '<div class="text-dark">' + nama + '</div>' +
                    '<div class="fw-bold text-dark">Wilayah</div>' +
                    '<div class="fw-bold text-dark">:</div>' +
                    '<div class="text-capitalize text-dark">' + wilayahStr + '</div>' +
                  '</div>' +
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

    $(document).on('click', '.btn-detail-alamat-investor', function() {
        let nama = $(this).data('nama');
        let alamat = $(this).data('alamat');
        let provinsi = $(this).data('provinsi') || '';
        let kabupaten = $(this).data('kabupaten') || '';
        let kecamatan = $(this).data('kecamatan') || '';
        let kelurahan = $(this).data('kelurahan') || '';

        let queryStr = encodeURIComponent(alamat);
        let mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' + queryStr;

        let wilayahStr = '';
        if (kelurahan) wilayahStr += kelurahan.toLowerCase() + ', ';
        if (kecamatan) wilayahStr += 'Kec. ' + kecamatan.toLowerCase() + ', ';
        if (kabupaten) wilayahStr += 'Kab. ' + kabupaten.toLowerCase() + ', ';
        if (provinsi) wilayahStr += 'Prov. ' + provinsi.toLowerCase();
        wilayahStr = wilayahStr.replace(/,\s*$/, '');

        Swal.fire({
            title: 'Alamat Lengkap Investor',
            html: '<div class="text-start mb-3" style="display: grid; grid-template-columns: max-content auto 1fr; column-gap: 8px; row-gap: 8px; font-size: 15px; line-height: 1.6;">' +
                    '<div class="fw-bold text-dark">Investor</div>' +
                    '<div class="fw-bold text-dark">:</div>' +
                    '<div class="text-dark">' + nama + '</div>' +
                    '<div class="fw-bold text-dark">Wilayah</div>' +
                    '<div class="fw-bold text-dark">:</div>' +
                    '<div class="text-capitalize text-dark">' + wilayahStr + '</div>' +
                  '</div>' +
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

