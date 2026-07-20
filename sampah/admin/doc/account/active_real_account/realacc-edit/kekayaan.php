
<div class="card">
    <form action="" method="post" id="form_kekayaan">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="ky-lokasi-rumah" class="form-label">Lokasi Rumah</label>
                        <input type="text" name="ky-lokasi-rumah" id="ky-lokasi-rumah" class="form-control" value="<?php echo $account['ACC_F_APP_KEKYAN_RMHLKS'] ?>" required>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="ky-nilai-njop" class="form-label">Nilai NJOP</label>
                        <input type="text" name="ky-nilai-njop" id="ky-nilai-njop" class="form-control" value="<?php echo $account['ACC_F_APP_KEKYAN_NJOP'] ?>" required>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="ky-deposit-bank" class="form-label">Deposit Bank</label>
                        <input type="text" name="ky-deposit-bank" id="ky-deposit-bank" class="form-control" value="<?php echo $account['ACC_F_APP_KEKYAN_DPST'] ?>" required>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="ky-jumlah" class="form-label">Jumlah</label>
                        <input type="text" name="ky-jumlah" id="ky-jumlah" class="form-control" value="<?php echo $account['ACC_F_APP_KEKYAN_NILAI'] ?>" required>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="ky-jumlah-kekayaan-lainya" class="form-label">Jumlah Kekayaan Lainnya</label>
                        <select name="ky-jumlah-kekayaan-lainya" id="ky-jumlah-kekayaan-lainya" class="form-control" required>
                            <option value="Antara Rp. 100 - 250 juta" <?= (strtoupper($account["ACC_F_APP_KEKYAN_LAIN"]) == 'ANTARA RP. 100 - 250 JUTA')? "selected" : ""; ?>>Antara Rp. 100 - 250 juta</option>
                            <option value="Antara Rp. 250 - 500 juta" <?= (strtoupper($account["ACC_F_APP_KEKYAN_LAIN"]) == 'ANTARA RP. 250 - 500 JUTA')? "selected" : ""; ?>>Antara Rp. 250 - 500 juta</option>
                            <option value="Di atas Rp. 500 juta" <?= (strtoupper($account["ACC_F_APP_KEKYAN_LAIN"]) == 'DI ATAS RP. 500 JUTA')? "selected" : ""; ?> >Di atas Rp. 500 juta</option>
                            <option value="Tidak ada" <?= (strtoupper($account["ACC_F_APP_KEKYAN_LAIN"]) == 'TIDAK ADA')? "selected" : ""; ?> >Tidak ada</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="ky-sumber-penghasilan" class="form-label required">Sumber Penghasilan</label>
                        <select name="ky-sumber-penghasilan" id="ky-sumber-penghasilan" class="form-control" required>
                            <option value="" selected disabled>Pilih</option>    
                            <?php foreach(App\Models\Regol::$sumberPenghasilan as $sumber) : ?>
                                <option value="<?= $sumber ?>" <?= (strtoupper($account["ACC_F_APP_KEKYAN_SUMBER_PENGHASILAN"] ?? "") == strtoupper($sumber))? "selected" : ""; ?>><?= $sumber ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" name="submit-kekayaan" class="btn btn-primary">Edit Data</button>
        </div>
    </form>
</div>

<script>
    $(document).ready(() => {
        $('#form_kekayaan').on('submit', function(e){
            e.preventDefault();
            let dtFrm = new FormData(this);
            dtFrm.append('sbmt_id', '<?= ($id_acc ?? '') ?>');

            let data  = Object.fromEntries(dtFrm);
            $.post("/ajax/post/account/edit_kekayaan", data, function(resp) {
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