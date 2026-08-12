<?php
use App\Models\Outlet;
use App\Models\Investor;
use Config\Core\SystemInfo;

$idOutlet = intval($_GET['id'] ?? ($_GET['c'] ?? 0));
$isEdit = ($idOutlet > 0);

$outletData = null;

if ($isEdit) {
    $outletData = Outlet::getOutletById($idOutlet);
    if (!$outletData) {
        $isEdit   = false;
        $idOutlet = 0;
    }
}

// Check if current outlet is expired or non-active/inactive
$isExpiredOrInactive = false;
if ($isEdit && !empty($outletData)) {
    $st = strtolower($outletData['status'] ?? '');
    $jt = $outletData['tgl_jatuh_tempo'] ?? '';
    if ($st === 'inactive' || $st === 'expired') {
        $isExpiredOrInactive = true;
    } elseif (!empty($jt) && strtotime(date('Y-m-d', strtotime($jt))) < strtotime(date('Y-m-d'))) {
        $isExpiredOrInactive = true;
    }
}

$requiredPermission = $isEdit ? "update" : "create";
if (!$adminPermissionCore->isHavePermission($moduleId, $requiredPermission)) {
    $redirectUrl = SystemInfo::app('ADMIN_URL') . '/outlet/view';
    die("<script>location.href = '{$redirectUrl}'; </script>");
}

// Fetch list of Investors
$loggedInLevel = intval($user['ADM_LEVEL'] ?? 1);
$loggedInId    = intval($user['ADM_ID'] ?? 1);
$investorList = Investor::getAllInvestors($loggedInLevel, $loggedInId);
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5"><?= $isEdit ? "Edit Data Outlet" : "Registrasi Outlet Baru"; ?></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/outlet/view">Outlet</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $isEdit ? "Edit Data" : "Registrasi"; ?></li>
        </ol>
    </div>
</div>

<form action="" method="post" id="form-create-outlet">
    <?php if ($isEdit) : ?>
        <input type="hidden" name="id_outlet" value="<?= $idOutlet; ?>">
        <input type="hidden" name="id_users_kasir" value="<?= $outletData['id_users']; ?>">
    <?php endif; ?>

    <div class="row">
        <!-- KIRI: INFORMASI OUTLET & FINANSIAL -->
        <div class="col-lg-6 mb-3">
            <div class="card custom-card h-100 mb-0">
                <div class="card-header">
                    <div class="d-flex justify-content-between mb-2">
                        <h5 class="card-title">Informasi Outlet</h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
