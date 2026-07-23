<?php
use Config\Core\SystemInfo;

if(!$adminPermissionCore->isHavePermission($moduleId, "create")) {
    $redirectUrl = SystemInfo::app('ADMIN_URL') . '/investor/view';
    die("<script>location.href = '{$redirectUrl}'; </script>");
}
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Registrasi Investor Baru</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(1) ?>/investor/view">Investor</a></li>
            <li class="breadcrumb-item active" aria-current="page">Registrasi</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mx-auto mb-3">
        <div class="card custom-card">
            <div class="card-header">
                <h5 class="card-title mb-0">Form Registrasi Investor</h5>
            </div>
            <div class="card-body">
                <form action="" method="post" id="form-create-investor">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="nama_lengkap" class="form-label fw-bold">Nama Lengkap Investor</label>
                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" placeholder="Masukkan nama lengkap" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="username" class="form-label fw-bold">Username</label>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username login" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="password" class="form-label fw-bold">Password</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="email" class="form-label fw-bold">Email (Opsional)</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Contoh: investor@tokomadura.com">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="no_hp" class="form-label fw-bold">No. HP / WhatsApp (Opsional)</label>
                                <input type="text" class="form-control" id="no_hp" name="no_hp" placeholder="Contoh: 08123456789">
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="alamat_investor" class="form-label fw-bold">Alamat Investor</label>
                                <textarea class="form-control" id="alamat_investor" name="alamat_investor" rows="3" placeholder="Masukkan alamat domisili investor"></textarea>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="persen_bagian_investor" class="form-label fw-bold">Persentase Bagi Hasil Investor (%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="100" class="form-control" id="persen_bagian_investor" name="persen_bagian_investor" placeholder="Contoh: 60.00" value="60.00" required>
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-muted">Persentase porsi keuntungan bersih yang menjadi hak investor ini.</small>
                            </div>
                        </div>
                        <div class="col-md-12 mt-4 d-flex justify-content-end gap-2">
                            <a href="<?= pathbreadcrumb(1) ?>/investor/view" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary" data-original-text="Submit">Simpan Investor</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#form-create-investor').on('submit', function(el) {
            el.preventDefault();
            let button = $(this).find('button[type="submit"]'), 
                data = $(this).serialize();
                
            button.addClass('loading');
            $.post("/ajax/post/investor/create", data, (resp) => {
                button.removeClass('loading');
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        location.href = resp.data?.redirect;
                    }
                });
            }, 'json');
        });
    });
</script>
