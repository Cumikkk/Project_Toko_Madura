<?php
    
    use App\Factory\FileUploadFactory;
    use App\Models\Account;
    use App\Models\Dpwd;
    use App\Models\Helper;
    use App\Models\FileUpload;
    $data = Helper::getSafeInput($_GET);
    $id_acc = $data["d"];

    $COMPANY = App\Models\CompanyProfile::$name;
    $progressAccount = Account::realAccountDetail($id_acc);
    $progressAccount = array_merge((Account::accoundCondition($progressAccount["ID_ACC"]) ?? []), $progressAccount);
    $depositData = Dpwd::findByRaccId($progressAccount["ID_ACC"]);
    $page_title = 'Progress Real Account';
    $web_name_full = $COMPANY;
    $userBanks = (!empty($progressAccount["MBR_BKJSN"])) ? json_decode($progressAccount["MBR_BKJSN"], true) : [];
    $accountCondition = Account::accoundCondition($progressAccount['ID_ACC']);

    if((!$depositData) || (!$progressAccount)){
        die("<script>alert('Invalid Account');location.href = '/account/progress_real_account/view'</script>");
    }
    
    if(!$accountCondition){
        die("<script>alert('Invalid Account Condition');location.href = '/account/progress_real_account/view'</script>");
    }

    /** explode bank admin */
    $bankAdmin = explode("/", $depositData['DPWD_BANK']);

    /** explode bank nasabah */
    $userBank = explode("/", $depositData['DPWD_BANKSRC']);

    
    $amountIDR = ($depositData["DPWD_CURR_FROM"] == "IDR") ? $depositData['DPWD_AMOUNT_SOURCE'] : $depositData['DPWD_AMOUNT'];
    $amountUSD = ($depositData["DPWD_CURR_FROM"] == "USD") ? $depositData['DPWD_AMOUNT_SOURCE'] : $depositData['DPWD_AMOUNT'];
?>
<div class="page-header">
	<div>
		<h2 class="main-content-title tx-24 mg-b-5">Accounting</h2>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="javascript:void(0);">Account</a></li>
			<li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $page_title; ?></a></li>
			<li class="breadcrumb-item active">Accounting</li>
		</ol>
	</div>
</div>

