
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
            <h4 class="text-center" style="margin: 0px;">VERIFIKASI KELENGKAPAN PROSES PENERIMAAN NASABAH SECARA ELEKTRONIK ONLINE DENGAN CDD SEDERHANA</h4>
            <table class="table" style="margin-top: 20px;">
                <tbody>
                    <?php 
                        $arrayList = [
                            [
                                "noformulid" => "FORMULIR PBK. CDDS.01",
                                "title" => "PROFILE PERUSAHAAN PIALANG BERJANGKA",
                                "description" => "Formulir ini berisi informasi mengenai profil perusahaan pialang berjangka, termasuk sejarah perusahaan, struktur organisasi, layanan yang ditawarkan, dan informasi penting lainnya yang perlu diketahui oleh nasabah sebelum melakukan transaksi perdagangan berjangka."
                            ],
                            [
                                "noformulid" => "FORMULIR PBK. CDDS.02",
                                "title" => "PERNYATAAN TELAH MELAKUKAN SIMULASI PERDAGANGAN BERJANGKA ATAU PERNYATAAN TELAH BERPENGALAMAN DALAM MELAKSANAKAN TRANSAKSI PERDAGANGAN BERJANGKA",
                                "description" => "Formulir ini digunakan untuk menyatakan bahwa nasabah telah melakukan simulasi perdagangan berjangka atau memiliki pengalaman dalam melaksanakan transaksi perdagangan berjangka. Hal ini penting untuk memastikan bahwa nasabah memahami risiko yang terkait dengan perdagangan berjangka sebelum terlibat dalam aktivitas tersebut."
                            ],
                            [
                                "noformulid" => "FORMULIR PBK. CDDS.03",
                                "title" => "PERNYATAAN PENGUNGKAPAN (DISCLOSURE STATEMENT)",
                                "description" => "Formulir ini berisi pernyataan pengungkapan yang memberikan informasi penting kepada nasabah mengenai risiko, biaya, dan ketentuan lainnya yang terkait dengan perdagangan berjangka. Nasabah diharapkan untuk membaca dan memahami isi formulir ini sebelum melakukan transaksi."
                            ],
                            [
                                "noformulid" => "FORMULIR PBK. CDDS.04",
                                "title" => "APLIKASI PEMBUKAAN REKENING TRANSAKSI",
                                "description" => "Formulir ini digunakan untuk mengajukan permohonan pembukaan rekening transaksi di perusahaan pialang berjangka. Formulir ini biasanya mencakup informasi pribadi nasabah, data keuangan, dan dokumen pendukung lainnya yang diperlukan untuk proses pembukaan rekening."
                            ],
                            [
                                "noformulid" => "FORMULIR PBK. CDDS.03",
                                "title" => "PERNYATAAN PENGUNGKAPAN (DISCLOSURE STATEMENT)",
                                "description" => "Formulir ini berisi pernyataan pengungkapan tambahan yang memberikan informasi lebih lanjut kepada nasabah mengenai risiko, biaya, dan ketentuan lainnya yang terkait dengan perdagangan berjangka. Formulir ini mungkin mencakup informasi tentang leverage, margin, dan risiko pasar yang perlu dipahami oleh nasabah."
                            ],
                            [
                                "noformulid" => "FORMULIR PBK. CDDS.05",
                                "title" => "DOKUMEN PEMBERITAHUAN ADANYA RISIKO",
                                "description" => "Formulir ini digunakan untuk memberikan pemberitahuan kepada nasabah tentang adanya risiko yang terkait dengan perdagangan berjangka. Formulir ini biasanya mencakup penjelasan tentang risiko pasar, risiko likuiditas, risiko kredit, dan risiko lainnya yang perlu dipahami oleh nasabah sebelum melakukan transaksi."
                            ],
                            [
                                "noformulid" => "FORMULIR PBK. CDDS.03",
                                "title" => "PERNYATAAN PENGUNGKAPAN (DISCLOSURE STATEMENT)",
                                "description" => "Formulir ini berisi pernyataan pengungkapan tambahan yang memberikan informasi lebih lanjut kepada nasabah mengenai risiko, biaya, dan ketentuan lainnya yang terkait dengan perdagangan berjangka. Formulir ini mungkin mencakup informasi tentang strategi perdagangan, manajemen risiko, dan faktor-faktor lain yang perlu dipahami oleh nasabah sebelum melakukan transaksi."
                            ],
                            [
                                "noformulid" => "FORMULIR PBK. CDDS.06",
                                "title" => "PERJANJIAN PEMBERIAN AMANAT",
                                "description" => "Formulir ini digunakan untuk menyatakan perjanjian pemberian amanat antara nasabah dan perusahaan pialang berjangka. Formulir ini biasanya mencakup ketentuan mengenai tanggung jawab, hak, dan kewajiban kedua belah pihak dalam melakukan transaksi perdagangan berjangka."
                            ],
                            [
                                "noformulid" => "FORMULIR PBK. CDDS.07",
                                "title" => "DAFTAR KONTRAK BERJANGKA, KONTRAK DERIVATIF DAN KONTRAK DERIVATIF LAINNYA BESERTA PERATURAN PERDAGANGAN (TRADING RULES)",
                                "title" => "DAFTAR KONTRAK BERJANGKA, KONTRAK DERIVATIF DAN KONTRAK DERIVATIF LAINNYA BESERTA PERATURAN PERDAGANGAN (TRADING RULES)",
                                "description" => "Formulir ini berisi daftar kontrak berjangka, kontrak derivatif, dan kontrak derivatif lainnya yang tersedia untuk diperdagangkan di perusahaan pialang berjangka, beserta peraturan perdagangan yang berlaku. Formulir ini memberikan informasi penting kepada nasabah tentang produk yang dapat diperdagangkan dan aturan yang harus diikuti dalam melakukan transaksi."
                            ],
                            [
                                "noformulid" => "FORMULIR PBK. CDDS.08",
                                "title" => "PERNYATAAN BERTANGGUNG JAWAB ATAS KODE AKSES TRANSAKSI NASABAH (PERSONAL ACCESS PASSWORD)",
                                "description" => "Formulir ini digunakan untuk menyatakan bahwa nasabah bertanggung jawab atas kode akses transaksi (personal access password) yang diberikan oleh perusahaan pialang berjangka. Formulir ini biasanya mencakup ketentuan mengenai keamanan kode akses, tanggung jawab nasabah dalam menjaga kerahasiaan kode akses, dan konsekuensi jika terjadi penyalahgunaan kode akses."
                            ],
                            [
                                "noformulid" => "FORMULIR PBK. CDDS.09",
                                "title" => "PERNYATAAN BAHWA DANA YANG DIGUNAKAN SEBAGAI MARGIN MERUPAKAN DANA MILIK NASABAH SENDIRI",
                                "description" => "Formulir ini digunakan untuk menyatakan bahwa dana yang digunakan sebagai margin dalam perdagangan berjangka merupakan dana milik nasabah sendiri. Formulir ini biasanya mencakup ketentuan mengenai sumber dana margin, tanggung jawab nasabah dalam menyediakan dana margin, dan konsekuensi jika terjadi penyalahgunaan dana margin."
                            ]
                        ];
                    ?>
                        
                    <?php foreach($arrayList as $key => $val) : ?>
                        <tr style="font-size: 13px;">
                            <td width="6%" class="text-center"><?= $key + 1 ?></td>
                            <td width="25%" class="text-center"><?= $val["noformulid"] ?></td>
                            <td style="text-align: left;"><?= $val["title"] ?></td>
                            <td width="10%" class="text-center"><div style="font-family: DejaVu Sans, sans-serif;">✔</div></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div style="text-align:center; margin-top: 10px;">
                <p>Dengan mengisi kolom "YA" dibawah ini, saya menyatakan bahwa saya telah<br>
                membaca dan memahami seluruh isi document yg disampaikan dalam<br>
                FORMULIR PBK.CDDS.01 sampai dengan FORMULIR PBK.CDDS.09</p>
                <p>Demikian Pernyataan ini dibuat dengan sebenarnya dalam keadaan sadar,<br>
                sehat jasmani dan rohani serta tanpa paksaan apapun dari pihak manapun</p>
                <table width="50%" style="text-align: center; margin: auto;">
                    <tr>
                        <td>Pernyataan menerima / Tidak:</td>
                        <td style="text-align: left;">Ya</td>
                    </tr>
                    <tr>
                        <td>Pernyataan pada Tanggal:</td>
                        <td style="text-align: left;"><?= date('d-m-Y', strtotime($realAccount["ACC_F_PROFILE_DATE"])) ?></td>
                    </tr>
                </table>
                <!-- <p>IP Address: <?= $realAccount["ACC_F_PROFILE_IP"] ?></p> -->
            </div>
        </div>
    </body>
</html>