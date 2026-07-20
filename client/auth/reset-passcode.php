<?php

use App\Models\Helper;
use App\Models\MemberPasscode;

$code = Helper::form_input($_GET['b'] ?? "");
if(empty($code)) {
    die("<script>alert('Invalid'); location.href = '/';</script>");
}

$isValidCode = MemberPasscode::findResetCode($code);
$isDeleted = !empty($isValidCode['PASSCODE_DELETED']);
if(!$isValidCode || $isDeleted){
    die("<script>alert('Invalid Code'); location.href = '/';</script>");
}

/** check expired */
if(strtotime($isValidCode['PASSCODE_RESET_EXPIRED']) < time()) {
    die("<script>alert('Reset Code Expired'); location.href = '/';</script>");
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
            <h3 class="panel-title">Reset Passcode</h3>
            <form method="post" id="form-reset-passcode">
                <input type="hidden" name="code" value="<?= $code ?>">
                <div class="input-group mb-25">
                    <span class="input-group-text"><i class="fa-regular fa-lock"></i></span>
                    <input type="password" inputmode="numeric" maxlength="6" autocomplete="one-time-code" required name="passcode" id="passcode" class="form-control" autocomplete="off" placeholder="New Passcode">
				</div>

                <div class="input-group mb-25">
                    <span class="input-group-text"><i class="fa-regular fa-lock"></i></span>
                    <input type="password" inputmode="numeric" maxlength="6" autocomplete="one-time-code" required name="passcode_confirm" id="passcode_confirm" class="form-control" autocomplete="off" placeholder="Confirm the new passcode">
				</div>

                <button type="submit" class="btn btn-primary w-100 login-btn">Reset</button>
            </form>
        </div>
    </div>

    <!-- footer start -->
   <?php require_once __DIR__ . "/footer.php"; ?>
</div>

<script type="text/javascript">
    function setupPinInput(input) {
        input.addEventListener('input', () => {
            input.value = input.value.replace(/\D/g, '').slice(0, 6);
        });

        input.addEventListener('paste', e => {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData)
            .getData('text')
            .replace(/\D/g, '')
            .slice(0, 6);

            input.value = paste;
        });
    }
    
    $(document).ready(function() {
        setupPinInput(document.getElementById('passcode'));
        setupPinInput(document.getElementById('passcode_confirm'));

        $('#form-reset-passcode').on('submit', function(event) {
            event.preventDefault();
            let data = $(this).serialize(),
                button = $(this).find('button[type="submit"]');

            button.addClass('loading');
            $.post("/ajax/auth/reset-passcode", data, (resp) => {
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