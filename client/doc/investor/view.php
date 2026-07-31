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

if ($resInvestors && $resInvestors->num_rows > 0) {
    while ($row = $resInvestors->fetch_assoc()) {
        $investorList[] = $row;
        $sumInvestors++;
        $sumOutlets += (int)$row['total_outlet'];
    }
}
?>

<div class="main-content-inner py-3 py-md-4">
    <!-- 1. Header Banner Card (Maroon Gradient Style - Matching Client Standard) -->
    <div class="row mb-3 mb-md-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 16px; background: linear-gradient(135deg, #7D0A0A 0%, #4D0709 100%); color: #fff;">
                <div class="card-body p-4 p-md-5">
                    <div class="row align-items-center g-3">
                        <div class="col-12">
                            <span class="badge bg-white text-danger fw-bold px-3 py-2 rounded-pill mb-2 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                <i class="fa-solid fa-crown text-warning me-1"></i> Master Access
                            </span>
                            <h2 class="fw-bold mb-2 text-white fs-3 fs-md-2">Data Investor</h2>
                            <p class="text-white-50 small mb-0">Memantau daftar seluruh mitra investor dan portofolio toko yang berada di bawah naungan Master Owner.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Metrics Summary Cards (Clean Border & Gradient Icons - Matching Outlet Page) -->
    <div class="row g-2 g-md-3 mb-4">
        <!-- Card 1: Total Investor -->
        <div class="col-md-6 col-12">
            <div class="card border border-body-subtle shadow-sm h-100" style="border-radius: 14px; border-left: 5px solid #7D0A0A !important;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: linear-gradient(135deg, #7D0A0A 0%, #4D0709 100%);">
                        <i class="fa-solid fa-user-tie fs-4"></i>
                    </div>
                    <div>
                        <div class="text-body-secondary small fw-semibold">Total Investor Mitra</div>
                        <div class="fs-4 fw-bold text-danger mb-0"><?= number_format($sumInvestors, 0, ',', '.'); ?> <span class="fs-6 fw-normal text-body-secondary">Investor</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Seluruh Outlet -->
        <div class="col-md-6 col-12">
            <div class="card border border-body-subtle shadow-sm h-100" style="border-radius: 14px; border-left: 5px solid #198754 !important;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: linear-gradient(135deg, #198754 0%, #0d5132 100%);">
                        <i class="fa-solid fa-store fs-4"></i>
                    </div>
                    <div>
                        <div class="text-body-secondary small fw-semibold">Total Toko / Outlet</div>
                        <div class="fs-4 fw-bold text-success mb-0"><?= number_format($sumOutlets, 0, ',', '.'); ?> <span class="fs-6 fw-normal text-body-secondary">Outlet</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Table Card Data Investor (Clean Bootstrap Table - 100% Matching Client Outlet Page) -->
    <div class="row">
        <div class="col-12">
            <div class="card border border-body-subtle shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-body py-3 px-4 d-flex align-items-center justify-content-between border-bottom border-body-subtle">
                    <h5 class="fw-bold text-body-emphasis mb-0 fs-6"><i class="fa-solid fa-users me-2 text-danger"></i>Daftar Investor Mitra</h5>
                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1.5 fw-semibold fs-12">
                        <i class="fa-solid fa-shield-halved me-1"></i>Master Owner View
                    </span>
                </div>
                <div class="card-body p-2 p-md-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 w-100">
                            <thead class="table-group-divider bg-body-secondary">
                                <tr class="text-uppercase small text-body-secondary">
                                    <th class="ps-3" style="width: 50px;">No</th>
                                    <th>Nama Investor</th>
                                    <th>Lokasi & Alamat</th>
                                    <th>Portofolio Outlet</th>
                                    <th>Tanggal Bergabung</th>
                                    <th class="text-center" style="width: 120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="border-0">
                                <?php if (!empty($investorList)) : ?>
                                    <?php $no = 1; foreach ($investorList as $inv) : ?>
                                        <tr>
                                            <td class="ps-3 fw-bold text-body-secondary"><?= $no++ ?></td>
                                            <td>
                                                <div class="fw-bold text-body-emphasis mb-0 fs-6"><?= htmlspecialchars($inv['nama_lengkap']) ?></div>
                                                <div class="text-body-secondary small mt-0.5">
                                                    <span class="text-danger me-1"><i class="fa-solid fa-at me-0.5"></i><?= htmlspecialchars($inv['username'] ?? '-') ?></span> &bull; 
                                                    <span class="text-success ms-1"><i class="fa-solid fa-phone me-0.5"></i><?= htmlspecialchars($inv['no_hp'] ?? '-') ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                                    <span class="badge bg-light text-body-secondary border" style="font-size: 11px;">
                                                        <i class="fa-solid fa-location-dot me-1 text-danger"></i><?= htmlspecialchars($inv['kecamatan'] ?: 'N/A') ?>
                                                    </span>
                                                    <button type="button" class="btn btn-xs btn-outline-danger btn-detail-alamat-investor rounded-pill px-2.5 py-1 shadow-xs fw-bold" style="font-size: 10.5px;"
                                                            data-nama="<?= htmlspecialchars($inv['nama_lengkap']) ?>"
                                                            data-kecamatan="<?= htmlspecialchars($inv['kecamatan'] ?: '-') ?>"
                                                            data-alamat="<?= htmlspecialchars($inv['alamat_investor'] ?: '-') ?>">
                                                        <i class="fa-solid fa-map-location-dot me-1"></i>Detail Alamat
                                                    </button>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-bold fs-12">
                                                    <i class="fa-solid fa-store me-1"></i><?= number_format($inv['total_aktif']) ?> Outlet
                                                </span>
                                            </td>
                                            <td class="small text-body-secondary">
                                                <?= !empty($inv['tanggal_bergabung']) ? date("d M Y", strtotime($inv['tanggal_bergabung'])) : '-' ?>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-xs btn-danger btn-lihat-outlet rounded-pill px-3 py-1.5 shadow-xs fw-bold text-nowrap" style="background-color: #7D0A0A; border-color: #7D0A0A; font-size: 11px;" data-id="<?= $inv['id_investor'] ?>" data-nama="<?= htmlspecialchars($inv['nama_lengkap']) ?>">
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
        </div>
    </div>
