<?php
    
    use App\Models\Account;
    use App\Models\Dpwd;
    use App\Models\Helper;
    use App\Models\FileUpload;
    $data = Helper::getSafeInput($_GET);

    $COMPANY         = App\Models\CompanyProfile::$name;
    $progressAccount = Account::realAccountDetail($data["d"]);
    $depositData     = Dpwd::findByRaccId($progressAccount["ID_ACC"]);
    $page_title      = 'Progress Real Account';
    $web_name_full   = $COMPANY;
    $id_acc          = $data["d"];
    $userBanks       = (!empty($progressAccount["MBR_BKJSN"])) ? json_decode($progressAccount["MBR_BKJSN"], true) : [];
    $MULTIPN         = ["multi", "multilateral"];
    $SPANAME         = ["spa"];

    if((!$depositData) || (!$progressAccount)){
        die("<script>alert('Invalid Account');location.href = '/account/progress_real_account/view'</script>");
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
		<h2 class="main-content-title tx-24 mg-b-5">Account Condition</h2>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="javascript:void(0);">Account</a></li>
			<li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $page_title; ?></a></li>
			<li class="breadcrumb-item active">Account Condition</li>
		</ol>
	</div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="card">
            <form method="post" id="wp-check-form">
                <div class="card-header pb-2">
                    <h5 class="card-title mb-0">ACCOUNT CONDITION - <?= $progressAccount['RTYPE_TYPE'] ?></h5>
                    <div><span><i class="small text-muted">Comisson Charge</i></span></div>
                </div>
                <div class="card-body">
                    <div class="row row-xs align-items-center mg-b-20">
                        <div class="col-md-3"><label class="mg-b-0">Nama</label></div>
                        <div class="col-md-9"><input type="text" class="form-control" readonly value="<?php echo $progressAccount['ACC_FULLNAME']; ?>" name="nama" id="nama"></div>
                    </div>
                    <div class="row row-xs align-items-center mg-b-20">
                        <div class="col-md-3"><label class="mg-b-0">Email</label></div>
                        <div class="col-md-9"><input type="text" class="form-control" readonly value="<?php echo $progressAccount['MBR_EMAIL']; ?>" name="email" id="email"></div>
                    </div>
                    <div class="row row-xs align-items-center mg-b-20">
                        <div class="col-md-3"><label class="mg-b-0">No. Telp</label></div>
                        <div class="col-md-9"><input type="text" class="form-control" readonly value="<?php echo $progressAccount['MBR_PHONE']; ?>" name="phone" id="phone"></div>
                    </div>
                    <hr>
    
                    <div class="row">
                        <?php foreach($userBanks as $key => $bank) : ?>
                            <div class="col-md-6">
                                
                                <div class="row row-xs align-items-center mg-b-20">
                                    <div class="col-md-3"><label for="bank_<?= $key + 1 ?>_name" class="mg-b-0">Bank <?= $key + 1 ?> Name</label></div>
                                    <div class="col-md-9"><input type="text" class="form-control" readonly value="<?php echo $bank['MBANK_NAME']; ?>"></div>
                                </div>
                                <div class="row row-xs align-items-center mg-b-20">
                                    <div class="col-md-3"><label for="bank_<?= $key + 1 ?>_account" class="mg-b-0">Bank <?= $key + 1 ?> Account</label></div>
                                    <div class="col-md-9"><input type="text" class="form-control" readonly value="<?php echo $bank['MBANK_ACCOUNT']; ?>"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <hr>
    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="row">
                                <!-- <div class="col-md-3" id="forexdiv1">Forex</div> -->
                                <div class="col-md-3" id="forexdiv1">Komisi</div>
                                <div class="col-md-9" id="forexinpt1">
                                    <input type="text" class="form-control text-center" name="forex" value="<?php echo number_format($progressAccount['RTYPE_KOMISI'], 0) ?>" readonly required>
                                    <!-- <select class="form-control text-center" name="forex" required>
                                        <option value="" selected disabled>Pilih Nilai</option>
                                        <?php foreach(Account::accountCommission() as $comm) : ?>
                                            <option value="<?= $comm ?>" <?= ($comm == $progressAccount['RTYPE_KOMISI'])? "selected" : ""; ?>><?= $comm ?></option>
                                        <?php endforeach; ?>
                                    </select> -->
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <div class="row mt-3">
                        <div class="col-md-6 mb-3">
                            <div class="row">
                                <div class="col-md-3">Nilai Margin</div>
                                <div class="col-md-9">
                                    <div class="input-group">
                                        <span class="input-group-text"><?php echo $progressAccount['RTYPE_CURR'] ?></span>
                                        <input type="text" class="form-control text-center" name="margin" value="<?php echo number_format((($progressAccount["RTYPE_ISFLOATING"]) ? $amountUSD : $amountIDR), 2) ?>" readonly required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="input-group">
                                <span class="input-group-text">Fixed Rate</span>
                                <input 
                                    type="text" 
                                    class="form-control text-center" 
                                    name="margin" 
                                    value="
                                        <?php
                                            if($progressAccount['RTYPE_ISFLOATING'] == 1){
                                                echo 'Floating';
                                            }else{ echo number_format($progressAccount['RTYPE_RATE'], 0); }
                                        ?>
                                    " 
                                    readonly 
                                    required
                                >

                                <!-- <select name="fix_rate" id="" class="form-control text-center" required>
                                    <option value="10.000" <?php echo ($progressAccount['RTYPE_RATE'] == '10000') ? 'selected' : ""; ?>>10.000</option>
                                    <option value="14.000" <?php echo ($progressAccount['RTYPE_RATE'] == '14000') ? 'selected' : ""; ?>>14.000</option>
                                    <option value="0" <?php echo ($progressAccount['RTYPE_RATE'] == '0') ? 'selected' : ""; ?>>Floating</option>
                                </select> -->
                            </div>
                        </div>
                    </div>
    
                    <div class="row mt-3">
                        <div class="col-md-12 mb-3">
                            <div class="row">
                                <div class="col-md-3" style="margin-block: auto;">Note:</div>
                                <div class="col-md-9 text-left" style="margin-block: auto;">
                                    <input type="text" class="form-control text-center" name="sbmt_note" value=" " required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <input type="hidden" name="sbmt_act" id="fld-act">
                    <input type="hidden" name="sbmt_id" id="sbmt_id" value="<?= $data["d"] ?>">
                    <button type="submit" id="sbmtacc" style="display: none;"></button>
                    <button type="button" name="reject" value="reject" class="btn act-btna btn-danger">Reject</button>
                    <button type="button" name="accept" value="accept" class="btn act-btna btn-success">Accept</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $(document).ready(() => {
        $('.act-btna').on('click', function(e){
            $('#fld-act').val($(this).val());
            $('#sbmtacc').click();
        });

        $('#wp-check-form').on('submit', function(e){
            e.preventDefault();
            let data = Object.fromEntries(new FormData(this));
            Swal.fire({
                text: "Mohon Tunggu...",
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            })

            $.post("/ajax/post/account/wp_check_action", data, function(resp) {
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