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
?>
<div class="main-content-inner py-3">
    <div class="mb-4">
        <h3 class="fw-bold text-dark mb-1">
            <i class="fa-solid fa-user-gear me-2" style="color: #701416;"></i>Profil Saya
        </h3>
        <p class="text-muted mb-0">Kelola informasi data diri dan kata sandi akun Anda.</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-4 col-12">
            <div class="card border-0 shadow-sm rounded-4 text-center p-4">
                <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center text-white mb-3 shadow" style="width: 90px; height: 90px; font-size: 36px; background-color: #701416;">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($userData['nama_lengkap']) ?></h5>
                <span class="badge bg-danger-subtle text-danger px-3 py-1 rounded-pill fw-semibold mb-3"><?= $roleLabel ?></span>
                <?php if(!empty($userData['kecamatan'])): ?>
                <p class="text-muted small mb-3"><i class="fa-solid fa-location-dot me-1"></i> Kec. <?= htmlspecialchars($userData['kecamatan']) ?></p>
                <?php endif; ?>
                <div class="border-top pt-3 text-start small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Username:</span>
                        <span class="fw-bold text-dark"><?= htmlspecialchars($userData['username']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Status Akun:</span>
                        <span class="badge bg-success-subtle text-success">Aktif</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h5 class="fw-bold text-dark mb-4 border-bottom pb-2">Informasi Akun</h5>
                <form id="formProfileUser">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Nama Lengkap</label>
                            <input type="text" class="form-control" name="nama_lengkap" value="<?= htmlspecialchars($userData['nama_lengkap']) ?>" required>
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Username</label>
                            <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($userData['username']) ?>" required>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">No. WhatsApp / Telepon</label>
                            <input type="text" class="form-control" name="no_hp" value="<?= htmlspecialchars($userData['no_hp'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Kecamatan</label>
                            <input type="text" class="form-control" name="kecamatan" value="<?= htmlspecialchars($userData['kecamatan'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Alamat Lengkap</label>
                        <textarea class="form-control" name="alamat_lengkap" rows="2"><?= htmlspecialchars($userData['alamat_lengkap'] ?? '') ?></textarea>
                    </div>

                    <h5 class="fw-bold text-dark mb-3 border-bottom pb-2 pt-2">Ganti Kata Sandi</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Password Baru</label>
                            <input type="password" class="form-control" name="password" placeholder="Kosongkan jika tidak diganti">
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="form-label fw-semibold">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" name="password_confirm" placeholder="Ulangi password baru">
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-danger px-4 py-2 fw-semibold" style="background-color: #701416; border-color: #701416; border-radius: 8px;">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
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
