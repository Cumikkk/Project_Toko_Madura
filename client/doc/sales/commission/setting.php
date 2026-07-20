<?php
use App\Library\Sales\SalesMain;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\Dpwd;
use App\Models\Helper;
use App\Models\User;

die("<script>location.href = '/404'; </script>");
if(!isset($salesData)) {
    $salesData = SalesMain::getUserType($user['MBR_TYPE']);
}

if(!$salesData->isHeadOfStructure()) {
    die("<script>alert('Invalid Permission'); location.href = '/dashboard'; </script>");
}

/** Get Available Product */
$availableProduct = Account::getAvailableProduct_list($userid);

/** default suffix */
$defaultSuffix = 0;
if(!empty($_GET["type"])) {
    $defaultSuffix = Helper::form_input($_GET["type"] ?? 0);
    $isSupportProduct = array_search($defaultSuffix, array_column($availableProduct, "RTYPE_SUFFIX"));
    if($isSupportProduct === false) {
        die("<script>alert('Invalid Product'); location.href = '/sales/commission/setting'; </script>");
    }
}


?>
<p class="mb-0">Wallet Kamu: <?= User::wallet($user['MBR_ID']); ?></p>
<p>Wallet NMI: <?= User::wallet($user['MBR_ID'], [Dpwd::$typeNmiCommission]); ?></p>

<div class="row">
    <div class="col-md-4 mb-3">
        <div class="panel">
            <div class="panel-body">
                <p>Group Type: </p>
                <div class="btn-box d-flex flex-column gap-2" id="nav-tab" role="tablist">
                    <?php foreach($availableProduct as $key => $product) : ?>
                        <?php if($key === 0 && $defaultSuffix === 0) $defaultSuffix = $product['RTYPE_SUFFIX']; ?>
                        <a href="/sales/commission/setting?type=<?= $product['RTYPE_SUFFIX'] ?>" class="py-2 btn btn-sm btn-outline-primary text-start <?= $defaultSuffix == $product['RTYPE_SUFFIX']? "active" : ""; ?>">
                            <?= implode("/", [$product['RTYPE_TYPE'], $product['RTYPE_KOMISI'], ($product['RTYPE_ISFLOATING']? "Floating" : $product['RTYPE_RATE'])]); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8 mb-3">
        <div class="panel">
            <div class="panel-header">
                <h5 class="panel-title">Commission Setting</h5>
            </div>
            <div class="panel-body">
                <?php $productDetail = AccountType::findBySuffix($defaultSuffix); ?>
                <?php if($productDetail) : ?>
                    <?php require_once __DIR__ . "/setting_detail.php"; ?>
                <?php else : ?>
                    <p>404</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>