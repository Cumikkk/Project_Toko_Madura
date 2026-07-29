<?php
use Config\Core\SystemInfo;
use Config\Core\Database;

$db = Database::connect();

// Fetch current setting
$resFee = $db->query("SELECT nilai FROM pengaturan_sistem WHERE nama_pengaturan = 'biaya_langganan_outlet' LIMIT 1");
$biayaLangganan = 100000.00;
if ($resFee && $resFee->num_rows > 0) {
    $biayaLangganan = (float)$resFee->fetch_assoc()['nilai'];
}
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Pengaturan Biaya Langganan</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item"><a href="#">Pengaturan</a></li>
            <li class="breadcrumb-item active" aria-current="page">Biaya Langganan</li>
        </ol>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-6 col-md-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="d-flex justify-content-between mb-2">
                    <h5 class="card-title">Tarif Biaya Langganan Outlet</h5>
                </div>
            </div>
            <div class="card-body">
                <form id="form-biaya-langganan" method="POST">
                    <input type="hidden" name="setting_type" value="biaya_langganan">
                    <div class="form-group mb-3">
                        <label class="form-label font-weight-semibold">Nominal Biaya Langganan Bulanan (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" step="1" min="0" name="biaya_langganan_outlet" class="form-control" value="<?= (int)$biayaLangganan ?>" placeholder="100000" required>
                        </div>
                        <small class="text-muted mt-1 d-block">Tarif ini berlaku sebagai biaya langganan bulanan default untuk seluruh outlet terdaftar.</small>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary px-4">Simpan Biaya Langganan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $('#form-biaya-langganan').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        var btn = $(this).find('button[type="submit"]');

        btn.prop('disabled', true);
        Swal.fire({
            title: 'Memproses...',
            text: 'Sedang menyimpan biaya langganan',
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
