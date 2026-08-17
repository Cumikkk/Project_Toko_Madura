<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();
$userId = (int) ($user['MBR_ID'] ?? $user['id_users'] ?? 0);

// Fetch detailed user & investor data with master_wilayah
$sql = "
    SELECT u.*, i.id_investor, COUNT(DISTINCT o.id_outlet) as total_outlet,
           mw.provinsi, mw.kabupaten, mw.kecamatan, mw.kelurahan, mw.kodepos
    FROM users u 
    LEFT JOIN master_wilayah mw ON u.id_wilayah = mw.id_wilayah
    LEFT JOIN investor i ON i.id_users = u.id_users 
    LEFT JOIN outlet o ON o.id_investor = i.id_investor 
    WHERE u.id_users = ? 
    GROUP BY u.id_users
";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();
$stmt->close();

$roleLabel = "Pengguna Sistem";
$avatarIcon = "fa-user-tie";

if ($user['role'] == 'investor') {
    $roleLabel = "Investor Toko Madura";
} elseif ($user['role'] == 'master') {
    $roleLabel = "Master Administrator";
    $avatarIcon = "fa-user-gear";
} elseif ($user['role'] == 'kasir') {
    $roleLabel = "Kasir Outlet";
    $avatarIcon = "fa-cash-register";
} elseif ($user['role'] == 'outlet') {
    $roleLabel = "Pengelola Outlet";
    $avatarIcon = "fa-store";
}

$bulanIndo = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$joinDate = '-';
if (!empty($userData['created_at']) && strtotime($userData['created_at']) > 0) {
    $ts = strtotime($userData['created_at']);
    $joinDate = date('d', $ts) . ' ' . ($bulanIndo[(int)date('n', $ts)] ?? '') . ' ' . date('Y', $ts);
}

// Build wilayah header string
$wilayahHeader = 'Lokasi belum diisi';
if (!empty($userData['kecamatan']) && $userData['kecamatan'] !== '-') {
    $cleanKel = !empty($userData['kelurahan']) ? preg_replace('/^(Kel\.|Desa)\s*/i', '', $userData['kelurahan']) : '';
    $cleanKec = !empty($userData['kecamatan']) ? (stripos($userData['kecamatan'], 'kec') === 0 ? $userData['kecamatan'] : 'Kec. ' . $userData['kecamatan']) : '';
    $cleanKab = !empty($userData['kabupaten']) ? (stripos($userData['kabupaten'], 'kab') === 0 || stripos($userData['kabupaten'], 'kota') === 0 ? $userData['kabupaten'] : 'Kab. ' . $userData['kabupaten']) : '';
    $wilayahParts = array_filter([$cleanKel, $cleanKec, $cleanKab]);
    if (!empty($wilayahParts)) {
        $wilayahHeader = ucwords(strtolower(implode(', ', $wilayahParts)));
    }
}
?>