<div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="id_investor" class="form-label fw-bold">Investor <span class="text-danger">*</span></label>
                                <select class="form-control" id="id_investor" name="id_investor" required>
                                    <option value="" disabled <?= empty($outletData['id_investor']) ? 'selected' : ''; ?>>-- Pilih Investor --</option>
                                    <?php if ($investorList && $investorList->num_rows > 0) : ?>
                                        <?php while ($inv = $investorList->fetch_assoc()) : ?>
                                            <option value="<?= $inv['id_investor']; ?>" <?= (($outletData['id_investor'] ?? 0) == $inv['id_investor']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($inv['nama_lengkap']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                                <small class="text-muted">Pilih investor yang menaungi outlet ini.</small>
                            </div>
                        </div>
<div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="nama_outlet" class="form-label fw-bold">Nama Outlet <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_outlet" name="nama_outlet"
                                    placeholder="Contoh: Toko Madura Waru"
                                    value="<?= htmlspecialchars($outletData['nama_outlet'] ?? ''); ?>" required>
                            </div>
                        </div>
<div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="kecamatan" class="form-label fw-bold">Kecamatan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="kecamatan" name="kecamatan"
                                    placeholder="Contoh: Waru"
                                    value="<?= htmlspecialchars($outletData['kecamatan'] ?? ''); ?>" required>
                            </div>
                        </div>
<!-- 6. ALAMAT LENGKAP -->
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="alamat_outlet" class="form-label fw-bold">Alamat Lengkap Outlet <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="alamat_outlet" name="alamat_outlet" rows="3"
                                    placeholder="Contoh: Jl. Raya Waru No. 123, RT 02 / RW 05, Sidoarjo"><?= htmlspecialchars($outletData['alamat_outlet'] ?? ''); ?></textarea>
                            </div>
                        </div>
<?php if ($isExpiredOrInactive) : ?>
                            <!-- 5. TANGGAL JATUH TEMPO (PERPANJANGAN MASA LANGGANAN) -->
                            <div class="col-md-12 mb-3">
                                <div class="form-group">
                                    <label for="tgl_jatuh_tempo" class="form-label fw-bold">Tanggal Jatuh Tempo (Masa Langganan Aktif)</label>
                                    <input type="date" class="form-control" id="tgl_jatuh_tempo" name="tgl_jatuh_tempo"
                                        value="<?= htmlspecialchars(!empty($outletData['tgl_jatuh_tempo']) ? date('Y-m-d', strtotime($outletData['tgl_jatuh_tempo'])) : date('Y-m-d', strtotime('+1 month'))); ?>">
                                    <small class="text-muted d-block mt-1">Ubah tanggal ini ke tanggal mendatang untuk memperpanjang masa aktif outlet tanpa perlu membuat akun baru lagi.</small>
                                </div>
                            </div>
                        <?php endif; ?>
<?php
                        $defaultInvestor = (float)($outletData['persentase_hak_investor'] ?? 50.00);
                        $defaultOutlet = 100.00 - $defaultInvestor;
                        ?>
                        <div class="col-md-4 mb-3">
                            <div class="form-group">
                                <label for="persentase_potongan" class="form-label fw-bold">Potongan Omzet (%) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.5" min="0" max="100" class="form-control fw-bold" id="persentase_potongan" name="persentase_potongan"
                                        placeholder="Contoh: 10.00"
                                        value="<?= htmlspecialchars($outletData['persentase_potongan'] ?? '10.00'); ?>" required>
                                    <span class="input-group-text border-end-0">%</span>
                                    <div class="input-group-text p-0 border-start-0 overflow-hidden bg-body-tertiary">
                                        <div class="d-flex flex-column h-100" style="width: 24px;">
                                            <button type="button" class="btn btn-sm btn-light border-0 rounded-0 py-0 px-1 text-body-secondary flex-fill d-flex align-items-center justify-content-center" onclick="stepAdminPotongan(1)" style="font-size: 10px; line-height: 1; padding: 2px;" title="Tambah (+1%)">
                                                <i class="fas fa-chevron-up"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border-0 border-top rounded-0 py-0 px-1 text-body-secondary flex-fill d-flex align-items-center justify-content-center" onclick="stepAdminPotongan(-1)" style="font-size: 10px; line-height: 1; padding: 2px;" title="Kurangi (-1%)">
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted" style="font-size: 11px;">Potongan harian dari omzet kotor.</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-group">
                                <label for="persentase_hak_investor" class="form-label fw-bold">Hak Investor (%) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.5" min="0" max="100" class="form-control fw-bold" id="persentase_hak_investor" name="persentase_hak_investor"
                                        placeholder="Contoh: 50.00"
                                        value="<?= htmlspecialchars($defaultInvestor); ?>" required oninput="balanceAdminOutletSplit('investor')">
                                    <span class="input-group-text border-end-0">%</span>
                                    <div class="input-group-text p-0 border-start-0 overflow-hidden bg-body-tertiary">
                                        <div class="d-flex flex-column h-100" style="width: 24px;">
                                            <button type="button" class="btn btn-sm btn-light border-0 rounded-0 py-0 px-1 text-body-secondary flex-fill d-flex align-items-center justify-content-center" onclick="stepAdminInvestor(1)" style="font-size: 10px; line-height: 1; padding: 2px;" title="Tambah (+1%)">
                                                <i class="fas fa-chevron-up"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border-0 border-top rounded-0 py-0 px-1 text-body-secondary flex-fill d-flex align-items-center justify-content-center" onclick="stepAdminInvestor(-1)" style="font-size: 10px; line-height: 1; padding: 2px;" title="Kurangi (-1%)">
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted" style="font-size: 11px;">Hak Investor.</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-group">
                                <label for="persen_bagian_outlet" class="form-label fw-bold">Hak Outlet (%) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.5" min="0" max="100" class="form-control fw-bold" id="persen_bagian_outlet" name="persen_bagian_outlet"
                                        placeholder="Contoh: 50.00"
                                        value="<?= htmlspecialchars($defaultOutlet); ?>" required oninput="balanceAdminOutletSplit('outlet')">
                                    <span class="input-group-text border-end-0">%</span>
                                    <div class="input-group-text p-0 border-start-0 overflow-hidden bg-body-tertiary">
                                        <div class="d-flex flex-column h-100" style="width: 24px;">
                                            <button type="button" class="btn btn-sm btn-light border-0 rounded-0 py-0 px-1 text-body-secondary flex-fill d-flex align-items-center justify-content-center" onclick="stepAdminOutlet(1)" style="font-size: 10px; line-height: 1; padding: 2px;" title="Tambah (+1%)">
                                                <i class="fas fa-chevron-up"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border-0 border-top rounded-0 py-0 px-1 text-body-secondary flex-fill d-flex align-items-center justify-content-center" onclick="stepAdminOutlet(-1)" style="font-size: 10px; line-height: 1; padding: 2px;" title="Kurangi (-1%)">
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted" style="font-size: 11px;">Hak outlet.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KANAN: AKUN AKSES PENGELOLA OUTLET -->
        <div class="col-lg-6 mb-3">
            <div class="card custom-card h-100 mb-0">
                <div class="card-header">
                    <div class="d-flex justify-content-between mb-2">
                        <h5 class="card-title">Akun Pengelola Outlet</h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="kasir_nama" class="form-label fw-bold">Nama Pengelola Outlet <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="kasir_nama" name="kasir_nama"
                                    placeholder="Contoh: Budi Santoso"
                                    value="<?= htmlspecialchars($outletData['kasir_nama'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="kasir_no_hp" class="form-label fw-bold">No. HP / WhatsApp <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="kasir_no_hp" name="kasir_no_hp"
                                    placeholder="Contoh: 081234567890"
                                    value="<?= htmlspecialchars($outletData['kasir_no_hp'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="kasir_username" class="form-label fw-bold">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="kasir_username" name="kasir_username"
                                    placeholder="Contoh: kasir_waru"
                                    value="<?= htmlspecialchars($outletData['kasir_username'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="kasir_password" class="form-label fw-bold">Password <?= $isEdit ? '(Opsional)' : '<span class="text-danger">*</span>'; ?></label>
                                <input type="password" class="form-control" id="kasir_password" name="kasir_password"
                                    placeholder="<?= $isEdit ? 'Biarkan kosong jika tidak diubah' : 'Masukkan password login'; ?>"
                                    <?= $isEdit ? '' : 'required'; ?>>
                                <small class="text-muted d-block mt-1">Password minimal 8 karakter, kombinasi huruf besar (A-Z), huruf kecil (a-z), dan angka (0-9).</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TOMBOL AKSI -->
        <div class="col-12 mt-3 d-flex justify-content-end gap-2 mb-4">
            <a href="<?= SystemInfo::app('ADMIN_URL') ?>/outlet/view" class="btn btn-secondary px-4">Batal</a>
            <button type="submit" class="btn btn-primary px-4" data-original-text="Submit">
                <i class="fas fa-save me-1"></i> <?= $isEdit ? "Simpan Perubahan" : "Simpan Outlet"; ?>
            </button>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function() {
        // Auto populate Bagi Hasil Investor when selecting an investor
        $('#id_investor').on('change', function() {
            let selectedOption = $(this).find('option:selected');
            let persen = selectedOption.data('persen');
            if (persen !== undefined && persen !== '') {
                $('#persentase_hak_investor').val(persen);
                window.balanceAdminOutletSplit('investor');
            }
        });

        window.balanceAdminOutletSplit = function(source) {
            if (source === 'investor') {
                const invVal = parseFloat($('#persentase_hak_investor').val());
                if (!isNaN(invVal)) {
                    const outVal = Math.max(0, 100 - invVal);
                    $('#persen_bagian_outlet').val(outVal.toFixed(2));
                }
            } else {
                const outVal = parseFloat($('#persen_bagian_outlet').val());
                if (!isNaN(outVal)) {
                    const invVal = Math.max(0, 100 - outVal);
                    $('#persentase_hak_investor').val(invVal.toFixed(2));
                }
            }
        };

        window.stepAdminPotongan = function(dir) {
            let el = $('#persentase_potongan');
            let val = parseFloat(el.val()) || 0;
            val = Math.max(0, Math.min(100, val + dir));
            el.val(val.toFixed(2));
        };

        window.stepAdminInvestor = function(dir) {
            let el = $('#persentase_hak_investor');
            let val = parseFloat(el.val()) || 0;
            val = Math.max(0, Math.min(100, val + dir));
            el.val(val.toFixed(2));
            window.balanceAdminOutletSplit('investor');
        };

        window.stepAdminOutlet = function(dir) {
            let el = $('#persen_bagian_outlet');
            let val = parseFloat(el.val()) || 0;
            val = Math.max(0, Math.min(100, val + dir));
            el.val(val.toFixed(2));
            window.balanceAdminOutletSplit('outlet');
        };

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
                        text: resp.message || 'Data outlet berhasil disimpan.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.href = resp.data?.redirect || "<?= SystemInfo::app('ADMIN_URL') ?>/outlet/view";
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Perhatian!',
                        text: resp.message || 'Gagal menyimpan data outlet.'
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
