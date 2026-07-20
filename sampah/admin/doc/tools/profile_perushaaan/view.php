<?php
    use App\Models\CompanyProfile;
    use App\Models\ProfilePerusahaan;
        
    $COMPANY_PRF     = CompanyProfile::profilePerusahaan();
    $COMPANY_MOF     = CompanyProfile::getMainOffice();
    $profile         = array_merge(($COMPANY_PRF ?? []), ["OFFICE" => ($COMPANY_MOF ?? [])]);
?>
<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Profile Perusahaan</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item">Tools</li>
            <li class="breadcrumb-item active" aria-current="page">Profile Perusahaan</li>
        </ol>
    </div>
</div>
<?php require_once __DIR__ . "/view_company.php"; ?>
<?php require_once __DIR__ . "/view_bank.php"; ?>
<?php require_once __DIR__ . "/view_kantor.php"; ?>