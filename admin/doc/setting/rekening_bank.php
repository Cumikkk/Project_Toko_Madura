<?php
use Config\Core\SystemInfo;
use Config\Core\Database;

$db = Database::connect();

// Fetch current bank settings
$settings = [];
$res = $db->query("SELECT nama_pengaturan, nilai FROM pengaturan_sistem WHERE nama_pengaturan IN ('bank_nama', 'bank_no_rekening', 'bank_atas_nama')");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $settings[$r['nama_pengaturan']] = $r['nilai'];
    }
}

$bankNama     = $settings['bank_nama'] ?? 'BCA';
$bankNoRek    = $settings['bank_no_rekening'] ?? '123-456-7890';
$bankAtasNama = $settings['bank_atas_nama'] ?? 'Toko Madura Pusat';
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Pengaturan Rekening Bank</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item"><a href="#">Pengaturan</a></li>
            <li class="breadcrumb-item active" aria-current="page">Rekening Bank</li>
        </ol>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-7 col-md-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="d-flex justify-content-between mb-2">
                    <h5 class="card-title">Informasi Rekening Bank Pembayaran</h5>
                </div>
            </div>
            <div class="card-body">
                <form id="form-rekening-bank" method="POST">
                    <input type="hidden" name="setting_type" value="rekening_bank">
                    <div class="form-group mb-3">
                        <label class="form-label font-weight-semibold">Nama Bank <span class="text-danger">*</span></label>
                        <input type="text" name="bank_nama" class="form-control" value="<?= htmlspecialchars($bankNama, ENT_QUOTES) ?>" placeholder="Contoh: BCA / Mandiri / BRI" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label font-weight-semibold">Nomor Rekening <span class="text-danger">*</span></label>
                        <input type="text" name="bank_no_rekening" class="form-control" value="<?= htmlspecialchars($bankNoRek, ENT_QUOTES) ?>" placeholder="Contoh: 123-456-7890" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label font-weight-semibold">Atas Nama Rekening <span class="text-danger">*</span></label>
                        <input type="text" name="bank_atas_nama" class="form-control" value="<?= htmlspecialchars($bankAtasNama, ENT_QUOTES) ?>" placeholder="Contoh: Toko Madura Pusat" required>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary px-4">Simpan Rekening Bank</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Preview Box -->
    <div class="col-lg-5 col-md-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="d-flex justify-content-between mb-2">
                    <h5 class="card-title">Pratinjau Di Investor</h5>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-2">Informasi rekening ini akan ditampilkan pada instruksi transfer pendaftaran outlet di portal investor:</p>
                <div class="p-3 bg-light rounded border" style="border-radius:10px;">
                    <p class="small text-dark mb-0">
                        Transfer ke <strong>Bank <?= htmlspecialchars($bankNama) ?>: <?= htmlspecialchars($bankNoRek) ?></strong> a.n. <strong><?= htmlspecialchars($bankAtasNama) ?></strong>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $('#form-rekening-bank').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        var btn = $(this).find('button[type="submit"]');

        btn.prop('disabled', true);
        Swal.fire({
            title: 'Memproses...',
            text: 'Sedang menyimpan rekening bank',
            allowOutsideClick: false,
            didOpen: function() {
                Swal.showLoading();
            }
        });

        $.post("<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/setting/update", formData, function(resp) {
            btn.prop('disabled', false);
            if (resp.success) {
                Swal.fire('Berhasil!', resp.message, 'success').then(function() {
                    location.reload();
                });
            } else {
                Swal.fire('Gagal!', resp.message || 'Gagal menyimpan pengaturan', 'error');
            }
        }, 'json').fail(function() {
            btn.prop('disabled', false);
            Swal.fire('Error!', 'Gagal terhubung ke server', 'error');
        });
    });
});
</script>
