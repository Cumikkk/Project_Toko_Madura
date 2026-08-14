<?php
use App\Models\Investor;
use App\Models\Master;
use Config\Core\SystemInfo;

$idInvestor = intval($_GET['id'] ?? ($_GET['c'] ?? 0));
$isEdit = ($idInvestor > 0);

$investorData = null;
if ($isEdit) {
    $investorData = Investor::getInvestorById($idInvestor);
    if (!$investorData) {
        $isEdit = false;
        $idInvestor = 0;
    }
}

$requiredPermission = $isEdit ? "update" : "create";
if (!$adminPermissionCore->isHavePermission($moduleId, $requiredPermission)) {
    $redirectUrl = SystemInfo::app('ADMIN_URL') . '/investor/view';
    die("<script>location.href = '{$redirectUrl}'; </script>");
}

// Fetch list of Master Owners
$masterList = Master::getAllMasterOptions();
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5"><?= $isEdit ? "Edit Data Investor" : "Registrasi Investor Baru"; ?></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/investor/view">Investor</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $isEdit ? "Edit Data" : "Registrasi"; ?></li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-md-10 mx-auto mb-3">
        <div class="card custom-card overflow-hidden">
            <div class="card-header">
                <div class="d-flex justify-content-between mb-2">
                    <h5 class="card-title"><?= $isEdit ? "Form Edit Data Investor" : "Form Registrasi Investor"; ?></h5>
                </div>
            </div>
            <div class="card-body">
                <form action="" method="post" id="form-create-investor">
                    <?php if ($isEdit) : ?>
                        <input type="hidden" name="id_investor" value="<?= $idInvestor; ?>">
                    <?php endif; ?>

                    <div class="row">
                        <!-- BARIS 1: NAMA LENGKAP & NO. HP -->
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="nama_lengkap" class="form-label fw-bold">Nama Lengkap Investor <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" placeholder="Contoh: Haji Ahmad Madura" value="<?= htmlspecialchars($investorData['nama_lengkap'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="no_hp" class="form-label fw-bold">No. HP / WhatsApp <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="no_hp" name="no_hp" placeholder="Contoh: 081234567890" value="<?= htmlspecialchars($investorData['no_hp'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <!-- BARIS 2: USERNAME & PASSWORD -->
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="username" class="form-label fw-bold">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Contoh: investor_ahmad" value="<?= htmlspecialchars($investorData['username'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="password" class="form-label fw-bold">Password <?= $isEdit ? '(Opsional)' : '<span class="text-danger">*</span>'; ?></label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="<?= $isEdit ? 'Biarkan kosong jika tidak diubah' : 'Masukkan password login'; ?>" <?= $isEdit ? "" : "required"; ?>>
                                <small class="text-muted d-block mt-1">Password minimal 8 karakter, kombinasi huruf besar (A-Z), huruf kecil (a-z), dan angka (0-9).</small>
                            </div>
                        </div>

                        <!-- BARIS 3: MASTER OWNER & BIAYA LANGGANAN -->
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="id_master" class="form-label fw-bold">Master Owner <span class="text-danger">*</span></label>
                                <select class="form-control" id="id_master" name="id_master" required>
                                    <option value="" disabled <?= empty($investorData['id_master']) ? 'selected' : ''; ?>>-- Pilih Master Owner --</option>
                                    <?php if ($masterList && $masterList->num_rows > 0) : ?>
                                        <?php while ($m = $masterList->fetch_assoc()) : ?>
                                            <option value="<?= $m['id_users']; ?>" <?= (($investorData['id_master'] ?? 0) == $m['id_users']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($m['nama_lengkap']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                                <small class="text-muted">Pilih Master Owner tempat investor ini dinaungi.</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="biaya_langganan_outlet" class="form-label fw-bold">Nominal Biaya Langganan Outlet (Rp) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text border-end-0">Rp</span>
                                    <input type="number" step="10000" min="0" class="form-control fw-bold border-start-0 border-end-0" id="biaya_langganan_outlet" name="biaya_langganan_outlet" placeholder="100000" value="<?= (int)($investorData['biaya_langganan_outlet'] ?? 100000); ?>" required>
                                    <div class="input-group-text p-0 border-start-0 overflow-hidden bg-body-tertiary">
                                        <div class="d-flex flex-column h-100" style="width: 24px;">
                                            <button type="button" class="btn btn-sm btn-light border-0 rounded-0 py-0 px-1 text-body-secondary flex-fill d-flex align-items-center justify-content-center" onclick="stepBiayaInvestor(25000)" style="font-size: 10px; line-height: 1; padding: 2px;" title="Tambah (+Rp 25.000)">
                                                <i class="fas fa-chevron-up"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border-0 border-top rounded-0 py-0 px-1 text-body-secondary flex-fill d-flex align-items-center justify-content-center" onclick="stepBiayaInvestor(-25000)" style="font-size: 10px; line-height: 1; padding: 2px;" title="Kurangi (-Rp 25.000)">
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted">Tarif ini berlaku sebagai biaya langganan bulanan seluruh outlet investor ini.</small>
                            </div>
                        </div>

                        <!-- BARIS 4: KECAMATAN & ALAMAT LENGKAP -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Wilayah / Desa <span class="text-danger">*</span></label>
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
                                        <?php if (!empty($investorData['id_wilayah'])) : ?>
                                            <option value="<?= htmlspecialchars($investorData['id_wilayah']); ?>" selected>
                                                <?= htmlspecialchars(ucwords(strtolower($investorData['kelurahan'] ?? 'Wilayah Terpilih'))); ?>
                                            </option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="alamat_lengkap" class="form-label fw-bold">Alamat Lengkap Investor <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="alamat_lengkap" name="alamat_lengkap" rows="3" placeholder="Contoh: Jl. Raya Waru No. 123, RT 02 / RW 05, Sidoarjo" required><?= htmlspecialchars($investorData['alamat_investor'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="col-md-12 mt-3 d-flex justify-content-end gap-2">
                            <a href="<?= SystemInfo::app('ADMIN_URL') ?>/investor/view" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary" data-original-text="Submit"><?= $isEdit ? "Simpan Perubahan" : "Simpan Investor"; ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function stepBiayaInvestor(amount) {
        let input = $('#biaya_langganan_outlet');
        let val = parseFloat(input.val()) || 0;
        let nextVal = Math.max(0, val + amount);
        input.val(nextVal);
    }

    var isEdit = <?= $isEdit ? 'true' : 'false' ?>;
    var edit_provinsi = "<?= $isEdit ? ($investorData['provinsi'] ?? '') : '' ?>";
    var edit_kabupaten = "<?= $isEdit ? ($investorData['kabupaten'] ?? '') : '' ?>";
    var edit_kecamatan = "<?= $isEdit ? ($investorData['kecamatan'] ?? '') : '' ?>";
    var edit_id_wilayah = "<?= $isEdit ? ($investorData['id_wilayah'] ?? '') : '' ?>";

    $(document).ready(function() {
        if ($.fn.select2) {
            $('#id_master').select2({ width: '100%' });
        }

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

        function openNextSelect2(selector) {
            setTimeout(() => {
                let $el = $(selector);
                $el.select2('open');
                let searchField = document.querySelector('.select2-container--open .select2-search__field');
                if (searchField) {
                    searchField.focus();
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
            }, 10);
        });

        // Inisialisasi semua 4 dropdown wilayah sejak awal
        initWilayahSelect2('#provinsi');
        initWilayahSelect2('#kabupaten');
        initWilayahSelect2('#kecamatan');
        initWilayahSelect2('#id_wilayah');

        // Load Provinsi
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
                    if (isEdit && edit_kabupaten) {
                        $('#kabupaten').trigger('change');
                        edit_provinsi = "";
                    } else if (!isEdit || !edit_kabupaten) {
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
                    if (isEdit && edit_kecamatan) {
                        $('#kecamatan').trigger('change');
                        edit_kabupaten = "";
                    } else if (!isEdit || !edit_kecamatan) {
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
                    if (isEdit && edit_id_wilayah) {
                        edit_kecamatan = "";
                        edit_id_wilayah = "";
                    } else if (!isEdit || !edit_id_wilayah) {
                        openNextSelect2('#id_wilayah');
                    }
                });
            }
        });

        // Initialize form
        loadProvinsi();

        $('#form-create-investor').on('submit', function(el) {
            el.preventDefault();
            let button = $(this).find('button[type="submit"]'),
                data = $(this).serialize();

            button.addClass('loading').prop('disabled', true);
            $.post("<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/investor/create", data, (resp) => {
                button.removeClass('loading').prop('disabled', false);
                if (resp.success) {
                    let isEdit = $('input[name="id_investor"]').val() ? true : false;
                    let defaultSuccessMsg = isEdit ? 'Data investor berhasil diperbarui.' : 'Data investor berhasil ditambahkan.';
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: resp.message || defaultSuccessMsg,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.href = resp.data?.redirect || "<?= SystemInfo::app('ADMIN_URL') ?>/investor/view";
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Perhatian!',
                        text: resp.message || 'Gagal menyimpan data investor.'
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