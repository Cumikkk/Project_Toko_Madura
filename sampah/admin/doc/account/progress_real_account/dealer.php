<?php
use App\Factory\MetatraderFactory;
use App\Models\Account;
use App\Models\Dpwd;
use App\Models\Helper;
use App\Models\FileUpload;
use App\Models\ProfilePerusahaan;
use App\Models\Regol;

$data = Helper::getSafeInput($_GET);
$id_acc = $data["d"];
$COMPANY = App\Models\CompanyProfile::$name;
$progressAccount = Account::realAccountDetail($data["d"]);
$progressAccount = array_merge((Account::accoundCondition($progressAccount["ID_ACC"]) ?? []), $progressAccount);
$page_title = 'Progress Real Account';
$web_name_full = $COMPANY;
$userBanks = (!empty($progressAccount["MBR_BKJSN"])) ? json_decode($progressAccount["MBR_BKJSN"], true) : [];
$date_month = Helper::bulan(date("m"));
$accountCondition = Account::accoundCondition($progressAccount['ID_ACC']);
$lastNote = Regol::getAccountHistoryLastNote($progressAccount['ID_ACC']);

if(!$progressAccount){
    die("<script>alert('Invalid Account');location.href = '/account/progress_real_account/view'</script>");
}

if(!$accountCondition) {
    die("<script>alert('Invalid Account Condition');location.href = '/account/progress_real_account/view'</script>");
}

if($progressAccount['ACC_STS'] != 1 || $progressAccount['ACC_WPCHECK'] != Regol::$statusWPCheckGoodFund) {
    die("<script>alert('Account has been processed');location.href = '/account/progress_real_account/view'</script>");
}

/** Find Sugesstion login */
$suggestionLogin = 0;
if($progressAccount['ACC_CDD'] == Regol::$cddTypeStandard && $progressAccount['ACC_NEEDS_UPGRADE']) {
    $suggestionLogin = $progressAccount['ACC_LOGIN'];
}

if(empty($suggestionLogin)) {
    $suggestionLogin = MetatraderFactory::createLoginByPrefix($progressAccount['RTYPE_PREFIX']);
}

$listWpb = ProfilePerusahaan::list_wpb(2)[0] ?? []; // 2 = Verifikator
?>
<div class="page-header">
	<div>
		<h2 class="main-content-title tx-24 mg-b-5">Dealer</h2>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
			<li class="breadcrumb-item">Account</li>
			<li class="breadcrumb-item"><a href="<?= pathbreadcrumb(2) ?>/view"><?php echo $page_title; ?></a></li>
			<li class="breadcrumb-item active">Dealer</li>
		</ol>
	</div>
</div>

