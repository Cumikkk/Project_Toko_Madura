<?php

use App\Library\MT5\Mt5Trades;
use App\Library\Sales\SalesMain;
use App\Models\AccountType;
use App\Models\Helper;
use App\Models\Ib;
use App\Models\Rebate;
use App\Models\Symbols;
use App\Models\User;

$userCode = Helper::form_input($_GET['d'] ?? "");
$dateStart = Helper::form_input($_GET['start'] ?? date("Y-m-01"));
$dateEnd = Helper::form_input($_GET['end'] ?? date("Y-m-t"));
$userdata = User::findByCode($userCode);
if(!$userdata) {
    die("<script>alert('Invalid Code'); location.href = '/commision/share/view';</script>");
}

$salesData = SalesMain::getUserType($userdata['MBR_TYPE']);
$userType = ($salesData)? $salesData->salesDetail['SLSSTRC_NAME'] : "Trader";

/** get head of sales info */
$headOfSales = SalesMain::findMyHeadOfSales($userdata['MBR_ID']);
$salesCommission = false;
if($headOfSales) {
    $salesCommission = SalesMain::salesCommission($headOfSales->memberData['MBR_ID']);
}

/** Symbol category */
$symbolCategory = Symbols::AllCategory();

/** List account trading group by product */
$uplines = Ib::getNetworks($userdata['MBR_ID'], "upline");
$accountTrading = Mt5Trades::getTradingAccounts_FilterByDateGroupByProduct($dateStart, $dateEnd, $userdata['MBR_ID']);

/** Share permission */
$permisShare = $adminPermissionCore->isHavePermission($moduleId, "share");
?>

