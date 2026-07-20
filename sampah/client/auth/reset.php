<?php

use App\Models\Helper;
use App\Models\User;

$code = Helper::form_input($_GET['b'] ?? "");
if(empty($code)) {
    die("<script>alert('Invalid'); location.href = '/';</script>");
}

$isValidCode = User::verifyResetCode($code);
if(!$isValidCode){
    die("<script>alert('Invalid Code'); location.href = '/';</script>");
}
?>
<div class="main-content login-panel">
    <div class="login-body">
        <div class="top d-flex justify-content-between align-items-center">
            <div class="logo">
                <img src="/assets/images/logo-full-first-state-futures.svg" alt="Logo">
            </div>
            <a href="/"><i class="fa-duotone fa-house-chimney"></i></a>
        </div>
        <div class="bottom">
            <h3 class="panel-title">Reset Password</h3>
            <form method="post" id="form-reset-password">
                <input type="hidden" name="code" value="<?= $code ?>">
                <div class="input-group mb-25">
                    <span class="input-group-text"><i class="fa-regular fa-lock"></i></span>
                    <input type="password" required name="password" class="form-control" autocomplete="off" placeholder="New password">
					<a role="button" class="password-show"><i class="fa-duotone fa-eye"></i></a>
				</div>

                <div class="input-group mb-25">
                    <span class="input-group-text"><i class="fa-regular fa-lock"></i></span>
                    <input type="password" required name="password_confirm" class="form-control" autocomplete="off" placeholder="Confirm the new password">
					<a role="button" class="password-show"><i class="fa-duotone fa-eye"></i></a>
				</div>

                <button type="submit" class="btn btn-primary w-100 login-btn">Reset</button>
            </form>
        </div>
    </div>

    <!-- footer start -->
   <?php require_once __DIR__ . "/footer.php"; ?>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#form-reset-password').on('submit', function(event) {
            event.preventDefault();
            let data = $(this).serialize(),
                button = $(this).find('button[type="submit"]');

            button.addClass('loading');
            $.post("/ajax/auth/reset-password", data, (resp) => {
                button.removeClass('loading');
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        location.href = '/';   
                    }
                })
            }, 'json')
        })
    })
</script>