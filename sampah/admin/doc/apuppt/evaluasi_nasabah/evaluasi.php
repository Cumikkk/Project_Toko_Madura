<?php
    use App\Models\Apuppt;
    use App\Models\Helper;
    use App\Models\FileUpload;
    use Config\Core\Database;

    $db         = Database::connect();
    $RSLT_DT    = Apuppt::dataEvaluasiNasabah(Helper::form_input($_GET["d"]));
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Home</a></li>
        <li class="breadcrumb-item"><a href="#">APUPPT</a></li>
        <li class="breadcrumb-item"><a href="#">Evaluasi Nasabah</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail Evaluasi Nasabah</li>
    </ol>
</nav>
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary card-outline">
            <div class="card-header font-weight-bold">Informasi Nasabah</div>
            <div class="card-body box-profile mb-3">
                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Nama Nasabah</b> <a class="float-right"><?php echo $RSLT_DT["ACC_F_APP_PRIBADI_NAMA"] ?></a>
                    </li>
                    <li class="list-group-item">
                        <b>NIK</b> <a class="float-right"><?php echo $RSLT_DT["ACC_F_APP_PRIBADI_ID"].' '.Apuppt::get_prov_nik($RSLT_DT["ACC_F_APP_PRIBADI_ID"], 'name') ?></a>
                    </li>
                    <li class="list-group-item">
                        <b>NPWP</b> <a class="float-right"><?php echo $RSLT_DT["ACC_F_APP_PRIBADI_NPWP"]?></a>
                    </li>
                    <li class="list-group-item">
                        <b>Tempat/Tanggal Lahir</b> <a class="float-right"><?php echo $RSLT_DT["ACC_F_APP_PRIBADI_TMPTLHR"].'/'.$RSLT_DT["ACC_F_APP_PRIBADI_TGLLHR"] ?></a>
                    </li>
                    <li class="list-group-item">
                        <b>Alamat</b> <a class="float-right"><?php echo $RSLT_DT["ACC_F_APP_PRIBADI_ALAMAT"] ?></a>
                    </li>
                    <li class="list-group-item">
                        <b>Kode Pos</b> <a class="float-right"><?php echo $RSLT_DT["ACC_F_APP_PRIBADI_ZIP"].' '.Apuppt::get_prov($RSLT_DT["ACC_F_APP_PRIBADI_ZIP"], 'name') ?></a>
                    </li>
                    <li class="list-group-item">
                        <b>No. Accout</b> <a class="float-right"><?php echo $RSLT_DT["ACC_LOGIN"] ?></a>
                    </li>
                    <li class="list-group-item">
                        <b>Tanggal Buka Account</b> <a class="float-right"><?php echo $RSLT_DT["ACC_DATETIME"] ?></a>
                    </li>
                    <li class="list-group-item">
                        <b>Produk Investasi</b> <a class="float-right"><?php echo $RSLT_DT["PRD"] ?></a>
                    </li>
                    <li class="list-group-item">
                        <b>Besaran Investasi Awal</b> <a class="float-right"><?php echo number_format($RSLT_DT["ACC_INITIALMARGIN"], 0) ?></a>
                    </li>
                    <li class="list-group-item">
                        <b>Pekerjaan/Profesi Nasabah</b> <a class="float-right"><?php echo $RSLT_DT["ACC_F_APP_KRJ_TYPE"] ?></a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header font-weight-bold">Dokumen Nasabah</div>
            <div class="card-body">
                <div class="table-responsive">
                    <div class="row">
                        <div class="col-md-3 mb-3 text-center">
                            <div>
                                <?php if($RSLT_DT['ACC_F_APP_FILE_IMG'] == ''|| $RSLT_DT['ACC_F_APP_FILE_IMG'] == '-' ){ ?>
                                    <img src="/assets/img/unknown-file.png" width="100%">
                                <?php } else { ?>
                                    <a target="_blank" href="<?php echo FileUpload::awsFile($RSLT_DT['ACC_F_APP_FILE_IMG']); ?>">
                                        <img src="<?php echo FileUpload::awsFile($RSLT_DT['ACC_F_APP_FILE_IMG']); ?>" width="75%">
                                    </a>
                                    <hr>
                                <?php }; ?>
                                <strong><u>Dokumen Pendukung</u></strong>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3 text-center">
                            <div>
                                <?php if($RSLT_DT['ACC_F_APP_FILE_FOTO'] == ''|| $RSLT_DT['ACC_F_APP_FILE_FOTO'] == '-' ){ ?>
                                    <img src="/assets/img/unknown-file.png" width="100%">
                                <?php } else { ?>
                                    <a target="_blank" href="<?php echo FileUpload::awsFile($RSLT_DT['ACC_F_APP_FILE_FOTO']); ?>">
                                        <img src="<?php echo FileUpload::awsFile($RSLT_DT['ACC_F_APP_FILE_FOTO']); ?>" width="75%">
                                    </a>
                                    <hr>
                                <?php }; ?>
                                <strong><u>Foto Terbaru</u></strong>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3 text-center">
                            <div>
                                <?php if($RSLT_DT['ACC_F_APP_FILE_ID'] == '' || $RSLT_DT['ACC_F_APP_FILE_ID'] == '-' ){ ?>
                                    <img src="/assets/img/unknown-file.png" width="100%">
                                <?php } else { ?>
                                    <a target="_blank" href="<?php echo FileUpload::awsFile($RSLT_DT['ACC_F_APP_FILE_ID']); ?>">
                                        <img src="<?php echo FileUpload::awsFile($RSLT_DT['ACC_F_APP_FILE_ID']); ?>" width="75%">
                                    </a>
                                    <hr>
                                <?php }; ?>
                                <strong><u>Foto Identitas</u></strong>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3 text-center">
                            <div>
                                <?php if($RSLT_DT['ACC_F_APP_FILE_IMG2'] == ''|| $RSLT_DT['ACC_F_APP_FILE_IMG2'] == '-' ){ ?>
                                    <img src="/assets/img/unknown-file.png" width="100%">
                                <?php } else { ?>
                                    <a target="_blank" href="<?php echo FileUpload::awsFile($RSLT_DT['ACC_F_APP_FILE_IMG2']); ?>">
                                        <img src="<?php echo FileUpload::awsFile($RSLT_DT['ACC_F_APP_FILE_IMG2']); ?>" width="75%">
                                    </a>
                                    <hr>
                                <?php }; ?>
                                <strong><u>Dokumen Pendukung Lainya</u></strong>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3 text-center"><div>&nbsp;</div></div>
                        <div class="col-md-4 mb-3 text-center">
                            <div>
                                <a target="_blank" href="<?php echo FileUpload::awsFile($RSLT_DT['DPWD_PIC']); ?>">
                                    <img src="<?php echo FileUpload::awsFile($RSLT_DT['DPWD_PIC']); ?>" width="75%">
                                </a>
                                <hr>
                                <strong><u>Deposit New Account</u></strong>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3 text-center"><div>&nbsp;</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3 mb-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header font-weight-bold">
                Faktor-faktor yang di periksa
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered" width="100%">
                        <thead class="bg-success">
                            <tr>
                                <th style="vertical-align: middle" class="text-white text-center">No.</th>
                                <th style="vertical-align: middle" class="text-white text-center">Faktor Risiko</th>
                                <th style="vertical-align: middle" class="text-white text-center">Keterangan Data</th>
                                <th style="vertical-align: middle" class="text-white text-center">Nilai Risiko</th>
                                <th style="vertical-align: middle" class="text-white text-center">Bobot Risiko</th>
                                <th style="vertical-align: middle" class="text-white text-center">Total Risiko</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $SQL_NAPUPAR = mysqli_query($db, '
                                    SELECT
                                        tb_rangetype.RATYP_NAME AS TP,
                                        tb_rangetype.ID_RATYP AS NSBR_TYPE,
                                        tb_rangetype.RATYP_BBR AS NSBR_BBTRISK
                                    FROM tb_rangetype
                                    ORDER BY CASE NSBR_TYPE
                                        WHEN 9 THEN 1
                                        WHEN 8 THEN 2
                                        WHEN 7 THEN 3
                                        WHEN 1 THEN 4
                                        WHEN 2 THEN 5
                                        WHEN 3 THEN 6
                                        WHEN 5 THEN 7
                                        WHEN 4 THEN 8
                                        WHEN 6 THEN 9
                                    ELSE 10 END
                                ');
                                if($SQL_NAPUPAR && mysqli_num_rows($SQL_NAPUPAR) > 0){
                                    $nnum = 0;
                                    $i    = 0;
                                    while($RSLT_NAPUPAR = mysqli_fetch_assoc($SQL_NAPUPAR)){
                                        $i++;
                            ?>
                                <tr>
                                    <td class="text-center"><?= ++$nnum; ?>.</td>
                                    <td>
                                        <?php
                                            if($RSLT_NAPUPAR["TP"] == "Risiko DTTOT & DPPSPM"){
                                                echo '
                                                    <div class="row">
                                                        <div class="col-8">'.$RSLT_NAPUPAR["TP"].'</div>
                                                        <div class="col-4"><a target="_blank" href="/apuppt/tabel_dttot/view/'.Apuppt::ddttotChk($RSLT_DT["ACC_F_APP_PRIBADI_NAMA"],$RSLT_DT["ACC_F_APP_PRIBADI_ID"], "link").'">Lihat Potensi('.Apuppt::ddttotChk($RSLT_DT["ACC_F_APP_PRIBADI_NAMA"],$RSLT_DT["ACC_F_APP_PRIBADI_ID"], "jumlah").')</a></div>
                                                    </div>
                                                ';
                                            }else{ echo $RSLT_NAPUPAR["TP"]; }
                                        ?>
                                    </td>
                                    <td> 
                                        <select 
                                            class="form-control text-center par" 
                                            data-bbr="<?php echo $RSLT_NAPUPAR["NSBR_BBTRISK"] ?>" 
                                            data-typ="<?= $RSLT_NAPUPAR["NSBR_TYPE"] ?>"
                                        >
                                            <option value disabled selected>Pilih salah satu</option>
                                            <?php
                                                $SQL_NME = mysqli_query($db, '
                                                    SELECT
                                                        tb_rangensb.ID_NSBR,
                                                        tb_rangensb.NSBR_TYPE,
                                                        tb_rangensb.NSBR_TYNAME,
                                                        tb_rangensb.NSBR_VAL
                                                    FROM tb_rangensb
                                                    WHERE tb_rangensb.NSBR_TYPE = '.$RSLT_NAPUPAR["NSBR_TYPE"].'
                                                ');
                                                if($SQL_NME && mysqli_num_rows($SQL_NME) > 0){
                                                    $APU_DT     = json_decode(((!empty($RSLT_DT["JSN_DT_APU"])) ? $RSLT_DT["JSN_DT_APU"] :'[]'), true);
                                                    foreach(mysqli_fetch_all($SQL_NME, MYSQLI_ASSOC) as $RSLT_NME){
                                                        $selected   = false;
                                                        if($RSLT_NME["NSBR_TYPE"] == 5){ 
                                                            $ARR_KRJ[] = $RSLT_NME["NSBR_TYNAME"]; 
                                                        }
                                                        $selected = ((isset($APU_DT[$RSLT_NME["NSBR_TYPE"]]) && $APU_DT[$RSLT_NME["NSBR_TYPE"]] == $RSLT_NME["ID_NSBR"]) ? 'selected' : (($selected == false) ? Apuppt::matchingToRacc(Helper::form_input($_GET["d"]), $RSLT_NME["NSBR_TYNAME"], $RSLT_NAPUPAR["TP"]) : ''));
                                            ?>
                                                <option 
                                                    value="<?php echo $RSLT_NME["NSBR_VAL"] ?>" 
                                                    data-x="<?php echo base64_encode($RSLT_NME["ID_NSBR"]) ?>" 
                                                    <?= $selected ?>
                                                >
                                                    <?php echo $RSLT_NME["NSBR_TYNAME"]; ?>
                                                </option>
                                            <?php
                                                    }
                                                }
                                            ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control text-dark text-center nir" readonly>
                                    </td>
                                    <td class="text-center"><input type="number" class="form-control text-center bbr" value="<?= $RSLT_NAPUPAR["NSBR_BBTRISK"] ?>" disabled></td>
                                    <td class="text-center"><input type="number" class="form-control text-center ttr" disabled></td>
                                </tr>
                            <?php
                                    }
                                }
                            ?>
                            <tr>
                                <td class="text-center" colspan="4">
                                    <h3>Penilaian Risiko Keseluruhan/Total:</h3>
                                </td>
                                <td colspan="2"><input type="number" class="form-control text-dark text-center" id="total" readonly></td>
                            </tr>
                            <tr>
                                <td class="text-center" colspan="4">
                                    <h3>Tingkat Risiko:</h3>
                                </td>
                                <td colspan="2"><input type="text" class="form-control text-dark text-center" id="tingResk" readonly></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-right">
                <form method="post" id="evl-form">
                    <?php if($RSLT_DT["APU_CMPLT"] == 1){ ?>
                        <a target="_blank" href="/export/apuppt_evluasi_nasabah_pdf?acc=<?= Helper::form_input($_GET["d"]) ?>" class="btn btn-lg btn-info">Print</a>
                    <?php }else if(isset($RSLT_DT["APU_X"])){ ?>
                        <button type="submit" name="iser" class="btn btn-lg btn-primary">Update</button>
                    <?php }else{ ?>
                        <button type="submit" name="iser" class="btn btn-lg btn-success">Evaluasi</button>
                    <?php } ?>
                    <input type="hidden" name="mrx" value="<?= Helper::form_input($_GET["d"]) ?>">
                    <?php
                        for($n = 1; $n <= $i; $n++){
                            echo '<input type="hidden" name="'.Apuppt::$evNasParamPrp.'" class="text-center hid" readonly>';
                        }
                    ?>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
        function isFloat(n){
            return Number(n) === n && n % 1 !== 0;
        }
        $('.par').on('change', function(e){
            console.log($('.par').index(this), $(this).val());
            var jma   = 0;
            var tul   = 0;
            var tnm   = 0;
            var rtr   = 0;
            var prk   = 0;
            $('.nir').eq($('.par').index(this)).val($(this).val());
            if(Boolean($(this).find(':selected').val())){
                $('.hid').eq($('.par').index(this)).attr('name', `<?= Apuppt::$evNasParamPrp ?>${$(this).data('typ')}`);
                $('.hid').eq($('.par').index(this)).val($(this).find(':selected').data('x'));
            }
            if(Boolean($(this).data('grp'))){
                var grpEl = $(this).parents('table').find(`input[data-grn="${$(this).data('grp')}"]:not([data-mhn="${$(this).data('mrp')}"])`);
                let ARR   = [];
                let jmd   =  Array.from($(this).parents('table').find(`select[data-grp="${$(this).data('grp')}"]`)).reduce((fv, sv, ix, IAR) => {
                    if(ix == 1){
                        ARR.push(fv);
                    }
                    if($(fv).data('mrp') == $(sv).data('mrp')){
                        ARR.push(sv);
                    }
                    return (ix == (IAR.length-1)) ? $(ARR.pop()).data('gbr') : sv;
                });
                if(grpEl.length > 1){
                    // console.log(grpEl, 'Jamak', $(this));
                    tul = Number($(this).parents('tr').find('.nir').val()); 
                    grpEl.each((i, e) => {
                        jma += Number($(e).val());
                    });
                    jma = Number(jma/jmd);
                }else{
                    // console.log(grpEl, 'Tunggal', $(this));
                    tul = Number(grpEl.val());
                    $(this).parents('tr').find('.nir').each((i, e) => {
                        jma += Number($(e).val());
                    });
                    jma = Number(jma/jmd);
                }
                // console.log(jma, tul);
                rtr = Number((((jma+tul) * $(this).data('bbr'))));
                $(this).parents('table').find(`input[data-ttrgrp="${$(this).data('grp')}"]`).val((isFloat(rtr) ? rtr.toFixed(3) : rtr));
            }else{
                $(this).parents('tr').find('.nir').each((i, e) => {
                    tnm += Number($(e).val());
                });
                rtr = Number((((tnm) * $(this).data('bbr'))));
                $(this).parents('tr').find('.ttr').val((isFloat(rtr) ? rtr.toFixed(3) : rtr));
            }
            $('.ttr').each((id, el) => {
                prk += Number($(el).val());
            });
            $('#total').val((isFloat(prk) ? prk.toFixed(3) : prk));
            $.ajax({
                url      : '/ajax/post/apuppt/evaluasi_nasabah/get_range',
                type     : 'POST',
                dataType : 'JSON',
                data     : {
                    val2 : document.getElementById('total').value
                }
            }).done(function(resp){
                document.getElementById('tingResk').value = (resp?.success == false) ? null : resp?.message;
            });
        });
        $('.par').trigger('change');

        $('#evl-form').on('submit', function(ev){
            ev.preventDefault();
            let data = $(this).serialize(), url = "/ajax/post/apuppt/evaluasi_nasabah/action";
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
        });
    });
</script>