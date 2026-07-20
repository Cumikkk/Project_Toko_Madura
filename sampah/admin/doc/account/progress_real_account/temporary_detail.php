<?php
    
    use App\Models\Account;
    use App\Models\Helper;
    use App\Models\FileUpload;
    use App\Models\Regol;
    $data = Helper::getSafeInput($_GET);

    $COMPANY = App\Models\CompanyProfile::$name;
    $page_title = 'Progress Real Account';
    $web_name_full = $COMPANY;
    $progressAccount = Account::realAccountDetail($data["d"]);
    $id_acc = $data["d"];
    $userBanks = (!empty($progressAccount["MBR_BKJSN"])) ? json_decode($progressAccount["MBR_BKJSN"], true) : [];
?>
<div class="page-header">
	<div>
		<h2 class="main-content-title tx-24 mg-b-5">Waiting Deposit</h2>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
			<li class="breadcrumb-item"><a href="javascript:void(0);">Account</a></li>
			<li class="breadcrumb-item"><a href="<?= pathbreadcrumb(2) ?>/view"><?php echo $page_title; ?></a></li>
			<li class="breadcrumb-item active">Waiting Deposit</li>
		</ol>
	</div>
</div>
<div class="row">
    <div class="col-md-12 mb-3">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title">Agreement</h5>
                    <a href="/export/all?acc=<?php echo $id_acc; ?>" class="btn btn-primary"><i class="fa fa-eye"></i> All Documents</a>
                </div>
            </div>
            <div class="card-body">
                <?php if($progressAccount['ACC_CDD'] == Regol::$cddTypeStandard) : ?>
                    <?php require_once __DIR__ . "/agreement/cdd.php" ?>
                <?php elseif($progressAccount['ACC_CDD'] == Regol::$cddTypeSederhana) : ?>
                    <?php require_once __DIR__ . "/agreement/cdds.php" ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-12 mb-3">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between flex-wrap align-items-center mb-3">
                    <h5 class="card-title">Summary</h5>
                    <!-- <div class="d-flex flex-start flex-wrap gap-2">
                        <a target="_blank" href="/export/all-new?acc=<?php echo $id_acc; ?>" class="btn btn-primary"><i class="fa fa-eye"></i> All Documents</a>
                    </div> -->
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <?php require_once __DIR__ . "/summary.php" ?>
                    </div>
                    <div class="col-md-4">
                        <?php require_once __DIR__ . "/summary_photo.php" ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>