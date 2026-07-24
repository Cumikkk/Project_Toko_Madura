<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();
$idInvestor = intval($_GET['id'] ?? ($_GET['c'] ?? 0));
$isEdit = ($idInvestor > 0);

$investorData = null;
if ($isEdit) {
    $resInv = $db->query("
        SELECT i.*, u.nama_lengkap, u.username, u.email, u.no_hp
        FROM investor i
        JOIN users u ON (u.id_users = i.id_users)
        WHERE i.id_investor = {$idInvestor}
        LIMIT 1
    ");
    if ($resInv && $resInv->num_rows > 0) {
        $investorData = $resInv->fetch_assoc();
    } else {
        $isEdit = false;
        $idInvestor = 0;
    }
}

$requiredPermission = $isEdit ? "update" : "create";
if (!$adminPermissionCore->isHavePermission($moduleId, $requiredPermission)) {
    $redirectUrl = SystemInfo::app('ADMIN_URL') . '/investor/view';
    die("<script>location.href = '{$redirectUrl}'; </script>");
}
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
    <div class="col-md-8 mx-auto mb-3">
        <div class="card custom-card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?= $isEdit ? "Form Edit Data Investor" : "Form Registrasi Investor"; ?></h5>
            </div>
            <div class="card-body">
                <form action="" method="post" id="form-create-investor">
                    <?php if ($isEdit) : ?>
                        <input type="hidden" name="id_investor" value="<?= $idInvestor; ?>">
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="nama_lengkap" class="form-label fw-bold">Nama Lengkap Investor</label>
                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" placeholder="Masukkan nama lengkap" value="<?= htmlspecialchars($investorData['nama_lengkap'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="username" class="form-label fw-bold">Username</label>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username login" value="<?= htmlspecialchars($investorData['username'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="password" class="form-label fw-bold">Password <?= $isEdit ? "(Opsional)" : ""; ?></label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="<?= $isEdit ? 'Biarkan kosong jika tidak diubah' : 'Masukkan password'; ?>" <?= $isEdit ? "" : "required"; ?>>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="email" class="form-label fw-bold">Email (Opsional)</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Contoh: investor@tokomadura.com" value="<?= htmlspecialchars($investorData['email'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="no_hp" class="form-label fw-bold">No. HP / WhatsApp (Opsional)</label>
                                <input type="text" class="form-control" id="no_hp" name="no_hp" placeholder="Contoh: 08123456789" value="<?= htmlspecialchars($investorData['no_hp'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="alamat_investor" class="form-label fw-bold">Alamat Investor</label>
                                <textarea class="form-control" id="alamat_investor" name="alamat_investor" rows="3" placeholder="Masukkan alamat domisili investor"><?= htmlspecialchars($investorData['alamat_investor'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="persen_bagian_investor" class="form-label fw-bold">Persentase Bagi Hasil Investor (%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="100" class="form-control" id="persen_bagian_investor" name="persen_bagian_investor" placeholder="Contoh: 60.00" value="<?= htmlspecialchars($investorData['persen_bagian_investor'] ?? '60.00'); ?>" required>
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-muted">Persentase porsi keuntungan bersih yang menjadi hak investor ini.</small>
                            </div>
                        </div>
                        <div class="col-md-12 mt-4 d-flex justify-content-end gap-2">
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
    $(document).ready(function() {
        $('#form-create-investor').on('submit', function(el) {
            el.preventDefault();
            let button = $(this).find('button[type="submit"]'), 
                data = $(this).serialize();
                
            button.addClass('loading').prop('disabled', true);
            $.post("<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/investor/create", data, (resp) => {
                button.removeClass('loading').prop('disabled', false);
                if (resp.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: resp.message || 'Data investor berhasil disimpan.',
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
                let errorMsg = 'Gagal terhubung ke server. Silakan coba lagi.';
                if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr && xhr.responseText) {
                    try {
                        let res = JSON.parse(xhr.responseText);
                        if (res.message) errorMsg = res.message;
                    } catch(e) {}
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
