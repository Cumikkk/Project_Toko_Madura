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
            FORMULIR PBK. CDDS. 03<br><br>
            <div style="text-align:center;">PERNYATAAN PENGUNGKAPAN<br><i>(DISCLOSURE STATEMENT)</i></div>
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
                        <td><strong><?= date('d-m-Y H:i', strtotime(App\Models\Account::realAccountDetail(($acc ?? ""))["ACC_F_DISC_DATE"])) ?></strong></td>
                    </tr>
                    <!-- <tr>
                        <td>IP Address</td>
                        <td style="vertical-align: top;"><div style="margin:0px 5px;">:</div></td>
                        <td><strong><?= App\Models\Account::realAccountDetail(($acc ?? ""))["ACC_F_DISC_IP"] ?></strong></td>
                    </tr> -->
                </table>
            </div>
        </div>
        <div style="page-break-before: always;">
            <div class="section">
                FORMULIR PBK. CDDS. 03<br><br>
                <div style="text-align:center;">PERNYATAAN PENGUNGKAPAN<br><i>(DISCLOSURE STATEMENT)</i></div>
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
                            <td><strong><?= date('d-m-Y H:i', strtotime(App\Models\Account::realAccountDetail(($acc ?? ""))["ACC_F_DISC_DATE2"])) ?></strong></td>
                        </tr>
                        <!-- <tr>
                            <td>IP Address</td>
                            <td style="vertical-align: top;"><div style="margin:0px 5px;">:</div></td>
                            <td><strong><?= App\Models\Account::realAccountDetail(($acc ?? ""))["ACC_F_DISC_IP"] ?></strong></td>
                        </tr> -->
                    </table>
                </div>
            </div>
        </div>
        <div style="page-break-before: always;">
            <div class="section">
                FORMULIR PBK. CDDS. 03<br><br>
                <div style="text-align:center;">PERNYATAAN PENGUNGKAPAN<br><i>(DISCLOSURE STATEMENT)</i></div>
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
                            <td><strong><?= date('d-m-Y H:i', strtotime(App\Models\Account::realAccountDetail(($acc ?? ""))["ACC_F_DISC_DATE3"])) ?></strong></td>
                        </tr>
                        <!-- <tr>
                            <td>IP Address</td>
                            <td style="vertical-align: top;"><div style="margin:0px 5px;">:</div></td>
                            <td><strong><?= App\Models\Account::realAccountDetail(($acc ?? ""))["ACC_F_DISC_IP"] ?></strong></td>
                        </tr> -->
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>