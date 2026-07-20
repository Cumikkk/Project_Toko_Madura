<?php
    use App\Models\Helper;
    use App\Models\Apuppt;
    use App\Models\Dpwd;
    use Config\Core\Database;
    use Config\Core\SystemInfo;

    $dbmetasrv       = SystemInfo::app('DB_METALIVE');
    $EDD_HSTRY_DT    = Apuppt::getEddData(Helper::form_input($_GET["d"]));
    $JSN_DT_HTRY     = json_decode(($EDD_HSTRY_DT["EDD_DT"] ?? '[]'), true);
    $JSN_DT_EQTY     = json_decode(($EDD_HSTRY_DT["EQT_DT"] ?? '[]'), true);
    $x               = ($EDD_HSTRY_DT["ID_ACC"] ?? Helper::form_input($_GET["d"]));
    $dpwd_date_qwr   = (isset($EDD_HSTRY_DT["ADD_DATTIME"])) ? 'DATE("'.$EDD_HSTRY_DT["ADD_DATTIME"].'")' : 'DATE(NOW())';
    $edd_htry_login  = (isset($EDD_HSTRY_DT["JSN_EQT"])) ? 'AND JSON_CONTAINS("'.$EDD_HSTRY_DT["JSN_EQT"].'", tb_2.ACC_LOGIN)' : '';
    $jum_acc         = 0;
    $ttl_dps         = 0;
    $ttl_eqt         = 0;
    $_SESSION["EQT"] = [];
?>
<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">EDD Evaluasi <?= ((isset($EDD_HSTRY_DT["ADD_DATTIME"])) ? '(History)' : '') ?></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item"><a href="#">APUPPT</a></li>
            <li class="breadcrumb-item"><a href="#">Enhanced Due Dillingence (EDD)</a></li>
            <?= ((isset($EDD_HSTRY_DT["ADD_DATTIME"])) ? '<li class="breadcrumb-item"><a href="#">History</a></li>' : '') ?>
            <li class="breadcrumb-item active" aria-current="page">EDD Evaluasi <?= ((isset($EDD_HSTRY_DT["ADD_DATTIME"])) ? '(History)' : '') ?></li>
        </ol>
    </div>
