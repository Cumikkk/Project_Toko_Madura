<?php
    use App\Models\Helper;
?>
<div class="card">
    <form action="" method="post" id="form_additional">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="ad-type" class="form-label">Type</label>
                        <select name="ad-type" id="ad-type" class="form-control">
                            <option value disabled>Choose one</option>
                            <?php
                                $SQL_PRODUCT = mysqli_query($db, '
                                    SELECT
                                        tb_racctype.ID_RTYPE,
                                        MD5(MD5(tb_racctype.ID_RTYPE)) AS HASH_ID_RTYPE,
                                        CONCAT(
                                            tb_racctype.RTYPE_NAME, "/",
                                            tb_racctype.RTYPE_KOMISI, "/",
                                            CASE
                                                WHEN RTYPE_RATE = 0 THEN "Floating"
                                                ELSE (RTYPE_RATE/1000)
                                            END
                                        ) AS `TYPE`
                                    FROM tb_racctype
                                ');
                                if($SQL_PRODUCT && mysqli_num_rows($SQL_PRODUCT) > 0){
                                    foreach(mysqli_fetch_all($SQL_PRODUCT, MYSQLI_ASSOC) as $RSLT_PRODUCT){
                            ?>
                                <option value="<?= $RSLT_PRODUCT["HASH_ID_RTYPE"] ?>" <?= ($account['ID_RTYPE'] == $RSLT_PRODUCT["ID_RTYPE"])? "selected" : "" ?>><?php echo  $RSLT_PRODUCT["TYPE"] ?></option>
                            <?php
                                    }
                                }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="ad-type" class="form-label">Limit Deposit</label>
                        <input type="text" class="form-control amount-formatter" name="acc-limit-margin" id="acc-limit-margin" value="<?= str_replace('.', ',', Helper::formatCurrency($account['ACC_LIMIT_MARGIN'])) ?>" required>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" name="submit-additional" class="btn btn-primary">Edit Data</button>
        </div>
    </form>
</div>


<script>
    $(document).ready(() => {
        $('#form_additional').on('submit', function(e){
            e.preventDefault();
            let dtFrm = new FormData(this);
            dtFrm.append('sbmt_id', '<?= ($id_acc ?? '') ?>');

            let data  = Object.fromEntries(dtFrm);
            $.post("/ajax/post/account/edit_additional", data, function(resp) {
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