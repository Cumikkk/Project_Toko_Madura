<div class="row signpages text-center">
    <div class="col-md-12">
        <div class="card">
            <div class="row row-sm">
                <div class="col-lg-12 col-xl-12 col-xs-12 col-sm-12 login_form ">
                    <div class="main-container container-fluid">
                        <div class="row row-sm">
                            <div class="card-body mt-2 mb-2">
                                <div class="clearfix"></div>
                                <form action="" method="post" id="form-login">
                                    <h5 class="text-start mb-2">Signin to Your Account</h5>
                                    <div class="form-group text-start">
                                        <label>Username</label>
                                        <input class="form-control" name="username" placeholder="Enter your username" type="text" autocomplete="off" required>
                                    </div>
                                    <div class="form-group text-start">
                                        <label>Password</label>
                                        <div class="input-group">
                                            <input class="form-control" id="password" name="password" placeholder="Enter your password" type="password" required>
                                            <button class="btn btn-light border" type="button" id="toggle-password" aria-label="Show password">
                                                <i class="fe fe-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-main-primary btn-block text-white">Sign In</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Row -->

<script type="text/javascript">
    $(document).ready(function() {
        $('#toggle-password').on('click', function() {
            const passwordInput = $('#password');
            const icon = $(this).find('i');
            const isHidden = passwordInput.attr('type') === 'password';

            passwordInput.attr('type', isHidden ? 'text' : 'password');
            icon.attr('class', isHidden ? 'fe fe-eye-off' : 'fe fe-eye');
            $(this).attr('aria-label', isHidden ? 'Hide password' : 'Show password');
        });

        $('#form-login').on('submit', function(event) {
            event.preventDefault();

            let button = $(this).find('button[type="submit"]');
            let data = Object.fromEntries(new FormData(this).entries());
            button.addClass('loading');
            $.post("/ajax/auth/signin", data, function(resp) {
                button.removeClass('loading');
                if(!resp.success) {
                    Swal.fire(resp.alert);
                    return false;
                }

                location.href = resp.data.redirect;
            }, 'json')
        })
    })
</script>