<?php
use Config\Core\SystemInfo;
use App\Models\Master;

// Fetch Master list with sub-counts
$masters = Master::getAllMasters();
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Daftar Master Toko Madura</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item">Master</li>
            <li class="breadcrumb-item active" aria-current="page">Data Master</li>
        </ol>
    </div>
</div>

<!-- Filter Card -->
<div class="row row-sm mb-3">
    <div class="col-lg-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="d-flex justify-content-between mb-2">
                    <h5 class="card-title">Filter Data Wilayah</h5>
                    <button type="button" id="btnResetFilter" class="btn btn-secondary btn-sm" title="Reset semua filter wilayah">
                        <i class="fe fe-refresh-cw me-1"></i> Reset Filter
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label fw-bold">Filter Provinsi</label>
                        <select id="filterProvinsi" class="form-select filter-select" data-placeholder="Semua Provinsi">
                            <option value="">Semua Provinsi</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label fw-bold">Filter Kabupaten / Kota</label>
                        <select id="filterKabupaten" class="form-select filter-select" data-placeholder="Semua Kabupaten" disabled>
                            <option value="">Semua Kabupaten</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label fw-bold">Filter Kecamatan</label>
                        <select id="filterKecamatan" class="form-select filter-select" data-placeholder="Semua Kecamatan" disabled>
                            <option value="">Semua Kecamatan</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label fw-bold">Filter Kelurahan / Desa</label>
                        <select id="filterKelurahan" class="form-select filter-select" data-placeholder="Semua Kelurahan" disabled>
                            <option value="">Semua Kelurahan</option>
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
                    <h5 class="card-title">List Master</h5>
                    <?php if($adminPermissionCore->isHavePermission($moduleId, "create")) : ?>
                        <a href="<?= SystemInfo::app('ADMIN_URL') ?>/master/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Tambah Master</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100 align-middle" id="table-master">
                        <thead>
                            <tr class="text-center">
                                <th class="text-center" style="width: 5%;">No</th>
                                <th class="text-center">Tanggal Bergabung</th>
                                <th class="text-center">Nama Master</th>
                                <th class="text-center">No. HP</th>
                                <th class="text-center">Wilayah</th>
                                <th class="text-center">Total Investor</th>
                                <th class="text-center">Total Outlet Aktif</th>
                                <th class="text-center" style="width: 15%;">#</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($masters && $masters->num_rows > 0) : ?>
                                <?php $no = 1; while ($row = $masters->fetch_assoc()) : ?>
                                    <tr data-provinsi="<?= htmlspecialchars(strtoupper($row['provinsi'] ?? '')) ?>" 
                                        data-kabupaten="<?= htmlspecialchars(strtoupper($row['kabupaten'] ?? '')) ?>" 
                                        data-kecamatan="<?= htmlspecialchars(strtoupper($row['kecamatan'] ?? '')) ?>" 
                                        data-kelurahan="<?= htmlspecialchars(strtoupper($row['kelurahan'] ?? '')) ?>">
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td class="text-center"><?= !empty($row['created_at']) ? date("d/m/Y H:i", strtotime($row['created_at'])) : '-' ?></td>
                                        <td class="text-start">
                                            <strong class="text-primary"><?= htmlspecialchars($row['nama_lengkap']) ?></strong>
                                            <br><small class="text-muted"><code>@<?= htmlspecialchars($row['username']) ?></code></small>
                                        </td>
                                        <td class="text-center"><?= htmlspecialchars($row['no_hp'] ?? '-') ?></td>
                                        <td class="text-center">
                                            <?php if (!empty($row['kecamatan']) && $row['kecamatan'] !== '-') : ?>
                                                <?php if (!empty($row['alamat'])) : ?>
                                                    <span class="badge bg-light text-dark border btn-lihat-alamat shadow-xs py-1.5 px-2.5" style="cursor: pointer; font-size: 13px; font-weight: 500;" 
                                                          data-nama="<?= htmlspecialchars($row['nama_lengkap']) ?>" 
                                                          data-alamat="<?= htmlspecialchars($row['alamat']) ?>" 
                                                          data-provinsi="<?= htmlspecialchars($row['provinsi'] ?? '') ?>"
                                                          data-kabupaten="<?= htmlspecialchars($row['kabupaten'] ?? '') ?>"
                                                          data-kecamatan="<?= htmlspecialchars($row['kecamatan'] ?? '') ?>"
                                                          data-kelurahan="<?= htmlspecialchars($row['kelurahan'] ?? '') ?>"
                                                          title="Klik untuk lihat detail alamat">
                                                        <i class="fa fa-map-marker text-danger me-1"></i>Kel. <?= htmlspecialchars(ucwords(strtolower($row['kelurahan'] ?? ''))) ?>, Kec. <?= htmlspecialchars(ucwords(strtolower($row['kecamatan'] ?? ''))) ?>, Kab. <?= htmlspecialchars(ucwords(strtolower($row['kabupaten'] ?? ''))) ?>, Prov. <?= htmlspecialchars(ucwords(strtolower($row['provinsi'] ?? ''))) ?>
                                                    </span>
                                                <?php else : ?>
                                                    <span class="text-muted" style="font-size: 13px;"><i class="fa fa-map-marker me-1"></i>Kel. <?= htmlspecialchars(ucwords(strtolower($row['kelurahan'] ?? ''))) ?>, Kec. <?= htmlspecialchars(ucwords(strtolower($row['kecamatan'] ?? ''))) ?>, Kab. <?= htmlspecialchars(ucwords(strtolower($row['kabupaten'] ?? ''))) ?>, Prov. <?= htmlspecialchars(ucwords(strtolower($row['provinsi'] ?? ''))) ?></span>
                                                <?php endif; ?>
                                            <?php else : ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (!empty($row['total_investor']) && $row['total_investor'] > 0) : ?>
                                                <span class="badge bg-info fs-6"><?= number_format($row['total_investor']) ?> Investor</span>
                                            <?php else : ?>
                                                <span class="text-muted fs-6">-</span>
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
                                                    <a href="<?= SystemInfo::app('ADMIN_URL') ?>/master/create?id=<?= $row['id_users'] ?>" class="btn btn-success btn-sm text-white btn-edit" title="Edit Master"><i class="fas fa-edit"></i></a>
                                                <?php endif; ?>
                                                <?php if($adminPermissionCore->isHavePermission($moduleId, "delete")) : ?>
                                                    <button type="button" class="btn btn-danger btn-sm text-white btn-delete" title="Hapus Master" onclick="deleteMaster(<?= $row['id_users'] ?>, '<?= htmlspecialchars($row['nama_lengkap'], ENT_QUOTES, 'UTF-8') ?>', <?= intval($row['total_investor'] ?? 0) ?>, <?= intval($row['total_outlet'] ?? 0) ?>)"><i class="fas fa-trash"></i></button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Belum ada data Master terdaftar.</td>
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
    const adminUrl = "<?= SystemInfo::app('ADMIN_URL') ?>";

    // Modal popup detail alamat master
    $(document).on('click', '.btn-lihat-alamat', function() {
        let nama = $(this).data('nama');
        let alamat = $(this).data('alamat');
        let provinsi = $(this).data('provinsi');
        let kabupaten = $(this).data('kabupaten');
        let kecamatan = $(this).data('kecamatan');
        let kelurahan = $(this).data('kelurahan');
        
        let queryStr = encodeURIComponent(alamat);
        let mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' + queryStr;
        
        Swal.fire({
            title: 'Alamat Lengkap Master Owner',
            html: '<p class="text-start mb-2"><strong>Master Owner:</strong> ' + nama + '</p>' +
                  '<p class="text-start mb-2"><strong>Wilayah:</strong> <span class="text-capitalize">Kel. ' + kelurahan.toLowerCase() + ', Kec. ' + kecamatan.toLowerCase() + ', Kab. ' + kabupaten.toLowerCase() + ', Prov. ' + provinsi.toLowerCase() + '</span></p>' +
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

    // Inisialisasi Select2 untuk Filter Wilayah
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

    // Inisialisasi ke-4 dropdown filter sejak awal
    initFilterSelect2('#filterProvinsi');
    initFilterSelect2('#filterKabupaten');
    initFilterSelect2('#filterKecamatan');
    initFilterSelect2('#filterKelurahan');

    var tableMaster = null;
    if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#table-master')) {
        tableMaster = $('#table-master').DataTable({
            processing: true,
            deferRender: true,
            scrollX: true,
            lengthMenu: [[10, 50, 100, -1], [10, 50, 100, "All"]],
            language: {
                searchPlaceholder: 'Cari master...',
                sSearch: '',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                paginate: { first: 'First', last: 'Last', next: 'Next', previous: 'Previous' }
            },
            order: [[1, 'desc']]
        });
    }

    // Load filter Provinsi
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

    // Custom filtering function for DataTable
    $.fn.dataTable.ext.search.push(function(settings, searchData, index) {
        if (!settings.nTable || settings.nTable.id !== 'table-master') {
            return true;
        }
        if (!tableMaster) {
            return true;
        }
        let $row = $(tableMaster.row(index).node());
        let rowProv = ($row.attr('data-provinsi') || '').toUpperCase().trim();
        let rowKab = ($row.attr('data-kabupaten') || '').toUpperCase().trim();
        let rowKec = ($row.attr('data-kecamatan') || '').toUpperCase().trim();
        let rowKel = ($row.attr('data-kelurahan') || '').toUpperCase().trim();
        
        let filterProv = ($('#filterProvinsi').val() || '').toUpperCase().trim();
        let filterKab = ($('#filterKabupaten').val() || '').toUpperCase().trim();
        let filterKec = ($('#filterKecamatan').val() || '').toUpperCase().trim();
        let filterKel = ($('#filterKelurahan').val() || '').toUpperCase().trim();
        
        if (filterProv && rowProv !== filterProv) {
            return false;
        }
        if (filterKab && rowKab !== filterKab) {
            return false;
        }
        if (filterKec && rowKec !== filterKec) {
            return false;
        }
        if (filterKel && rowKel !== filterKel) {
            return false;
        }
        return true;
    });

    // Event filter Provinsi
    $('#filterProvinsi').on('change', function() {
        let prov = $(this).val();
        $('#filterKabupaten').html('<option value="">Semua Kabupaten</option>').prop('disabled', true);
        initFilterSelect2('#filterKabupaten');
        $('#filterKecamatan').html('<option value="">Semua Kecamatan</option>').prop('disabled', true);
        initFilterSelect2('#filterKecamatan');
        $('#filterKelurahan').html('<option value="">Semua Kelurahan</option>').prop('disabled', true);
        initFilterSelect2('#filterKelurahan');
        
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
        if (tableMaster) {
            tableMaster.draw();
        }
    });

    // Event filter Kabupaten
    $('#filterKabupaten').on('change', function() {
        let prov = $('#filterProvinsi').val();
        let kab = $(this).val();
        $('#filterKecamatan').html('<option value="">Semua Kecamatan</option>').prop('disabled', true);
        initFilterSelect2('#filterKecamatan');
        $('#filterKelurahan').html('<option value="">Semua Kelurahan</option>').prop('disabled', true);
        initFilterSelect2('#filterKelurahan');

        if (kab) {
            $.post(adminUrl + "/ajax/post/wilayah/get_kecamatan", { provinsi: prov, kabupaten: kab }, function(res) {
                let options = '<option value="">Semua Kecamatan</option>';
                if (res.results) {
                    res.results.forEach(item => {
                        options += `<option value="${item.id}">${item.text}</option>`;
                    });
                }
                $('#filterKecamatan').html(options).prop('disabled', false);
                initFilterSelect2('#filterKecamatan');
                openNextFilterSelect2('#filterKecamatan');
            });
        }
        if (tableMaster) {
            tableMaster.draw();
        }
    });

    // Event filter Kecamatan
    $('#filterKecamatan').on('change', function() {
        let prov = $('#filterProvinsi').val();
        let kab = $('#filterKabupaten').val();
        let kec = $(this).val();
        $('#filterKelurahan').html('<option value="">Semua Kelurahan</option>').prop('disabled', true);
        initFilterSelect2('#filterKelurahan');

        if (kec) {
            $.post(adminUrl + "/ajax/post/wilayah/get_kelurahan", { provinsi: prov, kabupaten: kab, kecamatan: kec }, function(res) {
                let options = '<option value="">Semua Kelurahan</option>';
                if (res.results) {
                    res.results.forEach(item => {
                        let val = item.kelurahan || item.id;
                        options += `<option value="${val}">${item.text}</option>`;
                    });
                }
                $('#filterKelurahan').html(options).prop('disabled', false);
                initFilterSelect2('#filterKelurahan');
                openNextFilterSelect2('#filterKelurahan');
            });
        }
        if (tableMaster) {
            tableMaster.draw();
        }
    });

    // Event filter Kelurahan
    $('#filterKelurahan').on('change', function() {
        if (tableMaster) {
            tableMaster.draw();
        }
    });

    // Reset filter
    $('#btnResetFilter').on('click', function() {
        $('#filterProvinsi').val('').trigger('change.select2');
        $('#filterKabupaten').html('<option value="">Semua Kabupaten</option>').prop('disabled', true).val('').trigger('change.select2');
        $('#filterKecamatan').html('<option value="">Semua Kecamatan</option>').prop('disabled', true).val('').trigger('change.select2');
        $('#filterKelurahan').html('<option value="">Semua Kelurahan</option>').prop('disabled', true).val('').trigger('change.select2');
        
        initFilterSelect2('#filterProvinsi');
        initFilterSelect2('#filterKabupaten');
        initFilterSelect2('#filterKecamatan');
        initFilterSelect2('#filterKelurahan');
        
        if (tableMaster) {
            tableMaster.draw();
        }
    });
});

function deleteMaster(id, nama, totalInvestor, totalOutlet) {
    let alertHtml = `
        <div class="text-start fs-14">
            <p class="text-muted mb-3">Tindakan ini akan menghapus akun Master <strong class="text-dark">${nama}</strong> beserta seluruh data yang terikat di bawahnya:</p>
            
            <div class="bg-light p-3 rounded-3 border mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                    <span class="text-dark"><i class="fa fa-user-circle text-info me-2 fs-16"></i>Akun Master (${nama})</span>
                    <span class="badge bg-info rounded-pill px-3">Master</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                    <span class="text-dark"><i class="fa fa-handshake-o text-primary me-2 fs-16"></i>Akun Investor</span>
                    <span class="badge bg-primary rounded-pill px-3">${totalInvestor} Investor</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                    <span class="text-dark"><i class="fa fa-building text-warning me-2 fs-16"></i>Outlet</span>
                    <span class="badge bg-warning text-dark rounded-pill px-3">${totalOutlet} Outlet</span>
                </div>
                <div class="d-flex align-items-center">
                    <i class="fa fa-money text-danger me-2 fs-16"></i>
                    <span class="text-dark">Seluruh Laporan Omzet & Bagi Hasil</span>
                </div>
            </div>
            
            <p class="text-danger small mb-0 fw-semibold"><i class="fa fa-exclamation-triangle me-1"></i> Data yang dihapus bersifat permanen dan tidak dapat dikembalikan.</p>
        </div>
    `;

    Swal.fire({
        title: 'Hapus Master?',
        html: alertHtml,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus Master',
        cancelButtonText: 'Batal',
        customClass: {
            confirmButton: 'px-4 py-2',
            cancelButton: 'px-4 py-2'
        }
    }).then(function(result) {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses...',
                text: 'Sedang menghapus data Master & data terkait',
                allowOutsideClick: false,
                didOpen: function() { Swal.showLoading(); }
            });

            $.post("<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/master/delete", { id_users: id, id: id }, function(resp) {
                if (resp.success) {
                    Swal.fire('Berhasil!', resp.message, 'success').then(function() { location.reload(); });
                } else {
                    Swal.fire('Gagal!', resp.message || 'Gagal menghapus data master', 'error');
                }
            }, 'json').fail(function() {
                Swal.fire('Error!', 'Terjadi kesalahan sistem (Server Error). Silakan muat ulang halaman.', 'error');
            });
        }
    });
}
</script>
