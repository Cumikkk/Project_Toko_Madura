<?php 
use App\Models\Helper;
if($permisCreate = $adminPermissionCore->isHavePermission($moduleId, "view_company")){
?>
    <div class="row row-sm">
        <div class="col-lg-12 col-md-12 col-md-12">
            <div class="card custom-card">
                <div class="card-header">
                    <h4>Company</h4>
                </div>
                <div class="card-body border">
                    <form class="form-horizontal" action="" method="post" id="form-company">
                        <div class="mb-4 main-content-label">Dewan Direksi</div>
                        <div class="form-group ">
                            <div class="row row-sm">
                                <div class="col-md-3">
                                    <label for="president-direktur" class="form-label">Direktur Utama</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="president-direktur" id="president-direktur" placeholder="President Direktur" value="<?php echo $profile['PROF_DEWAN_DIREKSI'] ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group ">
                            <div class="row row-sm">
                                <div class="col-md-3">
                                    <label for="direktur-kepatuhan" class="form-label">Direktur Kepatuhan</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="direktur-kepatuhan" id="direktur-kepatuhan" placeholder="Direktur Kepatuhan" value="<?php echo $profile['PROF_DIREKTUR'] ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group ">
                            <div class="row row-sm">
                                <div class="col-md-3">
                                    <label for="direktur-operational" class="form-label">Direktur Operasional</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="direktur-operational" id="direktur-operational" placeholder="Direktur Kepatuhan" value="<?php echo $profile['PROF_OPERATIONAL'] ?>" required>
                                </div>
                            </div>
                        </div>
                        

                        <div class="mb-4 main-content-label">Komisaris</div>
                        <div class="form-group">
                            <div class="row row-sm">
                                <div class="col-md-3">
                                    <label for="komisaris-utama" class="form-label">Komisaris Utama</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="komisaris-utama" id="komisaris-utama" placeholder="Komisaris Utama" value="<?php echo $profile['PROF_KOMISARIS_UTAMA'] ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row row-sm">
                                <div class="col-md-3">
                                    <label for="komisaris" class="form-label">Komisaris</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="komisaris" id="komisaris" placeholder="Komisaris" value="<?php echo $profile['PROF_KOMISARIS'] ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4 main-content-label">Lainnya</div>
                        <div class="form-group">
                            <div class="row row-sm">
                                <div class="col-md-3">
                                    <label for="account-margin-limit" class="form-label">Limit Deposit</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control amount-formatter" name="account-margin-limit" id="account-margin-limit" value="<?= str_replace('.', ',', Helper::formatCurrency($profile['ACCOUNT_MARGIN_LIMIT'])) ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="row row-sm">
                                <div class="col-md-3">
                                    <label for="pemegang-saham" class="form-label">Susunan Pemegang Saham Perusahaan</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control mb-1 sp" name="pemegang-saham[]" placeholder="-" value="<?php echo explode(",", $profile['PROF_PEMEGANG_SAHAM'])[0] ?>" required>
                                    <input type="text" class="form-control mb-1 sp" name="pemegang-saham[]" placeholder="-" value="<?php echo explode(",", $profile['PROF_PEMEGANG_SAHAM'])[1] ?>" required>
                                    <input type="hidden" name="sp-merge" id="sp-merge" value="<?= $profile['PROF_PEMEGANG_SAHAM'] ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row row-sm">
                                <div class="col-md-3">
                                    <label for="no_izin_usaha" class="form-label">Nomor dan Tanggal Izin Usaha dari Bappebti</label>
                                </div>
                                <div class="col-md-9">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="no_izin_usaha" id="no_izin_usaha" placeholder="No. XXX/BAPPEBTI/XX/X/XXXX" value="<?php echo $profile['PROF_NO_IZIN_USAHA'] ?>" required>
                                        <input type="date" class="input-group-text" name="tgl_izin_usaha" id="tgl_izin_usaha" placeholder="" value="<?php echo Helper::default_date($profile['PROF_TGL_IZIN_USAHA'], "Y-m-d") ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row row-sm">
                                <div class="col-md-3">
                                    <label for="no_keanggotaan_bursa" class="form-label">Nomor dan Tanggal Keanggotaan Bursa Berjangka</label>
                                </div>
                                <div class="col-md-9">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="no_keanggotaan_bursa" id="no_keanggotaan_bursa" value="<?php echo $profile['PROF_NO_KEANGGOTAAN_BURSA'] ?>"  placeholder="No. XXX/BAPPEBTI/XX/X/XXXX" required>
                                        <input type="date" class="input-group-text" name="tgl_keanggotaan_bursa" id="tgl_keanggotaan_bursa" placeholder="" value="<?php echo Helper::default_date($profile['PROF_TGL_KEANGGOTAAN_BURSA'], "Y-m-d") ?>"  required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="row row-sm">
                                <div class="col-md-3">
                                    <label for="no_keanggotaan_lembaga" class="form-label">Nomor dan Tanggal Keanggotaan Lembaga Kliring Berjangka</label>
                                </div>
                                <div class="col-md-9">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="no_keanggotaan_lembaga" id="no_keanggotaan_lembaga" placeholder="No. XXX/BAPPEBTI/XX/X/XXXX" value="<?php echo $profile['PROF_NO_KEANGGOTAAN_LEMBAGA'] ?>" required>
                                        <input type="date" class="input-group-text" name="tgl_keanggotaan_lembaga" id="tgl_keanggotaan_lembaga" placeholder="" value="<?php echo Helper::default_date($profile['PROF_TGL_KEANGGOTAAN_LEMBAGA'], "Y-m-d") ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row row-sm">
                                <div class="col-md-3">
                                    <label for="no_persetujuan_peserta" class="form-label">Nomor dan Tanggal Persetujuan sebagai Peserta Sistem Perdagangan Alternatif</label>
                                </div>
                                <div class="col-md-9">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="no_persetujuan_peserta" id="no_persetujuan_peserta" placeholder="No. XXX/BAPPEBTI/XX/X/XXXX" value="<?php echo $profile['PROF_NO_PERSETUJUAN_PESERTA'] ?>" required>
                                        <input type="date" class="input-group-text" name="tgl_persetujuan_peserta" id="tgl_persetujuan_peserta" placeholder="" value="<?php echo Helper::default_date($profile['PROF_TGL_PERSETUJUAN_PESERTA'], "Y-m-d") ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="row row-sm">
                                <div class="col-md-3">
                                    <label for="no_keanggotaan_icdx" class="form-label">Nomor Keanggotaan ICDX</label>
                                </div>
                                <div class="col-md-9">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="no_keanggotaan_icdx" id="no_keanggotaan_icdx" placeholder="No. XXX/ICDX/XX/X/XXXX" value="<?php echo $profile['PROF_NO_ICDX'] ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="row row-sm">
                                <div class="col-md-3">
                                    <label for="no_keanggotaan_ich" class="form-label">Nomor Keanggotaan ICH</label>
                                </div>
                                <div class="col-md-9">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="no_keanggotaan_ich" id="no_keanggotaan_ich" placeholder="No. XXX/ICH/XX/X/XXXX" value="<?php echo $profile['PROF_NO_ICH'] ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row row-sm">
                                <div class="col-md-3">
                                    <label for="no_anggota_aspebtindo" class="form-label">Nomor Anggota Aspebtindo</label>
                                </div>
                                <div class="col-md-9">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="no_anggota_aspebtindo" id="no_anggota_aspebtindo" placeholder="No. Anggota" value="<?php echo $profile['NO_ANGGOTA_ASPEBTINDO'] ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row row-sm">
                                <div class="col-md-3">
                                    <label for="no_izin_usaha_bi" class="form-label">Nomor Izin usaha BI</label>
                                </div>
                                <div class="col-md-9">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="no_izin_usaha_bi" id="no_izin_usaha_bi" placeholder="No. Izin Usaha BI" value="<?php echo $profile['NO_IZIN_USAHA_BI'] ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row row-sm">
                                <div class="col-md-3">
                                    <label for="no_izin_usaha_ojk" class="form-label">Nomor Izin usaha OJK</label>
                                </div>
                                <div class="col-md-9">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="no_izin_usaha_ojk" id="no_izin_usaha_ojk" placeholder="No. Izin Usaha OJK" value="<?php echo $profile['NO_IZIN_USAHA_OJK'] ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 main-content-label">Pengaduan</div>
                        <div class="form-group">
                            <div class="row row-sm">
                                <div class="col-md-3">
                                    <label for="email_pengaduan" class="form-label">Email Pengaduan</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="email_pengaduan" id="email_pengaduan" placeholder="Email Pengaduan" value="<?php echo $profile['PROF_EML_PENGADUAN'] ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row row-sm">
                                <div class="col-md-3">
                                    <label for="phone_pengaduan" class="form-label">Phone Pengaduan</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="phone_pengaduan" id="phone_pengaduan" placeholder="Phone Pengaduan" value="<?php echo $profile['PROF_PHONE_PENGADUAN'] ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row row-sm">
                                <div class="col-md-3">
                                    <label for="faks_pengaduan" class="form-label">Faks Pengaduan</label>
                                </div>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="faks_pengaduan" id="faks_pengaduan" placeholder="Faks Pengaduan" value="<?php echo $profile['PROF_FAX_PENGADUAN'] ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="d-flex justify-content-end w-100">
                                <button type="submit" name="submit-profile" class="btn btn-success">Update</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(() => {
            $('.sp').on('keyup', function(e){
                let sp = [];
                $('.sp').each((i, e) => {
                    if($(e).val().length){
                        sp.push($(e).val());
                    }
                });
                $('#sp-merge').val(sp.join(', '));
            });
            $('#form-company').on('submit', function(ev){
                ev.preventDefault();
                let sbmBtn = $(this).find(':submit');
                sbmBtn.addClass('loading');
                let data = Object.fromEntries(new FormData(this));

                $.post("/ajax/post/tools/profile_perushaaan/update_company", data, function(resp) {
                    sbmBtn.removeClass('loading');
                    Swal.fire(resp.alert).then(() => {
                        if(resp.success) {
                            location.reload();
                        }
                    });
                }, 'json');
            });
        });
    </script>
<?php } ?>