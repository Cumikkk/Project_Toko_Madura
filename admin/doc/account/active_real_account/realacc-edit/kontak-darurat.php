
<div class="card">
    <form action="" method="post" id="form_kontak_darurat">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="kd-nama" class="form-label">Nama</label>
                        <input type="text" class="form-control" name="kd-nama" id="kd-nama" value="<?php echo $account['ACC_F_APP_DRRT_NAMA'] ?>" required>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="form-group">
                        <label for="kd-alamat" class="form-label">Alamat</label>
                        <input type="text" class="form-control" name="kd-alamat" id="kd-alamat" value="<?php echo $account['ACC_F_APP_DRRT_ALAMAT'] ?>" required>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="kd-kode-pos" class="form-label">Kode Pos</label>
                        <input type="text" class="form-control" name="kd-kode-pos" id="kd-kode-pos" value="<?php echo $account['ACC_F_APP_DRRT_ZIP'] ?>" required>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="kd-telepon" class="form-label">Telepon</label>
                        <input type="text" class="form-control" name="kd-telepon" id="kd-telepon" value="<?php echo $account['ACC_F_APP_DRRT_TLP'] ?>" required>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="kd-hubungan" class="form-label">Hubungan</label>
                        <input type="text" class="form-control" name="kd-hubungan" id="kd-hubungan" value="<?php echo $account['ACC_F_APP_DRRT_HUB'] ?>" required>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" name="submit-kontak-darurat" class="btn btn-primary">Edit Data</button>
        </div>
    </form>
</div>


<script>
    $(document).ready(() => {
        $('#form_kontak_darurat').on('submit', function(e){
            e.preventDefault();
            let dtFrm = new FormData(this);
            dtFrm.append('sbmt_id', '<?= ($id_acc ?? '') ?>');

            let data  = Object.fromEntries(dtFrm);
            $.post("/ajax/post/account/edit_kontak_darurat", data, function(resp) {
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