</div>

<!-- Modal Detail Alamat Investor & Modal Detail Outlet -->
<script type="text/javascript">
$(document).ready(function() {
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
        $('#container-detail-outlet').html('<tr><td colspan="4" class="text-center py-4 text-muted"><i class="fa-solid fa-spinner fa-spin me-2"></i>Memuat daftar toko...</td></tr>');
        $('#modalDetailOutlet').modal('show');

        $.get("<?= SystemInfo::app('CLIENT_URL') ?>/ajax/get/investor/outlets", { id_investor: idInv }, function(resp) {
            if (resp.success && resp.data.length > 0) {
                let html = '';
                $.each(resp.data, function(idx, item) {
                    let kecText = item.kecamatan ? item.kecamatan : '-';
                    let alamatBtn = '';
                    if (item.alamat_outlet) {
                        let safeNama = String(item.nama_outlet).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                        let safeAlamat = String(item.alamat_outlet).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                        alamatBtn = `
                            <button type="button" class="btn btn-xs btn-outline-danger btn-detail-alamat-outlet-item rounded-pill px-2.5 py-1 shadow-xs fw-bold ms-1" 
                                    style="font-size: 10.5px;"
                                    data-nama="${safeNama}" 
                                    data-kecamatan="${kecText}"
                                    data-alamat="${safeAlamat}">
                                <i class="fa-solid fa-map-location-dot me-1"></i>Detail Alamat
                            </button>
                        `;
                    }
                    let locColHtml = `<span class="badge bg-light text-body-secondary border me-1" style="font-size: 11px;"><i class="fa-solid fa-location-dot me-1 text-danger"></i>${kecText}</span>${alamatBtn}`;
                    let tglJoin = item.tanggal_bergabung ? item.tanggal_bergabung : (item.tanggal_disetujui ? item.tanggal_disetujui : '-');

                    html += `
                        <tr>
                            <td class="ps-3 text-center fw-bold text-muted">${idx + 1}</td>
                            <td><strong class="text-body-emphasis fs-6">${item.nama_outlet}</strong></td>
                            <td>${locColHtml}</td>
                            <td class="small text-body-secondary">${tglJoin}</td>
                        </tr>
                    `;
                });
                $('#container-detail-outlet').html(html);
            } else {
                $('#container-detail-outlet').html('<tr><td colspan="4" class="text-center py-4 text-muted"><i class="fa-solid fa-store-slash me-2 opacity-50"></i>Investor ini belum memiliki toko terdaftar.</td></tr>');
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

<!-- Modal Detail Outlet Investor (Maroon Gradient Style - Clean Client Standard) -->
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
                        <thead class="table-group-divider bg-body-secondary text-uppercase small text-body-secondary">
                            <tr>
                                <th class="ps-3 text-center" style="width: 50px;">No</th>
                                <th>Nama Outlet</th>
                                <th>Kecamatan & Detail Alamat</th>
                                <th>Tanggal Join</th>
                            </tr>
                        </thead>
                        <tbody id="container-detail-outlet" class="border-0">
                            <tr><td colspan="4" class="text-center py-4 text-muted"><i class="fa-solid fa-spinner fa-spin me-2"></i>Memuat data outlet...</td></tr>
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
