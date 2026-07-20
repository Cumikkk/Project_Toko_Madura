<?php

use App\Models\User;

$isLoggedIn = User::user();
if($isLoggedIn) {
    die("<script>location.href = '/dashboard';</script>");
}
?>

<div class="main-content login-panel login-panel-2">
	<div class="login-body login-body-2">
		<div class="page-title-bar d-flex justify-content-center align-items-center position-relative mb-3">
			<a href="#" class="btn-back position-absolute start-0 text-white" aria-label="Kembali">
				<i class="fa-solid fa-arrow-left" style="font-size: 20px;"></i>
			</a>
			<h3 class="panel-title mb-0">Login</h3>
		</div>
		<div class="top d-flex justify-content-between align-items-center">
			<div class="logo">
				<img src="/assets/images/logo-full-first-state-futures.svg" style="width: 25%;" class="img_logo" alt="Logo">
			</div>
			<a href="/" aria-label="go to Home"><i class="fa-duotone fa-house-chimney"></i></a>
		</div>
		<div class="bottom">
			<form method="post" id="form-signin">
				<input type="hidden" name="csrf_token" value="">
				<div class="input-group mb-25">
					<input required name="email" type="email" class="form-control" autocomplete="off" required placeholder="Email address">
					<span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
				</div>
				<div class="input-group mb-25">
					<input required name="password" id="password" type="password" class="form-control" autocomplete="off" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9])\S{8,64}$" title="Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character, and be at least 8 characters long." required placeholder="Password">
					<span class="input-group-text" style="cursor: pointer;" onclick="togglePassword('password')"><i id="passwordIcon" class="fa-regular fa-eye"></i></span>
				</div>
				<div class="d-flex justify-content-between mb-25">
					<div class="form-check">
						<!-- <input class="form-check-input" type="checkbox" name="remember" value="" id="loginCheckbox">
						<label class="form-check-label text-white" for="loginCheckbox">
							Remember Me
						</label> -->
					</div>
					<a href="/forgot" class="text-white fs-14">Forgot Password?</a>
				</div>
				<button type="submit" name="sumit_login" class="btn btn-primary w-100 login-btn">Login</button>
			</form>
			<!-- <div class="other-option">
				<p>Or continue with</p>
				<div class="social-box d-flex justify-content-center gap-20">
					<a href="/google_login" aria-label="login as google"><i class="fa-brands fa-google text-dark"></i></a>
				</div>
			</div> -->

			<div class="other-option">
				<p class="mb-0">Don't have an account? <a href="/signup" class="text-white text-decoration-underline">create.</a></p>
			</div>
		</div>
	</div>

	<!-- footer start -->
	<?php require_once __DIR__ . "/footer.php"; ?>
	<!-- footer end -->
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
	$("#form-signin").on("submit", function(e) {
		e.preventDefault();
		let formData = $(this).serialize(), 
			button = $(this).find('button[type="submit"]');

		button.addClass('loading');
		$.post("/ajax/auth/signin", formData, function(resp) {
			button.removeClass('loading');
			if(!resp.success) {
				Swal.fire(resp.alert);
				return false;
			}

			if(!resp.data.redirect) {
				Swal.fire(resp.alert);
				return false;
			}
			
			location.href = resp.data.redirect;
		}, 'json');
	});
	localStorage.clear();
</script>