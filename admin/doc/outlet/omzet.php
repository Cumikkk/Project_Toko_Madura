<?php
use Config\Core\Database;
use Config\Core\SystemInfo;
use App\Models\Outlet;

$db = Database::connect();

// Parameter filter bulan & tahun (default: 0 = Semua Bulan & Semua Tahun)
$selectedBulan = 0;
$selectedTahun = 0;

// Query data omzet & keuangan outlet (default: semua periode)
$omzetData = Outlet::getOutletOmzetMonitoring(0, 0);
$outlets   = $omzetData['outlets'] ?? [];

// Ambil daftar tahun dinamis dari data laporan omzet
$resTahunOmzet = $db->query("SELECT DISTINCT YEAR(tanggal_omzet) as tahun FROM laporan_omzet WHERE tanggal_omzet IS NOT NULL AND tanggal_omzet != '0000-00-00' ORDER BY tahun DESC");
$listTahunOmzet = [];
if ($resTahunOmzet && $resTahunOmzet->num_rows > 0) {
    while ($rowT = $resTahunOmzet->fetch_assoc()) {
        if (!empty($rowT['tahun'])) {
            $listTahunOmzet[] = intval($rowT['tahun']);
        }
    }
}
if (empty($listTahunOmzet)) {
    $listTahunOmzet[] = intval(date('Y'));
}

