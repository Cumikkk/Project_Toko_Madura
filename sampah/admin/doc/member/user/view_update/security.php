<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Send Email Reset Password</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-3" role="alert">
                    <small class="d-block mb-0">
                        <i class="fa fa-info-circle"></i> 
                        <i>Mengirim email reset password ke alamat email pengguna yang terdaftar agar pengguna dapat membuat password baru.</i>
                    </small>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <form action="/ajax/post/member/update/security_send_reset_password" method="post" id="form-send-reset-password">
                            <input type="hidden" name="code" value="<?= $userCode ?>">
                            <div class="form-group">
                                <label class="form-control-label required">Admin Password</label>
                                <div class="input-group">
                                    <input type="password" name="password" class="form-control" placeholder="Masukkan Password" required>
                                    <button type="submit" class="btn btn-info">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Send Email OTP Verification</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-3" role="alert">
                    <small class="d-block mb-0">
                        <i class="fa fa-info-circle"></i> 
                        <i>Mengirim ulang email verifikasi OTP kepada pengguna jika email sebelumnya tidak diterima atau sudah kedaluwarsa.</i>
                    </small>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <form action="/ajax/post/member/update/security_send_otp_verification" method="post" id="form-send-otp-verification">
                            <input type="hidden" name="code" value="<?= $userCode ?>">
                            <div class="form-group">
                                <label class="form-control-label required">Admin Password</label>
                                <div class="input-group">
                                    <input type="password" name="password" class="form-control" placeholder="Masukkan Password" required>
                                    <button type="submit" class="btn btn-info">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Check User Password</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-3" role="alert">
                    <small class="d-block mb-0">
                        <i class="fa fa-info-circle"></i> 
                        <i>Memeriksa kecocokan password pengguna untuk kebutuhan verifikasi internal oleh admin.</i>
                    </small>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <form action="/ajax/post/member/update/security_check_password" method="post" id="form-check-password">
                            <input type="hidden" name="code" value="<?= $userCode ?>">
                            <div class="form-group">
                                <label class="form-control-label required">User Password</label>
                                <div class="input-group">
                                    <input type="text" name="password" class="form-control" placeholder="Masukkan Password" required>
                                    <button type="submit" class="btn btn-info">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Update Password</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-3" role="alert">
                    <small class="d-block mb-0">
                        <i class="fa fa-info-circle"></i> 
                        <i>Memperbarui password akun pengguna secara langsung dari panel admin. Gunakan password kuat dan aman.</i>
                    </small>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <form action="/ajax/post/member/update/security_update_password" method="post" id="form-update-password">
                            <input type="hidden" name="code" value="<?= $userCode ?>">
                            <div class="form-group">
                                <div class="d-flex flex-wrap justify-content-between">
                                    <label class="form-control-label required mb-0">New password</label>
                                    <a href="javascript:void(0)" id="randomPassword" data-bs-toggle="tooltip" data-bs-title="Random Password">Generate</a>
                                </div>
                                <div class="input-group">
                                    <input type="text" name="new-password" class="form-control" placeholder="Masukkan Password" required>
                                    <button type="submit" class="btn btn-info">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<hr>
