<?php

use App\Library\Sales\SalesCommission;
use App\Models\Account;
use App\Models\Helper;
use App\Models\Refferal;
use App\Models\SalesConditions;
use App\Models\User;
use Config\Core\SystemInfo;

$idSalesCondition = Helper::form_input($_GET['id'] ?? "");
if(empty($idSalesCondition)) {
    exit("Invalid Request");
}

$salesCondition = SalesConditions::findByIdHash($idSalesCondition);
if(!$salesCondition) {
    exit("Sales conditions data not found");
}

$account = Account::realAccountDetail(md5(md5($salesCondition['SLSCONDITION_IDACC'] ?? "")));
if(!$account) {
    exit("Associated account data not found");
}

$client = User::findByMemberId($account['ACC_MBR']);
if(!$client) {
    exit("Associated client data not found");
}

$partner = User::findByMemberId($salesCondition['SLSCONDITION_PARTNER']);
if(!$partner) {
    exit("Associated partner data not found");
}

$salesCommissionData = SalesConditions::commissions($salesCondition['ID_SLSCONDITION']);
$refferalLink = sprintf("%s/%s", SystemInfo::app('CLIENT_URL'), implode("-", [$account['RTYPE_SUFFIX'], App\Models\Refferal::parseProductType($account['RTYPE_TYPE']), $account['MBR_CODE']]));
?>

<!DOCTYPE html>
<html>
    <head>
        <?php require_once(__DIR__  . "/style.php"); ?>
        <style>
            @page {
                margin-left: 50px;
                margin-right: 50px;
            }

            body {
                font-family: Arial, sans-serif;
                font-size: 14px;
            }

            .table-bordered {
                border: 1px solid #000;
                border-collapse: collapse;
            }

            .table-bordered th, .table-bordered td {
                border: 1px solid #000;
                padding: 8px;
            }

            .table-ttd {
                text-align: center;
            }
        </style>
    </head>
    <body>
        <?php require_once(__DIR__  . "/header.php"); ?><hr>
        <div class="section">
            <h2 class="text-center">ACCOUNT CONDITION</h2>
            <table width="100%" style="margin-bottom: 20px;">
                <tbody style="line-height: 20px;">
                    <tr>
                        <td width="30%">Nama Client</td>
                        <td width="2%">:</td>
                        <td><?php echo htmlspecialchars($client['MBR_NAME']); ?></td>
                    </tr>
                    <tr>
                        <td width="30%">No Account</td>
                        <td width="2%">:</td>
                        <td><?php echo htmlspecialchars($account['ACC_LOGIN']); ?></td>
                    </tr>
                    <tr>
                        <td width="30%">Type Account</td>
                        <td width="2%">:</td>
                        <td><?php echo htmlspecialchars($account['RTYPE_TYPE']); ?></td>
                    </tr>
                    <tr>
                        <td width="30%">Tanggal Open Account</td>
                        <td width="2%">:</td>
                        <td><?= date("d-M-Y", strtotime($account['ACC_WPCHECK_DATE'] ?? "")) ?></td>
                    </tr>
                    <tr>
                        <td width="30%">Referral Code</td>
                        <td width="2%">:</td>
                        <td><a target="_blank" href="<?= $refferalLink; ?>"><?= $refferalLink ?></a></td>
                    </tr>
                    <tr>
                        <td width="30%">Rate</td>
                        <td width="2%">:</td>
                        <td><?php echo htmlspecialchars($account['RTYPE_RATE']); ?></td>
                    </tr>
                    <tr>
                        <td width="30%">Komisi Charge</td>
                        <td width="2%">:</td>
                        <td>$<?php echo htmlspecialchars($account['RTYPE_KOMISI']); ?></td>
                    </tr>
                </tbody>
            </table>
            <table width="100%" style="margin-bottom: 15px;">
                <tbody style="line-height: 20px;">
                    <tr>
                        <td width="30%">Nama Business Partner</td>
                        <td width="2%">:</td>
                        <td><?php echo htmlspecialchars($partner['MBR_NAME']); ?></td>
                    </tr>
                    <tr>
                        <td width="30%">Nama Sales</td>
                        <td width="2%">:</td>
                        <td><?php echo htmlspecialchars($salesCondition['SLSCONDITION_SALES_NAME']); ?></td>
                    </tr>
                    <tr>
                        <td width="30%">Cabang</td>
                        <td width="2%">:</td>
                        <td><?php echo htmlspecialchars($salesCondition['SLSCONDITION_BRANCH']); ?></td>
                    </tr>
                </tbody>
            </table>
            <table width="100%" class="table-bordered" style="margin-bottom: 20px;">
                <thead style="background-color: #cccccc;">
                    <tr>
                        <th width="5%" rowspan="2">No</th>
                        <th rowspan="2">Pembagian Komisi dan Rebate</th>
                        <th rowspan="2">Posisi</th>
                        <th colspan="3"
                        >Jumlah</th>
                    </tr>
                    <tr>
                        <th width="15%">Forex</th>
                        <th width="15%">Gold</th>
                        <th width="15%">Index</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($salesCommissionData) === 0) : ?>
                        <tr>
                            <td colspan="6">No Data Available</td>
                        </tr>
                    <?php else : ?>
                        <?php for($i = 0; $i < count($salesCommissionData); $i++) : ?>
                            <tr>
                                <td><?= $i + 1 ?>.</td>
                                <td><?= $salesCommissionData[$i]['MBR_NAME'] ?></td>
                                <td><?= $salesCommissionData[$i]['sales_type'] ?></td>
                                <td class="text-center">$<?= $salesCommissionData[$i]['SLSCOM_FOREX'] ?></td>
                                <td class="text-center">$<?= $salesCommissionData[$i]['SLSCOM_GOLD'] ?></td>
                                <td class="text-center">$<?= $salesCommissionData[$i]['SLSCOM_INDEX'] ?></td>
                            </tr>
                        <?php endfor; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <table width="100%" class="table-ttd">
                <tbody>
                    <tr>
                        <td width="50%" style="padding-bottom: 80px;">Team Local Partner,</td>
                        <td width="50%" style="padding-bottom: 80px;">Mengetahui,</td>
                    </tr>
                    <tr>
                        <td width="50%"><?= htmlspecialchars($partner['MBR_NAME']); ?></td>
                        <td width="50%">
                            <?= htmlspecialchars($salesCondition['SLSCONDITION_WPNAME']); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </body>
</html>