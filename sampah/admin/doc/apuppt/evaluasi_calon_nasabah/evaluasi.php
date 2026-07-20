<?php

    use App\Models\Apuppt;
    use App\Models\User;
    use App\Models\Dpwd;
    use App\Models\Helper;
    use App\Models\FileUpload;
    use Config\Core\Database;
    $db       = Database::connect();
    $RSLT_DT  = Apuppt::evaluasiCalonNasabah(Helper::form_input($_GET["d"]));
    $RSLT_APU = Apuppt::srchEvaluasiCalonNasabah(Helper::form_input($_GET["d"]));

?>
<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Detail Evaluasi Calon Nasabah</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);">APUPPT</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);">Evaluasi Calon Nasabah</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detail Evaluasi Calon Nasabah</li>
        </ol>
    </div>
</div>
<?php if($RSLT_APU){ ?>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary card-outline">
                <div class="card-header font-weight-bold">Informasi Nasabah</div>
                <div class="card-body box-profile" style="height: 502.73px;">
                    <ul class="list-group list-group-unbordered mb-3">
                        <li class="list-group-item">
                            <b>Nama Nasabah</b> <a class="float-right"><?php echo $RSLT_APU["ACC_F_APP_PRIBADI_NAMA"] ?></a>
                        </li>
                        <li class="list-group-item">
                            <b>NIK</b> <a class="float-right"><?php echo $RSLT_APU["ACC_F_APP_PRIBADI_ID"].' '.Apuppt::get_prov_nik($RSLT_APU["ACC_F_APP_PRIBADI_ID"], 'name') ?></a>
                        </li>
                        <li class="list-group-item">
                            <b>Alamat</b> <a class="float-right"><?php echo $RSLT_APU["ACC_F_APP_PRIBADI_ALAMAT"] ?></a>
                        </li>
                        <li class="list-group-item">
                            <b>Kode Pos</b> <a class="float-right"><?php echo $RSLT_APU["ACC_F_APP_PRIBADI_ZIP"].' '.Apuppt::get_prov($RSLT_APU["ACC_F_APP_PRIBADI_ZIP"], 'name') ?></a>
                        </li>
                        <li class="list-group-item">
                            <b>No. Accout</b> <a class="float-right"><?php echo $RSLT_APU["ACC_LOGIN"] ?></a>
                        </li>
                        <li class="list-group-item">
                            <b>Tanggal Buka Account</b> <a class="float-right"><?php echo $RSLT_APU["ACC_DATETIME"] ?></a>
                        </li>
                        <li class="list-group-item">
                            <b>Produk Investasi</b> <a class="float-right"><?php echo $RSLT_APU["PRD"] ?></a>
                        </li>
                        <li class="list-group-item">
                            <b>Besaran Investasi Awal</b> <a class="float-right"><?php echo number_format($RSLT_APU["ACC_INITIALMARGIN"], 0) ?></a>
                        </li>
                        <li class="list-group-item">
                            <b>Pekerjaan/Profesi Nasabah</b> <a class="float-right"><?php echo $RSLT_APU["ACC_F_APP_KRJ_TYPE"] ?></a>
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
                                    <?php if($RSLT_APU['ACC_F_APP_FILE_IMG'] == ''|| $RSLT_APU['ACC_F_APP_FILE_IMG'] == '-' ){ ?>
                                        <img src="/assets/img/unknown-file.png" width="100%">
                                    <?php } else { ?>
                                        <a target="_blank" href="<?php echo FileUpload::awsFile($RSLT_APU['ACC_F_APP_FILE_IMG']); ?>">
                                            <img src="<?php echo FileUpload::awsFile($RSLT_APU['ACC_F_APP_FILE_IMG']); ?>" width="75%">
                                        </a>
                                        <hr>
                                    <?php }; ?>
                                    <strong><u>Dokumen Pendukung</u></strong>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3 text-center">
                                <div>
                                    <?php if($RSLT_APU['ACC_F_APP_FILE_FOTO'] == ''|| $RSLT_APU['ACC_F_APP_FILE_FOTO'] == '-' ){ ?>
                                        <img src="/assets/img/unknown-file.png" width="100%">
                                    <?php } else { ?>
                                        <a target="_blank" href="<?php echo FileUpload::awsFile($RSLT_APU['ACC_F_APP_FILE_FOTO']); ?>">
                                            <img src="<?php echo FileUpload::awsFile($RSLT_APU['ACC_F_APP_FILE_FOTO']); ?>" width="75%">
                                        </a>
                                        <hr>
                                    <?php }; ?>
                                    <strong><u>Foto Terbaru</u></strong>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3 text-center">
                                <div>
                                    <?php if($RSLT_APU['ACC_F_APP_FILE_ID'] == '' || $RSLT_APU['ACC_F_APP_FILE_ID'] == '-' ){ ?>
                                        <img src="/assets/img/unknown-file.png" width="100%">
                                    <?php } else { ?>
                                        <a target="_blank" href="<?php echo FileUpload::awsFile($RSLT_APU['ACC_F_APP_FILE_ID']); ?>">
                                            <img src="<?php echo FileUpload::awsFile($RSLT_APU['ACC_F_APP_FILE_ID']); ?>" width="75%">
                                        </a>
                                        <hr>
                                    <?php }; ?>
                                    <strong><u>Foto Identitas</u></strong>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3 text-center">
                                <div>
                                    <?php if($RSLT_APU['ACC_F_APP_FILE_IMG2'] == '' || $RSLT_APU['ACC_F_APP_FILE_IMG2'] == '-' ){ ?>
                                        <img src="/assets/img/unknown-file.png" width="100%">
                                    <?php } else { ?>
                                        <a target="_blank" href="<?php echo FileUpload::awsFile($RSLT_APU['ACC_F_APP_FILE_IMG2']); ?>">
                                            <img src="<?php echo FileUpload::awsFile($RSLT_APU['ACC_F_APP_FILE_IMG2']); ?>" width="75%">
                                        </a>
                                        <hr>
                                    <?php }; ?>
                                    <strong><u>Dokumen Pendukung Lainya</u></strong>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3 text-center"><div>&nbsp;</div></div>
                            <div class="col-md-4 mb-3 text-center">
                                <div>
                                    <a target="_blank" href="<?php echo FileUpload::awsFile($RSLT_APU['DPWD_PIC']); ?>" width="75%"></a>
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
                            <thead class="bg-primary">
                                <tr>
                                    <th style="vertical-align: middle" class="text-center text-white">No</th>
                                    <th style="vertical-align: middle" class="text-center text-white">Faktor Risiko</th>
                                    <th style="vertical-align: middle" class="text-center text-white">Keterangan Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $ARR_FKTR = mysqli_query($db,'SELECT * FROM tb_apuppt_evcannas_type');
                                    $fktr_row = mysqli_num_rows($ARR_FKTR);
                                    $no = 1;
                                    if($ARR_FKTR && $fktr_row > 0){
                                        $ARR_DB_VL = (json_decode($RSLT_APU['JSON_VL'], true) ?? []);
                                        while($RSLT_FKTR = mysqli_fetch_assoc($ARR_FKTR)){
                                ?>
                                    <tr>
                                        <td class="text-center"><?php echo $no.'.'; ?></td>
                                        <td>
                                            <?php
                                                if($RSLT_FKTR["EVCANNAS_TYPE_VAL"] == "Risiko DTTOT"){
                                                    echo '
                                                        <div class="row">
                                                            <div class="col-8">'.$RSLT_FKTR["EVCANNAS_TYPE_VAL"].'</div>
                                                            <div class="col-4">
                                                                <a target="_blank" href="/apuppt/tabel_dttot/view/'.Apuppt::ddttotChk($RSLT_APU["ACC_F_APP_PRIBADI_NAMA"],$RSLT_APU["ACC_F_APP_PRIBADI_ID"], "link").'">
                                                                    Lihat Potensi('.Apuppt::ddttotChk($RSLT_APU["ACC_F_APP_PRIBADI_NAMA"],$RSLT_APU["ACC_F_APP_PRIBADI_ID"], "jumlah").')
                                                                </a>
                                                            </div>
                                                        </div>
                                                    ';
                                                }else{ echo $RSLT_FKTR["EVCANNAS_TYPE_VAL"]; }
                                            ?>
                                        </td>
                                        <td>
                                            <select class="form-control text-dark text-center slk" data-slct="<?php echo ($ARR_DB_VL[$RSLT_FKTR["ID_EVCANNAS_TYPE"]] ?? ''); ?>" name="<?php echo Apuppt::$evaluasiCalonNasabahProp.$RSLT_FKTR["ID_EVCANNAS_TYPE"]; ?>" required disabled>
                                                <option value disabled selected>Plih Keterangan Data</option>
                                                <option value="0">Tidak Termasuk Daftar</option>
                                                <option value="1">Termasuk Dalam Daftar</option>
                                            </select>
                                        </td>
                                    </tr>
                                <?php
                                            $no++;
                                        }
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <?php
                        $ARR_BTN = [
                            '<button type="button" class="btn btn-lg btn-danger">Ditolak</button>',
                            '<button type="button" class="btn btn-lg btn-warning">Dipertimbangkan</button>',
                            '<button type="button" class="btn btn-lg btn-success">Dilanjutkan</button>'
                        ];
                        echo $ARR_BTN[$RSLT_APU["EVCAN_CONF"]];
                    ?>
                </div>
            </div>
        </div>
    </div>
    <script>
        Array.from(document.getElementsByClassName('slk')).forEach((el) => {
            Array.from(el.options).find((opt) => { return opt.value == el.dataset.slct; })?.toggleAttribute("selected");
        });
    </script>
<?php }else{ ?>
    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary card-outline">
                <div class="card-header font-weight-bold">Informasi Nasabah</div>
                <div class="card-body box-profile mb-3">
                    <ul class="list-group list-group-unbordered">
                        <li class="list-group-item">
                            <b>Nama Nasabah</b> <a class="float-right"><?php echo $RSLT_DT["ACC_F_APP_PRIBADI_NAMA"] ?></a>
                        </li>
                        <li class="list-group-item">
                            <b>NIK</b> <a class="float-right"><?php echo $RSLT_DT["ACC_F_APP_PRIBADI_ID"].' '.Apuppt::get_prov_nik($RSLT_DT["ACC_F_APP_PRIBADI_ID"], 'name') ?></a>
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
                    <div class="table-responsive mb-3">
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
                                        <a target="_blank" href="<?php echo FileUpload::awsFile($RSLT_DT['ACC_F_APP_FILE_FOTO']); ?>" width="75%">
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
                                        <a target="_blank" href="<?php echo FileUpload::awsFile($RSLT_DT['ACC_F_APP_FILE_ID']); ?>" width="75%">
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
                <form method="post" id="evcannas-form" action="<?= $filePermission['link'] ?>">
                    <div class="card-header font-weight-bold">
                        Faktor-faktor yang di periksa
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-bordered" width="100%">
                                <thead class="bg-primary">
                                    <tr>
                                        <th style="vertical-align: middle" class="text-center text-white">No</th>
                                        <th style="vertical-align: middle" class="text-center text-white">Faktor Risiko</th>
                                        <th style="vertical-align: middle" class="text-center text-white">Keterangan Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $ARR_FKTR = mysqli_query($db, $qwr = 'SELECT * FROM tb_apuppt_evcannas_type');
                                        $no = 1;
                                        if($ARR_FKTR && mysqli_num_rows($ARR_FKTR) > 0){
                                            while($RSLT_FKTR = mysqli_fetch_assoc($ARR_FKTR)){
                                    ?>
                                        <tr>
                                            <td class="text-center"><?php echo $no.'.'; ?></td>
                                            <td>
                                                <?php
                                                    if($RSLT_FKTR["EVCANNAS_TYPE_VAL"] == "Risiko DTTOT"){
                                                        echo '
                                                            <div class="row">
                                                                <div class="col-8">'.$RSLT_FKTR["EVCANNAS_TYPE_VAL"].'</div>
                                                                <div class="col-4">
                                                                    <a target="_blank" href="/apuppt/tabel_dttot/view/'.Apuppt::ddttotChk($RSLT_DT["ACC_F_APP_PRIBADI_NAMA"],$RSLT_DT["ACC_F_APP_PRIBADI_ID"], "link").'">
                                                                        Lihat Potensi('.Apuppt::ddttotChk($RSLT_DT["ACC_F_APP_PRIBADI_NAMA"],$RSLT_DT["ACC_F_APP_PRIBADI_ID"], "jumlah").')
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        ';
                                                    }else{ echo $RSLT_FKTR["EVCANNAS_TYPE_VAL"]; }
                                                ?>
                                            </td>
                                            <td>
                                                <select class="form-control text-dark text-center slk" name="<?php echo Apuppt::$evaluasiCalonNasabahProp.$RSLT_FKTR["ID_EVCANNAS_TYPE"]; ?>" required>
                                                    <option value disabled selected>Plih Keterangan Data</option>
                                                    <option value="0">Tidak Termasuk Daftar</option>
                                                    <option value="1">Termasuk Dalam Daftar</option>
                                                </select>
                                            </td>
                                        </tr>
                                    <?php
                                                $no++;
                                            }
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <input type="hidden" name="iser_r" id="iser_r">
                        <input type="hidden" name="mrx" id="mrx" value="<?= Helper::form_input($_GET["d"]) ?>">
                        <button type="submit" name="iser" value="0" class="btn btn-lg btn-danger">Ditolak</button>
                        <button type="submit" name="iser" value="1" class="btn btn-lg btn-warning">Dipertimbangkan</button>
                        <button type="submit" name="iser" value="2" class="btn btn-lg btn-success">Dilanjutkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function(){
            $('.slk').on('change', function(ev){
                if($('.slk')[0].value == 1 || $('.slk')[1].value == 1){
                    $('button[name="iser"][value="2"]').hide();
                    $('button[name="iser"][value="1"]').hide();
                    $('button[name="iser"][value="0"]').show();
                }else if($('.slk')[2].value == 1 || $('.slk')[3].value == 1){
                    $('button[name="iser"][value="0"]').hide();
                    $('button[name="iser"][value="2"]').hide();
                    $('button[name="iser"][value="1"]').show();
                }else if(($('.slk')[0].value == 0 && $('.slk')[0].value.length > 0) && ($('.slk')[1].value == 0 && $('.slk')[1].value.length > 0) && ($('.slk')[2].value == 0 && $('.slk')[2].value.length > 0) && ($('.slk')[3].value == 0 && $('.slk')[3].value.length > 0)){
                    $('button[name="iser"][value="2"]').show();
                    $('button[name="iser"][value="1"]').hide();
                    $('button[name="iser"][value="0"]').hide();
                }else{
                    $('button[name="iser"][value="2"]').show();
                    $('button[name="iser"][value="1"]').show();
                    $('button[name="iser"][value="0"]').show();
                }
                
                if($('.slk')[0].value == 1){
                    $('.slk').eq(1).removeAttr('required');
                    $('.slk').eq(2).removeAttr('required');
                    $('.slk').eq(3).removeAttr('required');
                }else if($('.slk')[1].value == 1){
                    $('.slk').eq(2).removeAttr('required');
                    $('.slk').eq(3).removeAttr('required');
                }else if($('.slk')[2].value == 1){
                    $('.slk').eq(3).removeAttr('required');
                }else{
                    $('.slk').eq(1).prop('required',true);
                    $('.slk').eq(2).prop('required',true);
                    $('.slk').eq(3).prop('required',true);
                }
            });
    
            $(`[name="iser"]`).on('click', function(e){
                $('#iser_r').val($(this).val());
            });
    
            $('#evcannas-form').on('submit', function(ev){
                ev.preventDefault();
                let data = $(this).serialize(), url = "/ajax/post/apuppt/evaluasi_calon_nasabah/create";
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
<?php } ?>