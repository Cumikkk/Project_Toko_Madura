<?php
use App\Models\Account;
use App\Models\Dpwd;
use App\Models\Helper;
use App\Models\FileUpload;
use App\Models\CompanyProfile;
$realAccount     = Account::realAccountDetail(($acc ?? ""));
$accnd           = Account::accoundCondition($realAccount['ID_ACC']);
$depositData     = Dpwd::findByRaccId($realAccount["ID_ACC"]);
$COMPANY_PRF     = CompanyProfile::profilePerusahaan();
$COMPANY_MOF     = CompanyProfile::getMainOffice();
$company         = CompanyProfile::$name;
$bank            = explode("/", $depositData['DPWD_BANKSRC']);
$bankName        = $bank[0] ?? "-";
$bankAccount     = $bank[1] ?? "-";
$bankHolder      = $bank[2] ?? "-";

$bapakatauibu = (!empty($realAccount['ACC_F_APP_PRIBADI_KELAMIN']) && $realAccount['ACC_F_APP_PRIBADI_KELAMIN'] == "Laki-laki")
    ? 'Bapak/<strike>Ibu</strike>'
    : '<strike>Bapak</strike>/Ibu';
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
            <hr>
            <div style="text-align:center;"><h4>PERNYATAAN PENGUNGKAPAN<br><i>(DISCLOSURE STATEMENT)</i></h4></div>
            <ol>
                <li>Perdagangan Berjangka BERISIKO SANGAT TINGGI tidak cocok untuk semua orang. Pastikan bahwa anda SEPENUHNYA MEMAHAMI RISIKO ini sebelum melakukan perdagangan.</li>
                <li>Perdagangan Berjangka merupakan produk keuangan dengan leverage dan dapat menyebabkan KERUGIAN ANDA MELEBIHI setoran awal Anda. Anda harus siap apabila SELURUH DANA ANDA HABIS.</li>
                <li>TIDAK ADA PENDAPATAN TETAP (FIXED INCOME) dalam Perdagangan Berjangka.</li>
                <li>Apabila anda PEMULA kami sarankan untuk mempelajari mekanisme transaksinya, PERDAGANGAN BERJANGKA membutuhkan pengetahuan dan pemahaman khusus.</li>
                <li>ANDA HARUS MELAKUKAN TRANSAKSI SENDIRI, segala risiko yang akan timbul akibat transaksi sepenuhnya akan menjadi tanggung jawab Saudara.</li>
                <li>User id dan password BERSIFAT PRIBADI DAN RAHASIA, anda bertanggung jawab atas penggunaannya, JANGAN SERAHKAN ke pihak lain terutama Wakil Pialang Berjangka dan pegawai Pialang Berjangka.</li>
                <li>ANDA berhak menerima LAPORAN ATAS TRANSAKSI yang anda lakukan. Waktu anda 2 X 24 JAM UNTUK MEMBERIKAN SANGGAHAN. Untuk transaksi yang TELAH SELESAI (DONE/SETTLE) DAPAT ANDA CEK melalui system informasi transaksi nasabah yang berfungsi untuk memastikan transaksi anda telah terdaftar di Lembaga Kliring Berjangka.</li>
            </ol>
            <div style="text-align:center;">SECARA DETAIL BACA DOKUMEN PEMBERITAHUAN ADANYA RESIKO DAN DOKUMEN PERJANJIAN PEMBERIAN AMANAT</div>
            <div style="text-align:center;margin-top:25px;margin-left:25%">
                <table>
                    <tr>
                        <td>Pernyataan Menerima</td>
                        <td style="vertical-align: top;"><div style="margin:0px 5px;">:</div></td>
                        <td><strong>YA</strong></td>
                    </tr>
                    <tr>
                        <td>Menyatakan pada tanggal</td>
                        <td style="vertical-align: top;"><div style="margin:0px 5px;">:</div></td>
                        <td><strong><?= date('Y-m-d H:i:s', strtotime($realAccount["ACC_F_DISC_DATE"])) ?></strong></td>
                    </tr>
                    <tr>
                        <td>IP Address</td>
                        <td style="vertical-align: top;"><div style="margin:0px 5px;">:</div></td>
                        <td><strong><?= $realAccount["ACC_F_DISC_IP"] ?></strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </body>
</html>