<!DOCTYPE html>
<html>
    <head>
        <?php require_once(__DIR__  . "/../../style.php"); ?>
    </head>
    <body>
        <?php require_once(__DIR__  . "/../../header.php"); ?><hr>

        <div class="section">
            <h4 class="text-center" style="margin: 0px;">SURAT PERNYATAAN TELAH BERPENGALAMAN MELAKSANAKAN TRANSAKSI PERDAGANGAN BERJANGKA KOMODITI</h4>
            Yang mengisi formulir di bawah ini:
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

            <p style="margin-bottom: 0px;">Dengan mengisi kolom “YA” di bawah ini, saya menyatakan bahwa saya telah memiliki pengalaman yang mencukupi dalam melaksanakan transaksi Perdagangan Berjangka karena pernah bertransaksi pada <?= App\Models\Account::realAccountDetail(($acc ?? ""))['ACC_F_PENGLAMAN_PERSH'] ?>, dan telah memahami tentang tata cara bertransaksi Perdagangan Berjangka. </p>
            <p>Demikian Pernyataan ini dibuat dengan sebenarnya dalam keadaan sadar, sehat jasmani dan rohani serta tanpa paksaan apapun dari pihak manapun.</p>

            <p style="margin: 0px;">Pernyataan menerima/tidak: Ya</p>
            <p style="margin: 0px;">Menerima pada Tanggal: <?= date("Y-m-d H:i", strtotime(App\Models\Account::realAccountDetail(($acc ?? ""))['ACC_F_SIMULASI_DATE'])) ?></p>

            <p style="margin-bottom: 0px;">*) Pilih salah satu</p>
            <p style="margin-bottom: 0px;">**) Isi sesuai dengan nama Pialang Berjangka tempat pernah melakukan transaksi Perdagangan Berjangka sebelumnya</p>
        </div>
    </body>
</html>