<style>
    .profile-hero-card {
        background: linear-gradient(135deg, #7D0A0A 0%, #4D0709 100%);
        border-radius: 18px;
        color: #ffffff;
        position: relative;
        overflow: hidden;
        border: none;
    }
    .profile-hero-card::before {
        content: '';
        position: absolute;
        top: -40px;
        right: -40px;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.04);
        pointer-events: none;
    }
    
    .profile-avatar-box {
        width: 85px;
        height: 85px;
        font-size: 36px;
        background: linear-gradient(135deg, #8B0000 0%, #4A0404 100%);
        border: 3.5px solid #ffffff;
        color: #ffffff;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }
    .avatar-online-dot {
        position: absolute;
        bottom: 2px;
        right: 2px;
        width: 16px;
        height: 16px;
        background-color: #22c55e;
        border: 2.5px solid #ffffff;
        border-radius: 50%;
    }

    .hero-stat-pill {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 12px;
        padding: 8px 14px;
        transition: all 0.2s ease;
    }
    .hero-stat-pill:hover {
        background: rgba(255, 255, 255, 0.15);
    }

    .card-profile-section {
        border-radius: 16px;
        border: none;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .section-header-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #7D0A0A;
        display: flex;
        align-items: center;
        gap: 8px;
        padding-bottom: 12px;
        border-bottom: 2px solid rgba(125, 10, 10, 0.08);
        margin-bottom: 18px;
    }

    .form-custom-group .input-group-text {
        background-color: var(--bs-body-bg, #f8fafc);
        border-color: var(--bs-border-color, #dee2e6);
        color: #7D0A0A;
        font-size: 14px;
        min-width: 44px;
        justify-content: center;
    }

    .form-custom-group .form-control {
        border-color: var(--bs-border-color, #dee2e6);
        padding: 0.6rem 0.9rem;
        font-size: 0.92rem;
    }

    .form-custom-group .form-control:focus {
        border-color: #7D0A0A;
        box-shadow: 0 0 0 0.2rem rgba(125, 10, 10, 0.12);
    }

    .toggle-password-btn {
        background-color: var(--bs-body-bg, #f8fafc);
        border-color: var(--bs-border-color, #dee2e6);
        color: #64748b;
        cursor: pointer;
    }
    .toggle-password-btn:hover {
        color: #7D0A0A;
        background-color: #f1f5f9;
    }

    .btn-save-profile {
        background: linear-gradient(135deg, #7D0A0A 0%, #4D0709 100%);
        color: #ffffff;
        border: none;
        padding: 12px 36px;
        font-size: 0.95rem;
        font-weight: 700;
        border-radius: 50px;
        box-shadow: 0 4px 14px rgba(125, 10, 10, 0.25);
        transition: all 0.25s ease;
    }
    .btn-save-profile:hover {
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(125, 10, 10, 0.35);
    }

    /* Select2 Container Styling */
    .select2-container {
        width: 100% !important;
        display: block !important;
    }
    .select2-container .select2-selection--single {
        height: 42px !important;
        border: 1px solid var(--bs-border-color, #dee2e6) !important;
        border-radius: 8px !important;
        background-color: var(--bs-body-bg, #ffffff) !important;
        cursor: pointer !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: var(--bs-body-color, #212529) !important;
        line-height: 40px !important;
        padding-left: 12px !important;
        padding-right: 32px !important;
        font-size: 0.92rem !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
        right: 8px !important;
        top: 0 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__clear {
        display: none !important;
    }
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #7D0A0A !important;
        box-shadow: 0 0 0 0.2rem rgba(125, 10, 10, 0.12) !important;
    }
    .select2-dropdown {
        border: 1px solid var(--bs-border-color, #dee2e6) !important;
        border-radius: 8px !important;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
        z-index: 99999 !important;
        background-color: #ffffff !important;
    }
    .select2-results__option {
        padding: 8px 12px !important;
        font-size: 0.92rem !important;
    }
    .select2-results__option--highlighted[aria-selected] {
        background-color: #7D0A0A !important;
        color: #ffffff !important;
    }
</style>

<div class="main-content-inner py-3 mb-5 pb-4">

    <!-- Header Hero Banner Card -->
    <div class="card profile-hero-card shadow-sm p-3 mb-4">
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
            
            <!-- Left Info: Avatar + User Identity -->
            <div class="d-flex align-items-center gap-3 text-center text-md-start flex-column flex-sm-row">
                <div class="profile-avatar-box flex-shrink-0">
                    <i class="fa-solid <?= $avatarIcon ?>"></i>
                    <div class="avatar-online-dot" title="Sesi Aktif"></div>
                </div>
                <div>
                    <div class="d-flex align-items-center justify-content-center justify-content-sm-start gap-2 mb-1 flex-wrap">
                        <h4 class="fw-bold mb-0 text-white"><?= htmlspecialchars($userData['nama_lengkap']) ?></h4>
                        <span class="badge bg-white text-danger fw-bold rounded-pill px-2.5 py-1 fs-12 shadow-sm">
                            <i class="fa-solid fa-shield-check me-1"></i><?= $roleLabel ?>
                        </span>
                    </div>
                    <p class="mb-1 text-white-50 small d-flex align-items-center justify-content-center justify-content-sm-start gap-2 flex-wrap">
                        <span><i class="fa-solid fa-at text-warning me-1"></i><?= htmlspecialchars($userData['username']) ?></span>
                        <span>•</span>
                        <span><i class="fa-solid fa-location-dot text-warning me-1"></i><?= htmlspecialchars($wilayahHeader) ?></span>
                    </p>
                    <div class="text-white-50 fs-12">
                        <i class="fa-solid fa-calendar-check text-warning me-1"></i>Bergabung sejak: <strong><?= $joinDate; ?></strong>
                    </div>
                </div>
            </div>

            <!-- Right Info: Inline Quick Stat Pills -->
            <div class="d-flex align-items-center gap-2 flex-wrap justify-content-center justify-content-md-end">
                <?php if ($user['role'] === 'investor' && !empty($userData['id_investor'])) : ?>
                    <div class="hero-stat-pill text-center text-md-start">
                        <small class="text-white-50 d-block fw-semibold fs-11 text-uppercase">Mitra Toko</small>
                        <span class="fw-bold text-white fs-6"><?= (int)($userData['total_outlet'] ?? 0); ?> <small class="fs-12 text-white-50">Cabang</small></span>
                    </div>
                <?php else : ?>
                    <div class="hero-stat-pill text-center text-md-start">
                        <small class="text-white-50 d-block fw-semibold fs-11 text-uppercase">Peran Usaha</small>
                        <span class="fw-bold text-white fs-6"><?= $roleLabel; ?></span>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- Master Form Container (Balanced 2-Column Grid: Akun & Keamanan (Kiri) | Wilayah & Domisili (Kanan)) -->
    <form id="formProfileUser" autocomplete="off">
        <div class="row g-4 align-items-stretch">
            
            <!-- LEFT COLUMN (col-lg-6): Identitas Akun & Keamanan Kata Sandi -->
            <div class="col-lg-6 col-12">
                <div class="card card-profile-section bg-body p-4">
                    <h6 class="section-header-title">
                        <i class="fa-solid fa-user-shield text-danger"></i>
                        Informasi Akun & Keamanan
                    </h6>

                    <div class="row g-3">
                        <!-- Nama Lengkap -->
                        <div class="col-12">
                            <label class="form-label fw-bold text-body-emphasis small mb-1">
                                Nama Lengkap Pengguna <span class="text-danger">*</span>
                            </label>
                            <div class="input-group form-custom-group">
                                <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                                <input type="text" class="form-control" name="nama_lengkap" value="<?= htmlspecialchars($userData['nama_lengkap']) ?>" placeholder="Masukkan nama lengkap" required>
                            </div>
                        </div>

                        <!-- Username -->
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-bold text-body-emphasis small mb-1">
                                Username Akun <span class="text-danger">*</span>
                            </label>
                            <div class="input-group form-custom-group">
                                <span class="input-group-text"><i class="fa-solid fa-at"></i></span>
                                <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($userData['username']) ?>" placeholder="Masukkan username" required>
                            </div>
                        </div>

                        <!-- No HP / WhatsApp -->
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-bold text-body-emphasis small mb-1">
                                No. WhatsApp / Telepon
                            </label>
                            <div class="input-group form-custom-group">
                                <span class="input-group-text"><i class="fa-brands fa-whatsapp"></i></span>
                                <input type="text" class="form-control" name="no_hp" value="<?= htmlspecialchars($userData['no_hp'] ?? '') ?>" placeholder="08xxxxxxxxxx">
                            </div>
                        </div>

                        <!-- Divider Keamanan -->
                        <div class="col-12 pt-1">
                            <div class="d-flex align-items-center gap-2 mb-2 pb-1 border-bottom">
                                <i class="fa-solid fa-key text-danger"></i>
                                <span class="fw-bold text-body-emphasis small text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Ubah Kata Sandi (Opsional)</span>
                            </div>
                        </div>

                        <!-- Password Baru -->
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-bold text-body-emphasis small mb-1">
                                Kata Sandi Baru
                            </label>
                            <div class="input-group form-custom-group">
                                <span class="input-group-text"><i class="fa-solid fa-lock-open"></i></span>
                                <input type="password" class="form-control" id="inputNewPassword" name="password" placeholder="Kata sandi baru">
                                <button class="btn toggle-password-btn px-2.5" type="button" data-target="inputNewPassword" title="Lihat/Sembunyikan Kata Sandi">
                                    <i class="fa-solid fa-eye fs-14"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Konfirmasi Password Baru -->
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-bold text-body-emphasis small mb-1">
                                Konfirmasi Kata Sandi
                            </label>
                            <div class="input-group form-custom-group">
                                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                <input type="password" class="form-control" id="inputConfirmPassword" name="password_confirm" placeholder="Ulangi kata sandi">
                                <button class="btn toggle-password-btn px-2.5" type="button" data-target="inputConfirmPassword" title="Lihat/Sembunyikan Kata Sandi">
                                    <i class="fa-solid fa-eye fs-14"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN (col-lg-6): Wilayah Administrasi & Alamat Lengkap -->
            <div class="col-lg-6 col-12">
                <div class="card card-profile-section bg-body p-4">
                    <h6 class="section-header-title">
                        <i class="fa-solid fa-map-location-dot text-danger"></i>
                        Wilayah
                    </h6>

                    <div class="row g-3">
                        <!-- Dropdown Provinsi -->
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-bold text-body-emphasis small mb-1">
                                Provinsi
                            </label>
                            <select class="wilayah-select" id="selectProvinsi" name="provinsi" data-placeholder="Pilih Provinsi..." disabled>
                                <option value=""></option>
                            </select>
                        </div>

                        <!-- Dropdown Kabupaten/Kota -->
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-bold text-body-emphasis small mb-1">
                                Kabupaten / Kota
                            </label>
                            <select class="wilayah-select" id="selectKabupaten" name="kabupaten" data-placeholder="Pilih Kabupaten/Kota..." disabled>
                                <option value=""></option>
                            </select>
                        </div>

                        <!-- Dropdown Kecamatan -->
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-bold text-body-emphasis small mb-1">
                                Kecamatan
                            </label>
                            <select class="wilayah-select" id="selectKecamatan" name="kecamatan" data-placeholder="Pilih Kecamatan..." disabled>
                                <option value=""></option>
                            </select>
                        </div>

                        <!-- Dropdown Kelurahan/Desa (id_wilayah) -->
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-bold text-body-emphasis small mb-1">
                                Kelurahan / Desa
                            </label>
                            <select class="wilayah-select" id="selectKelurahan" name="id_wilayah" data-placeholder="Pilih Kelurahan/Desa..." disabled>
                                <option value=""></option>
                            </select>
                        </div>

                        <!-- Alamat Lengkap -->
                        <div class="col-12">
                            <label class="form-label fw-bold text-body-emphasis small mb-1">
                                Detail Alamat Lengkap
                            </label>
                            <div class="input-group form-custom-group">
                                <span class="input-group-text align-items-start pt-2"><i class="fa-solid fa-location-dot"></i></span>
                                <textarea class="form-control" name="alamat_lengkap" placeholder="Alamat jalan / Plus code / Geotag domisili..." style="height: 72px; min-height: 72px; resize: vertical;"><?= htmlspecialchars($userData['alamat_lengkap'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BOTTOM ACTION BAR: Tombol Simpan Perubahan Profil -->
            <div class="col-12 text-center pt-2">
                <div class="card bg-body p-3 shadow-sm border-0 rounded-4 d-flex flex-row align-items-center justify-content-between flex-wrap gap-3">
                    <div class="text-start d-flex align-items-center gap-2 ps-2">
                        <i class="fa-solid fa-circle-info text-danger fs-5"></i>
                        <small class="text-muted">Pastikan data yang dimasukkan sudah benar sebelum menyimpan perubahan.</small>
                    </div>
                    <button type="submit" class="btn btn-save-profile d-inline-flex align-items-center justify-content-center gap-2 px-4 py-2.5">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Simpan Perubahan Profil</span>
                    </button>
                </div>
            </div>

        </div>
    </form>
</div>

<script type="text/javascript">
$(document).ready(function() {
    var clientUrl = '<?= SystemInfo::app("CLIENT_URL") ?>';

    // Existing user wilayah data for auto-selection
    var edit_provinsi = "<?= !empty($userData['provinsi']) ? addslashes($userData['provinsi']) : '' ?>";
    var edit_kabupaten = "<?= !empty($userData['kabupaten']) ? addslashes($userData['kabupaten']) : '' ?>";
    var edit_kecamatan = "<?= !empty($userData['kecamatan']) ? addslashes($userData['kecamatan']) : '' ?>";
    var edit_id_wilayah = <?= !empty($userData['id_wilayah']) ? (int)$userData['id_wilayah'] : 'null' ?>;
    var isInitialCascade = edit_provinsi ? true : false;

    function initWilayahSelect2(selector) {
        let $el = $(selector);
        let placeholder = $el.attr('data-placeholder') || 'Pilih...';
        
        if ($el.hasClass("select2-hidden-accessible")) {
            try { $el.select2('destroy'); } catch(e) {}
        }
        $el.parent().find('> .select2-container').remove();
        
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

    // Auto-focus search input inside Select2
    $(document).on('select2:open', function() {
        setTimeout(() => {
            let searchField = document.querySelector('.select2-container--open .select2-search__field');
            if (searchField) {
                searchField.focus();
            }
        }, 50);
    });

    // 1. Load Provinsi List
    function loadProvinsi() {
        $.post(clientUrl + "/ajax/post/wilayah/get_provinsi", function(res) {
            let options = '<option value=""></option>';
            if (res && res.results) {
                res.results.forEach(item => {
                    let selected = (item.id === edit_provinsi) ? 'selected' : '';
                    options += `<option value="${item.id}" ${selected}>${item.text}</option>`;
                });
            }
            if ($('#selectProvinsi').hasClass("select2-hidden-accessible")) {
                try { $('#selectProvinsi').select2('destroy'); } catch(e) {}
            }
            $('#selectProvinsi').parent().find('> .select2-container').remove();
            $('#selectProvinsi').html(options).prop('disabled', true);
            initWilayahSelect2('#selectProvinsi');

            if (isInitialCascade && edit_provinsi) {
                $('#selectProvinsi').trigger('change');
            }
        });
    }

    // 2. Change Provinsi -> Load Kabupaten
    $('#selectProvinsi').on('change', function() {
        let prov = $(this).val();

        ['#selectKabupaten', '#selectKecamatan', '#selectKelurahan'].forEach(function(sel) {
            if ($(sel).hasClass("select2-hidden-accessible")) {
                try { $(sel).select2('destroy'); } catch(e) {}
            }
            $(sel).parent().find('> .select2-container').remove();
            $(sel).html('<option value=""></option>').prop('disabled', true);
            initWilayahSelect2(sel);
        });

        if (prov) {
            $.post(clientUrl + "/ajax/post/wilayah/get_kabupaten", { provinsi: prov }, function(res) {
                let options = '<option value=""></option>';
                if (res && res.results) {
                    res.results.forEach(item => {
                        let selected = (item.id === edit_kabupaten) ? 'selected' : '';
                        options += `<option value="${item.id}" ${selected}>${item.text}</option>`;
                    });
                }
                if ($('#selectKabupaten').hasClass("select2-hidden-accessible")) {
                    try { $('#selectKabupaten').select2('destroy'); } catch(e) {}
                }
                $('#selectKabupaten').parent().find('> .select2-container').remove();
                $('#selectKabupaten').html(options).prop('disabled', true);
                initWilayahSelect2('#selectKabupaten');

                if (isInitialCascade && edit_kabupaten) {
                    $('#selectKabupaten').trigger('change');
                }
            });
        }
    });

    // 3. Change Kabupaten -> Load Kecamatan
    $('#selectKabupaten').on('change', function() {
        let prov = $('#selectProvinsi').val();
        let kab = $(this).val();

        ['#selectKecamatan', '#selectKelurahan'].forEach(function(sel) {
            if ($(sel).hasClass("select2-hidden-accessible")) {
                try { $(sel).select2('destroy'); } catch(e) {}
            }
            $(sel).parent().find('> .select2-container').remove();
            $(sel).html('<option value=""></option>').prop('disabled', true);
            initWilayahSelect2(sel);
        });

        if (kab) {
            $.post(clientUrl + "/ajax/post/wilayah/get_kecamatan", { provinsi: prov, kabupaten: kab }, function(res) {
                let options = '<option value=""></option>';
                if (res && res.results) {
                    res.results.forEach(item => {
                        let selected = (item.id === edit_kecamatan) ? 'selected' : '';
                        options += `<option value="${item.id}" ${selected}>${item.text}</option>`;
                    });
                }
                if ($('#selectKecamatan').hasClass("select2-hidden-accessible")) {
                    try { $('#selectKecamatan').select2('destroy'); } catch(e) {}
                }
                $('#selectKecamatan').parent().find('> .select2-container').remove();
                $('#selectKecamatan').html(options).prop('disabled', true);
                initWilayahSelect2('#selectKecamatan');

                if (isInitialCascade && edit_kecamatan) {
                    $('#selectKecamatan').trigger('change');
                }
            });
        }
    });

    // 4. Change Kecamatan -> Load Kelurahan / Desa (id_wilayah)
    $('#selectKecamatan').on('change', function() {
        let prov = $('#selectProvinsi').val();
        let kab = $('#selectKabupaten').val();
        let kec = $(this).val();

        if ($('#selectKelurahan').hasClass("select2-hidden-accessible")) {
            try { $('#selectKelurahan').select2('destroy'); } catch(e) {}
        }
        $('#selectKelurahan').parent().find('> .select2-container').remove();
        $('#selectKelurahan').html('<option value=""></option>').prop('disabled', true);
        initWilayahSelect2('#selectKelurahan');

        if (kec) {
            $.post(clientUrl + "/ajax/post/wilayah/get_kelurahan", { provinsi: prov, kabupaten: kab, kecamatan: kec }, function(res) {
                let options = '<option value=""></option>';
                if (res && res.results) {
                    res.results.forEach(item => {
                        let selected = (parseInt(item.id) === parseInt(edit_id_wilayah)) ? 'selected' : '';
                        options += `<option value="${item.id}" ${selected}>${item.text}</option>`;
                    });
                }
                if ($('#selectKelurahan').hasClass("select2-hidden-accessible")) {
                    try { $('#selectKelurahan').select2('destroy'); } catch(e) {}
                }
                $('#selectKelurahan').parent().find('> .select2-container').remove();
                $('#selectKelurahan').html(options).prop('disabled', true);
                initWilayahSelect2('#selectKelurahan');

                if (isInitialCascade) {
                    isInitialCascade = false; // Initial cascade finished!
                }
            });
        }
    });

    // Inisialisasi awal hanya untuk 3 child dropdown (Provinsi di-load langsung oleh loadProvinsi)
    initWilayahSelect2('#selectKabupaten');
    initWilayahSelect2('#selectKecamatan');
    initWilayahSelect2('#selectKelurahan');

    // Run Initial Load
    loadProvinsi();

    // Toggle Password Visibility (Show/Hide)
    $(document).on('click', '.toggle-password-btn', function() {
        const targetId = $(this).data('target');
        const inputField = $('#' + targetId);
        const icon = $(this).find('i');

        if (inputField.length > 0) {
            const currentType = inputField.attr('type');
            if (currentType === 'password') {
                inputField.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash text-danger');
            } else {
                inputField.attr('type', 'password');
                icon.removeClass('fa-eye-slash text-danger').addClass('fa-eye');
            }
        }
    });

    // Form Profile Submit Handler
    $('#formProfileUser').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');

        const pwd = $('input[name="password"]').val();
        const pwdConf = $('input[name="password_confirm"]').val();

        if (pwd !== '' && pwd !== pwdConf) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian!',
                text: 'Konfirmasi kata sandi baru tidak cocok. Silakan periksa kembali.'
            });
            return false;
        }

        const originalBtnHtml = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i> Menyimpan Perubahan...');

        $.ajax({
            url: "<?= SystemInfo::app('CLIENT_URL') ?>/ajax/post/profile/update",
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(resp) {
                submitBtn.prop('disabled', false).html(originalBtnHtml);
                if (resp && resp.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: resp.message || 'Profil berhasil diperbarui.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: (resp && resp.message) ? resp.message : 'Gagal menyimpan perubahan profil.'
                    });
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalBtnHtml);
                let errorMsg = 'Terjadi kendala pada server. Silakan coba lagi.';
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
