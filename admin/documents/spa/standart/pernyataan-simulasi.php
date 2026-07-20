<!DOCTYPE html>
<html>
    <head>
        <?php require_once(__DIR__ . "/../../style.php"); ?>
    </head>
    <body>
        <?php require_once(__DIR__  . "/../../header.php"); ?><hr>
        <div class="section">
            <h4 class="text-center" style="margin: 0px;">PERNYATAAN TELAH MELAKUKAN SIMULASI PERDAGANGAN BERJANGKA KOMODITI</h4>
            Yang mengisi formulir di bawah ini:
            <table class="table no-border" style="margin-top: 10px; border:1px solid black;">
                <tr>
                    <td width="30%" class="v-align-middle">Nama Lengkap</td>
                    <td width="1%" class="v-align-middle">:</td>
                    <td class="v-align-middle"><?= $realAccount['ACC_FULLNAME'] ?></td>
                </tr>
                <tr>
                    <td class="v-align-middle">Tempat/Tanggal Lahir</td>
                    <td class="v-align-middle">:</td>
                    <td class="v-align-middle">
                        <?= $realAccount['ACC_TEMPAT_LAHIR'] ?>, 
                        <?= date("d M Y", strtotime($realAccount['ACC_TANGGAL_LAHIR'])) ?>  
                    </td>
                </tr>
                <tr>
                    <td class="v-align-middle">Alamat Rumah</td>
                    <td class="v-align-middle">:</td>
                    <td class="v-align-middle"><?= $realAccount['ACC_ADDRESS'] ?></td>
                </tr>
                <tr>
                    <td class="v-align-middle">No. Identitas</td>
                    <td class="v-align-middle">:</td>
                    <td class="v-align-middle"><?= implode(" / ", [$realAccount['ACC_TYPE_IDT'], $realAccount['ACC_NO_IDT']]) ?></td>
                </tr>
                <tr>
                    <td class="v-align-middle">No. Demo Acc.</td>
                    <td class="v-align-middle">:</td>
                    <td class="v-align-middle"><?= $realAccount['ACC_DEMO'] ?></td>
                </tr>
            </table>

            <p style="margin-bottom: 0px;">Dengan mengisi kolom “YA” di bawah ini, saya menyatakan bahwa saya telah melakukan simulasi bertransaksi di bidang Perdagangan Berjangka Komoditi pada Perusahaan Pialang Berjangka <?= $COMPANY_PRF['COMPANY_NAME'] ?? "-"; ?>, dan telah memahami tentang tata cara bertransaksi di bidang Perdagangan Berjangka Komoditi.</p>
            <p>Demikian Pernyataan ini dibuat dengan sebenarnya dalam keadaan sadar, sehat jasmani dan rohani serta tanpa paksaan apapun dari pihak manapun.</p>

            <p style="margin: 0px;">Pernyataan menerima/tidak: Ya</p>
            <p style="margin: 0px;">Menerima pada Tanggal: <?= date("Y-m-d H:i", strtotime($realAccount['ACC_F_SIMULASI_DATE'])) ?></p>

            <p style="margin-bottom: 0px;">*) Pilih salah satu</p>
        </div>
    </body>
</html>