<div class="page-header">
	<div>
		<h2 class="main-content-title tx-24 mg-b-5"><?php echo $vp = 'Password'; ?></h2>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
			<li class="breadcrumb-item active" aria-current="page"><?php echo $vp; ?></li>
		</ol>
	</div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-3">
            <div class="card-header">Change Password</div>
            <form method="post" id="cpassform">
                <div class="card-body">
                    <div class="form-group">
                        <label>Old Password</label>
                        <input type="text" class="form-control" name="pass01" required autocomplete="off" placeholder="Old Passwod">
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="text" class="form-control" name="pass02" required autocomplete="off" placeholder="New Passwod" pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[^a-zA-Z0-9])(?=.*^[^'\x22\\<>]*$).{8,}$" title="Minimal 1 digit angka, 1 huruf kecil, 1 huruf besar, dan 1 karakter spesial(Kecuali ['],[&#8220],[\],[<],[>]). Dan Panjang text minimal harus sebanyak 8 karakter">
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="text" class="form-control" name="pass03" required autocomplete="off" placeholder="Confirm New Passwod">
                    </div>
                </div>
                <div class="card-footer text-right">
                    <input type="submit" class="btn btn-primary" value="submit" name="submit_password">
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $(document).ready(() => {
        $('#cpassform').on('submit', function(e){
            e.preventDefault();

            let data = Object.fromEntries(new FormData(this).entries());
            $.post("/ajax/post/password/update", data, function(resp) {
                $('#modalEditCountry').modal('hide');
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        location.reload();
                    }
                })
            }, 'json')
        });
    });
</script>