<?php if($progressAccount['ACC_CDD'] == Regol::$cddTypeStandard && $progressAccount['ACC_NEEDS_UPGRADE']) : ?>
    <div class="alert alert-info">
        <div class="d-flex align-items-center gap-2">
            <i class="fas fa-info-circle"></i>
            <span>Akun ini merupakan akun <strong>CDDS</strong> yang saat ini sedang dalam proses upgrade ke <strong>CDD Standar</strong>.</span>
        </div>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-3">
            <div class="card-header  pb-2">
                <h5 class="card-title mb-0">
                    ACCOUNT CONDITION - <?= $progressAccount['RTYPE_TYPE'] ?>
                </h5>
                <div><span><i class="small text-muted">Commisson Charge</i></span></div>
            </div>

            <form method="post" id="dealer-form">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2 mt-auto">Kondisi ini efektif bulan</div>
                        <div class="col-md-9 mb-2"><input type="text" class="form-control" readonly value="<?php echo date('m');?> (<?php echo $date_month ;?>)" required></div>
                    
                        <div class="col-md-3 mb-2 mt-auto"><label for="" class="required">No. Account</label></div>
                        <div class="col-md-9 mb-2"><input type="number" class="form-control" name="login"  value="<?= $suggestionLogin ?>" required></div>
                         
                        <div class="col-md-3 mb-2 mt-auto"><label for="" class="required">Password Master</label></div>
                        <div class="col-md-9 mb-2"><input type="text" class="form-control" name="password" value="<?= $progressAccount['ACC_PASS'] ?? Account::generatePassword(8); ?>" required></div>
    
                        <div class="col-md-3 mb-2 mt-auto"><label for="" class="required">Password Investor</label></div>
                        <div class="col-md-9 mb-2"><input type="text" class="form-control" name="investor" value="<?= $progressAccount['ACC_INVESTOR'] ?? Account::generatePassword(8); ?>" required></div>

                        <div class="col-md-3 mb-2 mt-auto">Nama Investor</div>
                        <div class="col-md-9 mb-2"><input type="text" class="form-control" value="<?php echo $progressAccount['ACC_FULLNAME'] ?>" readonly></div>
    
                        <div class="col-md-3 mb-2 mt-auto">E-Mail Investor</div>
                        <div class="col-md-9 mb-2"><input type="text" class="form-control" value="<?php echo $progressAccount['MBR_EMAIL'] ?>" readonly></div>
    
                        <div class="col-md-3 mb-2 mt-auto">No.Telp</div>
                        <div class="col-md-9 mb-2"><input type="text" class="form-control" value="<?php echo $progressAccount['ACC_F_APP_PRIBADI_HP'] ?>" readonly></div>
                    </div>
    
                    <div class="row">
                        <div class="col-md-3 mb-2 mt-auto">Commission Charge</div>
                        <div class="col-md-4 mb-2">
                            <input type="text" class="form-control" value="<?php echo $progressAccount['RTYPE_KOMISI']?>" readonly required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-2 mt-auto">Rate</div>
                        <div class="col-md-4 mb-2">
                            <input type="text" class="form-control" value="<?= $progressAccount['RTYPE_ISFLOATING']? "Floating" : Helper::formatCurrency($progressAccount['RTYPE_RATE']) ?>" readonly required>
                        </div>
                    </div>
    
                    <div class="row">
                        <div class="col-md-3 mb-2 mt-auto">Note</div>
                        <div class="col-md-9 mb-2">
                            <input type="text" class="form-control" value="<?php echo $lastNote['NOTE_NOTE'] ?? "-" ?>" readonly required>
                        </div>
                    </div>

                    <div class="row mt-2 align-items-center">
                        <div class="col-md-3 mb-2"><strong>Wakil Pialang Berjangka</strong> <span class="text-danger">*</span></div>
                        <div class="col-md-9 mb-2">
                            <select name="update_wpb" id="update_wpb" class="form-control form-select">
                                <option value="">Pilih</option>
                                <?php foreach($listWpb as $wpb) : ?>
                                    <option value="<?= $wpb['WPB_NAMA'] ?>" <?= (strtolower($wpb['WPB_NAMA'] ?? "") == strtolower($progressAccount['ACC_F_PERJ_WPB'] ?? ""))? "selected" : ""; ?>><?= $wpb['WPB_NAMA'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="text-small text-danger"><i>*automatically updates when changing options</i></span>
                        </div>
                    </div>

                    <div class="row mt-2 align-items-center">
                        <div class="col-md-3 mb-2 mt-auto"><strong>Preview Document</strong></div>
                        <div class="col-md-9 mb-2">
                            <div class="d-flex gap-2">
                                <a target="_blank" href="/export/all?acc=<?= $id_acc ?>" class="btn btn-sm btn-primary">Perjanjian Nasabah</a>
                                <a target="_blank" href="/export/bukti-konfirmasi-penerimaan-nasabah?acc=<?= $id_acc ?>" class="btn btn-sm btn-primary">Bukti Konfirmasi Nasabah</a>
                                <a target="_blank" href="<?= Regol::urlTradingRule($progressAccount['RTYPE_FILE']); ?>" class="btn btn-sm btn-primary">Trading Rules</a>
                            </div>
                        </div>
                    </div>
                </div>
    
                <div class="card-footer text-center">
                    <input type="hidden" name="sbmt_act" id="fld-act">
                    <input type="hidden" name="sbmt_id" id="sbmt_id" value="<?= $id_acc ?>">
                    <button type="submit" id="sbmtacc" style="display: none;"></button>
                    <button type="button" value="reject" data-act="reject" class="btn btn-danger act-btna">Reject</button>
                    <button type="button" value="accept" data-act="accept" class="btn btn-success act-btna">Accept</button>
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
        
        $('#dealer-form').on('submit', function(e){
            e.preventDefault();

            let formData = new FormData(this);
            // formData.append('password', $('#mt-pass').val());
            // formData.append('investor', $('#mt-invstr').val());

            let data = Object.fromEntries(formData);
            
            let ARCBTN = {
                title: `${$('#fld-act').val().toUpperCase()} DATA`,
                text: `Berikan catatan sebelum ${$('#fld-act').val().toUpperCase()}`,
                icon: 'warning',
                showCancelButton: true,
                reverseButtons: true,
                input: "text",
                inputLabel: `Masukan catatan`,
                inputAttributes: {
                    required: true,
                }
            };

            Swal.fire(ARCBTN).then((result) => {
                data["sbmt_note"] = result.value;
                if(result.isConfirmed) {
                    Swal.fire({
                        text: "Loading...",
                        allowOutsideClick: false,
                        didOpen: function() {
                            Swal.showLoading();
                        }
                    })
                    
                    $.ajax({
                        url: "/ajax/post/account/dealer_action",
                        type: "POST",
                        data: data,
                        dataType: "json",
                        success: function(resp) {
                            if(resp.data.require_confirmation) {
                                Swal.fire({
                                    title: "Konfirmasi pembuatan akun baru",
                                    text: `Apakah anda yakin ingin memperbarui nomor akun dari ${resp.data.login} ke ${data.login}?`,
                                    icon: "info",
                                    showCancelButton: true,
                                    reverseButtons: true
                                }).then((confirmationResult) => {
                                    if(confirmationResult.isConfirmed) {
                                        data.force_update = 1;
                                        $.ajax({
                                            url: "/ajax/post/account/dealer_action",
                                            type: "POST",
                                            data: data,
                                            dataType: "json",
                                            success: function(confirmResp) {
                                                Swal.fire(confirmResp.alert).then(() => {
                                                    if(confirmResp.success && confirmResp?.data?.reloc) {
                                                        location.href = confirmResp.data.reloc;
                                                    }
                                                })
                                            },
                                            error: function() {
                                                Swal.fire("Error", "Terjadi kesalahan saat memproses data", "error");
                                            }
                                        })
                                    } else {
                                        Swal.fire("Dibatalkan", "Aksi dibatalkan", "info");
                                    }
                                })
                                return;
                            }

                            Swal.fire(resp.alert).then(() => {
                                if(resp.success) {
                                    if(resp?.data?.reloc?.length){
                                        location.href = resp?.data?.reloc;
                                        return;
                                    }
                                    
                                    location.reload();
                                }
                            })
                        },
                        error: function() {
                            Swal.fire("Error", "Terjadi kesalahan saat memproses data", "error");
                        }
                    })
                }
            });

        });

        $('#update_wpb').on('change', function() {
            let selectedWpb = $(this).val();
            if(!selectedWpb) {
                Swal.fire("Error", "Failed to select WPB", "error");
                return;
            }

            Swal.fire({
                text: "Updating WPB...",
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            });

            $.post("/ajax/post/account/update_wpb", {selected: selectedWpb, id: $('input[name="sbmt_id"]').val()}, function(resp) {
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        location.reload();
                    }
                })
            }, 'json')
        });
    });
</script>