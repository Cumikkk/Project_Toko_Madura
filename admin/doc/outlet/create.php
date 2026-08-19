<?php
use App\Models\Outlet;
use App\Models\Investor;
use Config\Core\SystemInfo;

$idOutlet = intval($_GET['id'] ?? ($_GET['c'] ?? 0));
$isEdit = ($idOutlet > 0);

$outletData = null;

if ($isEdit) {
    $outletData = Outlet::getOutletById($idOutlet);
    if (!$outletData) {
        $isEdit   = false;
        $idOutlet = 0;
    }
}

// Check if current outlet is expired or non-active/inactive
$isExpiredOrInactive = false;
if ($isEdit && !empty($outletData)) {
    $st = strtolower($outletData['status'] ?? '');
    $jt = $outletData['tgl_jatuh_tempo'] ?? '';
    if ($st === 'inactive' || $st === 'expired') {
        $isExpiredOrInactive = true;
    } elseif (!empty($jt) && strtotime(date('Y-m-d', strtotime($jt))) < strtotime(date('Y-m-d'))) {
        $isExpiredOrInactive = true;
    }
}

$requiredPermission = $isEdit ? "update" : "create";
if (!$adminPermissionCore->isHavePermission($moduleId, $requiredPermission)) {
    $redirectUrl = SystemInfo::app('ADMIN_URL') . '/outlet/view';
    die("<script>location.href = '{$redirectUrl}'; </script>");
}

// Fetch list of Investors
$loggedInLevel = intval($user['ADM_LEVEL'] ?? 1);
$loggedInId    = intval($user['ADM_ID'] ?? 1);
$investorList = Investor::getAllInvestors($loggedInLevel, $loggedInId);
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5"><?= $isEdit ? "Edit Data Outlet" : "Registrasi Outlet Baru"; ?></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/outlet/view">Outlet</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $isEdit ? "Edit Data" : "Registrasi"; ?></li>
        </ol>
    </div>
</div>

