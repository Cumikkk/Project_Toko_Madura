<!DOCTYPE html>
<html>
    <head>
        <?php require_once(__DIR__ . "/../style.php"); ?>
    </head>
    <body>
        <div class="header">
            <img style="object-fit: cover; max-height: 100%; max-width: 100%;" src="data:image/png;base64,<?= base64_encode(file_get_contents($logo_pdf)); ?>">
        </div>

        <div class="section">
            <h4 class="text-center" style="margin: 0px;">PERNYATAAN TELAH MELAKUKAN SIMULASI PERDAGANGAN BERJANGKA KOMODITI</h4>
            <table class="table no-border" style="margin-top: 10px;">
                <tbody>
                    <tr>
                        <td colspan="3" class="v-align-middle">Yang mengisi formulir di bawah ini:</td>
                    </tr>
                    <tr>
                        <td width="30%" class="v-align-middle">Nama Lengkap</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_FULLNAME'] ?></td>
                    </tr>
                    <tr>
                        <td width="30%" class="v-align-middle">Tempat/Tanggal Lahir</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle">
                            <?= $realAccount['ACC_TEMPAT_LAHIR'] ?>, 
                            <?= date("d", strtotime($realAccount['ACC_TANGGAL_LAHIR'])) ?> 
                            <?= $tgl_lahir ?>
                            <?= date("Y", strtotime($realAccount['ACC_TANGGAL_LAHIR'])) ?> 
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" class="v-align-middle">Alamat Rumah</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_ADDRESS'] ?></td>
                    </tr>
                    <tr>
                        <td width="30%" class="v-align-middle">No. Identitas</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= implode(" / ", [$realAccount['ACC_TYPE_IDT'], $realAccount['ACC_NO_IDT']]) ?></td>
                    </tr>
                    <tr>
                        <td width="30%" class="v-align-middle">No. Demo Acc.</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_DEMO'] ?></td>
                    </tr>
                </tbody>
            </table>

            <p style="margin-bottom: 0px;">Dengan mengisi kolom “YA” di bawah ini, saya menyatakan bahwa saya telah melakukan simulasi bertransaksi di bidang Perdagangan Berjangka Komoditi pada PT Delapan Belas Berjangka, dan telah memahami tentang tata cara bertransaksi di bidang Perdagangan Berjangka Komoditi.</p>
            <p>Demikian Pernyataan ini dibuat dengan sebenarnya dalam keadaan sadar, sehat jasmani dan rohani serta tanpa paksaan apapun dari pihak manapun.</p>

            <p style="margin: 0px;">Pernyataan menerima/tidak: Ya</p>
            <p style="margin: 0px;">Menerima pada Tanggal (DD/MM/YYYY): <?= date("d/m/Y", strtotime($realAccount['ACC_F_SIMULASI_DATE'])) ?></p>

            <p style="margin-bottom: 0px;">*) Pilih salah satu</p>
        </div>
    </body>
</html>