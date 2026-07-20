<?php
    use App\Models\User;
?>
<div class="dashboard-breadcrumb mb-25">
    <h2>Security</h2>
</div>

<style>
	.password-show {
		position: absolute;
		top: 50%;
		right: 10px;
		color: #595959;
		-webkit-transform: translateY(-50%);
		transform: translateY(-50%);
		cursor: pointer;
		z-index: 5;
	}
</style>
<div class="row">
    <div class="col-6">
        <div class="panel">
            <div class="card">
                <form action="" method="post" id="form-update-password">
                    <div class="card-header">
                        Password
                    </div>
                    <div class="card-body">
                        <div class="mt-3">
                            <label for="current_pass" class="form-label">Current Password</label>
                            <div class="input-group mb-20">
                                <input required name="current_pass" type="password" id="current_pass" class="form-control" autocomplete="off" placeholder="Password" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9])\S{8,64}$" title="Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character, and be at least 8 characters long.">
                                <span class="input-group-text" style="cursor: pointer;" onclick="togglePassword('current_pass')"><i id="current_passIcon" class="fa-regular fa-eye"></i></span>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label for="regular" class="form-label">New Password</label>
                            <div class="input-group mb-20">
                                <input required name="new_pass" type="password" id="new_pass" class="form-control" autocomplete="off" placeholder="New Password" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9])\S{8,64}$" title="Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character, and be at least 8 characters long.">
                                <span class="input-group-text" style="cursor: pointer;" onclick="togglePassword('new_pass')"><i id="new_passIcon" class="fa-regular fa-eye"></i></span>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label for="confirm_new_pass" class="form-label">Confirm New Password</label>
                            <div class="input-group mb-20">
                                <input required name="confirm_new_pass" type="password" id="confirm_new_pass" class="form-control" autocomplete="off" placeholder="Confirm New Password" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9])\S{8,64}$" title="Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character, and be at least 8 characters long.">
                                <span class="input-group-text" style="cursor: pointer;" onclick="togglePassword('confirm_new_pass')"><i id="confirm_new_passIcon" class="fa-regular fa-eye"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary" name="change_pass">Submit</button>
                        <button type="reset" class="btn btn-danger" name="reset">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-6">
        <!-- <div class="panel mb-3">
            <div class="card">
                <div class="card-header">
                    2FA Auth (Coming Soon)
                </div>
                <div class="card-body">
                    <div class="mt-3">
                        <label for="basicInput" class="form-label">Key</label>
                        <input type="password" class="form-control" autocomplete="off" required>
                    </div>
                    <div class="mt-3">
                        <label for="basicInput" class="form-label">Code</label>
                        <input type="password" class="form-control" autocomplete="off" required>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary" name="submit">Submit</button>
                    <button type="reset" class="btn btn-danger" name="reset">Reset</button>
                </div>
            </div>
        </div> -->
        <div class="panel">
            <form id="delt_acc" method="post">
                <div class="card">
                    <div class="card-header">
                        Delete Account
                    </div>
                    <div class="card-body">
                        <label for="" class="form-control-label">Enter OTP code</label>
                        <div class="form-group mb-3">
                            <div class="input-group">
                                <input type="text" class="form-control" name="otp-code" required readonly>
                                <?php if(User::checkReqDeleteAccount()){ ?>
                                    <a href="javascript:void(0)" id="sendOtp" class="input-group-text" data-bs-toggle="tooltip" data-bs-title="Request OTP For Delete Account" data-bs-original-title="" title="">Request OTP</a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <?php if(User::checkReqDeleteAccount()){ ?>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-danger btn-block" name="submit_dlt">Delete</button>
                        </div>
                    <?php } ?>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    function togglePassword(getid) {
      	const input = document.getElementById(getid);
      	const iconId = document.getElementById(getid+'Icon');
      	if (input.type === "password") {
        	input.type = "text";
			iconId.classList.remove("fa-eye");
			iconId.classList.add("fa-eye-slash");
      	} else {
        	input.type = "password";
			iconId.classList.remove("fa-eye-slash");
			iconId.classList.add("fa-eye");
      	}
    }
</script>

<script type="text/javascript">
    $(document).ready(function() {
        $('#form-update-password').on('submit', function(event) {
            event.preventDefault();
            let data = $(this).serialize();
            let button = $(this).find('button[type="submit"]');

            button.addClass('loading');
            $.post("/ajax/post/profile/update-password", data, (resp) => {
                button.removeClass('loading');
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        location.reload();
                    }
                })
            }, 'json')
        });

        $('#sendOtp').on('click', function(e){
            Swal.fire({
                title: 'Loading',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                    $.post("/ajax/post/profile/delete-otp-send", {}, (resp) => {
                        Swal.fire(resp.alert).then(() => {
                            // if(resp.success) {
                            //     location.reload();
                            // }
                            $(`input[name="otp-code"]`).prop('readonly', false);
                        })
                    }, 'json');
                }
            });
        });

        
        $('#delt_acc').on('submit', function(e){
            e.preventDefault();
            let data = $(this).serialize();
            Swal.fire({
                title: 'Loading',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                    $.post("/ajax/post/profile/delete-otp-verification", data, (resp) => {
                        Swal.fire(resp.alert).then(() => {
                            if(resp.success) {
                                location.reload();
                            }
                        })
                    }, 'json');
                }
            });
        });
    })
</script>