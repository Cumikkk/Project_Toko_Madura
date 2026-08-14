<?php
use App\Models\Investor;
use App\Models\Master;
use Config\Core\SystemInfo;

$loggedInLevel = intval($user['ADM_LEVEL'] ?? 1);
$loggedInId    = intval($user['ADM_ID'] ?? 1);

// Fetch investors list with Master Owner name and active outlet counts
$investors = Investor::getAllInvestors($loggedInLevel, $loggedInId);
$masterOptions = Master::getAllMasterOptions();
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Daftar Investor Toko Madura</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Investor</li>
        </ol>
    </div>
</div>

<!-- Filter Card -->
<div class="row row-sm mb-3">
    <div class="col-lg-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="d-flex justify-content-between mb-2">
                    <h5 class="card-title">Filter Data Wilayah & Master</h5>
                    <button type="button" id="btnResetFilter" class="btn btn-secondary btn-sm" title="Reset semua filter">
                        <i class="fe fe-refresh-cw me-1"></i> Reset Filter
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-4 col-md-6">
                        <label class="form-label fw-bold">Filter Master Owner</label>
                        <select id="filterMaster" class="form-select filter-select" data-placeholder="Semua Master Owner">
                            <option value="">Semua Master Owner</option>
                            <?php if ($masterOptions && $masterOptions->num_rows > 0) : ?>
                                <?php while ($m = $masterOptions->fetch_assoc()) : ?>
                                    <option value="<?= htmlspecialchars(strtoupper($m['nama_lengkap'])); ?>">
                                        <?= htmlspecialchars($m['nama_lengkap']); ?> (@<?= htmlspecialchars($m['username']); ?>)
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <label class="form-label fw-bold">Filter Provinsi</label>
                        <select id="filterProvinsi" class="form-select filter-select" data-placeholder="Semua Provinsi">
                            <option value="">Semua Provinsi</option>
                        </select>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <label class="form-label fw-bold">Filter Kabupaten / Kota</label>
                        <select id="filterKabupaten" class="form-select filter-select" data-placeholder="Semua Kabupaten" disabled>
                            <option value="">Semua Kabupaten</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Table Card -->
