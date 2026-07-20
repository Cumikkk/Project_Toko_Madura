<?php 
if($user['MBR_VERIF'] != 1) {
    die("<script>location.href = '/verif/step-1';</script>");
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="panel">
             <div class="panel">
                <div class="panel-body ">
                    <div class="d-flex flex-column align-items-center justify-content-center gap-3">
                        <i class="far fa-check-circle fa-6x mb-3 text-success"></i>
                        <h6 class="text-success">Registrasi Berhasil!</h6>
                        <div class="text-center" style="font-size: 12px;">Akun anda telah berhasil dibuat.<br> Tim Kami akan segera memprosess informasi Anda.</div>
                        <a href="/verif/step-1" class="btn btn-success"><i class="fas fa-home"></i> Lanjut Ke Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>