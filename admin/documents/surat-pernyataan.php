<?php

    use App\Models\Admin;
    use App\Models\Account;
    use App\Models\Dpwd;
    use App\Models\Helper;
    use App\Models\FileUpload;
    use App\Models\CompanyProfile;
    use App\Models\ProfilePerusahaan;

    $realAccount     = Account::realAccountDetail(($acc ?? ""));
    $accnd           = Account::accoundCondition($realAccount['ID_ACC']);
    $depositData     = Dpwd::findByRaccId($realAccount["ID_ACC"]);
    $COMPANY_PRF     = CompanyProfile::profilePerusahaan();
    $COMPANY_MOF     = CompanyProfile::getMainOffice();
    $company         = CompanyProfile::$name;
    $list_wpb_satu   = ProfilePerusahaan::list_wpb(-1, 2);
    $list_wpb_satu   = ProfilePerusahaan::list_wpb(2, 2);
    $tgl_lahir       = Helper::bulan(date("m", strtotime($realAccount['ACC_TANGGAL_LAHIR'])));
    $date_day        = Helper::hari(date('w', strtotime($realAccount["ACC_F_PERJ_DATE"])));
    $date_month      = Helper::bulan(date('m', strtotime($realAccount["ACC_F_PERJ_DATE"])));
    $company_name    = $COMPANY_PRF["COMPANY_NAME"];
    $userBank        = (!empty($progressAccount["MBR_BKJSN"])) ? json_decode($progressAccount["MBR_BKJSN"], true) : [];
    $profile         = array_merge(($COMPANY_PRF ?? []), ["OFFICE" => ($COMPANY_MOF ?? [])]);
    $bank            = explode("/", ($depositData['DPWD_BANKSRC'] ?? ''));
    $admBanks        = $db->query("SELECT * FROM tb_bankadm");
    $offices         = $db->query("SELECT * FROM tb_office");
    $bankName        = $bank[0] ?? "-";
    $bankAccount     = $bank[1] ?? "-";
    $bankHolder      = $bank[2] ?? "-";

    $bapakatauibu = (!empty($realAccount['ACC_F_APP_PRIBADI_KELAMIN']) && $realAccount['ACC_F_APP_PRIBADI_KELAMIN'] == "Laki-laki")
    ? 'Bapak/<strike>Ibu</strike>'
    : '<strike>Bapak</strike>/Ibu';

    $idAcc = Helper::form_input($_GET['acc'] ?? "");
    $account = Account::realAccountDetail($idAcc);
    if(!$account) {
        exit('Invalid Request');
    }

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
            <h4 class="text-center" style="margin: 0px;">SURAT PERNYATAAN</h4>
            Saya, yang bertanda tangan di bawah ini:
            <table class="table no-border" style="margin-top: 20px; font-size: 15px;">
                <tbody>
                    <tr>
                        <td width="30%" class="v-align-top">Nama Lengkap</td>
                        <td width="3%" class="v-align-top">:</td>
                        <td class="v-align-top"><?= $realAccount['ACC_FULLNAME'] ?></td>
                    </tr>
                    <tr>
                        <td width="30%" class="v-align-top">Tempat/TanggalLahir</td>
                        <td width="3%" class="v-align-top">:</td>
                        <td class="v-align-top">
                            <?= $realAccount['ACC_TEMPAT_LAHIR'] ?>,
                            <?= date("d", strtotime($realAccount['ACC_TANGGAL_LAHIR'])) ?>
                            <?= $tgl_lahir ?>
                            <?= date("Y", strtotime($realAccount['ACC_TANGGAL_LAHIR'])) ?>
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" class="v-align-top">Alamat</td>
                        <td width="3%" class="v-align-top">:</td>
                        <td class="v-align-top"><?= $realAccount['ACC_ADDRESS'] ?></td>
                    </tr>
                    <tr>
                        <td width="30%" class="v-align-top">No. Identitas</td>
                        <td width="3%" class="v-align-top">:</td>
                        <td class="v-align-top"><?= $realAccount['ACC_TYPE_IDT'] ?> / <?= $realAccount['ACC_NO_IDT'] ?></td>
                    </tr>
                </tbody>
            </table>

            <p class="text-justify" style="font-size: 15px;">Dengan ini menerangkan dan menyatakan dengan sebenar-benarnya bahwa saya telah mendapat penjelasan dari : <strong><?= $company_name; ?></strong> yang berkedudukan di Jakarta, melalui Wakil Pialang Berjangka yang bernama <?= $realAccount["ACC_F_PERJ_WPB"] ?> mengenai mekanisme transaksi perdagangan berjangka yang akan saya lakukan sendiri. Saya juga :</p>

            <ol style="font-size: 15px;">
                <li>Telah sepenuhnya membaca, mengerti, serta memahami penjelasan mengenai isi dokumen
                Perjanjian Pemberian Amanat Nasabah, dokumen Pemberitahuan Adanya Risiko, serta semua
                ketentuan dan peraturan perdagangan (<i>tradingrules</i>);</li>
                <li>Telah menerima penjelasan dan mengerti bahwa hanya Wakil Pialang Berjangka yang berhak
                menjelaskan dokumen Pemberitahuan Adanya Risiko, dokumen Perjanjian Pemberian Amanat,
                serta peraturan perdagangan (<i>tradingrules</i>);</li>
                <li>Telah menerima penjelasan dan mengerti bahwa <i>user id</i> dan <i>password</i> bersifat pribadi dan rahasia
                sehingga tidak akan menyerahkan kepada pihak manapun termasuk kepada Wakil Pialang
                Berjangka, pihak yang dipekerjakan maupun pihak yang diberdayakan Pialang Berjangka, segala
                risiko akibat penyerahan <i>user id</i> dan <i>password</i> kepada pihak lain menjadi tanggung jawab saya; dan</li>
                <li>Telah menerima penjelasan dan mengerti mekanisme penyelesaian perselisihan dan pilihan tempat
                penyelesaian perselisihan yakni melalui Badan Arbitrase atau Pengadilan Negeri.</li>
            </ol>

            <p class="text-justify" style="font-size: 15px;">Terhadap apa yang saya jalankan dalam transaksi ini berikut segala risiko yang akan timbul akibat transaksi sepenuhnya akan menjadi tanggung jawab saya.</p>
            
            <p class="text-justify" style="font-size: 15px;">Bersama ini saya menyatakan bahwa dana yang saya gunakan untuk bertransaksi di <strong><?= $company_name; ?></strong>  adalah milik saya pribadi dan bukan dana pihak lain, serta tidak d iperoleh dari hasil kejahatan, penipuan, penggelapan, tindak pidana korupsi, tindak pidana narkotika , tindak pidana di bidang kehutanan, hasil pencucian uang, dan perbuatan melawan hukum lainnya serta tidak dimaksudkan untuk melakukan pencucian uang dan/atau pendanaan terorisme.</p>

            <p style="margin: 0px; font-size: 15px;">Pernyataan menerima/tidak: Ya</p>
            <p style="margin: 0px; font-size: 15px;">Menerima pada Tanggal (DD/MM/YYYY): <?= date("d/m/Y", strtotime($realAccount['ACC_WPCHECK_DATE'])) ?></p>
        </div>
    </body>
</html>