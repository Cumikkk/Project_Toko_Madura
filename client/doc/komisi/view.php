<?php
use App\Models\User;
use App\Models\Master;
use Config\Core\SystemInfo;

$user = User::user();
$masterId = (int)($user['MBR_ID'] ?? $user['id_users'] ?? 0);
$namaMaster = $user['nama_lengkap'] ?? $user['username'] ?? 'Master Owner';

// Fetch All Komisi List for Master via Master Model
$dataKomisi = Master::getKomisiListForMaster($masterId);
$komisiList = $dataKomisi['komisiList'];
$totalOverallKomisi = $dataKomisi['totalOverallKomisi'];
$totalKomisiBulanIni = $dataKomisi['totalKomisiBulanIni'];

// Fetch distinct years of komisi transfers via Master Model
$availableYears = Master::getAvailableTahunKomisiByMaster($masterId);

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
                            <h2 class="fw-bold mb-2 text-white fs-3 fs-md-2">Komisi Master</h2>
                            <p class="text-white-50 small mb-0">Rekapan komisi & apresiasi dari Admin atas kontribusi kemitraan investor Anda.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Metrics Summary Cards -->
    <div class="row g-2 g-md-3 mb-4">
        <!-- Card 1: Total Komisi Diterima -->
        <div class="col-md-6 col-12">
            <div class="card border border-body-subtle shadow-sm h-100" style="border-radius: 16px;">
                <div class="card-body p-3 p-md-4 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" style="width: 48px; height: 48px; background: linear-gradient(135deg, #198754 0%, #0d5132 100%);">
                        <i class="fa-solid fa-trophy fs-4"></i>
                    </div>
                    <div>
                        <div class="text-body-secondary text-uppercase fw-bold small mb-1">Total Komisi</div>
                        <div class="fs-4 fw-bold text-success mb-0" id="metricTotalKomisi">Rp <?= number_format($totalOverallKomisi, 0, ',', '.'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Komisi Bulan Ini -->
        <div class="col-md-6 col-12">
            <div class="card border border-body-subtle shadow-sm h-100" style="border-radius: 16px;">
                <div class="card-body p-3 p-md-4 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" style="width: 48px; height: 48px; background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                        <i class="fa-solid fa-calendar-check fs-4"></i>
                    </div>
                    <div>
                        <div class="text-body-secondary text-uppercase fw-bold small mb-1">Komisi Bulan Ini</div>
                        <div class="fs-4 fw-bold text-primary mb-0" id="metricKomisiBulanIni">Rp <?= number_format($totalKomisiBulanIni, 0, ',', '.'); ?></div>
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
                                <i class="fa-solid fa-award me-2 text-danger"></i>Riwayat Penyerahan Komisi
                            </h5>
                            <p class="text-body-secondary small mb-0">Pantau seluruh riwayat bukti transfer & komisi dari Admin</p>
                        </div>
                        <div>
                            <button type="button" id="btnResetFilterKomisi" class="btn btn-outline-danger btn-sm d-none align-items-center gap-1.5 rounded-pill px-3 shadow-xs" title="Reset Semua Filter">
                                <i class="fa-solid fa-rotate-left"></i> Reset Filter
                            </button>
                        </div>
                    </div>

                    <!-- INSTANT INLINE FILTER TOOLBAR (No Page Reload) -->
                    <div class="mt-3 pt-3 border-top border-body-subtle" id="toolbarFilterKomisi">
                        <div class="row g-2 align-items-end">
                            <!-- 1. Filter Bulan & Tahun Transfer -->
                            <div class="col-lg-3 col-md-6 col-12">
                                <label class="form-label text-body-secondary small fw-bold mb-1" style="font-size: 11px;">
                                    <i class="fa-solid fa-calendar-days text-danger me-1"></i>Periode Transfer
                                </label>
                                <div class="input-group input-group-sm">
                                    <select id="filterBulanKomisi" class="form-select form-select-sm bg-body-tertiary border-body-subtle fw-semibold" style="height: 38px;">
                                        <option value="0" hidden selected>Semua Bulan</option>
                                        <?php foreach ($bulanIndo as $mNum => $mName) : ?>
                                            <option value="<?= $mNum; ?>"><?= $mName; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select id="filterTahunKomisi" class="form-select form-select-sm bg-body-tertiary border-body-subtle fw-semibold" style="height: 38px;">
                                        <option value="0" hidden selected>Semua Tahun</option>
                                        <?php foreach ($availableYears as $y) : ?>
                                            <option value="<?= $y; ?>"><?= $y; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- 2. Rentang Tanggal Spesifik (Opsional) -->
                            <div class="col-lg-3 col-md-6 col-12" id="wrapRangeDate">
                                <label class="form-label text-body-secondary small fw-bold mb-1" style="font-size: 11px;">
                                    <i class="fa-solid fa-calendar-range text-danger me-1"></i>Rentang Tanggal (Opsional)
                                </label>
                                <div class="input-group input-group-sm">
                                    <input type="date" id="filterTglMulai" class="form-control form-control-sm bg-body-tertiary border-body-subtle fw-semibold" title="Tanggal Mulai" style="height: 38px;">
                                    <span class="input-group-text bg-body-tertiary border-body-subtle text-muted" style="height: 38px;">-</span>
                                    <input type="date" id="filterTglSelesai" class="form-control form-control-sm bg-body-tertiary border-body-subtle fw-semibold" title="Tanggal Selesai" style="height: 38px;">
                                </div>
                            </div>

                            <!-- 3. Urutkan Berdasarkan -->
                            <div class="col-lg-3 col-md-6 col-12">
                                <label class="form-label text-body-secondary small fw-bold mb-1" style="font-size: 11px;">
                                    <i class="fa-solid fa-arrow-down-a-z text-danger me-1"></i>Urutkan
                                </label>
                                <select id="filterSortKomisi" class="form-select form-select-sm bg-body-tertiary border-body-subtle fw-semibold" style="height: 38px;">
                                    <option value="newest">Default</option>
                                    <option value="highest">Nominal Terbesar</option>
                                    <option value="lowest">Nominal Terkecil</option>
                                </select>
                            </div>

                            <!-- 4. Cari Catatan / Nominal -->
                            <div class="col-lg-3 col-md-6 col-12">
                                <label class="form-label text-body-secondary small fw-bold mb-1" style="font-size: 11px;">
                                    <i class="fa-solid fa-magnifying-glass text-danger me-1"></i>Cari Catatan / Nominal
                                </label>
                                <input type="text" id="liveSearchKomisi" class="form-control form-control-sm bg-body-tertiary border-body-subtle fw-semibold" placeholder="Ketik catatan / nominal..." style="height: 38px;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-2 p-md-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 w-100" id="tableDataKomisi">
                            <thead class="table-group-divider bg-body-secondary">
                                <tr class="text-uppercase small text-body-secondary">
                                    <th class="ps-3 text-center" style="width: 50px;">No</th>
                                    <th class="text-center">Tanggal Transfer</th>
                                    <th>Catatan</th>
                                    <th class="text-center">Nominal Komisi</th>
                                    <th class="text-center pe-3">Bukti Bayar</th>
                                </tr>
                            </thead>
                            <tbody class="border-0" id="tbodyKomisi">
                                <?php if (!empty($komisiList)) : ?>
                                    <?php foreach ($komisiList as $index => $km) : 
                                        $tglStr = !empty($km['tgl_transfer']) ? date('Y-m-d', strtotime($km['tgl_transfer'])) : '';
                                        $mNum = !empty($km['tgl_transfer']) ? (int)date('n', strtotime($km['tgl_transfer'])) : 0;
                                        $yNum = !empty($km['tgl_transfer']) ? (int)date('Y', strtotime($km['tgl_transfer'])) : 0;
                                        $nomVal = (float)($km['nominal_transfer_komisi'] ?? 0);
                                        $buktiFoto = !empty($km['bukti_pembayaran']) ? trim($km['bukti_pembayaran']) : '';
                                        $proxyUrl = !empty($buktiFoto) ? SystemInfo::app('CLIENT_URL') . '/image-proxy.php?file=' . urlencode($buktiFoto) : '';
                                        $fileExt = !empty($buktiFoto) ? strtolower(pathinfo($buktiFoto, PATHINFO_EXTENSION)) : '';
                                    ?>
                                        <tr class="komisi-data-row" 
                                            data-date="<?= $tglStr; ?>"
                                            data-month="<?= $mNum; ?>"
                                            data-year="<?= $yNum; ?>"
                                            data-nominal="<?= $nomVal; ?>"
                                            data-id="<?= (int)$km['id_komisi']; ?>">
                                            <td class="ps-3 text-center fw-bold text-body-secondary row-index-num"><?= $index + 1; ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-body-tertiary border text-body-emphasis px-2.5 py-1 rounded-3 fw-semibold font-monospace small">
                                                    <i class="fa-regular fa-clock me-1 text-primary"></i>
                                                    <?= !empty($km['tgl_transfer']) ? date("d/m/Y H:i", strtotime($km['tgl_transfer'])) . ' WIB' : '-'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-body-emphasis mb-0 fs-6"><?= htmlspecialchars($km['catatan'] ?: '-'); ?></div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-bold fs-12">
                                                    + Rp <?= number_format($nomVal, 0, ',', '.'); ?>
                                                </span>
                                            </td>
                                            <td class="text-center pe-3">
                                                <?php if (!empty($buktiFoto)) : ?>
                                                    <?php if ($fileExt === 'pdf') : ?>
                                                        <a href="<?= $proxyUrl; ?>" target="_blank" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1 fw-semibold shadow-xs">
                                                            <i class="fa-solid fa-file-pdf me-1"></i> Lihat PDF
                                                        </a>
                                                    <?php else : ?>
                                                        <button type="button" 
                                                                class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1 fw-semibold btn-client-view-bukti-komisi shadow-xs" 
                                                                data-img="<?= $proxyUrl; ?>"
                                                                data-master="<?= htmlspecialchars($namaMaster, ENT_QUOTES, 'UTF-8'); ?>"
                                                                data-periode="<?= htmlspecialchars($km['catatan'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>"
                                                                data-nominal="Rp <?= number_format($nomVal, 0, ',', '.'); ?>"
                                                                title="Lihat Bukti Transfer">
                                                            <i class="fa-solid fa-image me-1"></i> Lihat Bukti
                                                        </button>
                                                    <?php endif; ?>
                                                <?php else : ?>
                                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1 font-monospace small">
                                                        Tidak Ada
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr id="emptyStaticRowKomisi">
                                        <td colspan="5" class="text-center py-5 text-body-secondary">
                                            <i class="fa-solid fa-receipt fs-1 text-muted opacity-50 mb-2 d-block"></i>
                                            Belum ada riwayat penyerahan komisi.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <tr id="noMatchingFilterRowKomisi" style="display: none;">
                                    <td colspan="5" class="text-center py-5 text-body-secondary">
                                        <i class="fa-solid fa-filter-circle-xmark fs-1 text-danger opacity-50 mb-2 d-block"></i>
                                        Tidak ada riwayat komisi yang sesuai dengan kriteria filter saat ini.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Record Summary Footer -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-3 border-top border-body-subtle mt-2">
                        <div class="small text-body-secondary fw-semibold ms-1">
                            Menampilkan <span class="text-body-emphasis fw-bold" id="footerCountVisibleKomisi"><?= count($komisiList); ?></span> dari <span class="text-body-emphasis fw-bold"><?= count($komisiList); ?></span> riwayat komisi
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {

    // INSTANT CLIENT-SIDE FILTER (0 Milidetik, Tanpa Reload Halaman)
    function applyInstantFilterKomisi() {
        let bulan = parseInt($('#filterBulanKomisi').val()) || 0;
        let tahun = parseInt($('#filterTahunKomisi').val()) || 0;
        let tglMulai = $('#filterTglMulai').val();
        let tglSelesai = $('#filterTglSelesai').val();
        let sort = $('#filterSortKomisi').val();
        let search = ($('#liveSearchKomisi').val() || '').toLowerCase().trim();

        let hasActiveFilter = (bulan > 0 || tahun > 0 || tglMulai !== '' || tglSelesai !== '' || sort !== 'newest' || search !== '');

        if (hasActiveFilter) {
            $('#btnResetFilterKomisi').removeClass('d-none').addClass('d-inline-flex');
        } else {
            $('#btnResetFilterKomisi').removeClass('d-inline-flex').addClass('d-none');
        }

        // Sorting Rows in DOM
        let $tbody = $('#tbodyKomisi');
        let rows = $('.komisi-data-row').get();

        rows.sort(function(a, b) {
            let dateA = $(a).attr('data-date');
            let dateB = $(b).attr('data-date');
            let nomA = parseFloat($(a).attr('data-nominal')) || 0;
            let nomB = parseFloat($(b).attr('data-nominal')) || 0;
            let idA = parseInt($(a).attr('data-id')) || 0;
            let idB = parseInt($(b).attr('data-id')) || 0;

            if (sort === 'highest') {
                return (nomB - nomA) || (dateB.localeCompare(dateA)) || (idB - idA);
            } else if (sort === 'lowest') {
                return (nomA - nomB) || (dateB.localeCompare(dateA)) || (idB - idA);
            } else { // newest
                return (dateB.localeCompare(dateA)) || (idB - idA);
            }
        });

        $.each(rows, function(idx, row) {
            $tbody.append(row);
        });

        // Filter Rows & Calculate Metrics
        let visibleCount = 0;
        let totalSum = 0;
        let thisMonthSum = 0;
        let now = new Date();
        let nowMonth = now.getMonth() + 1;
        let nowYear  = now.getFullYear();

        $('.komisi-data-row').each(function() {
            let $row = $(this);
            let rowDate = $row.attr('data-date');
            let rowMonth = parseInt($row.attr('data-month')) || 0;
            let rowYear = parseInt($row.attr('data-year')) || 0;
            let rowNominal = parseFloat($row.attr('data-nominal')) || 0;
            let rowText = $row.text().toLowerCase();

            let matchMonth = (bulan === 0 || rowMonth === bulan);
            let matchYear = (tahun === 0 || rowYear === tahun);
            let matchDateRange = true;
            if (tglMulai && rowDate < tglMulai) matchDateRange = false;
            if (tglSelesai && rowDate > tglSelesai) matchDateRange = false;
            let matchSearch = (!search || rowText.indexOf(search) > -1);

            if (matchMonth && matchYear && matchDateRange && matchSearch) {
                $row.show();
                visibleCount++;
                totalSum += rowNominal;
                if (rowMonth === nowMonth && rowYear === nowYear) {
                    thisMonthSum += rowNominal;
                }
                $row.find('.row-index-num').text(visibleCount);
            } else {
                $row.hide();
            }
        });

        $('#metricTotalKomisi').text('Rp ' + totalSum.toLocaleString('id-ID'));
        $('#metricKomisiBulanIni').text('Rp ' + thisMonthSum.toLocaleString('id-ID'));
        $('#footerCountVisibleKomisi').text(visibleCount);

        if (visibleCount === 0) {
            $('#noMatchingFilterRowKomisi').show();
        } else {
            $('#noMatchingFilterRowKomisi').hide();
        }
    }

    // Attach Instant Events
    $('#filterBulanKomisi, #filterTahunKomisi, #filterSortKomisi').on('change', function() {
        applyInstantFilterKomisi();
    });

    $('#filterTglMulai, #filterTglSelesai').on('change input', function() {
        applyInstantFilterKomisi();
    });

    // Buka popup kalender saat input tanggal diklik (tetap bisa ketik manual)
    $('#filterTglMulai, #filterTglSelesai').on('click', function() {
        try {
            if (typeof this.showPicker === 'function') {
                this.showPicker();
            }
        } catch (e) {}
    });

    $('#liveSearchKomisi').on('keyup search', function() {
        applyInstantFilterKomisi();
    });

    $('#btnResetFilterKomisi').on('click', function() {
        $('#filterBulanKomisi').val('0');
        $('#filterTahunKomisi').val('0');
        $('#filterTglMulai').val('');
        $('#filterTglSelesai').val('');
        $('#filterSortKomisi').val('newest');
        $('#liveSearchKomisi').val('');
        applyInstantFilterKomisi();
    });

    // Preview Bukti Transfer Popup (Desain Asli Sesuai Template Komisi)
    $(document).on('click', '.btn-client-view-bukti-komisi', function() {
        let imgUrl  = $(this).data('img');
        let master  = $(this).data('master');
        let periode = $(this).data('periode');
        let nominal = $(this).data('nominal');

        var infoHtml = '<div class="text-start bg-light p-3 rounded mb-3" style="font-size:13.5px; border:1px solid #e9ecef;">'
            + '<div class="d-flex align-items-center mb-2">'
            + '  <i class="fa-solid fa-user-circle text-primary me-2" style="width:20px; text-align:center;"></i>'
            + '  <span style="min-width:140px;" class="fw-bold">Master Owner:</span>'
            + '  <span class="text-dark fw-semibold">' + (master || '-') + '</span>'
            + '</div>'
            + '<div class="d-flex align-items-center mb-2">'
            + '  <i class="fa-solid fa-calendar-check text-success me-2" style="width:20px; text-align:center;"></i>'
            + '  <span style="min-width:140px;" class="fw-bold">Keterangan:</span>'
            + '  <span class="text-dark">' + (periode || '-') + '</span>'
            + '</div>'
            + '<div class="d-flex align-items-center">'
            + '  <i class="fa-solid fa-money-bill-wave text-warning me-2" style="width:20px; text-align:center;"></i>'
            + '  <span style="min-width:140px;" class="fw-bold">Nominal Komisi:</span>'
            + '  <span class="text-success fw-bold">' + nominal + '</span>'
            + '</div>'
            + '</div>';

        Swal.fire({
            title: '<div class="fw-bold text-danger fs-5"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Bukti Pembayaran Komisi Master</div>',
            html: infoHtml
                + '<img src="' + imgUrl + '" '
                + 'style="max-width:100%;max-height:60vh;border-radius:8px;border:1px solid #dee2e6;object-fit:contain;" '
                + 'onerror="this.outerHTML=\'<p class=\\\'text-danger mt-2\\\'><i class=\\\'fa-solid fa-triangle-exclamation me-1\\\'></i> Gambar gagal dimuat</p>\'">',
            showCloseButton: true,
            showConfirmButton: false,
            confirmButtonColor: '#7D0A0A',
            customClass: {
                popup: 'rounded-4'
            }
        });
    });

});
</script>
