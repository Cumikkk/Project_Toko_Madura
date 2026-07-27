<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();

// Fetch biaya_langganan_outlet setting
$res = $db->query("SELECT nilai FROM pengaturan_sistem WHERE nama_pengaturan = 'biaya_langganan_outlet' LIMIT 1");
$nilai = $res && $res->num_rows > 0 ? (float)$res->fetch_assoc()['nilai'] : 50000.00;
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Pengaturan Biaya Langganan</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item">Pengaturan</li>
            <li class="breadcrumb-item active" aria-current="page">Biaya Langganan</li>
        </ol>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-6 mx-auto">
        <div class="card custom-card">
            <div class="card-header">
                <h6 class="main-content-label mb-1">Nominal Biaya Langganan Outlet</h6>
                <p class="text-muted card-sub-title">Tentukan nominal setoran / biaya langganan per outlet yang harus dibayarkan Investor kepada Admin (Programmer).</p>
            </div>
            <div class="card-body">
                <form id="form-setting-langganan">
                    <div class="form-group mb-3">
                        <label class="fw-bold mb-1">Nominal Biaya Langganan (Per Outlet)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" step="1" min="0" class="form-control" name="biaya_langganan_outlet" value="<?= number_format($nilai, 0, '', '') ?>" required />
                        </div>
                        <small class="text-muted mt-1 d-block">Nominal ini akan menjadi tagihan standar saat Investor mengajukan pendaftaran outlet baru.</small>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary btn-save"><i class="fas fa-save me-1"></i> Simpan Biaya Langganan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#form-setting-langganan').on('submit', function(event) {
            event.preventDefault();
            
            Swal.fire({
                title: "Menyimpan...",
                text: "Memproses pembaruan biaya langganan",
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            });

            $.post("<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/pengaturan-langganan/update", $(this).serialize(), function(resp) {
                if (resp.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: resp.message || 'Biaya langganan berhasil diperbarui.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: resp.message || 'Gagal memperbarui biaya langganan.'
                    });
                }
            }, 'json').fail(function() {
                Swal.fire('Error', 'Gagal terhubung ke server.', 'error');
            });
        });
    });
</script>
