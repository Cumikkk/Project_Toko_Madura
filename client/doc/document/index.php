<?php

use App\Models\Account;
use App\Models\Helper;
use App\Models\ProfilePerusahaan;

if(empty($_GET['id'])) {
    die("<script>alert('Invalid ID'); location.href = '/account';</script>");
}

$id_acc = Helper::form_input($_GET['id'] ?? "-");
$account = Account::realAccountDetail($id_acc);
$sqlGet = $db->query("
    SELECT tb_racctype.RTYPE_FILE
    FROM tb_racctype
    WHERE tb_racctype.ID_RTYPE = ".$account['ACC_TYPE']."
    LIMIT 1
");
$row = $sqlGet->fetch_assoc();
$profile = ProfilePerusahaan::get();
if(empty($account)) {
    die("<script>alert('Invalid Account'); location.href = '/account';</script>");
}
?>
<style>
    .top_aling {
        vertical-align: top !important;
    }
    .white_left {
        white-space: normal !important;
        text-align: left !important;
    }
</style>
<div class="dashboard-breadcrumb mb-25">
    <h2>Document <?= App\Models\Regol::cddType($account['ACC_CDD'])['text'] ?> </h2>
</div>
<?php
    $accountType = filter_var(strtolower($account['RTYPE_TYPE_AS'] ?? ""), FILTER_SANITIZE_URL);
    $acc_cdd = strtolower(App\Models\Regol::cddType($account['ACC_CDD'])['text']);
    // print_r($accountType);
    // print_r("/{$accountType}/{$acc_cdd}.php");
    if(file_exists(__DIR__ . "/{$accountType}/{$acc_cdd}.php")) {
        require_once __DIR__ . "/{$accountType}/{$acc_cdd}.php";
    }
?>