<div class="page-header">
	<div>
		<h2 class="main-content-title tx-24 mg-b-5">Share</h2>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
			<li class="breadcrumb-item"><a href="javascript:void(0);">Report</a></li>
			<li class="breadcrumb-item active" aria-current="page"><a href="javascript:void(0);">Share</a></li>
		</ol>
	</div>
    <div>
        <form action="/ajax/post<?= $permisShare['link'] ?? "" ?>" method="post" id="share-rebate">
            <div class="row">
                <div class="col-md-5 mb-3">
                    <div class="input-group">
                        <span class="input-group-text">DateStart</span>
                        <input type="date" name="datestart" value="<?= date("Y-m-d", strtotime($dateStart)); ?>" class="form-control date">
                    </div>
                </div>
                <div class="col-md-5 mb-3">
                    <div class="input-group">
                        <span class="input-group-text">DateEnd</span>
                        <input type="date" name="dateend" value="<?= date("Y-m-d", strtotime($dateEnd)); ?>" class="form-control date">
                    </div>
                </div>
    
                <div class="col-md-2 mb-3">
                    <input type="hidden" name="code" value="<?= $userCode ?>">
                    <button type="submit" class="btn btn-primary" <?= (!$permisShare)? "disabled" : ""; ?>><i class="fas fa-share"></i> Share</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mb-2">
        <div class="card custom-card">
            <div class="card-body">
                <h5 class="card-title text-primary mb-3">User Detail</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <p class="mb-0"><b>Fullname</b></p>
                        <p class="mb-0"><?= $userdata['MBR_NAME']; ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <p class="mb-0"><b>Email</b></p>
                        <p class="mb-0"><?= $userdata['MBR_EMAIL']; ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <p class="mb-0"><b>Date Registered</b></p>
                        <p class="mb-0"><?= date("Y-m-d", strtotime($userdata['MBR_DATETIME'])); ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <p class="mb-0"><b>Position</b></p>
                        <p class="mb-0"><?= $userType ?></p>
                    </div>
                    <div class="col-md-4 mb-3">
                        <p class="mb-0"><b>Total Lot</b></p>
                        <p class="mb-0" id="totalLot">Loading...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-2">
        <div class="card custom-card">
            <div class="card-body">
                <h5 class="card-title text-primary mb-3">Head Info</h5>
                <?php if($headOfSales) : ?>
                    <div class="d-flex flex-column gap-2">
                        <p class="mb-0"><b>Fullname:</b> <?= $headOfSales->memberData['MBR_NAME']; ?></p>
                        <p class="mb-0"><b>Email:</b> <?= $headOfSales->memberData['MBR_EMAIL']; ?></p>
                    </div>
                <?php else : ?>
                    <div class="alert alert-warning">User ini tidak memiliki head of sales</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-8 mb-2">
        <div class="card custom-card">
            <div class="card-body">
                <h5 class="card-title text-primary mb-3">Preview Rebate</h5>
                <div class="table-responsive">
                    <?php foreach($accountTrading as $acc) : ?>
                        <?php 
                        $product = AccountType::findById($acc['id_product']);
                        $tradingLot = $acc['lots'];
                        ?>
                        <?php if($product) : ?>
                            <table class="table table-hover table-bordered">
                                <thead>
                                    <tr><th colspan="<?= 3 + count($symbolCategory) ?>"><?= implode("/", [$product['RTYPE_NAME'], $product['RTYPE_KOMISI'], ($product['RTYPE_ISFLOATING'])? "Floating" : ($product['RTYPE_RATE'] / 1000)]) ?></th></tr>
                                    <tr>
                                        <th>User</th>
                                        <th>Position</th>
                                        <?php foreach($symbolCategory as $symbol) : ?>
                                            <th><?= $symbol['SYMCAT_NAME'] ?></th>
                                        <?php endforeach; ?>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if($salesCommission) : ?>
                                        <?php $commissionSetting = $salesCommission->commissionSetting($acc['id_product'])->get(); ?>
                                        <?php if($commissionSetting) : ?>
                                            <?php foreach($commissionSetting['settings'] as $setting) : ?>
                                                <?php $searchUplineCommission = array_search($setting['id'], array_column($uplines, 'MBR_TYPE')); ?>
                                                <?php if($searchUplineCommission !== FALSE) : ?>
                                                    <?php 
                                                    $uplineCommission = $uplines[ $searchUplineCommission ];
                                                    if($uplineCommission['MBR_ID'] == $userdata['MBR_ID']) {
                                                        /** Skip jika member id == diri sendiri */
                                                        continue;
                                                    } 
                                                    
                                                    $totalUsdPerbaris = 0; 
                                                    ?>
                                                    <tr>
                                                        <td><?= $uplineCommission['MBR_NAME'] ?></td>
                                                        <td><?= $setting['name'] ?></td>
                                                        <?php foreach($setting['amounts'] as $amount) : ?>
                                                            <?php 
                                                            $lot = 0;
                                                            if($acc['symbol']['id'] == $amount['category_id']) {
                                                                $lot = $acc['lots'];
                                                            }
    
                                                            $amountBonus = $amount['amount'] * $lot;
                                                            $totalUsdPerbaris += $amountBonus; 
                                                            ?>
    
                                                            <td>$<?= $amountBonus ?></td>
                                                        <?php endforeach; ?>
                                                        <td>$<?= $totalUsdPerbaris ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="<?= 3 + count($symbolCategory) ?>"><p class="fw-bold">Belum Diatur</p></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-2">
        <div class="card custom-card">
            <div class="card-body">
                <h5 class="card-title text-primary mb-3">Trade History</h5>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered" id="table-trades">
                        <thead>
                            <tr>
                                <th>Ticket</th>
                                <th>Symbol</th>
                                <th>Lot</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        let table_trades = $('#table-trades').DataTable({
            processing: true,
            serverSide: true,
            order: [[0,'desc']],
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
            dom: "lrtip",
            ajax: {
                url: "/ajax/datatable/commision/detail/trade_history",
                data: function(d) {
                    d.datestart = $('input[name="datestart"]').val();
                    d.dateend = $('input[name="dateend"]').val();
                    d.code = $('input[name="code"]').val();
                }
            },
            drawCallback: function(a) {
                $('#totalLot').text(a.json.totalLot)
                Swal.close();
            },
            columnDefs: [
                { targets: 2, className: "text-end" }
            ]
        });

        $('.date').on('change', function() {
            Swal.fire({
                text: "Please wait...",
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            })

            table_trades.ajax.reload();
            table_rebate.ajax.reload();
        })

        $("#share-rebate").on('submit', function(event) {
            event.preventDefault();
            let data = $(this).serialize();
            let button = $(this).find('button[type="submit"]');
            let url = $(this).attr('action')

            button.addClass('loading');
            $.post(url, data, (resp) => {
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        location.reload();
                    }
                })
            }, 'json')
        })
    });
</script>