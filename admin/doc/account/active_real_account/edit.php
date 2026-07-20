<?php
    
    use App\Models\Account;
    use App\Models\Dpwd;
    use App\Models\Helper;
    use App\Models\FileUpload;
    $data = Helper::getSafeInput($_GET);

    $COMPANY         = App\Models\CompanyProfile::$name;
    $page_title      = 'Active Real Account';
    $web_name_full   = $COMPANY;
    $progressAccount = Account::realAccountDetail($data["d"]);
    $account         = $progressAccount;
    $progressAccount = array_merge((Account::accoundCondition($progressAccount["ID_ACC"]) ?? []), $progressAccount);
    $depositData     = Dpwd::findByRaccId($progressAccount["ID_ACC"]);
    $id_acc          = $data["d"];
    $userBanks       = (!empty($progressAccount["MBR_BKJSN"])) ? json_decode($progressAccount["MBR_BKJSN"], true) : [];
    $MULTIPN         = ["multi", "multilateral"];
    $SPANAME         = ["spa"];
    $current_url     = '/account/active_real_account/edit/'.$id_acc;

    if (!function_exists('form_input')) {
        function form_input($data){
            return Helper::form_input($data);
        }
    }
    
    $jenis = [
        "profile-pribadi",
        "other",
        "kontak-darurat",
        "pekerjaan",
        "kekayaan",
        "bank",
        "picture",
        "additional",
    ];
?>
<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Edit Data</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
                <li class="breadcrumb-item"><a href="javascript:void(0);">Account</a></li>
                <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(2) ?>/view"><?php echo $page_title; ?></a></li>
                <li class="breadcrumb-item"><a href="javascript:void(0);">Edit Data</a></li>
                <li class="breadcrumb-item active" aria-current="page"><a href="javascript:void(0);"><?php echo $account['ACC_LOGIN'] ?></a></li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="panel panel-primary tabs-style-4">
                            <div class="tab-menu-heading">
                                <div class="tabs-menu">
                                    <ul class="nav panel-tabs me-3">
                                        <?php foreach($jenis as $key => $j) : ?>
                                            <li class=""><a href="#tab-<?php echo $j ?>" class="<?php echo ($key == 0) ? "active" : ""; ?>" data-bs-toggle="tab"><?php echo ucfirst(str_replace("-", " ", $j)) ?></a></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-9 p-0 m-0 mb-3">
                        <div class="tab-content">
                            <?php foreach($jenis as $key => $j) : ?>
                                <div class="tab-pane <?php echo ($key == 0)? "active" : ""; ?>" id="tab-<?php echo $j; ?>">
                                    <?php 
                                    if(file_exists($rp = __DIR__."/realacc-edit/$j.php")) {
                                        include $rp;
                                    } else {
                                        echo 'NotFound!';
                                    }
                                    ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>