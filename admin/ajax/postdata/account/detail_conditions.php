<?php

use App\Models\Account;
use App\Models\Helper;
use App\Models\Refferal;
use App\Models\User;
use Config\Core\SystemInfo;

$actionId = Helper::form_input($_POST['actionId'] ?? "");
$sqlGetAccountConditions = $db->query("SELECT * FROM tb_sales_conditions WHERE MD5(MD5(ID_SLSCONDITION)) = '{$actionId}' AND SLSCONDITION_STS = 0 LIMIT 1");
if($sqlGetAccountConditions->num_rows == 0) {
    echo '<p class="text-danger">No pending sales conditions found for this action ID.</p>';
    exit;
}

$accountConditions = $sqlGetAccountConditions->fetch_assoc();
$account = Account::realAccountDetail(md5(md5($accountConditions['SLSCONDITION_IDACC'])));
if(!$account) {
    echo '<p class="text-danger">Invalid Account.</p>';
    exit;
}

$partner = User::findByMemberId($accountConditions['SLSCONDITION_PARTNER']);

$sqlGetCommissionData = $db->query("
    SELECT 
        upline.*,
        tsc.SLSCOM_FOREX,
        tsc.SLSCOM_GOLD,
        tsc.SLSCOM_INDEX
    FROM tb_sales_commission as tsc
    JOIN (
        SELECT
            tm.MBR_ID,
            tm.MBR_NAME,
            tm.MBR_CODE,
            tm.MBR_TYPE,
            IFNULL(tss.SLSSTRC_NAME, 'Trader') as sales_type
        FROM tb_member as tm
        LEFT JOIN tb_sales_structure as tss ON (tss.ID_SLSSTRC = tm.MBR_TYPE)
    ) as upline ON (upline.MBR_ID = tsc.SLSCOM_MBR)
    WHERE tsc.SLSCOM_IDACC = ".$account['ID_ACC']." 
    ORDER BY tsc.ID_SLSCOM ASC
");

if(!$sqlGetCommissionData || $sqlGetCommissionData->num_rows == 0) {
    echo '<p class="text-warning">No commission data found for this account.</p>';
    exit;
} 

$refferalLink = sprintf("%s/%s", SystemInfo::app('CLIENT_URL'), implode("-", [$account['RTYPE_SUFFIX'], App\Models\Refferal::parseProductType($account['RTYPE_TYPE']), $account['MBR_CODE']]));
?>

<div style="line-height: 1.7rem;">
    <p class="mb-0"><strong>Client Name:</strong> <?= $account['MBR_NAME'] ?></p>
    <p class="mb-0"><strong>Account Number:</strong> <?= $account['ACC_LOGIN'] ?></p>
    <p class="mb-0"><strong>Type Account:</strong> <?= $account['RTYPE_NAME'] ?></p>
    <p class="mb-0"><strong>Account opening date:</strong> <?= date("d-M-Y", strtotime($account['ACC_WPCHECK_DATE'] ?? "")) ?></p>
    <p class="mb-0"><strong>Referral Code:</strong> <a href="javascript:void(0)" class="referral-link text-decoration-underline"><?= $refferalLink ?></a></p>
    <p class="mb-0"><strong>Rate:</strong> <?= $account['RTYPE_ISFLOATING']? "Floating" : $account['RTYPE_RATE'] ?></p>
    <p class="mb-0"><strong>Charge Commission:</strong> $<?= $account['RTYPE_KOMISI'] ?></p>
</div>
<hr>
<div class="mb-3" style="line-height: 1.7rem;">
    <p class="mb-0"><strong>Business Partner Name:</strong> <?= $partner['MBR_NAME'] ?></p>
    <p class="mb-0"><strong>Sales Name:</strong> <?= $accountConditions['SLSCONDITION_SALES_NAME'] ?></p>
    <p class="mb-0"><strong>Branch:</strong> <?= $accountConditions['SLSCONDITION_BRANCH'] ?></p>
</div>

<table class="table table-hover table-bordered">
    <thead>
        <tr>
            <th rowspan="2" style="vertical-align: middle">Upline</th>
            <th rowspan="2" style="vertical-align: middle">Position</th>
            <th colspan="3" class="text-center">Amount</th>
        </tr>
        <tr>
            <th width="15%">Forex</th>
            <th width="15%">Gold</th>
            <th width="15%">Index</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($sqlGetCommissionData->fetch_all(MYSQLI_ASSOC) as $commission) : ?>
            <tr>
                <td><?= $commission['MBR_NAME'] ?></td>
                <td><?= $commission['sales_type'] ?></td>
                <td class="text-end">$<?= $commission['SLSCOM_FOREX'] ?></td>
                <td class="text-end">$<?= $commission['SLSCOM_GOLD'] ?></td>
                <td class="text-end">$<?= $commission['SLSCOM_INDEX'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script type="text/javascript">
    $(document).ready(function() {
        document.querySelector('.referral-link').addEventListener('click', function(e) {
            e.preventDefault();
            const text = this.innerText;
            const link = this;
            const originalText = text;
            
            // Gunakan Clipboard API jika tersedia
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function() {
                    showCopySuccess(link, originalText);
                }).catch(function(err) {
                    console.error('Failed to copy:', err);
                    fallbackCopy(text, link, originalText);
                });
            } else {
                // Fallback untuk browser lama
                fallbackCopy(text, link, originalText);
            }
        })
    })
    
    // Fallback copy function
    function fallbackCopy(text, link, originalText) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        
        try {
            document.execCommand('copy');
            showCopySuccess(link, originalText);
        } catch (err) {
            console.error('Fallback copy failed:', err);
        } finally {
            document.body.removeChild(textarea);
        }
    }
     
    // Show copy success message
    function showCopySuccess(link, originalText) {
        link.style.color = '#28a745';
        link.innerText = 'Copy success!';
        
        setTimeout(function() {
            link.innerText = originalText;
            link.style.color = '';
        }, 2000);
    }
</script>