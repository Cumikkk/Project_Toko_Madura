<?php
    // echo '<pre>';
    // var_dump($db);
    // echo '</pre>';die;
?>
<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Penilaian Risiko</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);">APUPPT</a></li>
            <li class="breadcrumb-item active" aria-current="page">Penilaian Risiko</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header font-weight-bold">Range Nilai Risiko dan Tingkat Rsiko</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th style="vertical-align: middle" class="text-center">No.</th>
                                <th style="vertical-align: middle" class="text-center">Range Nilai Risiko</th>
                                <th style="vertical-align: middle" class="text-center">Tingkat Risiko</th>
                                <th style="vertical-align: middle" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $SQL_RNG = mysqli_query($db,'
                                    SELECT
                                        tb_range.RNG_MIN,
                                        tb_range.RNG_MAX,
                                        tb_range.RNG_LEVEL,
                                        tb_range.RNG_TYPE,
                                        MD5(MD5(tb_range.ID_RNG)) AS ID_RNG
                                    FROM tb_range
                                    WHERE tb_range.RNG_TYPE = 1
                                ');
                                if($SQL_RNG){
                                    $i = 1;
                                    while($RSLT_RNG = mysqli_fetch_assoc($SQL_RNG)){
                            ?>
                                <tr>
                                    <td class="text-center"><?php echo $i.'.'; ?></td>
                                    <td class="text-center"><?php echo $RSLT_RNG["RNG_MIN"].' - '.$RSLT_RNG["RNG_MAX"] ?></td>
                                    <td class="text-center"><?php echo $RSLT_RNG["RNG_LEVEL"] ?></td>
                                    <td class="text-center">
                                        <button data-bs-target="#modal_updt" data-bs-toggle="modal" class="btn btn-sm btn-primary text-white mdlUpdt" 
                                            data-mn="<?php echo $RSLT_RNG["RNG_MIN"] ?>"
                                            data-mx="<?php echo $RSLT_RNG["RNG_MAX"] ?>"
                                            data-lv="<?php echo $RSLT_RNG["RNG_LEVEL"] ?>"
                                            data-ty="<?php echo $RSLT_RNG["RNG_TYPE"] ?>"
                                            data-xd="<?php echo $RSLT_RNG["ID_RNG"] ?>"
                                        >
                                            Edit Data <i class="fa fa-pencil" aria-hidden="true"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php       
                                        $i++;            
                                    }
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header font-weight-bold">Klasifikasi Risiko Nasabah Berdasarkan Total Point</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table" class="table table-striped table-hover table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th style="vertical-align: middle" class="text-center">No.</th>
                                <th style="vertical-align: middle" class="text-center">Total Point Range</th>
                                <th style="vertical-align: middle" class="text-center">Tingkat Risiko</th>
                                <th style="vertical-align: middle" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $SQL_RISK_NSB = mysqli_query($db,'
                                    SELECT
                                        tb_range.RNG_MIN,
                                        tb_range.RNG_MAX,
                                        tb_range.RNG_LEVEL,
                                        tb_range.RNG_TYPE,
                                        MD5(MD5(tb_range.ID_RNG)) AS ID_RNG
                                    FROM tb_range
                                    WHERE tb_range.RNG_TYPE = 2
                                ');
                                if($SQL_RISK_NSB){
                                    $i = 1;
                                    while($RSLT_RNSB = mysqli_fetch_assoc($SQL_RISK_NSB)){
                            ?>
                                <tr>
                                    <td class="text-center"><?php echo $i.'.'; ?></td>
                                    <?php 
                                        if($RSLT_RNSB["RNG_LEVEL"] == 'Rendah' || $RSLT_RNSB["RNG_LEVEL"] == 'Tinggi'){
                                            $val = ($RSLT_RNSB["RNG_LEVEL"] == 'Rendah') ? $RSLT_RNSB["RNG_MAX"] : (($RSLT_RNSB["RNG_LEVEL"] == 'Tinggi') ? $RSLT_RNSB["RNG_MIN"] : '');
                                            $arw = ($RSLT_RNSB["RNG_LEVEL"] == 'Rendah') ? '<' : (($RSLT_RNSB["RNG_LEVEL"] == 'Tinggi') ? '>' : '');
                                    ?>  
                                        <td class="text-center"><?php echo $arw.$val ?></td>
                                    <?php }else{ ?>
                                        <td class="text-center"><?php echo $RSLT_RNSB["RNG_MIN"].' - '.$RSLT_RNSB["RNG_MAX"] ?></td>
                                    <?php }?>
                                    <td class="text-center"><?php echo $RSLT_RNSB["RNG_LEVEL"] ?></td>
                                    <td class="text-center">
                                        <button data-bs-target="#modal_updt" data-bs-toggle="modal" class="btn btn-sm btn-primary text-white mdlUpdt" 
                                            data-mn="<?php echo $RSLT_RNSB["RNG_MIN"] ?>"
                                            data-mx="<?php echo $RSLT_RNSB["RNG_MAX"] ?>"
                                            data-lv="<?php echo $RSLT_RNSB["RNG_LEVEL"] ?>"
                                            data-ty="<?php echo $RSLT_RNSB["RNG_TYPE"] ?>"
                                            data-xd="<?php echo $RSLT_RNSB["ID_RNG"] ?>"
                                        >
                                            Edit Data <i class="fa fa-pencil" aria-hidden="true"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php       
                                        $i++;            
                                    }
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <?php
        $SQL_CD = mysqli_query($db,'
            SELECT
                tb_rangetype.RATYP_NAME AS TP,
                tb_rangetype.ID_RATYP AS NSBR_TYPE,
                tb_rangetype.RATYP_BBR AS NSBR_BBTRISK
            FROM tb_rangetype
        ') or die(mysqli_error($db));
        if($SQL_CD && mysqli_num_rows($SQL_CD) > 0){
            $ib = 0;
            while($CD_RSLT = mysqli_fetch_assoc($SQL_CD)){
                $wrd = ($CD_RSLT["NSBR_TYPE"] == 1) ? 'Nama' : (($CD_RSLT["NSBR_TYPE"] == 8) ? 'Status' : (($CD_RSLT["NSBR_TYPE"] == 9) ? 'Masuk' : NULL));
    ?>
        <div class="col-lg-12 mt-5">
            <div class="card">
                <div class="card-header font-weight-bold">
                    <?php echo $CD_RSLT["TP"] ?>
                    <div class="float-right row" style="margin-left: auto; justify-content: flex-end;">
                        <div class="col-4" style="flex-basis: max-content;">
                            <button class="btn btn-sm btn-primary pcl plus text-white"
                                data-hdr="<?php echo $CD_RSLT["TP"] ?>"
                                value="<?php echo md5(md5(($CD_RSLT["NSBR_TYPE"]))) ?>"
                            >
                                Update Bobot Risiko <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="col-4 row">
                            <div class="col-7" style="align-self: self-end;">
                                <label>Bobot Risiko</label>
                            </div>
                            <input type="number" class="form-control vbr text-center col-5" value="<?php echo $CD_RSLT["NSBR_BBTRISK"] ?>" id="brisk" name="brisk"  required autocomplete="off">
                        </div>
                        <div class="col-4" style="flex-basis: max-content;">
                            <button data-bs-target="#modal_insert" data-bs-toggle="modal" class="btn btn-sm btn-success ins plus text-white"
                                data-typ="<?php echo base64_encode($CD_RSLT["NSBR_TYPE"]) ?>"
                                data-hdr="<?php echo $CD_RSLT["TP"] ?>"
                            >
                                Tambahkan Parameter <i class="fa fa-plus" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                    
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-striped table-hover table-bordered" width="100%">
                            <thead class="<?php echo $BGARR[$ib] ?>">
                                <tr>
                                    <th style="vertical-align: middle" class="text-center">No.</th>
                                    <th style="vertical-align: middle" class="text-center">
                                        <?php
                                            if($CD_RSLT["NSBR_TYPE"] == 1){
                                                echo str_replace("Risiko Daftar",($wrd ?? ''),$CD_RSLT["TP"]);
                                            }else{ echo str_replace("Risiko",($wrd ?? ''),$CD_RSLT["TP"]); }
                                        ?>
                                    </th>
                                    <th style="vertical-align: middle" class="text-center">Tingkat Risiko</th>
                                    <th style="vertical-align: middle" class="text-center">Nilai Risiko</th>
                                    <th style="vertical-align: middle" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $SQL_NSB1 = mysqli_query($db,'
                                        SELECT
                                            tb_rangensb.NSBR_TYNAME,
                                            (
                                                SELECT
                                                    tb_range.RNG_LEVEL
                                                FROM tb_range
                                                WHERE tb_range.RNG_TYPE = 1
                                                AND (tb_rangensb.NSBR_VAL >= tb_range.RNG_MIN AND tb_rangensb.NSBR_VAL <= tb_range.RNG_MAX)
                                                LIMIT 1
                                            ) AS LV,
                                            tb_rangensb.NSBR_VAL,
                                            MD5(MD5(tb_rangensb.ID_NSBR)) AS ID_NSBR
                                        FROM tb_rangensb
                                        WHERE tb_rangensb.NSBR_TYPE = '.$CD_RSLT["NSBR_TYPE"].'
                                    ') or die(myslqi_error($db));
                                    if($SQL_NSB1 && mysqli_num_rows($SQL_NSB1) > 0){
                                        $i = 1;
                                        while($RSLT_NSB1 = mysqli_fetch_assoc($SQL_NSB1)){
                                ?>
                                    <tr>
                                        <td class="text-center"><?php echo $i.'.'; ?></td>
                                        <td class="text-center"><?php echo $RSLT_NSB1["NSBR_TYNAME"] ?></td>
                                        <td class="text-center"><?php echo $RSLT_NSB1["LV"] ?></td>
                                        <td class="text-center"><?php echo $RSLT_NSB1["NSBR_VAL"] ?></td>
                                        <td class="text-center">
                                            <button data-bs-target="#modal_updt_nsb" data-bs-toggle="modal" class="btn btn-sm btn-info text-white mdlNsb" 
                                                data-hdr="<?php echo $CD_RSLT["TP"] ?>"
                                                data-nme="<?php echo $RSLT_NSB1["NSBR_TYNAME"] ?>"
                                                data-lvl="<?php echo $RSLT_NSB1["LV"] ?>"
                                                data-idx="<?php echo $RSLT_NSB1["ID_NSBR"] ?>"
                                                data-bbr="<?php echo $RSLT_NSB1["NSBR_VAL"] ?>"
                                            >
                                                Edit Data <i class="fa fa-pencil" aria-hidden="true"></i>
                                            </button>
                                            <button class="btn btn-sm del btn-danger text-white" data-x="<?php echo $RSLT_NSB1["ID_NSBR"] ?>" data-nme="<?php echo $RSLT_NSB1["NSBR_TYNAME"] ?>">
                                                Hapus Data <i class="fa fa-trash" aria-hidden="true"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php   
                                        $i++;
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
            $ib += 1;
            }
        }
    ?>
</div>

<div class="modal fade" id="modal_updt" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form method="post" id="niltingrisk">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Form Update Nilai Risiko</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="mdlBdy">
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="typ" id="typ">
                    <input type="hidden" name="ixd" id="ixd">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="submit_updt" class="btn btn-success">Update</button>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="modal fade" id="modal_updt_nsb" tabindex="-1" role="dialog" aria-labelledby="hdr" aria-hidden="true">
    <form method="post" id="updt_nilrisk">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="hdr"></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Tingkat Risiko</label>
                        <input type="text" class="form-control text-center" id="tkr" readonly autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label id="bbl">Nilai Risiko</label>
                        <input type="number" class="form-control text-center" id="bbr" name="bbr"  required autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="idx" id="idx">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="submit_updt_nsb" class="btn btn-success">Update</button>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="modal fade" id="modal_insert" tabindex="-1" role="dialog" aria-labelledby="hdr" aria-hidden="true">
    <form method="post" id="createPar">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="hdr2"></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Parameter</label>
                        <input type="text" class="form-control text-center" name="prtr" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label id="bbl">Nilai Risiko</label>
                        <input type="number" class="form-control text-center" name="bbrk"  required autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="pyt" id="pyt">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="submit_ins" class="btn btn-success">Submit</button>
                </div>
            </div>
        </div>
    </form>
</div>
<form method="post" id="frm">
    <input type="hidden" name="st">
    <input type="hidden" name="x" id="x">
</form>
<form method="post" id="frm2">
    <input type="hidden" name="st2">
    <input type="hidden" name="x2" id="x2">
    <input type="hidden" name="v" id="v">
</form>

<script>
    $(document).ready(() => {

        let mdlUpdt = Array.from(document.getElementsByClassName('mdlUpdt'));
        let mdlNsb  = Array.from(document.getElementsByClassName('mdlNsb'));
        let ins     = Array.from(document.getElementsByClassName('ins'));
        let del     = Array.from(document.getElementsByClassName('del'));
        let pcl     = Array.from(document.getElementsByClassName('pcl'));
        let vbr     = Array.from(document.getElementsByClassName('vbr'));
        let mdlBdy  = document.getElementById('mdlBdy');
        let carHdr  = document.getElementById('exampleModalLabel');
        let typ     = document.getElementById('typ');
        let ixd     = document.getElementById('ixd');
        mdlUpdt.forEach(function(el){
            el.addEventListener('click', function(e){
                let val = e.currentTarget.dataset;
                typ.value = val.ty;
                ixd.value = val.xd;
                if(val.ty == 1){
                    carHdr.innerHTML = `Form Update Nilai Risiko`;
                    mdlBdy.innerHTML = `
                        <div class="form-group">
                            <label>Tingkat Risiko</label>
                            <input type="text" class="form-control text-center" value="${val.lv}" readonly autocomplete="off">
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-4">
                                    <label>Nilai Minimal</label>
                                    <input type="number" class="form-control text-center" name="min" value="${val.mn}" required autocomplete="off">
                                </div>
                                <div class="col-4 text-center">
                                    <label>(Sampai Dengan)</label>
                                    <br>
                                    <span style="font-size: xxx-large; line-height: 50%;">-</span>
                                </div>
                                <div class="col-4">
                                    <label>Nilai Maximal</label>
                                    <input type="number" class="form-control text-center" name="max" value="${val.mx}" required autocomplete="off">
                                </div>
                            </div>
                        </div>
                    `;
                }else if(val.ty == 2){
                    carHdr.innerHTML = `Form Update Klasifikasi Risiko Nasabah`;
                    let dcl = ``;
                    if(val.lv == 'Rendah'){
                        dcl = `
                            <div class="col-12">
                                <label>Terbilnag Rendah Jika Nilai Lebih Kecil-dari(<)</label>
                                <input type="number" class="form-control text-center" name="max" value="${val.mx}" required autocomplete="off">
                            </div>
                        `;
                    }else if(val.lv == 'Menengah'){
                        dcl = `
                            <div class="col-4">
                                <label>Nilai Minimal</label>
                                <input type="number" class="form-control text-center" name="min" value="${val.mn}" required autocomplete="off">
                            </div>
                            <div class="col-4 text-center">
                                <label>(Sampai Dengan)</label>
                                <br>
                                <span style="font-size: xxx-large; line-height: 50%;">-</span>
                            </div>
                            <div class="col-4">
                                <label>Nilai Maximal</label>
                                <input type="number" class="form-control text-center" name="max" value="${val.mx}" required autocomplete="off">
                            </div>
                        `;
                    }else if(val.lv == 'Tinggi'){
                        dcl = `
                            <div class="col-12">
                                <label>Terbilnag Tinggi Jika Nilai Lebih Besar-dari(>)</label>
                                <input type="number" class="form-control text-center" name="max" value="${val.mn}" required autocomplete="off">
                            </div>
                        `;
                    }
                    mdlBdy.innerHTML = `
                        <div class="form-group">
                            <label>Tingkat Risiko</label>
                            <input type="text" class="form-control text-center" name="lvl" value="${val.lv}" readonly required autocomplete="off">
                        </div>
                        <div class="form-group">
                            <div class="row">
                                ${dcl}
                            </div>
                        </div>
                    `;
                }
            });
        });

        $(`#niltingrisk`).on('submit', function(ev){
            $(`#modal_updt`).modal('hide');
            ev.preventDefault();
            let data = $(this).serialize(), url = "/ajax/post/apuppt/penilaian-risiko/update-tingkat-risiko";
            // console.log(data);
            Swal.fire({
                text: "Please wait...",
                allowOutsideClick: false,
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
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        if(resp?.data?.reloc?.length){
                            location.href = resp?.data?.reloc;
                        }else{ location.reload(); }
                    }
                });

            });
        });

        ins.forEach(function(el3){
            el3.addEventListener('click', function(event){
                let val = event.currentTarget.dataset;
                document.getElementById('hdr2').innerHTML = `Form Penambahan Parameter ${val.hdr}`;
                document.getElementById('pyt').value      = val.typ;
            });
        });
        $(`#createPar`).on('submit', function(e){
            $(`#modal_insert`).modal('hide');
            e.preventDefault();
            let data = $(this).serialize(), url = "/ajax/post/apuppt/penilaian-risiko/create-parameter";
            // console.log(data);
            Swal.fire({
                text: "Please wait...",
                allowOutsideClick: false,
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
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        if(resp?.data?.reloc?.length){
                            location.href = resp?.data?.reloc;
                        }else{ location.reload(); }
                    }
                });

            });
        });
        
        mdlNsb.forEach(function(el2){
            el2.addEventListener('click', function(ev){
                let val = ev.currentTarget.dataset;
                document.getElementById('hdr').innerHTML = `Update ${val.hdr}`;
                document.getElementById('bbl').innerHTML = `Bobot Risiko Dari ${val.nme}`;
                document.getElementById('tkr').value     = `${val.lvl}`;
                document.getElementById('bbr').value     = `${val.bbr}`;
                document.getElementById('idx').value     = `${val.idx}`;
            });
        });
        $(`#updt_nilrisk`).on('submit', function(e){
            $(`#modal_updt_nsb`).modal('hide');
            e.preventDefault();
            let data = $(this).serialize(), url = "/ajax/post/apuppt/penilaian-risiko/update-parameter";
            // console.log(data);
            Swal.fire({
                text: "Please wait...",
                allowOutsideClick: false,
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
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        if(resp?.data?.reloc?.length){
                            location.href = resp?.data?.reloc;
                        }else{ location.reload(); }
                    }
                });

            });
        });

        del.forEach(function(el4){
            el4.addEventListener('click', function(eve){
                let val = eve.currentTarget.dataset;
                document.getElementById('x').value = val.x;
                // document.getElementById('frm').submit();

                Swal.fire({
                    title: "Hapus Parameter",
                    text: `Konfirmasi Bahwa Anda Akan Menghapus Parameter "${val.nme}"`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Hapus",
                    cancelButtonText: "Batal",
                }).then((result) => {
                    if (result.isConfirmed) {
                        let data = $(`#frm`).serialize(), url = "/ajax/post/apuppt/penilaian-risiko/delete-parameter";
                        // console.log(data, url);
                        Swal.fire({
                            text: "Please wait...",
                            allowOutsideClick: false,
                            didOpen: function() {
                                Swal.showLoading();
                            }
                        });
                        $.ajax({
                            url: url,
                            type: 'post',
                            dataType: "json",
                            data: new FormData($(`#frm`)[0]),
                            contentType: false,
                            processData: false,
                            cache: false,
                        }).done((resp) => {
                            Swal.fire(resp.alert).then(() => {
                                if(resp.success) {
                                    if(resp?.data?.reloc?.length){
                                        location.href = resp?.data?.reloc;
                                    }else{ location.reload(); }
                                }
                            });

                        });
                    }
                });
            });
        });

        pcl.forEach(function(el5,i){
            el5.addEventListener('click', function(evt){
                var tVal = evt.currentTarget;
                document.getElementById('x2').value = tVal.value;
                document.getElementById('v').value  = vbr[i].value;
                
                // let cfm = confirm(`Konfirmasi Bahwa Anda Akan Update Bobot Risiko Dari ${tVal.dataset.hdr} Dengan Nilai ${vbr[i].value}`);
                // if(cfm){
                //     document.getElementById('frm2').submit();
                // }

                Swal.fire({
                    title: "Update Bobot Risiko",
                    text: `Konfirmasi Bahwa Anda Akan Update Bobot Risiko Dari ${tVal.dataset.hdr} Dengan Nilai ${vbr[i].value}`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Update",
                    cancelButtonText: "Batal",
                }).then((result) => {
                    if (result.isConfirmed) {
                        let data = $(`#frm2`).serialize(), url = "/ajax/post/apuppt/penilaian-risiko/update-bobot-risiko";
                        // console.log($(`#frm2`));
                        Swal.fire({
                            text: "Please wait...",
                            allowOutsideClick: false,
                            didOpen: function() {
                                Swal.showLoading();
                            }
                        });
                        $.ajax({
                            url: url,
                            type: 'post',
                            dataType: "json",
                            data: new FormData($(`#frm2`)[0]),
                            contentType: false,
                            processData: false,
                            cache: false,
                        }).done((resp) => {
                            Swal.fire(resp.alert).then(() => {
                                if(resp.success) {
                                    if(resp?.data?.reloc?.length){
                                        location.href = resp?.data?.reloc;
                                    }else{ location.reload(); }
                                }
                            });

                        });
                    }
                });

                
            });
        });
    });
</script>