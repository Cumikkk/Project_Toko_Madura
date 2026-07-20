<?php
use App\Models\ProfilePerusahaan;
$profile = App\Models\ProfilePerusahaan::get(); 
?>

<style>
    .dash_bottom {
        font-size: 14px;
        margin-bottom: .5rem !important;
    }
    .unset_padding {
        padding-top: unset !important;
        padding-bottom: unset !important;
    }
</style>
<form method="post" id="form-profile-perusahaan">
    <input type="hidden" name="csrf_token" value="<?= uniqid(); ?>">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-body">
                    <div class="text-center mb-25"><h5>PROFIL PERUSAHAAN PIALANG BERJANGKA</h5></div>
                    <div style="margin:10px 10px 5px 10px;">
                        <div class="row dash_bottom">
                            <label class="col-sm-4 col-form-label unset_padding fw-bold">Nama Perusahaan</label>
                            <div class="col-sm-8">
                                <?= $profile['COMPANY_NAME'] ?? "-" ?>
                            </div>
                        </div>
                        <div class="row dash_bottom">
                            <label class="col-sm-4 col-form-label unset_padding fw-bold">Alamat :</label>
                            <div class="col-sm-8">
                                <?= $profile['OFFICE']['OFC_ADDRESS'] ?>
                            </div>
                        </div>
                        <div class="row dash_bottom">
                            <label class="col-sm-4 col-form-label unset_padding fw-bold">No. Telepon :</label>
                            <div class="col-sm-8">
                                <?php echo $profile['OFFICE']['OFC_PHONE'] ?>
                            </div>
                        </div>
                        <div class="row dash_bottom">
                            <label class="col-sm-4 col-form-label unset_padding fw-bold">No Fax :</label>
                            <div class="col-sm-8">
                                <?php echo $profile['OFFICE']['OFC_FAX'] ?>
                            </div>
                        </div>
                        <div class="row dash_bottom">
                            <label class="col-sm-4 col-form-label unset_padding fw-bold">E-Mail :</label>
                            <div class="col-sm-8">
                                <?php echo $profile['OFFICE']['OFC_EMAIL'] ?>
                            </div>
                        </div>
                        <div class="row dash_bottom">
                            <label class="col-sm-4 col-form-label unset_padding fw-bold">Home Page :</label>
                            <div class="col-sm-8">
                                <?php echo $profile['PROF_HOMEPAGE'] ?>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <h6 class="fw-bold mb-2">Susunan Pengurus Perusahaan</h6>
                    <!-- <div class="table-responsive">
                        <table class="table table-fixed table-hover">
                            <tbody>
                                <tr>
                                    <td width="20%" class="top-align">Komisaris Utama</td>
                                    <td width="3%" class="top-align">:</td>
                                    <td class="top-align text-start"><?= $profile['PROF_KOMISARIS_UTAMA'] ?></td>
                                </tr>
                                <tr>
                                    <td width="20%" class="top-align">Komisaris</td>
                                    <td width="3%" class="top-align">:</td>
                                    <td class="top-align text-start"><?= $profile['PROF_KOMISARIS'] ?></td>
                                </tr>
                                <tr>
                                    <td width="20%" class="top-align">Direktur Utama</td>
                                    <td width="3%" class="top-align">:</td>
                                    <td class="top-align text-start"><?= $profile['PROF_DEWAN_DIREKSI'] ?></td>
                                </tr>
                                <tr>
                                    <td width="20%" class="top-align">Direktur Kepatuhan</td>
                                    <td width="3%" class="top-align">:</td>
                                    <td class="top-align text-start"><?= $profile['PROF_DIREKTUR'] ?></td>
                                </tr>
                                <tr>
                                    <td width="20%" class="top-align">Direktur Operasional</td>
                                    <td width="3%" class="top-align">:</td>
                                    <td class="top-align text-start"><?= $profile['PROF_OPERATIONAL'] ?></td>
                                </tr>
                            </tbody>    
                        </table>
                    </div> -->
                    <div style="margin:10px 10px 5px 10px;">
                        <div class="row dash_bottom">
                            <label class="col-sm-4 col-form-label unset_padding fw-bold">Komisaris Utama :</label>
                            <div class="col-sm-8"><?= $profile['PROF_KOMISARIS_UTAMA'] ?></div>
                        </div>
                        <div class="row dash_bottom">
                            <label class="col-sm-4 col-form-label unset_padding fw-bold">Komisaris :</label>
                            <div class="col-sm-8"><?= $profile['PROF_KOMISARIS'] ?></div>
                        </div>
                        <div class="row dash_bottom">
                            <label class="col-sm-4 col-form-label unset_padding fw-bold">Direktur Utama :</label>
                            <div class="col-sm-8"><?= $profile['PROF_DEWAN_DIREKSI'] ?></div>
                        </div>
                        <div class="row dash_bottom">
                            <label class="col-sm-4 col-form-label unset_padding fw-bold">Direktur Kepatuhan :</label>
                            <div class="col-sm-8"><?= $profile['PROF_DIREKTUR'] ?></div>
                        </div>
                        <div class="row dash_bottom">
                            <label class="col-sm-4 col-form-label unset_padding fw-bold">Direktur Operasional :</label>
                            <div class="col-sm-8"><?= $profile['PROF_OPERATIONAL'] ?></div>
                        </div>
                    </div>
                    <hr>
                    <h6 class="fw-bold mb-2">Susunan Pemegang Saham Perusahaan</h6>
                    <div style="margin:10px 10px 5px 10px;">
                        <ol style="padding-left: 10px;">
                            <?php foreach(explode(",", $profile['PROF_PEMEGANG_SAHAM']) as $key =>  $pemegangSaham) : ?>
                                <li><?= $pemegangSaham ?></li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                    <hr>
                    <h6 class="fw-bold mb-2">Nomor dan Tanggal Izin Usaha dari Bappebti</h6>
                    <div style="margin:10px 10px 5px 10px;">
                        <div class="dash_bottom"><?= $profile['PROF_NO_IZIN_USAHA'] ?> Tanggal: <?= date("Y-m-d", strtotime($profile['PROF_TGL_IZIN_USAHA'])) ?></div>
                    </div>
                    <hr>
                    <h6 class="fw-bold mb-2">Nomor dan Tanggal Keanggotaan Bursa Berjangka</h6>
                    <div style="margin:10px 10px 5px 10px;">
                        <div class="dash_bottom"><?= $profile['PROF_NO_KEANGGOTAAN_BURSA'] ?> Tanggal: <?= date("Y-m-d", strtotime($profile['PROF_TGL_KEANGGOTAAN_BURSA'])) ?></div>
                    </div>
                    <hr>
                    <h6 class="fw-bold mb-2">Nomor dan Tanggal Keanggotaan Lembaga Kliring Berjangka</h6>
                    <div style="margin:10px 10px 5px 10px;">
                        <div class="dash_bottom"><?= $profile['PROF_NO_KEANGGOTAAN_LEMBAGA'] ?> Tanggal: <?= date("Y-m-d", strtotime($profile['PROF_TGL_KEANGGOTAAN_LEMBAGA'])) ?></div>
                    </div>
                    <hr>
                    <h6 class="fw-bold mb-2">Nomor dan Tanggal Persetujuan sebagai Peserta Sistem Perdagangan Alternatif</h6>
                    <div style="margin:10px 10px 5px 10px;">
                        <div class="dash_bottom"><?= $profile['PROF_NO_PERSETUJUAN_PESERTA'] ?> Tanggal: <?= date("Y-m-d", strtotime($profile['PROF_TGL_PERSETUJUAN_PESERTA'])) ?></div>
                    </div>
                    <!-- <hr>
                    <h6 class="fw-bold mb-2">Nomor Keanggotaan ICDX</h6>
                    <div style="margin:10px 10px 5px 10px;">
                        <div class="dash_bottom"><?= $profile['PROF_NO_ICDX'] ?></div>
                    </div>
                    <hr>
                    <h6 class="fw-bold mb-2">Keanggotaan ICH</h6>
                    <div style="margin:10px 10px 5px 10px;">
                        <div class="dash_bottom"><?= $profile['PROF_NO_ICH']; ?></div>
                    </div>
                    <hr>
                    <h6 class="fw-bold mb-2">Nomor Anggota Aspebtindo</h6>
                    <div style="margin:10px 10px 5px 10px;">
                        <div class="dash_bottom"><?= $profile['NO_ANGGOTA_ASPEBTINDO']; ?></div>
                    </div>
                    <hr>
                    <h6 class="fw-bold mb-2">Nomor Izin usaha BI</h6>
                    <div style="margin:10px 10px 5px 10px;">
                        <div class="dash_bottom"><?= $profile['NO_IZIN_USAHA_BI']; ?></div>
                    </div> -->
                    <hr>
                    <h6 class="fw-bold mb-2">Nomor Izin usaha OJK</h6>
                    <div style="margin:10px 10px 5px 10px;">
                        <div class="dash_bottom"><?= $profile['NO_IZIN_USAHA_OJK']; ?></div>
                    </div>
                    <hr>
                    <h6 class="fw-bold mb-2">Nama Penyelenggara Sistem Perdagangan Alternatif</h6>
                    <div style="margin:10px 10px 5px 10px;">
                        <div class="dash_bottom"><?= $profile['FOREX_SYS'] ?></div>
                    </div>
                    <hr>
                    <h6 class="fw-bold mb-2">Kontrak Berjangka Yang Diperdagangkan</h6>
                    <div style="margin:10px 10px 5px 10px;">
                        <div class="row dash_bottom">
                            <label class="col-sm-4 col-form-label unset_padding fw-bold">Kontrak Berjangka Emas :</label>
                            <div class="col-sm-8">( GOL, GOL 250, GOL 100 )</div>
                        </div>
                        <div class="row dash_bottom">
                            <label class="col-sm-4 col-form-label unset_padding fw-bold">Kontrak Berjangka Kopi :</label>
                            <div class="col-sm-8">( ACF, RCF )</div>
                        </div>
                        <div class="row dash_bottom">
                            <label class="col-sm-4 col-form-label unset_padding fw-bold">Kontrak Berjangka Olein :</label>
                            <div class="col-sm-8">( OLE, OLE 10 )</div>
                        </div>
                        <div class="row dash_bottom">
                            <label class="col-sm-4 col-form-label unset_padding fw-bold">Kontrak Berjangka Indeks emas :</label>
                            <div class="col-sm-8">( KBIE )</div>
                        </div>
                        <div class="row dash_bottom">
                            <label class="col-sm-4 col-form-label unset_padding fw-bold">Kontrak Berjangka Coklat :</label>
                            <div class="col-sm-8">( CC5 )</div>
                        </div>
                    </div>
                    <hr>
                    <h6 class="fw-bold mb-2">Kontrak Derivatif Syariah Yang Diperdagangkan</h6>
                    <div style="margin:10px 10px 5px 10px;">
                        <div class="dash_bottom">Kontrak Derivatif dalam Sistem Perdagangan Alternatif (SPA)</div>
                        <div class="dash_bottom">Kontrak CFD Mata Uang Asing (FOREX) dan Loco Emas (XAU) , Silver (XAG) , Oil (CLSK)</div>
                        <div class="dash_bottom">Indeks Saham Jepang, Indeks Saham Hongkong, NAS100, DOW, SPX500</div>
                    </div>
                    <hr>
                    <h6 class="fw-bold mb-2">Kontrak Derivatif dalam Sistem Perdagangan Alternatif dengan volume minimum 0,1 (nol koma satu) lot Yang Diperdagangkan</h6>
                    <div style="margin:10px 10px 5px 10px;">
                        <div class="dash_bottom">Kontrak CFD Mata Uang Asing (FOREX) dan Loco Emas (XAU) , Silver (XAG) , Oil (CLSK)</div>
                    </div>
                    <hr>
                    <h6 class="fw-bold mb-2">Biaya secara rinci yang dibebankan pada Nasabah</h6>
                    <div style="margin:10px 10px 5px 10px;">
                        <div class="dash_bottom">Berdasarkan Jenis produk Komisi $50/lot settled / Interest / Swap / Rollover fee</div>
                    </div>
                    <hr>
                    <h6 class="fw-bold mb-2">Nomor atau alamat email jika terjadi keluhan</h6>
                    
                    <div style="margin:10px 10px 5px 10px;">
                        <div class="row dash_bottom">
                            <label class="col-sm-4 col-form-label unset_padding fw-bold">Email :</label>
                            <div class="col-sm-8"><?= $profile['PROF_EML_PENGADUAN'] ?></div>
                        </div>
                        <div class="row dash_bottom">
                            <label class="col-sm-4 col-form-label unset_padding fw-bold">No. Telepon :</label>
                            <div class="col-sm-8"><?= $profile['PROF_PHONE_PENGADUAN'] ?></div>
                        </div>
                        <!-- <div class="row dash_bottom">
                            <label class="col-sm-4 col-form-label unset_padding fw-bold">Fax :</label>
                            <div class="col-sm-8"><?= $profile['PROF_FAX_PENGADUAN'] ?></div>
                        </div> -->
                    </div>
                    <hr>
                    <h6 class="fw-bold mb-2">Sarana penyelesaian perselisihan yang dipergunakan apabila terjadi perselisihan</h6>
                    <div style="margin:10px 10px 5px 10px;">
                        <ol style="padding-left: 10px;">
                            <li>Secara musyawarah untuk mencapai mufakat antara Para Pihak</li>
                            <li>Memanfaatkan sarana penyelesaian perselisihan yang tersedia di Bursa Berjangka (JFX)</li>
                            <li>Badan Arbitrase Perdagangan Berjangka Komoditi (BAKTI) atau Pengadilan Negeri</li>
                        </ol>
                    </div>
                    <hr>
                    <h6 class="fw-bold mb-2">Nama-Nama Wakil Pialang Berjangka yang Bekerja di Perusahaan Pialang Berjangka</h6>
                    <div style="margin:10px 10px 5px 10px;">
                        <?php $list_wpb_satu = ProfilePerusahaan::list_wpb(-1, 2); ?>
                        <?php foreach($list_wpb_satu as $wpb) : ?>
                            <?php foreach($wpb as $w) : ?>
                                <div class="row dash_bottom g-0 align-items-start">
                                    <div class="col-5 col-md-4 pe-3"><?php echo $w['WPB_NAMA'] ?></div>
                                    <div class="col-7 col-md-8"><?php echo $w['WPB_NO1'] ?? '' ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>
                    <hr>
                    <h6 class="fw-bold mb-2">Nama-Nama Wakil Pialang Berjangka yang secara khusus ditunjuk oleh Pialang Berjangka untuk melakukan verifikasi dalam rangka penerimaan Nasabah elektronik online</h6>
                    <div style="margin:10px 10px 5px 10px;">
                        <?php $list_wpb_satu3 = ProfilePerusahaan::list_wpb(2, 2); ?>
                        <?php foreach($list_wpb_satu3 as $wpb3) : ?>
                            <?php foreach($wpb3 as $w3) : ?>
                                <div class="row dash_bottom g-0 align-items-start">
                                    <div class="col-5 col-md-4 pe-3"><?php echo $w3['WPB_NAMA'] ?></div>
                                    <div class="col-7 col-md-8">
                                        <?php echo $w3['WPB_NO1'] ?? '' ?>
                                        <br>
                                        <?php echo $w3['WPB_NO2'] ?? '' ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>
                    <hr>
                    <h6 class="fw-bold mb-2">Nomor Rekening Terpisah (Segregated Account) Perusahaan Pialang Berjangka</h6>
                    <div style="margin:10px 10px 5px 10px;">
                        <ol style="padding-left: 10px;">
                            <?php $sql_get_bankadm = $db->query("SELECT * FROM tb_bankadm"); ?>
                            <?php if($sql_get_bankadm) : ?>
                                <?php foreach($sql_get_bankadm->fetch_all(MYSQLI_ASSOC) as $key => $bkadm) : ?>
                                    <li><?= $bkadm['BKADM_NAME'] ?> / <?= $bkadm['BKADM_ACCOUNT'] ?> (<?= $bkadm['BKADM_CURR'] ?>)</li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ol>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12 text-center">
                            <h5>PERNYATAAN TELAH MEMBACA PROFIL PERUSAHAAN PIALANG BERJANGKA</h5>
                            <p>
                                Dengan mengisi kolom “YA” di bawah ini, saya menyatakan bahwa saya telah membaca dan menerima informasi
                                <strong>PROFIL PERUSAHAAN PIALANG BERJANGKA</strong>, mengerti dan memahami isinya.
                            </p>
                        </div>
                        <div class="col-6 mt-3">
                            Pernyataan menerima/tidak<br>
                            <input type="radio" name="aggree" value="Ya" class="form-check-input radio_css" style="margin-top: 10px;" required <?= $realAccount['ACC_F_PROFILE'] ? 'checked' : NULL ?>>
                            <label style="top: 0.5rem;position: relative;margin-bottom: 0;vertical-align: top;margin-right:1.5rem;">Ya</label>
                            <input type="radio" name="aggree" value="Tidak" class="form-check-input radio_css" style="margin-top: 10px;" required>
                            <label style="top: 0.5rem;position: relative;margin-bottom: 0;vertical-align: top;margin-right:1.5rem;">Tidak</label>
                        </div>
                        <div class="col-6 mt-3">
                            <div class="text-cemter">Menerima pada Tanggal</div>
                            <input type="text" name="agg_date" readonly required value="<?= $realAccount['ACC_F_PROFILE_DATE'] ?? date("Y-m-d H:i:s"); ?>" class="form-control text-center mb-3 <?= (empty($realAccount['ACC_F_PROFILE']))? "realtime-date" : ""; ?>">
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
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function() {
        $('#form-profile-perusahaan').on('submit', function(event) {
            event.preventDefault();
            let data = Object.fromEntries(new FormData(this).entries());
            Swal.fire({
                text: "Please wait...",
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            })

            $.post("/ajax/regol/profilePerusahaan", data, function(resp) {
                if(!resp.success) {
                    Swal.fire(resp.alert);
                    return;
                }
                
                location.href = resp.redirect
            }, 'json')
        })
    })
</script>