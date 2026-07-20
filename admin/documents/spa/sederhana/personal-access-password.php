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
            Formulir PBK. CDDS. 08<br><br>
            <h4 class="text-center" style="margin: 0px;">PERNYATAAN BERTANGGUNG JAWAB ATAS<br>
KODE AKSES TRANSAKSI NASABAH (<i>Personal Access Password</i>)</h4>
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
                            <?= App\Models\Helper::bulan(App\Models\Account::realAccountDetail(($acc ?? ""))['ACC_TANGGAL_LAHIR']) ?>
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
                        <td width="30%" class="v-align-top">Nomor Account</td>
                        <td width="3%" class="v-align-top">:</td>
                        <td class="v-align-top"><?= App\Models\Account::realAccountDetail(($acc ?? ""))['ACC_LOGIN'] ?></td>
                    </tr>
                </tbody>
            </table>
            
            <p class="text-justify">Dengan mengisi kolom “YA” di bawah ini, saya menyatakan bahwa saya
            bertanggungjawab sepenuhnya terhadap kode akses transaksi Nasabah
            (<i>Personal Access Password</i>) dan tidak menyerahkan kode akses transaksi
            Nasabah (<i>Personal Access Password</i>) ke pihak lain, terutama kepada
            pegawai Pialang Berjangka atau pihak yang memiliki kepentingan dengan
            Pialang Berjangka.</p>

            <div style="border: 1px solid black; padding: 5px;">
                <p style="text-align: center;"><strong>PERINGATAN !!!<br>
                Pialang Berjangka, Wakil Pialang Berjangka, pegawai Pialang Berjangka, atau pihak<br>
                yang memiliki kepentingan dengan dengan Pialang Berjangka dilarang menerima kode<br>
                akses transaksi Nasabah (<i>Personal Access Password</i>).</strong></p>
            </div>

            <p class="text-justify">Demikian Pernyataan ini dibuat dengan sebenarnya dalam keadaan sadar, sehat jasmani dan rohani serta tanpa paksaan apapun dari pihak manapun.</p>

            <table>
                <tr>
                    <td>Pernyataan menerima/tidak </td>
                    <td style="vertical-align: top;">
                        <div style="margin:0px 5px;">:</div>
                    </td>
                    <td><strong>YA</strong></td>
                </tr>
                <tr>
                    <td>Pernyataan pada Tanggal</td>
                    <td style="vertical-align: top;">
                        <div style="margin:0px 5px;">:</div>
                    </td>
                    <td><strong><?= date('d-m-Y H:i', strtotime(App\Models\Account::realAccountDetail(($acc ?? ""))["ACC_F_KODE_DATE"])) ?></strong></td>
                </tr>
            </table>
        </div>
    </body>
</html>