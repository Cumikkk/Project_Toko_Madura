<?php
use Config\Core\Database;
use App\Models\User;
use Config\Core\SystemInfo;

$user = User::user();
$db = Database::connect();
$userId = (int)($user['MBR_ID'] ?? $user['id_users'] ?? 0);

// Fetch all investors for Master Owner with complete outlet status breakdown
$sqlInv = "
    SELECT 
        i.id_investor,
        u.nama_lengkap,
        u.username,
        u.no_hp,
        i.kecamatan,
        i.alamat_investor,
        i.tanggal_bergabung,
        COUNT(o.id_outlet) as total_outlet,
        SUM(CASE WHEN o.status = 'active' AND (o.tgl_jatuh_tempo IS NULL OR o.tgl_jatuh_tempo >= NOW()) THEN 1 ELSE 0 END) as total_aktif,
        SUM(CASE WHEN o.status = 'active' AND o.tgl_jatuh_tempo < NOW() THEN 1 ELSE 0 END) as total_expired,
        SUM(CASE WHEN o.status = 'pending' THEN 1 ELSE 0 END) as total_pending,
        SUM(CASE WHEN o.status = 'reject' THEN 1 ELSE 0 END) as total_reject
    FROM investor i
    JOIN users u ON u.id_users = i.id_users
    LEFT JOIN outlet o ON o.id_investor = i.id_investor
    WHERE i.id_master = {$userId} OR i.id_master IS NULL
    GROUP BY i.id_investor
    ORDER BY i.id_investor DESC
";

$resInvestors = $db->query($sqlInv);
$investorList = [];
$sumInvestors = 0;
$sumOutlets = 0;
$sumAktif = 0;

if ($resInvestors && $resInvestors->num_rows > 0) {
    while ($row = $resInvestors->fetch_assoc()) {
        $investorList[] = $row;
        $sumInvestors++;
        $sumOutlets += (int)$row['total_outlet'];
        $sumAktif += (int)$row['total_aktif'];
    }
}
?>

<!-- 1. Header Banner Card (Maroon Gradient Style) -->
<div class="card border-0 shadow-sm mb-4 text-white overflow-hidden position-relative" style="background: linear-gradient(135deg, #7D0A0A 0%, #4A0404 100%); border-radius: 16px;">
    <div class="card-body p-4 position-relative z-1 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-white bg-opacity-15 p-3 d-flex align-items-center justify-content-center shadow-xs" style="width: 58px; height: 58px; backdrop-filter: blur(4px);">
                <i class="fa-solid fa-users-gear text-warning fs-3"></i>
            </div>
            <div>
                <h4 class="fw-extrabold text-white mb-1" style="letter-spacing: 0.3px;">Data Investor Pemodal</h4>
                <p class="text-white-50 small mb-0">Monitoring portofolio mitra investor dan daftar toko yang berada di bawah naungan Master Owner</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 bg-white bg-opacity-10 px-3 py-2 rounded-pill border border-white border-opacity-20 shadow-xs" style="backdrop-filter: blur(6px);">
            <i class="fa-solid fa-crown text-warning me-1"></i>
            <span class="fw-bold text-white small text-uppercase">MASTER OWNER PANEL</span>
        </div>
    </div>
</div>