// Ambil opsi filter investor
$investorOptions = $db->query("
    SELECT inv.id_investor, u.nama_lengkap, u.username 
    FROM investor inv 
    JOIN users u ON u.id_users = inv.id_users 
    ORDER BY u.nama_lengkap ASC
");

// Daftar nama bulan dalam bahasa Indonesia
$namaBulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

if ($selectedBulan > 0 && $selectedTahun > 0) {
    $periodeLabel = ($namaBulan[$selectedBulan] ?? '-') . ' ' . $selectedTahun;
} elseif ($selectedBulan > 0) {
    $periodeLabel = ($namaBulan[$selectedBulan] ?? '-') . ' (Semua Tahun)';
} elseif ($selectedTahun > 0) {
    $periodeLabel = 'Semua Bulan ' . $selectedTahun;
} else {
    $periodeLabel = 'Semua Periode';
}

// Kalkulasi metrik ringkasan omzet & transaksi
$totalOmzetPeriode       = 0;
$totalTrxPeriode         = 0;
$totalPotonganPeriode    = 0;
$totalHakInvestorPeriode = 0;
$outletAktifTrx          = 0;
$outletRekomenNaik       = 0;
$totalOutletsCount       = !empty($outlets) ? count($outlets) : 0;

if (!empty($outlets)) {
    foreach ($outlets as $o) {
        $omz = (float)($o['omzet_periode'] ?? 0);
        $trx = (int)($o['transaksi_periode'] ?? 0);
        $pot = (float)($o['potongan_periode'] ?? 0);
        $hak = (float)($o['hak_investor_periode'] ?? 0);

        $totalOmzetPeriode       += $omz;
        $totalTrxPeriode         += $trx;
        $totalPotonganPeriode    += $pot;
        $totalHakInvestorPeriode += $hak;

        if ($trx > 0 || $omz > 0) {
            $outletAktifTrx++;
        }
        if ($omz >= 10000000) {
            $outletRekomenNaik++;
        }
    }
}
$avgOmzetPerOutlet = ($totalOutletsCount > 0) ? ($totalOmzetPeriode / $totalOutletsCount) : 0;

function safeJsonAlamatOmzet($str) {
    return json_encode(trim(preg_replace('/\s+/', ' ', $str ?? '')));
}
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Monitoring Omzet & Biaya Langganan</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/outlet/view">Outlet</a></li>
            <li class="breadcrumb-item active" aria-current="page">Omzet & Biaya Langganan</li>
        </ol>
    </div>
</div>

<!-- Main Table Card -->
<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-header">
                <div class="d-flex justify-content-between mb-2">
                    <h5 class="card-title">Daftar Omzet & Evaluasi Biaya Langganan Toko</h5>
                </div>
            </div>
            <div class="card-body">

                <!-- Toolbar Filter -->
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-lg-2 col-md-4">
                            <label class="form-label small fw-bold mb-1">Filter Bulan</label>
                            <select id="filterBulan" class="form-select filter-select" data-placeholder="Semua Bulan">
                                <option value="" selected>Semua Bulan</option>
                                <?php foreach ($namaBulan as $mNum => $mName) : ?>
                                    <option value="<?= $mNum ?>"><?= $mName ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-4">
                            <label class="form-label small fw-bold mb-1">Filter Tahun</label>
                            <select id="filterTahun" class="form-select filter-select" data-placeholder="Semua Tahun">
                                <option value="" selected>Semua Tahun</option>
                                <?php foreach ($listTahunOmzet as $y) : ?>
                                    <option value="<?= $y ?>"><?= $y ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-4">
                            <label class="form-label small fw-bold mb-1">Filter Investor</label>
                            <select id="filterInvestor" class="form-select filter-select" data-placeholder="Semua Investor">
                                <option value="">Semua Investor</option>
                                <?php if ($investorOptions && $investorOptions->num_rows > 0) : ?>
                                    <?php while ($inv = $investorOptions->fetch_assoc()) : ?>
                                        <option value="<?= htmlspecialchars(strtoupper($inv['nama_lengkap'])) ?>">
                                            <?= htmlspecialchars($inv['nama_lengkap']) ?> (@<?= htmlspecialchars($inv['username']) ?>)
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-4">
                            <label class="form-label small fw-bold mb-1">Filter Provinsi</label>
                            <select id="filterProvinsi" class="form-select filter-select" data-placeholder="Semua Provinsi">
                                <option value="">Semua Provinsi</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-4">
                            <label class="form-label small fw-bold mb-1">Filter Kabupaten / Kota</label>
                            <select id="filterKabupaten" class="form-select filter-select" data-placeholder="Semua Kabupaten" disabled>
                                <option value="">Semua Kabupaten</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-4">
                            <button type="button" id="btnResetFilter" class="btn btn-secondary btn-sm w-100 d-flex align-items-center justify-content-center" style="height: 38px;" title="Reset semua filter wilayah & investor">
                                <i class="fe fe-refresh-cw me-1"></i> Reset Filter
                            </button>
                        </div>
                    </div>
                </div>

                <!-- DataTable Omzet -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100 align-middle" id="table-omzet-monitoring">
                        <thead>
                            <tr class="text-center">
                                <th class="text-center" style="width: 4%;">NO</th>
                                <th class="text-center">NAMA OUTLET</th>
                                <th class="text-center">INVESTOR</th>
                                <th class="text-center">WILAYAH</th>
                                <th class="text-center">TRANSAKSI</th>
                                <th class="text-center">OMZET KOTOR</th>
                                <th class="text-center">POTONGAN SISTEM</th>
                                <th class="text-center">HAK INVESTOR</th>
                                <th class="text-center">TARIF LANGGANAN</th>
                                <th class="text-center" style="width: 8%;">#</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($outlets)) : ?>
                                <?php $no = 1; foreach ($outlets as $row) :
                                    $omzetNom = (float)($row['omzet_periode'] ?? 0);
                                    $trxNom   = (int)($row['transaksi_periode'] ?? 0);
                                    $potNom   = (float)($row['potongan_periode'] ?? 0);
                                    $invNom   = (float)($row['hak_investor_periode'] ?? 0);
                                    $tarifNom = (int)($row['biaya_langganan_outlet'] ?? 100000);
                                ?>
                                    <tr id="outlet-row-<?= $row['id_outlet'] ?>"
                                        data-investor="<?= htmlspecialchars(strtoupper($row['nama_investor'] ?? '')) ?>"
                                        data-provinsi="<?= htmlspecialchars(strtoupper($row['provinsi'] ?? '')) ?>"
                                        data-kabupaten="<?= htmlspecialchars(strtoupper($row['kabupaten'] ?? '')) ?>">
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td class="text-start">
                                            <strong class="text-primary"><?= htmlspecialchars($row['nama_outlet']) ?></strong>
                                            <?php if (!empty($row['pengelola_toko']) || !empty($row['no_hp_toko'])) : ?>
                                                <br><small class="text-muted">
                                                    <?php if (!empty($row['pengelola_toko'])) : ?>
                                                        <i class="fa fa-user me-1"></i><?= htmlspecialchars($row['pengelola_toko']) ?>
                                                    <?php endif; ?>
                                                    <?php if (!empty($row['pengelola_toko']) && !empty($row['no_hp_toko'])) : ?>
                                                        <span class="mx-1">&bull;</span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($row['no_hp_toko'])) : ?>
                                                        <i class="fab fa-whatsapp text-success me-1"></i><?= htmlspecialchars($row['no_hp_toko']) ?>
                                                    <?php endif; ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-start">
                                            <?php if (!empty($row['nama_investor'])) : ?>
                                                <strong class="text-primary"><?= htmlspecialchars($row['nama_investor']) ?></strong>
                                                <?php if (!empty($row['username_investor']) || !empty($row['no_hp_investor'])) : ?>
                                                    <br><small class="text-muted">
                                                        <?php if (!empty($row['username_investor'])) : ?>
                                                            <code>@<?= htmlspecialchars($row['username_investor']) ?></code>
                                                        <?php endif; ?>
                                                        <?php if (!empty($row['username_investor']) && !empty($row['no_hp_investor'])) : ?>
                                                            <span class="mx-1">&bull;</span>
                                                        <?php endif; ?>
                                                        <?php if (!empty($row['no_hp_investor'])) : ?>
                                                            <i class="fab fa-whatsapp text-success me-1"></i><?= htmlspecialchars($row['no_hp_investor']) ?>
                                                        <?php endif; ?>
                                                    </small>
                                                <?php endif; ?>
                                            <?php else : ?>
                                                <span class="text-muted">Belum Ada Investor</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (!empty($row['kecamatan']) && $row['kecamatan'] !== '-') : ?>
                                                <?php if (!empty($row['alamat_outlet'])) : ?>
                                                    <span class="badge bg-light text-dark border btn-lihat-alamat shadow-xs mt-1 py-1 px-2"
                                                          style="cursor: pointer; font-size: 13px; font-weight: 500;"
                                                          onclick='showAlamatOmzet(<?= safeJsonAlamatOmzet($row['nama_outlet']) ?>, <?= safeJsonAlamatOmzet($row['alamat_outlet']) ?>, <?= safeJsonAlamatOmzet($row['provinsi'] ?? "") ?>, <?= safeJsonAlamatOmzet($row['kabupaten'] ?? "") ?>, <?= safeJsonAlamatOmzet($row['kecamatan'] ?? "") ?>, <?= safeJsonAlamatOmzet($row['kelurahan'] ?? "") ?>)'
                                                          title="Klik untuk lihat detail alamat">
                                                        <i class="fa fa-map-marker text-danger me-1"></i><?= htmlspecialchars(ucwords(strtolower($row['kelurahan'] ?? ''))) ?>, Kec. <?= htmlspecialchars(ucwords(strtolower($row['kecamatan'] ?? ''))) ?>
                                                    </span>
                                                <?php else : ?>
                                                    <span class="text-muted" style="font-size: 13px;"><i class="fa fa-map-marker me-1"></i><?= htmlspecialchars(ucwords(strtolower($row['kelurahan'] ?? ''))) ?>, Kec. <?= htmlspecialchars(ucwords(strtolower($row['kecamatan'] ?? ''))) ?></span>
                                                <?php endif; ?>
                                            <?php else : ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center col-trx">
                                            <?php if ($trxNom > 0) : ?>
                                                <span class="badge bg-primary"><?= number_format($trxNom) ?> Trx</span>
                                            <?php else : ?>
                                                <span class="text-muted">0 Trx</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end fw-bold col-omzet <?= $omzetNom > 0 ? 'text-success' : 'text-muted' ?>">
                                            Rp <?= number_format($omzetNom, 0, ',', '.') ?>
                                        </td>
                                        <td class="text-end text-danger col-pot">
                                            Rp <?= number_format($potNom, 0, ',', '.') ?>
                                        </td>
                                        <td class="text-end text-primary fw-bold col-inv">
                                            Rp <?= number_format($invNom, 0, ',', '.') ?>
                                        </td>
                                        <td class="text-center col-tarif">
                                            <strong>Rp <?= number_format($tarifNom, 0, ',', '.') ?></strong><small class="text-muted"> / Bln</small>
                                            <?php if ($omzetNom >= 10000000) : ?>
                                                <br><span class="badge bg-success mt-1" style="font-size: 10.5px;"><i class="fa fa-arrow-up me-1"></i>Rekomen Naik</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= SystemInfo::app('ADMIN_URL') ?>/outlet/rincian_omzet?id=<?= $row['id_outlet'] ?>&bulan=0&tahun=0" class="btn btn-outline-info btn-sm py-1 px-2.5 shadow-xs btn-rincian" title="Rincian Omzet Outlet">
                                                <i class="fa fa-bar-chart me-1"></i>Rincian
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr><td colspan="10" class="text-center text-muted py-4">Belum ada data outlet pada periode ini.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
// Show Alamat Popup
function showAlamatOmzet(nama, alamat, provinsi, kabupaten, kecamatan, kelurahan) {
    var queryStr = encodeURIComponent(alamat);
    var mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' + queryStr;
    Swal.fire({
        title: 'Alamat Lengkap Outlet',
        html: '<div class="text-start mb-3" style="display: grid; grid-template-columns: max-content auto 1fr; column-gap: 8px; row-gap: 8px; font-size: 15px; line-height: 1.6;">' +
                '<div class="fw-bold text-dark">Outlet</div>' +
                '<div class="fw-bold text-dark">:</div>' +
                '<div class="text-dark">' + nama + '</div>' +
                '<div class="fw-bold text-dark">Wilayah</div>' +
                '<div class="fw-bold text-dark">:</div>' +
                '<div class="text-capitalize text-dark">' + kelurahan.toLowerCase() + ', Kec. ' + kecamatan.toLowerCase() + ', Kab. ' + kabupaten.toLowerCase() + ', Prov. ' + provinsi.toLowerCase() + '</div>' +
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
}

let tableOmzet = null;

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
    const adminUrl = "<?= SystemInfo::app('ADMIN_URL') ?>";

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

    // Inisialisasi semua filter select2 ke nilai default
    $('#filterBulan').val('');
    $('#filterTahun').val('');
    $('#filterInvestor').val('');
    $('#filterProvinsi').val('');
    $('#filterKabupaten').val('');

    initFilterSelect2('#filterBulan');
    initFilterSelect2('#filterTahun');
    initFilterSelect2('#filterInvestor');
    initFilterSelect2('#filterProvinsi');
    initFilterSelect2('#filterKabupaten');

    // Navigasi Otomatis Berurutan saat Filter Dipilih (Enter/Select)
    $('#filterBulan').on('select2:select', function() {
        openNextFilterSelect2('#filterTahun');
    });

    $('#filterTahun').on('select2:select', function() {
        openNextFilterSelect2('#filterInvestor');
    });

    $('#filterInvestor').on('select2:select', function() {
        openNextFilterSelect2('#filterProvinsi');
    });

    // Bersihkan URL query parameter saat refresh
    if (window.history.replaceState && window.location.search) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    // ============================================================
    // DataTables
    // ============================================================
    var tableOmzet = null;
    if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#table-omzet-monitoring')) {
        tableOmzet = $('#table-omzet-monitoring').DataTable({
            processing: true,
            deferRender: true,
            scrollX: true,
            lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
            pageLength: 5,
            language: {
                searchPlaceholder: 'Cari outlet, investor, wilayah...',
                sSearch: '',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                paginate: { first: 'First', last: 'Last', next: 'Next', previous: 'Previous' }
            },
            order: [[5, 'desc']]
        });

        if ($.fn.select2) {
            setTimeout(function() {
                $('#table-omzet-monitoring_wrapper .dataTables_length select').select2({
                    minimumResultsForSearch: Infinity,
                    width: 'auto'
                });
            }, 50);
        }
    }

    // ============================================================
    // Load Filter Provinsi dari API wilayah (sama seperti halaman master)
    // ============================================================
    $.post(adminUrl + "/ajax/post/wilayah/get_provinsi", function(res) {
        let options = '<option value="">Semua Provinsi</option>';
        if (res.results) {
            res.results.forEach(item => {
                options += `<option value="${item.id}">${item.text}</option>`;
            });
        }
        $('#filterProvinsi').html(options).prop('disabled', false);
        initFilterSelect2('#filterProvinsi');
    });

    // ============================================================
    // Custom DataTables filtering function
    // ============================================================
    $.fn.dataTable.ext.search.push(function(settings, searchData, index) {
        if (!settings.nTable || settings.nTable.id !== 'table-omzet-monitoring') return true;
        if (!tableOmzet) return true;

        let $row = $(tableOmzet.row(index).node());
        let rowInv  = ($row.attr('data-investor') || '').toUpperCase().trim();
        let rowProv = ($row.attr('data-provinsi') || '').toUpperCase().trim();
        let rowKab  = ($row.attr('data-kabupaten') || '').toUpperCase().trim();

        let filterInv  = ($('#filterInvestor').val() || '').toUpperCase().trim();
        let filterProv = ($('#filterProvinsi').val() || '').toUpperCase().trim();
        let filterKab  = ($('#filterKabupaten').val() || '').toUpperCase().trim();

        if (filterInv && rowInv !== filterInv) return false;
        if (filterProv && rowProv !== filterProv) return false;
        if (filterKab && rowKab !== filterKab) return false;
        return true;
    });

    function formatNumber(num) {
        return Number(num || 0).toLocaleString('id-ID');
    }
    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    function capitalizeWords(str) {
        if (!str) return '';
        return str.toLowerCase().replace(/\b[a-z]/g, function(letter) {
            return letter.toUpperCase();
        });
    }

    // ============================================================
    // AJAX Dynamic Update Data Omzet Tanpa Reload Halaman
    // ============================================================
    function fetchAndRenderOmzetTable(bulan, tahun) {
        $.post(adminUrl + "/ajax/post/outlet/get_monitoring_omzet", { bulan: bulan, tahun: tahun }, function(res) {
            if (res && res.success && res.outlets) {
                res.outlets.forEach(function(row) {
                    let $tr = $('#outlet-row-' + row.id_outlet);
                    if ($tr.length) {
                        let omzetNom = parseFloat(row.omzet_periode) || 0;
                        let trxNom   = parseInt(row.transaksi_periode) || 0;
                        let potNom   = parseFloat(row.potongan_periode) || 0;
                        let invNom   = parseFloat(row.hak_investor_periode) || 0;
                        let tarifNom = parseInt(row.biaya_langganan_outlet) || 100000;

                        let trxHtml = (trxNom > 0) ? `<span class="badge bg-primary">${formatNumber(trxNom)} Trx</span>` : `<span class="text-muted">0 Trx</span>`;
                        let omzetHtml = `Rp ${formatNumber(omzetNom)}`;
                        let potHtml = `Rp ${formatNumber(potNom)}`;
                        let invHtml = `Rp ${formatNumber(invNom)}`;
                        
                        let tarifHtml = `<strong>Rp ${formatNumber(tarifNom)}</strong><small class="text-muted"> / Bln</small>`;
                        if (omzetNom >= 10000000) {
                            tarifHtml += `<br><span class="badge bg-success mt-1" style="font-size: 10.5px;"><i class="fa fa-arrow-up me-1"></i>Rekomen Naik</span>`;
                        }

                        let rincianUrl = `${adminUrl}/outlet/rincian_omzet?id=${row.id_outlet}&bulan=${bulan}&tahun=${tahun}`;

                        $tr.find('.col-trx').html(trxHtml);
                        $tr.find('.col-omzet').html(omzetHtml).removeClass('text-success text-muted').addClass(omzetNom > 0 ? 'text-success' : 'text-muted');
                        $tr.find('.col-pot').html(potHtml);
                        $tr.find('.col-inv').html(invHtml);
                        $tr.find('.col-tarif').html(tarifHtml);
                        $tr.find('.btn-rincian').attr('href', rincianUrl);
                    }
                });
            }
        });
    }

    // ============================================================
    // Event Trigger Filter Omzet
    $('#filterBulan, #filterTahun').on('change', function() {
        let bulan = $('#filterBulan').val() || 0;
        let tahun = $('#filterTahun').val() || 0;
        fetchAndRenderOmzetTable(bulan, tahun);
    });

    $('#filterInvestor, #filterKabupaten').on('change', function() {
        if (tableOmzet) {
            tableOmzet.draw();
        }
    });

    // Filter Provinsi
    $('#filterProvinsi').on('change', function() {
        let prov = $(this).val();
        $('#filterKabupaten').html('<option value="">Semua Kabupaten</option>').prop('disabled', true);
        initFilterSelect2('#filterKabupaten');

        if (prov) {
            $.post(adminUrl + "/ajax/post/wilayah/get_kabupaten", { provinsi: prov }, function(res) {
                let options = '<option value="">Semua Kabupaten</option>';
                if (res.results) {
                    res.results.forEach(item => {
                        options += `<option value="${item.id}">${item.text}</option>`;
                    });
                }
                $('#filterKabupaten').html(options).prop('disabled', false);
                initFilterSelect2('#filterKabupaten');
                openNextFilterSelect2('#filterKabupaten');
            });
        }
        if (tableOmzet) tableOmzet.draw();
    });

    // Reset Filter (semua filter kembali ke default tanpa reload)
    $('#btnResetFilter').on('click', function() {
        $('#filterBulan').val('').trigger('change.select2');
        $('#filterTahun').val('').trigger('change.select2');
        $('#filterInvestor').val('').trigger('change.select2');
        $('#filterProvinsi').val('').trigger('change.select2');
        $('#filterKabupaten').html('<option value="">Semua Kabupaten</option>').prop('disabled', true);
        initFilterSelect2('#filterBulan');
        initFilterSelect2('#filterTahun');
        initFilterSelect2('#filterInvestor');
        initFilterSelect2('#filterProvinsi');
        initFilterSelect2('#filterKabupaten');
        fetchAndRenderOmzetTable(0, 0);
        if (tableOmzet) tableOmzet.draw();
    });
});
</script>
