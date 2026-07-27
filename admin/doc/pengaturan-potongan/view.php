<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();

// Fetch potongan_global setting
$res = $db->query("SELECT nilai FROM pengaturan_sistem WHERE nama_pengaturan = 'potongan_global' LIMIT 1");
$nilai = $res && $res->num_rows > 0 ? (float)$res->fetch_assoc()['nilai'] : 10.00;
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Pengaturan Potongan Global Omzet</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item">Pengaturan</li>
            <li class="breadcrumb-item active" aria-current="page">Potongan Global</li>
        </ol>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-6 mx-auto">
        <div class="card custom-card">
            <div class="card-header">
                <h6 class="main-content-label mb-1">Persentase Potongan Global Omzet</h6>
                <p class="text-muted card-sub-title">Tentukan persentase potongan otomatis yang dikenakan pada setiap laporan omzet outlet Toko Madura.</p>
            </div>
            <div class="card-body">
                <form id="form-setting-potongan">
                    <div class="form-group mb-3">
                        <label class="fw-bold mb-1">Persentase Potongan Global</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" max="100" class="form-control" name="potongan_global" value="<?= number_format($nilai, 2, '.', '') ?>" required />
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted mt-1 d-block">Persentase ini menjadi acuan potongan akhir bulan sebelum omzet bersih dibagihasilkan.</small>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary btn-save"><i class="fas fa-save me-1"></i> Simpan Potongan Global</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#form-setting-potongan').on('submit', function(event) {
            event.preventDefault();
            
            Swal.fire({
                title: "Menyimpan...",
                text: "Memproses pembaruan potongan global omzet",
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            });

            $.post("<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/pengaturan-potongan/update", $(this).serialize(), function(resp) {
                if (resp.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: resp.message || 'Potongan global omzet berhasil diperbarui.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: resp.message || 'Gagal memperbarui potongan global omzet.'
                    });
                }
            }, 'json').fail(function() {
                Swal.fire('Error', 'Gagal terhubung ke server.', 'error');
            });
        });
    });
</script>
