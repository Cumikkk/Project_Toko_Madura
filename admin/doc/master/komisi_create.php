<?php
use App\Models\Master;
use Config\Core\SystemInfo;

$idKomisi = isset($_GET['id']) ? intval($_GET['id']) : 0;
$isEdit   = ($idKomisi > 0);
$komisiData = null;

if ($isEdit) {
    $komisiData = Master::getKomisiById($idKomisi);
    if (!$komisiData) {
        $redirectUrl = SystemInfo::app('ADMIN_URL') . '/master/komisi';
        die("<script>alert('Data Komisi tidak ditemukan!'); location.href = '{$redirectUrl}';</script>");
    }
}

// Fetch all Master accounts for dropdown select
$resMasters = Master::getAllMasterOptions();
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
                                <small class="text-muted">Pilih Master Owner sebagai penerima komisi ini.</small>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="tgl_transfer" class="form-label fw-bold">Tanggal Transfer <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="tgl_transfer" name="tgl_transfer" 
                                       value="<?= !empty($komisiData['tgl_transfer']) ? date('Y-m-d\TH:i', strtotime($komisiData['tgl_transfer'])) : date('Y-m-d\TH:i'); ?>" required>
                            </div>
                        </div>

                        <!-- BARIS 2: NOMINAL KOMISI -->

                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="nominal" class="form-label fw-bold">Nominal Komisi (Rp) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text border-end-0">Rp</span>
                                    <input type="number" step="10000" min="0" class="form-control fw-bold border-start-0 border-end-0" id="nominal" name="nominal_transfer_komisi" placeholder="500000" 
                                           value="<?= (int)($komisiData['nominal_transfer_komisi'] ?? 500000); ?>" required>
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
                                <label for="catatan" class="form-label fw-bold">Catatan / Pesan untuk Master <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="catatan" name="catatan" rows="2" placeholder="Contoh: Komisi atas apresiasi keberhasilan memperkenalkan investor baru." required><?= htmlspecialchars($komisiData['catatan'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="bukti_pembayaran" class="form-label fw-bold">Bukti Transfer Komisi <?= $isEdit ? '(Opsional)' : '<span class="text-danger">*</span>'; ?></label>
                                <input type="file" class="form-control" id="bukti_pembayaran" name="bukti_pembayaran" accept="image/*,.pdf" <?= $isEdit ? '' : 'required'; ?>>
                                <small class="text-muted">Upload foto struk transfer / bukti bayar komisi ke Master Owner (Format: JPG, PNG, WEBP, PDF, Maks 5MB).</small>
                                <?php if (!empty($komisiData['bukti_pembayaran'])) : ?>
                                    <?php $fileExt = strtolower(pathinfo($komisiData['bukti_pembayaran'], PATHINFO_EXTENSION)); ?>
                                    <div class="mt-2">
                                        <span class="text-muted fs-13">Bukti saat ini: </span>
                                        <?php if ($fileExt === 'pdf') : ?>
                                            <a href="<?= SystemInfo::app('ADMIN_URL') ?>/image-proxy.php?file=<?= urlencode($komisiData['bukti_pembayaran']) ?>" target="_blank" class="btn btn-xs btn-outline-primary ms-1">
                                                <i class="fas fa-file-pdf me-1"></i> Lihat PDF
                                            </a>
                                        <?php else : ?>
                                            <button type="button" class="btn btn-xs btn-outline-primary ms-1" 
                                                    onclick="previewBuktiKomisi('<?= htmlspecialchars($komisiData['bukti_pembayaran'], ENT_QUOTES) ?>', '<?= htmlspecialchars($komisiData['nama_master'] ?? 'Unknown', ENT_QUOTES) ?>', '<?= htmlspecialchars($komisiData['catatan'] ?? '-', ENT_QUOTES) ?>', 'Rp <?= number_format($komisiData['nominal_transfer_komisi'] ?? 0, 0, ',', '.') ?>')">
                                                <i class="fas fa-image me-1"></i> Lihat Bukti
                                            </button>
                                        <?php endif; ?>
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

<style>
/* Custom Symmetrical Styling for File Input */
#bukti_pembayaran.form-control {
    padding: 0.375rem 0.75rem !important;
    height: 38px !important;
    line-height: 1.5 !important;
    display: flex;
    align-items: center;
    border-radius: 6px;
}
#bukti_pembayaran.form-control::file-selector-button {
    height: 38px;
    margin: -0.375rem 0.75rem -0.375rem -0.75rem;
    border: none;
    border-right: 1px solid var(--bs-border-color, #ced4da);
    background-color: #e9ecef;
    padding: 0 0.85rem;
    font-weight: 600;
    color: #495057;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
}
</style>

<script type="text/javascript">
function previewBuktiKomisi(filePath, namaMaster, catatan, nominal) {
    if (!filePath) {
        Swal.fire('Informasi', 'Bukti pembayaran belum diunggah.', 'info');
        return;
    }
    var adminUrl = '<?= SystemInfo::app("ADMIN_URL") ?>';
    var proxyUrl = adminUrl + '/image-proxy.php?file=' + encodeURIComponent(filePath);

    var infoHtml = '<div class="text-start bg-light p-3 rounded mb-3" style="font-size:13.5px; border:1px solid #e9ecef;">'
        + '<div class="d-flex align-items-center mb-2">'
        + '  <i class="fa fa-user-circle text-primary me-2" style="width:20px; text-align:center;"></i>'
        + '  <span style="min-width:140px;" class="fw-bold">Master Owner:</span>'
        + '  <span class="text-dark fw-semibold">' + (namaMaster || '-') + '</span>'
        + '</div>'
        + '<div class="d-flex align-items-center mb-2">'
        + '  <i class="fa fa-calendar-check-o text-success me-2" style="width:20px; text-align:center;"></i>'
        + '  <span style="min-width:140px;" class="fw-bold">Catatan:</span>'
        + '  <span class="text-dark">' + (catatan || '-') + '</span>'
        + '</div>'
        + '<div class="d-flex align-items-center">'
        + '  <i class="fa fa-money text-warning me-2" style="width:20px; text-align:center;"></i>'
        + '  <span style="min-width:140px;" class="fw-bold">Nominal Komisi:</span>'
        + '  <span class="text-success fw-bold">' + nominal + '</span>'
        + '</div>'
        + '</div>';

    Swal.fire({
        title: '<i class="fa fa-file-text-o me-2 text-info"></i>Bukti Pembayaran Komisi Master',
        html: infoHtml
            + '<img src="' + proxyUrl + '" '
            + 'style="max-width:100%;max-height:60vh;border-radius:8px;border:1px solid #dee2e6;object-fit:contain;" '
            + 'onerror="this.outerHTML=\'<p class=\\\'text-danger mt-2\\\'><i class=\\\'fa fa-exclamation-triangle me-1\\\'></i> Gambar gagal dimuat</p>\'">',
        showCloseButton: true,
        showConfirmButton: false,
        scrollbarPadding: false,
        heightAuto: false,
        width: 640
    });
}

function stepKomisi(amount) {
    let input = $('#nominal');
    let val = parseFloat(input.val()) || 0;
    let nextVal = Math.max(0, val + amount);
    input.val(nextVal);
}

$(document).ready(function() {
    // Override & unbind any legacy template fileselect alert listeners
    $(document).off('fileselect');
    $(':file').off('fileselect');

    // SweetAlert2 Toast Notification on file select (replacing native browser alert)
    $('#bukti_pembayaran').on('change', function() {
        let file = this.files && this.files[0] ? this.files[0] : null;
        if (file) {
            Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            }).fire({
                icon: 'success',
                title: 'File Berkas Dipilih',
                text: file.name
            });
        }
    });
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
                    let isEdit = $('input[name="id_komisi"]').val() ? true : false;
                    let defaultSuccessMsg = isEdit ? 'Data komisi berhasil diperbarui.' : 'Data komisi berhasil ditambahkan.';
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: resp.message || defaultSuccessMsg,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.href = resp.data?.redirect || "<?= SystemInfo::app('ADMIN_URL') ?>/master/komisi";
                    });
                } else {
                    Swal.fire('Gagal!', resp.message || 'Gagal menyimpan data komisi.', 'error');
                }
            },
            error: function() {
                btn.prop('disabled', false);
                Swal.fire('Error!', 'Terjadi kendala pada server (atau sesi Anda habis). Silakan coba lagi.', 'error');
            }
        });
    });
});
</script>
