<?php

use App\Factory\UtmHandler;
use App\Models\Country;
use App\Models\Helper;

$utmData = UtmHandler::extractUtmData();
$referral = Helper::form_input($utmData['referral'] ?? "");
UtmHandler::saveUtmToCookie($utmData);
?>

<div class="main-content login-panel">
	<div class="login-body">
		<div class="top d-flex justify-content-between align-items-center">
			<div>
				<a href="#" class="btn-back position-absolute text-white" style="margin-top: -5px;" aria-label="Kembali">
					<i class="fa-solid fa-arrow-left" style="font-size: 20px;"></i>
				</a>
			</div>
			<div class="logo">
				<img src="/assets/images/logo-full-first-state-futures.svg" alt="Logo">
			</div>
			<a href="/"><i class="fa-duotone fa-house-chimney"></i></a>
		</div>
		<div class="bottom">
			<h3 class="panel-title">Registration</h3>
			<form id="form-signup" method="post">
				<input type="hidden" name="referral" class="form-control" autocomplete="off" placeholder="Referal" value="<?= $referral ?>">
				<input type="hidden" name="csrf_token" value="">
				<div class="input-group mb-25">
					<span class="input-group-text"><i class="fa-regular fa-user"></i></span>
					<input type="text" name="fullname" pattern="^[A-Za-zÀ-ÖØ-öø-ÿ'’`ʼ\-\.\s]{2,100}$" required data-parsley-required class="form-control"
						autocomplete="off" placeholder="Full Name" title="hanya boleh huruf, spasi, tanda hubung (-), titik (.), atau apostrof (').">
				</div>
				<div class="input-group mb-25">
					<span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
					<input type="email" required name="email" class="form-control" autocomplete="off"
						placeholder="Email">
				</div>
				<div class="input-group mb-25">
					<span class="input-group-text"><i class="fa-regular fa-lock"></i></span>
					<input type="password" required name="password" class="form-control rounded-end"
						autocomplete="off" placeholder="Password" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9])\S{8,64}$" title="Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character, and be at least 8 characters long." >
					<a role="button" class="password-show"><i class="fa-duotone fa-eye"></i></a>
				</div>
				<div class="input-group mb-25">
					<div class="input-group-text" style="width: fit-content;">
						<select name="phone_code">
							<?php foreach (Country::countries() as $country): ?>
								<option value="<?= $country['COUNTRY_PHONE_CODE'] ?>" <?= ($country['COUNTRY_PHONE_CODE'] == "+62") ? "selected" : ""; ?> data-content="<?= strtolower(substr($country['COUNTRY_CURR'], 0, 2)) ?>"><?= $country['COUNTRY_PHONE_CODE'] ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<input type="text" name="phone" required data-parsley-required class="form-control" maxlength="13"  autocomplete="off" placeholder="Phone Number">
				</div>
				
				<div class="d-flex justify-content-between mb-25">
					<div class="form-check">
						<input class="form-check-input" name="terms" type="checkbox" required 
							id="loginCheckbox">
						<label class="form-check-label text-white" for="loginCheckbox">
							Saya telah membaca dan menyetujui 
							<a href="#" data-bs-toggle="modal" data-bs-target="#addLabelModal" class="text-white text-decoration-underline">Syarat dan Ketentuan serta Kebijakan Privasi</a>
						</label>
					</div>
				</div>
				<button type="submit" name="submit_register" class="btn btn-primary w-100 login-btn">Sign up</button>
				<p class="mt-2">Already have an account? <a href="/" class="text-white text-decoration-underline">click here.</a></p>
			</form>
			<!-- <div class="other-option">
				<p>Or continue with</p>
				<div class="social-box d-flex justify-content-center gap-20">
					<a href="#"><i class="fa-brands fa-facebook-f"></i></a>
					<a href="#"><i class="fa-brands fa-twitter"></i></a>
					<a href="#"><i class="fa-brands fa-google"></i></a>
					<a href="#"><i class="fa-brands fa-instagram"></i></a>
				</div>
			</div> -->
		</div>
	</div>

	<!-- footer start -->
	<?php require_once __DIR__ . "/footer.php"; ?>
	<!-- footer end -->
</div>

