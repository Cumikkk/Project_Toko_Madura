<?php
use Config\Core\Database;
use Config\Core\SystemInfo;
use App\Models\Helper;
use App\Models\Outlet;

$queryParam = Helper::getSafeInput($_GET);
$idOutlet   = intval($queryParam['id'] ?? 0);

if ($idOutlet <= 0) {
    echo '<div class="alert alert-danger"><i class="fe fe-alert-circle me-2"></i>ID Outlet tidak valid.</div>';
    return;
}

// Fetch detail outlet & seluruh transaksi omzet (default: 0, 0 untuk semua periode agar filter client-side DataTables mulus tanpa reload)
$result    = Outlet::getOutletOmzetDetail($idOutlet, 0, 0);
$outlet    = $result['outlet'] ?? null;
$transaksi = $result['transaksi'] ?? [];
$summary   = $result['summary'] ?? [];

if (!$outlet) {
    echo '<div class="alert alert-danger"><i class="fe fe-alert-circle me-2"></i>Outlet tidak ditemukan.</div>';
    return;
}

$db = Database::connect();

// Ekstrak daftar tahun unik secara dinamis dari data transaksi
$daftarTahun = [];
if (!empty($transaksi)) {
    foreach ($transaksi as $t) {
        if (!empty($t['tanggal_omzet'])) {
            $y = date('Y', strtotime($t['tanggal_omzet']));
            if (!in_array($y, $daftarTahun)) {
                $daftarTahun[] = $y;
            }
        }
    }
    rsort($daftarTahun);
}
if (empty($daftarTahun)) {
    $daftarTahun[] = date('Y');
}

$namaBulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$tarifNom = (int)($outlet['biaya_langganan_outlet'] ?? 100000);
$totalOmzetAll = (float)($summary['total_omzet'] ?? 0);
?>

<style>
/* Rapatkan & Gabungkan Baris Total Footer agar Menyatu Rapat dengan Tabel */
#table-rincian-omzet_wrapper .dataTables_scrollBody table {
    margin-bottom: 0 !important;
    border-bottom: none !important;
}
#table-rincian-omzet_wrapper .dataTables_scrollFoot {
    background-color: #f1f5f9 !important;
    border-top: 2px solid #cbd5e1 !important;
    margin-top: 0 !important;
    padding-top: 0 !important;
}
#table-rincian-omzet_wrapper .dataTables_scrollFoot table {
    margin-top: 0 !important;
    margin-bottom: 0 !important;
    border-top: none !important;
    background-color: #f1f5f9 !important;
}
#table-rincian-omzet_wrapper .dataTables_scrollFoot tfoot tr {
    background-color: #f1f5f9 !important;
}
#table-rincian-omzet_wrapper .dataTables_scrollFoot tfoot td {
    padding: 10px 8px !important;
    background-color: #f1f5f9 !important;
    border-top: none !important;
    border-bottom: 1px solid #cbd5e1 !important;
    vertical-align: middle !important;
    font-size: 13.5px;
}
</style>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Rincian Omzet Outlet</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/outlet/omzet">Monitoring Omzet</a></li>
            <li class="breadcrumb-item active" aria-current="page">Rincian Omzet</li>
        </ol>
    </div>
</div>

