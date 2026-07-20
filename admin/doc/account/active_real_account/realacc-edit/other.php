<div class="card">
    <form action="" method="post" id="form_other">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="o-tujuan-pembukaan-rekening" class="form-label">Tujuan Pembukaan Rekening</label>
                        <select name="o-tujuan-pembukaan-rekening" id="o-tujuan-pembukaan-rekening" class="form-control">
                            <option value="Lindungi nilai" <?= (strtoupper($account['ACC_F_APP_TUJUANBUKA']) == "LINDUNGI NILAI")? "selected" : ""; ?>>Lindungi nilai</option>
                            <option value="Gain" <?= (strtoupper($account['ACC_F_APP_TUJUANBUKA']) == "GAIN")? "selected" : ""; ?>>Gain</option>
                            <option value="Spekulasi" <?= (strtoupper($account['ACC_F_APP_TUJUANBUKA']) == "SPEKULASI")? "selected" : ""; ?>>Spekulasi</option>
                            <option value="Lainnya" <?= (strtoupper($account['ACC_F_APP_TUJUANBUKA']) == "LAINNYA")? "selected" : ""; ?>>Lainnya</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="o-pengalaman-investasi" class="form-label">Pengalaman Investasi</label>
                        <select name="o-pengalaman-investasi" id="o-pengalaman-investasi" class="form-control">
                            <option value="Ya" <?= (strtoupper($account['ACC_F_APP_PENGINVT']) == "YA")? "selected" : ""; ?>>Ya</option>
                            <option value="Tidak" <?= (strtoupper($account['ACC_F_APP_PENGINVT']) == "TIDAK")? "selected" : ""; ?>>Tidak</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="o-daftar-kekayaan" class="form-label">Daftar Kekayaan /thn</label>
                        <select name="o-daftar-kekayaan" id="o-daftar-kekayaan" class="form-control">
                            <option value="Antara 100-250 juta" <?= (strtoupper($account['ACC_F_APP_KEKYAN']) == "ANTARA 100-250 JUTA")? "selected" : ""; ?>>Antara 100-250 juta</option>
                            <option value="Antara 250-500 juta" <?= (strtoupper($account['ACC_F_APP_KEKYAN']) == "ANTARA 250-500 JUTA")? "selected" : ""; ?>>Antara 250-500 juta</option>
                            <option value="Diatas 500 juta rupiah" <?= (strtoupper($account['ACC_F_APP_KEKYAN']) == "DIATAS 500 JUTA RUPIAH")? "selected" : ""; ?>>Diatas 500 juta rupiah</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" name="submit-other" class="btn btn-primary">Edit Data</button>
        </div>
    </form>
</div>

<script>
    $(document).ready(() => {
        $('#form_other').on('submit', function(e){
            e.preventDefault();
            let dtFrm = new FormData(this);
            dtFrm.append('sbmt_id', '<?= ($id_acc ?? '') ?>');

            let data  = Object.fromEntries(dtFrm);
            $.post("/ajax/post/account/edit_other", data, function(resp) {
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