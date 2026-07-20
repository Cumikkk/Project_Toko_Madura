<?php

use App\Models\Helper;
use App\Models\User;
use Carbon\Carbon;

$user = User::user();
if(!$user) {
    die("<script>alert('Invalid Code'); location.href='/';</script>");
}

$uniqueCode = Helper::form_input($_GET['b'] ?? "");
if($uniqueCode != md5(md5($user['MBR_ID'] . $user['ID_MBR']))) {
    User::logout();
    die("<script>alert('Invalid Otp Code'); location.href='/';</script>");
}

$split_email = str_split($user['MBR_EMAIL']);
$post_at = strpos($user['MBR_EMAIL'], "@");
$mask_email = Helper::mask_email($user['MBR_EMAIL']);

$expiredSecond = 0;
if(!empty($user['MBR_OTP_EXPIRED'])) {
    $expiredSecond = Carbon::now()->floorSeconds()->diffInSeconds(Carbon::parse($user['MBR_OTP_EXPIRED']), false);
    if($expiredSecond <= 0) {
        $expiredSecond = 0;
    }
}
?>
<!-- main content start -->
<div class="main-content login-panel two-factor-panel">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6">
                <div class="text-lg-start text-center logo mb-4">
                    <img src="/assets/images/logo-full-first-state-futures.svg" alt="logo">
                </div>
                <p class="text-lg-start text-center mb-lg-0 mb-4">It's the Bright One, it's the Right One, that's Business.</p>
            </div>
            <div class="col-lg-6">
                <div class="static-body">
                    <div class="panel bg-transparent">
                        <div class="panel-body">
                            <div class="part-img w-25 m-auto mb-lg-5 mb-4 px-lg-4">
                                <img src="/assets/images/phone.png" alt="image">
                            </div>
                            <div class="part-txt text-center">
                                <h2>Two-Factor Verification</h2>
                                <p class="mb-2">Enter the verification code we sent to</p>
                                <p class="fw-semibold fs-5 mb-0">
                                    <?php echo $mask_email ?>
                                </p>
                                <p class="mt-0 mb-0">or</p>
                                <p class="fw-semibold fs-5 mb-lg-4 mb-0">
                                    <?= Helper::mask_string($user['MBR_PHONE'], 3, 2); ?>
                                </p>
                            </div>
                            <div class="verification-area text-center">
                                <div id="otp_target"></div>
                                <p class="mb-4">Type your 4 digit security code</p>
                                <div class="d-flex gap-2 align-items-center justify-content-center flex-wrap">
                                    <button type="button" id="resend_code_email" class="btn btn-sm btn-primary" disabled data-type="email" data-text="Resend code" data-second="<?= $expiredSecond ?>">
                                        <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M3.75 5.25L3 6V18L3.75 18.75H20.25L21 18V6L20.25 5.25H3.75ZM4.5 7.6955V17.25H19.5V7.69525L11.9999 14.5136L4.5 7.6955ZM18.3099 6.75H5.68986L11.9999 12.4864L18.3099 6.75Z" fill="#ffffff"></path> </g></svg>
                                        <span class="text">
                                            Resend Code
                                        </span>
                                    </button>
                                    <a href="/logout" id="logout" class="btn btn-secondary"><i class="fa fa-power-off"></i> Logout</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- main content end -->

<script src="/assets/vendor/js/otpdesigner.min.js"></script>
<script type="text/javascript">
    function otpConfimationStore() {
        // let buttonResendCodeWhatsapp = document.getElementById('resend_code_whatsapp');
        let buttonResendCodeEmail = document.getElementById('resend_code_email');
        let expiredSecond = <?= $expiredSecond ?> || 0;

        function resendCodePost(type) {
            Swal.fire({
                text: "Loading...",
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            })

            $.ajax({
                url: "/ajax/auth/resend-otp",
                type: "post",
                dataType: "json",
                data: {
                    code: '<?= $uniqueCode ?>',
                    type: type
                },
                success: function(resp) {
                    Swal.fire(resp.alert);
                    if(resp.success) {
                        expiredSecond = resp.data.expiredSecond;
                        // buttonResendCodeWhatsapp.dataset.second = expiredSecond;
                        buttonResendCodeEmail.dataset.second = expiredSecond;
                    }
                },
                error: function() {
                    Swal.fire("Failed", "An error occurred while processing your request", "error");
                }
            })
        }

        function setCountdown() {
            if(expiredSecond <= 0) {
                // buttonResendCodeWhatsapp.removeAttribute('disabled');
                // buttonResendCodeWhatsapp.querySelector('span').textContent = 'Resend Code';
                buttonResendCodeEmail.removeAttribute('disabled');
                buttonResendCodeEmail.querySelector('span').textContent = 'Resend Code';
                return;    

            }

            let time = formatTime(expiredSecond);
            expiredSecond--;
            // buttonResendCodeWhatsapp.setAttribute('disabled', 'disabled');
            // buttonResendCodeWhatsapp.querySelector('span').textContent = `Resend Code (${time})`;
            buttonResendCodeEmail.setAttribute('disabled', 'disabled');
            buttonResendCodeEmail.querySelector('span').textContent = `Resend Code (${time})`;
        }

        function formatTime(sec) {
            var m = Math.floor(sec / 60);
            var s = sec % 60;
            return (m < 10 ? '0' + m : m) + ':' + (s < 10 ? '0' + s : s);
        }
        
        return {
            // buttonResendCodeWhatsapp,
            buttonResendCodeEmail,
            resendCodePost,
            setCountdown,
            formatTime
        }
    }

    $(document).ready(function() {
        const otpConfirmation = otpConfimationStore();
        [otpConfirmation.buttonResendCodeEmail].forEach(button => {
            button.addEventListener('click', function() {
                const type = this.dataset.type;
                otpConfirmation.resendCodePost(type);
            })
        })

        $('#otp_target').otpdesigner({
            length: 4,
            onlyNumbers: true,
            typingDone: function(code) {
                Swal.fire({
                    text: "Please wait...",
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                })

                $.post("/ajax/auth/otp-verification", {code: '<?= $uniqueCode ?>', otp: code}, (resp) => {
                    Swal.fire(resp.alert).then(() => {
                        if(resp.success) {
                            location.href = resp.data.redirect;
                        }
                    })
                }, 'json');
            }
        })

        setInterval(() => {
            otpConfirmation.setCountdown();
        }, 1000);
    })
</script>
