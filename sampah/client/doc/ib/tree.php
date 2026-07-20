<?php

use App\Models\Account;
use App\Models\Ib;
use App\Models\Refferal;
use App\Models\User;

$isIB = User::get_ib_data($user['MBR_ID'], [-1]); 
$upline = Ib::userUpline($user['MBR_IDSPN']);
if(!$upline) {
    die("<script>alert('Setup Failed'); location.href = '/ib/become'; ;</script>");
}    
?>

<?php if($isIB) : ?>
    <?php 
    $totalDownline = count(Ib::getNetworks($user['MBR_ID'], "downline"));
    $userRefferal = Refferal::createUserReferral($user['MBR_ID']);
    $accountRefferal = Refferal::createAccountReferralV2($user['MBR_ID']);
    ?>
    
    <link rel="stylesheet" href="/assets/bstreeview/bstreeview.css"/>
    <script src="/assets/bstreeview/bstreeview.js"></script>
    <div class="row">
        <div class="col-md-12">
            <div class="dashboard-breadcrumb">
                <h2>Treeview and Refferal</h2>
                <div class="input-group-a dashboard-filter">
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-25">
            <div class="panel">
                <div class="panel-header">
                    <h5>Upline Profile</h5>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" style="table-layout: fixed; word-break: break-word;">
                            <tbody>
                                <tr>
                                    <td width="30%">Upline</td>
                                    <td width="70%"><?= $upline['MBR_NAME'] ?? "-" ?></td>
                                </tr>
                                <tr>
                                    <td width="30%">IB Status</td>
                                    <td width="70%"><?= Ib::$status[ $isIB['BECOME_STS'] ]['html']; ?></td>
                                </tr>
                                <tr>
                                    <td width="30%">Total Downline</td>
                                    <td width="70%"><?= $totalDownline ?></td>
                                </tr>

                                <?php if($salesData && $salesData->isCanShareRefferal()) : ?>
                                <?php   if($user['MBR_REFFERALALL'] == -1) : ?>
                                    <tr>
                                        <td width="30%">User Refferal</td>
                                        <td width="70%"><a href="javascript:void(0)" class="copytext"><?= $userRefferal ?></a></td>
                                    </tr>
                                <?php   endif; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <?php if($salesData && $salesData->isCanShareRefferal()) : ?>
            <div class="col-md-12 mb-3">
                <div class="panel">
                    <div class="panel-header">
                        <h5>Account Refferal</h5>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" style="table-layout: fixed; word-break: break-word;">
                                <tbody>
                                    <?php foreach($accountRefferal as $accRef) : ?>
                                        <tr>
                                            <td width="30%"><?= implode("/", [$accRef['name'], $accRef['rate'], $accRef['commission']]); ?></td>
                                            <td width="70%" class="text-start">
                                                <a href="javascript:void(0)" class="copytext"><?= $accRef['link'] ?></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="col-md-12">
            <div class="panel">
                <div class="panel-header">
                    <h5>Member Tree</h5>
                </div>
                <div class="panel-body" style="min-height: 300px;">
                    <div id="tree"></div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {
            $.post("/ajax/post/ib/treeview", {}, function(resp) {
                $('#tree').bstreeview({
                    data: resp,
                    expandIcon:'fa fa-angle-down',
                    collapseIcon:'fa fa-angle-right'
                })
            }, 'json')
        })

        $('.copytext').on('click', function() {
            const textToCopy = $(this).text();
            const $this = $(this);
            const originalText = $this.html();
            
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(textToCopy)
                    .then(() => {
                        $this.html('<span class="text-success">Copied!</span>');
                        setTimeout(() => {
                            $this.html(originalText);
                        }, 1500);
                    })
                    .catch(err => {
                        copyTextFallback(textToCopy, $this, originalText);
                    });
            } else {
                copyTextFallback(textToCopy, $this, originalText);
            }
        });

        function copyTextFallback(text, element, originalText) {
            try {
                const tempInput = $('<textarea>');
                tempInput.val(text);
                $('body').append(tempInput);
                
                tempInput.select();
                const successful = document.execCommand('copy');
                tempInput.remove();
                
                if (successful) {
                    element.html('<span class="text-success">Copied!</span>');
                } else {
                    element.html('<span class="text-danger">Failed to copy</span>');
                }
                
                setTimeout(() => {
                    element.html(originalText);
                }, 1500);
            } catch (err) {
                console.error('Failed to copy text: ', err);
                element.html('<span class="text-danger">Copy not supported</span>');
                setTimeout(() => {
                    element.html(originalText);
                }, 1500);
            }
        }
    </script>

    <style>
        .copytext {
            cursor: pointer;
            color: #0d6efd;
            text-decoration: underline;
        }
        .copytext:hover {
            color: #0a58ca;
        }
    </style>
    
<?php else : ?>
    <?php require_once __DIR__ . "/../../template/404.php"; ?>
<?php endif; ?>