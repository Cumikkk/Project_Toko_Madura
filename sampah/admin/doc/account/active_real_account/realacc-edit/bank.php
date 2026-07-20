
<div class="card">
    <form action="" method="post" id="form_bank">
        <div class="card-body">
            <?php 
                $nmbr = 1;
                foreach($userBanks as $bankmbr){ 
            ?>
                <h5 class="card-title mb-2">Bank <?= $nmbr?></h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="b-nama" class="form-label">Nama</label>
                            <input type="text" class="form-control" name="b-nama<?= ($nmbr != 1 ? $nmbr : '' ) ?>" id="b-nama" value="<?php echo $bankmbr['MBANK_HOLDER'] ?>" required>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="b-nama-bank" class="form-label">Nama Bank</label>
                            <select name="b-nama-bank<?= ($nmbr != 1 ? $nmbr : '' ) ?>" id="b-nama-bank" class="form-control" required>
                                <option value="" selected>Select</option>
                                <?php $SQL_BANK = mysqli_query($db, "SELECT BANKLST_NAME FROM tb_banklist"); ?>
                                <?php if($SQL_BANK) : ?>
                                    <?php while($bank = mysqli_fetch_assoc($SQL_BANK)): ?>
                                        <option value="<?php echo $bank['BANKLST_NAME'] ?>" <?= (strtoupper($bank['BANKLST_NAME']) == strtoupper($bankmbr['MBANK_NAME']))? "selected" : ""; ?>>
                                            <?php echo $bank['BANKLST_NAME'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="b-nomor-rekening" class="form-label">Nomor Rekening</label>
                            <input type="text" class="form-control" name="b-nomor-rekening<?= ($nmbr != 1 ? $nmbr : '' ) ?>" id="b-nomor-rekening" value="<?php echo $bankmbr['MBANK_ACCOUNT'] ?>" required>
                        </div>
                    </div>
                </div>
            <?php 
                if($nmbr ==  2){ break; }
                $nmbr++;
                } 
            ?>
        </div>
        <div class="card-footer">
            <button type="submit" name="submit-bank" class="btn btn-primary">Edit Data</button>
        </div>
    </form>
</div>

<script>
    $(document).ready(() => {
        $('#form_bank').on('submit', function(e){
            e.preventDefault();
            let dtFrm = new FormData(this);
            dtFrm.append('sbmt_id', '<?= ($id_acc ?? '') ?>');

            let data  = Object.fromEntries(dtFrm);
            $.post("/ajax/post/account/edit_bank", data, function(resp) {
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