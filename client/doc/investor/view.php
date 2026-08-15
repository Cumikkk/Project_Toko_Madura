<?php
use Config\Core\Database;
use App\Models\User;
use Config\Core\SystemInfo;

$user = User::user();
$db = Database::connect();
$userId = (int)($user['MBR_ID'] ?? $user['id_users'] ?? 0);

// Fetch distinct kabupaten of investors for filter dropdown
$availableKabupaten = [];
$resKab = $db->query("
    SELECT DISTINCT mw.kabupaten 
    FROM investor i 
    JOIN users u ON u.id_users = i.id_users 
    JOIN master_wilayah mw ON mw.id_wilayah = u.id_wilayah 
    WHERE (i.id_master = {$userId} OR i.id_master IS NULL) 
      AND mw.kabupaten IS NOT NULL AND mw.kabupaten != ''
    ORDER BY mw.kabupaten ASC
");
if ($resKab) {
    while ($kRow = $resKab->fetch_assoc()) {
        $availableKabupaten[] = $kRow['kabupaten'];
    }
}

// Fetch distinct years of investor registration
$availableYears = [];
$resYears = $db->query("
    SELECT DISTINCT YEAR(u.created_at) as y_periode 
    FROM investor i 
    JOIN users u ON u.id_users = i.id_users 
    WHERE (i.id_master = {$userId} OR i.id_master IS NULL) 
    ORDER BY y_periode DESC
");
if ($resYears) {
    while ($yRow = $resYears->fetch_assoc()) {
        if (!empty($yRow['y_periode'])) {
            $availableYears[] = (int)$yRow['y_periode'];
        }
    }
}
if (!in_array((int)date('Y'), $availableYears)) {
    array_unshift($availableYears, (int)date('Y'));
}

// Fetch All Investors List for Master (Instant Client-Side Filtering)
$sqlInv = "
    SELECT 
        i.id_investor,
        u.nama_lengkap,
        u.username,
        u.no_hp,
        mw.provinsi,
        mw.kabupaten,
        mw.kecamatan,
        mw.kelurahan,
        u.alamat_lengkap as alamat_investor,
        u.created_at as tanggal_bergabung,
        COUNT(o.id_outlet) as total_outlet,
        SUM(CASE WHEN (o.status = 'active' OR (o.status IN ('pending', 'reject') AND o.tipe_request = 'perpanjangan')) AND (o.tgl_jatuh_tempo IS NULL OR o.tgl_jatuh_tempo >= NOW()) THEN 1 ELSE 0 END) as total_aktif
    FROM investor i
    JOIN users u ON u.id_users = i.id_users
    LEFT JOIN master_wilayah mw ON mw.id_wilayah = u.id_wilayah
    LEFT JOIN outlet o ON o.id_investor = i.id_investor
    WHERE (i.id_master = {$userId} OR i.id_master IS NULL)
    GROUP BY i.id_investor
    ORDER BY i.id_investor DESC
";

$resInvestors = $db->query($sqlInv);
$investorList = [];
$totalOverallInvestors = 0;
$totalOverallActiveOutlets = 0;

if ($resInvestors && $resInvestors->num_rows > 0) {
    while ($row = $resInvestors->fetch_assoc()) {
        $invId = (int)$row['id_investor'];
        $outlets = [];
        $sqlOut = "SELECT o.nama_outlet, mw_out.provinsi, mw_out.kabupaten, mw_out.kecamatan, mw_out.kelurahan, u.alamat_lengkap as alamat_outlet, 
                          o.tgl_disetujui as tanggal_bergabung 
                   FROM outlet o 
                   JOIN users u ON u.id_users = o.id_users 
                   LEFT JOIN master_wilayah mw_out ON mw_out.id_wilayah = u.id_wilayah
                   WHERE o.id_investor = {$invId} 
                     AND (o.status = 'active' OR (o.status IN ('pending', 'reject') AND o.tipe_request = 'perpanjangan')) 
                     AND (o.tgl_jatuh_tempo IS NULL OR o.tgl_jatuh_tempo >= NOW())
                   ORDER BY o.id_outlet DESC";
        $resOut = $db->query($sqlOut);
        if ($resOut) {
            while ($out = $resOut->fetch_assoc()) {
                $outlets[] = $out;
            }
        }
        $row['outlets_data'] = $outlets;
        $investorList[] = $row;
        $totalOverallInvestors++;
        $totalOverallActiveOutlets += (int)$row['total_aktif'];
    }
}

$bulanIndo = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
?>

<div class="main-content-inner py-3 py-md-4">
    <!-- 1. Header Banner Card (Maroon Gradient Style) -->
    <div class="row mb-3 mb-md-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 16px; background: linear-gradient(135deg, #7D0A0A 0%, #4D0709 100%); color: #fff;">
                <div class="card-body p-4 p-md-5">
                    <div class="row align-items-center g-3">
                        <div class="col-12">
                            <span class="badge bg-white text-danger fw-bold px-3 py-2 rounded-pill mb-2 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                <i class="fa-solid fa-shield-check me-1"></i> Master Access
                            </span>
                            <h2 class="fw-bold mb-2 text-white fs-3 fs-md-2">Data Investor & Kemitraan</h2>
                            <p class="text-white-50 small mb-0">Pantau seluruh investor di bawah jaringan Anda beserta persebaran toko aktif yang dikelola.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Metrics Summary Cards -->
    <div class="row g-2 g-md-3 mb-4">
        <!-- Card 1: Total Investor Terdaftar -->
        <div class="col-md-6 col-12">
            <div class="card border border-body-subtle shadow-sm h-100" style="border-radius: 16px;">
                <div class="card-body p-3 p-md-4 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" style="width: 48px; height: 48px; background: linear-gradient(135deg, #7D0A0A 0%, #500606 100%);">
                        <i class="fa-solid fa-users-gear fs-4"></i>
                    </div>
                    <div>
                        <div class="text-body-secondary text-uppercase fw-bold small mb-1">Total Investor</div>
                        <div class="fs-4 fw-bold text-body-emphasis mb-0" id="metricTotalInvestor"><?= number_format($totalOverallInvestors); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Toko Aktif -->
        <div class="col-md-6 col-12">
            <div class="card border border-body-subtle shadow-sm h-100" style="border-radius: 16px;">
                <div class="card-body p-3 p-md-4 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" style="width: 48px; height: 48px; background: linear-gradient(135deg, #198754 0%, #0d5132 100%);">
                        <i class="fa-solid fa-store fs-4"></i>
                    </div>
                    <div>
                        <div class="text-body-secondary text-uppercase fw-bold small mb-1">Total Toko Aktif</div>
                        <div class="fs-4 fw-bold text-success mb-0" id="metricTotalOutlet"><?= number_format($totalOverallActiveOutlets); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Table Card with Integrated Instant Filter Toolbar -->
    <div class="row">
        <div class="col-12">
            <div class="card border border-body-subtle shadow-sm" style="border-radius: 16px;">
                
                <!-- Card Header with Title & Active Filter Summary -->
                <div class="card-header bg-body py-3 px-3 px-md-4 border-bottom border-body-subtle">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <h5 class="fw-bold text-body-emphasis mb-1 fs-6">
                                <i class="fa-solid fa-list-check me-2 text-danger"></i>Daftar Investor Kemitraan
                            </h5>
                            <p class="text-body-secondary small mb-0">Informasi profil investor, wilayah kemitraan, dan jumlah outlet aktif</p>
                        </div>
                    </div>

                    <!-- INSTANT INLINE FILTER TOOLBAR (No Page Reload) -->
                    <div class="mt-3 pt-3 border-top border-body-subtle" id="toolbarFilter">
                        <div class="row g-2 align-items-end">
                            <!-- 1. Filter Status Outlet -->
                            <div class="col-lg-3 col-md-6 col-12">
                                <label class="form-label text-body-secondary small fw-bold mb-1" style="font-size: 11px;">
                                    <i class="fa-solid fa-store text-danger me-1"></i>Status Outlet
                                </label>
                                <select id="filterStatusOutlet" class="form-select form-select-sm bg-body-tertiary border-body-subtle fw-semibold" style="height: 38px;">
                                    <option value="all">Semua Status Outlet</option>
                                    <option value="active">Punya Outlet Aktif (&gt; 0)</option>
                                    <option value="empty">Belum Punya Outlet (0)</option>
                                </select>
                            </div>

                            <!-- 2. Filter Kabupaten / Kota -->
                            <div class="col-lg-3 col-md-6 col-12">
                                <label class="form-label text-body-secondary small fw-bold mb-1" style="font-size: 11px;">
                                    <i class="fa-solid fa-map-location-dot text-danger me-1"></i>Kabupaten / Kota
                                </label>
                                <select id="filterKabupaten" class="form-select form-select-sm bg-body-tertiary border-body-subtle fw-semibold" style="height: 38px;">
                                    <option value="">Semua Kabupaten / Kota</option>
                                    <?php foreach ($availableKabupaten as $kab) : ?>
                                        <option value="<?= htmlspecialchars(strtoupper($kab)); ?>"><?= htmlspecialchars(ucwords(strtolower($kab))); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- 3. Filter Bulan & Tahun Bergabung -->
                            <div class="col-lg-3 col-md-6 col-12">
                                <label class="form-label text-body-secondary small fw-bold mb-1" style="font-size: 11px;">
                                    <i class="fa-solid fa-calendar-days text-danger me-1"></i>Periode Bergabung
                                </label>
                                <div class="input-group input-group-sm">
                                    <select id="filterBulan" class="form-select form-select-sm bg-body-tertiary border-body-subtle fw-semibold" style="height: 38px;">
                                        <option value="0">Semua Bulan</option>
                                        <?php foreach ($bulanIndo as $mNum => $mName) : ?>
                                            <option value="<?= $mNum; ?>"><?= $mName; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select id="filterTahun" class="form-select form-select-sm bg-body-tertiary border-body-subtle fw-semibold" style="height: 38px;">
                                        <option value="0">Semua Tahun</option>
                                        <?php foreach ($availableYears as $y) : ?>
                                            <option value="<?= $y; ?>"><?= $y; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- 4. Live Search Input & Reset Button -->
                            <div class="col-lg-3 col-md-6 col-12">
                                <label class="form-label text-body-secondary small fw-bold mb-1" style="font-size: 11px;">
                                    <i class="fa-solid fa-magnifying-glass text-danger me-1"></i>Cari Nama / No. HP
                                </label>
                                <div class="d-flex gap-1.5">
                                    <input type="text" id="liveSearchInvestor" class="form-control form-control-sm bg-body-tertiary border-body-subtle fw-semibold" placeholder="Ketik nama / no. hp..." style="height: 38px;">
                                    <button type="button" id="btnResetFilterInvestor" class="btn btn-light border btn-sm px-2.5 d-none align-items-center justify-content-center text-danger fw-semibold text-nowrap" style="height: 38px;" title="Reset Semua Filter">
                                        <i class="fa-solid fa-rotate-left me-1"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-2 p-md-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 w-100" id="tableDataInvestor">
                            <thead class="table-group-divider bg-body-secondary">
                                <tr class="text-uppercase small text-body-secondary">
                                    <th class="ps-3 text-center" style="width: 50px;">No</th>
                                    <th>Nama Investor</th>
                                    <th class="text-center">Wilayah</th>
                                    <th class="text-center">Total Outlet Aktif</th>
                                    <th class="text-center pe-3">Tanggal Bergabung</th>
                                </tr>
                            </thead>
                            <tbody class="border-0" id="tbodyInvestor">
                                <?php if (!empty($investorList)) : ?>
                                    <?php foreach ($investorList as $index => $inv) : 
                                        $invMonth = !empty($inv['tanggal_bergabung']) ? (int)date('n', strtotime($inv['tanggal_bergabung'])) : 0;
                                        $invYear  = !empty($inv['tanggal_bergabung']) ? (int)date('Y', strtotime($inv['tanggal_bergabung'])) : 0;
                                        $statusOutletRow = ((int)$inv['total_aktif'] > 0) ? 'active' : 'empty';
                                        $kabUpper = !empty($inv['kabupaten']) ? strtoupper(trim($inv['kabupaten'])) : '';
                                    ?>
                                        <tr class="investor-data-row" 
                                            data-status="<?= $statusOutletRow; ?>"
                                            data-kabupaten="<?= htmlspecialchars($kabUpper); ?>"
                                            data-bulan="<?= $invMonth; ?>"
                                            data-tahun="<?= $invYear; ?>"
                                            data-outlets-count="<?= (int)$inv['total_aktif']; ?>">
                                            <td class="ps-3 text-center fw-bold text-body-secondary row-index-num"><?= $index + 1; ?></td>
                                            <td>
                                                <div class="fw-bold text-body-emphasis mb-0 fs-6"><?= htmlspecialchars($inv['nama_lengkap']); ?></div>
                                                <div class="text-body-secondary small mt-0.5">
                                                    <span class="text-success"><i class="fa-solid fa-phone me-1"></i><?= htmlspecialchars($inv['no_hp'] ?? '-'); ?></span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <?php if (!empty($inv['kecamatan']) && $inv['kecamatan'] !== '-') : ?>
                                                    <span class="badge bg-light text-dark border btn-detail-alamat-investor shadow-xs py-1.5 px-2.5" style="cursor: pointer; font-size: 13px; font-weight: 500;"
                                                           data-nama="<?= htmlspecialchars($inv['nama_lengkap'], ENT_QUOTES, 'UTF-8'); ?>"
                                                           data-provinsi="<?= htmlspecialchars($inv['provinsi'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                           data-kabupaten="<?= htmlspecialchars($inv['kabupaten'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                           data-kecamatan="<?= htmlspecialchars($inv['kecamatan'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                           data-kelurahan="<?= htmlspecialchars($inv['kelurahan'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                           data-alamat="<?= htmlspecialchars($inv['alamat_investor'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                           title="Klik untuk lihat detail lokasi">
                                                        <i class="fa-solid fa-location-dot me-1 text-danger"></i><?= htmlspecialchars(ucwords(strtolower($inv['kelurahan'] ?? ''))) ?>, Kec. <?= htmlspecialchars(ucwords(strtolower($inv['kecamatan'] ?? ''))) ?>
                                                    </span>
                                                <?php else : ?>
                                                    <span class="text-muted" style="font-size: 13px;">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-bold fs-12 btn-lihat-outlet shadow-sm" style="cursor: pointer;" data-nama="<?= htmlspecialchars($inv['nama_lengkap'], ENT_QUOTES, 'UTF-8'); ?>" data-outlets="<?= htmlspecialchars(json_encode($inv['outlets_data'] ?? []), ENT_QUOTES, 'UTF-8'); ?>" title="Klik untuk melihat detail outlet">
                                                    <i class="fa-solid fa-store me-1"></i><?= number_format($inv['total_aktif']); ?> Outlet
                                                </span>
                                            </td>
                                            <td class="text-center pe-3">
                                                <span class="badge bg-body-tertiary border text-body-emphasis px-2.5 py-1 rounded-3 fw-semibold font-monospace small">
                                                    <i class="fa-regular fa-clock me-1 text-primary"></i>
                                                    <?= !empty($inv['tanggal_bergabung']) ? date("d/m/Y H:i", strtotime($inv['tanggal_bergabung'])) . ' WIB' : '-'; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr id="emptyStaticRow">
                                        <td colspan="5" class="text-center py-5 text-body-secondary">
                                            <i class="fa-solid fa-users-slash fs-1 text-muted opacity-50 mb-2 d-block"></i>
                                            Belum ada data investor terdaftar.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <tr id="noMatchingFilterRow" style="display: none;">
                                    <td colspan="5" class="text-center py-5 text-body-secondary">
                                        <i class="fa-solid fa-filter-circle-xmark fs-1 text-danger opacity-50 mb-2 d-block"></i>
                                        Tidak ada data investor yang sesuai dengan kriteria filter saat ini.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Record Summary Footer -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-3 border-top border-body-subtle mt-2">
                        <div class="small text-body-secondary fw-semibold ms-1">
                            Menampilkan <span class="text-body-emphasis fw-bold" id="footerCountVisible"><?= count($investorList); ?></span> dari <span class="text-body-emphasis fw-bold"><?= count($investorList); ?></span> investor
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {

    var bulanNames = {
        1: 'Januari', 2: 'Februari', 3: 'Maret', 4: 'April', 5: 'Mei', 6: 'Juni',
        7: 'Juli', 8: 'Agustus', 9: 'September', 10: 'Oktober', 11: 'November', 12: 'Desember'
    };

    // INSTANT CLIENT-SIDE FILTER (0 Milidetik, Tanpa Reload Halaman)
    function applyInstantFilterInvestor() {
        let filterStatus = $('#filterStatusOutlet').val();
        let filterKab = ($('#filterKabupaten').val() || '').toUpperCase().trim();
        let filterBulan = parseInt($('#filterBulan').val()) || 0;
        let filterTahun = parseInt($('#filterTahun').val()) || 0;
        let search = ($('#liveSearchInvestor').val() || '').toLowerCase().trim();

        let hasActiveFilter = (filterStatus !== 'all' || filterKab !== '' || filterBulan > 0 || filterTahun > 0 || search !== '');

        if (hasActiveFilter) {
            $('#btnResetFilterInvestor').removeClass('d-none').addClass('d-inline-flex');
        } else {
            $('#btnResetFilterInvestor').removeClass('d-inline-flex').addClass('d-none');
        }

        let visibleCount = 0;
        let totalVisibleOutlets = 0;

        $('.investor-data-row').each(function() {
            let $row = $(this);
            let rowStatus = $row.attr('data-status');
            let rowKab = ($row.attr('data-kabupaten') || '').toUpperCase().trim();
            let rowBulan = parseInt($row.attr('data-bulan')) || 0;
            let rowTahun = parseInt($row.attr('data-tahun')) || 0;
            let rowOutlets = parseInt($row.attr('data-outlets-count')) || 0;
            let rowText = $row.text().toLowerCase();

            let matchStatus = (filterStatus === 'all' || rowStatus === filterStatus);
            let matchKab = (!filterKab || rowKab === filterKab);
            let matchBulan = (filterBulan === 0 || rowBulan === filterBulan);
            let matchTahun = (filterTahun === 0 || rowTahun === filterTahun);
            let matchSearch = (!search || rowText.indexOf(search) > -1);

            if (matchStatus && matchKab && matchBulan && matchTahun && matchSearch) {
                $row.show();
                visibleCount++;
                totalVisibleOutlets += rowOutlets;
                $row.find('.row-index-num').text(visibleCount);
            } else {
                $row.hide();
            }
        });

        $('#metricTotalInvestor').text(visibleCount.toLocaleString('id-ID'));
        $('#metricTotalOutlet').text(totalVisibleOutlets.toLocaleString('id-ID'));
        $('#footerCountVisible').text(visibleCount);

        if (visibleCount === 0) {
            $('#noMatchingFilterRow').show();
        } else {
            $('#noMatchingFilterRow').hide();
        }
    }

    // Attach Instant Events
    $('#filterStatusOutlet, #filterKabupaten, #filterBulan, #filterTahun').on('change', function() {
        applyInstantFilterInvestor();
    });

    $('#liveSearchInvestor').on('keyup search', function() {
        applyInstantFilterInvestor();
    });

    $('#btnResetFilterInvestor').on('click', function() {
        $('#filterStatusOutlet').val('all');
        $('#filterKabupaten').val('');
        $('#filterBulan').val('0');
        $('#filterTahun').val('0');
        $('#liveSearchInvestor').val('');
        applyInstantFilterInvestor();
    });

    function formatWilayahText(kel, kec, kab, prov) {
        let cleanKel = kel ? kel.replace(/^Kel\.\s*/i, '').replace(/^Desa\s*/i, '').trim() : '';
        let cleanKec = kec ? (kec.toLowerCase().startsWith('kec') ? kec : 'Kec. ' + kec) : '';
        let cleanKab = kab ? (kab.toLowerCase().startsWith('kab') || kab.toLowerCase().startsWith('kota') ? kab : 'Kab. ' + kab) : '';
        let cleanProv = prov ? (prov.toLowerCase().startsWith('prov') ? prov : 'Prov. ' + prov) : '';
        return [cleanKel, cleanKec, cleanKab, cleanProv].filter(Boolean).join(', ');
    }

    $(document).on('click', '.btn-detail-alamat-investor', function() {
        let nama = $(this).data('nama');
        let alamat = $(this).data('alamat');
        let provinsi = $(this).data('provinsi') || '';
        let kabupaten = $(this).data('kabupaten') || '';
        let kecamatan = $(this).data('kecamatan') || '';
        let kelurahan = $(this).data('kelurahan') || '';

        let cleanKel = kelurahan ? kelurahan.replace(/^Kel\.\s*/i, '').replace(/^Desa\s*/i, '').trim() : '';
        let cleanKec = kecamatan ? (kecamatan.toLowerCase().startsWith('kec') ? kecamatan : 'Kec. ' + kecamatan) : '';
        let cleanKab = kabupaten ? (kabupaten.toLowerCase().startsWith('kab') || kabupaten.toLowerCase().startsWith('kota') ? kabupaten : 'Kab. ' + kabupaten) : '';
        let cleanProv = provinsi ? (provinsi.toLowerCase().startsWith('prov') ? provinsi : 'Prov. ' + provinsi) : '';
        
        let wilayahStr = [cleanKel, cleanKec, cleanKab, cleanProv].filter(Boolean).join(', ') || '-';
        let queryStr = encodeURIComponent((nama ? nama + ' ' : '') + (kecamatan && kecamatan !== '-' ? 'Kec. ' + kecamatan + ' ' : '') + (alamat && alamat !== '-' ? alamat : ''));
        let mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' + (alamat ? encodeURIComponent(alamat) : queryStr);

        Swal.fire({
            title: `<div class="text-danger fw-extrabold fs-5 mb-0"><i class="fa-solid fa-location-dot me-2"></i>Detail Lokasi Investor</div>`,
            html: `
                <div class="text-start bg-light p-3.5 p-md-4 rounded-4 border border-secondary-subtle mt-2 mb-1 shadow-sm">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2.5 border-bottom gap-2">
                        <span class="text-secondary small fw-bold text-uppercase d-inline-flex align-items-center text-nowrap flex-shrink-0" style="font-size: 11px; letter-spacing: 0.5px;">
                            <i class="fa-solid fa-user-tie text-danger me-2 fs-6"></i>Nama Investor
                        </span>
                        <strong class="text-dark fs-6 ms-2 text-end text-break">${nama || '-'}</strong>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2.5 border-bottom gap-2">
                        <span class="text-secondary small fw-bold text-uppercase d-inline-flex align-items-center text-nowrap flex-shrink-0" style="font-size: 11px; letter-spacing: 0.5px;">
                            <i class="fa-solid fa-map-pin text-danger me-2 fs-6"></i>Wilayah
                        </span>
                        <strong class="text-dark ms-2 text-end text-break" style="font-size: 13.5px; line-height: 1.4;">${wilayahStr}</strong>
                    </div>
                    <div>
                        <div class="text-secondary small fw-bold mb-2 text-uppercase d-inline-flex align-items-center text-nowrap" style="font-size: 11px; letter-spacing: 0.5px;">
                            <i class="fa-solid fa-house-chimney text-danger me-2 fs-6"></i>Alamat Lengkap (Jalan / Geotag)
                        </div>
                        <div class="p-3 bg-white rounded-3 border border-secondary-subtle text-dark fw-semibold" style="font-size: 13.5px; line-height: 1.6; text-align: left; word-break: break-word;">
                            <a href="${mapsUrl}" target="_blank" rel="noopener noreferrer" class="text-primary text-decoration-underline fw-bold d-block mb-1" title="Klik untuk membuka lokasi di Google Maps">
                                ${alamat || '-'} <i class="fa-solid fa-arrow-up-right-from-square ms-1 text-primary" style="font-size: 11px;"></i>
                            </a>
                            <small class="text-muted d-block text-start mt-2 pt-2 border-top" style="font-size: 11px; font-weight: normal;">
                                <i class="fa-solid fa-circle-info me-1 text-danger"></i>Klik teks alamat di atas untuk membuka lokasi di Google Maps (Desktop / Aplikasi HP)
                            </small>
                        </div>
                    </div>
                </div>
            `,
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#7D0A0A',
            customClass: {
                popup: 'rounded-4'
            }
        });
    });

    $(document).on('click', '.btn-lihat-outlet', function() {
        let namaInv = $(this).data('nama');
        let outlets = $(this).data('outlets');

        let html = '<div class="table-responsive"><table class="table table-hover align-middle mb-0 w-100 text-start" style="font-size: 13.5px;">';
        html += '<thead class="table-group-divider bg-body-secondary text-uppercase small text-body-secondary"><tr><th class="ps-3 text-center" style="width: 50px;">No</th><th>Nama Outlet</th><th class="text-center">Wilayah</th><th class="text-center">Tanggal Bergabung</th></tr></thead>';
        html += '<tbody class="border-0">';

        if (outlets && outlets.length > 0) {
            $.each(outlets, function(idx, item) {
                let safeNama = String(item.nama_outlet || '-').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                let safeKel = item.kelurahan ? item.kelurahan : '';
                let safeKec = item.kecamatan ? item.kecamatan : '';
                let safeKab = item.kabupaten ? item.kabupaten : '';
                let safeProv = item.provinsi ? item.provinsi : '';
                let safeAlamat = String(item.alamat_outlet || '-').replace(/"/g, '&quot;').replace(/'/g, '&#39;');

                let cleanKel = safeKel ? safeKel.replace(/^Kel\.\s*/i, '').replace(/^Desa\s*/i, '').trim() : '';
                let wilayahBadgeText = (cleanKel ? cleanKel + ', Kec. ' + safeKec : (safeKec ? 'Kec. ' + safeKec : '-'));
                let locColHtml = item.alamat_outlet ? 
                    `<span class="badge bg-light text-body-secondary border btn-detail-alamat-outlet-item shadow-xs" style="font-size: 11px; cursor: pointer;" onclick="$(this).closest('tr').next('.detail-lokasi-row').fadeToggle(200);" title="Klik untuk lihat/tutup detail alamat"><i class="fa-solid fa-location-dot me-1 text-danger"></i>${wilayahBadgeText} <i class="fa-solid fa-caret-down ms-1"></i></span>` :
                    `<span class="badge bg-light text-body-secondary border" style="font-size: 11px;"><i class="fa-solid fa-location-dot me-1 text-danger"></i>${wilayahBadgeText}</span>`;
                let tglJoin = item.tanggal_bergabung ? item.tanggal_bergabung : (item.tgl_disetujui ? item.tgl_disetujui : '-');
                
                let fullWilayahOutlet = formatWilayahText(safeKel, safeKec, safeKab, safeProv);
                let mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(safeAlamat !== '-' ? safeAlamat : '');

                html += `
                    <tr>
                        <td class="ps-3 text-center fw-bold text-muted">${idx + 1}</td>
                        <td><strong class="text-body-emphasis fs-6">${safeNama}</strong></td>
                        <td class="text-center">${locColHtml}</td>
                        <td class="text-center small text-body-secondary">${tglJoin}</td>
                    </tr>
                `;
                
                if (item.alamat_outlet) {
                    html += `
                    <tr class="detail-lokasi-row" style="display: none;">
                        <td class="border-0"></td>
                        <td colspan="3" class="py-2 pe-3 border-0">
                            <div class="p-3 bg-white border border-danger-subtle rounded-3 shadow-sm d-flex align-items-start gap-3 text-start w-100" style="word-break: break-word;">
                                <div class="bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                                    <i class="fa-solid fa-map-location-dot fs-6"></i>
                                </div>
                                <div class="flex-grow-1 text-start">
                                    <span class="d-block text-body-secondary small fw-bold mb-1">Wilayah: <strong class="text-dark">${fullWilayahOutlet || '-'}</strong></span>
                                    <span class="d-block text-body-secondary small fw-bold mb-1">Alamat Lengkap:</span>
                                    <a href="${mapsUrl}" target="_blank" rel="noopener noreferrer" class="text-primary text-decoration-underline fw-bold d-inline-block mb-1" style="font-size: 13px;" title="Klik untuk membuka lokasi di Google Maps">
                                        ${safeAlamat} <i class="fa-solid fa-arrow-up-right-from-square ms-1 text-primary" style="font-size: 10px;"></i>
                                    </a>
                                    <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                        <i class="fa-solid fa-circle-info text-danger me-1"></i>Klik teks alamat untuk petunjuk arah di Google Maps (Desktop / Aplikasi HP)
                                    </small>
                                </div>
                            </div>
                        </td>
                    </tr>
                    `;
                }
            });
        } else {
            html += '<tr><td colspan="4" class="text-center py-4 text-muted"><i class="fa-solid fa-store-slash me-1"></i> Investor ini belum memiliki outlet yang aktif.</td></tr>';
        }

        html += '</tbody></table></div>';

        Swal.fire({
            title: `<div class="fw-bold text-danger fs-5"><i class="fa-solid fa-store me-2"></i>Daftar Outlet Milik ${namaInv || 'Investor'}</div>`,
            html: html,
            width: '750px',
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#7D0A0A',
            customClass: {
                popup: 'rounded-4'
            }
        });
    });

});
</script>
