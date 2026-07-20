<?php

use App\Models\Helper;
use App\Models\ProfilePerusahaan;

$profile = ProfilePerusahaan::get();
?>
<div class="row">

</div>
<div class="row">
    <div class="col-sm-9 mx-auto">
        <form method="post" id="form-perjanjian-amanat">
            <input type="hidden" name="csrf_token" value="<?= uniqid(); ?>">
            <div class="panel">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center" style="vertical-align: middle;padding: 20px 0 10px 0;" id="permanat-head">
                            <h6 class="text-center">
                                <u>PERJANJIAN PEMBERIAN AMANAT SECARA ELEKTRONIK ONLINE<br>UNTUK TRANSAKSI KONTRAK DERIVATIF<br>DALAM SISTEM PERDAGANGAN ALTERNATIF</u>
                            </h6>
                        </div>
                        <hr>
                        <div class="border border-3 text-center">
                            <h6 class="mt-1">PERHATIAN !</h6>
                            <p>PERJANJIAN INI MERUPAKAN KONTRAK HUKUM. HARAP DIBACA DENGAN SEKSAMA</p>
                        </div>
    
                        <p class="mt-3">Pada hari ini <b><?= Helper::hari( date("w") ) ?? "-" ?></b>, tanggal <b><?= date("d") ?></b>, bulan <b><?= Helper::bulan(date("n")) ?? "-" ?></b>, tahun <b><?= date("Y") ?></b>, kami yang mengisi perjanjian di bawah ini:</p>
                        <!-- <div class="table-responsive">
                            <table class="table table-hover" style="text-align: left; table-layout: fixed; word-break: break;" width="100%">
                                <tbody>
                                    <tr>
                                        <td width="2%" rowspan="3" class="top-align fw-bold">1.</td>
                                        <td width="15%" class="top-align fw-bold text-start">Nama</td>
                                        <td width="3%" class="top-align"> : </td>
                                        <td class="top-align text-start"><?= $realAccount['ACC_FULLNAME']; ?></td>
                                    </tr>
                                    <tr>
                                        <td width="15%" class="top-align fw-bold text-start">Pekerjaan / Jabatan</td>
                                        <td width="3%" class="top-align"> : </td>
                                        <td class="top-align text-start"><?= $realAccount['ACC_F_APP_KRJ_TYPE']; ?> / <?= $realAccount['ACC_F_APP_KRJ_JBTN'] ?></td>
                                    </tr>
                                    <tr>
                                        <td width="15%" class="top-align fw-bold text-start">Alamat</td>
                                        <td width="3%" class="top-align"> : </td>
                                        <td class="top-align text-start"><?= $realAccount['ACC_ADDRESS']; ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div> -->
                        <div>
                            <div><span class="top-align fw-bold">1.</span> <span class="top-align fw-bold text-start">Nama</span> : <?= $realAccount['ACC_FULLNAME']; ?></div>
                            <div> <span class="top-align fw-bold text-start">Pekerjaan / Jabatan</span> : <?= $realAccount['ACC_F_APP_KRJ_TYPE']; ?> / <?= $realAccount['ACC_F_APP_KRJ_JBTN'] ?></div>
                            <div> <span class="top-align fw-bold text-start">Alamat</span> : <?= $realAccount['ACC_ADDRESS']; ?></div>
                        </div>
    
                        <p class="mt-3">dalam hal ini bertindak untuk dan atas nama sendiri, yang selanjutnya di sebut Nasabah,</p>
                        <!-- <div class="table-responsive">
                            <table class="table table-hover" style="text-align: left; table-layout: fixed;" width="100%">
                                <tbody>
                                    <tr>
                                        <td width="2%" rowspan="3" class="top-align fw-bold">2.</td>
                                        <td width="15%" class="top-align fw-bold text-start">Nama</td>
                                        <td width="3%" class="top-align"> : </td>
                                        <td class="top-align text-start"><?= ProfilePerusahaan::wpb_verifikator()['WPB_NAMA'] ?? "-" ?></td>
                                    </tr>
                                    <tr>
                                        <td width="15%" class="top-align fw-bold text-start">Pekerjaan / Jabatan</td>
                                        <td width="3%" class="top-align"> : </td>
                                        <td class="top-align text-start">(Petugas Wakil Pialang yang Ditunjuk Memverifikasi)</td>
                                    </tr>
                                    <tr>
                                        <td width="15%" class="top-align fw-bold text-start">Alamat</td>
                                        <td width="3%" class="top-align"> : </td>
                                        <td class="top-align text-start"><?= $profile['OFFICE']["OFC_ADDRESS"] ?? "" ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div> -->
                        <div>
                            <div><span class="top-align fw-bold">2.</span> <span class="top-align fw-bold text-start">Nama</span> : <?= ProfilePerusahaan::wpb_verifikator()['WPB_NAMA'] ?? "-" ?></div>
                            <div> <span class="top-align fw-bold text-start">Pekerjaan / Jabatan</span> : (Petugas Wakil Pialang yang Ditunjuk Memverifikasi)</div>
                            <div> <span class="top-align fw-bold text-start">Alamat</span> : <?= $profile['OFFICE']["OFC_ADDRESS"] ?? "" ?></div>
                        </div>
    
                        <p class="mt-3 text-justify">dalam hal ini bertindak untuk dan atas nama <strong><?= $profile['COMPANY_NAME'] ?></strong> yang selanjutnya disebut <strong>Pialang Berjangka</strong>,</p>
                        <p class="text-justify">Nasabah dan Pialang Berjangka secara bersama - sama selanjutnya disebut <strong>Para Pihak</strong>.</p>
                        <p class="text-justify">Para pihak sepakat untuk mengadakan Perjanjian Pemberian Amanat untuk melakukan
                            transaksi penjualan maupun pembelian Kontrak Derivatif Dalam Sistem Perdagangan Alternatif dengan ketentuan sebagai berikut:</p>
    
                        <ol>
                            <li class="mb-4">
                                <strong>Margin dan Pembayaran Lainnya</strong>
                                <ol>
                                    <li><strong>Nasabah menempatkan sejumlah dana</strong> (Margin) ke Rekening Terpisah (Segregated Account) Pialang Berjangka sebagai Margin Awal dan wajib mempertahankannya sebagaimana ditetapkan.</li>
                                    <li>membayar biaya-biaya yang diperlukan untuk transaksi, yaitu biaya transaksi, pajak, komisi, dan biaya pelayanan, biaya bunga sesuai tingkat yang berlaku, dan biaya lainnya yang dapat dipertanggungjawabkan berkaitan dengan transaksi sesuai amanat Nasabah, maupun biaya rekening Nasabah.</li>
                                </ol>
                                <div class="text-left">
                                    <label style="cursor:pointer;">
                                        <input type="checkbox"  name="box[]" class="form-check-input ck-item" style="border: 1.5px solid #fff5f5 !important;" value="YA" required=""> Saya sudah membaca dan memahami
                                    </label>
                                </div> 
                            </li>
                            <li class="mb-4">
                                <strong>Pelaksanaan Transaksi</strong>
                                <ol>
                                    <li>Setiap transaksi Nasabah dilaksanakan secara elektronik Online oleh Nasabah yang bersangkutan.</li>
                                    <li>Setiap amanat Nasabah yang diterima dapat langsung dilaksanakan sepanjang nilai Margin yang tersedia pada rekeningnya mencukupi dan eksekusinya dapat menimbulkan perbedaan waktu terhadap proses pelaksanaan transaksi tersebut. Nasabah harus mengetahui posisi Margin dan posisi terbuka sebelum memberikan amanat untuk transaksi berikutnya.</li>
                                    <li>Setiap transaksi Nasabah secara bilateral dilawankan dengan Penyelenggara Sistem Perdagangan Alternatif PT Capital Megah Mandiriyang bekerjasama dengan Pialang Berjangka.</li>
                                    <li>Nasabah bertanggung jawab atas keamanan dan penggunaan username dan password dalam transaksi Perdagangan Berjangka, oleh karenanya Nasabah dilarang memberitahukan, menyerahkan atau meminjamkan username dan password kepada pihak lain, termasuk kepada pegawai Pialang Berjangka.</li>
                                </ol>
                                <div class="text-left">
                                    <label style="cursor:pointer;">
                                        <input type="checkbox"  name="box[]" class="form-check-input ck-item" style="border: 1.5px solid #fff5f5 !important;" value="YA" required> Saya sudah membaca dan memahami
                                    </label>
                                </div> 
                            </li>
                            <li class="mb-4">
                                <strong>Kewajiban Memelihara Margin</strong>
                                <ol>
                                    <li>Nasabah wajib memelihara/memenuhi tingkat Margin yang harus tersedia di rekening pada Pialang Berjangka sesuai dengan jumlah yang telah ditetapkan baik diminta ataupun tidak oleh Pialang Berjangka.</li>
                                    <li>Apabila jumlah Margin memerlukan penambahan maka Pialang Berjangka wajib memberitahukan dan memintakan kepada Nasabah untuk menambah Margin segera.</li>
                                    <li>Apabila jumlah Margin memerlukan tambahan (Call Margin) maka Nasabah wajib melakukan penyerahan Call Margin selambat-lambatnya sebelum dimulai hari perdagangan berikutnya. Kewajiban Nasabah sehubungan dengan penyerahan Call Margin tidak terbatas pada jumlah Margin awal.</li>
                                    <li>Pialang Berjangka tidak berkewajiban melaksanakan amanat untuk melakukan transaksi yang baru dari Nasabah sebelum Call Margin dipenuhi.</li>
                                    <li>Untuk memenuhi kewajiban Call Margin dan keuangan lainnya dari Nasabah, Pialang Berjangka dapat mencairkan dana Nasabah yang ada di Pialang Berjangka.</li>
                                </ol>
                                <div class="text-left">
                                    <label style="cursor:pointer;">
                                        <input type="checkbox"  name="box[]" class="form-check-input ck-item" style="border: 1.5px solid #fff5f5 !important;" value="YA" required> Saya sudah membaca dan memahami
                                    </label>
                                </div> 
                            </li>
                            <li class="mb-4">
                                <strong>Hak Pialang Berjangka Melikuidasi Posisi Nasabah</strong>
                                <p style="padding-left:5px;">Nasabah bertanggung jawab memantau/mengetahui posisi terbukanya secara terus- menerus dan memenuhi kewajibannya. Apabila dalam jangka waktu tertentu dana pada rekening Nasabah kurang dari yang dipersyaratkan, Pialang Berjangka dapat menutup posisi terbuka Nasabah secara keseluruhan atau sebagian, membatasi transaksi, atau tindakan lain untuk melindungi diri dalam pemenuhan Margin tersebut dengan terlebih dahulu memberitahu atau tanpa memberitahu Nasabah dan Pialang Berjangka tidak bertanggung jawab atas kerugian yang timbul akibat tindakan tersebut.<br>
                                </p><div class="text-left">
                                    <label style="cursor:pointer;">
                                        <input type="checkbox"  name="box[]" class="form-check-input ck-item" style="border: 1.5px solid #fff5f5 !important;" value="YA" required> Saya sudah membaca dan memahami
                                    </label>
                                </div> 
                            </li>
                            <li class="mb-4">
                                <strong>Penggantian Kerugian Tidak Adanya Penutupan Posisi</strong>
                                <p style="padding-left:5px;">Apabila Nasabah tidak mampu melakukan penutupan atas transaksi yang jatuh tempo, Pialang Berjangka dapat melakukan penutupan atas transaksi Nasabah yang terjadi. Nasabah wajib membayar biaya-biaya, termasuk biaya kerugian dan premi yang telah dibayarkan oleh Pialang Berjangka, dan apabila Nasabah lalai untuk membayar biaya-biaya tersebut, Pialang Berjangka berhak untuk mengambil pembayaran dari dana Nasabah.<br>
                                </p><div class="text-left">
                                    <label style="cursor:pointer;">
                                        <input type="checkbox"  name="box[]" class="form-check-input ck-item" style="border: 1.5px solid #fff5f5 !important;" value="YA" required> Saya sudah membaca dan memahami
                                    </label>
                                </div> 
                            </li>
                            <li class="mb-4">
                                <strong>Pialang Berjangka Dapat Membatasi Posisi</strong>
                                <p style="padding-left:5px;">Nasabah mengakui hak Pialang Berjangka untuk membatasi posisi terbuka Kontrak dan Nasabah tidak melakukan transaksi melebihi batas yang telah ditetapkan tersebut.<br>
                                </p><div class="text-left">
                                    <label style="cursor:pointer;">
                                        <input type="checkbox"  name="box[]" class="form-check-input ck-item" style="border: 1.5px solid #fff5f5 !important;" value="YA" required> Saya sudah membaca dan memahami
                                    </label>
                                </div> 
                            </li>
                            <li class="mb-4">
                                <strong>Tidak Ada Jaminan atas Informasi atau Rekomendasi</strong>
                                <p style="padding-left:5px;">Nasabah mengakui bahwa :</p>
                                <ol>
                                    <li>Informasi dan rekomendasi yang diberikan oleh Pialang Berjangka kepada Nasabah tidak selalu lengkap dan perlu diverifikasi.</li>
                                    <li>Pialang Berjangka tidak menjamin bahwa informasi dan rekomendasi yang diberikan merupakan informasi yang akurat dan lengkap.</li>
                                    <li>Informasi dan rekomendasi yang diberikan oleh Wakil Pialang Berjangka yang satu dengan yang lain mungkin berbeda karena perbedaan analisis fundamental atau teknikal. Nasabah menyadari bahwa ada kemungkinan Pialang Berjangka dan pihak terafiliasinya memiliki posisi di pasar dan memberikan rekomendasi tidak konsisten kepada Nasabah.</li>
                                </ol>
                                <div class="text-left">
                                    <label style="cursor:pointer;">
                                        <input type="checkbox"  name="box[]" class="form-check-input ck-item" style="border: 1.5px solid #fff5f5 !important;" value="YA" required> Saya sudah membaca dan memahami
                                    </label>
                                </div> 
                            </li>
                            <li class="mb-4">
                                <strong>Pembatasan Tanggung Jawab Pialang Berjangka.</strong>
                                <ol>
                                    <li>Pialang Berjangka tidak bertanggung jawab untuk memberikan penilaian kepada Nasabah mengenai iklim, pasar, keadaan politik dan ekonomi nasional dan internasional, nilai Kontrak Derivatif, kolateral, atau memberikan nasihat mengenai keadaan pasar. Pialang Berjangka hanya memberikan pelayanan untuk melakukan transaksi secara jujur serta memberikan laporan atas transaksi tersebut.</li>
                                    <li>Perdagangan sewaktu-waktu dapat dihentikan oleh pihak yang memiliki otoritas (Bappebti/Bursa Berjangka) tanpa pemberitahuan terlebih dahulu kepada Nasabah. Atas posisi terbuka yang masih dimiliki oleh Nasabah pada saat perdagangan tersebut dihentikan, maka akan diselesaikan (likuidasi) berdasarkan pada peraturan/ketentuan yang dikeluarkan dan ditetapkan oleh pihak otoritas tersebut, dan semua kerugian serta biaya yang timbul sebagai akibat dihentikannya transaksi oleh pihak otoritas perdagangan tersebut, menjadi beban dan tanggung jawab Nasabah sepenuhnya.</li>
                                </ol>
                                <div class="text-left">
                                    <label style="cursor:pointer;">
                                        <input type="checkbox"  name="box[]" class="form-check-input ck-item" style="border: 1.5px solid #fff5f5 !important;" value="YA" required> Saya sudah membaca dan memahami
                                    </label>
                                </div> 
                            </li>
                            <li class="mb-4">
                                <strong>Transaksi Harus Mematuhi Peraturan Yang Berlaku</strong>
                                <p style="padding-left:5px;">
                                    Semua transaksi dilakukan sendiri oleh Nasabah dan wajib mematuhi peraturan
                                    perundang-undangan di bidang Perdagangan Berjangka, kebiasaan dan
                                    interpretasi resmi yang ditetapkan oleh Bappebti atau Bursa Berjangka. 
                                </p>
                                <div class="text-left">
                                    <label style="cursor:pointer;">
                                        <input type="checkbox"  name="box[]" class="form-check-input ck-item" style="border: 1.5px solid #fff5f5 !important;" value="YA" required> Saya sudah membaca dan memahami
                                    </label>
                                </div> 
                            </li>
                            <li class="mb-4">
                                <strong>Pialang Berjangka tidak Bertanggung jawab atas Kegagalan Komunikasi</strong>
                                <p style="padding-left:5px;">Pialang Berjangka tidak bertanggung jawab atas keterlambatan atau tidak tepat waktunya pengiriman amanat atau informasi lainnya yang disebabkan oleh kerusakan fasilitas komunikasi atau sebab lain diluar kontrol Pialang Berjangka.<br>
                                </p>
                                <div class="text-left">
                                    <label style="cursor:pointer;">
                                        <input type="checkbox"  name="box[]" class="form-check-input ck-item" style="border: 1.5px solid #fff5f5 !important;" value="YA" required> Saya sudah membaca dan memahami
                                    </label>
                                </div> 
                            </li>
                            <li class="mb-4">
                                <strong>Konfirmasi</strong>
                                <ol>
                                    <li>Konfirmasi dari Nasabah dapat berupa surat, telex, media lain, surat elektronik, secara tertulis ataupun rekaman suara.</li>
                                    <li>Pialang Berjangka berkewajiban menyampaikan konfirmasi transaksi, laporan rekening, permintaan Call Margin, dan pemberitahuan lainnya kepada Nasabah secara akurat, benar dan secepatnya pada alamat (email) Nasabah sesuai dengan yang tertera dalam rekening Nasabah. Apabila dalam jangka waktu 2 x 24 jam setelah amanat jual atau beli disampaikan, tetapi Nasabah belum menerima konfirmasi melalui alamat email Nasabah dan/atau sistem transaksi, Nasabah segera memberitahukan hal tersebut kepada Pialang Berjangka melalui telepon dan disusul dengan pemberitahuan tertulis.</li>
                                    <li>Jika dalam waktu 2 x 24 jam sejak tanggal penerimaan konfirmasi tersebut tidak ada sanggahan dari Nasabah maka konfirmasi Pialang Berjangka dianggap benar dan sah.</li>
                                    <li>Kekeliruan atas konfirmasi yang diterbitkan Pialang Berjangka akan diperbaiki oleh Pialang Berjangka sesuai keadaan yang sebenarnya dan demi hukum konfirmasi yang lama batal.</li>
                                    <li>Nasabah tidak bertanggung jawab atas transaksi yang dilaksanakan atas rekeningnya apabila konfirmasi tersebut tidak disampaikan secara benar dan akurat.</li>
                                </ol>
                                <div class="text-left">
                                    <label style="cursor:pointer;">
                                        <input type="checkbox"  name="box[]" class="form-check-input ck-item" style="border: 1.5px solid #fff5f5 !important;" value="YA" required> Saya sudah membaca dan memahami
                                    </label>
                                </div> 
                            </li>
                            <li class="mb-4">
                                <strong>Kebenaran Informasi Nasabah</strong>
                                <p style="padding-left:5px;">Nasabah memberikan informasi yang benar dan akurat mengenai data Nasabah yang diminta oleh Pialang Berjangka dan akan memberitahukan paling lambat dalam waktu 3 (tiga) hari kerja setelah terjadi perubahan, termasuk perubahan kemampuan keuangannya untuk terus melaksanakan transaksi.<br>
                                </p><div class="text-left">
                                    <label style="cursor:pointer;">
                                        <input type="checkbox"  name="box[]" class="form-check-input ck-item" style="border: 1.5px solid #fff5f5 !important;" value="YA" required> Saya sudah membaca dan memahami
                                    </label>
                                </div> 
                            </li>
                            <li class="mb-4">
                                <strong>Komisi Transaksi</strong>
                                <p style="padding-left:5px;">Nasabah mengetahui dan menyetujui bahwa Pialang Berjangka berhak untuk memungut komisi atas transaksi yang telah dilaksanakan, dalam jumlah sebagaimana akan ditetapkan dari waktu ke waktu oleh Pialang Berjangka. Perubahan beban (fees) dan biaya lainnya harus disetujui secara tertulis oleh Para Pihak.<br>
                                </p><div class="text-left">
                                    <label style="cursor:pointer;">
                                        <input type="checkbox"  name="box[]" class="form-check-input ck-item" style="border: 1.5px solid #fff5f5 !important;" value="YA" required> Saya sudah membaca dan memahami
                                    </label>
                                </div> 
                            </li>
                            <li class="mb-4">
                                <strong>Pemberian Kuasa</strong>
                                <p style="padding-left:5px;">Nasabah memberikan kuasa kepada Pialang Berjangka untuk menghubungi bank, lembaga keuangan, Pialang Berjangka lain, atau institusi lain yang terkait untuk memperoleh keterangan atau verifikasi mengenai informasi yang diterima dari Nasabah. Nasabah mengerti bahwa penelitian mengenai data hutang pribadi dan bisnis dapat dilakukan oleh Pialang Berjangka apabila diperlukan. Nasabah diberikan kesempatan untuk memberitahukan secara tertulis dalam jangka waktu yang telah disepakati untuk melengkapi persyaratan yang diperlukan.<br>
                                </p><div class="text-left">
                                    <label style="cursor:pointer;">
                                        <input type="checkbox"  name="box[]" class="form-check-input ck-item" style="border: 1.5px solid #fff5f5 !important;" value="YA" required> Saya sudah membaca dan memahami
                                    </label>
                                </div> 
                            </li>
                            <li class="mb-4">
                                <strong>Pemindahan Dana</strong>
                                <p style="padding-left:5px;">Pialang Berjangka dapat setiap saat mengalihkan dana dari satu rekening ke rekening lainnya berkaitan dengan kegiatan transaksi yang dilakukan Nasabah seperti pembayaran komisi, pembayaran biaya transaksi, kliring dan keterlambatan dalam memenuhi kewajibannya, tanpa terlebih dahulu memberitahukan kepada Nasabah. Transfer yang telah dilakukan akan segera diberitahukan secara tertulis kepada Nasabah<br>
                                </p><div class="text-left">
                                    <label style="cursor:pointer;">
                                        <input type="checkbox"  name="box[]" class="form-check-input ck-item" style="border: 1.5px solid #fff5f5 !important;" value="YA" required> Saya sudah membaca dan memahami
                                    </label>
                                </div> 
                            </li>
                            <li class="mb-4">
                                <strong>Pemberitahuan</strong>
                                <ol>
                                    <li seq=" (4)">Semua komunikasi, uang, surat berharga, dan kekayaan lainnya harus dikirimkan langsung ke alamat Nasabah seperti tertera dalam rekeningnya atau alamat lain yang ditetapkan/diberitahukan secara tertulis oleh Nasabah.</li>
                                    <li seq=" (5)">
                                        Semua uang, harus disetor atau ditransfer langsung oleh Nasabah ke Rekening Terpisah (Segregated Account) Pialang Berjangka:
                                        <table width="100%" style="margin-left:5px;">
                                            <tbody>
                                                <tr>
                                                    <td width="36%">Nama</td>
                                                    <td width="2%">&nbsp;:&nbsp;</td>
                                                    <td><?= $profile['COMPANY_NAME'];  ?></td>
                                                </tr>
                                                <tr>
                                                    <td valign="top">Alamat</td>
                                                    <td valign="top">&nbsp;:&nbsp;</td>
                                                    <td><?= $profile['OFFICE']['OFC_ADDRESS']; ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <div class="table-responsive mt-2">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Currency</th>
                                                        <th>Account</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $sql_get_bankadm = mysqli_query($db, "SELECT * FROM tb_bankadm"); ?>
                                                    <?php if($sql_get_bankadm) : ?>
                                                        <?php while($bkadm = mysqli_fetch_assoc($sql_get_bankadm)) : ?>
                                                            <tr>
                                                                <td><?= $bkadm['BKADM_NAME'] ?></td>
                                                                <td><?= $bkadm['BKADM_CURR'] ?></td>
                                                                <td><?= $bkadm['BKADM_ACCOUNT'] ?></td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <p class="mt-2">
                                            dan dianggap sudah diterima oleh Pialang Berjangka apabila sudah ada tanda terima bukti setor atau transfer dari pegawai Pialang Berjangka.<br>
                                        </p>
                                    </li>
                                    <li>Semua surat berharga, kekayaan lainnya, atau komunikasi harus dikirim kepada Pialang Berjangka:
                                        <div class="table-responsive">
                                            <table class="table table-hover" style="text-align: left; table-layout: fixed; word-break: break;" width="100%">
                                                <tbody>
                                                    <tr>
                                                        <td width="15%" class="top-align fw-bold text-start">Nama</td>
                                                        <td width="3%" class="top-align"> : </td>
                                                        <td class="top-align text-start"><?= $profile['COMPANY_NAME']; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="15%" class="top-align fw-bold text-start">Alamat</td>
                                                        <td width="3%" class="top-align"> : </td>
                                                        <td class="top-align text-start"><?= $profile['OFFICE']['OFC_ADDRESS'] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="15%" class="top-align fw-bold text-start">Telepon</td>
                                                        <td width="3%" class="top-align"> : </td>
                                                        <td class="top-align text-start"><?= $profile['OFFICE']['OFC_PHONE'] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="15%" class="top-align fw-bold text-start">Faksimili</td>
                                                        <td width="3%" class="top-align"> : </td>
                                                        <td class="top-align text-start"><?= $profile['OFFICE']['OFC_FAX'] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="15%" class="top-align fw-bold text-start">E-mail</td>
                                                        <td width="3%" class="top-align"> : </td>
                                                        <td class="top-align text-start"><?= $profile['OFFICE']['OFC_EMAIL'] ?></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        dan dianggap sudah diterima oleh Pialang Berjangka apabila sudah ada tanda bukti penerimaan dari pegawai Pialang Berjangka.
                                        <br>
                                    </li>
                                </ol>
                                <div class="text-left">
                                    <label style="cursor:pointer;">
                                        <input type="checkbox"  name="box[]" class="form-check-input ck-item" style="border: 1.5px solid #fff5f5 !important;" value="YA" required> Saya sudah membaca dan memahami
                                    </label>
                                </div> 
                            </li>
                            <li class="mb-4">
                                <strong>Dokumen Pemberitahuan Adanya Risiko</strong>
                                <p style="padding-left:5px;">Nasabah mengakui menerima dan mengerti Dokumen Pemberitahuan Adanya Risiko.<br>
                                </p><div class="text-left">
                                    <label style="cursor:pointer;">
                                        <input type="checkbox"  name="box[]" class="form-check-input ck-item" style="border: 1.5px solid #fff5f5 !important;" value="YA" required> Saya sudah membaca dan memahami
                                    </label>
                                </div> 
                            </li>
                            <li class="mb-4">
                                <strong>Jangka Waktu Perjanjian dan Pengakhiran</strong>
                                <ol>
                                    <li>Perjanjian ini mulai berlaku terhitung sejak tanggal dilakukannya konfirmasi oleh Pialang Berjangka dengan diterimanya Bukti Konfirmasi Penerimaan Nasabah dari Pialang Berjangka oleh Nasabah.</li>
                                    <li>Nasabah dapat mengakhiri Perjanjian ini hanya jika Nasabah sudah tidak lagi memiliki posisi terbuka dan tidak ada kewajiban Nasabah yang diemban oleh atau terhutang kepada Pialang Berjangka.</li>
                                    <li>Pengakhiran tidak membebaskan salah satu Pihak dari tanggung jawab atau kewajiban yang terjadi sebelum pemberitahuan tersebut.</li>
                                </ol>
                                <div class="text-left">
                                    <label style="cursor:pointer;">
                                        <input type="checkbox"  name="box[]" class="form-check-input ck-item" style="border: 1.5px solid #fff5f5 !important;" value="YA" required> Saya sudah membaca dan memahami
                                    </label>
                                </div> 
                            </li>
                            <li class="mb-4">
                                <strong>Berakhirnya Perjanjian</strong>
                                <p style="padding-left:5px;">Perjanjian dapat berakhir dalam hal Nasabah:</p>
                                <ol>
                                    <li>dinyatakan pailit, memiliki hutang yang sangat besar, dalam proses peradilan, menjadi hilang ingatan, mengundurkan diri atau meninggal;</li>
                                    <li>tidak dapat memenuhi atau mematuhi perjanjian ini dan/atau melakukan pelanggaran terhadapnya;</li>
                                    <li>
                                        berkaitan dengan butir (1) dan (2) tersebut diatas, Pialang Berjangka dapat :                                                            
                                        <ol>
                                            <li>meneruskan atau menutup posisi Nasabah tersebut setelah mempertimbangkannya secara cermat dan jujur ; dan</li>
                                            <li>menolak transaksi dari Nasabah.</li>
                                        </ol>
                                    </li>
                                    <li seq=" (4)">Pengakhiran Perjanjian sebagaimana dimaksud dengan angka (1) dan (2) tersebut di atas tidak melepaskan kewajiban dari Para Pihak yang berhubungan dengan penerimaan atau kewajiban pembayaran atau pertanggungjawaban kewajiban lainnya yang timbul dari Perjanjian.</li>
                                </ol>
                                <div class="text-left">
                                    <label style="cursor:pointer;">
                                        <input type="checkbox"  name="box[]" class="form-check-input ck-item" style="border: 1.5px solid #fff5f5 !important;" value="YA" required> Saya sudah membaca dan memahami
                                    </label>
                                </div> 
                            </li>
                            <li class="mb-4">
                                <strong><i>Force Majeur</i></strong>
                                <p style="padding-left:5px;">
                                    Tidak ada satupun pihak di dalam Perjanjian dapat diminta pertanggungjawabannya untuk suatu keterlambatan atau terhalangnya memenuhi kewajiban berdasarkan Perjanjian yang diakibatkan oleh suatu sebab yang berada di luar kemampuannya atau kekuasaannya (<i>force majeur</i>), sepanjang pemberitahuan tertulis mengenai sebab itu disampaikannya kepada pihak lain dalam Perjanjian dalam waktu tidak lebih dari 24 (dua puluh empat) jam sejak timbulnya sebab itu.<br>
                                    Yang dimaksud dengan <i>Force Majeur</i> dalam Perjanjian adalah peristiwa kebakaran, bencana alam (seperti gempa bumi, banjir, angin topan, petir), pemogokan umum, huru hara, peperangan, perubahan terhadap peraturan perundang-undangan yang berlaku dan kondisi di bidang ekonomi, keuangan dan Perdagangan Berjangka, pembatasan yang dilakukan oleh otoritas Perdagangan Berjangka dan Bursa Berjangka serta terganggunya sistem perdagangan, kliring dan penyelesaian transaksi Kontrak Berjangka di mana transaksi dilaksanakan yang secara langsung mempengaruhi pelaksanaan pekerjaan berdasarkan Perjanjian.<br>
                                </p>
                                <div class="text-left">
                                    <label style="cursor:pointer;">
                                        <input type="checkbox"  name="box[]" class="form-check-input ck-item" style="border: 1.5px solid #fff5f5 !important;" value="YA" required> Saya sudah membaca dan memahami
                                    </label>
                                </div> 
                            </li>
                            <li class="mb-4">
                                <strong>Perubahan atas Isian dalam Perjanjian Pemberian Amanat</strong>
                                <p style="padding-left:5px;">Perubahan atas isian dalam Perjanjian ini hanya dapat dilakukan atas persetujuan Para Pihak, atau Pialang Berjangka telah memberitahukan secara tertulis perubahan yang diinginkan, dan Nasabah tetap memberikan perintah untuk transaksi dengan tanpa memberikan tanggapan secara tertulis atas usul perubahan tersebut. Tindakan Nasabah tersebut dianggap setuju atas usul perubahan tersebut.<br>
                                </p><div class="text-left">
                                    <label style="cursor:pointer;">
                                        <input type="checkbox"  name="box[]" class="form-check-input ck-item" style="border: 1.5px solid #fff5f5 !important;" value="YA" required> Saya sudah membaca dan memahami
                                    </label>
                                </div> 
                            </li>
                            <li class="mb-4">
                                <strong>Tanggung Jawab Kepada Nasabah</strong>
                                <ol type="a">
                                    <li>
                                        Penyelenggara Sistem Perdagangan Alternatif yang merupakan pihak yang menguasai dan/atau memiliki sistem perdagangan elektronik bertanggung jawab atas pelanggaran 
                                        penyalahgunaan sistem perdagangan elektronik sesuai dengan ketentuan yang diatur dalam Perjanjian Kerjasama (PKS) dan peraturan perdagangan (trading rules) 
                                        antara Penyelenggara Sistem Perdagangan Alternatif dan Peserta Sistem Perdagangan Alternatif yang mengakibatkan kerugian Nasabah.
                                    </li>
                                    <li>
                                        Peserta Sistem Perdagangan Alternatif yang merupakan pihak yang menggunakan sistem perdagangan 
                                        elektronik bertanggung jawab atas pelanggaran penyalahgunaan sistem perdagangan elektronik 
                                        sebagaimana dimaksud pada angka 22 huruf (a) yang mengakibatkan kerugian Nasabah.
                                    </li>
                                    <li>
                                        Dalam pemanfaatan sistem perdagangan elektronik, 
                                        Penyelenggara Sistem Perdagangan Alternatif dan/atau Peserta Sistem Perdagangan 
                                        Alternatif tidak bertanggung jawab atas kerugian Nasabah diluar hal-hal yang telah diatur pada 
                                        angka 22 huruf (a) dan (b), antara lain: kerugian yang diakibatkan oleh risiko-risiko yang 
                                        disebutkan di dalam Dokumen Pemberitahuan Adanya Risiko yang telah dimengerti dan disetujui 
                                        oleh Nasabah.
                                    </li>
                                </ol>
                                <div class="text-left">
                                    <label style="cursor:pointer;">
                                        <input type="checkbox" name="box[]" class="form-check-input ck-item" style="border: 1.5px solid #fff5f5 !important;" value="YA" required/> 
                                        Saya sudah membaca dan memahami
                                    </label>
                                </div> 
                            </li>
                            <li class="mb-4">
                                <strong>Penyelesaian Perselisihan</strong>
                                <ol>
                                    <li>Semua perselisihan dan perbedaan pendapat yang timbul dalam pelaksanaan Perjanjian ini wajib diselesaikan terlebih dahulu secara musyawarah untuk mencapai mufakat antara Para Pihak.</li>
                                    <li>Apabila perselisihan dan perbedaan pendapat yang timbul tidak dapat diselesaikan secara musyawarah untuk mencapai mufakat, Para Pihak wajib memanfaatkan sarana penyelesaian perselisihan yang tersedia di Bursa Berjangka.</li>
                                    <li>
                                        Apabila perselisihan dan perbedaan pendapat yang timbul tidak dapat diselesaikan melalui cara sebagaimana dimaksud pada angka (1) dan angka (2), maka Para Pihak sepakat untuk menyelesaikan perselisihan melalui *):
                                        <div class="row">
                                            <div class="col-2"><input class="form-check-input radio_css" type="radio" name="step07_kotapenyelesaian" checked value="BAKTI" required=""></div>
                                            <div class="col-2">&nbsp;&nbsp;a.&nbsp;&nbsp;</div>
                                            <div class="col-8">
                                                Badan Arbitrase Perdagangan Berjangka Komoditi (BAKTI) 
                                                berdasarkan Peraturan dan Prosedur Badan Arbitrase 
                                                Perdagangan Berjangka Komoditi (BAKTI); atau
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-2"><input class="form-check-input radio_css" type="radio" name="step07_kotapenyelesaian" value="Pengadilan Negeri Jakarta Utara" required=""></div>
                                            <div class="col-2">&nbsp;&nbsp;b.&nbsp;&nbsp;</div>
                                            <div class="col-8">
                                                Pengadilan Negeri Jakarta Utara
                                            </div>
                                        </div>
                                        <!-- <table width="50%" style="margin-left:5px;">
                                            <tbody>
                                                <tr>
                                                    <td width="2%" style="vertical-align:top"><input class="form-check-input radio_css" type="radio" name="step07_kotapenyelesaian" <?php echo (retnull("ACC_F_PERJ_PERSLISIHAN") == 'BAKTI') ? 'checked' : NULL; ?> value="BAKTI" required=""></td>
                                                    <td width="2%" style="vertical-align:top">&nbsp;&nbsp;a.&nbsp;&nbsp;</td>
                                                    <td>Badan Arbitrase Perdagangan Berjangka Komoditi (BAKTI) 
                                                    berdasarkan Peraturan dan Prosedur Badan Arbitrase 
                                                    Perdagangan Berjangka Komoditi (BAKTI); atau</td>
                                                </tr>
                                                <tr>
                                                    <td width="2%" style="vertical-align:top"><input class="form-check-input radio_css" type="radio" name="step07_kotapenyelesaian" <?php echo (retnull("ACC_F_PERJ_PERSLISIHAN") == 'Pengadilan Negeri Jakarta Utara') ? 'checked' : NULL; ?> value="Pengadilan Negeri Jakarta Utara" required=""></td>
                                                    <td width="2%" style="vertical-align:top">&nbsp;&nbsp;b.&nbsp;&nbsp;</td>
                                                    <td>Pengadilan Negeri Jakarta Utara</td>
                                                </tr>
                                            </tbody>
                                        </table> -->
                                    </li>
                                    <li>Kantor atau kantor cabang Pialang Berjangka terdekat dengan domisili Nasabah tempat penyelesaian dalam hal terjadi perselisihan.
                                        <ul>
                                            <li>
                                                Kantor yang dipilih (salah satu)<br>
                                                Daftar Kantor: 
                                                <ol type="a">
                                                    <?php foreach(ProfilePerusahaan::office() as $key => $ofc) : ?>
                                                        <li>
                                                            <input type="radio" class="form-check-input radio_css" name="step07_kantorpenyelesaian" <?php echo (strtoupper(retnull("ACC_F_PERJ_KANTOR")) == strtoupper($ofc['OFC_CITY']) || $key == 0) ? 'checked' : NULL; ?> value="<?= strtoupper($ofc['OFC_CITY']); ?>" required="">
                                                            <?= strtoupper($ofc['OFC_CITY']) ?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ol>
                                            </li>
                                        </ul>    
                                    </li>
                                </ol>
                                <div class="text-left">
                                    <label style="cursor:pointer;">
                                        <input type="checkbox"  name="box[]" class="form-check-input ck-item" style="border: 1.5px solid #fff5f5 !important;" value="YA" required> Saya sudah membaca dan memahami
                                    </label>
                                </div> 
                            </li>
                            <li class="mb-4">
                                <strong>Bahasa</strong>
                                <p style="padding-left:5px;">Perjanjian ini dibuat dalam Bahasa Indonesia.<br>
                                </p><div class="text-left">
                                    <label style="cursor:pointer;">
                                        <input type="checkbox"  name="box[]" class="form-check-input ck-item" style="border: 1.5px solid #fff5f5 !important;" value="YA" required> Saya sudah membaca dan memahami
                                    </label>
                                </div> 
                            </li>
                        </ol>
    
                        <label style="cursor:pointer;">
                            <input type="checkbox" id="check_all" data-done="<?= !empty($realAccount['ACC_F_PERJ']) ?>" class="form-check-input me-1" style="border: 1.5px solid #fff5f5 !important;" /> 
                            Setujui Semua
                        </label>
                        
                        <p class="mt-3 text-justify">
                            Demikian Perjanjian Pemberian Amanat ini dibuat oleh Para Pihak dalam keadaan sadar, sehat jasmani rohani dan tanpa unsur paksaan dari pihak manapun.
                        </p>
                        <p class="text-justify">
                            "Saya telah membaca, mengerti dan setuju terhadap semua ketentuan yang<br>tercantum dalam perjanjian ini"
                        </p>
                        <hr>
                        <p class="text-center">
                            Dengan mengisi kolom "YA" di bawah ini, saya menyatakan bahwa saya telah menerima<br>
                            "PERJANJIAN PEMBERIAN AMANAT TRANSAKSI KONTRAK <span class="title text-uppercase">Derivatif Dalam Sistem Perdagangan Alternatif</span>"<br>
                            mengerti dan menyetujui isinya.
                        </p>
    
                        <hr>
    
                        <div class="row">
                            <div class="col-md-6 mb-2 col-sm-12">
                                <label>Pernyataan menerima/tidak</label><br>
                                <input type="radio" id="agree" name="aggree" value="Ya" class="form-check-input" required <?= !empty($realAccount['ACC_F_PERJ'])? "checked" : "" ?>> 
                                <label for="aggree">Ya</label>
                                <input type="radio" name="aggree" value="Tidak" class="form-check-input ms-2" required>
                                <label for="aggree">Tidak</label>
                            </div>
    
                            <div class="col-md-6 col-sm-12">
                                <label for="" class="form-label">Menerima pada tanggal</label>
                                <input type="text" name="agg_date" readonly required value="<?= retnull("ACC_F_PERJ_DATE", date('Y-m-d H:i:s')) ?>" class="form-control text-center mb-3 <?= empty($realAccount['ACC_F_PERJ_DATE'])? "realtime-date" : "" ?>">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex flex-row justify-content-end align-items-center gap-2 mt-25">
                            <a href="<?= ($prevPage['page'])? ("/account/create?page=".$prevPage['page']) : "javascript:void(0)"; ?>" class="btn btn-secondary">Previous</a>
                            <button type="submit" class="btn btn-primary">Next</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript">
    $(function () {
        const $checkAll = $('#check_all');
        const itemSelector = '.ck-item:not(:disabled):not(#check_all)'; // Exclude check_all from items

        function syncMaster() {
            const $items   = $(itemSelector);
            const total    = $items.length;
            const checked  = $items.filter(':checked').length;

            console.log('Total items:', total, 'Checked items:', checked); // Debug log
            
            $checkAll.prop('checked', checked === total);
            $checkAll.prop('indeterminate', checked > 0 && checked < total);
        }

        $checkAll.on('change', function () {
            $(itemSelector).prop('checked', this.checked);
            syncMaster();
        });

        $(document).on('change', itemSelector, syncMaster);

        syncMaster();
    });
</script>

<script type="text/javascript">
    $(document).ready(function(){
        // if($('#check_all')?.data('done')) {
        //     $('#check_all').prop('checked', true).change();
        // }
        
        $('#form-perjanjian-amanat').on('submit', function(event) {
            event.preventDefault();
            Swal.fire({
                text: "Please wait...",
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            })
            
            $.ajax({
                url: "/ajax/regol/perjanjianPemberianAmanat",
                type: "POST",
                dataType: "json",
                data: new FormData(this),
                processData: false,
                contentType: false,
                cache: false
            }).done(function(resp) {
                if(!resp.success) {
                    Swal.fire(resp.alert);
                    return;
                }
                
                location.href = resp.redirect
            })
        })
    });
</script>