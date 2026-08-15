<?php
use Config\Core\Database;
use Config\Core\SystemInfo;
use App\Models\Helper;

$queryParam = Helper::getSafeInput($_GET);
$idOutlet = intval($queryParam['id'] ?? 0);

if ($idOutlet <= 0) {
    echo '<div class="alert alert-danger"><i class="fe fe-alert-circle me-2"></i>ID Outlet tidak valid.</div>';
    return;
}

$db = Database::connect();

// Fetch outlet info
$resOutlet = $db->query("
    SELECT o.id_outlet, o.nama_outlet, o.tgl_jatuh_tempo, o.status,
           u_kasir.nama_lengkap as pengelola_toko, u_kasir.no_hp as no_hp_toko,
           inv.id_investor, inv.biaya_langganan_outlet,
           u_inv.nama_lengkap as nama_investor, u_inv.username as username_investor, u_inv.no_hp as no_hp_investor
    FROM outlet o
    LEFT JOIN users u_kasir ON u_kasir.id_users = o.id_users
    LEFT JOIN investor inv ON inv.id_investor = o.id_investor
    LEFT JOIN users u_inv ON u_inv.id_users = inv.id_users
    WHERE o.id_outlet = {$idOutlet}
    LIMIT 1
");
$outlet = ($resOutlet && $resOutlet->num_rows > 0) ? $resOutlet->fetch_assoc() : null;

if (!$outlet) {
    echo '<div class="alert alert-danger"><i class="fe fe-alert-circle me-2"></i>Outlet tidak ditemukan.</div>';
    return;
}

// Fetch riwayat langganan
$riwayat = [];
$totalDanaMasuk = 0;
$totalTrxActive = 0;

$resRiwayat = $db->query("SELECT * FROM riwayat_langganan WHERE id_outlet = {$idOutlet} ORDER BY id_riwayat DESC");
if ($resRiwayat) {
    while ($r = $resRiwayat->fetch_assoc()) {
        $riwayat[] = $r;
        if (($r['status'] ?? '') === 'active') {
            $totalDanaMasuk += (float)($r['nominal_transfer'] ?? 0);
            $totalTrxActive++;
        }
    }
}

// Format status jatuh tempo
$jatuhTempoBadge = '-';
if (!empty($outlet['tgl_jatuh_tempo'])) {
    $jatuhTempoStr = date('d/m/Y', strtotime($outlet['tgl_jatuh_tempo']));
    $isExpired = strtotime($outlet['tgl_jatuh_tempo']) < time();
    $jatuhTempoBadge = $isExpired
        ? '<span class="text-danger fw-bold me-2" style="font-size: 16px;">' . $jatuhTempoStr . '</span> <span class="badge bg-danger">Expired</span>'
        : '<span class="text-success fw-bold me-2" style="font-size: 16px;">' . $jatuhTempoStr . '</span> <span class="badge bg-success">Aktif</span>';
} else {
    $jatuhTempoBadge = '<span class="badge bg-secondary">Belum Diatur</span>';
}

// Ekstrak daftar tahun unik secara dinamis dari data riwayat yang ada di tabel
$daftarTahun = [];
if (!empty($riwayat)) {
    foreach ($riwayat as $r) {
        if (!empty($r['tgl_request'])) {
            $y = date('Y', strtotime($r['tgl_request']));
            if (!in_array($y, $daftarTahun)) {
                $daftarTahun[] = $y;
            }
        }
        if (!empty($r['tgl_disetujui'])) {
            $y2 = date('Y', strtotime($r['tgl_disetujui']));
            if (!in_array($y2, $daftarTahun)) {
                $daftarTahun[] = $y2;
            }
        }
    }
    rsort($daftarTahun);
}
if (empty($daftarTahun)) {
    $daftarTahun[] = date('Y');
}

// Nama bulan untuk filter
$namaBulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Riwayat Pembayaran Langganan</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/outlet/view">Data Outlet</a></li>
            <li class="breadcrumb-item active" aria-current="page">Riwayat Pembayaran</li>
        </ol>
    </div>
</div>

<!-- 1. Tiga Kartu Summary di Luar Card Utama (Judul Kartu Standar Dashboard: h6 text-muted) -->
<div class="row row-sm mb-3">
    <!-- Card 1: Informasi Outlet -->
    <div class="col-lg-4 col-md-6 col-12 mb-2">
        <div class="card custom-card h-100 mb-0">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 text-muted">Informasi Outlet</h6>
                    <i class="fa fa-building text-primary fs-16"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1 text-truncate"><?= htmlspecialchars($outlet['nama_outlet']) ?></h5>
                <div class="small text-muted text-truncate" style="font-size: 12.5px;">
                    <strong class="text-dark"><?= htmlspecialchars($outlet['pengelola_toko'] ?? '-') ?></strong>
                    <?php if (!empty($outlet['no_hp_toko'])) : ?>
                        <span class="mx-1">&bull;</span>
                        <span><i class="fab fa-whatsapp text-success me-1"></i><?= htmlspecialchars($outlet['no_hp_toko']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Investor Yang Menaungi -->
    <div class="col-lg-4 col-md-6 col-12 mb-2">
        <div class="card custom-card h-100 mb-0">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 text-muted">Investor Yang Menaungi</h6>
                    <i class="fa fa-handshake-o text-warning fs-16"></i>
                </div>
                <?php if (!empty($outlet['nama_investor'])) : ?>
                    <h5 class="fw-bold mb-1 text-truncate" style="color: #6f42c1;"><?= htmlspecialchars($outlet['nama_investor']) ?></h5>
                    <div class="small text-muted text-truncate" style="font-size: 12.5px;">
                        <?php if (!empty($outlet['username_investor'])) : ?>
                            <code style="color: #d63384; font-size: 12px;">@<?= htmlspecialchars($outlet['username_investor']) ?></code>
                        <?php else : ?>
                            <span>-</span>
                        <?php endif; ?>
                        <?php if (!empty($outlet['no_hp_investor'])) : ?>
                            <span class="mx-1">&bull;</span>
                            <span><i class="fab fa-whatsapp text-success me-1"></i><?= htmlspecialchars($outlet['no_hp_investor']) ?></span>
                        <?php endif; ?>
                    </div>
                <?php else : ?>
                    <h5 class="fw-bold text-muted mb-1">Belum Ada Investor</h5>
                    <div class="small text-muted" style="font-size: 12.5px;">Outlet mandiri tanpa naungan investor</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Card 3: Status Langganan -->
    <div class="col-lg-4 col-md-12 col-12 mb-2">
        <div class="card custom-card h-100 mb-0">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 text-muted">Status Langganan</h6>
                    <i class="fa fa-calendar-check-o text-success fs-16"></i>
                </div>
                <div class="d-flex align-items-center mb-1" style="min-height: 24px;">
                    <?php if (!empty($outlet['tgl_jatuh_tempo'])) : ?>
                        <h5 class="fw-bold mb-0 me-2 <?= $isExpired ? 'text-danger' : 'text-success' ?>"><?= $jatuhTempoStr ?></h5>
                        <span class="badge <?= $isExpired ? 'bg-danger' : 'bg-success' ?>"><?= $isExpired ? 'Expired' : 'Aktif' ?></span>
                    <?php else : ?>
                        <h5 class="fw-bold text-muted mb-0 me-2">-</h5>
                        <span class="badge bg-secondary">Belum Diatur</span>
                    <?php endif; ?>
                </div>
                <div class="small text-muted text-truncate" style="font-size: 12.5px;">
                    Tarif Langganan: <strong class="text-success">Rp <?= number_format($outlet['biaya_langganan_outlet'] ?? 100000, 0, ',', '.') ?> / Bln</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 2. Main Table Card dengan Toolbar Filter di Dalamnya -->
<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="card-title mb-0">Daftar Riwayat Pembayaran</h5>
                    <a href="<?= SystemInfo::app('ADMIN_URL') ?>/outlet/view" class="btn btn-secondary btn-sm">
                        <i class="fe fe-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">

                <!-- Toolbar Filter di Dalam Card (Format Standar Master Data) -->
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label small fw-bold mb-1">Filter Tipe Request</label>
                            <select id="filterTipe" class="form-select filter-select" data-placeholder="Semua Tipe">
                                <option value="">Semua Tipe</option>
                                <option value="PENDAFTARAN BARU">Pendaftaran Baru</option>
                                <option value="PERPANJANGAN">Perpanjangan</option>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label small fw-bold mb-1">Filter Status</label>
                            <select id="filterStatus" class="form-select filter-select" data-placeholder="Semua Status">
                                <option value="">Semua Status</option>
                                <option value="DISETUJUI">Disetujui</option>
                                <option value="PENDING">Pending</option>
                                <option value="DITOLAK">Ditolak</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-4">
                            <label class="form-label small fw-bold mb-1">Filter Bulan</label>
                            <select id="filterBulan" class="form-select filter-select" data-placeholder="Semua Bulan">
                                <option value="">Semua Bulan</option>
                                <?php foreach ($namaBulan as $mNum => $mName) : ?>
                                    <option value="<?= sprintf('%02d', $mNum) ?>"><?= $mName ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-4">
                            <label class="form-label small fw-bold mb-1">Filter Tahun</label>
                            <select id="filterTahun" class="form-select filter-select" data-placeholder="Semua Tahun">
                                <option value="">Semua Tahun</option>
                                <?php foreach ($daftarTahun as $y) : ?>
                                    <option value="<?= $y ?>"><?= $y ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-4">
                            <button type="button" id="btnResetFilter" class="btn btn-secondary btn-sm w-100 d-flex align-items-center justify-content-center" style="height: 38px;" title="Reset semua filter">
                                <i class="fe fe-refresh-cw me-1"></i> Reset Filter
                            </button>
                        </div>
                    </div>
                </div>

                <!-- DataTable Riwayat Pembayaran (Font & Format Standar Admin) -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100 align-middle" id="table-histori-langganan">
                        <thead>
                            <tr class="text-center">
                                <th class="text-center" style="width: 5%;">NO</th>
                                <th class="text-center">TANGGAL REQUEST</th>
                                <th class="text-center">TIPE REQUEST</th>
                                <th class="text-center">NOMINAL</th>
                                <th class="text-center">TANGGAL DISETUJUI</th>
                                <th class="text-center">STATUS</th>
                                <th class="text-center" style="width: 12%;">BUKTI BAYAR</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($riwayat)) : ?>
                                <?php $no = 1; foreach ($riwayat as $r) : ?>
                                    <?php
                                    // Tanggal request
                                    $tglRequest = !empty($r['tgl_request']) ? date('d/m/Y H:i', strtotime($r['tgl_request'])) : '-';
                                    
                                    // Tipe request
                                    $tipeHtml = ($r['tipe_request'] ?? 'baru') === 'perpanjangan'
                                        ? '<span class="badge bg-warning text-dark"><i class="fa fa-refresh me-1"></i>Perpanjangan</span>'
                                        : '<span class="badge bg-info text-white"><i class="fa fa-plus-circle me-1"></i>Pendaftaran Baru</span>';
                                    
                                    // Nominal
                                    $nominalHtml = '<strong class="text-success">Rp ' . number_format($r['nominal_transfer'] ?? 0, 0, ',', '.') . '</strong>';
                                    
                                    // Tanggal disetujui
                                    $tglDisetujui = !empty($r['tgl_disetujui']) ? date('d/m/Y H:i', strtotime($r['tgl_disetujui'])) : '-';
                                    
                                    // Status
                                    $statusHtml = '-';
                                    if ($r['status'] === 'pending') {
                                        $statusHtml = '<span class="badge bg-warning text-dark">Pending</span>';
                                    } elseif ($r['status'] === 'active') {
                                        $statusHtml = '<span class="badge bg-success">Disetujui</span>';
                                    } elseif ($r['status'] === 'reject') {
                                        $alasan = htmlspecialchars($r['alasan_penolakan'] ?? '', ENT_QUOTES);
                                        $statusHtml = '<span class="badge bg-danger" title="' . $alasan . '">Ditolak</span>';
                                    }
                                    ?>
                                    <tr class="text-center">
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td class="text-center"><?= $tglRequest ?></td>
                                        <td class="text-center"><?= $tipeHtml ?></td>
                                        <td class="text-center"><?= $nominalHtml ?></td>
                                        <td class="text-center"><?= $tglDisetujui ?></td>
                                        <td class="text-center"><?= $statusHtml ?></td>
                                        <td class="text-center">
                                            <?php if (!empty($r['bukti_pembayaran'])) : ?>
                                                <button type="button" class="btn btn-outline-info btn-sm py-1 px-2.5"
                                                        onclick="previewBukti('<?= htmlspecialchars($r['bukti_pembayaran'], ENT_QUOTES) ?>',
                                                                              '<?= htmlspecialchars($outlet['nama_outlet'], ENT_QUOTES) ?>',
                                                                              '<?= htmlspecialchars($outlet['nama_investor'] ?? '-', ENT_QUOTES) ?>',
                                                                              '<?= number_format($r['nominal_transfer'] ?? 0, 0, ',', '.') ?>')">
                                                    <i class="fa fa-image me-1"></i>Lihat Bukti
                                                </button>
                                            <?php else : ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Belum ada riwayat pembayaran untuk outlet ini.</td>
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
function previewBukti(filePath, namaOutlet, namaInvestor, biayaLangganan) {
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

    Swal.fire({
        title: '<i class="fa fa-file-text-o me-2 text-info"></i>Bukti Pembayaran Pendaftaran Outlet',
        html: infoHtml
            + '<img src="' + proxyUrl + '" '
            + 'style="max-width:100%;max-height:60vh;border-radius:8px;border:1px solid #dee2e6;object-fit:contain;" '
            + 'onerror="this.outerHTML=\'<p class=\\\'text-danger mt-2\\\'><i class=\\\'fa fa-exclamation-triangle me-1\\\'></i> Gambar gagal dimuat</p>\'">',
        showCloseButton: true,
        showConfirmButton: false,
        scrollbarPadding: false,
        heightAuto: false,
        width: 640
    });
}

