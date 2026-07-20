<?php 
use App\Models\Country;

?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="panel">
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-warning">
                        <p>Please make sure the name and email that is registered is appropriate</p>
                    </div>
                    <form action="" method="POST" id="form-step-1">
                        <input type="hidden" name="csrf_token" value="">
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <label for="fullname" class="form-label"><span class="text-danger">*</span>Fullname</label>
                                <input type="text" name="fullname" id="fullname" class="form-control" value="<?php echo $user['MBR_NAME']; ?>" <?= !empty($user['MBR_NAME'])? "disabled" : ""; ?>>
                            </div>
            
                            <div class="col-md-6 mb-2">
                                <label for="email" class="form-label"><span class="text-danger">*</span>Email</label>
                                <input type="email" id="email" disabled class="form-control" value="<?php echo $user['MBR_EMAIL']; ?>">
                            </div>
    
                            <div class="col-md-6 mb-2">
                                <label for="" class="form-label"><span class="text-danger">*</span>Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo $user['MBR_PHONE'] ?? ""; ?>" minlength="9" required placeholder="phone number: 08xxxxxxxxxx" <?= !empty($user['MBR_PHONE'])? "disabled" : ""; ?>>
                            </div>
    
                            <div class="col-md-6 mb-2">
                                <label for="gender" class="form-label">Gender</label>
                                <select name="gender" id="gender" class="form-control">
                                    <option value="" selected>Plase select (optional)</option>
                                    <option value="Laki-laki" <?php echo ($user['MBR_JENIS_KELAMIN'] == "Laki-laki") ? "selected" : ""; ?>>Laki-laki</option>
                                    <option value="Perempuan" <?php echo ($user['MBR_JENIS_KELAMIN'] == "Perempuan") ? "selected" : ""; ?>>Perempuan</option>
                                </select>
                            </div>
    
                            <div class="col-md-6 mb-2">
                                <label for="country" class="form-label">Country</label>
                                <select name="country" id="country" class="form-control">
                                    <option value="" selected disabled>Select</option>
                                    <?php foreach(Country::countries() as $country) : ?>
                                        <option value="<?= $country['COUNTRY_NAME'] ?>" <?= ($user['MBR_COUNTRY'] == $country['COUNTRY_NAME'])? "selected" : ""; ?>>
                                            <?= $country['COUNTRY_NAME'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
    
                            <div class="col-md-12 mb-2">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" name="address" id="address" placeholder="(optional)" rows="5"></textarea>
                            </div>
    
                            <?php if($user['MBR_VERIF'] == 1) : ?>
                                <div class="col-md-12 w-100">
                                    <button type="submit" name="next" class="btn btn-primary float-end">Next</button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $('#form-step-1').on('submit', function(event) {
        event.preventDefault();
        let data = $(this).serialize(),
            button = $(this).find('button[type="submit"]');

        button.addClass('loading');
        $.post("/ajax/post/verif/step-1", data, (resp) => {
            button.removeClass('loading');
            Swal.fire(resp.alert).then(() => {
                if(resp.success) {
                    location.href = resp.data.redirect;
                }
            })
        }, 'json')
    })
</script>