<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-header">
                <div class="d-flex justify-content-between mb-2">
                    <h5 class="card-title">List Investor</h5>
                    <?php if($adminPermissionCore->isHavePermission($moduleId, "create")) : ?>
                        <a href="<?= SystemInfo::app('ADMIN_URL') ?>/investor/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Tambah Investor</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100 align-middle" id="investor-table">
                        <thead>
                            <tr class="text-center">
                                <th class="text-center" style="width: 5%;">NO</th>
                                <th class="text-center">TANGGAL BERGABUNG</th>
                                <th class="text-center">NAMA INVESTOR</th>
                                <th class="text-center">NO. HP</th>
                                <th class="text-center">WILAYAH</th>
                                <th class="text-center">BIAYA LANGGANAN / OUTLET</th>
                                <th class="text-center">MASTER OWNER</th>
                                <th class="text-center">TOTAL OUTLET AKTIF</th>
                                <th class="text-center" style="width: 15%;">#</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($investors && $investors->num_rows > 0) : ?>
                                <?php $no = 1; while ($row = $investors->fetch_assoc()) : ?>
                                    <tr data-provinsi="<?= htmlspecialchars(strtoupper($row['provinsi'] ?? '')) ?>" 
                                        data-kabupaten="<?= htmlspecialchars(strtoupper($row['kabupaten'] ?? '')) ?>" 
                                        data-kecamatan="<?= htmlspecialchars(strtoupper($row['kecamatan'] ?? '')) ?>" 
                                        data-kelurahan="<?= htmlspecialchars(strtoupper($row['kelurahan'] ?? '')) ?>"
                                        data-master="<?= htmlspecialchars(strtoupper($row['nama_master'] ?? '')) ?>">
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td class="text-center"><?= !empty($row['tanggal_bergabung']) ? date("d/m/Y H:i", strtotime($row['tanggal_bergabung'])) : '-' ?></td>
                                        <td class="text-start">
                                            <strong class="text-primary"><?= htmlspecialchars($row['nama_lengkap']) ?></strong>
                                            <br><small class="text-muted"><code>@<?= htmlspecialchars($row['username']) ?></code></small>
                                        </td>
                                        <td class="text-center"><?= htmlspecialchars($row['no_hp'] ?? '-') ?></td>
                                        <td class="text-center">
                                            <?php if (!empty($row['kecamatan']) && $row['kecamatan'] !== '-') : ?>
                                                <?php if (!empty($row['alamat_investor'])) : ?>
                                                    <span class="badge bg-light text-dark border btn-lihat-alamat shadow-xs py-1.5 px-2.5" style="cursor: pointer; font-size: 13px; font-weight: 500;" 
                                                          data-nama="<?= htmlspecialchars($row['nama_lengkap']) ?>" 
                                                          data-alamat="<?= htmlspecialchars($row['alamat_investor']) ?>" 
                                                          data-provinsi="<?= htmlspecialchars($row['provinsi'] ?? '') ?>"
                                                          data-kabupaten="<?= htmlspecialchars($row['kabupaten'] ?? '') ?>"
                                                          data-kecamatan="<?= htmlspecialchars($row['kecamatan'] ?? '') ?>"
                                                          data-kelurahan="<?= htmlspecialchars($row['kelurahan'] ?? '') ?>"
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
                                        <td class="text-center">Rp <?= number_format($row['biaya_langganan_outlet'] ?? 100000, 0, ',', '.') ?> / Bln</td>
                                        <td class="text-start">
                                            <strong class="text-primary"><?= htmlspecialchars($row['nama_master'] ?? 'Master Owner') ?></strong>
                                            <?php if (!empty($row['username_master'])) : ?>
                                                <br><small class="text-muted"><code>@<?= htmlspecialchars($row['username_master']) ?></code></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (!empty($row['total_outlet']) && $row['total_outlet'] > 0) : ?>
                                                <span class="badge bg-success fs-6"><?= number_format($row['total_outlet']) ?> Toko</span>
                                            <?php else : ?>
                                                <span class="text-muted fs-6">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="action d-flex justify-content-center gap-2">
                                                <?php if($adminPermissionCore->isHavePermission($moduleId, "update")) : ?>
                                                    <a href="<?= SystemInfo::app('ADMIN_URL') ?>/investor/create?id=<?= $row['id_investor'] ?>" class="btn btn-success btn-sm text-white btn-edit" title="Edit Investor"><i class="fas fa-edit"></i></a>
                                                <?php endif; ?>
                                                <?php if($adminPermissionCore->isHavePermission($moduleId, "delete")) : ?>
                                                    <button type="button" class="btn btn-danger btn-sm text-white btn-delete" title="Hapus Investor" onclick="deleteInvestor(<?= $row['id_investor'] ?>, '<?= htmlspecialchars($row['nama_lengkap'], ENT_QUOTES, 'UTF-8') ?>', <?= intval($row['total_outlet'] ?? 0) ?>)"><i class="fas fa-trash"></i></button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">Belum ada data investor terdaftar.</td>
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
$(document).ready(function() {
    var adminUrl = '<?= SystemInfo::app("ADMIN_URL") ?>';

    // Inisialisasi Select2 untuk Filter Wilayah & Master
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

    // Inisialisasi dropdown filter
    initFilterSelect2('#filterMaster');
    initFilterSelect2('#filterProvinsi');
    initFilterSelect2('#filterKabupaten');

    var tableInvestor = null;
    if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#investor-table')) {
        tableInvestor = $('#investor-table').DataTable({
            processing: true,
            deferRender: true,
            scrollX: true,
            lengthMenu: [
                [10, 50, 100, -1],
                [10, 50, 100, "All"]
            ],
            language: {
                searchPlaceholder: 'Cari investor...',
                sSearch: '',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                paginate: {
                    first: 'First',
                    last: 'Last',
                    next: 'Next',
                    previous: 'Previous'
                }
            },
            order: [[1, 'desc']]
        });
    }

    // Load Provinsi untuk Filter
    $.get(adminUrl + "/ajax/post/wilayah/get_provinsi", function(res) {
        let options = '<option value="">Semua Provinsi</option>';
        if (res.results) {
            res.results.forEach(item => {
                options += `<option value="${item.id}">${item.text}</option>`;
            });
        }
        $('#filterProvinsi').html(options);
        initFilterSelect2('#filterProvinsi');
    });

    // Custom filtering function for DataTable
    $.fn.dataTable.ext.search.push(function(settings, searchData, index) {
        if (!settings.nTable || settings.nTable.id !== 'investor-table') {
            return true;
        }
        if (!tableInvestor) {
            return true;
        }
        let $row = $(tableInvestor.row(index).node());
        let rowMaster = ($row.attr('data-master') || '').toUpperCase().trim();
        let rowProv = ($row.attr('data-provinsi') || '').toUpperCase().trim();
        let rowKab = ($row.attr('data-kabupaten') || '').toUpperCase().trim();
        
        let filterMaster = ($('#filterMaster').val() || '').toUpperCase().trim();
        let filterProv = ($('#filterProvinsi').val() || '').toUpperCase().trim();
        let filterKab = ($('#filterKabupaten').val() || '').toUpperCase().trim();
        
        if (filterMaster && rowMaster !== filterMaster) {
            return false;
        }
        if (filterProv && rowProv !== filterProv) {
            return false;
        }
        if (filterKab && rowKab !== filterKab) {
            return false;
        }
        return true;
    });

    // Event filter Master Owner
    $('#filterMaster').on('change select2:select', function(e) {
        if (e.type === 'select2:select' && $(this).val()) {
            openNextFilterSelect2('#filterProvinsi');
        }
        if (tableInvestor) {
            tableInvestor.draw();
        }
    });

    // Event filter Provinsi
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
        if (tableInvestor) {
            tableInvestor.draw();
        }
    });

    // Event filter Kabupaten
    $('#filterKabupaten').on('change', function() {
        if (tableInvestor) {
            tableInvestor.draw();
        }
    });

    // Reset Filter Button
    $('#btnResetFilter').on('click', function() {
        $('#filterMaster').val('').trigger('change');
        $('#filterProvinsi').val('').trigger('change');
        $('#filterKabupaten').html('<option value="">Semua Kabupaten</option>').prop('disabled', true);
        initFilterSelect2('#filterKabupaten');
        if (tableInvestor) {
            tableInvestor.draw();
        }
    });

    // Modal popup detail alamat investor
    $('.btn-lihat-alamat').on('click', function() {
        let nama = $(this).data('nama');
        let alamat = $(this).data('alamat');
        let provinsi = $(this).data('provinsi');
        let kabupaten = $(this).data('kabupaten');
        let kecamatan = $(this).data('kecamatan');
        let kelurahan = $(this).data('kelurahan');
        
        let queryStr = encodeURIComponent(alamat);
        let mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' + queryStr;
        
        Swal.fire({
            title: 'Alamat Lengkap Investor',
            html: '<div class="text-start mb-3" style="display: grid; grid-template-columns: max-content auto 1fr; column-gap: 8px; row-gap: 8px; font-size: 15px; line-height: 1.6;">' +
                    '<div class="fw-bold text-dark">Investor</div>' +
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
    });
});

