<?php if(count(App\Models\Account::myAccount($user['MBR_ID'])) <= 0) die("<script>location.href = '/deposit'; </script>"); ?>

<?php 
$selectedMethod = App\Models\Helper::form_input($_GET['method'] ?? "");
?>
<div class="dashboard-breadcrumb mb-25">
    <h2>Form Deposit</h2>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <div class="panel">
            <div class="panel-body">
                <p>Metode Deposit: </p>
                <div class="btn-box d-flex flex-column gap-2" id="nav-tab" role="tablist">
                    <?php foreach(App\Models\PaymentSystem::activeDeposit() as $key => $payment) : ?>
                        <?php $selectedMethod = (empty($selectedMethod) && $key == 0)? $payment['DPWDMTH_CODE'] : ""; ?>
                        <a class="py-2 btn btn-sm btn-outline-primary <?= ($selectedMethod == $payment['DPWDMTH_CODE'])? "active" : ""; ?>"><?= $payment['DPWDMTH_NAME'] ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8 mb-3">
        <div class="panel">
            <div class="panel-body">
                <?php if(file_exists(__DIR__ . "/method/{$selectedMethod}.php")) : ?>
                    <?php require_once __DIR__ . "/method/{$selectedMethod}.php"; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>