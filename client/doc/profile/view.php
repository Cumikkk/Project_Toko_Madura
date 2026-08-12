<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();
$userId = (int) $user['MBR_ID'];
$sql = "SELECT * FROM users WHERE id_users = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();
$stmt->close();

$roleLabel = "Pengguna";
if ($user['role'] == 'investor') {
    $roleLabel = "Investor Toko Madura";
} elseif ($user['role'] == 'master') {
    $roleLabel = "Master Admin";
} elseif ($user['role'] == 'kasir') {
    $roleLabel = "Kasir Outlet";
} elseif ($user['role'] == 'outlet') {
    $roleLabel = "Pengelola Outlet";
}

$nameParts = explode(' ', $userData['nama_lengkap']);
$initials = '';
if(count($nameParts) >= 2) {
    $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
} else {
    $initials = strtoupper(substr($userData['nama_lengkap'], 0, 2));
}
?>
<style>
    .profile-card-header {
        height: 120px;
        background: linear-gradient(135deg, #7D0A0A 0%, #4A0404 100%);
    }
    .profile-avatar {
        width: 100px;
        height: 100px;
        font-size: 36px;
        background: #ffffff;
        border: 4px solid #ffffff;
        color: #7D0A0A;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        margin-top: -50px;
    }
    .form-floating > .form-control:focus ~ label,
    .form-floating > .form-control:not(:placeholder-shown) ~ label {
        color: #7D0A0A;
        font-weight: 600;
    }
    .form-floating > .form-control:focus {
        border-color: #7D0A0A;
        box-shadow: 0 0 0 0.25rem rgba(125, 10, 10, 0.15);
    }
    .btn-save-profile {
        background: linear-gradient(135deg, #7D0A0A 0%, #4A0404 100%);
        border: none;
        transition: all 0.3s ease;
    }
    .btn-save-profile:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(125, 10, 10, 0.3);
    }
</style>

<div class="main-content-inner py-4">
    <div class="mb-4">
        <h3 class="fw-bold text-dark mb-1">
            <i class="fa-solid fa-user-gear me-2" style="color: #701416;"></i>Profil Saya
        </h3>
        <p class="text-muted mb-0">Kelola informasi data diri dan keamanan akun Anda.</p>
    </div>

    <div class="row g-4">
        <!-- Left Column: Master Profile Card -->
        <div class="col-lg-4 col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden text-center">
                <div class="profile-card-header"></div>
                <div class="card-body px-4 pb-4 pt-0">
                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3 profile-avatar">
                        <span class="fw-bold"><?= $initials ?></span>
                    </div>
                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($userData['nama_lengkap']) ?></h5>
                    <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fw-semibold mb-3">
                        <i class="fa-solid fa-user-shield me-1"></i> <?= $roleLabel ?>
                    </span>
                    <?php if(!empty($userData['kecamatan'])): ?>
                    <p class="text-muted small mb-4"><i class="fa-solid fa-location-dot me-1 text-danger"></i> Kec. <?= htmlspecialchars($userData['kecamatan']) ?></p>
                    <?php endif; ?>
                    
                    <div class="border-top pt-4 text-start small">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted"><i class="fa-solid fa-at me-2"></i>Username</span>
                            <span class="fw-bold text-dark"><?= htmlspecialchars($userData['username']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted"><i class="fa-solid fa-shield-check me-2"></i>Status Akun</span>
                            <span class="badge bg-success-subtle text-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> Aktif</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Forms -->
        <div class="col-lg-8 col-12">
            <form id="formProfileUser">
                <!-- Card 1: Informasi Akun -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-id-card text-danger me-2"></i> Informasi Akun</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6 col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control rounded-3" id="namaLengkap" name="nama_lengkap" value="<?= htmlspecialchars($userData['nama_lengkap']) ?>" placeholder="Nama Lengkap" required>
                                    <label for="namaLengkap">Nama Lengkap</label>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control rounded-3" id="username" name="username" value="<?= htmlspecialchars($userData['username']) ?>" placeholder="Username" required>
                                    <label for="username">Username</label>
                                </div>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6 col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control rounded-3" id="noHp" name="no_hp" value="<?= htmlspecialchars($userData['no_hp'] ?? '') ?>" placeholder="No. WhatsApp / Telepon">
                                    <label for="noHp">No. WhatsApp / Telepon</label>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control rounded-3" id="kecamatan" name="kecamatan" value="<?= htmlspecialchars($userData['kecamatan'] ?? '') ?>" placeholder="Kecamatan">
                                    <label for="kecamatan">Kecamatan</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-floating">
                            <textarea class="form-control rounded-3" id="alamatLengkap" name="alamat_lengkap" placeholder="Alamat Lengkap" style="height: 100px"><?= htmlspecialchars($userData['alamat_lengkap'] ?? '') ?></textarea>
                            <label for="alamatLengkap">Alamat Lengkap</label>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Keamanan / Ganti Password -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-lock text-danger me-2"></i> Keamanan Akun</h5>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted small mb-3">Kosongkan kolom password jika Anda tidak ingin mengubah kata sandi.</p>
                        <div class="row g-3">
                            <div class="col-md-6 col-12">
                                <div class="form-floating">
                                    <input type="password" class="form-control rounded-3" id="password" name="password" placeholder="Password Baru">
                                    <label for="password">Password Baru</label>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-floating">
                                    <input type="password" class="form-control rounded-3" id="passwordConfirm" name="password_confirm" placeholder="Konfirmasi Password">
                                    <label for="passwordConfirm">Konfirmasi Password Baru</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end mb-4">
                    <button type="submit" class="btn btn-danger btn-lg px-5 py-2 fw-bold shadow-sm rounded-pill btn-save-profile">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $('#formProfileUser').on('submit', function(e) {
        e.preventDefault();
        let button = $(this).find('button[type="submit"]'),
            data = $(this).serialize();
            
        let pwd = $('input[name="password"]').val();
        let pwdConf = $('input[name="password_confirm"]').val();
        
        if(pwd !== '' && pwd !== pwdConf) {
            Swal.fire('Perhatian!', 'Konfirmasi password baru tidak cocok.', 'warning');
            return;
        }
            
        button.addClass('loading').prop('disabled', true);
        $.post("<?= SystemInfo::app('CLIENT_URL') ?>/ajax/post/profile/update", data, (resp) => {
            button.removeClass('loading').prop('disabled', false);
            if (resp.success) {
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
                    title: 'Perhatian!',
                    text: resp.message || 'Gagal menyimpan profil.'
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
