<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();

$idKomisi = isset($_GET['id']) ? intval($_GET['id']) : 0;
$isEdit   = ($idKomisi > 0);
$komisiData = null;

if ($isEdit) {
    $res = $db->query("SELECT * FROM komisi_master WHERE id_komisi = {$idKomisi} LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $komisiData = $res->fetch_assoc();
    } else {
        $redirectUrl = SystemInfo::app('ADMIN_URL') . '/master/komisi';
        die("<script>alert('Data Komisi tidak ditemukan!'); location.href = '{$redirectUrl}';</script>");
    }
}

// Fetch all Master accounts for dropdown select
$resMasters = $db->query("SELECT id_users, nama_lengkap, username FROM users WHERE role = 'master' ORDER BY nama_lengkap ASC");
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5"><?= $isEdit ? "Edit Komisi Master" : "Tambah Komisi Master Baru"; ?></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item">Master</li>
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/master/komisi">Komisi</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $isEdit ? "Edit Komisi" : "Tambah Komisi"; ?></li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-md-10 mx-auto mb-3">
        <div class="card custom-card overflow-hidden">
            <div class="card-header">
                <div class="d-flex justify-content-between mb-2">
                    <h5 class="card-title"><?= $isEdit ? "Form Edit Komisi Master" : "Form Input Komisi Master Baru"; ?></h5>
                </div>
            </div>
            <div class="card-body">
                <form action="" method="post" id="form-komisi-master">
                    <?php if ($isEdit) : ?>
                        <input type="hidden" name="id_komisi" value="<?= $idKomisi; ?>">
                    <?php endif; ?>

                    <div class="row">
                        <!-- BARIS 1: MASTER OWNER & TANGGAL TRANSFER -->
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="id_master" class="form-label fw-bold">Master Owner <span class="text-danger">*</span></label>
                                <select class="form-control" id="id_master" name="id_master" required>
                                    <option value="" disabled <?= empty($komisiData['id_master']) ? 'selected' : ''; ?>>-- Pilih Master Owner --</option>
                                    <?php if ($resMasters && $resMasters->num_rows > 0) : ?>
                                        <?php while ($m = $resMasters->fetch_assoc()) : ?>
                                            <option value="<?= $m['id_users']; ?>" <?= (($komisiData['id_master'] ?? 0) == $m['id_users']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($m['nama_lengkap']); ?> (@<?= htmlspecialchars($m['username']); ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                                <small class="text-muted">Pilih Master Owner penerima komisi ini.</small>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="tanggal_komisi" class="form-label fw-bold">Tanggal Transfer / Penyerahan <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="tanggal_komisi" name="tanggal_komisi" 
                                       value="<?= !empty($komisiData['tanggal_komisi']) ? date('Y-m-d\TH:i', strtotime($komisiData['tanggal_komisi'])) : date('Y-m-d\TH:i'); ?>" required>
                            </div>
                        </div>

                        <!-- BARIS 2: PERIODE / KETERANGAN & NOMINAL KOMISI -->
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="periode" class="form-label fw-bold">Periode / Keterangan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="periode" name="periode" placeholder="Contoh: Bonus Komisi Rekrutmen Investor Ags 2026" 
                                       value="<?= htmlspecialchars($komisiData['periode'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="nominal" class="form-label fw-bold">Nominal Komisi (Rp) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text border-end-0">Rp</span>
                                    <input type="number" step="10000" min="0" class="form-control fw-bold border-start-0 border-end-0" id="nominal" name="nominal" placeholder="500000" 
                                           value="<?= (int)($komisiData['nominal'] ?? 500000); ?>" required>
                                    <div class="input-group-text p-0 border-start-0 overflow-hidden bg-body-tertiary">
                                        <div class="d-flex flex-column h-100" style="width: 24px;">
                                            <button type="button" class="btn btn-sm btn-light border-0 rounded-0 py-0 px-1 text-body-secondary flex-fill d-flex align-items-center justify-content-center" onclick="stepKomisi(50000)" style="font-size: 10px; line-height: 1; padding: 2px;" title="Tambah (+Rp 50.000)">
                                                <i class="fas fa-chevron-up"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border-0 border-top rounded-0 py-0 px-1 text-body-secondary flex-fill d-flex align-items-center justify-content-center" onclick="stepKomisi(-50000)" style="font-size: 10px; line-height: 1; padding: 2px;" title="Kurangi (-Rp 50.000)">
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- BARIS 3: CATATAN & BUKTI TRANSFER -->
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="catatan" class="form-label fw-bold">Catatan / Pesan untuk Master (Opsional)</label>
                                <textarea class="form-control" id="catatan" name="catatan" rows="2" placeholder="Contoh: Komisi atas apresiasi keberhasilan memperkenalkan investor baru."><?= htmlspecialchars($komisiData['catatan'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="bukti_pembayaran" class="form-label fw-bold">Bukti Transfer Komisi (Opsional)</label>
                                <input type="file" class="form-control" id="bukti_pembayaran" name="bukti_pembayaran" accept="image/*,.pdf">
                                <small class="text-muted">Upload foto struk transfer / bukti bayar komisi ke Master Owner (Format: JPG, PNG, WEBP, PDF, Maks 5MB).</small>
                                <?php if (!empty($komisiData['bukti_pembayaran'])) : ?>
                                    <div class="mt-2">
                                        <span class="text-muted fs-13">Bukti saat ini: </span>
                                        <a href="<?= SystemInfo::app('ADMIN_URL') ?>/<?= htmlspecialchars($komisiData['bukti_pembayaran']) ?>" target="_blank" class="btn btn-xs btn-outline-primary ms-1">
                                            <i class="fas fa-external-link-alt me-1"></i> Lihat Bukti Existing
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-12 mt-3 d-flex justify-content-end gap-2">
                            <a href="<?= SystemInfo::app('ADMIN_URL') ?>/master/komisi" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary" id="btn-submit-komisi"><?= $isEdit ? "Simpan Perubahan" : "Simpan Komisi"; ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
function stepKomisi(amount) {
    let input = $('#nominal');
    let val = parseFloat(input.val()) || 0;
    let nextVal = Math.max(0, val + amount);
    input.val(nextVal);
}

$(document).ready(function() {
    $('#form-komisi-master').on('submit', function(e) {
        e.preventDefault();
        let btn = $('#btn-submit-komisi');
        btn.prop('disabled', true);

        let formData = new FormData(this);

        $.ajax({
            url: "<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/master/komisi",
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(resp) {
                btn.prop('disabled', false);
                if (resp.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: resp.message || 'Data komisi berhasil disimpan.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.href = "<?= SystemInfo::app('ADMIN_URL') ?>/master/komisi";
                    });
                } else {
                    Swal.fire('Gagal!', resp.message || 'Gagal menyimpan data komisi.', 'error');
                }
            },
            error: function() {
                btn.prop('disabled', false);
                Swal.fire('Error!', 'Gagal terhubung ke server.', 'error');
            }
        });
    });
});
</script>
