<div class="main-content login-panel">
    <div class="login-body">
        <div class="top d-flex justify-content-between align-items-center">
            <div class="logo">
                <img src="/assets/images/logo-full-first-state-futures.svg" alt="Logo">
            </div>
            <a href="/"><i class="fa-duotone fa-house-chimney"></i></a>
        </div>
        <div class="bottom">
            <h3 class="panel-title">Forgot Password</h3>
            <form method="post" id="form-reset-password">
                <div class="input-group mb-25">
                    <span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
                    <input type="email" required name="email" class="form-control" placeholder="email address">
                </div>
                <button type="submit" name="submit-reset" class="btn btn-primary w-100 login-btn">Send</button>
            </form>
            <div class="other-option">
                <p class="mb-0">Remember the password? <a href="../">Login</a></p>
            </div>
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
            $.post("/ajax/auth/send-reset-password", data, (resp) => {
                button.removeClass('loading');
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        location.reload();   
                    }
                })
            }, 'json')
        })
    })
</script>