<!DOCTYPE html>
<html>
    <head>
        <?php require_once(__DIR__  . "/../style.php"); ?>
    </head>
    <body>
        <?php require_once(__DIR__  . "/../header.php"); ?><hr>
        <div class="row" style="font-size: 14px;">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center" style="margin-bottom:10px;"><strong>PROFIL PERUSAHAAN PIALANG BERJANGKA</strong></div>
                        <div class="table-responsive">
                            <table class="table table-fixed table-hover" style="table-layout: auto">
                                <tr>
                                    <td width="1%" style="white-space: nowrap;vertical-align:top;">Nama</td>
                                    <td width="1%" style="border-right:none;vertical-align:top;">:</td>
                                    <td style="border-left:none;vertical-align:top;"><?= $profile['COMPANY_NAME'] ?? "-"; ?></td>
                                </tr>
                                <tr>
                                    <td width="1%" style="white-space: nowrap;vertical-align:top;">Alamat</td>
                                    <td width="1%" style="border-right:none;vertical-align:top;">:</td>
                                    <td style="border-left:none;vertical-align:top;"><?= $profile['OFFICE']['OFC_ADDRESS'] ?? "-"; ?></td>
                                </tr>
                                <tr>
                                    <td width="1%" style="white-space: nowrap;vertical-align:top;">No. Telepon</td>
                                    <td width="1%" style="border-right:none;vertical-align:top;">:</td>
                                    <td style="border-left:none;vertical-align:top;"><?= $profile['OFFICE']['OFC_PHONE'] ?? "-"; ?></td>
                                </tr>
                                <tr>
                                    <td width="1%" style="white-space: nowrap;vertical-align:top;">Faksimili</td>
                                    <td width="1%" style="border-right:none;vertical-align:top;">:</td>
                                    <td style="border-left:none;vertical-align:top;"><?= $profile['OFFICE']['OFC_FAX'] ?? "-"; ?></td>
                                </tr>
                                <tr>
                                    <td width="1%" style="white-space: nowrap;vertical-align:top;">E-mail</td>
                                    <td width="1%" style="border-right:none;vertical-align:top;">:</td>
                                    <td style="border-left:none;vertical-align:top;"><?= $profile['OFFICE']['OFC_EMAIL'] ?? "-"; ?></td>
                                </tr>
                                <tr>
                                    <td width="1%" style="white-space: nowrap;vertical-align:top;">Home-page</td>
                                    <td width="1%" style="border-right:none;vertical-align:top;">:</td>
                                    <td style="border-left:none;vertical-align:top;"><?= $profile['PROF_HOMEPAGE'] ?? "-"; ?></td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        Susunan Pengurus Perusahaan:<br>
                                        <ol style="margin-left:-20px;margin-top:0px;margin-bottom:0px;">
                                            <li>Komisaris Utama : <?= $profile['PROF_KOMISARIS_UTAMA'] ?></li>
                                            <li>Komisaris : <?= $profile['PROF_KOMISARIS'] ?></li>
                                            <li>Direktur Utama : <?= $profile['PROF_DEWAN_DIREKSI'] ?></li>
                                            <li>Direktur Kepatuhan : <?= $profile['PROF_DIREKTUR'] ?></li>
                                            <li>Direktur Operasional : <?= $profile['PROF_OPERATIONAL'] ?></li>
                                        </ol>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        Susunan Pemegang Saham Perusahaan:<br>
                                        <ol style="margin-left:-20px;margin-top:0px;margin-bottom:0px;">
                                            <?php foreach(explode(",", $profile['PROF_PEMEGANG_SAHAM']) as $key =>  $pemegangSaham) : ?>
                                                <li><?= $pemegangSaham ?></li>
                                            <?php endforeach; ?>
                                        </ol>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        Nomor dan Tanggal Izin Usaha dari Bappebti:<br>
                                        <?= $profile['PROF_NO_IZIN_USAHA'] ?> Tanggal: <?= date('d M Y', strtotime($profile['PROF_TGL_IZIN_USAHA'])) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        Nomor dan Tanggal Keanggotaan Bursa Berjangka:<br>
                                        <?= $profile['PROF_NO_KEANGGOTAAN_BURSA'] ?> Tanggal: <?= date('d M Y', strtotime($profile['PROF_TGL_KEANGGOTAAN_BURSA'])) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        Nomor dan Tanggal Keanggotaan Lembaga Kliring Berjangka:<br>
                                        <?= $profile['PROF_NO_KEANGGOTAAN_LEMBAGA'] ?> Tanggal: <?= date('d M Y', strtotime($profile['PROF_TGL_KEANGGOTAAN_LEMBAGA'])) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        Nomor dan Tanggal Persetujuan sebagai Peserta Sistem Perdagangan Alternatif:<br>
                                        <?= $profile['PROF_NO_PERSETUJUAN_PESERTA'] ?> Tanggal: <?= date('d M Y', strtotime($profile['PROF_TGL_PERSETUJUAN_PESERTA'])) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        Nama Penyelenggara Sistem Perdagangan Alternatif<br>
                                        <?= $profile['FOREX_SYS'] ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        Kontrak Berjangka Yang Diperdagangkan*):<br>
                                        <ol style="margin-left:-20px;margin-top:0px;margin-bottom:0px;">
                                            <li>Kontrak berjangka Emas ( GOL, GOL 250, GOL 100 )</li>
                                            <li>Kontrak berjangka Kopi ( ACF, RCF )</li>
                                            <li>Kontrak Berjangka Olein ( OLE, OLE 10 )</li>
                                            <li>Kontrak Berjangka Indeks emas ( KBIE )</li>
                                            <li>Kontrak Berjangka Coklat ( CC5 )</li>
                                        </ol>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        Kontrak Derivatif Syariah Yang Diperdagangkan*):<br>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        Kontrak Derivatif dalam Sistem Perdagangan Alternatif (SPA):<br>
                                        Kontrak CFD Mata Uang Asing (FOREX) dan Loco Emas (XAU) , Silver (XAG) , Oil (CLSK),
                                        Indeks Saham Jepang, Indeks Saham Hongkong, NAS100, DOW, SPX500
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        Kontrak Derivatif dalam Sistem Perdagangan Alternatif dengan volume minimum 0,1 (nol koma satu) lot Yang Diperdagangkan*):<br>
                                        Kontrak CFD Mata Uang Asing (FOREX) dan Loco Emas (XAU) , Silver (XAG) , Oil (CLSK)
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        Biaya secara rinci yang dibebankan kepada Nasabah:<br>
                                        Berdasarkan Jenis produk Komisi $50/lot settled / Interest / Swap / Rollover fee
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        Nomor atau alamat email jika terjadi keluhan:<br>
                                        Email : <?= $profile['PROF_EML_PENGADUAN'] ?><br>
                                        No. Telepon : <?= $profile['PROF_PHONE_PENGADUAN'] ?><br>
                                        Faks : <?= $profile['PROF_FAX_PENGADUAN'] ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        Sarana penyelesaian perselisihan yang dipergunakan apabila terjadi perselisihan:<br>
                                        <ol style="margin-left:-20px;margin-top:0px;margin-bottom:0px;">
                                            <li>Secara musyawarah untuk mencapai mufakat antara Para Pihak;</li>
                                            <li>Memanfaatkan sarana penyelesaian perselisihan yang tersedia di Bursa Berjangka (JFX);</li>
                                            <li>Badan Arbitrase Perdagangan Berjangka Komoditi (BAKTI) atau Pengadilan Negeri</li>
                                        </ol>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        Nama-Nama Wakil Pialang Berjangka yang bekerja di Perusahaan Pialang Berjangka:<br>
                                        <table width="75%" style="border: none; border-collapse: collapse;">
                                            <?php 
                                                foreach($list_wpb_satu as $wpb){
                                                    foreach($wpb as $w){
                                            ?>
                                            <tr>
                                                <td width="50%" style="border: none;"><?= $w['WPB_NAMA'] ?></td>
                                                <td width="50%" style="border: none;"><?= $w['WPB_NO1'] ?></td>
                                            </tr>
                                            <?php };}; ?>
                                        </table>
                                        <!-- <ol style="margin-left:-20px;margin-top:0px;margin-bottom:0px;">
                                            <?php 
                                                foreach($list_wpb_satu as $wpb){
                                                    foreach($wpb as $w){
                                                        echo "<li>".$w['WPB_NAMA']."</li>";
                                                    }; 
                                                };
                                            ?>
                                        </ol> -->
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        Nama-Nama Wakil Pialang Berjangka yang secara khusus ditunjuk oleh Pialang Berjangka untuk melakukan verifikasi dalam rangka penerimaan Nasabah elektronik on- line:<br>
                                        <table width="75%" style="border: none; border-collapse: collapse;">
                                            <?php 
                                                foreach($list_wpb_dua as $wpb){
                                                    foreach($wpb as $w){
                                            ?>
                                            <tr>
                                                <td width="50%" style="border: none;"><?= $w['WPB_NAMA'] ?></td>
                                                <td width="50%" style="border: none;"><?= $w['WPB_NO1'] ?><br><?= $w['WPB_NO2'] ?></td>
                                            </tr>
                                            <?php };}; ?>
                                        </table>
                                        <!-- <ol style="margin-left:-20px;margin-top:0px;margin-bottom:0px;">
                                            <?php 
                                                foreach($list_wpb_dua as $wpb){
                                                    foreach($wpb as $w){
                                                        echo "<li>".$w['WPB_NAMA']."</li>";
                                                    }; 
                                                };
                                            ?>
                                        </ol> -->
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        Nomor Rekening Terpisah (Segregated Account) Perusahaan Pialang Berjangka:<br>
                                        <table>
                                        <?php $sql_get_bankadm = $db->query("SELECT * FROM tb_bankadm"); ?>
                                        <?php if($sql_get_bankadm) : ?>
                                            <?php foreach($sql_get_bankadm->fetch_all(MYSQLI_ASSOC) as $key => $bkadm) : ?>
                                                    
                                                    <!-- <?= $bkadm['BKADM_ACCOUNT'] ?> <?= $bkadm['BKADM_NAME'] ?> (<?= $bkadm['BKADM_CURR'] ?>)<br> -->
                                                    <tr>
                                                        <td width="50%" style="border: none;"><?= $bkadm['BKADM_NAME'] ?></td>
                                                        <td width="50%" style="border: none;"><?= $bkadm['BKADM_CURR'] ?></td>
                                                        <td width="50%" style="border: none;"><?= $bkadm['BKADM_ACCOUNT'] ?></td>
                                                    </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <p style="text-align: center;">PERNYATAAN TELAH MEMBACA PROFIL PERUSAHAAN PIALANG BERJANGKA</p>
                        <p style="text-align: justify;">
                            Dengan mengisi kolom “YA” di bawah ini, saya menyatakan bahwa saya
                            telah membaca dan menerima informasi PROFIL PERUSAHAAN PIALANG 
                            BERJANGKA, mengerti dan memahami isinya.
                        </p>
                        <table>
                            <tr>
                                <td>Pernyataan menerima/Tidak </td>
                                <td style="vertical-align: top;"><div style="margin:0px 5px;">:</div></td>
                                <td><strong>YA</strong></td>
                            </tr>
                            <tr>
                                <td>Pernyataan pada Tanggal</td>
                                <td style="vertical-align: top;"><div style="margin:0px 5px;">:</div></td>
                                <td><strong><?= date('d-m-Y H:i', strtotime($realAccount["ACC_F_PROFILE_DATE"])) ?></strong></td>
                            </tr>
                        
                        </table>

                        <p>*) Isi sesuai dengan permohonan Pialang Berjangka</p>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>