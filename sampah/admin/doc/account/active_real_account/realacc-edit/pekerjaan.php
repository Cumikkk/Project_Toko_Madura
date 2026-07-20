
<div class="card">
    <form action="" method="post" id="form_pekerjaan">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="k-type-pekerjaan" class="form-label required">Type Pekerjaan</label>
                        <select name="k-type-pekerjaan" id="k-type-pekerjaan" class="form-control form-select" required>
                            <option value="">Select</option>
                            <?php foreach(App\Models\Regol::$listPekerjaan as $pekerjaan): ?>
                                <option value="<?= $pekerjaan ?>" <?= ($pekerjaan == $account['ACC_F_APP_KRJ_TYPE'])? "selected" : ""; ?>><?= $pekerjaan ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="k-nama-perusahaan" class="form-label required">Nama Perusahaan</label>
                        <input type="text" name="k-nama-perusahaan" id="k-nama-perusahaan" class="form-control" value="<?php echo $account['ACC_F_APP_KRJ_NAMA'] ?>" required>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="k-bidang-pekerjaan" class="form-label required">Bidang Pekerjaan</label>
                        <input type="text" name="k-bidang-pekerjaan" id="k-bidang-pekerjaan" class="form-control" value="<?php echo $account['ACC_F_APP_KRJ_BDNG'] ?>" required>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="k-jabatan" class="form-label required">Jabatan</label>
                        <input type="text" name="k-jabatan" id="k-jabatan" class="form-control" value="<?php echo $account['ACC_F_APP_KRJ_JBTN'] ?>" required>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="k-nomor-telepon-kantor" class="form-label">Nomor Telepon Kantor</label>
                        <input type="text" name="k-nomor-telepon-kantor" id="k-nomor-telepon-kantor" class="form-control" value="<?php echo $account['ACC_F_APP_KRJ_TLP'] ?>">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="k-lama-bekerja" class="form-label">Lama Bekerja</label>
                        <input type="text" name="k-lama-bekerja" id="k-lama-bekerja" class="form-control" value="<?php echo $account['ACC_F_APP_KRJ_LAMA'] ?>">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="k-kantor-sebelumnya" class="form-label">Kantor Sebelumnya</label>
                        <input type="text" name="k-kantor-sebelumnya" id="k-kantor-sebelumnya" class="form-control" value="<?php echo $account['ACC_F_APP_KRJ_LAMASBLM'] ?>">
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="form-group">
                        <label for="k-alamat-tempat-kerja" class="form-label">Alamat Tempat Kerja</label>
                        <input type="text" name="k-alamat-tempat-kerja" id="k-alamat-tempat-kerja" class="form-control" value="<?php echo $account['ACC_F_APP_KRJ_ALAMAT'] ?>">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="k-kode-pos-kantor" class="form-label">Kode Pos</label>
                        <input type="text" name="k-kode-pos-kantor" id="k-kode-pos-kantor" class="form-control" value="<?php echo $account['ACC_F_APP_KRJ_ZIP'] ?>">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="k-fax-kantor" class="form-label">FAX</label>
                        <input type="text" name="k-fax-kantor" id="k-fax-kantor" class="form-control" value="<?php echo $account['ACC_F_APP_KRJ_FAX'] ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" name="submit-pekerjaan" class="btn btn-primary">Edit Data</button>
        </div>
    </form>
</div>

<script>
    $(document).ready(() => {
        $('#form_pekerjaan').on('submit', function(e){
            e.preventDefault();
            let dtFrm = new FormData(this);
            dtFrm.append('sbmt_id', '<?= ($id_acc ?? '') ?>');

            let data  = Object.fromEntries(dtFrm);
            $.post("/ajax/post/account/edit_pekerjaan", data, function(resp) {
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