<!-- 1. Tiga Kartu Summary di Luar Card Utama -->
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

    <!-- Card 3: Tarif & Total Omzet -->
    <div class="col-lg-4 col-md-12 col-12 mb-2">
        <div class="card custom-card h-100 mb-0">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 text-muted">Tarif & Performa Omzet</h6>
                    <i class="fa fa-money text-success fs-16"></i>
                </div>
                <div class="d-flex align-items-center mb-1">
                    <h5 class="fw-bold mb-0 me-2 text-dark">Rp <?= number_format($tarifNom, 0, ',', '.') ?><small class="text-muted fw-normal fs-12"> / Bln</small></h5>
                    <?php if ($totalOmzetAll >= 10000000) : ?>
                        <span class="badge bg-success" style="font-size: 10.5px;"><i class="fa fa-arrow-up me-1"></i>Rekomen Naik</span>
                    <?php endif; ?>
                </div>
                <div class="small text-muted text-truncate" style="font-size: 12.5px;">
                    Total Omzet Terdata: <strong class="text-success">Rp <?= number_format($totalOmzetAll, 0, ',', '.') ?></strong>
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
                    <h5 class="card-title mb-0">Daftar Rincian Transaksi Omzet</h5>
                    <a href="<?= SystemInfo::app('ADMIN_URL') ?>/outlet/omzet" class="btn btn-secondary btn-sm">
                        <i class="fe fe-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">

                <!-- Toolbar Filter di Dalam Card -->
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-lg-5 col-md-5">
                            <label class="form-label small fw-bold mb-1">Filter Bulan</label>
                            <select id="filterBulan" class="form-select filter-select" data-placeholder="Semua Bulan">
                                <option value="">Semua Bulan</option>
                                <?php foreach ($namaBulan as $mNum => $mName) : ?>
                                    <option value="<?= sprintf('%02d', $mNum) ?>"><?= $mName ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-5 col-md-5">
                            <label class="form-label small fw-bold mb-1">Filter Tahun</label>
                            <select id="filterTahun" class="form-select filter-select" data-placeholder="Semua Tahun">
                                <option value="">Semua Tahun</option>
                                <?php foreach ($daftarTahun as $y) : ?>
                                    <option value="<?= $y ?>"><?= $y ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-2">
                            <button type="button" id="btnResetFilter" class="btn btn-secondary btn-sm w-100 d-flex align-items-center justify-content-center" style="height: 38px;" title="Reset semua filter periode">
                                <i class="fe fe-refresh-cw me-1"></i> Reset Filter
                            </button>
                        </div>
                    </div>
                </div>

                <!-- DataTable Transaksi Omzet -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100 align-middle" id="table-rincian-omzet">
                        <thead>
                            <tr class="text-center">
                                <th class="text-center" style="width: 5%;">NO</th>
                                <th class="text-center">TANGGAL OMZET</th>
                                <th class="text-center">OMZET KOTOR</th>
                                <th class="text-center">PERSENTASE POTONGAN</th>
                                <th class="text-center">HAK INVESTOR</th>
                                <th class="text-center">HAK OUTLET</th>
                                <th class="text-center">BERSIH OUTLET TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($transaksi)) : ?>
                                <?php $no = 1; foreach ($transaksi as $t) :
                                    $tglTs = !empty($t['tanggal_omzet']) ? strtotime($t['tanggal_omzet']) : 0;
                                    $tglFormatted = $tglTs > 0 ? date("d/m/Y", $tglTs) : '-';
                                    $blnVal = $tglTs > 0 ? date('m', $tglTs) : '';
                                    $thnVal = $tglTs > 0 ? date('Y', $tglTs) : '';
                                    $pctInv = (float)($t['persentase_hak_investor'] ?? 50);
                                    $pctOutlet = 100 - $pctInv;
                                    $nomOmzet = (float)($t['nominal_omzet'] ?? 0);
                                    $nomPotongan = (float)($t['nominal_potongan'] ?? 0);
                                    $nomHakInvestor = (float)($t['nominal_hak_investor'] ?? 0);
                                    $nomHakOutlet = (float)($t['nominal_hak_outlet'] ?? 0);
                                    $nomBersih = max(0, $nomOmzet - $nomHakInvestor);
                                ?>
                                    <tr data-bulan="<?= $blnVal ?>" 
                                        data-tahun="<?= $thnVal ?>"
                                        data-omzet="<?= $nomOmzet ?>"
                                        data-potongan="<?= $nomPotongan ?>"
                                        data-investor="<?= $nomHakInvestor ?>"
                                        data-outlet="<?= $nomHakOutlet ?>"
                                        data-bersih="<?= $nomBersih ?>">
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td class="text-center"><?= $tglFormatted ?></td>
                                        <td class="text-end fw-bold text-success">
                                            Rp <?= number_format($nomOmzet, 0, ',', '.') ?>
                                        </td>
                                        <td class="text-end text-danger">
                                            Rp <?= number_format($nomPotongan, 0, ',', '.') ?>
                                            <small class="text-muted">(<?= number_format($t['persentase_potongan'] ?? 0, 0) ?>%)</small>
                                        </td>
                                        <td class="text-end fw-bold text-primary">
                                            Rp <?= number_format($nomHakInvestor, 0, ',', '.') ?>
                                            <small class="text-muted">(<?= number_format($pctInv, 0) ?>%)</small>
                                        </td>
                                        <td class="text-end fw-bold text-warning">
                                            Rp <?= number_format($nomHakOutlet, 0, ',', '.') ?>
                                            <small class="text-muted">(<?= number_format($pctOutlet, 0) ?>%)</small>
                                        </td>
                                        <td class="text-end fw-bold text-dark">
                                            Rp <?= number_format($nomBersih, 0, ',', '.') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Belum ada transaksi omzet tercatat untuk outlet ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr class="align-middle">
                                <td class="text-center fw-bold py-2"></td>
                                <td class="text-center text-uppercase fw-bold py-2">TOTAL KESELURUHAN:</td>
                                <td class="text-end text-success fw-bold py-2" id="footTotalOmzet">Rp 0</td>
                                <td class="text-end text-danger fw-bold py-2" id="footTotalPotongan">Rp 0</td>
                                <td class="text-end text-primary fw-bold py-2" id="footTotalInvestor">Rp 0</td>
                                <td class="text-end text-warning fw-bold py-2" id="footTotalOutlet">Rp 0</td>
                                <td class="text-end text-dark fw-bold py-2" id="footTotalBersih">Rp 0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
