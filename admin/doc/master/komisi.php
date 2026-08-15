<?php
use Config\Core\SystemInfo;
use App\Models\Master;

// Fetch all Komisi records
$listKomisi = Master::getAllKomisi();
$rowsKomisi = [];
$daftarTahunKomisi = [];
if ($listKomisi && $listKomisi->num_rows > 0) {
    while ($r = $listKomisi->fetch_assoc()) {
        $rowsKomisi[] = $r;
        if (!empty($r['tgl_transfer'])) {
            $y = date('Y', strtotime($r['tgl_transfer']));
            if (!in_array($y, $daftarTahunKomisi)) {
                $daftarTahunKomisi[] = $y;
            }
        }
    }
    rsort($daftarTahunKomisi);
}
if (empty($daftarTahunKomisi)) {
    $daftarTahunKomisi[] = date('Y');
}
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Komisi Master Owner</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item">Master</li>
            <li class="breadcrumb-item active" aria-current="page">Komisi</li>
        </ol>
    </div>
</div>

<!-- Main Table Card -->
<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-header">
                <div class="d-flex justify-content-between mb-2">
                    <h5 class="card-title">Riwayat Komisi Master</h5>
                    <?php if($adminPermissionCore->isHavePermission($moduleId, "create")) : ?>
                        <a href="<?= SystemInfo::app('ADMIN_URL') ?>/master/komisi_create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Tambah Komisi Master</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <!-- Toolbar Filter Data Komisi Terintegrasi -->
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-lg-4 col-md-4">
                            <label class="form-label small fw-bold mb-1">Filter Master Owner</label>
                            <select id="filterMaster" class="form-select filter-select" data-placeholder="Semua Master Owner">
                                <option value="">Semua Master Owner</option>
                                <?php 
                                $resMastersOpt = Master::getAllMasterOptions();
                                if ($resMastersOpt && $resMastersOpt->num_rows > 0) :
                                    while ($m = $resMastersOpt->fetch_assoc()) :
                                ?>
                                    <option value="<?= htmlspecialchars(strtoupper($m['nama_lengkap'])); ?>"><?= htmlspecialchars($m['nama_lengkap']); ?> (@<?= htmlspecialchars($m['username']); ?>)</option>
                                <?php 
                                    endwhile;
                                endif; 
                                ?>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-3">
                            <label class="form-label small fw-bold mb-1">Filter Bulan</label>
                            <select id="filterBulan" class="form-select filter-select" data-placeholder="Semua Bulan">
                                <option value="">Semua Bulan</option>
                                <option value="01">Januari</option>
                                <option value="02">Februari</option>
                                <option value="03">Maret</option>
                                <option value="04">April</option>
                                <option value="05">Mei</option>
                                <option value="06">Juni</option>
                                <option value="07">Juli</option>
                                <option value="08">Agustus</option>
                                <option value="09">September</option>
                                <option value="10">Oktober</option>
                                <option value="11">November</option>
                                <option value="12">Desember</option>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-3">
                            <label class="form-label small fw-bold mb-1">Filter Tahun</label>
                            <select id="filterTahun" class="form-select filter-select" data-placeholder="Semua Tahun">
                                <option value="">Semua Tahun</option>
                                <?php foreach ($daftarTahunKomisi as $y) : ?>
                                    <option value="<?= $y; ?>"><?= $y; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-2">
                            <button type="button" id="btnResetFilter" class="btn btn-secondary btn-sm w-100 d-flex align-items-center justify-content-center" style="height: 38px;" title="Reset semua filter komisi">
                                <i class="fe fe-refresh-cw me-1"></i> Reset Filter
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100 align-middle" id="table-komisi-master">
                        <thead>
                            <tr class="text-center">
                                <th class="text-center" style="width: 5%;">NO</th>
                                <th class="text-center">TANGGAL TRANSFER</th>
                                <th class="text-center">NAMA MASTER</th>
                                <th class="text-center">NOMINAL KOMISI</th>
                                <th class="text-center">BUKTI BAYAR</th>
                                <th class="text-center">CATATAN</th>
                                <th class="text-center" style="width: 12%;">#</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($rowsKomisi)) : ?>
                                <?php $no = 1; foreach ($rowsKomisi as $row) : ?>
                                    <tr data-master="<?= htmlspecialchars(strtoupper($row['nama_master'] ?? '')) ?>"
                                        data-bulan="<?= date('m', strtotime($row['tgl_transfer'])) ?>"
                                        data-tahun="<?= date('Y', strtotime($row['tgl_transfer'])) ?>">
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td class="text-center"><?= date("d/m/Y H:i", strtotime($row['tgl_transfer'])) ?></td>
                                        <td class="text-start">
                                            <strong class="text-primary"><?= htmlspecialchars($row['nama_master']) ?></strong>
                                            <br><small class="text-muted"><code>@<?= htmlspecialchars($row['username_master']) ?></code></small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success fs-6">Rp <?= number_format($row['nominal_transfer_komisi'], 0, ',', '.') ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if (!empty($row['bukti_pembayaran'])) : ?>
                                                <?php $fileExt = strtolower(pathinfo($row['bukti_pembayaran'], PATHINFO_EXTENSION)); ?>
                                                <?php if ($fileExt === 'pdf') : ?>
                                                    <a href="<?= SystemInfo::app('ADMIN_URL') ?>/image-proxy.php?file=<?= urlencode($row['bukti_pembayaran']) ?>" target="_blank" class="btn btn-outline-info btn-sm">
                                                        <i class="fas fa-file-pdf me-1"></i> Lihat PDF
                                                    </a>
                                                <?php else : ?>
                                                    <button type="button" class="btn btn-outline-info btn-sm" 
                                                            onclick="previewBuktiKomisi('<?= htmlspecialchars($row['bukti_pembayaran'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['nama_master'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['catatan'] ?? '-', ENT_QUOTES) ?>', 'Rp <?= number_format($row['nominal_transfer_komisi'], 0, ',', '.') ?>')">
                                                        <i class="fas fa-image me-1"></i> Lihat Bukti
                                                    </button>
                                                <?php endif; ?>
                                            <?php else : ?>
                                                <span class="badge bg-light text-dark">Belum ada</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-start"><?= htmlspecialchars($row['catatan'] ?? '-') ?></td>
                                        <td class="text-center">
                                            <div class="action d-flex justify-content-center gap-2">
                                                <?php if($adminPermissionCore->isHavePermission($moduleId, "create")) : ?>
                                                    <a href="<?= SystemInfo::app('ADMIN_URL') ?>/master/komisi_create?id=<?= $row['id_komisi'] ?>" class="btn btn-success btn-sm text-white" title="Edit Komisi"><i class="fas fa-edit"></i></a>
                                                <?php endif; ?>
                                                <?php if($adminPermissionCore->isHavePermission($moduleId, "delete")) : ?>
                                                    <button type="button" class="btn btn-danger btn-sm text-white" title="Hapus Komisi" 
                                                            onclick="deleteKomisi(<?= $row['id_komisi'] ?>, '<?= htmlspecialchars($row['nama_master'], ENT_QUOTES, 'UTF-8') ?>', 'Rp <?= number_format($row['nominal_transfer_komisi'], 0, ',', '.') ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Belum ada riwayat komisi master terdaftar.</td>
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
function previewBuktiKomisi(filePath, namaMaster, catatan, nominal) {
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
        + '  <i class="fa fa-user-circle text-primary me-2" style="width:20px; text-align:center;"></i>'
        + '  <span style="min-width:140px;" class="fw-bold">Master Owner:</span>'
        + '  <span class="text-dark fw-semibold">' + (namaMaster || '-') + '</span>'
        + '</div>'
        + '<div class="d-flex align-items-center mb-2">'
        + '  <i class="fa fa-calendar-check-o text-success me-2" style="width:20px; text-align:center;"></i>'
        + '  <span style="min-width:140px;" class="fw-bold">Catatan:</span>'
        + '  <span class="text-dark">' + (catatan || '-') + '</span>'
        + '</div>'
        + '<div class="d-flex align-items-center">'
        + '  <i class="fa fa-money text-warning me-2" style="width:20px; text-align:center;"></i>'
        + '  <span style="min-width:140px;" class="fw-bold">Nominal Komisi:</span>'
        + '  <span class="text-success fw-bold">' + nominal + '</span>'
        + '</div>'
        + '</div>';

    Swal.fire({
        title: '<i class="fa fa-file-text-o me-2 text-info"></i>Bukti Pembayaran Komisi Master',
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

function deleteKomisi(id, master, nominal) {
    Swal.fire({
        title: 'Hapus Record Komisi?',
        text: `Hapus komisi untuk Master ${master} sebesar ${nominal}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus Komisi',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses...',
                text: 'Sedang menghapus data komisi',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.post("<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/master/komisi", { action: 'delete', id_komisi: id }, function(resp) {
                if (resp.success) {
                    Swal.fire('Berhasil!', resp.message, 'success').then(() => { location.reload(); });
                } else {
                    Swal.fire('Gagal!', resp.message || 'Gagal menghapus data komisi', 'error');
                }
            }, 'json').fail(() => {
                Swal.fire('Error!', 'Terjadi kesalahan sistem (Server Error). Silakan muat ulang halaman.', 'error');
            });
        }
    });
}

let tableKomisi = null;

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
    initFilterSelect2('#filterMaster');
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
    $('#filterMaster').on('select2:select', function() {
        openNextFilterSelect2('#filterBulan');
    });

    $('#filterBulan').on('select2:select', function() {
        openNextFilterSelect2('#filterTahun');
    });

    // Inisialisasi DataTable Komisi
    if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#table-komisi-master')) {
        tableKomisi = $('#table-komisi-master').DataTable({
            processing: true,
            deferRender: true,
            scrollX: true,
            lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
            pageLength: 5,
            language: {
                searchPlaceholder: 'Cari komisi...',
                sSearch: '',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                paginate: { first: 'First', last: 'Last', next: 'Next', previous: 'Previous' }
            },
            order: [[1, 'desc']]
        });

        if ($.fn.select2) {
            setTimeout(function() {
                $('#table-komisi-master_wrapper .dataTables_length select').select2({
                    minimumResultsForSearch: Infinity,
                    width: 'auto'
                });
            }, 50);
        }
    }

    // Filter Custom DataTable untuk Komisi
    $.fn.dataTable.ext.search.push(function(settings, searchData, index) {
        if (settings.nTable.id !== 'table-komisi-master') {
            return true;
        }
        if (!tableKomisi) {
            return true;
        }

        let $row = $(tableKomisi.row(index).node());
        let rowMaster = ($row.attr('data-master') || '').toUpperCase().trim();
        let rowBulan  = ($row.attr('data-bulan') || '').trim();
        let rowTahun  = ($row.attr('data-tahun') || '').trim();

        let filterMaster = ($('#filterMaster').val() || '').toUpperCase().trim();
        let filterBulan  = ($('#filterBulan').val() || '').trim();
        let filterTahun  = ($('#filterTahun').val() || '').trim();

        if (filterMaster && rowMaster !== filterMaster) {
            return false;
        }
        if (filterBulan && rowBulan !== filterBulan) {
            return false;
        }
        if (filterTahun && rowTahun !== filterTahun) {
            return false;
        }

        return true;
    });

    // Event Trigger Filter Komisi
    $('#filterMaster, #filterBulan, #filterTahun').on('change', function() {
        if (tableKomisi) {
            tableKomisi.draw();
        }
    });

    // Reset Filter Button
    $('#btnResetFilter').on('click', function() {
        $('#filterMaster').val('').trigger('change');
        $('#filterBulan').val('').trigger('change');
        $('#filterTahun').val('').trigger('change');
        if (tableKomisi) {
            tableKomisi.draw();
        }
    });
});
</script>