function deleteInvestor(id, name, totalOutlet) {
    let alertHtml = `
        <div class="text-start fs-14">
            <p class="text-muted mb-3">Tindakan ini akan menghapus akun Investor <strong class="text-dark">${name}</strong> beserta seluruh data yang terikat di bawahnya:</p>
            
            <div class="bg-light p-3 rounded-3 border mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                    <span class="text-dark"><i class="fa fa-handshake-o text-primary me-2 fs-16"></i>Akun Investor (${name})</span>
                    <span class="badge bg-primary rounded-pill px-3">Investor</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                    <span class="text-dark"><i class="fa fa-building text-warning me-2 fs-16"></i>Outlet di Bawah Kepemilikannya</span>
                    <span class="badge bg-warning text-dark rounded-pill px-3">${totalOutlet} Outlet</span>
                </div>
                <div class="d-flex align-items-center mb-2 pb-2 border-bottom">
                    <i class="fa fa-money text-danger me-2 fs-16"></i>
                    <span class="text-dark">Riwayat Laporan Omzet & Rekap Bagi Hasil</span>
                </div>
                <div class="d-flex align-items-center">
                    <i class="fa fa-user-times text-danger me-2 fs-16"></i>
                    <span class="text-dark">Akun Kasir Outlet</span>
                </div>
            </div>
            
            <p class="text-danger small mb-0 fw-semibold"><i class="fa fa-exclamation-triangle me-1"></i> Data yang dihapus bersifat permanen dan tidak dapat dikembalikan.</p>
        </div>
    `;

    Swal.fire({
        title: 'Hapus Investor?',
        html: alertHtml,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus Investor',
        cancelButtonText: 'Batal',
        customClass: {
            confirmButton: 'px-4 py-2',
            cancelButton: 'px-4 py-2'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses...',
                text: "Sedang menghapus investor & outlet terkait",
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            });

            $.post("<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/investor/delete", { id_investor: id, id: id }, function(resp) {
                if (resp.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: resp.message,
                        confirmButtonText: 'OK'
                    }).then(function() {
                        location.reload();
                    });
                } else {
                    Swal.fire('Gagal!', resp.message || 'Gagal menghapus data investor', 'error');
                }
            }, 'json').fail(function() {
                Swal.fire('Error!', 'Terjadi kesalahan sistem (Server Error). Silakan muat ulang halaman.', 'error');
            });
        }
    });
}
</script>
