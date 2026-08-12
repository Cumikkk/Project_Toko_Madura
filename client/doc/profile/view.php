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
$avatarIcon = "fa-user-tie";

if ($user['role'] == 'investor') {
    $roleLabel = "Investor Toko Madura";
} elseif ($user['role'] == 'master') {
    $roleLabel = "Master Admin";
} elseif ($user['role'] == 'kasir') {
    $roleLabel = "Kasir Outlet";
    $avatarIcon = "fa-cash-register";
} elseif ($user['role'] == 'outlet') {
    $roleLabel = "Pengelola Outlet";
    $avatarIcon = "fa-store";
}
?>
<style>
    .profile-avatar {
        width: 100px;
        height: 100px;
        font-size: 40px;
        background: linear-gradient(135deg, #7D0A0A 0%, #4A0404 100%);
        border: 4px solid #ffffff;
        color: #ffffff;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        margin: 0 auto;
    }
    
    .form-control {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        background-color: #ffffff;
        padding: 0.6rem 1rem;
    }
    .form-control:focus {
        border-color: #7D0A0A;
        background-color: #ffffff;
        box-shadow: 0 0 0 0.25rem rgba(125, 10, 10, 0.15);
    }
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.4rem;
        font-size: 0.95rem;
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
    
    .card-title-profile {
        font-size: 1.15rem;
        font-weight: 700;
        color: #7D0A0A;
        border-bottom: 2px solid rgba(125, 10, 10, 0.1);
        padding-bottom: 0.75rem;
        margin-bottom: 1.5rem;
    }
</style>

<div class="main-content-inner py-4">
    <div class="mb-4">
        <h3 class="fw-bold text-dark mb-1">
            <i class="fa-solid fa-user-gear me-2" style="color: #7D0A0A;"></i>Profil Saya
        </h3>
        <p class="text-muted mb-0">Kelola informasi data diri dan keamanan akun Anda.</p>
    </div>

    <div class="row g-4">
        <!-- Left Column: Master Profile Card -->
        <div class="col-lg-4 col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden text-center bg-white">
                <div class="card-body p-4 p-md-5">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mb-4 profile-avatar">
                        <i class="fa-solid <?= $avatarIcon ?>"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2"><?= htmlspecialchars($userData['nama_lengkap']) ?></h5>
                    <div class="mb-4">
                        <span class="badge px-3 py-2 rounded-pill fw-semibold" style="background-color: rgba(125, 10, 10, 0.1); color: #7D0A0A;">
                            <i class="fa-solid fa-user-shield me-1"></i> <?= $roleLabel ?>
                        </span>
                    </div>
                    
                    <?php if(!empty($userData['kecamatan'])): ?>
                    <p class="text-muted small mb-4"><i class="fa-solid fa-location-dot me-1" style="color: #7D0A0A;"></i> Kec. <?= htmlspecialchars($userData['kecamatan']) ?></p>
                    <?php endif; ?>
                    
                    <div class="border-top pt-4 text-start small">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted"><i class="fa-solid fa-at me-2"></i>Username</span>
                            <span class="fw-bold text-dark"><?= htmlspecialchars($userData['username']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted"><i class="fa-solid fa-shield-check me-2"></i>Status Akun</span>
                            <span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill"><i class="fa-solid fa-circle-check me-1"></i> Aktif</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Forms -->
        <div class="col-lg-8 col-12">
            <form id="formProfileUser">
                <!-- Card 1: Informasi Akun -->
                <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
                    <div class="card-body p-4 p-md-5">
                        <h5 class="card-title-profile"><i class="fa-solid fa-id-card me-2"></i>Informasi Akun</h5>
                        
                        <div class="row g-4">
                            <div class="col-md-6 col-12">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" name="nama_lengkap" value="<?= htmlspecialchars($userData['nama_lengkap']) ?>" placeholder="Masukkan nama lengkap" required>
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($userData['username']) ?>" placeholder="Masukkan username" required>
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label">No. WhatsApp / Telepon</label>
                                <input type="text" class="form-control" name="no_hp" value="<?= htmlspecialchars($userData['no_hp'] ?? '') ?>" placeholder="08xxxxxxxxxx">
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label">Kecamatan</label>
                                <input type="text" class="form-control" name="kecamatan" value="<?= htmlspecialchars($userData['kecamatan'] ?? '') ?>" placeholder="Nama kecamatan">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Alamat Lengkap</label>
                                <textarea class="form-control" name="alamat_lengkap" placeholder="Detail alamat" style="height: 100px"><?= htmlspecialchars($userData['alamat_lengkap'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Keamanan Akun -->
                <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
                    <div class="card-body p-4 p-md-5">
                        <h5 class="card-title-profile"><i class="fa-solid fa-lock me-2"></i>Keamanan Akun</h5>
                        <p class="text-muted small mb-4">Kosongkan kolom password di bawah ini jika Anda tidak ingin mengubah kata sandi.</p>
                        
                        <div class="row g-4">
                            <div class="col-md-6 col-12">
                                <label class="form-label">Password Baru</label>
                                <input type="password" class="form-control" name="password" placeholder="Password baru">
                            </div>
                            <div class="col-md-6 col-12">
                                <label class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" class="form-control" name="password_confirm" placeholder="Ulangi password baru">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-end mt-2 mb-4">
                    <button type="submit" class="btn btn-danger px-5 py-2 fw-bold shadow-sm rounded-pill btn-save-profile">
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
