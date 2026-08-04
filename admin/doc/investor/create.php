<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();
$idInvestor = intval($_GET['id'] ?? ($_GET['c'] ?? 0));
$isEdit = ($idInvestor > 0);

$investorData = null;
if ($isEdit) {
    $resInv = $db->query("
        SELECT i.*, u.nama_lengkap, u.username, u.no_hp
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

// Fetch list of Master Owners
$masterList = $db->query("SELECT id_users, nama_lengkap FROM users WHERE role = 'master' ORDER BY nama_lengkap ASC");
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
                                <label for="nama_lengkap" class="form-label fw-bold">Nama Lengkap Investor</label>
                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" placeholder="Contoh: Haji Ahmad Madura" value="<?= htmlspecialchars($investorData['nama_lengkap'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="no_hp" class="form-label fw-bold">No. HP / WhatsApp (Opsional)</label>
                                <input type="text" class="form-control" id="no_hp" name="no_hp" placeholder="Contoh: 081234567890" value="<?= htmlspecialchars($investorData['no_hp'] ?? ''); ?>">
                            </div>
                        </div>

                        <!-- BARIS 2: USERNAME & PASSWORD -->
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="username" class="form-label fw-bold">Username</label>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Contoh: investor_ahmad" value="<?= htmlspecialchars($investorData['username'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="password" class="form-label fw-bold">Password <?= $isEdit ? "(Opsional)" : ""; ?></label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="<?= $isEdit ? 'Biarkan kosong jika tidak diubah' : 'Masukkan password login'; ?>" <?= $isEdit ? "" : "required"; ?>>
                                <small class="text-muted d-block mt-1">Password minimal 8 karakter, kombinasi huruf besar (A-Z), huruf kecil (a-z), dan angka (0-9).</small>
                            </div>
                        </div>

                        <!-- BARIS 3: MASTER OWNER & KECAMATAN -->
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="id_master" class="form-label fw-bold">Master Owner</label>
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
                                <label for="kecamatan" class="form-label fw-bold">Kecamatan</label>
                                <input type="text" class="form-control" id="kecamatan" name="kecamatan" placeholder="Contoh: Waru" value="<?= htmlspecialchars($investorData['kecamatan'] ?? ''); ?>">
                            </div>
                        </div>

                        <!-- BARIS 4: ALAMAT INVESTOR -->
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="alamat_investor" class="form-label fw-bold">Alamat Investor Lengkap</label>
                                <textarea class="form-control" id="alamat_investor" name="alamat_investor" rows="3" placeholder="Contoh: Jl. Raya Waru No. 123, RT 02 / RW 05, Sidoarjo"><?= htmlspecialchars($investorData['alamat_investor'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <!-- BARIS 5: BIAYA LANGGANAN OUTLET -->
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="biaya_langganan_outlet" class="form-label fw-bold">Nominal Biaya Langganan Outlet (Rp) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" step="1" min="0" class="form-control" id="biaya_langganan_outlet" name="biaya_langganan_outlet" placeholder="100000" value="<?= (int)($investorData['biaya_langganan_outlet'] ?? 100000); ?>" required>
                                </div>
                                <small class="text-muted">Tarif ini akan otomatis berlaku sebagai biaya langganan bulanan untuk seluruh outlet milik investor ini.</small>
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
