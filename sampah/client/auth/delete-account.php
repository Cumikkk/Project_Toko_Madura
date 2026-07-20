<div class="main-content login-panel">
    <div class="login-body">
        <div class="top d-flex justify-content-between align-items-center">
            <div class="logo">
                <img src="/assets/images/logo-rrfx3.png" alt="Logo">
            </div>
            <a href="/"><i class="fa-duotone fa-house-chimney"></i></a>
        </div>
        <div class="bottom">
            <h3 class="panel-title">Delete Account</h3>
            <form method="post" id="form-delete-account">
                <div class="input-group mb-25">
                    <span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
                    <input type="email" required name="email" class="form-control" placeholder="email address">
                </div>
                <div class="input-group mb-1">
                    <span class="input-group-text"><i class="fa-regular fa-lock"></i></span>
                    <input type="number" required name="otp" class="form-control" placeholder="Kode OTP">
                </div>
                <a href="javascript:void(0)" id="resendcode" class="float-end mb-3" disabled data-text="Resend code" data-second="0" style="font-size: 12px;">Resend Code</a>
                <button type="submit" name="submit-reset" class="btn btn-primary w-100 login-btn">Submit</button>
            </form>
            <div class="other-option">
                <p class="mb-0">Back to login? <a href="/">Login</a></p>
            </div>
        </div>
    </div>

    <!-- footer start -->
   <?php require_once __DIR__ . "/footer.php"; ?>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#form-delete-account').on('submit', function(event) {
            event.preventDefault();
            let data = $(this).serialize(),
                button = $(this).find('button[type="submit"]');

            button.addClass('loading');
            $.post("/ajax/auth/delete-account", data, (resp) => {
                button.removeClass('loading');
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        location.reload();   
                    }
                })
            }, 'json')
        })

        $('#resendcode').on('click', function() {
            let email = $('input[name="email"]').val();
            if(!email || !email.length) {
                Swal.fire("Gagal", "Kolom email harus diisi", "info");
                die;
            }

            let haveRequestDate = localStorage.getItem(email);
            if(haveRequestDate) {
                diff = (haveRequestDate - new Date());
                diff = Math.floor(diff / 1000);
                if(diff > 0) {
                    $('#resendcode').data('second', diff);
                    return;
                }
            }

            Swal.fire({
                text: "Loading...",
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            })

            $.post("/ajax/auth/resend-otp-delete-account", {email: email}, (resp) => {
                Swal.fire(resp.alert);
                if(resp.success) {
                    localStorage.setItem(email, new Date().getTime() + (1000 * resp.data.expiredSecond));
                    $('#resendcode').data('second', resp.data.expiredSecond);
                }
            }, 'json')
        })

        setInterval(() => {
            setCountdown();
        }, 1000);
    })

    function setCountdown() {
        let second = $('#resendcode')?.data('second') || 0;
        if(second <= 0) {
            $('#resendcode').removeClass('text-muted');
            $('#resendcode').text(`Resend Code`);
            return;    
        }
        
        let time = formatTime(second);
        second--;
        $('#resendcode').addClass('text-muted');
        $('#resendcode').text(`Resend Code (${time})`);
        $('#resendcode').data('second', second);
    }

    function formatTime(sec) {
        var m = Math.floor(sec / 60);
        var s = sec % 60;
        return (m < 10 ? '0' + m : m) + ':' + (s < 10 ? '0' + s : s);
    }
</script>