let tableRincian = null;

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
        templateResult: function(data) {
            // Sembunyikan opsi "Semua" dari daftar dropdown yang bisa dipilih
            if (!data.id || data.id === '') {
                return null;
            }
            return data.text;
        },
        language: { noResults: function() { return 'Tidak ada data ditemukan'; } }
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

$(document).ready(function() {
    // Inisialisasi Select2 pada Filter
    initFilterSelect2('#filterBulan');
    initFilterSelect2('#filterTahun');

    // Auto focus search field saat Select2 filter dibuka
    $(document).on('select2:open', function() {
        setTimeout(() => {
            let searchField = document.querySelector('.select2-container--open .select2-search__field');
            if (searchField) {
                searchField.focus();
            }
        }, 10);
    });

    $('.filter-select').on('select2:close', function() {
        let $container = $(this).next('.select2-container');
        $container.find('.select2-selection').blur();
    });

    // Navigasi Otomatis Berurutan saat Filter Dipilih
    $('#filterBulan').on('select2:select', function() {
        openNextFilterSelect2('#filterTahun');
    });

    // Inisialisasi DataTable Rincian Omzet dengan footerCallback dinamis
    if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#table-rincian-omzet')) {
        tableRincian = $('#table-rincian-omzet').DataTable({
            processing: true,
            deferRender: true,
            scrollX: true,
            lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
            pageLength: 5,
            language: {
                searchPlaceholder: 'Cari transaksi...',
                sSearch: '',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                paginate: { first: 'First', last: 'Last', next: 'Next', previous: 'Previous' }
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
            },
            footerCallback: function (row, data, start, end, display) {
                var api = this.api();

                var formatRupiah = function (num) {
                    return 'Rp ' + Number(Math.round(num) || 0).toLocaleString('id-ID');
                };

                var totalOmzet = 0;
                var totalPotongan = 0;
                var totalInvestor = 0;
                var totalOutlet = 0;
                var totalBersih = 0;

                // Hitung total hanya untuk baris yang lolos filter / pencarian
                display.forEach(function (index) {
                    var $row = $(api.row(index).node());
                    totalOmzet += parseFloat($row.attr('data-omzet')) || 0;
                    totalPotongan += parseFloat($row.attr('data-potongan')) || 0;
                    totalInvestor += parseFloat($row.attr('data-investor')) || 0;
                    totalOutlet += parseFloat($row.attr('data-outlet')) || 0;
                    totalBersih += parseFloat($row.attr('data-bersih')) || 0;
                });

                $('#footTotalOmzet').html(formatRupiah(totalOmzet));
                $('#footTotalPotongan').html(formatRupiah(totalPotongan));
                $('#footTotalInvestor').html(formatRupiah(totalInvestor));
                $('#footTotalOutlet').html(formatRupiah(totalOutlet));
                $('#footTotalBersih').html(formatRupiah(totalBersih));
            }
        });

        if ($.fn.select2) {
            setTimeout(function() {
                $('#table-rincian-omzet_wrapper .dataTables_length select').select2({
                    minimumResultsForSearch: Infinity,
                    width: 'auto'
                });
            }, 50);
        }
    }

    // Filter Custom DataTable untuk Rincian Omzet
    $.fn.dataTable.ext.search.push(function(settings, searchData, index) {
        if (settings.nTable.id !== 'table-rincian-omzet') {
            return true;
        }
        if (!tableRincian) {
            return true;
        }

        let $row = $(tableRincian.row(index).node());
        let rowBulan = ($row.attr('data-bulan') || '').trim();
        let rowTahun = ($row.attr('data-tahun') || '').trim();

        let filterBulan = ($('#filterBulan').val() || '').trim();
        let filterTahun = ($('#filterTahun').val() || '').trim();

        if (filterBulan && rowBulan !== filterBulan) {
            return false;
        }
        if (filterTahun && rowTahun !== filterTahun) {
            return false;
        }

        return true;
    });

    // Event Trigger Filter
    $('#filterBulan, #filterTahun').on('change', function() {
        if (tableRincian) {
            tableRincian.draw();
        }
    });

    // Reset Filter Button
    $('#btnResetFilter').on('click', function() {
        $('#filterBulan').val('').trigger('change.select2');
        $('#filterTahun').val('').trigger('change.select2');
        initFilterSelect2('#filterBulan');
        initFilterSelect2('#filterTahun');
        if (tableRincian) {
            tableRincian.draw();
        }
    });
});
</script>

