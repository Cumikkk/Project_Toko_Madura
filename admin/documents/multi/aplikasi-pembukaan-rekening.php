<!DOCTYPE html>
<html>
    <head>
        <?php require_once(__DIR__  . "/../style.php"); ?>
    </head>
    <body>
        <div class="header">
            <img style="object-fit: cover; max-height: 100%; max-width: 100%;" src="data:image/png;base64,<?= base64_encode(file_get_contents($logo_pdf)); ?>">
        </div>

        <div class="section">
            <h4 class="text-center" style="margin: 0px;">APLIKASI PEMBUKAAN REKENING TRANSAKSI SECARA ELEKTRONIK ONLINE</h4>
            <table class="table no-border" style="margin-top: 10px;">
                <tbody>
                    <tr>
                        <th colspan="3" class="v-align-middle" style="padding: 6px; background-color: #edebe0;">
                            <b style="font-size: 16px;">DATA PRIBADI</b>
                        </th>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Nama Lengkap</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_FULLNAME'] ?> </td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Kewarganegaraan</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_KEWARGANEGARAAN'] ?> </td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Tempat/Tanggal Lahir</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle">
                            <?= $realAccount['ACC_TEMPAT_LAHIR'] ?>, 
                            <?= date("d", strtotime($realAccount['ACC_TANGGAL_LAHIR'])) ?> 
                            <?= $tgl_lahir ?>
                            <?= date("Y", strtotime($realAccount['ACC_TANGGAL_LAHIR'])) ?> 
                        </td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">No. Identitas *)</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= implode(" / ", [$realAccount['ACC_TYPE_IDT'], $realAccount['ACC_NO_IDT']]) ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">No. NPWP *)</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_PRIBADI_NPWP'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Jenis Kelamin</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_PRIBADI_KELAMIN'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Nama Ibu Kandung *)</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_PRIBADI_NAMAISTRI'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Status Perkawinan</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_PRIBADI_STSKAWIN'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Nama Istri/Suami *)</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_PRIBADI_NAMAISTRI'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Pendidikan *)</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_PENDIDIKAN'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Alamat Rumah</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_ADDRESS'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">No. Telp Rumah</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_PRIBADI_TLP'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">No. Faksimili Rumah</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_PRIBADI_FAX'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">No. Telp Handphone</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_PRIBADI_HP'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Status Kepemilikan Rumah</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_PRIBADI_STSRMH'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Tujuan Pembukaan Rekening</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_TUJUANBUKA'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Pengalaman Investasi</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_PENGINVT'] ?></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="v-align-middle">
                            Apakah Anda memiliki anggota keluarga yang bekerja di BAPPEBTI/Bursa Berjangka/Kliring Berjangka?
                            <p style="margin: 0px;"><i>Tidak</i></p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" class="v-align-middle">
                            Apakah Anda telah dinyatakan pailit oleh Pengadilan?
                            <p style="margin: 0px;"><i>Tidak</i></p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="break-before section">
            <table class="table no-border">
                <tbody>
                    <tr>
                        <th colspan="3" class="v-align-middle" style="padding: 6px; background-color: #edebe0;">
                            <b style="font-size: 16px;">PIHAK YANG DIHUBUNGI DALAM KEADAAN DARURAT</b>
                        </th>
                    </tr>
                    <tr>
                        <td colspan="3" class="v-align-middle">Dalam keadaan darurat, pihak yang dapat dihubungi</td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Nama Lengkap</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_DRRT_NAMA'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Alamat Rumah</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_DRRT_ALAMAT'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Kode Pos</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_DRRT_ZIP'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">No. Telp</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_DRRT_TLP'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Hubungan dengan anda</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_DRRT_HUB'] ?></td>
                    </tr>
                </tbody>
                <tbody>
                    <tr>
                        <th colspan="3" class="v-align-middle" style="padding: 6px; background-color: #edebe0;">
                            <b style="font-size: 16px;">PEKERJAAN</b>
                        </th>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Pekerjaan</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_KRJ_TYPE'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Nama Perusahaan</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_KRJ_NAMA'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Bidang Usaha</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_KRJ_BDNG'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Jabatan</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_KRJ_JBTN'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Lama Bekerja</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_KRJ_LAMA'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Kantor Sebelumnya</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_KRJ_LAMASBLM'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Alamat Kantor</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_KRJ_ALAMAT'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Kode Pos</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_KRJ_ZIP'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">No. Telp kantor</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_KRJ_TLP'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">No. Faksimili</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_KRJ_FAX'] ?></td>
                    </tr>
                </tbody>
                <tbody>
                    <tr>
                        <th colspan="3" class="v-align-middle" style="padding: 6px; background-color: #edebe0;">
                            <b style="font-size: 16px;">DAFTAR KEKAYAAN</b>
                        </th>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Sumber Penghasilan</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_KEKYAN_SUMBER_PENGHASILAN'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Penghasilan per tahun</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_KEKYAN'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Rumah Lokasi</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_KEKYAN_RMHLKS'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Nilai NJOP</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_KEKYAN_NJOP'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Deposit Bank</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_KEKYAN_DPST'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Jumlah</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_KEKYAN_NILAI'] ?></td>
                    </tr>
                    <tr>
                        <td width="35%" class="v-align-middle">Lainnya</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle"><?= $realAccount['ACC_F_APP_KEKYAN_LAIN'] ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="break-before section">
            <table class="table no-border">
                <tbody>
                    <tr>
                        <th colspan="3" class="v-align-middle" style="padding: 6px; background-color: #edebe0;">
                            <b style="font-size: 16px;">REKENING BANK NASABAH UNTUK PENYETORAN DAN PENARIKAN MARGIN</b>
                        </th>
                    </tr>
                    <tr>
                        <td colspan="3" class="v-align-middle">
                            Rekening Bank Nasabah Untuk Penyetoran dan Penarikan Margin (hanya rekening dibawah ini yang dapat Saudara pergunakan untuk lalulintas margin)
                        </td>
                    </tr>
                    <?php foreach($userBanks as $bank) : ?>
                        <tr>
                            <td width="35%" class="v-align-middle">Nama Bank</td>
                            <td width="3%" class="v-align-middle">:</td>
                            <td class="v-align-middle"><?= ($bank['MBANK_NAME'] ?? '') ?></td>
                        </tr>
                        <!-- <tr>
                            <td width="35%" class="v-align-middle">Cabang</td>
                            <td width="3%" class="v-align-middle">:</td>
                            <td class="v-align-middle"><?= ($bank['MBANK_BRANCH'] ?? '') ?></td>
                        </tr> -->
                        <tr>
                            <td width="35%" class="v-align-middle">Nomor A/C</td>
                            <td width="3%" class="v-align-middle">:</td>
                            <td class="v-align-middle"><?= ($bank['MBANK_ACCOUNT'] ?? '') ?></td>
                        </tr>
                        <!-- <tr>
                            <td width="35%" class="v-align-middle">Jenis Rekening</td>
                            <td width="3%" class="v-align-middle">:</td>
                            <td class="v-align-middle"><?= ($bank['MBANK_TYPE'] ?? '') ?></td>
                        </tr> -->
                    <?php endforeach; ?>
                </tbody>
            </table>
            <table class="table no-border">
                <tbody>
                    <tr>
                        <th colspan="3" class="v-align-middle" style="padding: 6px; background-color: #edebe0;">
                            <b style="font-size: 16px;">DOKUMEN YANG DILAMPIRKAN</b>
                        </th>
                    </tr>
                    <tr>
                        <td width="50%" class="v-align-middle"><?= $realAccount['ACC_F_APP_FILE_TYPE'] ?></td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle" style="text-align: left !important;">Hasil Scan/photo (Lampirkan)</td>
                    </tr>
                    <tr>
                        <td width="50%" class="v-align-middle">Dokumen Pendukung Lainnya</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle">Hasil Scan/photo (Lampirkan)</td>
                    </tr>
                    <tr>
                        <td width="50%" class="v-align-middle">Foto Terbaru</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle">Hasil Scan/photo (Lampirkan)</td>
                    </tr>
                    <tr>
                        <td width="50%" class="v-align-middle">Foto Identitas (<?= $realAccount['ACC_TYPE_IDT'] ?>)</td>
                        <td width="3%" class="v-align-middle">:</td>
                        <td class="v-align-middle">Hasil Scan/photo (Lampirkan)</td>
                    </tr>
                </tbody>
            </table>
            <table class="table no-border">
                <tbody>
                    <tr>
                        <th colspan="3" class="v-align-middle" style="padding: 6px; background-color: #edebe0;">
                            <b style="font-size: 16px;">PERNYATAAN KEBENARAN DAN TANGGUNG JAWAB</b>
                        </th>
                    </tr>
                    <tr>
                        <td colspan="3" class="v-align-middle">
                            Dengan mengisi kolom “YA” di bawah ini, saya menyatakan bahwa semua informasi dan semua dokumen yang saya lampirkan dalam APLIKASI PEMBUKAAN REKENING TRANSAKSI SECARA ELEKTRONIK ONLINE adalah benar dan tepat, Saya akan bertanggung jawab penuh apabila dikemudian hari terjadi sesuatu hal sehubungan dengan ketidakbenaran data yang saya berikan.
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" class="v-align-middle">
                            <p style="margin: 0px;">Pernyataan Kebenaran dan Tanggung Jawab: Ya</p>
                            <p style="margin-bottom: 0px;">Menyatakan pada Tanggal (DD/MM/YYYY): <?= date("d/m/Y", strtotime($realAccount['ACC_F_APP_DATE'])) ?></p>
                            <p style="margin-bottom: 0px;">*) Pilih salah satu</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </body>
</html>