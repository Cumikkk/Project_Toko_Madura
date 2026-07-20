<?php
    
    use App\Factory\FileUploadFactory;
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
?>
<div class="page-header">
	<div>
		<h2 class="main-content-title tx-24 mg-b-5">Deposit New Account</h2>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="javascript:void(0);">Account</a></li>
			<li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $page_title; ?></a></li>
			<li class="breadcrumb-item active">Deposit New Account</li>
		</ol>
	</div>
</div>
<div class="row row-sm">
    <div class="col-md-8 mb-3">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h6 class="main-content-label mb-1">Data Deposit New Account</h6>
                        <p class="text-muted card-sub-title">A/N <?php echo $progressAccount["ACC_FULLNAME"].' ('.$progressAccount["MBR_EMAIL"].')' ?></p>
                    </div>

                    <?php if($progressAccount['ACC_WPCHECK'] == 2) : ?>
                        <div>
                            <button type="button" class="btn btn-sm btn-danger ripple act btnAct" data-act="reject">Reject</button>
                            <button type="button" class="btn btn-sm btn-success ripple act btnAct" data-act="accept">Accept</button>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered tableSmall mb-0">
                        <tbody>
                            <tr>
                                <td colspan="2" class="fw-bold row-separator bg-primary">Bank Nasabah</td>
                            </tr>
                            <tr>
                                <td width="30%" class="fw-bold">Nama Bank</td>
                                <td><?= $userBank[0] ?? "-" ?></td>
                            </tr>
                            <tr>
                                <td width="30%" class="fw-bold">No. Rekening</td>
                                <td><?= $userBank[1] ?? "-" ?></td>
                            </tr>
                            <tr>
                                <td width="30%" class="fw-bold">Pemilik Rekening</td>
                                <td><?= $userBank[2] ?? "-" ?></td>
                            </tr>
                        </tbody>
                        <tbody>
                            <tr>
                                <td colspan="2" class="fw-bold row-separator bg-primary">Bank Penerima</td>
                            </tr>
                            <tr>
                                <td width="30%" class="fw-bold">Nama Bank</td>
                                <td><?= $bankAdmin[0] ?? "-" ?></td>
                            </tr>
                            <tr>
                                <td width="30%" class="fw-bold">No. Rekening</td>
                                <td><?= $bankAdmin[1] ?? "-" ?></td>
                            </tr>
                            <tr>
                                <td width="30%" class="fw-bold">Pemilik Rekening</td>
                                <td><?= $bankAdmin[2] ?? "-" ?></td>
                            </tr>
                        </tbody>
                        <tbody>
                            <tr>
                                <td colspan="2" class="fw-bold row-separator bg-primary">Jumlah</td>
                            </tr>

                            <?php if($progressAccount['RTYPE_ISFLOATING'] == 1) : ?>
                                <tr>
                                    <td width="30%" class="fw-bold">Nilai USD</td>
                                    <td>USD <?= Helper::formatCurrency($depositData['DPWD_AMOUNT'] / $depositData['DPWD_RATE']) ?></td>
                                </tr>
                            <?php else : ?>
                                <tr>
                                    <td width="30%" class="fw-bold">Nilai IDR</td>
                                    <td>IDR <?= Helper::formatCurrency($depositData['DPWD_AMOUNT_SOURCE']) ?></td>
                                </tr>
                                <tr>
                                    <td width="30%" class="fw-bold">Nilai USD</td>
                                    <td>USD <?= Helper::formatCurrency($depositData['DPWD_AMOUNT']) ?></td>
                                </tr>
                                <tr>
                                    <td width="30%" class="fw-bold">Rate</td>
                                    <td><?= Helper::formatCurrency($depositData['DPWD_RATE']) ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tbody>
                            <tr>
                                <td colspan="2" class="fw-bold row-separator bg-primary">Detail Akun</td>
                            </tr>
                            <!-- <tr>
                                <td width="30%" class="fw-bold">Login</td>
                                <td><?= $progressAccount['ACC_LOGIN'] ?></td>
                            </tr> -->
                            <tr>
                                <td width="30%" class="fw-bold">Group</td>
                                <td><?= $progressAccount['RTYPE_GROUP'] ?></td>
                            </tr>
                            <tr>
                                <td width="30%" class="fw-bold">Type</td>
                                <td><?= $progressAccount['RTYPE_TYPE'] ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title text-primary">Bukti Transfer</h5>
                <input type="file" class="dropify" data-default-file="<?= FileUploadFactory::aws()->awsFile(($depositData['DPWD_PIC'] ?? '-')) ?>">
            </div>
        </div>
    </div>
</div>
<script>
    $('.btnAct').on('click', function() {
        let ARCBTN = {
            title: `${$(this).data('act').toUpperCase()} DATA`,
            text: `Berikan catatan sebelum ${$(this).data('act').toUpperCase()}`,
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
            if(result.isConfirmed) {
                let id  = '<?= $data["d"] ?>';
                let act = $(this).data('act');
                $.post("/ajax/post/account/client_deposit_acion", {sbmt_id: id, sbmt_act: act, sbmt_note: result.value}, function(resp) {
                    Swal.fire(resp.alert).then(() => {
                        if(resp.success) {
                            if(resp?.data?.reloc?.length){
                                location.href = resp?.data?.reloc;
                            }else{ location.reload(); }
                        }
                    })
                }, 'json');
            }
        });
    });
</script>