

<!DOCTYPE html>
<html>
    <head>
        <?php require_once(__DIR__  . "/../../style.php"); ?>
        <style>
            @page {
                margin-left: 50px;
                margin-right: 50px;
            }
        </style>
    </head>
    <body>
        <?php require_once(__DIR__  . "/../../header.php"); ?><hr>

        <div class="section">
            <h4 class="text-center" style="margin: 0px;">PERNYATAAN BAHWA DANA YANG DIGUNAKAN SEBAGAI MARGIN MERUPAKAN DANA MILIK NASABAH SENDIRI</h4>
            Yang Mengisi formulir di bawah ini:
            <table class="table no-border" style="margin-top: 20px; font-size: 15px;">
                <tbody>
                    <tr>
                        <td width="30%" class="v-align-top">Nama Lengkap</td>
                        <td width="3%" class="v-align-top">:</td>
                        <td class="v-align-top"><?= App\Models\Account::realAccountDetail(($acc ?? ""))['ACC_FULLNAME'] ?></td>
                    </tr>
                    <tr>
                        <td width="30%" class="v-align-top">Tempat/TanggalLahir</td>
                        <td width="3%" class="v-align-top">:</td>
                        <td class="v-align-top">
                            <?= App\Models\Account::realAccountDetail(($acc ?? ""))['ACC_TEMPAT_LAHIR'] ?>,
                            <?= date("d", strtotime(App\Models\Account::realAccountDetail(($acc ?? ""))['ACC_TANGGAL_LAHIR'])) ?>
                            <?= App\Models\Helper::bulan(date("m", strtotime(App\Models\Account::realAccountDetail(($acc ?? ""))['ACC_TANGGAL_LAHIR']))); ?>
                            <?= date("Y", strtotime(App\Models\Account::realAccountDetail(($acc ?? ""))['ACC_TANGGAL_LAHIR'])) ?>
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" class="v-align-top">Alamat</td>
                        <td width="3%" class="v-align-top">:</td>
                        <td class="v-align-top"><?= App\Models\Account::realAccountDetail(($acc ?? ""))['ACC_ADDRESS'] ?></td>
                    </tr>
                    <tr>
                        <td width="30%" class="v-align-top">Kode Pos</td>
                        <td width="3%" class="v-align-top">:</td>
                        <td class="v-align-top"><?= App\Models\Account::realAccountDetail(($acc ?? ""))['ACC_ZIPCODE'] ?></td>
                    </tr>
                    <tr>
                        <td width="30%" class="v-align-top">No. Identitas</td>
                        <td width="3%" class="v-align-top">:</td>
                        <td class="v-align-top"><?= App\Models\Account::realAccountDetail(($acc ?? ""))['ACC_TYPE_IDT'] ?> / <?= App\Models\Account::realAccountDetail(($acc ?? ""))['ACC_NO_IDT'] ?></td>
                    </tr>
                    <tr>
                        <td width="30%" class="v-align-top">No. Acc</td>
                        <td width="3%" class="v-align-top">:</td>
                        <td class="v-align-top"><?= App\Models\Account::realAccountDetail(($acc ?? ""))['ACC_LOGIN'] ?></td>
                    </tr>
                </tbody>
            </table>
            
            <p class="text-justify">Dengan mengisi kolom “YA” di bawah ini, Bersama ini saya menyatakan bahwa dana yang saya gunakan untuk bertransaksi di <?= $COMPANY_PRF['COMPANY_NAME'] ?> adalah milik saya pribadi dan bukan dana pihak lain, serta tidak diperoleh dari hasil kejahatan, penipuan, penggelapan, tindak pidana korupsi, tindak pidana narkotika, tindak pidana di bidang kehutanan, hasil pencucian uang, dan perbuatan melawan hukum lainnya serta tidak dimaksudkan untuk melakukan pencucian uang dan/atau pendanaan terorisme.</p>

            <p class="text-justify">Demikian surat pernyataan ini saya buat dalam keadaan sadar, sehat jasmani dan rohani serta tanpa paksaan dari pihak manapun.</p>

            <p class="text-justify">Demikian Pernyataan ini dibuat dengan sebenarnya dalam keadaan sadar, sehat jasmani dan rohani serta tanpa paksaan apapun dari pihak manapun.</p>

            <p style="margin: 0px;">Pernyataan menerima/tidak: Ya</p>
            <p style="margin: 0px;">Menerima pada Tanggal: <?= date("Y-m-d H:i", strtotime(App\Models\Account::realAccountDetail(($acc ?? ""))['ACC_F_DANA_DATE'])) ?></p>
        </div>
    </body>
</html>