<?php

use App\Library\Sales\SalesMain;
use App\Models\Helper;
use App\Models\Product;
use App\Models\Symbols;

/** check symbol */
$symbol = Helper::form_input($_POST['symbol'] ?? "");
$symbolDetail = Symbols::findByNameCategory($symbol);
if(!$symbolDetail) {
    exit("Invalid Symbol Category");
}

/** check product */
$productSuffix = Helper::form_input($_POST['product'] ?? "");
$product = Product::findBySuffix($productSuffix);
if(!$product) {
    exit("Invalid Product");
}

/** Sales Commission */
$salesCommission = SalesMain::salesCommission($user['MBR_ID']);
if(!$salesCommission) {
    exit("Invalid Commission");
}

/** commission setting */
$commissionSetting = $salesCommission->commissionSetting($product['ID_RTYPE'])->get();
$maxCommission = $salesCommission->max($symbolDetail['ID_SYMCAT']);
?>

<p class="mb-0">Jumlah Maksimal: <?= $maxCommission ?></p>
<p>Jumlah Dialokasikan: <span id="allocatedAmount"></span></p>
<input type="hidden" name="product" value="<?= $productSuffix ?>">
<input type="hidden" name="symbol" value="<?= $symbol ?>">
<div class="row">
    <?php foreach($commissionSetting['settings'] as $setting) : ?>
        <?php foreach($setting['amounts'] as $amount) : ?>
            <?php if($amount['category_id'] == $symbolDetail['ID_SYMCAT']) : ?>
                <div class="col-md-6">
                    <label for="<?= $setting['code'] ?>" class="form-label required"><?= $setting['name'] ?></label>
                    <input type="number" class="form-control sales" placeholder="0" name="sales[<?= $setting['code'] ?>]" value="<?= $amount['amount'] ?>" required>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        let inputs = $('input.sales');
        
        function refreshAmount() {
            let allocatedAmount = 0;
            $.each(inputs, (i, el) => {
                if(!isNaN(parseFloat(el.value))) {
                    allocatedAmount += parseFloat(el.value);
                }else {
                    allocatedAmount += 0;
                }
            })

            $('#allocatedAmount').text(allocatedAmount)
        }
        
        refreshAmount();
        inputs.on('keyup', function(e) {
            refreshAmount()
        })
    })
</script>