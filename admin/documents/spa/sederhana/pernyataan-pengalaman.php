<!DOCTYPE html>
<html>
    <head>
        <?php require_once(__DIR__  . "/../../style.php"); ?>
    </head>
    <body>
        <?php require_once(__DIR__  . "/../../header.php"); ?><hr>

        <div class="section">
            FORMULIR PBK. CDDS. 02.2<br><br>
            <p class="text-center" style="padding: 2px;">SURAT PERNYATAAN TELAH BERPENGALAMAN<br>MELAKSANAKAN TRANSAKSI PERDAGANGAN BERJANGKA KOMODITI</p><br>
            <p style="text-align:center;">Yang mengisi formulir di bawah ini:</p>
            <table class="table no-border" style="margin-top: 10px;">
                <tbody>
                    <tr>
                        <td width="30%" class="v-align-middle">Nama Lengkap</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= App\Models\Account::realAccountDetail(($acc ?? ""))['ACC_FULLNAME'] ?></td>
                    </tr>
                    <tr>
                        <td width="30%" class="v-align-middle">Tempat/Tanggal Lahir</td>
                        <td class="v-align-middle">:</td>
                        <td class="v-align-middle">
                            <?= App\Models\Account::realAccountDetail(($acc ?? ""))['ACC_TEMPAT_LAHIR'] ?>, 
                            <?= date("d M Y", strtotime(App\Models\Account::realAccountDetail(($acc ?? ""))['ACC_TANGGAL_LAHIR'])) ?>  
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" class="v-align-middle">Alamat Rumah</td>
                        <td class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= App\Models\Account::realAccountDetail(($acc ?? ""))['ACC_ADDRESS'] ?></td>
                    </tr>
                    <tr>
                        <td width="30%" class="v-align-middle">No. Identitas</td>
                        <td class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= implode(" / ", [App\Models\Account::realAccountDetail(($acc ?? ""))['ACC_TYPE_IDT'], App\Models\Account::realAccountDetail(($acc ?? ""))['ACC_NO_IDT']]) ?></td>
                    </tr>
                    <tr>
                        <td width="30%" class="v-align-middle">No. Demo Acc.</td>
                        <td class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= App\Models\Account::realAccountDetail(($acc ?? ""))['ACC_DEMO'] ?></td>
                    </tr>
                </tbody>
            </table>

            <p style="margin-bottom: 0px;text-align: justify;">Dengan mengisi kolom “YA” di bawah ini, saya menyatakan bahwa saya
            telah memiliki pengalaman yang mencukupi dalam melaksanakan transaksi
            Perdagangan Berjangka karena pernah bertransaksi pada Perusahaan
            Pialang Berjangka <?= App\Models\Account::realAccountDetail(($acc ?? ""))['ACC_F_PENGLAMAN_PERSH'] ?>, dan telah memahami tentang tata cara bertransaksi
            Perdagangan Berjangka. </p>
            <p>Demikian Pernyataan ini dibuat dengan sebenarnya dalam keadaan sadar,
            sehat jasmani dan rohani serta tanpa paksaan apapun dari pihak manapun.</p>
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
                    <td><strong><?= date('d-m-Y H:i', strtotime(App\Models\Account::realAccountDetail(($acc ?? ""))["ACC_F_PENGLAMAN_DATE"])) ?></strong></td>
                </tr>
            </table>

            <p style="margin-bottom: 0px;">*) Pilih salah satu</p>
            <p style="margin-bottom: 0px;">**) Isi sesuai dengan nama Pialang Berjangka tempat pernah melakukan transaksi Perdagangan Berjangka sebelumnya</p>
        </div>
    </body>
</html>