<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Reset OTP Request</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-3" role="alert">
                    <small class="d-block mb-0">
                        <i class="fa fa-info-circle"></i> 
                        <i>Fitur untuk mereset limit permintaan kode OTP pada akun pengguna yang terblokir sementara akibat terlalu banyak permintaan saat verifikasi.</i>
                    </small>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <form action="/ajax/post/member/update/security_reset_otp_request" method="post" id="form-reset-otp-request">
                            <input type="hidden" name="code" value="<?= $userCode ?>">
                            <div class="form-group">
                                <label class="form-control-label required">Admin Password</label>
                                <div class="input-group">
                                    <input type="password" name="password" class="form-control" placeholder="Masukkan Password" required>
                                    <button type="submit" class="btn btn-info">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Send OTP Code</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-2" role="alert">
                    <p class="d-block mb-0 small">
                        <i class="fa fa-info-circle"></i> 
                        <i>Fitur untuk mengirim kode OTP ke email pengguna untuk kebutuhan <strong>Withdrawal</strong>, <strong>Delete Account</strong>, <strong>Update Passcode</strong> dan tindakan lainnya yang memerlukan verifikasi OTP.</i>
                    </p>
                    <p class="d-block mb-0 small text-danger"><i></i></p>
                </div>
                <div class="alert alert-danger mb-3" role="alert">
                    <small class="d-block mb-0">
                        <i class="fa fa-exclamation-triangle"></i> 
                        <i>Pengiriman kode OTP akan menggantikan kode OTP sebelumnya yang masih berlaku, pastikan pengguna menggunakan kode OTP terbaru untuk verifikasi. Pastikan email pengguna aktif dan dapat menerima email, hindari pengiriman terlalu sering untuk mencegah pemblokiran sementara.</i>
                    </small>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <form action="/ajax/post/member/update/security_send_otp_code" method="post" id="form-send-otp-code">
                            <input type="hidden" name="code" value="<?= $userCode ?>">
                            <div class="form-group">
                                <label class="form-control-label required">Admin Password</label>
                                <div class="input-group">
                                    <input type="password" name="password" class="form-control" placeholder="Masukkan Password" required>
                                    <button type="submit" class="btn btn-info">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#randomPassword').on('click', function() {
            $.post("/ajax/post/utils/random_password", {}, (resp) => {
                if(resp.success) {
                    $('input[name="new-password"]').val( resp.data.password )
                }
            }, 'json')
        })

        $('#form-reset-otp-request').on('submit', function(e) {
            e.preventDefault();
            let button = $(this).find('button[type="submit"]');
            button.addClass('loading');

            $.post($(this).attr('action'), $(this).serialize(), (resp) => {
                button.removeClass('loading');
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        location.reload();
                    }
                })
            }, 'json')
        })
        
        $('#form-send-reset-password').on('submit', function(e) {
            e.preventDefault();
            let button = $(this).find('button[type="submit"]');
            button.addClass('loading');

            $.post($(this).attr('action'), $(this).serialize(), (resp) => {
                button.removeClass('loading');
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        location.reload();
                    }
                })
            }, 'json')
        })

        $('#form-send-otp-verification').on('submit', function(e) {
            e.preventDefault();
            let button = $(this).find('button[type="submit"]');
            button.addClass('loading');

            $.post($(this).attr('action'), $(this).serialize(), (resp) => {
                button.removeClass('loading');
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        location.reload();
                    }
                })
            }, 'json')
        })

        $('#form-check-password').on('submit', function(e) {
            e.preventDefault();
            let button = $(this).find('button[type="submit"]');
            button.addClass('loading');

            $.post($(this).attr('action'), $(this).serialize(), (resp) => {
                button.removeClass('loading');
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        location.reload();
                    }
                })
            }, 'json')
        })

        $('#form-update-password').on('submit', function(e) {
            e.preventDefault();
            let button = $(this).find('button[type="submit"]');
            button.addClass('loading');

            $.post($(this).attr('action'), $(this).serialize(), (resp) => {
                button.removeClass('loading');
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        location.reload();
                    }
                })
            }, 'json')
        })

        $('#form-send-otp-code').on('submit', function(e) {
            e.preventDefault();
            let button = $(this).find('button[type="submit"]');
            button.addClass('loading');

            $.ajax({
                url: $(this).attr('action'),
                type: "post",
                dataType: "json",
                data: $(this).serialize(),
                success: function(resp) {
                    Swal.fire(resp.alert).then(() => {
                        if(resp.success) {
                            location.reload();
                        }
                    })
                },
                error: function(error) {
                    Swal.fire('Error', 'an error occurred while processing the request.', 'error');
                }
            })
            .always(() => button.removeClass('loading'))
        })
    })
</script>