<?php
use Config\Core\SystemInfo;
use Config\Core\Database;

$db = Database::connect();

// Fetch current settings
$settings = [];
$res = $db->query("SELECT nama_pengaturan, nilai FROM pengaturan_sistem");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $settings[$r['nama_pengaturan']] = $r['nilai'];
    }
}

$bankNama       = $settings['bank_nama'] ?? 'BCA';
$bankNoRek      = $settings['bank_no_rekening'] ?? '123-456-7890';
$bankAtasNama   = $settings['bank_atas_nama'] ?? 'Toko Madura Pusat';
$biayaLangganan = (float)($settings['biaya_langganan_outlet'] ?? 100000.00);
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Pengaturan Sistem & Rekening Bank</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Pengaturan</li>
        </ol>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-8 col-md-12">
        <div class="card custom-card">
            <div class="card-header border-bottom">
                <h6 class="main-content-label mb-0"><i class="fa fa-cog text-primary me-2"></i>Kelola Pengaturan Sistem</h6>
            </div>
            <div class="card-body">
                <form id="form-setting" method="POST">
                    <h6 class="text-primary font-weight-bold mb-3"><i class="fa fa-university me-2"></i>Informasi Rekening Bank Pembayaran</h6>
                    <div class="row row-sm mb-3">
                        <div class="col-md-4 mb-2">
                            <label class="form-label font-weight-semibold">Nama Bank <span class="text-danger">*</span></label>
                            <input type="text" name="bank_nama" class="form-control" value="<?= htmlspecialchars($bankNama, ENT_QUOTES) ?>" placeholder="Contoh: BCA / Mandiri / BRI" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label font-weight-semibold">Nomor Rekening <span class="text-danger">*</span></label>
                            <input type="text" name="bank_no_rekening" class="form-control" value="<?= htmlspecialchars($bankNoRek, ENT_QUOTES) ?>" placeholder="Contoh: 123-456-7890" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label font-weight-semibold">Atas Nama Rekening <span class="text-danger">*</span></label>
                            <input type="text" name="bank_atas_nama" class="form-control" value="<?= htmlspecialchars($bankAtasNama, ENT_QUOTES) ?>" placeholder="Contoh: Toko Madura Pusat" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary px-4"><i class="fa fa-save me-1"></i> Simpan Pengaturan Rekening</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Preview Card -->
    <div class="col-lg-4 col-md-12">
        <div class="card custom-card">
            <div class="card-header border-bottom">
                <h6 class="main-content-label mb-0"><i class="fa fa-eye text-info me-2"></i>Tampilan Di Portal Investor</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small">Berikut adalah pratinjau bagaimana informasi rekening bank tampil kepada investor saat mendaftarkan outlet:</p>
                
                <div class="p-3 bg-light rounded border border-info" style="border-radius:12px;">
                    <strong class="text-dark small d-block mb-1"><i class="fa fa-university text-primary me-1"></i> Rekening Pembayaran Resmi</strong>
                    <p class="small text-muted mb-0">
                        Transfer ke <strong id="preview-bank">Bank <?= htmlspecialchars($bankNama) ?>: <?= htmlspecialchars($bankNoRek) ?></strong> a.n. <strong id="preview-an"><?= htmlspecialchars($bankAtasNama) ?></strong>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    $('#form-setting').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        var btn = $(this).find('button[type="submit"]');

        btn.prop('disabled', true);
        Swal.fire({
            title: 'Memproses...',
            text: 'Sedang menyimpan pengaturan sistem',
            allowOutsideClick: false,
            didOpen: function() {
                Swal.showLoading();
            }
        });

        $.post("<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/pengaturan/update", formData, function(resp) {
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
