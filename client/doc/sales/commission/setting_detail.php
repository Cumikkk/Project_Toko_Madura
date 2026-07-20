<?php

use App\Library\Sales\SalesMain;

$_SESSION['modal'] = ['modal-commission-setting'];
$salesCommission = SalesMain::salesCommission($user['MBR_ID']);
$commissionSetting = [];
if($salesCommission) {
    $commissionSetting = $salesCommission->commissionSetting($productDetail['ID_RTYPE'])->get();
}
?>

<div class="table-responsive">
    <table class="table table-hover table-bordered">
        <thead>
            <tr>
                <th class="text-start"><?= implode("/", [$productDetail['RTYPE_TYPE'], $productDetail['RTYPE_KOMISI'], ($productDetail['RTYPE_ISFLOATING']? "Floating" : $productDetail['RTYPE_RATE'])]); ?></th>
                <?php if(!empty($commissionSetting['symbols'])) : ?>
                    <?php foreach($commissionSetting['symbols'] as $symcat) : ?>
                        <th class="text-center" width="20%">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><?= $symcat['name']; ?> <small>(<?= $symcat['max']; ?>)</small></span>
                                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modal-setting" class="btn btn-sm btn-primary" data-symbol="<?= $symcat['name']; ?>" data-product="<?= $productDetail['RTYPE_SUFFIX']; ?>">
                                    <i class="fas fa-edit text-dark"></i>
                                </a>
                            </div>
                        </th>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($commissionSetting['settings'])) : ?>
                <?php foreach($commissionSetting['settings'] as $sales) : ?>
                    <tr>
                        <td class="text-start"><?= $sales['name'] ?></td>
                        <?php foreach($sales['amounts'] as $cat) : ?>
                            <td class="text-center">
                                <a href="javascript:void(0)" class="text-decoration-none amount-setting" data-symbol="<?= $cat['category_name'] ?>" data-sales="<?= $sales['code'] ?>" data-product="<?= $productDetail['RTYPE_SUFFIX'] ?>">
                                    <?= $cat['amount']; ?>
                                </a>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('.amount-setting').on('click', function(evt) {
            let target = $(evt.currentTarget);
            if(!target.data()) {
                Swal.fire("Failed", "Invalid Data", "error");
                return;
            }
            
            let data = target.data();
            Swal.fire({
                title: "Commission Amount",
                input: "number",
                inputLabel: `${data.sales?.replaceAll('_', ' ')?.toUpperCase()} - ${data.symbol?.toUpperCase()}`,
                customClass: {
                    inputLabel: 'swal2-title fs-6',
                    inputValue: 'swal2-title'
                },
                showCancelButton: true,
                reverseButtons: true,
                showLoaderOnConfirm: true,
                preConfirm: async (input) => {
                    if(!input) return Swal.showValidationMessage("Please fill amount field");
                    if(isNaN(input)) return Swal.showValidationMessage("Invalid value");

                    let postData = target.data();
                    postData.amount = input;
                    let response = await $.post("/ajax/post/commission/update_amount", postData, (resp) => resp, 'json');
                    if(!response.success) {
                        return Swal.showValidationMessage(response.message);
                    }

                    return response;
                }
            }).then((result) => {
                if(result.isConfirmed && result.value.success) {
                    target.text(result.value.data.amount);
                }
            })
        })
    })
</script>