</div>
<div class="row mb-4">
    <?php
        $SQL_EDPAR = mysqli_query($db,'SELECT tb_range_edtype.ID_EDTYPE,tb_range_edtype.EDTYPE_DESC FROM tb_range_edtype');
        if($SQL_EDPAR && mysqli_num_rows($SQL_EDPAR) > 0){
            while($RSLT_EDPAR = mysqli_fetch_assoc($SQL_EDPAR)){
    ?>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header font-weight-bold">Keterangan Parameter <?php echo $RSLT_EDPAR["EDTYPE_DESC"] ?></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered" width="100%">
                            <thead class="bg-primary">
                                <tr>
                                    <th style="vertical-align: middle" class="text-center text-white">No</th>
                                    <th style="vertical-align: middle" class="text-center text-white"><?php echo Apuppt::thrplce($RSLT_EDPAR["EDTYPE_DESC"], $RSLT_EDPAR["ID_EDTYPE"]) ?></th>
                                    <th style="vertical-align: middle" class="text-center text-white">Tingkat Risiko</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $SQL_EDPARVAL = mysqli_query($db,'
                                        SELECT
                                            tb_range_edd.EDD_DESC,
                                            tb_range_edd.EDD_LV
                                        FROM tb_range_edd
                                        WHERE tb_range_edd.EDD_TYPE = '.$RSLT_EDPAR["ID_EDTYPE"].'
                                    ');
                                    if($SQL_EDPARVAL && mysqli_num_rows($SQL_EDPARVAL) > 0){
                                        $nm = 1;
                                        while($RSTL_EDPARVAL = mysqli_fetch_assoc($SQL_EDPARVAL)){
                                ?>
                                    <tr>
                                        <td class="text-center"><?php echo $nm.'.'; ?></td>
                                        <td class="text-center"><?php echo $RSTL_EDPARVAL["EDD_DESC"] ?></td>
                                        <td class="text-center"><?php echo $RSTL_EDPARVAL["EDD_LV"] ?></td>
                                    </tr>
                                <?php
                                            $nm++;
                                        }
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php
            }
        }
    ?>
</div>

<div class="row mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header font-weight-bold">Data Nasabah</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered" width="100%">
                        <thead class="bg-dark">
                            <tr>
                                <th style="vertical-align: middle" class="text-center text-white">TGL</th>
                                <th style="vertical-align: middle" class="text-center text-white">Nama</th>
                                <th style="vertical-align: middle" class="text-center text-white">NIK</th>
                                <th style="vertical-align: middle" class="text-center text-white">Email</th>
                                <th style="vertical-align: middle" class="text-center text-white">Login</th>
                                <th style="vertical-align: middle" class="text-center text-white">Konfirmasi APUPPT</th>
                                <th style="vertical-align: middle" class="text-center text-white">Deposit Per Hari</th>
                                <th style="vertical-align: middle" class="text-center text-white">Equity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $SQL_TD = mysqli_query($db, '
                                    SELECT
	                                    tb_tst.*,
                                        0 AS RESK
                                    FROM (
                                        SELECT
                                            @X := tb_2.ID_ACC AS X,
                                            tb_2.ACC_DATETIME,
                                            tb_2.ACC_FULLNAME,
                                            tb_2.ACC_NO_IDT,
                                            (
                                                SELECT
                                                    tb_member.MBR_EMAIL
                                                FROM tb_member 
                                                WHERE tb_member.MBR_ID = tb_2.ACC_MBR
                                                LIMIT 1
                                            ) AS EMAIL,
                                            tb_2.ACC_LOGIN,
                                            IFNULL(
                                                (
                                                    SELECT
                                                        SUM(IFNULL(tb_dpwd.DPWD_AMOUNT,0))
                                                    FROM tb_dpwd
                                                    WHERE tb_dpwd.DPWD_RACC = tb_2.ID_ACC
                                                    AND tb_dpwd.DPWD_TYPE = '.Dpwd::$typeDeposit.'
                                                    AND tb_dpwd.DPWD_STS = -1
                                                    AND tb_dpwd.DPWD_STSACC = -1
                                                    AND tb_dpwd.DPWD_STSVER = -1
                                                    AND DATE(tb_dpwd.DPWD_DATETIME) = '.$dpwd_date_qwr.'
                                                )
                                            ,0) AS TOTAL_DP,
                                            0 AS EQT
                                        FROM tb_racc tb_1
                                        INNER JOIN tb_racc tb_2 ON(tb_1.ACC_NO_IDT = tb_2.ACC_NO_IDT)
                                        WHERE (tb_2.ACC_DERE = 1)
                                        AND (tb_2.ACC_LOGIN != "0" AND tb_2.ACC_LOGIN IS NOT NULL)
                                        AND MD5(MD5(tb_1.ID_ACC)) = "'.$x.'"
                                        '.$edd_htry_login.'
                                    ) AS tb_tst
                                ');
                                if($SQL_TD && mysqli_num_rows($SQL_TD) > 0){
                                    $jum_acc = 0;
                                    $ARR_CLR = [
                                        "Rendah"   => "success",
                                        "Menengah" => "warning",
                                        "Tinggi"   => "danger"
                                    ];
                                    while($TD_RSLT = mysqli_fetch_assoc($SQL_TD)){
                                        $lgn = $TD_RSLT["ACC_LOGIN"];
                                        array_push($_SESSION["EQT"], ["lgn" => $TD_RSLT["ACC_LOGIN"], "vl" => $TD_RSLT["EQT"]]);
                            ?>
                                <tr>
                                    <td class="text-center"><?php echo $TD_RSLT["ACC_DATETIME"] ?></td>
                                    <td><?php echo $TD_RSLT["ACC_FULLNAME"] ?></td>
                                    <td><?php echo $TD_RSLT["ACC_NO_IDT"] ?></td>
                                    <td><?php echo $TD_RSLT["EMAIL"] ?></td>
                                    <td class="text-center"><?php echo $TD_RSLT["ACC_LOGIN"] ?></td>
                                    <td class="text-center">
                                        <?php echo (!empty($TD_RSLT["RESK"])) ? '<span class="badge bg-'.$ARR_CLR[preg_replace('/[^A-Za-z]+/i', '', $TD_RSLT["RESK"])].' h-50 d-inline-block bg-opacity-15 text-white" style="font-size: 12px;">'.$TD_RSLT["RESK"].'</span>' : ''; ?>
                                    </td>
                                    <td class="text-center">Rp. <?php echo $TD_RSLT["TOTAL_DP"] ?></td>
                                    <td class="text-center">$. <?php echo number_format(($JSN_DT_EQTY["$lgn"] ?? $TD_RSLT["EQT"]), 2) ?></td>
                                </tr>
                            <?php
                                        $jum_acc++;
                                        $ttl_dps += $TD_RSLT["TOTAL_DP"];
                                        $ttl_eqt += ($JSN_DT_EQTY["$lgn"] ?? $TD_RSLT["EQT"]);
                                    }
                                }
                            ?>
                            <tr>
                                <td colspan="6" class="text-center">
                                    <h3><?php echo "Total Dari ($jum_acc) Akun:"; ?></h3>
                                </td>
                                <td class="text-center"><?php echo 'Rp.'.number_format($ttl_dps, 0); ?></td>
                                <td class="text-center"><?php echo '$.'.number_format($ttl_eqt, 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4 mb-3">
    <div class="col-md-3"></div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header font-weight-bold">Summary/Ringkasan</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered" width="100%">
                        <thead class="bg-info text-white">
                            <tr>
                                <th style="vertical-align: middle" class="text-center">Parameter</th>
                                <th style="vertical-align: middle" class="text-center">Keterangan Data</th>
                                <th style="vertical-align: middle" class="text-center">Data Nasabah</th>
                                <th style="vertical-align: middle" class="text-center">Tingkat Risiko</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $SQL_EDPAR2 = mysqli_query($db,'SELECT tb_range_edtype.ID_EDTYPE,tb_range_edtype.EDTYPE_DESC FROM tb_range_edtype');
                                $nx = 0;
                                if($SQL_EDPAR2 && mysqli_num_rows($SQL_EDPAR2) > 0){
                                    $selected   = false;
                                    while($RSLT_EPAR = mysqli_fetch_assoc($SQL_EDPAR2)){
                                        $vl = (($RSLT_EPAR["ID_EDTYPE"] == 1) ?  $jum_acc.' account' : (($RSLT_EPAR["ID_EDTYPE"] == 2) ? 'Rp.'.number_format($ttl_dps, 0) : (($RSLT_EPAR["ID_EDTYPE"] == 3) ? '$'.number_format($ttl_eqt, 2) : 0)));
                            ?>
                                <tr>
                                    <td class="par_name"><?php echo $RSLT_EPAR["EDTYPE_DESC"] ?></td>
                                    <td>
                                        <select class="form-control par text-dark" data-typ="<?php echo $RSLT_EPAR["ID_EDTYPE"] ?>" name="param" required>
                                            <option value disabled selected>Plih Keterangan Data</option>
                                            <?php
                                                $SQL_SEL = mysqli_query($db,'
                                                    SELECT
                                                        tb_range_edd.ID_EDD,
                                                        tb_range_edd.EDD_DESC,
                                                        tb_range_edd.EDD_LV
                                                    FROM tb_range_edd
                                                    WHERE tb_range_edd.EDD_TYPE = '.$RSLT_EPAR["ID_EDTYPE"].'
                                                ');
                                                if($SQL_SEL && mysqli_num_rows($SQL_SEL) > 0){
                                                    while($SEL_RSLT = mysqli_fetch_assoc($SQL_SEL)){
                                                        $indctr = (($selected == false) && Apuppt::comp($RSLT_EPAR["ID_EDTYPE"], $vl, $SEL_RSLT["EDD_DESC"]) == $SEL_RSLT["EDD_DESC"]);
                                            ?>
                                                <option 
                                                    value="<?php echo $SEL_RSLT["EDD_LV"] ?>" 
                                                    <?php 
                                                        if(isset($JSN_DT_HTRY[$RSLT_EPAR["ID_EDTYPE"]]) && $JSN_DT_HTRY[$RSLT_EPAR["ID_EDTYPE"]] == $SEL_RSLT["ID_EDD"]){
                                                            $selected = true;
                                                            echo 'selected';
                                                        }else if($indctr){ 
                                                            $selected = true;
                                                            echo 'selected'; 
                                                        }
                                                    ?> 
                                                    data-x="<?php echo base64_encode($SEL_RSLT["ID_EDD"]) ?>"
                                                >
                                                    <?php echo $SEL_RSLT["EDD_DESC"]; ?>
                                                </option>
                                            <?php
                                                    }
                                                }
                                            ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control text-center text-dark" readonly value="<?php echo $vl ?>">
                                    </td>
                                    <td>
                                        <input type="text" readonly class="form-control text-center lev text-dark">
                                    </td>
                                </tr>
                            <?php
                                        $selected   = false;
                                        $nx++;
                                    }
                                }
                            ?>
                            <tr>
                                <td class="par_name">Faktor Lainnya</td>
                                <td colspan="3"><input type="text" value="<?= ($EDD_HSTRY_DT["ADD_ARF"] ?? '') ?>" <?= ((isset($EDD_HSTRY_DT["ADD_ARF"])) ? 'readonly' : '') ?> required class="form-control text-center text-dark" id="lin"></td>
                            </tr>
                            <tr>
                                <td class="par_name">Analisa Dan Rekomendasi</td>
                                <td colspan="3"><input type="text" value="<?= ($EDD_HSTRY_DT["ADD_RKM"] ?? '') ?>" <?= ((isset($EDD_HSTRY_DT["ADD_RKM"])) ? 'readonly' : '') ?> required class="form-control text-center text-dark" id="rin"></td>
                            </tr>
                            
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-right">
                <?php if(isset($EDD_HSTRY_DT["ADD_DATTIME"])){ ?>
                    <a class="btn btn-lg btn-info" target="_blank" href="/export/apuppt_evluasi_edd_pdf?acc=<?= Helper::form_input($_GET["d"]) ?>">Print</a>
                <?php }else{ ?>
                    <form method="post" id="frm">
                        <?php
                            for($n = 1; $n <= $nx; $n++){
                                echo '<input type="hidden" name="'.Apuppt::$eddParam.'" class="text-center hid" readonly required>';
                            }
                        ?>
                        <input type="hidden" name="anf" class="text-center lin hid" readonly required>
                        <input type="hidden" name="rkm" class="text-center rin hid" readonly required>
                        <input type="hidden" name="iser" readonly value="<?= $x ?>" required>
                        <button type="submit" name="updt" class="btn btn-lg btn-primary">Evaluasi</button>
                    </form>
                <?php } ?>
            </div>
        </div>
    </div>
    <div class="col-md-3"></div>
</div>

<script>
    $(document).ready(() => {
        let par_name = Array.from(document.getElementsByClassName('par_name'));
        let par      = Array.from(document.getElementsByClassName('par'));
        let lev      = Array.from(document.getElementsByClassName('lev'));
        let hid      = Array.from(document.getElementsByClassName('hid'));
        document.getElementById("lin").addEventListener('keyup', function(ev){
            if(hid.find((hvl) => { return hvl.className.includes(`${ev.target.id}`); })){
                hid.find((hvl) => { return hvl.className.includes(`${ev.target.id}`); }).value = this.value
            }
        });
        document.getElementById("rin").addEventListener('keyup', function(ev){
            if(hid.find((hvl) => { return hvl.className.includes(`${ev.target.id}`); })){
                hid.find((hvl) => { return hvl.className.includes(`${ev.target.id}`); }).value = this.value
            }
        });
        par.forEach(function(el, i){
            lev[i].value = el.options[el.selectedIndex].value;
            if(hid.length){
                hid[i].value = (el.options[el.selectedIndex].dataset.x !== undefined) ? el.options[el.selectedIndex].dataset.x : null;
                hid[i].name  = (el.dataset.typ === undefined) ? null : '<?php echo Apuppt::$eddParam ?>'+el.dataset.typ;
            }
            el.addEventListener('change', function(e){
                lev[i].value = e.currentTarget.value;
                if(hid.length){
                    hid[i].value = e.currentTarget.options[e.currentTarget.selectedIndex].dataset.x;
                }
            });
        });
        $('.par').trigger('change');
        document.getElementById('frm')?.addEventListener('submit', function(e){
            e.preventDefault();
            let ARR_VAL = [];
            let num = null;
            hid.forEach(function(elem, i){ARR_VAL.push(elem.value); num = i;});
            
            if(!ARR_VAL.includes('')){
                // e.target.submit();
    
                let data = $(this).serialize(), url = "/ajax/post/apuppt/edd/action";
                // console.log(data, url);
                
                Swal.fire({
                    text: "Please wait...",
                    allowOutsideClick: false,
                    allowEscapeKey   : false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });
                $.ajax({
                    url: url,
                    type: 'post',
                    dataType: "json",
                    data: new FormData(this),
                    contentType: false,
                    processData: false,
                    cache: false,
                }).done((resp) => {
                    $('#modal-datepicker').modal('hide');
                    Swal.fire(resp.alert).then(() => {
                        if(resp.success) {
                            if(resp?.data?.reloc?.length){
                                location.href = resp?.data?.reloc;
                            }else{ location.reload(); }
                        }
                    });
    
                });
            }else{
                // let node = (lev[ARR_VAL.indexOf('')] != undefined) ? lev[ARR_VAL.indexOf('')].parentElement.previousElementSibling.previousElementSibling.previousElementSibling.innerText : ((lev[(ARR_VAL.indexOf('') - 1)].parentElement.parentElement.nextElementSibling.children[0].innerText != undefined) ? lev[(ARR_VAL.indexOf('') - 1)].parentElement.parentElement.nextElementSibling.children[0].innerText : lev[(ARR_VAL.indexOf('') - 2)].parentElement.parentElement.nextElementSibling.nextElementSibling.children[0].innerText);
                // alert(`Harap Pilih/Isi Keterangan Data Pada Baris ${par_name[ARR_VAL.indexOf('')].innerText}`); 
                Swal.fire(`Harap Pilih/Isi Keterangan Data Pada Baris ${par_name[ARR_VAL.indexOf('')].innerText}`); 
            }
        });
    });
</script>