<form action="" method="post" id="form-create-outlet">
    <?php if ($isEdit) : ?>
        <input type="hidden" name="id_outlet" value="<?= $idOutlet; ?>">
        <input type="hidden" name="id_users_kasir" value="<?= $outletData['id_users']; ?>">
    <?php endif; ?>

    <div class="row align-items-start">
        <!-- KIRI: INFORMASI OUTLET & KEUANGAN -->
        <div class="col-lg-6 mb-3">
            <div class="card custom-card mb-0">
                <div class="card-header">
                    <div class="d-flex justify-content-between mb-2">
                        <h5 class="card-title">Informasi Outlet</h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- 1. Investor -->
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="id_investor" class="form-label fw-bold">Investor <span class="text-danger">*</span></label>
                                <select class="form-control" id="id_investor" name="id_investor" required>
                                    <option value="" disabled <?= empty($outletData['id_investor']) ? 'selected' : ''; ?>>-- Pilih Investor --</option>
                                    <?php if ($investorList && $investorList->num_rows > 0) : ?>
                                        <?php while ($inv = $investorList->fetch_assoc()) : ?>
                                            <option value="<?= $inv['id_investor']; ?>" 
                                                data-provinsi="<?= htmlspecialchars($inv['provinsi'] ?? ''); ?>"
                                                data-kabupaten="<?= htmlspecialchars($inv['kabupaten'] ?? ''); ?>"
                                                <?= (($outletData['id_investor'] ?? 0) == $inv['id_investor']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($inv['nama_lengkap']); ?> (@<?= htmlspecialchars($inv['username']); ?>)<?= !empty($inv['provinsi']) ? ' - ' . htmlspecialchars($inv['provinsi']) : ''; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                                <small class="text-muted">Pilih investor yang menaungi outlet ini.</small>
                            </div>
                        </div>

                        <!-- 2. Nama Outlet -->
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="nama_outlet" class="form-label fw-bold">Nama Outlet <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_outlet" name="nama_outlet"
                                    placeholder="Contoh: Toko Madura Waru"
                                    value="<?= htmlspecialchars($outletData['nama_outlet'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <!-- 3. Wilayah / Desa -->
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
                                        <?php if (!empty($outletData['id_wilayah'])) : ?>
                                            <option value="<?= htmlspecialchars($outletData['id_wilayah']); ?>" selected>
                                                <?= htmlspecialchars(ucwords(strtolower($outletData['kelurahan'] ?? 'Wilayah Terpilih'))); ?>
                                            </option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <small id="note_wilayah_investor" class="text-muted d-block mt-1" style="font-size: 11.5px;"></small>
                        </div>

                        <!-- 4. Alamat Lengkap -->
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="alamat_outlet" class="form-label fw-bold">Alamat Lengkap Outlet <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="alamat_outlet" name="alamat_outlet" rows="3"
                                    placeholder="Contoh: Jl. Raya Waru No. 123, RT 02 / RW 05, Sidoarjo"><?= htmlspecialchars($outletData['alamat_outlet'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <?php if ($isExpiredOrInactive) : ?>
                            <!-- 5. TANGGAL JATUH TEMPO (PERPANJANGAN MASA LANGGANAN) -->
                            <div class="col-md-12 mb-3">
                                <div class="form-group">
                                    <label for="tgl_jatuh_tempo" class="form-label fw-bold">Tanggal Jatuh Tempo (Masa Langganan Aktif)</label>
                                    <input type="date" class="form-control" id="tgl_jatuh_tempo" name="tgl_jatuh_tempo"
                                        value="<?= htmlspecialchars(!empty($outletData['tgl_jatuh_tempo']) ? date('Y-m-d', strtotime($outletData['tgl_jatuh_tempo'])) : date('Y-m-d', strtotime('+1 month'))); ?>">
                                    <small class="text-muted d-block mt-1">Ubah tanggal ini ke tanggal mendatang untuk memperpanjang masa aktif outlet tanpa perlu membuat akun baru lagi.</small>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- 6. SKEMA BAGI HASIL -->
                        <?php
                        $defaultInvestor = (float)($outletData['persentase_hak_investor'] ?? 50.00);
                        $defaultOutlet = 100.00 - $defaultInvestor;
                        ?>
                        <div class="col-md-4 mb-3">
                            <div class="form-group">
                                <label for="persentase_potongan" class="form-label fw-bold">Potongan Omzet (%) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.5" min="0" max="100" class="form-control fw-bold" id="persentase_potongan" name="persentase_potongan"
                                        placeholder="Contoh: 10.00"
                                        value="<?= htmlspecialchars($outletData['persentase_potongan'] ?? '10.00'); ?>" required>
                                    <span class="input-group-text border-end-0">%</span>
                                    <div class="input-group-text p-0 border-start-0 overflow-hidden bg-body-tertiary">
                                        <div class="d-flex flex-column h-100" style="width: 24px;">
                                            <button type="button" class="btn btn-sm btn-light border-0 rounded-0 py-0 px-1 text-body-secondary flex-fill d-flex align-items-center justify-content-center" onclick="stepAdminPotongan(1)" style="font-size: 10px; line-height: 1; padding: 2px;" title="Tambah (+1%)">
                                                <i class="fas fa-chevron-up"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border-0 border-top rounded-0 py-0 px-1 text-body-secondary flex-fill d-flex align-items-center justify-content-center" onclick="stepAdminPotongan(-1)" style="font-size: 10px; line-height: 1; padding: 2px;" title="Kurangi (-1%)">
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted" style="font-size: 11px;">Potongan harian dari omzet kotor.</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-group">
                                <label for="persentase_hak_investor" class="form-label fw-bold">Hak Investor (%) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.5" min="0" max="100" class="form-control fw-bold" id="persentase_hak_investor" name="persentase_hak_investor"
                                        placeholder="Contoh: 50.00"
                                        value="<?= htmlspecialchars($defaultInvestor); ?>" required oninput="balanceAdminOutletSplit('investor')">
                                    <span class="input-group-text border-end-0">%</span>
                                    <div class="input-group-text p-0 border-start-0 overflow-hidden bg-body-tertiary">
                                        <div class="d-flex flex-column h-100" style="width: 24px;">
                                            <button type="button" class="btn btn-sm btn-light border-0 rounded-0 py-0 px-1 text-body-secondary flex-fill d-flex align-items-center justify-content-center" onclick="stepAdminInvestor(1)" style="font-size: 10px; line-height: 1; padding: 2px;" title="Tambah (+1%)">
                                                <i class="fas fa-chevron-up"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border-0 border-top rounded-0 py-0 px-1 text-body-secondary flex-fill d-flex align-items-center justify-content-center" onclick="stepAdminInvestor(-1)" style="font-size: 10px; line-height: 1; padding: 2px;" title="Kurangi (-1%)">
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted" style="font-size: 11px;">Hak Investor.</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-group">
                                <label for="persen_bagian_outlet" class="form-label fw-bold">Hak Outlet (%) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.5" min="0" max="100" class="form-control fw-bold" id="persen_bagian_outlet" name="persen_bagian_outlet"
                                        placeholder="Contoh: 50.00"
                                        value="<?= htmlspecialchars($defaultOutlet); ?>" required oninput="balanceAdminOutletSplit('outlet')">
                                    <span class="input-group-text border-end-0">%</span>
                                    <div class="input-group-text p-0 border-start-0 overflow-hidden bg-body-tertiary">
                                        <div class="d-flex flex-column h-100" style="width: 24px;">
                                            <button type="button" class="btn btn-sm btn-light border-0 rounded-0 py-0 px-1 text-body-secondary flex-fill d-flex align-items-center justify-content-center" onclick="stepAdminOutlet(1)" style="font-size: 10px; line-height: 1; padding: 2px;" title="Tambah (+1%)">
                                                <i class="fas fa-chevron-up"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border-0 border-top rounded-0 py-0 px-1 text-body-secondary flex-fill d-flex align-items-center justify-content-center" onclick="stepAdminOutlet(-1)" style="font-size: 10px; line-height: 1; padding: 2px;" title="Kurangi (-1%)">
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted" style="font-size: 11px;">Hak outlet.</small>
                            </div>
                        </div>

                        <!-- 7. BUKTI PEMBAYARAN -->
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="bukti_pembayaran" class="form-label fw-bold">Bukti Pembayaran / Kuitansi (Opsional)</label>
                                <input type="file" class="form-control" id="bukti_pembayaran" name="bukti_pembayaran" accept="image/*,.pdf">
                                <small class="text-muted">Upload foto struk/kuitansi pembayaran manual atau transfer (Format: JPG, PNG, WEBP, PDF, Maks 5MB).</small>
                                <?php if (!empty($outletData['bukti_pembayaran'])) : ?>
                                    <?php $fileExt = strtolower(pathinfo($outletData['bukti_pembayaran'], PATHINFO_EXTENSION)); ?>
                                    <div class="mt-2" id="existing-bukti-box">
                                        <span class="text-muted fs-13">Bukti saat ini: </span>
                                        <?php if ($fileExt === 'pdf') : ?>
                                            <a href="<?= SystemInfo::app('ADMIN_URL') ?>/image-proxy.php?file=<?= urlencode($outletData['bukti_pembayaran']) ?>" target="_blank" class="btn btn-xs btn-outline-primary ms-1">
                                                <i class="fas fa-file-pdf me-1"></i> Lihat PDF
                                            </a>
                                        <?php else : ?>
                                            <button type="button" class="btn btn-xs btn-outline-primary ms-1" 
                                                    onclick="previewBuktiOutlet('<?= htmlspecialchars($outletData['bukti_pembayaran'], ENT_QUOTES) ?>', '<?= htmlspecialchars($outletData['nama_outlet'] ?? 'Outlet', ENT_QUOTES) ?>', '<?= htmlspecialchars($outletData['nama_investor'] ?? 'Investor', ENT_QUOTES) ?>')">
                                                <i class="fas fa-image me-1"></i> Lihat Bukti
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KANAN: AKUN PENGELOLA OUTLET (PENDEK & MEMUAT TOMBOL AKSI) -->
        <div class="col-lg-6 mb-3">
            <div class="card custom-card mb-0">
                <div class="card-header">
                    <div class="d-flex justify-content-between mb-2">
                        <h5 class="card-title">Akun Pengelola Outlet</h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="kasir_nama" class="form-label fw-bold">Nama Pengelola Outlet <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="kasir_nama" name="kasir_nama"
                                    placeholder="Contoh: Budi Santoso"
                                    value="<?= htmlspecialchars($outletData['kasir_nama'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="kasir_no_hp" class="form-label fw-bold">No. HP / WhatsApp <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="kasir_no_hp" name="kasir_no_hp"
                                    placeholder="Contoh: 081234567890"
                                    value="<?= htmlspecialchars($outletData['kasir_no_hp'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="kasir_username" class="form-label fw-bold">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="kasir_username" name="kasir_username"
                                    placeholder="Contoh: kasir_waru"
                                    value="<?= htmlspecialchars($outletData['kasir_username'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="kasir_password" class="form-label fw-bold">Password <?= $isEdit ? '(Opsional)' : '<span class="text-danger">*</span>'; ?></label>
                                <input type="password" class="form-control" id="kasir_password" name="kasir_password"
                                    placeholder="<?= $isEdit ? 'Biarkan kosong jika tidak diubah' : 'Masukkan password login'; ?>"
                                    <?= $isEdit ? '' : 'required'; ?>>
                                <small class="text-muted d-block mt-1">Password minimal 8 karakter, kombinasi huruf besar (A-Z), huruf kecil (a-z), dan angka (0-9).</small>
                            </div>
                        </div>

                        <!-- TOMBOL AKSI MENYATU DI KARTU KANAN -->
                        <div class="col-12 mt-2 pt-3 border-top d-flex justify-content-end gap-2">
                            <a href="<?= SystemInfo::app('ADMIN_URL') ?>/outlet/view" class="btn btn-secondary px-4">Batal</a>
                            <button type="submit" class="btn btn-primary px-4" data-original-text="Submit">
                                <i class="fas fa-save me-1"></i> <?= $isEdit ? "Simpan Perubahan" : "Simpan Outlet"; ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">
    var isEdit = <?= $isEdit ? 'true' : 'false' ?>;
    var edit_provinsi = "<?= $isEdit ? ($outletData['provinsi'] ?? '') : '' ?>";
    var edit_kabupaten = "<?= $isEdit ? ($outletData['kabupaten'] ?? '') : '' ?>";
    var edit_kecamatan = "<?= $isEdit ? ($outletData['kecamatan'] ?? '') : '' ?>";
    var edit_id_wilayah = "<?= $isEdit ? ($outletData['id_wilayah'] ?? '') : '' ?>";

    $(document).ready(function() {
        if ($.fn.select2) {
            $('#id_investor').select2({ 
                width: '100%',
                placeholder: '-- Pilih Investor --',
                allowClear: false,
                language: { noResults: function() { return 'Tidak ada investor ditemukan'; } }
            });
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

        $('.wilayah-select, #id_investor').on('select2:close', function() {
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

        // Auto focus awal saat form dimuat
        setTimeout(() => {
            if (!isEdit) {
                $('#id_investor').next('.select2-container').find('.select2-selection').focus();
            } else {
                $('#nama_outlet').focus();
            }
        }, 150);

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
                    }
                });
            }
        });

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

        function applyInvestorProvinsiConstraint(invProv) {
            if (invProv) {
                $('#note_wilayah_investor').html('<i class="fe fe-info me-1 text-primary"></i>Wilayah dibatasi sesuai domisili Investor: <strong class="text-primary">' + invProv + '</strong>');
                $('#provinsi').html(`<option value="${invProv}" selected>${invProv}</option>`).prop('disabled', true);
                initWilayahSelect2('#provinsi');
                $('#provinsi').trigger('change');
            } else {
                $('#note_wilayah_investor').text('');
                loadProvinsi();
            }
        }

        // Initialize form
        let initialInvProv = $('#id_investor option:selected').data('provinsi') || '';
        if (initialInvProv) {
            applyInvestorProvinsiConstraint(initialInvProv);
        } else {
            loadProvinsi();
        }

        // Auto populate Bagi Hasil Investor & Kunci Wilayah when selecting an investor & move focus
        $('#id_investor').on('change select2:select', function(e) {
            let selectedOption = $(this).find('option:selected');
            let persen = selectedOption.data('persen');
            if (persen !== undefined && persen !== '') {
                $('#persentase_hak_investor').val(persen);
                window.balanceAdminOutletSplit('investor');
            }
            let invProv = selectedOption.data('provinsi') || '';
            applyInvestorProvinsiConstraint(invProv);

            if (e.type === 'select2:select') {
                setTimeout(function() {
                    $('#nama_outlet').focus();
                }, 100);
            }
        });

        $('#nama_outlet').on('keydown', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                if ($('#kabupaten option').length > 1 && !$('#kabupaten').prop('disabled')) {
                    openNextSelect2('#kabupaten');
                } else if (!$('#provinsi').prop('disabled')) {
                    openNextSelect2('#provinsi');
                } else {
                    openNextSelect2('#kabupaten');
                }
            }
        });

        $('#id_wilayah').on('select2:select', function() {
            setTimeout(function() {
                $('#alamat_outlet').focus();
            }, 100);
        });

        $('#alamat_outlet').on('keydown', function(e) {
            if (e.which === 13 && !e.shiftKey) {
                e.preventDefault();
                if ($('#tgl_jatuh_tempo').length) {
                    $('#tgl_jatuh_tempo').focus();
                } else {
                    $('#persentase_potongan').focus();
                }
            }
        });

        if ($('#tgl_jatuh_tempo').length) {
            $('#tgl_jatuh_tempo').on('keydown', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#persentase_potongan').focus();
                }
            });
        }

        $('#persentase_potongan').on('keydown', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#persentase_hak_investor').focus();
            }
        });

        $('#persentase_hak_investor').on('keydown', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#persen_bagian_outlet').focus();
            }
        });

        $('#persen_bagian_outlet').on('keydown', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#bukti_pembayaran').focus();
            }
        });

        $('#bukti_pembayaran').on('keydown', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#kasir_nama').focus();
            }
        });

        $('#kasir_nama').on('keydown', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#kasir_no_hp').focus();
            }
        });

        $('#kasir_no_hp').on('keydown', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#kasir_username').focus();
            }
        });

        $('#kasir_username').on('keydown', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#kasir_password').focus();
            }
        });

        $('#kasir_password').on('keydown', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#form-create-outlet').trigger('submit');
            }
        });

        window.balanceAdminOutletSplit = function(source) {
            if (source === 'investor') {
                const invVal = parseFloat($('#persentase_hak_investor').val());
                if (!isNaN(invVal)) {
                    const outVal = Math.max(0, 100 - invVal);
                    $('#persen_bagian_outlet').val(outVal.toFixed(2));
                }
            } else {
                const outVal = parseFloat($('#persen_bagian_outlet').val());
                if (!isNaN(outVal)) {
                    const invVal = Math.max(0, 100 - outVal);
                    $('#persentase_hak_investor').val(invVal.toFixed(2));
                }
            }
        };

        window.stepAdminPotongan = function(dir) {
            let el = $('#persentase_potongan');
            let val = parseFloat(el.val()) || 0;
            val = Math.max(0, Math.min(100, val + dir));
            el.val(val.toFixed(2));
        };

        window.stepAdminInvestor = function(dir) {
            let el = $('#persentase_hak_investor');
            let val = parseFloat(el.val()) || 0;
            val = Math.max(0, Math.min(100, val + dir));
            el.val(val.toFixed(2));
            window.balanceAdminOutletSplit('investor');
        };

        window.stepAdminOutlet = function(dir) {
            let el = $('#persen_bagian_outlet');
            let val = parseFloat(el.val()) || 0;
            val = Math.max(0, Math.min(100, val + dir));
            el.val(val.toFixed(2));
            window.balanceAdminOutletSplit('outlet');
        };

        // Toast Notification saat file bukti transfer dipilih
        $('#bukti_pembayaran').on('change', function() {
            let file = this.files && this.files[0] ? this.files[0] : null;
            if (file) {
                Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2500,
                    timerProgressBar: true
                }).fire({
                    icon: 'success',
                    title: 'File Berkas Dipilih',
                    text: file.name
                });
            }
        });

        $('#form-create-outlet').on('submit', function(el) {
            el.preventDefault();
            let button   = $(this).find('button[type="submit"]'),
                formData = new FormData(this);

            button.addClass('loading').prop('disabled', true);
            $.ajax({
                url: "<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/outlet/create",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                dataType: "json",
                success: (resp) => {
                    button.removeClass('loading').prop('disabled', false);
                    if (resp.success) {
                        let isEdit = $('input[name="id_outlet"]').val() ? true : false;
                        let defaultSuccessMsg = isEdit ? 'Data outlet berhasil diperbarui.' : 'Data outlet berhasil ditambahkan.';
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: resp.message || defaultSuccessMsg,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.href = resp.data?.redirect || "<?= SystemInfo::app('ADMIN_URL') ?>/outlet/view";
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Perhatian!',
                            text: resp.message || 'Gagal menyimpan data outlet.'
                        });
                    }
                },
                error: (xhr) => {
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
                }
            });
        });
    });
</script>
<style>
/* Custom Symmetrical Styling for File Input */
#bukti_pembayaran.form-control {
    padding: 0.375rem 0.75rem !important;
    height: 38px !important;
    line-height: 1.5 !important;
    display: flex;
    align-items: center;
    border-radius: 6px;
}
#bukti_pembayaran.form-control::file-selector-button {
    height: 38px;
    margin: -0.375rem 0.75rem -0.375rem -0.75rem;
    border: none;
    border-right: 1px solid var(--bs-border-color, #ced4da);
    background-color: #e9ecef;
    padding: 0 0.85rem;
    font-weight: 600;
    color: #495057;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
}
</style>

<script type="text/javascript">
function previewBuktiOutlet(filePath, namaOutlet, namaInvestor) {
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
        + '<div class="d-flex align-items-center">'
        + '  <i class="fa fa-handshake-o text-success me-2" style="width:20px; text-align:center;"></i>'
        + '  <span style="min-width:140px;" class="fw-bold">Nama Investor:</span>'
        + '  <span class="text-dark">' + (namaInvestor || '-') + '</span>'
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
</script>
