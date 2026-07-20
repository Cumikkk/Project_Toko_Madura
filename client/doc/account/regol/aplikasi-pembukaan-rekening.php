<?php

use App\Models\FileUpload;
use App\Models\Helper;
use App\Models\User;
use App\Models\Country;

$myBanks = App\Models\User::myBank($user['MBR_ID']);
$isRequiredNpwp = App\Models\Regol::isRequiredNpwp($realAccount['ACC_CDD'] ?? 1);
$_SESSION['modal'] = ['create-bank'];
?>
<style>
    .row_dash {
        border-bottom: 1px dashed #ddd;
        padding-bottom: 10px;
        margin-bottom: .5rem !important;
    }
    .dash_bottom {
        font-size: 14px;
        margin-bottom: .5rem !important;
    }
</style>

<form method="post" enctype="multipart/form-data" id="form-aplikasi-pembukaan-rekening">
    <input type="hidden" name="csrf_token" value="<?= uniqid(); ?>">
    <div class="card">
        <div class="card-body">
            <div class="text-center"><h5>APLIKASI PEMBUKAAN REKENING TRANSAKSI SECARA ELEKTRONIK ONLINE</h5></div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header text-center">
                            DATA PRIBADI
                        </div>
                        <div class="card-body mb-3">
                            <div class="row dash_bottom mb-1">
                                <label class="col-sm-5 col-form-label fw-bold">Nama Lengkap</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control" disabled value="<?= $realAccount['ACC_FULLNAME']; ?>">
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <label for="datpri_kwngrn" class="col-sm-5 col-form-label fw-bold">Kewarganegaraan</label>
                                <div class="col-sm-7">
                                    <select name="datpri_kwngrn" id="datpri_kwngrn" class="form-select" required>
                                        <option value="" selected disabled>Select</option>
                                        <?php foreach(Country::countries() as $country) : ?>
                                            <option value="<?= $country['COUNTRY_NAME'] ?>" <?= (strtolower($country['COUNTRY_NAME']) == strtolower(retnull("ACC_F_APP_KEWARGANEGARAAN", "indonesia")))? "selected" : "" ?>><?= $country['COUNTRY_NAME'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row dash_bottom mb-1">
                                <label class="col-sm-5 col-form-label fw-bold">Tempat / Tanggal Lahir</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control" disabled value="<?= $realAccount['ACC_TEMPAT_LAHIR']; ?>, <?= date("Y-m-d", strtotime($realAccount['ACC_TANGGAL_LAHIR'])) ?>">
                                </div>
                            </div>
                            <div class="row dash_bottom mb-1">
                                <label class="col-sm-5 col-form-label fw-bold">Jenis Identitas</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control" disabled value="<?= $realAccount['ACC_TYPE_IDT']; ?>, <?= $realAccount['ACC_NO_IDT'] ?>">
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <label for="app_npwp" class="col-sm-5 col-form-label fw-bold <?= $isRequiredNpwp ? 'required' : '' ?>">No. NPWP</label>
                                <div class="col-sm-7">
                                    <input type="text" data-kind="npwp" inputmode="numeric" autocomplete="off" placeholder="No. Npwp" id="app_npwp" name="app_npwp" value="<?= $realAccount['ACC_F_APP_PRIBADI_NPWP'] ?>" class="form-control" <?= $isRequiredNpwp ? 'required' : '' ?>>
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <label for="app_gender" class="col-sm-5 col-form-label fw-bold">Jenis kelamin<span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <select name="app_gender" id="app_gender" class="form-select" required>
                                        <?php foreach(['Laki-laki', 'Perempuan'] as $gender) : ?>
                                            <option value="<?= $gender ?>" <?= (strtolower($realAccount['ACC_F_APP_PRIBADI_KELAMIN'] ?? $user['MBR_JENIS_KELAMIN'] ?? "") == strtolower($gender))? "selected" : ""; ?>><?= $gender ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <label for="app_nama_ibu" class="col-sm-5 col-form-label fw-bold">Nama Ibu Kandung<span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <input type="text" data-kind="nama" inputmode="text" autocomplete="off" placeholder="Nama Ibu Kandung" id="app_nama_ibu" name="app_nama_ibu" value="<?= $realAccount['ACC_F_APP_PRIBADI_IBU'] ?>" class="form-control" required>
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <label for="app_status_perkawinan" class="col-sm-5 col-form-label fw-bold">Status Perkawinan<span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <select name="app_status_perkawinan" id="app_status_perkawinan" class="form-select" required>
                                        <?php foreach(["Tidak Kawin", "Kawin", "Janda", "Duda"] as $status_perkawinan) : ?>
                                            <option value="<?= $status_perkawinan ?>" <?= (strtolower($realAccount['ACC_F_APP_PRIBADI_STSKAWIN'] ?? "") == strtolower($status_perkawinan))? "selected" : ""; ?>><?= $status_perkawinan ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row dash_bottom mb-1" id="tr_acc_app_nama_istri">
                                <label for="acc_app_nama_istri" class="col-sm-5 col-form-label fw-bold">Nama Istri/Suami</label>
                                <div class="col-sm-7">
                                    <input type="text" data-kind="nama" inputmode="text" name="acc_app_nama_istri" class="form-control" id="acc_app_nama_istri" value="<?= $realAccount['ACC_F_APP_PRIBADI_NAMAISTRI']; ?>">
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <label for="app_status_perkawinan" class="col-sm-5 col-form-label fw-bold">Pendidikan<span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <select name="datpri_pendidikan" class="form-control">
                                        <option value disabled selected>Pilih Salah Satu</option>
                                        <?php
                                            foreach (App\Models\Regol::$pendidikanTerakhir as $pnddkvl) {
                                                echo '<option value="'.$pnddkvl.'" '.((retnull("ACC_F_APP_PENDIDIKAN") == $pnddkvl) ? 'selected' : '').'>'.$pnddkvl.'</option>';
                                            }
                                        ?>
                                        <option data-vlnny="Pendidikan Lainnya" <?= (((!in_array(retnull("ACC_F_APP_PENDIDIKAN"), App\Models\Regol::$pendidikanTerakhir)) && (!empty(retnull("ACC_F_APP_PENDIDIKAN")))) ? 'selected' : '') ?> value="<?= (((!in_array(retnull("ACC_F_APP_PENDIDIKAN"), App\Models\Regol::$pendidikanTerakhir)) && (!empty(retnull("ACC_F_APP_PENDIDIKAN")))) ? retnull("ACC_F_APP_PENDIDIKAN") : 'Lainnya') ?>">Lainnya<?= (((!in_array(retnull("ACC_F_APP_PENDIDIKAN"), App\Models\Regol::$pendidikanTerakhir)) && (!empty(retnull("ACC_F_APP_PENDIDIKAN")))) ? "('".retnull("ACC_F_APP_PENDIDIKAN")."')" : '') ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="row dash_bottom mb-1">
                                <label class="col-sm-5 col-form-label fw-bold">Provinsi</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control" disabled value="<?= $realAccount['ACC_PROVINCE']; ?>">
                                </div>
                            </div>
                            <div class="row dash_bottom mb-1">
                                <label class="col-sm-5 col-form-label fw-bold">Kabupaten / Kota</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control" disabled value="<?= $realAccount['ACC_REGENCY']; ?>">
                                </div>
                            </div>
                            <div class="row dash_bottom mb-1">
                                <label class="col-sm-5 col-form-label fw-bold">Kecamatan</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control" disabled value="<?= $realAccount['ACC_DISTRICT']; ?>">
                                </div>
                            </div>
                            <div class="row dash_bottom mb-1">
                                <label class="col-sm-5 col-form-label fw-bold">Desa</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control" disabled value="<?= $realAccount['ACC_VILLAGE']; ?>">
                                </div>
                            </div>
                            <div class="row dash_bottom mb-1">
                                <label class="col-sm-5 col-form-label fw-bold">Kode Pos</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control" disabled value="<?= $realAccount['ACC_ZIPCODE']; ?>">
                                </div>
                            </div>
                            <div class="row dash_bottom mb-1">
                                <label class="col-sm-5 col-form-label fw-bold">Alamat Lengkap</label>
                                <div class="col-sm-7">
                                    <textarea class="form-control" readonly rows="3"><?= $realAccount['ACC_ADDRESS'] ?></textarea>
                                </div>
                            </div>
                            <div class="row mb-25">
                                <div class="col-xs-6">
                                    <label class="col-form-label fw-bold">RT</label>
                                    <input type="text" class="form-control" disabled value="<?= $realAccount['ACC_RT']; ?>" skip>
                                </div>
                                <div class="col-xs-6">
                                    <label class="col-form-label fw-bold">RW</label>
                                    <input type="text" class="form-control" disabled value="<?= $realAccount['ACC_RW']; ?>" skip>
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <label for="app_telepon_rumah" class="col-sm-5 col-form-label fw-bold">No. Telp Rumah</label>
                                <div class="col-sm-7">
                                    <input type="text" data-kind="phone-optional" inputmode="tel" autocomplete="off" placeholder="No. Telp Rumah" id="app_telepon_rumah" name="app_telepon_rumah" value="<?= $realAccount['ACC_F_APP_PRIBADI_TLP'] ?? 0 ?>" class="form-control">
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <label for="app_faksimili_rumah" class="col-sm-5 col-form-label fw-bold">No. Faksimili Rumah</label>
                                <div class="col-sm-7">
                                    <input type="text" data-kind="phone-optional" inputmode="tel" autocomplete="off" placeholder="No. Faksimili Rumah" id="app_faksimili_rumah" name="app_faksimili_rumah" value="<?= $realAccount['ACC_F_APP_PRIBADI_FAX'] ?? 0 ?>" class="form-control">
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <label for="app_no_handphone" class="col-sm-5 col-form-label fw-bold">No. Telp Handphone<span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <div class="row">
                                        <div class="col-4">
                                            <select name="app_no_handphone_code" class="input-group-text" id="cntry">
                                                <?php foreach (Country::countries() as $country): ?>
                                                    <option value="<?= $country['COUNTRY_PHONE_CODE'] ?>" <?= ($country['COUNTRY_PHONE_CODE'] == "+62") ? "selected" : ""; ?> data-content="<?= strtolower(substr($country['COUNTRY_CURR'], 0, 2)) ?>">
                                                        <?= $country['COUNTRY_PHONE_CODE'] ?>
                                                        <!-- <img src="https://flagsapi.com/<?= strtoupper(substr($country['COUNTRY_CURR'], 0, 2)) ?>/flat/64.png"> -->
                                                        <!-- <span class="flag-icon flag-icon-<?= strtolower(substr($country['COUNTRY_CURR'], 0, 2)) ?>"></span> -->
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-8">
                                            <input type="text" data-kind="phone" inputmode="tel" autocomplete="off" placeholder="8xxxxxxxxx" id="app_no_handphone" name="app_no_handphone" value="<?= ($realAccount['ACC_F_APP_PRIBADI_HP'] == 0)? $user['MBR_PHONE'] : ($realAccount['ACC_F_APP_PRIBADI_HP'] ?? 0); ?>" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <label for="app_status_rumah" class="col-sm-5 col-form-label fw-bold">Status Kepemilikan Rumah<span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <div>
                                        <select id="app_status_rumah" class="form-select" required>
                                            <?php foreach(["Pribadi", "Keluarga", "Sewa/Kontrak", "Lainnya"] as $status_rumah) : ?>
                                                <option value="<?= $status_rumah ?>" <?= (strtolower($realAccount['ACC_F_APP_PRIBADI_STSRMH'] ?? "") == strtolower($status_rumah))? "selected" : ""; ?>><?= $status_rumah ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mt-2" id="div_app_status_rumah">
                                        <input type="text" class="form-control" name="app_status_rumah" placeholder="Lainnya..." value="<?= $realAccount['ACC_F_APP_PRIBADI_STSRMH']; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <label for="app_tujuan_pembukaan_rek" class="col-sm-5 col-form-label fw-bold">Tujuan Pembukaan Rekening<span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <div>
                                        <select id="app_tujuan_pembukaan_rek" class="form-select" required>
                                            <?php foreach(App\Models\Regol::$tujuanPembukaan as $tujuan_pembukaan) : ?>
                                                <option value="<?= $tujuan_pembukaan ?>" <?= (strtolower($realAccount['ACC_F_APP_TUJUANBUKA'] ?? "") == strtolower($tujuan_pembukaan) || strtolower($tujuan_pembukaan) == "lainnya")? "selected" : ""; ?>><?= $tujuan_pembukaan ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mt-2" id="div_app_tujuan_pembukaan_rek" data-value="<?= $realAccount['ACC_F_APP_TUJUANBUKA']; ?>">
                                        <input type="text" class="form-control" name="app_tujuan_pembukaan_rek" placeholder="Lainnya...">
                                    </div>
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <label for="app_pengalaman_investasi" class="col-sm-5 col-form-label fw-bold">Pengalaman Investasi<span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <select name="app_pengalaman_investasi" id="app_pengalaman_investasi" class="form-select" required>
                                        <?php foreach(["Ya", "Tidak"] as $pengalaman_investasi) : ?>
                                            <option value="<?= $pengalaman_investasi ?>" <?= (strtolower($realAccount['ACC_F_APP_PENGINVT'] ?? "") == strtolower($pengalaman_investasi))? "selected" : ""; ?>><?= $pengalaman_investasi ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row dash_bottom" id="div_app_pengalaman_investasi">
                                <label for="bidang_investasi" class="col-sm-5 col-form-label fw-bold">Bidang<span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <input type="text" name="bidang_investasi" class="form-control" id="bidang_investasi" value="<?= $realAccount['ACC_F_APP_PENGINVT_BIDANG']; ?>">
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <div class="col-sm-12">
                                    <div class="form-check">
                                        <input class="form-check-input" name="app_anggota_berjangka" id="app_anggota_berjangka" type="checkbox" required disabled checked>
                                        <label class="form-check-label" for="app_anggota_berjangka">
                                            Saya menyetujui bahwa tidak memiliki anggota keluarga yang<br>bekerja di BAPPEBTI / Bursa Berjangka / Kliring Berjangka
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <div class="col-sm-12">
                                    <div class="form-check">
                                        <input class="form-check-input" name="app_pailit" id="app_pailit" type="checkbox" required disabled checked>
                                        <label class="form-check-label" for="app_pailit">
                                            Saya menyetujui bahwa tidak dinyatakan pailit oleh Pengadilan
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header text-center">
                            PIHAK YANG DAPAT DIHUBUNGI DALAM KEADAAN DARURAT
                        </div>
                        <div class="card-body">
                            <small>Dalam keadaan darurat, pihak yang dapat dihubungi</small>
                            <div class="row mt-2 dash_bottom">
                                <label for="app_darurat_nama" class="col-sm-5 col-form-label fw-bold">Nama Lengkap<span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <input type="text" data-kind="nama" inputmode="text" autocomplete="off" placeholder="Nama Lengkap" id="app_darurat_nama" name="app_darurat_nama" value="<?= $realAccount['ACC_F_APP_DRRT_NAMA'] ?>" class="form-control" required>
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <label for="app_darurat_nama" class="col-sm-5 col-form-label fw-bold">Alamat Rumah<span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <input type="text" autocomplete="off" placeholder="Alamat" id="app_darurat_alamat" name="app_darurat_alamat" value="<?= $realAccount['ACC_F_APP_DRRT_ALAMAT'] ?>" class="form-control" required>
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <label for="app_darurat_kodepos" class="col-sm-5 col-form-label fw-bold">Kode Pos</label>
                                <div class="col-sm-7">
                                    <input type="text" inputmode="numeric" autocomplete="off" placeholder="Kode Pos" id="app_darurat_kodepos" name="app_darurat_kodepos" value="<?= $realAccount['ACC_F_APP_DRRT_ZIP'] ?>" class="form-control">
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <label for="app_darurat_telepon" class="col-sm-5 col-form-label fw-bold">No. Telepon<span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <input type="text" data-kind="phone" inputmode="tel" autocomplete="off" placeholder="No. Telp" id="app_darurat_telepon" name="app_darurat_telepon" value="<?= $realAccount['ACC_F_APP_DRRT_TLP'] ?>" class="form-control" required>
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <label for="app_darurat_hubungan" class="col-sm-5 col-form-label fw-bold">Hubungan dengan anda<span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <select name="app_darurat_hubungan" id="app_darurat_hubungan" class="form-control" required>
                                        <?php foreach(App\Models\Regol::$jenisHubunganPihakDarurat as $app_darurat_hubungan) : ?>
                                            <option value="<?= $app_darurat_hubungan ?>" <?= (strtolower($realAccount['ACC_F_APP_DRRT_HUB'] ?? "") == strtolower($app_darurat_hubungan))? "selected" : ""; ?>><?= $app_darurat_hubungan ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mt-2">
                        <div class="card-header text-center">
                            PEKERJAAN
                        </div>
                        <div class="card-body">
                            <div class="row dash_bottom">
                                <label for="app_pekerjaan" class="col-sm-5 col-form-label fw-bold">Pekerjaan<span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <select id="app_pekerjaan" class="form-control" required>
                                        <option value="">Please Select</option>
                                        <?php foreach(App\Models\Regol::$listPekerjaan as $pekerjaan) : ?>
                                            <option value="<?= $pekerjaan ?>" <?= (strtolower($realAccount['ACC_F_APP_KRJ_TYPE'] ?? "") == strtolower($pekerjaan))? "selected" : ""; ?>><?= $pekerjaan ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="mt-2" id="div_app_pekerjaan">
                                        <input type="text" class="form-control" name="app_pekerjaan" placeholder="Lainnya..." value="<?= $realAccount['ACC_F_APP_KRJ_TYPE']; ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <label for="app_nama_perusahaan" class="col-sm-5 col-form-label fw-bold">Nama Perusahaan<span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <input type="text" autocomplete="off" placeholder="Nama Perusahaan" id="app_nama_perusahaan" name="app_nama_perusahaan" value="<?= $realAccount['ACC_F_APP_KRJ_NAMA'] ?>" class="form-control" required>
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <label for="app_bidang_usaha" class="col-sm-5 col-form-label fw-bold">Bidang Usaha<span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <input type="text" autocomplete="off" placeholder="Bidang Usaha" id="app_bidang_usaha" name="app_bidang_usaha" value="<?= $realAccount['ACC_F_APP_KRJ_BDNG'] ?>" class="form-control" required>
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <label for="app_jabatan_pekerjaan" class="col-sm-5 col-form-label fw-bold">Jabatan<span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <input type="text" autocomplete="off" placeholder="Nama Jabatan" id="app_jabatan_pekerjaan" name="app_jabatan_pekerjaan" value="<?= $realAccount['ACC_F_APP_KRJ_JBTN'] ?>" class="form-control" required>
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <label for="app_lama_bekerja" class="col-sm-5 col-form-label fw-bold">Lama Bekerja<span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <input type="text" autocomplete="off" placeholder="Contoh: 3 Tahun" id="app_lama_bekerja" name="app_lama_bekerja" value="<?= $realAccount['ACC_F_APP_KRJ_LAMA'] ?>" class="form-control" required>
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <label for="app_lama_bekerja_sebelumnya" class="col-sm-5 col-form-label fw-bold">Lama Bekerja (Kantor Sebelumnya)<span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <input type="text" autocomplete="off" placeholder="Contoh: 3 Tahun" id="app_lama_bekerja_sebelumnya" name="app_lama_bekerja_sebelumnya" value="<?= $realAccount['ACC_F_APP_KRJ_LAMASBLM'] ?>" class="form-control" required>
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <label for="app_alamat_kantor" class="col-sm-5 col-form-label fw-bold">Alamat Kantor<span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <input type="text" autocomplete="off" placeholder="Alamat Kantor" id="app_alamat_kantor" name="app_alamat_kantor" value="<?= $realAccount['ACC_F_APP_KRJ_ALAMAT'] ?>" class="form-control" required>
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <label for="app_kodepos_kantor" class="col-sm-5 col-form-label fw-bold">Kode Pos</label>
                                <div class="col-sm-7">
                                    <input type="text" data-kind="kodepos" inputmode="numeric" autocomplete="off" inputmode="numeric" autocomplete="off" placeholder="Kode Pos" id="app_kodepos_kantor" name="app_kodepos_kantor" value="<?= $realAccount['ACC_F_APP_KRJ_ZIP'] ?>" class="form-control">
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <label for="app_no_telp_kantor" class="col-sm-5 col-form-label fw-bold">No. Telp Kantor</label>
                                <div class="col-sm-7">
                                    <input type="text" data-kind="phone-optional" inputmode="tel" autocomplete="off" placeholder="No. Telp Kantor" id="app_no_telp_kantor" name="app_no_telp_kantor" value="<?= $realAccount['ACC_F_APP_KRJ_TLP'] ?>" class="form-control">
                                </div>
                            </div>
                            <div class="row">
                                <label for="app_nomor_fax_kantor" class="col-sm-5 col-form-label fw-bold">No. Faksimili Kantor</label>
                                <div class="col-sm-7">
                                    <input type="text" data-kind="phone-optional" inputmode="tel" autocomplete="off" placeholder="No. Faksimili" id="app_nomor_fax_kantor" name="app_nomor_fax_kantor" value="<?= $realAccount['ACC_F_APP_KRJ_FAX'] ?>" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mt-2">
                        <div class="card-header text-center">
                            DAFTAR KEKAYAAN
                        </div>
                        <div class="card-body">
                            <div class="row dash_bottom">
                                <label for="app_penghasilan" class="col-sm-5 col-form-label fw-bold">Sumber Penghasilan<span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <select name="dftrkkyan_sumpeng" class="form-control">
                                        <option value disabled selected>Pilih Salah Satu</option>
                                        <?php
                                            foreach (App\Models\Regol::$sumberPenghasilan as $pnghslvl) {
                                                echo '<option value="'.$pnghslvl.'" '.((retnull("ACC_F_APP_KEKYAN_SUMBER_PENGHASILAN") == $pnghslvl) ? 'selected' : '').'>'.$pnghslvl.'</option>';
                                            }
                                        ?>
                                        <option data-vlnny="Penghasilan Lainnya" <?= (((!in_array(retnull("ACC_F_APP_KEKYAN_SUMBER_PENGHASILAN"), App\Models\Regol::$sumberPenghasilan)) && (!empty(retnull("ACC_F_APP_KEKYAN_SUMBER_PENGHASILAN")))) ? 'selected' : '') ?> value="<?= (((!in_array(retnull("ACC_F_APP_KEKYAN_SUMBER_PENGHASILAN"), App\Models\Regol::$sumberPenghasilan)) && (!empty(retnull("ACC_F_APP_KEKYAN_SUMBER_PENGHASILAN")))) ? retnull("ACC_F_APP_KEKYAN_SUMBER_PENGHASILAN") : 'Lainnya') ?>">Lainnya<?= (((!in_array(retnull("ACC_F_APP_KEKYAN_SUMBER_PENGHASILAN"), App\Models\Regol::$sumberPenghasilan)) && (!empty(retnull("ACC_F_APP_KEKYAN_SUMBER_PENGHASILAN")))) ? "('".retnull("ACC_F_APP_KEKYAN_SUMBER_PENGHASILAN")."')" : '') ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <label for="app_penghasilan" class="col-sm-5 col-form-label fw-bold">Penghasilan Per tahun<span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <select name="app_penghasilan" id="app_penghasilan" class="form-control" required>
                                        <?php foreach(App\Models\Regol::$listPendapatan as $penghasilan) : ?>
                                            <option value="<?= $penghasilan ?>" <?= (strtolower($realAccount['ACC_F_APP_KEKYAN'] ?? "") == strtolower($penghasilan))? "selected" : ""; ?>><?= $penghasilan ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <label for="app_lokasi_rumah" class="col-sm-5 col-form-label fw-bold">Lokasi Rumah<span class="text-danger">*</span></label>
                                <div class="col-sm-7">
                                    <input type="text" autocomplete="off" placeholder="Lokasi Rumah" id="app_lokasi_rumah" name="app_lokasi_rumah" value="<?= $realAccount['ACC_F_APP_KEKYAN_RMHLKS'] ?? "" ?>" class="form-control" required>
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <label for="app_nilai_njop" class="col-sm-5 col-form-label fw-bold">Nilai NJOP</label>
                                <div class="col-sm-7">
                                    <select name="app_nilai_njop" id="app_nilai_njop" class="form-control">
                                        <option  value="">Pilih</option>
                                        <?php foreach(App\Models\Regol::$kurang1mLebih5m as $listkurang1mLebih5m) : ?>
                                            <option value="<?= $listkurang1mLebih5m ?>" <?= (strtolower($realAccount['ACC_F_APP_KEKYAN_NJOP'] ?? "") == strtolower($listkurang1mLebih5m))? "selected" : ""; ?>><?= $listkurang1mLebih5m ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <!-- <input type="number" autocomplete="off" placeholder="Nilai NJOP" name="app_nilai_njop" id="app_nilai_njop" value="<?= $realAccount['ACC_F_APP_KEKYAN_NJOP'] ?? "" ?>" class="form-control"> -->
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <label for="app_deposit_bank" class="col-sm-5 col-form-label fw-bold">Deposit Bank</label>
                                <div class="col-sm-7">
                                    <select name="app_deposit_bank" id="app_deposit_bank" class="form-control">
                                        <option value="">Pilih</option>
                                        <?php foreach(App\Models\Regol::$kurang500jtLebih2m as $listkurang500jtLebih2m) : ?>
                                            <option value="<?= $listkurang500jtLebih2m ?>" <?= (strtolower($realAccount['ACC_F_APP_KEKYAN_DPST'] ?? "") == strtolower($listkurang500jtLebih2m))? "selected" : ""; ?>><?= $listkurang500jtLebih2m ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <!-- <input type="number" autocomplete="off" placeholder="Deposit Bank" name="app_deposit_bank" id="app_deposit_bank" value="<?= $realAccount['ACC_F_APP_KEKYAN_DPST'] ?? "" ?>" class="form-control"> -->
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <label for="app_kekayaan_lainnya" class="col-sm-5 col-form-label fw-bold">Lainnya</label>
                                <div class="col-sm-7">
                                    <select name="app_kekayaan_lainnya" id="app_kekayaan_lainnya" class="form-control">
                                        <option value="">Pilih</option>
                                        <?php foreach(App\Models\Regol::$kurang1mLebih5m as $listkurang1mLebih5m) : ?>
                                            <option value="<?= $listkurang1mLebih5m ?>" <?= (strtolower($realAccount['ACC_F_APP_KEKYAN_LAIN'] ?? "") == strtolower($listkurang1mLebih5m))? "selected" : ""; ?>><?= $listkurang1mLebih5m ?></option>
                                        <?php endforeach; ?>
                                        <option>Tidak ada</option>
                                    </select>
                                    <!-- <input type="text" autocomplete="off" placeholder="Lainnya" name="app_kekayaan_lainnya" id="app_kekayaan_lainnya" value="<?= $realAccount['ACC_F_APP_KEKYAN_LAIN'] ?? "" ?>" class="form-control"> -->
                                </div>
                            </div>
                            <div class="row dash_bottom">
                                <label for="app_jumlah_kekayaan_lainnya" class="col-sm-5 col-form-label fw-bold">Jumlah</label>
                                <div class="col-sm-7">
                                    <input type="number" autocomplete="off" placeholder="Jumlah" name="app_jumlah_kekayaan_lainnya" id="app_jumlah_kekayaan_lainnya" value="<?= $realAccount['ACC_F_APP_KEKYAN_JMLH'] ?? "" ?>" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12 mb-3">
                     <div class="card mb-3">
                        <div class="card-header text-center">
                            REKENING BANK NASABAH UNTUK PENYETORAN DAN PENARIKAN MARGIN
                        </div>
                        <div class="card-body mb-3">
                            <p>
                                Rekening Bank Nasabah Untuk Penyetoran dan Penarikan Margin 
                                (hanya rekening dibawah ini yang dapat Saudara pergunakan untuk lalulintas margin)
                            </p>
                            <div class="row">
                                <?php for($i = 0; $i < 2; $i++) : ?>
                                    <?php $mbank = isset($myBanks[$i])? $myBanks[$i] : []; ?>
                                    <?php $isRequired = ($i == 0); ?>
                                    <div class="col-md-6 mb-3 px-4">
                                        <h5 class="card-title">Bank <?= $i + 1; ?></h5>
                                        <hr>
                                        <div class="row mt-2">
                                            <div class="col-md-12 mb-3">
                                                <label for="" class="form-label <?= ($isRequired)? "required" : ""; ?>">Nama Bank</label>
                                                <select name="bank_name<?= $i+1; ?>" class="form-control form-select input-sm" <?= ($isRequired)? "required" : ""; ?>>
                                                    <option value="">Pilih</option>
                                                    <?php foreach(App\Models\BankList::all() as $bank) : ?>
                                                        <option value="<?= $bank['BANKLST_NAME'] ?>" <?= ($mbank && $mbank['MBANK_NAME'] == $bank['BANKLST_NAME'])? "selected" : ""; ?>><?= $bank['BANKLST_NAME'] ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-12 col-md-6 mb-3">
                                                <label for="" class="form-label text-wrap <?= ($isRequired)? "required" : ""; ?>">Nama Pemilik Rekening</label>
                                                <input type="text" class="form-control" value="<?= $mbank['MBANK_HOLDER'] ?? $user['MBR_NAME']; ?>" disabled <?= ($isRequired)? "required" : ""; ?>>
                                            </div>
                                            <div class="col-md-12 col-md-6 mb-3">
                                                <label for="" class="form-label text-wrap <?= ($isRequired)? "required" : ""; ?>">No. Rekening</label>
                                                <input type="text" data-kind="bankaccount" inputmode="numeric" autocomplate="off" class="form-control input-sm" name="bank_number<?= $i+1; ?>" value="<?= $mbank['MBANK_ACCOUNT'] ?? ""; ?>" placeholder="Nomor Rekening" <?= ($isRequired)? "required" : ""; ?>>
                                            </div>
                                        </div>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card component-jquery-uploader">
                        <div class="card-header text-center">
                            DOKUMEN YANG DILAMPIRKAN
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xxl-4 col-sm-6 mb-3">
                                    <label for="app_image_1" class="form-label required" style="width: auto; line-height: normal;">
                                        Rekening Koran Bank / <br> 
                                        Tagihan Kartu Kredit / <br> 
                                        Rekening Listrik, Telepon / <br> 
                                        NPWP
                                    </label>
                                    <input type="file" class="dropify" id="app_image_1" name="app_image_1" data-allowed-file-extensions="png jpg jpeg" data-default-file="<?= App\Factory\FileUploadFactory::aws()->awsFile($realAccount['ACC_F_APP_FILE_IMG'] ?? "") ?>" <?= empty($realAccount['ACC_F_APP_FILE_IMG'])? "required" : "skip" ?>>
                                </div>
                                <div class="col-xxl-4 col-sm-6 mb-3">
                                    <label for="app_foto_terbaru" class="form-label required">Foto Terbaru (Selfie)</label>
                                    <input type="file" class="dropify" id="app_foto_terbaru" name="app_foto_terbaru" <?= empty($realAccount['ACC_F_APP_FILE_FOTO'])? "required" : "skip" ?>
                                        data-max-file-size="4M"
                                        data-min-width="480"
                                        data-min-height="640"
                                        data-show-remove="false"
                                        data-allowed-file-extensions="png jpg jpeg" 
                                        data-default-file="<?= App\Factory\FileUploadFactory::aws()->awsFile($realAccount['ACC_F_APP_FILE_FOTO'] ?? ""); ?>">
                                    <div class="border rounded-3 bg-light p-3 mt-2">
                                        <div class="fw-bold text-primary small mb-2">Keterangan Foto Selfie</div>
                                        <ul class="small text-muted mb-0 ps-3">
                                            <li>Minimal ukuran file 100KB dan maksimal 4MB</li>
                                            <li>Minimal dimensi file 480x640 px</li>
                                            <li>Gunakan foto terbaru dengan wajah terlihat jelas</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-xxl-4 col-sm-6 mb-3">
                                    <label for="app_foto_identitas" class="form-label required">Foto KTP</label>
                                    <input type="file" class="dropify" id="app_foto_identitas" name="app_foto_identitas" <?= empty($realAccount['ACC_F_APP_FILE_ID'])? "required" : "skip" ?>
                                        data-max-file-size="2M"
                                        data-min-width="480"
                                        data-min-height="320"
                                        data-show-remove="false"
                                        data-allowed-file-extensions="png jpg jpeg" 
                                        data-default-file="<?= App\Factory\FileUploadFactory::aws()->awsFile($realAccount['ACC_F_APP_FILE_ID'] ?? ""); ?>">
                                    <div class="border rounded-3 bg-light p-3 mt-2">
                                        <div class="fw-bold text-primary small mb-2">Keterangan Foto KTP</div>
                                        <ul class="small text-muted mb-0 ps-3">
                                            <li>Minimal ukuran file 100KB dan maksimal 2MB</li>
                                            <li>Minimal dimensi file 480x320 px</li>
                                            <li>Pastikan seluruh dokumen terlihat jelas dan tidak terpotong</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-xxl-4 col-sm-6 mb-3">
                                    <label for="app_image_3" class="form-label">Dokumen Lainnya</label>
                                    <input type="file" class="dropify" id="app_image_3" name="app_image_3" data-allowed-file-extensions="png jpg jpeg" data-default-file="<?= empty($realAccount['ACC_F_APP_FILE_IMG3'])? "" : App\Factory\FileUploadFactory::aws()->awsFile($realAccount['ACC_F_APP_FILE_IMG3']) ?>">
                                </div>
                                <div class="col-xxl-4 col-sm-6 mb-3">
                                    <label for="app_image_4" class="form-label">Dokumen Lainnya</label>
                                    <input type="file" class="dropify" id="app_image_4" name="app_image_4" data-allowed-file-extensions="png jpg jpeg" data-default-file="<?= empty($realAccount['ACC_F_APP_FILE_IMG4'])? "" : App\Factory\FileUploadFactory::aws()->awsFile($realAccount['ACC_F_APP_FILE_IMG4']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="text-center fw-bold">
                        PERNYATAAN KEBENARAN DAN TANGGUNG JAWAB
                    </div>
                </div>
            </div>
            <p class="mt-3">
                Dengan mengisi kolom “YA” di bawah ini, saya menyatakan bahwa semua informasi dan
                semua dokumen yang saya lampirkan dalam <strong>APLIKASI PEMBUKAAN REKENING
                TRANSAKSI SECARA ELEKTRONIK ONLINE</strong> adalah benar dan tepat, Saya akan
                bertanggung jawab penuh apabila dikemudian hari terjadi sesuatu hal sehubungan
                dengan ketidakbenaran data yang saya berikan. 
            </p>
            <div class="row mt-3">
                <div class="col-6 mt-3">
                    Pernyataan Kebenaran dan Tanggung Jawab<br>
                    <input type="radio" name="aggree" value="Ya" class="form-check-input radio_css" style="margin-top: 10px;" required <?= !empty($realAccount['ACC_F_APP'])? "checked" : "" ?>>
                    <label style="top: 0.5rem;position: relative;margin-bottom: 0;vertical-align: top;margin-right:1.5rem;">Ya</label>
                    <input type="radio" name="aggree" value="Tidak" class="form-check-input radio_css" style="margin-top: 10px;" required>
                    <label style="top: 0.5rem;position: relative;margin-bottom: 0;vertical-align: top;margin-right:1.5rem;">Tidak</label>
                </div>
                <div class="col-6 mt-3">
                    <div class="text-cemter">Menyatakan pada Tanggal</div>
                    <input type="text" name="agg_date" readonly required value="<?= retnull("ACC_F_APP_DATE", date('Y-m-d H:i:s')) ?>" class="form-control text-center mb-3 <?= empty($realAccount['ACC_F_APP_DATE'])? "realtime-date" : "" ?>">
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
</form>

<script>
    (() => {
        // Konfigurasi ringkas: title, pattern, minimal & maksimal KARAKTER (bukan hanya digit)
        const CONFIG = {
            "nama": {
                title: "Nama hanya boleh huruf, spasi, titik, apostrof, dan tanda minus",
                // huruf latin + spasi . ' ’ -
                pattern: "^[A-Za-zÀ-ÖØ-öø-ÿ .,'’\\-]+$",
                min: 2,  
                max: 80
            },
            "bankaccount": { 
                title: "Pastikan nomer bank benar", 
                pattern: "^\\d{10,16}$", 
                min: 10,  
                max: 16 
            },
            "kodepos": { 
                title: "Kode pos harus 5 digit angka", 
                pattern: "^\\d{5}$", 
                min: 5,  
                max: 5 
            },
            "npwp":    { 
                title: "NPWP harus 16 digit angka (tanpa titik/strip)", 
                pattern: "^\\d{16}$", 
                min: 16, 
                max: 16 
            },
            "phone": { 
                title: "Nomor telepon tidak valid",
                pattern: "^\\d{8,13}$",
                min: 9, 
                max: 15 
            },
            "phone-optional": { 
                title: "Nomor telepon tidak valid",
                pattern: "^(0|\\d{8,13})$",
                min: 0, 
                max: 15 
            }
        };


        // --- Filter nilai sesuai tipe ---
        function sanitizeByKind(val, kind) {
            if (kind === "nama") {
                // huruf latin + spasi . ' ’ -
                return (val || "").replace(/[^A-Za-zÀ-ÖØ-öø-ÿ .,'’\-]/g, "");
            }
            if (kind === "phone" || kind === "phone-optional") {
                // angka + opsional satu '+' di depan
                let digitsOnly = (val || "").replace(/\D/g, "");
                // Hapus "62" dari depan jika ada
                if (digitsOnly.startsWith("62")) {
                    digitsOnly = digitsOnly.substring(2);
                }
                return digitsOnly;
            }
            // kodepos & npwp: angka saja
            return (val || "").replace(/\D/g, "");
        }

        // Blokir karakter tidak valid saat KETIK (paste dibersihkan di 'input')
        document.addEventListener("beforeinput", (e) => {
            const el = e.target;
            if (!el.matches('input[data-kind]')) return;

            const kind = el.dataset.kind;
            const t = e.inputType;
            const ch = e.data ?? "";

            if (t === "insertText") {
                if (kind === "nama") {
                    // izinkan huruf latin + spasi . ' ’ -
                    if (!/^[A-Za-zÀ-ÖØ-öø-ÿ .,'’\-]$/.test(ch)) e.preventDefault();
                } else if (kind === "phone") {
                    const selStart = el.selectionStart ?? 0;
                    const insertingPlus = ch === "+";
                    const alreadyPlus = el.value.includes("+");
                    const isDigit = /\d/.test(ch);
                    if (insertingPlus) {
                        if (selStart !== 0 || alreadyPlus) e.preventDefault();
                    } else if (!isDigit) {
                        e.preventDefault();
                    }
                } else {
                    // kodepos/npwp -> hanya digit
                    if (!/\d/.test(ch)) e.preventDefault();
                }
            }
        });

        // Terapkan aturan + balon error bawaan browser
        function applyRules(el, { showNow = false } = {}) {
            const kind = el.dataset.kind;
            const cfg = CONFIG[kind];
            if (!cfg) return;

            // sanitize
            const cleaned = sanitizeByKind(el.value, kind);
            if (cleaned !== el.value) el.value = cleaned;

            // atribut validasi
            el.setAttribute("title", cfg.title);
            el.setAttribute("pattern", cfg.pattern);
            el.setAttribute("minlength", String(cfg.min));
            el.setAttribute("maxlength", String(cfg.max));

            // cek validitas ringan untuk pesan cepat
            const val = el.value;
            let msg = "";
            if (val.length === 0) {
                msg = ""; // biarkan required
            } else if (val.length < cfg.min) {
                msg = `Minimal ${cfg.min} karakter.`;
            } else if (val.length > cfg.max) {
                msg = `Maksimal ${cfg.max} karakter.`;
            } else if (!(new RegExp(cfg.pattern).test(val))) {
                msg = cfg.title;
            }
            el.setCustomValidity(msg);

            if (showNow) el.reportValidity();
        }

        // keyup -> tampilkan balon sekarang
        document.addEventListener("keyup", (e) => {
            if (e.target.matches('input[data-kind]')) applyRules(e.target, { showNow: true });
        });

        // input -> handle paste/autofill
        document.addEventListener("input", (e) => {
            if (e.target.matches('input[data-kind]')) applyRules(e.target);
        });

        // init
        window.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll('input[data-kind]').forEach(el => applyRules(el));
            
        });
    })();
</script>

<script type="text/javascript">
    $(document).ready(function() {

        $('.dropify').dropify();
        $('.dropify').on('click', function() {
            var wrapper = $(this).closest('.dropify-wrapper');
            wrapper?.removeClass('has-error');
            wrapper?.find('.dropify-error').hide();
        })
        
        // $.each(['#app_nilai_njop', '#app_deposit_bank', '#app_kekayaan_lainnya'], (i, val) => {
        //     $(val).on('focus keyup', function() {
        //         let njop = $('#app_nilai_njop').val() || 0;
        //         let deposit = $('#app_deposit_bank').val() || 0;
        //         let kekayaan = $('#app_kekayaan_lainnya').val() || 0;

        //         $('input[name="app_jumlah"]').val(new Intl.NumberFormat("en-US", {style: "currency", currency: "IDR", minimumFractionDigits: 0}).format((parseFloat(njop) + parseFloat(deposit) + parseFloat(kekayaan))))
        //     })
        // });

        $('#form-aplikasi-pembukaan-rekening').on('change', 'input[type="file"]', async function() {
            await validateImageInput(this);
        });
        
        $('#form-aplikasi-pembukaan-rekening').find(':input').on('invalid', function(e){
            $(this).focus(function(et) {
                setTimeout(() => {
                    $(this).get(0).reportValidity();
                }, 1000);
            });
        });

        $('#form-aplikasi-pembukaan-rekening').on('submit', function(event) {
            event.preventDefault();
            let data = Object.fromEntries(new FormData(this).entries());
            Swal.fire({
                text: "Please wait...",
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            })

            $.ajax({
                url: "/ajax/regol/aplikasiPembukaanRekening",
                type: "POST",
                dataType: "json",
                data: new FormData(this),
                contentType: false,
                processData: false,
                cache: false
            }).done(function(resp) {
                if(!resp.success) {
                    Swal.fire(resp.alert);
                    return;
                }
                
                location.href = resp.redirect
            })
        });

        $('#app_status_perkawinan').on('change', function() {
            return ($(this).val()?.toLowerCase() == "kawin")
                ? $('#tr_acc_app_nama_istri').show().find('input').attr('required', 'required')
                : $('#tr_acc_app_nama_istri').hide().find('input').removeAttr('required')
        }).change()

        $('#app_status_rumah').on('change', function() {
            return ($(this).val().toLowerCase() == "lainnya")
                ? $('#div_app_status_rumah').show().find('input').attr('required', 'required').val("")
                : $('#div_app_status_rumah').hide().find('input').removeAttr('required').val($(this).val())
        }).change();

        $('#app_tujuan_pembukaan_rek').on('change', function() {
            let divTujuanPembukaan = $('#div_app_tujuan_pembukaan_rek');
            return ($(this).val().toLowerCase() == "lainnya")
                ? divTujuanPembukaan.show().find('input').attr('required', 'required').val( divTujuanPembukaan.data('value') || "" )
                : divTujuanPembukaan.hide().find('input').removeAttr('required').val($(this).val())
        }).change();

        $('#app_pengalaman_investasi').on('change', function() {
            return ($(this).val().toLowerCase() == "ya")
                ? $('#div_app_pengalaman_investasi').show().find('input').attr('required', 'required')
                : $('#div_app_pengalaman_investasi').hide().find('input').removeAttr('required')
        }).change();

        $('#app_pekerjaan').on('change', function() {
            if(!$(this).val()) {
                if($('#div_app_pekerjaan').show().find('input').val()) {
                    $(this).val("Lainnya")
                    $('#div_app_pekerjaan').show().find('input').attr('required', 'required')
                    return;
                }
            }

            return ($(this).val().toLowerCase() == "lainnya")
                ? $('#div_app_pekerjaan').show().find('input').attr('required', 'required').val("")
                : $('#div_app_pekerjaan').hide().find('input').removeAttr('required').val($(this).val())
        }).change();

        $('#app_dokumen_pendukung').on('change', function() {
            return ($(this).val().toLowerCase() == "lainnya")
                ? $('#div_app_dokumen_pendukung').show().find('input').attr('required', 'required').val("")
                : $('#div_app_dokumen_pendukung').hide().find('input').removeAttr('required').val($(this).val())
        }).change();
    })
</script>