<div class="modal fade" id="addLabelModal" tabindex="-1" aria-labelledby="addLabelModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h1 class="modal-title fs-5" id="addLabelModalLabel">Syarat dan Ketentuan</h1>
				<button type="button" class="btn btn-sm btn-icon btn-outline-primary" data-bs-dismiss="modal" aria-label="Close"><i class="fa-light fa-xmark"></i></button>
			</div>
			<div class="modal-body">
				<p><strong>1. Pengantar</strong></p>
				<p>
				<span>Selamat datang di <?= App\Models\CompanyProfile::profilePerusahaan()['COMPANY_NAME'] ?? "-" ?>. Dengan menggunakan produk dan
					layanan kami, termasuk produk merek <?= App\Models\CompanyProfile::profilePerusahaan()['COMPANY_NAME'] ?? "-" ?> ("Produk"), Anda setuju untuk
					mematuhi dan terikat oleh Syarat dan Ketentuan berikut ini.</span>
				</p>
				<p>
				<strong><span>2. Definisi</span></strong>
				</p>
				<p><span>Dalam Syarat dan Ketentuan ini:</span></p>
				<ol>
				<li><span>"Perusahaan" berarti <?= App\Models\CompanyProfile::profilePerusahaan()['COMPANY_NAME'] ?? "-" ?>.</span></li>
				<li>
					<span>"Produk" berarti semua produk dan layanan yang ditawarkan oleh
					Perusahaan.</span>
				</li>
				<li>
					<span>"Pengguna" berarti setiap individu atau entitas yang menggunakan
					Produk.</span>
				</li>
				<li>
					<span>"Pialang" berarti pihak yang melakukan transaksi atas nama
					Pengguna.</span>
				</li>
				<li>
					<span>"Investasi" berarti penempatan dana atau aset ke dalam produk finansial
					dengan harapan mendapatkan keuntungan.</span>
				</li>
				<li>
					<span>"Investor" berarti individu atau entitas yang melakukan investasi melalui
					Produk.</span>
				</li>
				<li>
					<span>"BAPPEBTI" berarti Badan Pengawas Perdagangan Berjangka Komoditi, yang
					mengatur dan mengawasi perdagangan berjangka di Indonesia.</span>
				</li>
				<li>
					<span>"Hukum Indonesia" berarti semua undang-undang, peraturan, dan ketentuan
					yang berlaku di Indonesia.</span>
				</li>
				</ol>
				<p>
				<strong><span>3. Penggunaan Produk</span></strong>
				</p>
				<ol>
				<li>
					<span>Pengguna harus berusia minimal 18 tahun untuk menggunakan Produk.</span>
				</li>
				<li>
					<span>Pengguna harus menyediakan informasi yang akurat dan lengkap saat
					mendaftar atau menggunakan Produk.</span>
				</li>
				<li>
					<span>Pengguna tidak boleh menggunakan Produk untuk tujuan ilegal atau tidak
					sah.</span>
				</li>
				<li>
					<span>Pengguna setuju untuk mematuhi semua peraturan dan ketentuan yang
					berlaku, termasuk yang ditetapkan oleh BAPPEBTI dan hukum Indonesia.</span>
				</li>
				</ol>
				<p>
				<strong><span>4. Hak dan Kewajiban Pengguna</span></strong>
				</p>
				<ol>
				<li>
					<span>Pengguna berhak mendapatkan layanan yang sesuai dengan deskripsi yang
					diberikan oleh Perusahaan.</span>
				</li>
				<li>
					<span>Pengguna bertanggung jawab untuk menjaga kerahasiaan informasi akun
					mereka.</span>
				</li>
				<li>
					<span>Pengguna wajib melaporkan setiap penggunaan yang tidak sah atau
					pelanggaran keamanan kepada Perusahaan.</span>
				</li>
				<li>
					<span>Pengguna bertanggung jawab atas semua transaksi yang dilakukan melalui
					akun mereka.</span>
				</li>
				<li>
					<span>Pengguna harus memahami risiko yang terkait dengan investasi dan
					perdagangan berjangka.</span>
				</li>
				<li>
					<span>Pengguna setuju untuk mematuhi semua aturan yang ditetapkan oleh BAPPEBTI
					dan hukum Indonesia.</span>
				</li>
				</ol>
				<p>
				<strong><span>5. Hak dan Kewajiban Perusahaan</span></strong>
				</p>
				<ol>
				<li>
					<span>Perusahaan berhak untuk mengubah atau menghentikan Produk kapan saja
					tanpa pemberitahuan sebelumnya.</span>
				</li>
				<li>
					<span>Perusahaan berkewajiban untuk memberikan dukungan pelanggan dan menangani
					keluhan dengan segera dan efisien.</span>
				</li>
				<li>
					<span>Perusahaan berhak untuk menangguhkan atau menghentikan akun Pengguna yang
					melanggar Syarat dan Ketentuan ini.</span>
				</li>
				<li>
					<span>Perusahaan berhak untuk menolak atau membatalkan transaksi yang dianggap
					tidak sah atau melanggar hukum.</span>
				</li>
				<li>
					<span>Perusahaan wajib mematuhi peraturan yang ditetapkan oleh BAPPEBTI dan
					hukum Indonesia.</span>
				</li>
				</ol>
				<p>
				<strong><span>6. Pembayaran dan Biaya</span></strong>
				</p>
				<ol>
				<li>
					<span>Pengguna setuju untuk membayar semua biaya yang terkait dengan penggunaan
					Produk sesuai dengan tarif yang berlaku.</span>
				</li>
				<li>
					<span>Semua pembayaran yang dilakukan tidak dapat dikembalikan kecuali
					ditentukan lain oleh Perusahaan.</span>
				</li>
				<li>
					<span>Pengguna bertanggung jawab untuk semua biaya dan pajak yang timbul dari
					transaksi mereka.</span>
				</li>
				</ol>
				<p>
				<strong><span>7. Risiko Investasi</span></strong>
				</p>
				<ol>
				<li>
					<span>Pengguna memahami bahwa perdagangan berjangka dan investasi melibatkan
					risiko tinggi dan mungkin tidak cocok untuk semua investor.</span>
				</li>
				<li>
					<span>Pengguna harus melakukan penilaian risiko sendiri dan mencari saran
					independen jika diperlukan sebelum melakukan transaksi.</span>
				</li>
				<li>
					<span>Perusahaan tidak bertanggung jawab atas kerugian yang timbul dari
					keputusan investasi yang dibuat oleh Pengguna.</span>
				</li>
				</ol>
				<p>
				<strong><span>8. Pembatasan Tanggung Jawab</span></strong>
				</p>
				<ol>
				<li>
					<span>Perusahaan tidak bertanggung jawab atas kerugian langsung, tidak
					langsung, insidental, khusus, atau konsekuensial yang timbul dari
					penggunaan atau ketidakmampuan untuk menggunakan Produk.</span>
				</li>
				<li>
					<span>Perusahaan tidak menjamin bahwa Produk akan bebas dari kesalahan atau
					gangguan.</span>
				</li>
				</ol>
				<p>
				<strong><span>9. Perlindungan Data</span></strong>
				</p>
				<ol>
				<li>
					<span>Perusahaan berkomitmen untuk melindungi privasi dan data pribadi Pengguna
					sesuai dengan Kebijakan Privasi yang berlaku.</span>
				</li>
				<li>
					<span>Pengguna setuju untuk pengumpulan dan penggunaan data pribadi mereka
					sesuai dengan Kebijakan Privasi Perusahaan.</span>
				</li>
				</ol>
				<p>
				<strong><span>10. Perubahan Syarat dan Ketentuan</span></strong>
				</p>
				<ol>
				<li>
					<span>Perusahaan berhak untuk mengubah Syarat dan Ketentuan ini kapan
					saja.</span>
				</li>
				<li>
					<span>Perubahan akan berlaku segera setelah dipublikasikan di situs web
					Perusahaan. Pengguna diharapkan untuk meninjau Syarat dan Ketentuan ini
					secara berkala.</span>
				</li>
				</ol>
				<p>
				<strong><span>11. Hukum yang Berlaku</span></strong>
				</p>
				<p>
				<span>Syarat dan Ketentuan ini diatur dan ditafsirkan sesuai dengan hukum yang
					berlaku di Indonesia. Setiap sengketa yang timbul dari Syarat dan Ketentuan
					ini akan diselesaikan di pengadilan yang berwenang di Indonesia.</span>
				</p>
				<p>
				<strong><span>12. Kontak Kami</span></strong>
				</p>
				<p>
				<span>Jika Anda memiliki pertanyaan atau kekhawatiran terkait Syarat dan
					Ketentuan ini, silakan hubungi kami di:</span>
				</p>
				<ul>
				<li>
					<span>Alamat: <?= App\Models\CompanyProfile::getMainOffice()['ADDRESS'] ?? "-" ?></span>
				</li>
				<li><span>Email: <?= App\Models\CompanyProfile::getMainOffice()['EMAIL'] ?? "-" ?></span></li>
				<li><span>Telepon: 021-50322008</span></li>
				</ul>
				<p>
				<span>Dengan menggunakan Produk kami, Anda menyetujui Syarat dan Ketentuan
					ini.</span>
				</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-bs-dismiss="modal">Tutup</button>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	function format(item, state) {
		if (!item.id) {
			return item.text;
		}
		var countryUrl = "https://hatscripts.github.io/circle-flags/flags/";
		var stateUrl = "https://oxguy3.github.io/flags/svg/us/";
		var url = state ? stateUrl : countryUrl;
		var ctr = item.element?.dataset.content;
		var img = $("<img>", {
			class: "img-flag",
			width: 30,
			src: url + ctr.toLowerCase() + ".svg"
		});
		var span = $("<span>", {
			text: " " + item.text
		});
		span.prepend(img);
		return span;
	}
	$(document).ready(function() {
		$(`[name="phone_code"]`).select2({
			templateResult: function(item) {
				return format(item, false);
			}
		});

		$('input[name="phone"]').on('input', function() {
			this.value = this.value.replace(/[^0-9]/g, '');
		})

		$("#form-signup").on("submit", function(e) {
			e.preventDefault();
			let formData = $(this).serialize();
			let button = $(this).find('button[type="submit"]');
			button.addClass('loading');
			
			$.ajax({
				url: "/ajax/auth/signup",
				type: "POST",
				data: formData,
				dataType: "json",
				success: function(resp) {
					Swal.fire(resp.alert).then(() => {
						if(resp.success) {
							location.href = resp.data.redirect;
						}
					});
				},
				error: function() {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'An error occurred. Please try again later.'
					});
				}
			})
			.always(function() {
				button.removeClass('loading');
			});
		});
	})
</script>