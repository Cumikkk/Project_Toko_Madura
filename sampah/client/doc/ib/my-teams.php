<?php 

use App\Library\Sales\SalesMain;
$salesData = SalesMain::getUserType($user['MBR_TYPE']);
if(!$salesData || $salesData->isAllowToRequestAccountCondition() === false) {
    die("<script>location.href = '/404'</script>");
}

?>
<link rel="stylesheet" href="/assets/css/my-teams.css">
<div class="dashboard-breadcrumb mb-25">
    <h2>My Teams</h2>
    <div class="input-group-a dashboard-filter"></div>
</div>

<?php require_once __DIR__ . "/components/summary.php" ?>
<?php require_once __DIR__ . "/components/account_conditions.php" ?>