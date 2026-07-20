<?php

    use App\Factory\FileUploadFactory;
    use App\Models\Account;
    use App\Models\Dpwd;
    use App\Models\Helper;
    use App\Models\FileUpload;
    use App\Models\CompanyProfile;
    use App\Models\ProfilePerusahaan;
    use Config\Core\SystemInfo;

    $realAccount     = Account::realAccountDetail(($acc ?? ""));
    $accnd           = Account::accoundCondition($realAccount['ID_ACC']);
    $COMPANY_PRF     = CompanyProfile::profilePerusahaan();
    $COMPANY_MOF     = CompanyProfile::getMainOffice();
    $company         = CompanyProfile::$name;
    $VERIFICATOR     = ProfilePerusahaan::wpb_verifikator_search_bynme($realAccount["ACC_F_PERJ_WPB"]);
    // print_r($VERIFICATOR);

    $bapakatauibu = (!empty($realAccount['ACC_F_APP_PRIBADI_KELAMIN']) && $realAccount['ACC_F_APP_PRIBADI_KELAMIN'] == "Laki-laki")
    ? 'Bapak/<strike>Ibu</strike>'
    : '<strike>Bapak</strike>/Ibu';

    $dateTime = date("Y-m-d H:i:s", strtotime($realAccount['ACC_WPCHECK_DATE'] ?? $realAccount['ACC_F_CMPLT_DATE'] ?? $realAccount['ACC_F_DISC4_DATE']));
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
            
            .header { 
                display: block;
                /* margin: auto; */
                /* position: fixed;  */
                height: 75px !important; 
                top: -50px !important; 
                text-align: center;
                width: 100vw;
                margin-bottom: 10px !important; 
                /* background-color: purple;  */
            }
        </style>
    </head>
    <body style="font-size: 12px !important;">
        <div style="text-align:center; margin-top: -20px;">
            <img style="object-fit: cover; max-height: 90%; max-width: 40%; margin-bottom: 10px;" src="data:image/png;base64,<?= base64_encode(file_get_contents($logo_pdf)); ?>"></td>
        </div>
        <div class="section">
            <div style="text-align:center;vertical-align: middle;padding: 0 0 10px 0;">
                BUKTI KONFIRMASI PENERIMAAN NASABAH PADA<br>
                <?= strtoupper($COMPANY_PRF['COMPANY_NAME']) ?>
            </div>
            Saya yang bertandatangan dibawah ini:
            <table style="border-spacing: 0px;">
                <tr>
                    <td width="25%" style="vertical-align: top;">Nama</td>
                    <td width="1%" style="vertical-align: top; text-align:center;">&nbsp;:&nbsp;</td>
                    <td><?= $realAccount["ACC_F_PERJ_WPB"] ?></td>
                </tr>
                <tr>
                    <td>Pekerjaan/Jabatan</td>
                    <td width="1%" style="vertical-align: top; text-align:center;">&nbsp;:&nbsp;</td>
                    <td>Wakil Pialang Berjangka</td>
                </tr>
                <tr>
                    <td style="vertical-align: top;">Alamat</td>
                    <td width="1%" style="vertical-align: top; text-align:center;">&nbsp;:&nbsp;</td>
                    <td><?= $COMPANY_MOF["OFC_ADDRESS"] ?></td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align: justify; margin-top: 10px;">
                        Dalam hal ini bertindak untuk dan atas nama <?= strtoupper($COMPANY_PRF['COMPANY_NAME']) ?><br><br>
                        <div style="margin-top:-10px;">Pada hari ini <?= Helper::hari(date('w', strtotime($dateTime))) ?>,  tanggal <?= date('d', strtotime($dateTime)) ?> <?= Helper::bulan(date('m', strtotime($dateTime))) ?> <?= date('Y', strtotime($dateTime)) ?> mengkonfirmasi kepada:</div>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top;">Nama Lengkap</td>
                    <td width="1%" style="vertical-align: top; text-align:center;">&nbsp;:&nbsp;</td>
                    <td><?= $realAccount["ACC_FULLNAME"] ?></td>
                </tr>
                <tr>
                    <td style="vertical-align: top;">Alamat Rumah</td>
                    <td width="1%" style="vertical-align: top; text-align:center;">&nbsp;:&nbsp;</td>
                    <td><?= $realAccount["ACC_ADDRESS"] ?>, <?= $realAccount["MBR_ZIP"] ?></td>
                </tr>
                <tr>
                    <td style="vertical-align: top;"><?= $realAccount["ACC_TYPE_IDT"] ?></td>
                    <td width="1%" style="vertical-align: top; text-align:center;">&nbsp;:&nbsp;</td>
                    <td><?= $realAccount["ACC_NO_IDT"] ?></td>
                </tr>
                <tr>
                    <td style="vertical-align: top;">No. Acc.</td>
                    <td width="1%" style="vertical-align: top; text-align:center;">&nbsp;:&nbsp;</td>
                    <td><?= $accnd['ACCCND_LOGIN'] ?? 0 ?></td>
                </tr>
            </table>
            <p style="text-align: justify;">
                Bahwa <?= $bapakatauibu ?> <?= $realAccount["MBR_NAME"] ?> telah resmi menjadi Nasabah <?= strtoupper($COMPANY_PRF['COMPANY_NAME']) ?> sejak tanggal <?= date('d', strtotime($dateTime)) ?> <?= Helper::bulan(date('m', strtotime($dateTime))) ?>  <?= date('Y', strtotime($dateTime)) ?> dengan nomor account new account berdasarkan Perjanjian Pemberian Amanat yang <?= $bapakatauibu ?> <?= $realAccount["MBR_NAME"] ?> telah isi dan
                setujui berdasarkan ketentuan Peraturan Kepala Bappebti Nomor 99/BAPPEBTI/PER/11/2012 Tentang Penerimaan Nasabah Secara Elektronik Online Di Bidang Perdagangan Berjangka Komoditi sebagaimana telah diubah dengan
                Peraturan Kepala Bappebti Nomor 107/BAPPEBTI/PER/11/2013, serta telah mengisi dan menyetujui dokumen sebagai berikut:
            </p>
            <ol style="text-align: justify;margin-top:-10px;">
                <li>
                    Pernyataan Telah Melakukan Simulasi Perdagangan Berjangka atau Pernyataan Telah
                    Berpengalaman Dalam Melaksanakan Transaksi Perdagangan Berjangka;
                </li>
                <li>Profil Nasabah dan aplikasi pembukaan rekening;</li>
                <li>Dokumen Pemberitahuan Adanya Resiko;</li>
                <li>Perjanjian Pemberian Amanat;</li>
                <li>Peraturan Perdagangan (trading rules); dan</li>
                <li>Pernyataan Dari Nasabah Untuk Tidak Menyerahkan Kode Akses Transaksi Nasabah (Personal Access Password) Ke Pihak Lain.</li>
            </ol>
            <p style="text-align: justify;margin-top: -10px;margin-bottom: -10px;">
                Dengan membaca, mengisi dan menyetujui dokumen sebagaimana dimaksud di atas, dengan demikian <?= $bapakatauibu ?> <?= $realAccount["MBR_NAME"] ?>:
            </p>
            <ol class="style1" style="text-align: justify;">
                <li>memahami dan mengerti resiko-resiko yang ada, termasuk kerugian atas seluruh dana yang Disetor;</li>
                <li>memahami kewajiban dan hak selaku Nasabah Pialang Berjangka;</li>
                <li>memahami dan mengerti mekanisme dan dan cara Perdagangan Berjangka;</li>
                <li>
                    memahami untuk tidak membuat perjanjian dalam bentuk apapun baik secara lisan maupun tertulis dengan pegawai Pialang Berjangka atau pihak yang memiliki kepentingan dengan Pialang Berjangka diluar Perjanjian
                    Perdagangan Berjangka dan peraturan perdagangan (trading rules) antara Nasabah dengan <?= strtoupper($COMPANY_PRF['COMPANY_NAME']) ?>;
                </li>
                <li>
                    memahami untuk bertanggungjawab sepenuhnya terhadap nama pengguna (user id) dan kode akses transaksi Nasabah (Personal Access Password), dan tidak menyerahkan nama pengguna (user id) dan kode akses transaksi
                    Nasabah (Personal Access Password) ke pihak lain, terutama kepada pegawai Pialang Berjangka atau pihak yang memiliki kepentingan Pialang Berjangka;
                </li>
                <li>melakukan simulasi atau mengerti mekanisme transaksi Perdagangan Berjangka;</li>
                <li>memahami mengenai peraturan perdagangan (trading rules) antara Nasabah dengan <?= strtoupper($COMPANY_PRF['COMPANY_NAME']) ?>;</li>
                <li>
                    memahami tentang mekanisme penggunaan Rekening Terpisah (segregated account), termasuk penyetoran dan penarikan dana, yakni akun keluar masuk dana wajib sama dengan akun yang didaftarkan dalam aplikasi
                    pembukaan rekening, dan pelaksanaannya wajib dilakukan melalui pindah buku/transfer, serta prosedur penarikan dana; dan
                </li>
                <li>
                    memahami dana yang dipergunakan dalam bertransaksi adalah dana milik pribadi, bukan
                    dari dan/atau milik pihak lain, atau berasal dari pencucian uang.
                </li>
            </ol>
            <p style="text-align: justify;margin-top: -10px;margin-bottom: -10px;">
                Data yang kami terima dari <?= $bapakatauibu ?> <?= $realAccount["MBR_NAME"] ?> akan kami rekam dan catat, dan sepenuhnya menjadi milik <?= strtoupper($COMPANY_PRF['COMPANY_NAME']) ?>. Kami bertanggung jawab untuk menjaga
                kerahasiaan data dan informasi <?= $bapakatauibu ?> <?= $realAccount["MBR_NAME"] ?> sesuai dengan peraturan perundang-Undangan.
            </p>
            <table width="90%" align="center">
                <tr align="left">
                    <td width="50%">
                        <p class="style1">
                            Verifikator<br />
                            Wakil Pialang Berjangka
                        </p>
                    </td>
                    <td width="50%">
                        <span class="style1">
                            Mengetahui<br />
                            Direktur Utama <?= strtoupper($COMPANY_PRF['COMPANY_NAME']) ?>
                        </span>
                    </td>
                </tr>
                <tr align="left">
                    <td>
                        <!-- <img src="data:image/png;base64,'.base64_encode(req_image($test = $awsUrl.'/'."ttdcap_fz2.png")).'" width="40%"> -->
                        <?php if(count($VERIFICATOR)){ ?>
                            <img src="data:image/png;base64,<?= base64_encode(file_get_contents(FileUploadFactory::aws()->awsFile($VERIFICATOR["WPB_TANDA_TANGAN"]))); ?>" height="63px">
                        <?php } ?>
                    </td>
                    <td>
                        <img src="data:image/png;base64,<?= base64_encode(file_get_contents(SystemInfo::app('CLIENT_URL') . '/assets/images/stamp-rrfx2.png')); ?>" width="35%">
                    </td>
                </tr>
                <tr align="left">
                    <td width="50%"><span class="style1"></span> ( <?= $realAccount["ACC_F_PERJ_WPB"] ?> )</td>
                    <td width="50%"><span class="style1">( <?= $COMPANY_PRF["PROF_DEWAN_DIREKSI"] ?> )</span></td>
                </tr>
            </table>
            <!-- <div style="text-align:center;margin-top:25px;margin-left:25%">
                <table>
                    <tr>
                        <td>Menyatakan pada tanggal</td>
                        <td style="vertical-align: top;"><div style="margin:0px 5px;">:</div></td>
                        <td><strong><?= date('Y-m-d H:i:s', strtotime($dateTime)) ?></strong></td>
                    </tr>
                </table>
            </div> -->
        </div>
    </body>
</html>