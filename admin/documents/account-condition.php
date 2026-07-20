<?php

    use App\Models\Account;
    use App\Models\Dpwd;
    use App\Models\Helper;
    use App\Models\FileUpload;
    use App\Models\CompanyProfile;
    use App\Models\MemberBank;

    $realAccount = Account::realAccountDetail(($acc ?? ""));
    $accnd = Account::accoundCondition($realAccount['ID_ACC']);
    $depositData     = Dpwd::findByRaccId($realAccount["ID_ACC"]);
    $companyProfile = CompanyProfile::profilePerusahaan();
    $mainOffice = CompanyProfile::getMainOffice();
    $userActiveBanks = MemberBank::activeBanks($realAccount['ACC_MBR']);
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
        </style>
    </head>
    <body>
        <?php require_once(__DIR__  . "/header.php"); ?><hr>

        <div class="section">
            <h4 class="text-center" style="margin: 0px;">ACCOUNT CONDITION</h4>
            <table class="table no-border" style="margin-top: 20px;">
                <tbody>
                    <tr>
                        <th width="60%" class="text-center" style="background-color: #edebe0;">Detail</th>
                        <th width="2%"></th>
                        <th class="text-center" style="background-color: #edebe0;">Product</th>
                    </tr> 
                    <tr>
                        <td class="v-align-top" style="font-size: 15px; text-align: left;">
                            <table class="table no-border" style="font-size: 15px;">
                                <tbody>
                                    <tr>
                                        <td width="30%" class="v-align-top">Kondisi ini efektif mulai bulan</td>
                                        <td width="3%" class="v-align-top">:</td>
                                        <td class="v-align-top">
                                            <?= date('m', strtotime($realAccount['ACC_WPCHECK_DATE'])).' ('.Helper::bulan(date('m', strtotime($realAccount['ACC_WPCHECK_DATE']))).')'; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="30%" class="v-align-top">No. Account</td>
                                        <td width="3%" class="v-align-top">:</td>
                                        <td class="v-align-top"><?= $realAccount['ACC_LOGIN'] ?></td>
                                    </tr>
                                    <tr>
                                        <td width="30%" class="v-align-top">Nama Investor</td>
                                        <td width="3%" class="v-align-top">:</td>
                                        <td class="v-align-top"><?= $realAccount['ACC_FULLNAME'] ?></td>
                                    </tr>
                                    <tr>
                                        <td width="30%" class="v-align-top">Email Investor</td>
                                        <td width="3%" class="v-align-top">:</td>
                                        <td class="v-align-top"><?= $realAccount['MBR_EMAIL'] ?></td>
                                    </tr>
                                    <tr>
                                        <td width="30%" class="v-align-top">No. Telepon</td>
                                        <td width="3%" class="v-align-top">:</td>
                                        <td class="v-align-top"><?= $realAccount['ACC_F_APP_PRIBADI_HP'] ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                        <td></td>
                        <td class="v-align-top" style="font-size: 15px; text-align: left;">
                            <table class="table no-border">
                                <tbody>
                                    <tr>
                                        <td width="30%" class="v-align-top">Product</td>
                                        <td width="3%" class="v-align-top">:</td>
                                        <td class="v-align-top"><?= $realAccount['RTYPE_NAME'] ?></td>
                                    </tr>
                                    <tr>
                                        <td width="30%" class="v-align-top">Rate</td>
                                        <td width="3%" class="v-align-top">:</td>
                                        <td class="v-align-top"><?= ($realAccount['RTYPE_ISFLOATING'] == 1) ? 'Floating' : $realAccount['RTYPE_RATE'] ?></td>
                                    </tr>
                                    <tr>
                                        <td width="30%" class="v-align-top">Commision</td>
                                        <td width="3%" class="v-align-top">:</td>
                                        <td class="v-align-top">$ <?= Helper::formatCurrency($realAccount['RTYPE_KOMISI']) ?></td>
                                    </tr>
                                    <!-- <tr>
                                        <td width="30%" class="v-align-top">Introducing Broker</td>
                                        <td width="3%" class="v-align-top">:</td>
                                        <td class="v-align-top"><?= $accnd['MBR_NAME'] ?></td>
                                    </tr> -->
                                </tbody>
                            </table>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <th width="60%" class="text-center" style="background-color: #edebe0;">User Banks</th>
                        <th width="2%"></th>
                    </tr> 
                    <?php foreach($userActiveBanks as $key => $userBank) : ?>
                        <tr>
                            <td class="v-align-top" style="font-size: 15px; text-align: left;">
                                <table class="table no-border" style="font-size: 15px;">
                                    <tbody>
                                        <tr>
                                            <td colspan="3" class="v-align-top">
                                                -
                                                <?= $userBank['MBANK_NAME'] ?> /
                                                <?= $userBank['MBANK_ACCOUNT'] ?> /
                                                <?= $userBank['MBANK_HOLDER'] ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <table class="table no-border" style="margin-top: 20px;">
                <tbody>
                    <tr>
                        <td width="50%" class="text-center v-align-top">Accounting</td>
                        <td width="50%" class="text-center v-align-top">Direktur Utama</td>
                    </tr>
                    <tr>
                        <td><div style="height: 50px;"></div></td>
                        <td><div style="height: 50px;"></div></td>
                    </tr>
                    <tr>
                        <td width="50%" class="text-center v-align-top">( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</td>
                        <td width="50%" class="text-center v-align-top">( <?= $companyProfile['PROF_DEWAN_DIREKSI'] ?> )</td>
                    </tr>
                </tbody>
            </table>
            <p class="text-center" style="margin-bottom: 0px;">Menyatakan pada tanggal : <b><?= date("Y-m-d H:i:s", strtotime($accnd['ACCCND_DATEMARGIN'])) ?></b></p>
        </div>
    </body>
</html>