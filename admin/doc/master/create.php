<?php
use App\Models\Master;
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();

$idMaster = isset($_GET['id']) ? intval($_GET['id']) : (isset($_GET['c']) ? intval($_GET['c']) : 0);
$isEdit   = ($idMaster > 0);
$masterData = null;

if ($isEdit) {
    if (!$adminPermissionCore->isHavePermission($moduleId, "update")) {
        $redirectUrl = SystemInfo::app('ADMIN_URL') . '/master/view';
        die("<script>location.href = '{$redirectUrl}';</script>");
    }
    $masterData = Master::getMasterById($idMaster);
    if (!$masterData) {
        $redirectUrl = SystemInfo::app('ADMIN_URL') . '/master/view';
        die("<script>alert('Data Master tidak ditemukan!'); location.href = '{$redirectUrl}';</script>");
    }
} else {
    if (!$adminPermissionCore->isHavePermission($moduleId, "create")) {
        $redirectUrl = SystemInfo::app('ADMIN_URL') . '/master/view';
        die("<script>location.href = '{$redirectUrl}';</script>");
    }
}
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5"><?= $isEdit ? "Edit Data Master" : "Registrasi Master Baru"; ?></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/master/view">Master</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $isEdit ? "Edit Data" : "Registrasi"; ?></li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-md-10 mx-auto mb-3">
        <div class="card custom-card overflow-hidden">
            <div class="card-header">
                <div class="d-flex justify-content-between mb-2">
                    <h5 class="card-title"><?= $isEdit ? "Form Edit Data Master" : "Form Registrasi Master"; ?></h5>
                </div>
            </div>
            <div class="card-body">
                <form action="" method="post" id="form-create-master">
                    <?php if ($isEdit) : ?>
                        <input type="hidden" name="id_users" value="<?= $idMaster; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <!-- BARIS 1: NAMA LENGKAP & NO. HP -->
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="nama_lengkap" class="form-label fw-bold">Nama Lengkap Master <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" placeholder="Contoh: Haji Ahmad Madura" value="<?= htmlspecialchars($masterData['nama_lengkap'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="no_hp" class="form-label fw-bold">No. HP / WhatsApp <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="no_hp" name="no_hp" placeholder="Contoh: 081234567890" value="<?= htmlspecialchars($masterData['no_hp'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <!-- BARIS 2: KECAMATAN & ALAMAT LENGKAP -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Wilayah<span class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <select class="form-select wilayah-select" id="provinsi" data-placeholder="Pilih Provinsi" required>
                                        <option value=""></option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <select class="form-select wilayah-select" id="kabupaten" data-placeholder="Pilih Kabupaten" required disabled>
                                        <option value=""></option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <select class="form-select wilayah-select" id="kecamatan" data-placeholder="Pilih Kecamatan" required disabled>
                                        <option value=""></option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <select class="form-select wilayah-select" id="id_wilayah" name="id_wilayah" data-placeholder="Pilih Kelurahan" required disabled>
                                        <option value=""></option>
                                        <?php if (!empty($masterData['id_wilayah'])) : ?>
                                            <option value="<?= htmlspecialchars($masterData['id_wilayah']); ?>" selected>
                                                <?= htmlspecialchars(ucwords(strtolower($masterData['kelurahan'] ?? 'Wilayah Terpilih'))); ?>
                                            </option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="alamat" class="form-label fw-bold">Alamat Lengkap Master <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3" placeholder="Contoh: Jl. Trunojoyo No. 10, RT 02 / RW 05, Bangkalan" required><?= htmlspecialchars($masterData['alamat_lengkap'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <!-- BARIS 3: USERNAME & PASSWORD -->
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="username" class="form-label fw-bold">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Contoh: master_ahmad" value="<?= htmlspecialchars($masterData['username'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="password" class="form-label fw-bold">Password <?= $isEdit ? '(Opsional)' : '<span class="text-danger">*</span>'; ?></label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="<?= $isEdit ? 'Biarkan kosong jika tidak diubah' : 'Masukkan password login'; ?>" <?= $isEdit ? "" : "required"; ?>>
                                <small class="text-muted d-block mt-1">Password minimal 8 karakter, kombinasi huruf besar (A-Z), huruf kecil (a-z), dan angka (0-9).</small>
                            </div>
                        </div>

                        <div class="col-md-12 mt-3 d-flex justify-content-end gap-2">
                            <a href="<?= SystemInfo::app('ADMIN_URL') ?>/master/view" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary" data-original-text="Submit"><?= $isEdit ? "Simpan Perubahan" : "Simpan Master"; ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var isEdit = <?= $isEdit ? 'true' : 'false' ?>;
    var edit_provinsi = "<?= $isEdit ? ($masterData['provinsi'] ?? '') : '' ?>";
    var edit_kabupaten = "<?= $isEdit ? ($masterData['kabupaten'] ?? '') : '' ?>";
    var edit_kecamatan = "<?= $isEdit ? ($masterData['kecamatan'] ?? '') : '' ?>";
    var edit_id_wilayah = "<?= $isEdit ? ($masterData['id_wilayah'] ?? '') : '' ?>";

    $(document).ready(function() {

        const adminUrl = "<?= SystemInfo::app('ADMIN_URL') ?>";

        // Inisialisasi Select2 manual untuk dropdown wilayah
        function initWilayahSelect2(selector) {
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

        let isInitialEditCascade = isEdit;

        function openNextSelect2(selector) {
            setTimeout(() => {
                let $el = $(selector);
                if ($el.length && !$el.prop('disabled')) {
                    $el.select2('open');
                }
            }, 120);
        }

        $('.wilayah-select').on('select2:close', function() {
            let $container = $(this).next('.select2-container');
            $container.find('.select2-selection').blur();
        });

        $(document).on('select2:open', function() {
            setTimeout(() => {
                let searchField = document.querySelector('.select2-container--open .select2-search__field');
                if (searchField) {
                    searchField.focus();
                }
            }, 50);
        });

        // Inisialisasi semua 4 dropdown wilayah sejak awal
        initWilayahSelect2('#provinsi');
        initWilayahSelect2('#kabupaten');
        initWilayahSelect2('#kecamatan');
        initWilayahSelect2('#id_wilayah');

        // Auto focus ke input pertama saat halaman dimuat
        setTimeout(() => {
            $('#nama_lengkap').focus();
        }, 150);

        function loadProvinsi() {
            $.post(adminUrl + "/ajax/post/wilayah/get_provinsi", function(res) {
                let options = '<option value=""></option>';
                if (res.results) {
                    res.results.forEach(item => {
                        let selected = (item.id === edit_provinsi) ? 'selected' : '';
                        options += `<option value="${item.id}" ${selected}>${item.text}</option>`;
                    });
                }
                $('#provinsi').html(options).prop('disabled', false);
                initWilayahSelect2('#provinsi');
                if (isEdit && edit_provinsi) {
                    $('#provinsi').trigger('change');
                }
            });
        }

        // Navigasi saat data TETAP dipilih (tidak berubah)
        $('#provinsi').on('select2:select', function() {
            setTimeout(() => {
                if ($('#kabupaten option').length > 1 && !$('#kabupaten').prop('disabled')) {
                    openNextSelect2('#kabupaten');
                }
            }, 150);
        });

        $('#kabupaten').on('select2:select', function() {
            setTimeout(() => {
                if ($('#kecamatan option').length > 1 && !$('#kecamatan').prop('disabled')) {
                    openNextSelect2('#kecamatan');
                }
            }, 150);
        });

        $('#kecamatan').on('select2:select', function() {
            setTimeout(() => {
                if ($('#id_wilayah option').length > 1 && !$('#id_wilayah').prop('disabled')) {
                    openNextSelect2('#id_wilayah');
                }
            }, 150);
        });

        $('#id_wilayah').on('select2:select', function() {
            setTimeout(() => {
                $('#alamat').focus();
            }, 150);
        });

        // Load Kabupaten
        $('#provinsi').on('change', function() {
            let prov = $(this).val();
            $('#kabupaten').html('<option value=""></option>').prop('disabled', true);
            initWilayahSelect2('#kabupaten');
            $('#kecamatan').html('<option value=""></option>').prop('disabled', true);
            initWilayahSelect2('#kecamatan');
            $('#id_wilayah').html('<option value=""></option>').prop('disabled', true);
            initWilayahSelect2('#id_wilayah');
            
            if (prov) {
                $.post(adminUrl + "/ajax/post/wilayah/get_kabupaten", { provinsi: prov }, function(res) {
                    let options = '<option value=""></option>';
                    if (res.results) {
                        res.results.forEach(item => {
                            let selected = (item.id === edit_kabupaten) ? 'selected' : '';
                            options += `<option value="${item.id}" ${selected}>${item.text}</option>`;
                        });
                    }
                    $('#kabupaten').html(options).prop('disabled', false);
                    initWilayahSelect2('#kabupaten');
                    if (isInitialEditCascade && edit_kabupaten) {
                        $('#kabupaten').trigger('change');
                        edit_provinsi = "";
                    } else {
                        openNextSelect2('#kabupaten');
                    }
                });
            }
        });

        // Load Kecamatan
        $('#kabupaten').on('change', function() {
            let prov = $('#provinsi').val();
            let kab = $(this).val();
            $('#kecamatan').html('<option value=""></option>').prop('disabled', true);
            initWilayahSelect2('#kecamatan');
            $('#id_wilayah').html('<option value=""></option>').prop('disabled', true);
            initWilayahSelect2('#id_wilayah');

            if (kab) {
                $.post(adminUrl + "/ajax/post/wilayah/get_kecamatan", { provinsi: prov, kabupaten: kab }, function(res) {
                    let options = '<option value=""></option>';
                    if (res.results) {
                        res.results.forEach(item => {
                            let selected = (item.id === edit_kecamatan) ? 'selected' : '';
                            options += `<option value="${item.id}" ${selected}>${item.text}</option>`;
                        });
                    }
                    $('#kecamatan').html(options).prop('disabled', false);
                    initWilayahSelect2('#kecamatan');
                    if (isInitialEditCascade && edit_kecamatan) {
                        $('#kecamatan').trigger('change');
                        edit_kabupaten = "";
                    } else {
                        openNextSelect2('#kecamatan');
                    }
                });
            }
        });

        // Load Kelurahan
        $('#kecamatan').on('change', function() {
            let prov = $('#provinsi').val();
            let kab = $('#kabupaten').val();
            let kec = $(this).val();
            $('#id_wilayah').html('<option value=""></option>').prop('disabled', true);
            initWilayahSelect2('#id_wilayah');

            if (kec) {
                $.post(adminUrl + "/ajax/post/wilayah/get_kelurahan", { provinsi: prov, kabupaten: kab, kecamatan: kec }, function(res) {
                    let options = '<option value=""></option>';
                    if (res.results) {
                        res.results.forEach(item => {
                            let selected = (item.id === edit_id_wilayah) ? 'selected' : '';
                            options += `<option value="${item.id}" ${selected}>${item.text}</option>`;
                        });
                    }
                    $('#id_wilayah').html(options).prop('disabled', false);
                    initWilayahSelect2('#id_wilayah');
                    if (isInitialEditCascade && edit_id_wilayah) {
                        edit_kecamatan = "";
                        edit_id_wilayah = "";
                        isInitialEditCascade = false;
                        setTimeout(() => {
                            $('#nama_lengkap').focus();
                        }, 100);
                    } else {
                        openNextSelect2('#id_wilayah');
                    }
                });
            }
        });

        // Navigasi Enter antar input teks
        $('#nama_lengkap').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $('#no_hp').focus();
            }
        });

        $('#no_hp').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                openNextSelect2('#provinsi');
            }
        });

        $('#username').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $('#password').focus();
            }
        });

        // Initialize form
        loadProvinsi();

        $('#form-create-master').on('submit', function(el) {
            el.preventDefault();
            let button = $(this).find('button[type="submit"]'), 
                data = $(this).serialize();
                
            button.addClass('loading').prop('disabled', true);
            $.post("<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/master/create", data, (resp) => {
                button.removeClass('loading').prop('disabled', false);
                if (resp.success) {
                    let isEdit = $('input[name="id_users"]').val() ? true : false;
                    let defaultSuccessMsg = isEdit ? 'Data master berhasil diperbarui.' : 'Data master berhasil ditambahkan.';
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: resp.message || defaultSuccessMsg,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.href = resp.data?.redirect || "<?= SystemInfo::app('ADMIN_URL') ?>/master/view";
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Perhatian!',
                        text: resp.message || 'Gagal menyimpan data master.'
                    });
                }
            }, 'json').fail(function(xhr) {
                button.removeClass('loading').prop('disabled', false);
                let errorMsg = 'Terjadi kendala pada server (atau sesi Anda habis). Silakan coba lagi.';
                if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Perhatian!',
                    text: errorMsg
                });
            });
        });
    });
</script>
