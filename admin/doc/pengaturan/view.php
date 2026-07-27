<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();

// Fetch settings
$settings = $db->query("SELECT * FROM pengaturan_sistem");
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Pengaturan Sistem</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Pengaturan</li>
        </ol>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-6 mx-auto">
        <div class="card custom-card">
            <div class="card-header">
                <h6 class="main-content-label mb-1">Konfigurasi Potongan & Bagi Hasil</h6>
                <p class="text-muted card-sub-title">Atur nilai parameter global untuk kalkulasi bagi hasil Toko Madura.</p>
            </div>
            <div class="card-body">
                <form id="form-settings">
                    <?php if ($settings && $settings->num_rows > 0) : ?>
                        <?php while ($row = $settings->fetch_assoc()) : ?>
                            <?php 
                                $isCurrency = strpos($row['nama_pengaturan'], 'biaya') !== false;
                            ?>
                            <div class="form-group mb-3">
                                <label class="fw-bold mb-1"><?= ucwords(str_replace('_', ' ', $row['nama_pengaturan'])) ?></label>
                                <div class="input-group">
                                    <?php if ($isCurrency) : ?>
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" step="1" class="form-control" name="settings[<?= $row['nama_pengaturan'] ?>]" value="<?= number_format($row['nilai'], 0, '', '') ?>" required />
                                    <?php else : ?>
                                        <input type="number" step="0.01" class="form-control" name="settings[<?= $row['nama_pengaturan'] ?>]" value="<?= $row['nilai'] ?>" required />
                                        <span class="input-group-text">%</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <p class="text-muted text-center">Belum ada pengaturan sistem di database.</p>
                    <?php endif; ?>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary btn-save">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#form-settings').on('submit', function(event) {
            event.preventDefault();
            
            Swal.fire({
                text: "Menyimpan...",
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            });

            $.post("/ajax/post/pengaturan/update", $(this).serialize(), function(resp) {
                Swal.fire(resp.alert).then(() => {
                    if (resp.success) {
                        location.reload();
                    }
                });
            }, 'json');
        });
    });
</script>
