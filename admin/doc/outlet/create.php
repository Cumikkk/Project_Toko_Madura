<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();
$idOutlet = intval($_GET['id'] ?? ($_GET['c'] ?? 0));
$isEdit = ($idOutlet > 0);

$outletData = null;
$kasirData  = null;

if ($isEdit) {
    $resOut = $db->query("
        SELECT o.*, u.nama_lengkap as kasir_nama, u.username as kasir_username, u.no_hp as kasir_no_hp
        FROM outlet o
        LEFT JOIN users u ON (u.id_users = o.id_users)
        WHERE o.id_outlet = {$idOutlet} LIMIT 1
    ");
    if ($resOut && $resOut->num_rows > 0) {
        $outletData = $resOut->fetch_assoc();
        $kasirData  = $outletData; // same row, has kasir_ prefixed fields
    } else {
        $isEdit   = false;
        $idOutlet = 0;
    }
}

$requiredPermission = $isEdit ? "update" : "create";
if (!$adminPermissionCore->isHavePermission($moduleId, $requiredPermission)) {
    $redirectUrl = SystemInfo::app('ADMIN_URL') . '/outlet/view';
    die("<script>location.href = '{$redirectUrl}'; </script>");
}

// Fetch list of Investors
$investorList = $db->query("
    SELECT i.id_investor, u.nama_lengkap, i.persen_bagian_investor
    FROM investor i
    JOIN users u ON (u.id_users = i.id_users)
    ORDER BY u.nama_lengkap ASC
");

// Auto-generate kode_outlet
if (empty($outletData['kode_outlet'])) {
    $lastKode = $db->query("SELECT kode_outlet FROM outlet ORDER BY id_outlet DESC LIMIT 1");
    $lastRow  = $lastKode ? $lastKode->fetch_assoc() : null;
    $lastNum  = $lastRow ? intval(substr($lastRow['kode_outlet'], 3)) : 0;
    $nextKode = 'TM-' . sprintf('%03d', $lastNum + 1);
} else {
    $nextKode = $outletData['kode_outlet'];
}
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5"><?= $isEdit ? "Edit Cabang Toko Madura" : "Registrasi Cabang Toko Baru"; ?></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/outlet/view">Outlet</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $isEdit ? "Edit Toko" : "Registrasi Toko"; ?></li>
        </ol>
    </div>
</div>

<form action="" method="post" id="form-create-outlet">
    <?php if ($isEdit) : ?>
        <input type="hidden" name="id_outlet" value="<?= $idOutlet; ?>">
        <input type="hidden" name="id_users_kasir" value="<?= $outletData['id_users']; ?>">
    <?php endif; ?>

    <div class="row">
        <!-- LEFT COLUMN: Data Toko / Cabang -->
        <div class="col-md-6 mb-3">
            <div class="card custom-card overflow-hidden h-100 mb-0">
                <div class="card-header">
                    <h5 class="card-title mb-0">Data Toko / Cabang</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="kode_outlet" class="form-label fw-bold">Kode Outlet</label>
                                <input type="text" class="form-control" id="kode_outlet" name="kode_outlet"
                                    placeholder="Contoh: TM-001"
                                    value="<?= htmlspecialchars($nextKode); ?>" required>
                                <small class="text-muted">Generate otomatis, bisa diubah manual.</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="nama_outlet" class="form-label fw-bold">Nama Toko / Cabang</label>
                                <input type="text" class="form-control" id="nama_outlet" name="nama_outlet"
                                    placeholder="Contoh: Toko Madura Cabang Waru"
                                    value="<?= htmlspecialchars($outletData['nama_outlet'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="id_investor" class="form-label fw-bold">Investor Pemodal</label>
                                <select class="form-control" id="id_investor" name="id_investor" required>
                                    <option value="" disabled <?= empty($outletData['id_investor']) ? 'selected' : ''; ?>>-- Pilih Investor Pemodal --</option>
                                    <?php if ($investorList && $investorList->num_rows > 0) : ?>
                                        <?php while ($inv = $investorList->fetch_assoc()) : ?>
                                            <option value="<?= $inv['id_investor']; ?>" <?= (($outletData['id_investor'] ?? 0) == $inv['id_investor']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($inv['nama_lengkap']); ?> (Bagi Hasil: <?= number_format($inv['persen_bagian_investor'], 0); ?>%)
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="alamat_outlet" class="form-label fw-bold">Alamat Lengkap Toko</label>
                                <textarea class="form-control" id="alamat_outlet" name="alamat_outlet" rows="3"
                                    placeholder="Masukkan alamat lokasi toko cabang"><?= htmlspecialchars($outletData['alamat_outlet'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Data Akun Kasir Pengelola -->
        <div class="col-md-6 mb-3">
            <div class="card custom-card overflow-hidden h-100 mb-0">
                <div class="card-header">
                    <h5 class="card-title mb-0">Akun Kasir Pengelola</h5>
                    <?php if ($isEdit) : ?>
                        <p class="text-muted card-sub-title mb-0 mt-1">Kosongkan password jika tidak ingin mengubah.</p>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="kasir_nama" class="form-label fw-bold">Nama Lengkap Kasir</label>
                                <input type="text" class="form-control" id="kasir_nama" name="kasir_nama"
                                    placeholder="Contoh: Budi Santoso"
                                    value="<?= htmlspecialchars($outletData['kasir_nama'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="kasir_no_hp" class="form-label fw-bold">No. HP Kasir</label>
                                <input type="text" class="form-control" id="kasir_no_hp" name="kasir_no_hp"
                                    placeholder="Contoh: 08123456789"
                                    value="<?= htmlspecialchars($outletData['kasir_no_hp'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="kasir_username" class="form-label fw-bold">Username Login Kasir</label>
                                <input type="text" class="form-control" id="kasir_username" name="kasir_username"
                                    placeholder="Contoh: kasir_waru"
                                    value="<?= htmlspecialchars($outletData['kasir_username'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="kasir_password" class="form-label fw-bold">
                                    Password Kasir <?= $isEdit ? '<span class="text-muted fw-normal">(opsional)</span>' : ''; ?>
                                </label>
                                <input type="password" class="form-control" id="kasir_password" name="kasir_password"
                                    placeholder="<?= $isEdit ? 'Kosongkan jika tidak ubah' : 'Buat password kasir'; ?>"
                                    <?= $isEdit ? '' : 'required'; ?>>
                                <small class="text-muted d-block mt-1">Password minimal 8 karakter, kombinasi huruf besar, huruf kecil, dan angka.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 mt-3 d-flex justify-content-end gap-2">
            <a href="<?= SystemInfo::app('ADMIN_URL') ?>/outlet/view" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary" data-original-text="Submit">
                <?= $isEdit ? "Simpan Perubahan" : "Simpan Toko & Kasir"; ?>
            </button>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function() {
        $('#form-create-outlet').on('submit', function(el) {
            el.preventDefault();
            let button = $(this).find('button[type="submit"]'),
                data   = $(this).serialize();

            button.addClass('loading').prop('disabled', true);
            $.post("<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/outlet/create", data, (resp) => {
                button.removeClass('loading').prop('disabled', false);
                if (resp.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: resp.message || 'Data toko berhasil disimpan.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.href = resp.data?.redirect || "<?= SystemInfo::app('ADMIN_URL') ?>/outlet/view";
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Perhatian!',
                        text: resp.message || 'Gagal menyimpan data toko.'
                    });
                }
            }, 'json').fail(function(xhr) {
                button.removeClass('loading').prop('disabled', false);
                Swal.fire({
                    icon: 'error',
                    title: 'Perhatian!',
                    text: 'Gagal terhubung ke server. Silakan coba lagi.'
                });
            });
        });
    });
</script>
