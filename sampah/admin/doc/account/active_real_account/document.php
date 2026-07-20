<?php
    
    use App\Models\Account;
    use App\Models\Dpwd;
    use App\Models\Helper;
    use App\Models\FileUpload;
    use App\Models\Regol;
    $data = Helper::getSafeInput($_GET);

    $COMPANY         = App\Models\CompanyProfile::$name;
    $page_title      = 'Active Real Account';
    $web_name_full   = $COMPANY;
    $progressAccount = Account::realAccountDetail($data["d"]);
    $progressAccount = array_merge((Account::accoundCondition($progressAccount["ID_ACC"]) ?? []), $progressAccount);
    $depositData     = Dpwd::findByRaccId($progressAccount["ID_ACC"]);
    $id_acc          = $data["d"];
    $userBanks       = (!empty($progressAccount["MBR_BKJSN"])) ? json_decode($progressAccount["MBR_BKJSN"], true) : [];
    $MULTIPN         = ["multi", "multilateral"];
    $SPANAME         = ["spa"];
?>
<div class="page-header">
	<div>
		<h2 class="main-content-title tx-24 mg-b-5">Dokumen</h2>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
			<li class="breadcrumb-item">Account</li>
			<li class="breadcrumb-item"><a href="<?= pathbreadcrumb(2) ?>/view"><?php echo $page_title; ?></a></li>
			<li class="breadcrumb-item active">Dokumen</li>
		</ol>
	</div>
</div>

<div class="row mt-4">
    <div class="col-md-12 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex w-100 justify-content-center">
                    <a target="_blank" data-tst="test" href="/export/account-condition?acc=<?php echo $id_acc; ?>" class="btn btn-primary mx-2">Account Condition</a>
                    <a target="_blank" data-tst="test" href="/export/bukti-konfirmasi-penerimaan-nasabah?acc=<?php echo $id_acc; ?>" class="btn btn-primary mx-2">WP Confirm</a>
                    <a target="_blank" data-tst="test" href="/export/pernyataan-pengungkapan?acc=<?php echo $id_acc; ?>" class="btn btn-primary mx-2">Diclosure Statemen</a>
                    <a target="_blank" href="/export/all?acc=<?php echo $id_acc; ?>" class="btn btn-primary mx-2">All Document</a>
                    <!-- <a id="all" href="javascript:void(0);" class="btn btn-primary mx-2">All Document</a> -->
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Summary</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <?php require_once __DIR__ . "/../progress_real_account/summary.php" ?>
                    </div>

                    <div class="col-md-4">
                        <?php require_once __DIR__ . "/../progress_real_account/summary_photo.php" ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12 mb-3">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title">Agreement</h5>
                </div>
            </div>
            <div class="card-body">
                <?php if($progressAccount['ACC_CDD'] == Regol::$cddTypeStandard) : ?>
                    <?php require_once __DIR__ . "/../progress_real_account/agreement/cdd.php" ?>
                <?php elseif($progressAccount['ACC_CDD'] == Regol::$cddTypeSederhana) : ?>
                    <?php require_once __DIR__ . "/../progress_real_account/agreement/cdds.php" ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script>
    $('#all').on('click', function(){
        $('[data-tst]').click();
    });
</script>