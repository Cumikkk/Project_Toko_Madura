<div class="card p-0">
    <form action="" method="post" id="form_profile_pribadi">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <div class="form-group">
                        <label for="pp-nama" class="form-label">Nama</label>
                        <input type="text" class="form-control" name="pp-nama" id="pp-nama" required value="<?php echo $account['ACC_FULLNAME'] ?>">
                    </div>
                </div>
    
                <div class="col-md-4 mb-2">
                    <div class="form-group">
                        <label for="pp-tempat-lahir" class="form-label">Tempat Lahir</label>
                        <input type="text" class="form-control" name="pp-tempat-lahir" id="pp-tempat-lahir" required value="<?php echo $account['ACC_TEMPAT_LAHIR'] ?>">
                    </div>
                </div>
    
                <div class="col-md-4 mb-2">
                    <div class="form-group">
                        <label for="pp-tanggal-lahir" class="form-label">Tanggal Lahir</label>
                        <input type="date" class="form-control" name="pp-tanggal-lahir" id="pp-tanggal-lahir" required value="<?php echo $account['ACC_TANGGAL_LAHIR'] ?>">
                    </div>
                </div>
                
                <div class="col-md-4 mb-2">
                    <div class="form-group">
                        <label for="pp-npwp" class="form-label">NPWP</label>
                        <input type="number" class="form-control" name="pp-npwp" id="pp-npwp" required value="<?php echo $account['ACC_F_APP_PRIBADI_NPWP'] ?>">
                    </div>
                </div>
    
                <div class="col-md-4 mb-2">
                    <div class="form-group">
                        <label for="pp-type-id" class="form-label">Type ID</label>
                        <select name="pp-type-id" id="pp-type-id" class="form-control">
                            <option value="KTP" <?php echo (strtoupper($account['ACC_TYPE_IDT']) == "KTP") ? "selected" : ""; ?>>KTP</option>
                            <option value="PASSPORT" <?php echo (strtoupper($account['ACC_TYPE_IDT']) == "PASSPORT") ? "selected" : ""; ?>>PASSPORT</option>
                        </select>
                    </div>
                </div>
    
                <div class="col-md-4 mb-2">
                    <div class="form-group">
                        <label for="pp-id-number" class="form-label">ID Number</label>
                        <input type="number" class="form-control" name="pp-id-number" id="pp-id-number" required value="<?php echo $account['ACC_NO_IDT'] ?>">
                    </div>
                </div>
    
                <div class="col-md-4 mb-2">
                    <div class="form-group">
                        <label for="pp-jenis-kelamin" class="form-label">Jenis Kelamin</label>
                        <select name="pp-jenis-kelamin" id="pp-jenis-kelamin" class="form-control">
                            <option value="Laki-laki" <?php echo (strtoupper($account['ACC_F_APP_PRIBADI_KELAMIN']) == "LAKI-LAKI") ? "selected" : ""; ?>>Laki-laki</option>
                            <option value="Perempuan" <?php echo (strtoupper($account['ACC_F_APP_PRIBADI_KELAMIN']) == "PEREMPUAN") ? "selected" : ""; ?>>Perempuan</option>
                        </select>
                    </div>
                </div>
    
                <div class="col-md-4 mb-2">
                    <div class="form-group">
                        <label for="pp-ibu-kandung" class="form-label">Ibu Kandung</label>
                        <input type="text" class="form-control" name="pp-ibu-kandung" id="pp-ibu-kandung" required value="<?php echo $account['ACC_F_APP_PRIBADI_IBU'] ?>">
                    </div>
                </div>
    
                <div class="col-md-4 mb-2">
                    <div class="form-group">
                        <label for="pp-status-perkawinan" class="form-label">Status Perkawinan</label>
                        <select name="pp-status-perkawinan" id="pp-status-perkawinan" class="form-control">
                            <option value="Tidak Kawin" <?= (strtoupper($account['ACC_F_APP_PRIBADI_STSKAWIN']) == "TIDAK KAWIN") ? "selected" : ""; ?>>Tidak Kawin</option>
                            <option value="Kawin" <?= (strtoupper($account['ACC_F_APP_PRIBADI_STSKAWIN']) == "KAWIN") ? "selected" : ""; ?>>Kawin</option>
                            <option value="Janda" <?= (strtoupper($account['ACC_F_APP_PRIBADI_STSKAWIN']) == "JANDA") ? "selected" : ""; ?>>Janda</option>
                            <option value="Duda" <?= (strtoupper($account['ACC_F_APP_PRIBADI_STSKAWIN']) == "DUDA") ? "selected" : ""; ?>>Duda</option>
                        </select>
                    </div>
                </div>
    
                <div class="col-md-4 mb-2">
                    <div class="form-group">
                        <label for="pp-nama-suami-istri" class="form-label">Nama Suami/Istri</label>
                        <input type="text" class="form-control" name="pp-nama-suami-istri" id="pp-nama-suami-istri" value="<?php echo $account['ACC_F_APP_PRIBADI_NAMAISTRI'] ?>">
                    </div>
                </div>
    
                <div class="col-md-4 mb-2">
                    <div class="form-group">
                        <label for="pp-alamat" class="form-label">Alamat</label>
                        <input type="text" class="form-control" name="pp-alamat" id="pp-alamat" required value="<?php echo $account['ACC_ADDRESS'] ?>">
                    </div>
                </div>
    
                <div class="col-md-4 mb-2">
                    <div class="form-group">
                        <label for="pp-kode-pos" class="form-label">Kode Pos</label>
                        <input type="number" class="form-control" name="pp-kode-pos" id="pp-kode-pos" required value="<?php echo $account['ACC_ZIPCODE'] ?>">
                    </div>
                </div>
    
                <div class="col-md-4 mb-2">
                    <div class="form-group">
                        <label for="pp-nomor-telepon" class="form-label">Nomor Telephone</label>
                        <input type="text" class="form-control" name="pp-nomor-telepon" id="pp-nomor-telepon" required value="<?php echo $account['ACC_F_APP_PRIBADI_TLP'] ?>">
                    </div>
                </div>
    
                <div class="col-md-4 mb-2">
                    <div class="form-group">
                        <label for="pp-nomor-fax" class="form-label">Nomor Faksimili</label>
                        <input type="text" class="form-control" name="pp-nomor-fax" id="pp-nomor-fax" required value="<?php echo $account['ACC_F_APP_PRIBADI_FAX'] ?>">
                    </div>
                </div>
    
                <div class="col-md-4 mb-2">
                    <div class="form-group">
                        <label for="pp-nomor-handphone" class="form-label">Nomor Handphone</label>
                        <input type="text" class="form-control" name="pp-nomor-handphone" id="pp-nomor-handphone" required value="<?php echo $account['ACC_F_APP_PRIBADI_HP'] ?>">
                    </div>
                </div>
    
                <div class="col-md-4 mb-2">
                    <div class="form-group">
                        <label for="pp-status-rumah" class="form-label">Status Kepemilikan Rumah</label>
                        <select name="pp-status-rumah" id="pp-status-rumah" class="form-control">
                            <option value="Pribadi" <?= (strtoupper($account['ACC_F_APP_PRIBADI_STSRMH']) == "PRIBADI") ? "selected" : ""; ?>>Pribadi</option>
                            <option value="Keluarga" <?= (strtoupper($account['ACC_F_APP_PRIBADI_STSRMH']) == "KELUARGA") ? "selected" : ""; ?>>Keluarga</option>
                            <option value="Sewa" <?= (strtoupper($account['ACC_F_APP_PRIBADI_STSRMH']) == "SEWA") ? "selected" : ""; ?>>Sewa</option>
                            <option value="Lainnya" <?= (strtoupper($account['ACC_F_APP_PRIBADI_STSRMH']) == "LAINNYA") ? "selected" : ""; ?>>Lainnya</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-4 mb-2">
                    <div class="form-group">
                        <label for="pp-pendidikan-terakhir" class="form-label required">Pendidikan Terakhir</label>
                        <select name="pp-pendidikan-terakhir" id="pp-pendidikan-terakhir" class="form-control" required>
                            <option value="" selected disabled>Pilih</option>
                            <?php foreach(App\Models\Regol::$pendidikanTerakhir as $pendidikan) : ?>
                                <option value="<?= $pendidikan ?>" <?= (strtoupper($account['ACC_F_APP_PENDIDIKAN'] ?? "") == strtoupper($pendidikan)) ? "selected" : ""; ?>><?= $pendidikan ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-4 mb-2">
                    <div class="form-group">
                        <label for="pp-kewarganegaraan" class="form-label required">Kewarganegaraan</label>
                        <select name="pp-kewarganegaraan" id="pp-kewarganegaraan" class="form-control" required>
                            <option value="" selected disabled>Pilih</option>
                            <?php foreach(App\Models\Country::countries() as $country) : ?>
                                <option value="<?= $country['COUNTRY_NAME'] ?>" <?= (strtoupper($account['ACC_F_APP_KEWARGANEGARAAN'] ?? "") == strtoupper($country['COUNTRY_NAME'])) ? "selected" : ""; ?>><?= $country['COUNTRY_NAME'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-6 mb-2">
                    <div class="form-group">
                        <label for="pp-nomor-sid" class="form-label">Nomor SID</label>
                        <input type="text" class="form-control" name="pp-nomor-sid" id="pp-nomor-sid" value="<?php echo $account['ACC_F_SID_NOMER'] ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" name="submit-profile-pribadi" class="btn btn-primary">Edit Data</button>
        </div>
    </form>
</div>
<script>
    $(document).ready(() => {
        $('#pp-status-perkawinan').on('change', function() {
            let val = $(this).val();
            if(val.toLowerCase() == "kawin") { 
                $('#pp-nama-suami-istri').attr('required', true);
            }else{
                $('#pp-nama-suami-istri').removeAttr('required');
            }
        }).change();

        $('#pp-nomor-sid')
        .mask('** - * - **** - ****** - **')
        .on('input', function() {
            this.value = this.value.toUpperCase();
        })

        $('#form_profile_pribadi').on('submit', function(e){
            e.preventDefault();
            let dtFrm = new FormData(this);
            dtFrm.append('sbmt_id', '<?= ($id_acc ?? '') ?>');

            let data  = Object.fromEntries(dtFrm);
            $.post("/ajax/post/account/edit_profile_pribadi", data, function(resp) {
                $('#myModalAcc').modal('hide');
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        if(resp?.data?.reloc?.length){
                            location.href = resp?.data?.reloc;
                        }else{ location.reload(); }
                    }
                });
            }, 'json');
        });
    });
</script>