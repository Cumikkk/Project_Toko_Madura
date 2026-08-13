<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();
$userId = (int) ($user['MBR_ID'] ?? $user['id_users'] ?? 0);

// Fetch detailed user & investor data
$sql = "
    SELECT u.*, i.id_investor, COUNT(DISTINCT o.id_outlet) as total_outlet 
    FROM users u 
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
$roleBadgeClass = "bg-primary-subtle text-primary";
$avatarIcon = "fa-user-tie";

if ($user['role'] == 'investor') {
    $roleLabel = "Investor Toko Madura";
    $roleBadgeClass = "bg-danger-subtle text-danger border border-danger-subtle";
} elseif ($user['role'] == 'master') {
    $roleLabel = "Master Administrator";
    $roleBadgeClass = "bg-dark text-white";
} elseif ($user['role'] == 'kasir') {
    $roleLabel = "Kasir Outlet";
    $avatarIcon = "fa-cash-register";
    $roleBadgeClass = "bg-info-subtle text-info";
} elseif ($user['role'] == 'outlet') {
    $roleLabel = "Pengelola Outlet";
    $avatarIcon = "fa-store";
    $roleBadgeClass = "bg-warning-subtle text-warning-emphasis";
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
?>

<style>
    .profile-hero-card {
        background: linear-gradient(135deg, #7D0A0A 0%, #4D0709 100%);
        border-radius: 20px;
        color: #ffffff;
        position: relative;
        overflow: hidden;
    }
    .profile-hero-card::before {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.05);
        pointer-events: none;
    }
    
    .profile-avatar-container {
        position: relative;
        display: inline-block;
    }
    .profile-avatar-box {
        width: 105px;
        height: 105px;
        font-size: 44px;
        background: linear-gradient(135deg, #8B0000 0%, #4A0404 100%);
        border: 4px solid #ffffff;
        color: #ffffff;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
        border-radius: 50%;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .avatar-status-dot {
        position: absolute;
        bottom: 6px;
        right: 6px;
        width: 18px;
        height: 18px;
        background-color: #22c55e;
        border: 3px solid #ffffff;
        border-radius: 50%;
    }

    .stat-mini-card {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border-radius: 14px;
        padding: 12px 16px;
        transition: all 0.25s ease;
    }
    .stat-mini-card:hover {
        background: rgba(255, 255, 255, 0.16);
        transform: translateY(-2px);
    }

    .form-custom-group .input-group-text {
        background-color: var(--bs-body-bg, #f8fafc);
        border-color: var(--bs-border-color, #dee2e6);
        color: #7D0A0A;
        font-size: 15px;
        min-width: 46px;
        justify-content: center;
    }

    .form-custom-group .form-control {
        border-color: var(--bs-border-color, #dee2e6);
        padding: 0.65rem 1rem;
        font-size: 0.95rem;
    }

    .form-custom-group .form-control:focus {
        border-color: #7D0A0A;
        box-shadow: 0 0 0 0.25rem rgba(125, 10, 10, 0.15);
    }

    .card-profile-section {
        border-radius: 18px;
        border: none;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .section-title-badge {
        font-size: 1rem;
        font-weight: 700;
        color: #7D0A0A;
        display: flex;
        align-items: center;
        gap: 8px;
        padding-bottom: 12px;
        border-bottom: 2px solid rgba(125, 10, 10, 0.1);
        margin-bottom: 20px;
    }

    .btn-save-profile {
        background: linear-gradient(135deg, #7D0A0A 0%, #4D0709 100%);
        color: #ffffff;
        border: none;
        padding: 12px 36px;
        font-size: 1rem;
        font-weight: 700;
        border-radius: 50px;
        box-shadow: 0 6px 18px rgba(125, 10, 10, 0.25);
        transition: all 0.3s ease;
    }
    .btn-save-profile:hover {
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(125, 10, 10, 0.35);
    }
    .btn-save-profile:active {
        transform: translateY(0);
    }

    .toggle-password-btn {
        background-color: var(--bs-body-bg, #f8fafc);
        border-color: var(--bs-border-color, #dee2e6);
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .toggle-password-btn:hover {
        color: #7D0A0A;
        background-color: #f1f5f9;
    }
</style>

<div class="main-content-inner py-4">
    
    <!-- Title Page -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
        <div>
            <h3 class="fw-extrabold text-body-emphasis mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-user-gear text-danger fs-3"></i>
                Profil Saya
            </h3>
            <p class="text-body-secondary mb-0 small">Kelola informasi identitas akun, data diri, dan keamanan kata sandi Anda secara fleksibel.</p>
        </div>
        <div>
            <span class="badge bg-body-tertiary border text-body-emphasis px-3 py-2 rounded-pill small fw-semibold shadow-sm">
                <i class="fa-solid fa-clock me-1 text-danger"></i> Sesi Aktif: <?= date('d/m/Y'); ?>
            </span>
        </div>
    </div>

    <!-- Header Hero Banner Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card profile-hero-card border-0 shadow-sm p-3 p-md-4">
                <div class="row align-items-center g-3">
                    <!-- Avatar & Primary Info -->
                    <div class="col-lg-7 col-12">
                        <div class="d-flex flex-column flex-sm-row align-items-center align-items-sm-start text-center text-sm-start gap-3">
                            <div class="profile-avatar-container flex-shrink-0">
                                <div class="profile-avatar-box">
                                    <i class="fa-solid <?= $avatarIcon ?>"></i>
                                </div>
                                <div class="avatar-status-dot" title="Akun Aktif"></div>
                            </div>
                            <div class="pt-1">
                                <div class="d-flex align-items-center justify-content-center justify-content-sm-start gap-2 mb-1.5 flex-wrap">
                                    <h4 class="fw-bold mb-0 text-white"><?= htmlspecialchars($userData['nama_lengkap']) ?></h4>
                                    <span class="badge bg-white text-danger fw-bold rounded-pill px-3 py-1 fs-12 shadow-sm">
                                        <i class="fa-solid fa-shield-check me-1"></i><?= $roleLabel ?>
                                    </span>
                                </div>
                                <p class="mb-2 text-white-50 small d-flex align-items-center justify-content-center justify-content-sm-start gap-2 flex-wrap">
                                    <span><i class="fa-solid fa-at text-warning me-1"></i><?= htmlspecialchars($userData['username']) ?></span>
                                    <span>•</span>
                                    <span><i class="fa-solid fa-location-dot text-warning me-1"></i><?= !empty($userData['kecamatan']) ? ('Kec. ' . htmlspecialchars($userData['kecamatan'])) : 'Lokasi belum diisi' ?></span>
                                </p>
                                <div class="d-flex align-items-center justify-content-center justify-content-sm-start gap-2 text-white-50 fs-12">
                                    <i class="fa-solid fa-calendar-check text-warning"></i>
                                    <span>Bergabung sejak: <strong><?= $joinDate; ?></strong></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mini Stat Cards (Right Side for Investor/User) -->
                    <div class="col-lg-5 col-12">
                        <div class="row g-2 justify-content-end">
                            <?php if ($user['role'] === 'investor' && !empty($userData['id_investor'])) : ?>
                                <div class="col-6 col-sm-6">
                                    <div class="stat-mini-card text-center text-sm-start">
                                        <small class="text-white-50 d-block fw-semibold fs-11 text-uppercase mb-1">
                                            <i class="fa-solid fa-hashtag me-1 text-warning"></i>ID Investor
                                        </small>
                                        <span class="fw-bold text-white fs-5">#<?= sprintf('%03d', $userData['id_investor']); ?></span>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-6">
                                    <div class="stat-mini-card text-center text-sm-start">
                                        <small class="text-white-50 d-block fw-semibold fs-11 text-uppercase mb-1">
                                            <i class="fa-solid fa-store me-1 text-warning"></i>Mitra Toko
                                        </small>
                                        <span class="fw-bold text-white fs-5"><?= (int)($userData['total_outlet'] ?? 0); ?> <small class="fs-12 text-white-50">Cabang</small></span>
                                    </div>
                                </div>
                            <?php else : ?>
                                <div class="col-6 col-sm-6">
                                    <div class="stat-mini-card text-center text-sm-start">
                                        <small class="text-white-50 d-block fw-semibold fs-11 text-uppercase mb-1">
                                            <i class="fa-solid fa-circle-check me-1 text-warning"></i>Status Akun
                                        </small>
                                        <span class="fw-bold text-white fs-6"><i class="fa-solid fa-check-circle me-1 text-success"></i> Terverifikasi</span>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-6">
                                    <div class="stat-mini-card text-center text-sm-start">
                                        <small class="text-white-50 d-block fw-semibold fs-11 text-uppercase mb-1">
                                            <i class="fa-solid fa-user-shield me-1 text-warning"></i>Peran Usaha
                                        </small>
                                        <span class="fw-bold text-white fs-6 text-truncate d-block"><?= $roleLabel; ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Form Section -->
    <div class="row g-4">
        
        <!-- Left Column: Quick Profile Info Card -->
        <div class="col-lg-4 col-12">
            <div class="card card-profile-section bg-body border-0 shadow-sm overflow-hidden mb-4">
                <div class="card-body p-4">
                    <h6 class="section-title-badge">
                        <i class="fa-solid fa-id-card-clip text-danger"></i>
                        Ringkasan Kontak
                    </h6>

                    <div class="list-group list-group-flush border-0">
                        <div class="list-group-item bg-transparent px-0 py-3 border-bottom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                                <span class="text-body-secondary small fw-semibold">Nama Akun</span>
                            </div>
                            <span class="fw-bold text-body-emphasis small text-truncate" style="max-width: 150px;"><?= htmlspecialchars($userData['nama_lengkap']); ?></span>
                        </div>

                        <div class="list-group-item bg-transparent px-0 py-3 border-bottom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="fa-brands fa-whatsapp"></i>
                                </div>
                                <span class="text-body-secondary small fw-semibold">No. WhatsApp</span>
                            </div>
                            <span class="fw-bold text-body-emphasis small"><?= !empty($userData['no_hp']) ? htmlspecialchars($userData['no_hp']) : '-'; ?></span>
                        </div>

                        <div class="list-group-item bg-transparent px-0 py-3 border-bottom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-warning-subtle text-warning-emphasis d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="fa-solid fa-location-dot"></i>
                                </div>
                                <span class="text-body-secondary small fw-semibold">Kecamatan</span>
                            </div>
                            <span class="fw-bold text-body-emphasis small"><?= !empty($userData['kecamatan']) ? htmlspecialchars($userData['kecamatan']) : '-'; ?></span>
                        </div>

                        <div class="list-group-item bg-transparent px-0 py-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>
                                <span class="text-body-secondary small fw-semibold">Status Akses</span>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                <i class="fa-solid fa-circle-check me-1"></i>Aktif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Petunjuk Keamanan Card -->
            <div class="card card-profile-section border border-warning-subtle shadow-sm p-3" style="background: rgba(255, 193, 7, 0.05);">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; font-size: 16px;">
                        <i class="fa-solid fa-lightbulb"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-body-emphasis mb-1" style="font-size: 13.5px;">Tips Keamanan Akun</h6>
                        <p class="text-body-secondary mb-0 fs-12" style="line-height: 1.45;">
                            Gunakan kombinasi kata sandi yang kuat (huruf besar, huruf kecil, dan angka). Jangan berikan username & password Anda kepada siapapun.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Edit Profile Forms -->
        <div class="col-lg-8 col-12">
            <form id="formProfileUser" autocomplete="off">
                
                <!-- Card 1: Informasi Personal -->
                <div class="card card-profile-section bg-body border-0 shadow-sm mb-4">
                    <div class="card-body p-4 p-md-4.5">
                        <h6 class="section-title-badge">
                            <i class="fa-solid fa-user-pen text-danger"></i>
                            Informasi Identitas & Diri
                        </h6>

                        <div class="row g-3">
                            <!-- Nama Lengkap -->
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-bold text-body-emphasis small mb-1.5">
                                    Nama Lengkap <span class="text-danger">*</span>
                                </label>
                                <div class="input-group form-custom-group">
                                    <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                                    <input type="text" class="form-control" name="nama_lengkap" value="<?= htmlspecialchars($userData['nama_lengkap']) ?>" placeholder="Masukkan nama lengkap" required>
                                </div>
                            </div>

                            <!-- Username -->
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-bold text-body-emphasis small mb-1.5">
                                    Username Pengguna <span class="text-danger">*</span>
                                </label>
                                <div class="input-group form-custom-group">
                                    <span class="input-group-text"><i class="fa-solid fa-at"></i></span>
                                    <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($userData['username']) ?>" placeholder="Masukkan username" required>
                                </div>
                            </div>

                            <!-- No HP / WA -->
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-bold text-body-emphasis small mb-1.5">
                                    No. WhatsApp / Telepon
                                </label>
                                <div class="input-group form-custom-group">
                                    <span class="input-group-text"><i class="fa-brands fa-whatsapp"></i></span>
                                    <input type="text" class="form-control" name="no_hp" value="<?= htmlspecialchars($userData['no_hp'] ?? '') ?>" placeholder="Contoh: 081234567890">
                                </div>
                            </div>

                            <!-- Kecamatan -->
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-bold text-body-emphasis small mb-1.5">
                                    Kecamatan Domisili
                                </label>
                                <div class="input-group form-custom-group">
                                    <span class="input-group-text"><i class="fa-solid fa-map-location-dot"></i></span>
                                    <input type="text" class="form-control" name="kecamatan" value="<?= htmlspecialchars($userData['kecamatan'] ?? '') ?>" placeholder="Nama kecamatan domisili">
                                </div>
                            </div>

                            <!-- Alamat Lengkap -->
                            <div class="col-12">
                                <label class="form-label fw-bold text-body-emphasis small mb-1.5">
                                    Detail Alamat Lengkap
                                </label>
                                <div class="input-group form-custom-group">
                                    <span class="input-group-text align-items-start pt-2.5"><i class="fa-solid fa-location-dot"></i></span>
                                    <textarea class="form-control" name="alamat_lengkap" placeholder="Masukkan alamat domisili lengkap Anda..." style="height: 90px; resize: vertical;"><?= htmlspecialchars($userData['alamat_lengkap'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Keamanan Akun & Password -->
                <div class="card card-profile-section bg-body border-0 shadow-sm mb-4">
                    <div class="card-body p-4 p-md-4.5">
                        <h6 class="section-title-badge">
                            <i class="fa-solid fa-lock text-danger"></i>
                            Pembaruan Kata Sandi (Opsional)
                        </h6>
                        <p class="text-body-secondary small mb-3">Kosongkan kolom kata sandi di bawah ini apabila Anda tidak ingin mengubah password akun saat ini.</p>

                        <div class="row g-3">
                            <!-- Password Baru -->
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-bold text-body-emphasis small mb-1.5">
                                    Kata Sandi Baru
                                </label>
                                <div class="input-group form-custom-group">
                                    <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                                    <input type="password" class="form-control" id="inputNewPassword" name="password" placeholder="Ketik kata sandi baru">
                                    <button class="btn toggle-password-btn px-3" type="button" data-target="inputNewPassword" title="Lihat/Sembunyikan Kata Sandi">
                                        <i class="fa-solid fa-eye fs-14"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Konfirmasi Password Baru -->
                            <div class="col-md-6 col-12">
                                <label class="form-label fw-bold text-body-emphasis small mb-1.5">
                                    Konfirmasi Kata Sandi Baru
                                </label>
                                <div class="input-group form-custom-group">
                                    <span class="input-group-text"><i class="fa-solid fa-shield-halved"></i></span>
                                    <input type="password" class="form-control" id="inputConfirmPassword" name="password_confirm" placeholder="Ulangi kata sandi baru">
                                    <button class="btn toggle-password-btn px-3" type="button" data-target="inputConfirmPassword" title="Lihat/Sembunyikan Kata Sandi">
                                        <i class="fa-solid fa-eye fs-14"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button Bar -->
                <div class="d-flex justify-content-end align-items-center mb-4">
                    <button type="submit" class="btn btn-save-profile d-inline-flex align-items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Simpan Perubahan Profil</span>
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {

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