<div class="row row-sm">
    <div class="col-lg-12 col-md-12">
        <div class="card">
            <div class="card-body">
                <div>
                    <h6 class="main-content-label mb-1">Margin Receipt & Data Deposit New Account <?php echo ($depositData["DPWD_AMOUNT"]); ?></h6>
                    <p class="text-muted card-sub-title">A/N <?php echo $progressAccount["ACC_FULLNAME"].' ('.$progressAccount["MBR_EMAIL"].')' ?></p>
                </div>
                <div class="d-lg-flex">
                    <!-- <h2 class="main-content-label mb-1">#<?php echo $depositData["DPWD_VOUCHER"] ?></h2> -->
                    <div class="ms-auto">
                        <p class="mb-1"><span class="font-weight-bold">Tanggal Upload Bukti Deposit</span> <?= empty($depositData['DPWD_DATETIME'])? "-" : date("d-m-Y H:i:s", strtotime($depositData["DPWD_DATETIME"])); ?></p>
                        <p class="mb-0"><span class="font-weight-bold">Tanggal Penetapan Ticket &nbsp;</span> <?= empty($depositData['DPWD_DATETIME'])? "-" : date("d-m-Y H:i:s", strtotime($depositData["DPWD_DATETIME"])) ?></p>
                    </div>
                </div>
                <hr class="mg-b-40">
                <div class="table-responsive mg-t-40">
                    <table class="table table-invoice table-bordered">
                        <thead>
                            <tr>
                                <th class="wd-20p text-center" colspan="2">Bukti Deposit</th>
                                <th class="wd-50p text-center" colspan="4">Margin Receipt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="2" rowspan="4">
                                    <div class="invoice-notes">
                                        <a href="<?php echo (!empty($depositData["DPWD_PIC"])) ? FileUploadFactory::aws()->awsFile($depositData["DPWD_PIC"]) : NULL ?>" target="_blank">
                                            <img alt="" <?php echo (!empty($depositData["DPWD_PIC"])) ? 'src="'.FileUploadFactory::aws()->awsFile($depositData["DPWD_PIC"]).'"' : NULL ?>>
                                        </a>
                                    </div><!-- invoice-notes -->
                                </td>
                                <td class="text-center">A/C. No. :</td>
                                <td colspan="3"><input class="form-control text-center" placeholder="" value="<?php echo $progressAccount["ACCCND_LOGIN"] ?>" readonly type="text"></td>
                            </tr>
                            <tr>
                                <td class="text-center">Client's Name :</td>
                                <td colspan="3"><input class="form-control text-center" placeholder="" value="<?php echo $progressAccount["ACC_FULLNAME"] ?>" readonly type="text"></td>
                            </tr>

                            <?php if($depositData['DPWD_CURR_FROM'] == "IDR") : ?>
                                <tr>
                                    <td class="text-center">The sum of rupiah :</td>
                                    <td colspan="2"><input class="form-control text-center marquee" placeholder="" value="<?php echo Helper::penyebut(round($depositData["DPWD_AMOUNT_SOURCE"], 0)).' rupiah'; ?>" readonly type="text"></td>
                                    <td><input class="form-control text-center" placeholder="" value="<?= $depositData['DPWD_CURR_FROM'] . " " . Helper::formatCurrency($depositData["DPWD_AMOUNT_SOURCE"], 0) ?>" readonly type="text"></td>
                                </tr>
                                <tr>
                                    <td class="text-center">
                                        <div class="form-group">
                                            <label>Amount in USD</label>
                                            <input class="form-control text-center" placeholder="" value="<?= Helper::formatCurrency($amountUSD) ?>" readonly type="text">
                                        </div>
                                    </td>
                                    <td class="text-center" colspan="2">
                                        <div class="form-group">
                                            <label>Rate</label>
                                            <input class="form-control text-center" placeholder="" value="<?= (($progressAccount["RTYPE_ISFLOATING"]) ? 'Floating' : Helper::formatCurrency($progressAccount['RTYPE_RATE'])) ?>" readonly type="text">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-group">
                                            <label>Amount in IDR</label>
                                            <input class="form-control text-center" placeholder="" value="<?= Helper::formatCurrency($amountIDR) ?>" readonly type="text">
                                        </div>
                                    </td>
                                </tr>

                            <?php elseif($depositData['DPWD_CURR_FROM'] == "USD") : ?>
                                <tr>
                                    <td class="text-center">
                                        <div class="form-group">
                                            <label>Amount in USD</label>
                                            <input class="form-control text-center" placeholder="" value="<?= Helper::formatCurrency($amountUSD) ?>" readonly type="text">
                                        </div>
                                    </td>
                                    <td class="text-center" colspan="2">
                                        <div class="form-group">
                                            <label>Rate</label>
                                            <input class="form-control text-center" placeholder="" value="<?= (($progressAccount["RTYPE_ISFLOATING"]) ? 'Floating' : Helper::formatCurrency($depositData['DPWD_RATE'])) ?>" readonly type="text">
                                        </div>
                                    </td>
                                </tr>

                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-center">
                <button type="button" class="btn btn-danger ripple act-btna" value="reject" data-bs-target="#modal-datepicker" data-bs-toggle="modal">Reject</button>
                <button type="button" class="btn btn-success ripple act-btna" value="accept" data-bs-target="#modal-datepicker" data-bs-toggle="modal">Accept</button>
                <a class="btn btn-primary ripple" target="_blank" href="/export/trans-deposit-detail?acc=<?php echo $id_acc; ?>">Print</a>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-datepicker">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-content-demo">
            <form method="post" enctype="multipart/form-data" id="accntng-form">
                <div class="modal-header">
                    <h6 class="modal-title">Form <span id="headTitle2"></span></h6>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group" id="uloadMutasi">
                        <label>Upload Bukti Mutasi</label>
                        <input type="file" name="mutasi" class="dropify dropify1" id="fileMutasi" onchange="readURL(this);" data-height="200" required>
                    </div>
                    <div class="form-group">
                        <label>Masukan catatan untuk <span id="labelTitle1"></span></label>
                        <input name="sbmt_note" class="form-control text-center" placeholder="Masukan Catatan" type="text" required>
                    </div>
                </div>
                <div class="modal-footer text-center">
                    <input type="hidden" name="sbmt_id" value="<?= $id_acc ?>">
                    <input type="hidden" name="sbmt_act" id="acc-act">
                    <button type="submit" class="btn btn-primary ripple btn-block text-white" type="button" id="sendButton2">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $(document).ready(() => {
        $('.act-btna').on('click', function(e){
            $('#acc-act').val($(this).val());
            $('#uloadMutasi').css('display', ($(this).val() == 'reject' ? 'none' : ''));
            $('#fileMutasi').prop('required', ($(this).val() == 'reject' ? false : true));
        });

        $('#accntng-form').on('submit', function(ev){
            ev.preventDefault();
            let data = new FormData(this);
            let button = $(this).find('button[type="submit"]');

            button.addClass('loading');
            $.ajax({
                url         : '/ajax/post/account/accounting_action',
                type        : 'POST',
                dataType    : 'JSON',
                enctype     : 'multipart/form-data',
                data        : data,
                contentType : false,
                chache      : false,
                processData : false
            }).done((resp) => {
                $('#modal-datepicker').modal('hide');
                button.removeClass('loading');
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