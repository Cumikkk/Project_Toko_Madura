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
$roleBadgeClass = "bg-danger-subtle text-danger border border-danger-subtle";
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
        font-size: 1rem;
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
        padding: 10px 32px;
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

    .security-check-box {
        background-color: rgba(125, 10, 10, 0.03);
        border: 1px solid rgba(125, 10, 10, 0.1);
        border-radius: 12px;
        padding: 12px 14px;
    }
</style>

<div class="main-content-inner py-3 mb-5 pb-4">
    
    <!-- Title Section -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div>
            <h4 class="fw-extrabold text-body-emphasis mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-user-gear text-danger fs-4"></i>
                Pengaturan Profil Saya
            </h4>
            <p class="text-body-secondary mb-0 small">Kelola informasi identitas, domisili, dan keamanan kata sandi akun Anda dalam satu tampilan presisi.</p>
        </div>
        <div>
            <span class="badge bg-body-tertiary border text-body-emphasis px-3 py-2 rounded-pill small fw-semibold shadow-sm">
                <i class="fa-solid fa-user-check me-1 text-success"></i> Status: Terverifikasi
            </span>
        </div>
    </div>

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
                        <span><i class="fa-solid fa-location-dot text-warning me-1"></i><?= !empty($userData['kecamatan']) ? ('Kec. ' . htmlspecialchars($userData['kecamatan'])) : 'Lokasi belum diisi' ?></span>
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
                        <small class="text-white-50 d-block fw-semibold fs-11 text-uppercase">ID Investor</small>
                        <span class="fw-bold text-white fs-6">#<?= sprintf('%03d', $userData['id_investor']); ?></span>
                    </div>
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
                <div class="hero-stat-pill text-center text-md-start">
                    <small class="text-white-50 d-block fw-semibold fs-11 text-uppercase">No. WhatsApp</small>
                    <span class="fw-bold text-white fs-6"><?= !empty($userData['no_hp']) ? htmlspecialchars($userData['no_hp']) : '-'; ?></span>
                </div>
            </div>

        </div>
    </div>

    <!-- Master Form Container (100% Balanced 2-Column Grid) -->
    <form id="formProfileUser" autocomplete="off">
        <div class="row g-4">
            
            <!-- LEFT COLUMN (col-lg-6): Identitas & Kontak -->
            <div class="col-lg-6 col-12">
                <div class="card card-profile-section bg-body p-4">
                    <h6 class="section-header-title">
                        <i class="fa-solid fa-id-card text-danger"></i>
                        Data Identitas & Informasi Kontak
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

                        <!-- Kecamatan -->
                        <div class="col-12">
                            <label class="form-label fw-bold text-body-emphasis small mb-1">
                                Kecamatan Domisili
                            </label>
                            <div class="input-group form-custom-group">
                                <span class="input-group-text"><i class="fa-solid fa-map-location-dot"></i></span>
                                <input type="text" class="form-control" name="kecamatan" value="<?= htmlspecialchars($userData['kecamatan'] ?? '') ?>" placeholder="Nama kecamatan domisili">
                            </div>
                        </div>

                        <!-- Alamat Lengkap -->
                        <div class="col-12">
                            <label class="form-label fw-bold text-body-emphasis small mb-1">
                                Detail Alamat Lengkap
                            </label>
                            <div class="input-group form-custom-group">
                                <span class="input-group-text align-items-start pt-2"><i class="fa-solid fa-location-dot"></i></span>
                                <textarea class="form-control" name="alamat_lengkap" placeholder="Detail alamat rumah / usaha lengkap Anda..." style="height: 65px; min-height: 65px; resize: vertical;"><?= htmlspecialchars($userData['alamat_lengkap'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN (col-lg-6): Keamanan & Kata Sandi -->
            <div class="col-lg-6 col-12">
                <div class="card card-profile-section bg-body p-4">
                    <h6 class="section-header-title">
                        <i class="fa-solid fa-shield-halved text-danger"></i>
                        Keamanan & Pembaruan Kata Sandi
                    </h6>

                    <div class="row g-3">
                        <!-- Password Baru -->
                        <div class="col-12">
                            <label class="form-label fw-bold text-body-emphasis small mb-1">
                                Kata Sandi Baru
                            </label>
                            <div class="input-group form-custom-group">
                                <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                                <input type="password" class="form-control" id="inputNewPassword" name="password" placeholder="Kosongkan jika tidak ingin mengubah kata sandi">
                                <button class="btn toggle-password-btn px-3" type="button" data-target="inputNewPassword" title="Lihat/Sembunyikan Kata Sandi">
                                    <i class="fa-solid fa-eye fs-14"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Konfirmasi Password Baru -->
                        <div class="col-12">
                            <label class="form-label fw-bold text-body-emphasis small mb-1">
                                Konfirmasi Kata Sandi Baru
                            </label>
                            <div class="input-group form-custom-group">
                                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                                <input type="password" class="form-control" id="inputConfirmPassword" name="password_confirm" placeholder="Ulangi kata sandi baru untuk verifikasi">
                                <button class="btn toggle-password-btn px-3" type="button" data-target="inputConfirmPassword" title="Lihat/Sembunyikan Kata Sandi">
                                    <i class="fa-solid fa-eye fs-14"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BOTTOM BAR: Action Button Bar -->
            <div class="col-12 mt-2 mb-5 pb-4">
                <div class="card border-0 shadow-sm rounded-4 bg-body p-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2 text-body-secondary small">
                            <i class="fa-solid fa-circle-info text-primary"></i>
                            <span>Pastikan seluruh data yang Anda ubah sudah sesuai sebelum menekan tombol simpan.</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 ms-auto">
                            <button type="reset" class="btn btn-light border rounded-pill px-4 fw-bold text-body-secondary">
                                <i class="fa-solid fa-rotate-left me-1"></i> Batal
                            </button>
                            <button type="submit" class="btn btn-save-profile d-inline-flex align-items-center gap-2">
                                <i class="fa-solid fa-floppy-disk"></i>
                                <span>Simpan Perubahan Profil</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
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