$(document).ready(function() {
    var table = null;

    // Inisialisasi Select2 Helper (Sama seperti Halaman Master)
    function initFilterSelect2(selector) {
        let $el = $(selector);
        let placeholder = $el.attr('data-placeholder') || 'Pilih...';
        if ($el.data('select2')) {
            $el.select2('destroy');
        }
        $el.select2({
            width: '100%',
            placeholder: placeholder,
            allowClear: false,
            language: { noResults: function() { return 'Tidak ada hasil'; } }
        });
    }

    function openNextFilterSelect2(selector) {
        setTimeout(() => {
            let $el = $(selector);
            $el.select2('open');
            let searchField = document.querySelector('.select2-container--open .select2-search__field');
            if (searchField) {
                searchField.focus();
            }
        }, 120);
    }

    $('.filter-select').on('select2:close', function() {
        let $container = $(this).next('.select2-container');
        $container.find('.select2-selection').blur();
    });

    $(document).on('select2:open', function() {
        setTimeout(() => {
            let searchField = document.querySelector('.select2-container--open .select2-search__field');
            if (searchField) {
                searchField.focus();
            }
        }, 10);
    });

    // Inisialisasi filter Select2
    initFilterSelect2('#filterTipe');
    initFilterSelect2('#filterStatus');
    initFilterSelect2('#filterBulan');
    initFilterSelect2('#filterTahun');

    // Custom DataTables Filter Function
    $.fn.dataTable.ext.search.push(
        function(settings, data, dataIndex) {
            if (settings.nTable.id !== 'table-histori-langganan') {
                return true;
            }

            var filterTipe   = ($('#filterTipe').val() || '').toUpperCase();
            var filterStatus = ($('#filterStatus').val() || '').toUpperCase();
            var filterBulan  = $('#filterBulan').val() || '';
            var filterTahun  = $('#filterTahun').val() || '';

            var colTanggal = data[1] || '';
            var colTipe    = (data[2] || '').toUpperCase();
            var colStatus  = (data[5] || '').toUpperCase();

            // Filter Tipe Request
            if (filterTipe && colTipe.indexOf(filterTipe) === -1) {
                return false;
            }

            // Filter Status
            if (filterStatus && colStatus.indexOf(filterStatus) === -1) {
                return false;
            }

            // Filter Bulan & Tahun
            if (filterBulan || filterTahun) {
                var parts = colTanggal.split(' ')[0].split('/');
                if (parts.length === 3) {
                    var rowMonth = parts[1];
                    var rowYear  = parts[2];
                    if (filterBulan && rowMonth !== filterBulan) {
                        return false;
                    }
                    if (filterTahun && rowYear !== filterTahun) {
                        return false;
                    }
                } else {
                    return false;
                }
            }

            return true;
        }
    );

    if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#table-histori-langganan')) {
        table = $('#table-histori-langganan').DataTable({
            processing: true,
            deferRender: true,
            scrollX: true,
            lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
            pageLength: 5,
            language: {
                searchPlaceholder: 'Cari riwayat...',
                sSearch: '',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                paginate: { first: 'First', last: 'Last', next: 'Next', previous: 'Previous' }
            },
            order: [[1, 'desc']]
        });

        if ($.fn.select2) {
            setTimeout(function() {
                $('#table-histori-langganan_wrapper .dataTables_length select').select2({
                    minimumResultsForSearch: Infinity,
                    width: 'auto'
                });
            }, 50);
        }
    }

    // Event filter: alur otomatis membuka dropdown berikutnya persis seperti halaman Master
    $('#filterTipe').on('change select2:select', function(e) {
        if (e.type === 'select2:select' && $(this).val()) {
            openNextFilterSelect2('#filterStatus');
        }
        if (table) table.draw();
    });

    $('#filterStatus').on('change select2:select', function(e) {
        if (e.type === 'select2:select' && $(this).val()) {
            openNextFilterSelect2('#filterBulan');
        }
        if (table) table.draw();
    });

    $('#filterBulan').on('change select2:select', function(e) {
        if (e.type === 'select2:select' && $(this).val()) {
            openNextFilterSelect2('#filterTahun');
        }
        if (table) table.draw();
    });

    $('#filterTahun').on('change select2:select', function(e) {
        if (table) table.draw();
    });

    // Reset Filter Event
    $('#btnResetFilter').on('click', function() {
        $('#filterTipe').val('').trigger('change.select2');
        $('#filterStatus').val('').trigger('change.select2');
        $('#filterBulan').val('').trigger('change.select2');
        $('#filterTahun').val('').trigger('change.select2');
        
        initFilterSelect2('#filterTipe');
        initFilterSelect2('#filterStatus');
        initFilterSelect2('#filterBulan');
        initFilterSelect2('#filterTahun');

        if (table) {
            table.draw();
        }
    });
});
</script>
