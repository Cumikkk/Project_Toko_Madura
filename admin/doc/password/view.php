<?php 
use Config\Core\SystemInfo;
?>
<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Ubah Password</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);">Profil</a></li>
            <li class="breadcrumb-item active" aria-current="page">Ubah Password</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-md-10 mx-auto mb-3">
        <div class="card custom-card overflow-hidden">
            <div class="card-header">
                <div class="d-flex justify-content-between mb-2">
                    <h5 class="card-title">Form Ubah Password</h5>
                </div>
            </div>
            <div class="card-body">
                <form method="post" id="cpassform">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label class="form-label fw-bold">Password Lama <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="pass01" required autocomplete="off" placeholder="Masukkan password lama">
                            </div>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label class="form-label fw-bold">Password Baru <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="pass02" required autocomplete="off" placeholder="Masukkan password baru" pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[^a-zA-Z0-9])(?=.*^[^'\x22\\<>]*$).{8,}$" title="Minimal 1 digit angka, 1 huruf kecil, 1 huruf besar, dan 1 karakter spesial. Dan Panjang text minimal harus sebanyak 8 karakter">
                                <small class="text-muted d-block mt-1">Password minimal 8 karakter, kombinasi huruf besar (A-Z), huruf kecil (a-z), angka (0-9), dan karakter spesial.</small>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label class="form-label fw-bold">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="pass03" required autocomplete="off" placeholder="Masukkan ulang password baru">
                            </div>
                        </div>

                        <div class="col-md-12 mt-3 d-flex justify-content-end gap-2">
                            <a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary" name="submit_password">Simpan Perubahan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(() => {
        $('#cpassform').on('submit', function(e){
            e.preventDefault();
            let button = $(this).find('button[type="submit"]'), 
                data = Object.fromEntries(new FormData(this).entries());

            // Validasi di frontend
            if (data.pass02 !== data.pass03) {
                Swal.fire({
                    icon: 'error',
                    title: 'Perhatian!',
                    text: 'Konfirmasi password baru tidak cocok!'
                });
                return;
            }
                
            button.addClass('loading').prop('disabled', true);
            $.post("<?= SystemInfo::app('ADMIN_URL') ?>/ajax/post/password/update", data, function(resp) {
                button.removeClass('loading').prop('disabled', false);
                Swal.fire({
                    icon: resp.success ? "success" : "error", 
                    title: resp.success ? "Berhasil!" : "Perhatian!", 
                    text: resp.message
                }).then(() => {
                    if(resp.success) {
                        location.reload();
                    }
                });
            }, 'json').fail(function() {
                button.removeClass('loading').prop('disabled', false);
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan!',
                    text: 'Terjadi kesalahan koneksi server.'
                });
            });
        });
    });
</script>