<!-- 2. Metrics Summary Cards -->
<div class="row g-3 mb-4">
    <!-- Card 1: Total Investor -->
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100 p-3" style="border-radius: 16px; background: var(--bs-card-bg, #fff);">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-body-secondary small fw-bold text-uppercase d-block mb-1">Total Investor Mitra</span>
                    <h3 class="fw-extrabold text-body-emphasis mb-0"><?= number_format($sumInvestors); ?></h3>
                    <small class="text-secondary" style="font-size: 11.5px;"><i class="fa-solid fa-user-check text-success me-1"></i>Terhubung dengan Master</small>
                </div>
                <div class="rounded-4 bg-danger bg-opacity-10 p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="fa-solid fa-user-tie text-danger fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Total Seluruh Outlet -->
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100 p-3" style="border-radius: 16px; background: var(--bs-card-bg, #fff);">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-body-secondary small fw-bold text-uppercase d-block mb-1">Total Toko / Outlet</span>
                    <h3 class="fw-extrabold text-primary mb-0"><?= number_format($sumOutlets); ?></h3>
                    <small class="text-secondary" style="font-size: 11.5px;"><i class="fa-solid fa-store me-1 text-primary"></i>Keseluruhan Portofolio</small>
                </div>
                <div class="rounded-4 bg-primary bg-opacity-10 p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="fa-solid fa-store text-primary fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 3: Total Outlet Aktif -->
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100 p-3" style="border-radius: 16px; background: var(--bs-card-bg, #fff);">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-body-secondary small fw-bold text-uppercase d-block mb-1">Outlet Langganan Aktif</span>
                    <h3 class="fw-extrabold text-success mb-0"><?= number_format($sumAktif); ?></h3>
                    <small class="text-success" style="font-size: 11.5px;"><i class="fa-solid fa-circle-check me-1"></i>Masa aktif berjalan</small>
                </div>
                <div class="rounded-4 bg-success bg-opacity-10 p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="fa-solid fa-circle-check text-success fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 3. Table Card Data Investor -->
<div class="card border-0 shadow-sm" style="border-radius: 16px; background: var(--bs-card-bg, #fff);">
    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-users text-danger fs-5"></i>
            <h5 class="fw-bold text-body-emphasis mb-0">Daftar Investor Mitra</h5>
        </div>
        <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1.5 fw-semibold fs-12">
            <i class="fa-solid fa-shield-halved me-1"></i>Master Owner View
        </span>
    </div>
    <div class="card-body p-3 p-md-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle w-100" id="table-master-investor">
                <thead class="bg-body-secondary text-uppercase small text-body-secondary">
                    <tr>
                        <th class="text-center" style="width: 5%;">No</th>
                        <th>Nama Investor</th>
                        <th>Lokasi & Alamat</th>
                        <th class="text-center">Portofolio Outlet</th>
                        <th class="text-center">Tanggal Bergabung</th>
                        <th class="text-center" style="width: 15%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($investorList)) : ?>
                        <?php $no = 1; foreach ($investorList as $inv) : ?>
                            <tr>
                                <td class="text-center fw-bold text-body-secondary"><?= $no++ ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px; font-size: 14px;">
                                            <?= strtoupper(substr($inv['nama_lengkap'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <strong class="text-body-emphasis fs-6 d-block mb-0"><?= htmlspecialchars($inv['nama_lengkap']) ?></strong>
                                            <small class="text-body-secondary">
                                                <i class="fa-solid fa-at me-1 text-danger"></i><?= htmlspecialchars($inv['username'] ?? '-') ?> &bull; 
                                                <i class="fa-solid fa-phone ms-1 me-1 text-success"></i><?= htmlspecialchars($inv['no_hp'] ?? '-') ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="badge bg-light text-body-emphasis border"><i class="fa-solid fa-location-dot me-1 text-danger"></i>Kec. <?= htmlspecialchars($inv['kecamatan'] ?: 'N/A') ?></span>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-detail-alamat-investor rounded-pill px-2.5 py-0.5" style="font-size: 11px; font-weight: 600;"
                                                data-nama="<?= htmlspecialchars($inv['nama_lengkap']) ?>"
                                                data-kecamatan="<?= htmlspecialchars($inv['kecamatan'] ?: '-') ?>"
                                                data-alamat="<?= htmlspecialchars($inv['alamat_investor'] ?: '-') ?>">
                                            <i class="fa-solid fa-map-location-dot me-1"></i> Detail Alamat
                                        </button>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex flex-column align-items-center gap-1">
                                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-bold fs-12">
                                            <i class="fa-solid fa-store me-1"></i><?= $inv['total_outlet'] ?> Toko
                                        </span>
                                        <?php if ($inv['total_outlet'] > 0) : ?>
                                            <div class="d-flex align-items-center gap-1 mt-0.5">
                                                <?php if ($inv['total_aktif'] > 0) : ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size: 10px;" title="Toko Aktif">
                                                        <i class="fa-solid fa-circle-check me-1"></i><?= $inv['total_aktif'] ?> Aktif
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($inv['total_expired'] > 0) : ?>
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5" style="font-size: 10px;" title="Toko Expired">
                                                        <i class="fa-solid fa-triangle-exclamation me-1"></i><?= $inv['total_expired'] ?> Expired
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($inv['total_pending'] > 0) : ?>
                                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2 py-0.5" style="font-size: 10px;" title="Menunggu Konfirmasi">
                                                        <i class="fa-solid fa-clock me-1"></i><?= $inv['total_pending'] ?> Pending
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-center small text-body-secondary">
                                    <?= !empty($inv['tanggal_bergabung']) ? date("d M Y", strtotime($inv['tanggal_bergabung'])) : '-' ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm btn-lihat-outlet rounded-pill px-3 py-1 shadow-xs fw-semibold" data-id="<?= $inv['id_investor'] ?>" data-nama="<?= htmlspecialchars($inv['nama_lengkap']) ?>">
                                        <i class="fa-solid fa-eye me-1"></i> Lihat Toko
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-body-secondary">
                                <i class="fa-solid fa-users-slash fs-1 text-muted opacity-50 mb-2 d-block"></i>
                                Belum ada data investor terdaftar di sistem.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detail Alamat Investor (SweetAlert2 Trigger & Standard Fallback) -->
<script type="text/javascript">
$(document).ready(function() {
    if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#table-master-investor')) {
        $('#table-master-investor').DataTable({
            processing: true,
            scrollX: true,
            language: {
                search: "Cari Investor:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ investor",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "Lanjut",
                    previous: "Kembali"
                }
            }
        });
    }

    $(document).on('click', '.btn-detail-alamat-investor', function() {
        const nama = $(this).data('nama');
        const kec = $(this).data('kecamatan');
        const alamat = $(this).data('alamat');

        let html = `
            <div class="text-start fs-14">
                <div class="p-3 bg-light rounded-3 border mb-2">
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-body-secondary"><i class="fa-solid fa-user-tie text-danger me-2"></i>Nama Investor</span>
                        <span class="fw-bold text-dark">${nama}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-body-secondary"><i class="fa-solid fa-map-location-dot text-primary me-2"></i>Kecamatan</span>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3">${kec}</span>
                    </div>
                    <div class="pt-1">
                        <span class="text-body-secondary d-block mb-1"><i class="fa-solid fa-location-dot text-danger me-2"></i>Alamat Lengkap:</span>
                        <div class="p-2.5 bg-white rounded border text-dark fw-semibold" style="font-size: 13.5px; line-height: 1.5;">${alamat}</div>
                    </div>
                </div>
            </div>
        `;

        Swal.fire({
            title: '<div class="fw-bold text-danger fs-5"><i class="fa-solid fa-building-user me-2"></i>Detail Lokasi Investor</div>',
            html: html,
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#7D0A0A',
            customClass: {
                popup: 'rounded-4'
            }
        });
    });

    $('.btn-lihat-outlet').on('click', function() {
        let idInv = $(this).data('id');
        let namaInv = $(this).data('nama');

        $('#modal-investor-nama').text(namaInv);
        $('#container-detail-outlet').html('<tr><td colspan="5" class="text-center py-4 text-muted"><i class="fa-solid fa-spinner fa-spin me-2"></i>Memuat daftar toko...</td></tr>');
        $('#modalDetailOutlet').modal('show');

        $.get("<?= SystemInfo::app('CLIENT_URL') ?>/ajax/get/investor/outlets", { id_investor: idInv }, function(resp) {
            if (resp.success && resp.data.length > 0) {
                let html = '';
                $.each(resp.data, function(idx, item) {
                    let kecText = item.kecamatan ? 'Kec. ' + item.kecamatan : '-';
                    let alamatBtn = '';
                    if (item.alamat_outlet) {
                        let safeNama = String(item.nama_outlet).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                        let safeAlamat = String(item.alamat_outlet).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                        alamatBtn = `
                            <button type="button" class="btn btn-outline-danger btn-xs ms-1 btn-detail-alamat-outlet-item" 
                                    data-nama="${safeNama}" 
                                    data-kecamatan="${kecText}"
                                    data-alamat="${safeAlamat}" 
                                    title="Lihat Alamat Lengkap">
                                <i class="fa-solid fa-circle-info"></i>
                            </button>
                        `;
                    }
                    let locColHtml = `<span>${kecText}</span>${alamatBtn}`;

                    let statusBadge = '';
                    if (item.status === 'active') {
                        statusBadge = '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1"><i class="fa-solid fa-circle-check me-1"></i>Aktif</span>';
                    } else if (item.status === 'pending') {
                        statusBadge = '<span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1"><i class="fa-solid fa-clock me-1"></i>Menunggu Verifikasi</span>';
                    } else if (item.status === 'reject') {
                        statusBadge = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1"><i class="fa-solid fa-circle-xmark me-1"></i>Ditolak Admin</span>';
                    } else {
                        statusBadge = '<span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5 py-1">Non-Aktif</span>';
                    }

                    let tglJoin = item.tanggal_bergabung ? item.tanggal_bergabung : (item.tanggal_disetujui ? item.tanggal_disetujui : '-');

                    html += `
                        <tr>
                            <td class="text-center fw-bold text-muted">${idx + 1}</td>
                            <td><strong class="text-primary fs-6">${item.nama_outlet}</strong></td>
                            <td><small class="text-body-secondary">${locColHtml}</small></td>
                            <td class="text-center">${statusBadge}</td>
                            <td class="text-center small text-body-secondary">${tglJoin}</td>
                        </tr>
                    `;
                });
                $('#container-detail-outlet').html(html);
            } else {
                $('#container-detail-outlet').html('<tr><td colspan="5" class="text-center py-4 text-muted"><i class="fa-solid fa-store-slash me-2 opacity-50"></i>Investor ini belum memiliki toko terdaftar.</td></tr>');
            }
        }, 'json');
    });

    $(document).on('click', '.btn-detail-alamat-outlet-item', function() {
        const nama = $(this).data('nama');
        const kec = $(this).data('kecamatan');
        const alamat = $(this).data('alamat');

        let html = `
            <div class="text-start fs-14">
                <div class="p-3 bg-light rounded-3 border mb-2">
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-body-secondary"><i class="fa-solid fa-store text-danger me-2"></i>Nama Outlet</span>
                        <span class="fw-bold text-dark">${nama}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-body-secondary"><i class="fa-solid fa-map-location-dot text-primary me-2"></i>Kecamatan</span>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3">${kec}</span>
                    </div>
                    <div class="pt-1">
                        <span class="text-body-secondary d-block mb-1"><i class="fa-solid fa-location-dot text-danger me-2"></i>Alamat Lengkap Outlet:</span>
                        <div class="p-2.5 bg-white rounded border text-dark fw-semibold" style="font-size: 13.5px; line-height: 1.5;">${alamat}</div>
                    </div>
                </div>
            </div>
        `;

        Swal.fire({
            title: '<div class="fw-bold text-danger fs-5"><i class="fa-solid fa-building-user me-2"></i>Detail Lokasi Outlet</div>',
            html: html,
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#7D0A0A',
            customClass: {
                popup: 'rounded-4'
            }
        });
    });
});
</script>

<!-- Modal Detail Outlet Investor (Maroon Gradient Style) -->
<div class="modal fade" id="modalDetailOutlet" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header text-white px-4 py-3 border-0" style="background: linear-gradient(135deg, #7D0A0A 0%, #4A0404 100%);">
                <h5 class="modal-title d-flex align-items-center gap-2">
                    <i class="fa-solid fa-store text-warning"></i>
                    <span>Portofolio Toko Outlet: <span id="modal-investor-nama" class="fw-extrabold text-warning"></span></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-body-secondary text-uppercase small text-body-secondary">
                            <tr>
                                <th class="text-center" style="width: 5%;">No</th>
                                <th>Nama Outlet</th>
                                <th>Kecamatan & Detail Alamat</th>
                                <th class="text-center">Status Toko</th>
                                <th class="text-center">Tanggal Join</th>
                            </tr>
                        </thead>
                        <tbody id="container-detail-outlet">
                            <tr><td colspan="5" class="text-center py-4 text-muted"><i class="fa-solid fa-spinner fa-spin me-2"></i>Memuat data outlet...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-2.5 border-0">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
