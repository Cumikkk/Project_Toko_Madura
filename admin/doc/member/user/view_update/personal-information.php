<form action="/ajax/post<?= $subPermission['link'] ?>" method="post" id="form-personal-information">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Personal information</h5>
        </div>
        <div class="card-body">
            <input type="hidden" name="code" value="<?= $userCode ?>">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="fullname" class="form-control-label">Full name</label>
                        <input type="text" name="fullname" id="fullname" class="form-control" placeholder="Full name" pattern="^[A-Za-zÀ-ÖØ-öø-ÿ'’`ʼ\-\.\s]{2,100}$" value="<?= $userData['MBR_NAME']; ?>">
                    </div>
                </div>
    
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="phone" class="form-control-label">Phone number</label>
                        <div class="input-group">
                            <select name="phone_code" id="phone_code" class="input-group-text">
                                <?php foreach(App\Models\Country::countries() as $country) : ?>
                                    <option value="<?= $country['COUNTRY_PHONE_CODE'] ?>" <?= ($country['COUNTRY_PHONE_CODE'] == $userData['MBR_PHONE_CODE'])? "selected" : ""; ?>><?= $country['COUNTRY_PHONE_CODE'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="number" name="phone" id="phone" class="form-control" placeholder="Phone number" value="<?= str_replace($userData['MBR_PHONE_CODE'], "", $userData['MBR_PHONE']) ?>">
                        </div>
                    </div>
                </div>
    
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="province" class="form-control-label">Province</label>
                        <select name="province" id="province" class="form-control select2">
                            <option value="">Pilih</option>
                            <?php foreach(App\Models\Wilayah::provinces() as $province) : ?>
                                <option value="<?= $province ?>" <?= $userData['MBR_PROVINCE'] == $province? "selected" : ""; ?>><?= $province ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
    
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="regency" class="form-control-label">Regency/City</label>
                        <select name="regency" id="regency" class="form-control select2">
                            <option value="">Pilih</option>
                        </select>
                    </div>
                </div>
    
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="district" class="form-control-label">Subdistrict</label>
                        <select name="district" id="district" class="form-control select2">
                            <option value="">Pilih</option>
                        </select>
                    </div>
                </div>
    
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="villages" class="form-control-label">Sub-district/Village</label>
                        <select name="villages" id="villages" class="form-control select2">
                            <option value="">Pilih</option>
                        </select>
                    </div>
                </div>
    
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="postal_code" class="form-control-label">Postal code</label>
                        <input type="number" name="postal_code" id="postal_code" class="form-control">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="place_of_birth" class="form-control-label">Place of birth</label>
                        <input type="text" name="place_of_birth" id="place_of_birth" class="form-control" placeholder="Place of birth" value="<?= $userData['MBR_TMPTLAHIR'] ?>">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="date_of_birth" class="form-control-label">Date of birth</label>
                        <input type="date" name="date_of_birth" id="date_of_birth" class="form-control" placeholder="Date of birth" value="<?= $userData['MBR_TGLLAHIR'] ?>">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="gender" class="form-control-label">Gender</label>
                        <select name="gender" id="gender" class="form-control form-select">
                            <option value="">Pilih</option>
                            <option value="Laki-laki" <?= (strtoupper($userData['MBR_JENIS_KELAMIN'] ?? "-") == "LAKI-LAKI")? "selected" : ""; ?>>Laki-laki</option>
                            <option value="Perempuan" <?= (strtoupper($userData['MBR_JENIS_KELAMIN'] ?? "-") == "PEREMPUAN")? "selected" : ""; ?>>Perempuan</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <label for="address" class="form-control-label">Complete address</label>
                        <textarea name="address" id="address" rows="5" class="form-control"><?= $userData['MBR_ADDRESS'] ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary px-4">Update</button>
        </div>
    </div>
</form>
        
<script type="text/javascript">
    $(document).ready(function() {
        const province = $('#province');
        const regency = $('#regency');
        const district = $('#district');
        const villages = $('#villages');
        const postalCode = $('#postal_code');
        const code = $('input[name="code"]').val();

        province.on('change', function() {
            if(province.val()) {
                regency.empty();
                $.post("/ajax/post/wilayah/regency", {province: this.value, user: code}, (resp) => {
                    if(resp.success) {
                        regency.append(`<option value="">Pilih</option>`);
                        $.each(resp.data, (i, val) => {
                            regency.append(`<option value="${val.name}" ${val.selected}>${val.name}</option>`);
                        })
    
                        if(regency.find("option:selected").length) {
                            regency.change();
                        }
                    }
                }, "json")
            }
        }).change();

        regency.on('change', function() {
            district.empty();
            $.post("/ajax/post/wilayah/district", {regency: regency.val(), user: code}, (resp) => {
                if(resp.success) {
                    district.append(`<option value="">Pilih</option>`);
                    $.each(resp.data, (i, val) => {
                        district.append(`<option value="${val.name}" ${val.selected}>${val.name}</option>`);
                    })

                    if(district.find("option:selected").length) {
                        district.change();
                    }
                }
            }, "json")
        });

        district.on('change', function() {
            villages.empty();
            $.post("/ajax/post/wilayah/villages", {district: district.val(), user: code}, (resp) => {
                if(resp.success) {
                    villages.append(`<option value="">Pilih</option>`);
                    $.each(resp.data, (i, val) => {
                        villages.append(`<option value="${val.name}" ${val.selected} data-postalcode="${val.postalCode}">${val.name}</option>`);
                    })

                    if(villages.find("option:selected").length) {
                        villages.change();
                    }
                }
            }, "json")
        });

        villages.on('change', function() {
            postalCode.val(villages.find('option:selected')?.data('postalcode'))
        })

        $('#form-personal-information').on('submit', function(e) {
            e.preventDefault();
            let button = $(this).find('button[type="submit"]');
            button.addClass('loading');

            $.post($(this).attr('action'), $(this).serialize(), (resp) => {
                button.removeClass('loading');
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        location.reload();
                    }
                })
            }, 'json');
        })
    })
</script>