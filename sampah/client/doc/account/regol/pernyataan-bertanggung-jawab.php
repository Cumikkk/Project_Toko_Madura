<div class="row">
    <div class="col-md-9 mx-auto">
        <form method="post" id="form-pernyataan-bertanggung-jawab">
            <input type="hidden" name="csrf_token" value="<?= uniqid(); ?>">
            <div class="card">
                <div class="card-body">
                    <div class="text-center"><h5>PERNYATAAN BERTANGGUNG JAWAB ATAS KODE AKSES TRANSAKSI NASABAH (Personal Access Password)</h5></div>
                    <hr>
                    
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
                    <div class="row">
                        <div class="col-12">
                            <!-- <div class="table-responsive">
                                <p>Yang mengisi formulir di bawah ini :</p>
                                <table class="table table-hover" style="text-align: left; table-layout: fixed;" width="100%">
                                    <tr>
                                        <td width="30%" class="top-align fw-bold">Nama Lengkap</td>
                                        <td width="3%" class="top-align"> : </td>
                                        <td class="top-align text-start"><?= $realAccount['ACC_FULLNAME'] ?></td>
                                    </tr>
                                    <tr>
                                        <td width="30%" class="top-align fw-bold">Tempat Lahir</td>
                                        <td width="3%" class="top-align"> : </td>
                                        <td class="top-align text-start"><?= $realAccount['ACC_TEMPAT_LAHIR'] ?></td>
                                    </tr>
                                    <tr>
                                        <td width="30%" class="top-align fw-bold">Tanggal Lahir</td>
                                        <td width="3%" class="top-align"> : </td>
                                        <td class="top-align text-start"><?= $realAccount['ACC_TANGGAL_LAHIR'] ?></td>
                                    </tr>
                                    <tr>
                                        <td width="30%" class="top-align fw-bold">Alamat Rumah</td>
                                        <td width="3%" class="top-align"> : </td>
                                        <td class="top-align text-start"><?= $realAccount['ACC_ADDRESS'] ?></td>
                                    </tr>
                                    <tr>
                                        <td width="30%" class="top-align fw-bold">Provinsi</td>
                                        <td width="3%" class="top-align"> : </td>
                                        <td class="top-align text-start"><?= $realAccount['ACC_PROVINCE'] ?></td>
                                    </tr>
                                    <tr>
                                        <td width="30%" class="top-align fw-bold">Kabupaten/Kota</td>
                                        <td width="3%" class="top-align"> : </td>
                                        <td class="top-align text-start"><?= $realAccount['ACC_REGENCY'] ?></td>
                                    </tr>
                                    <tr>
                                        <td width="30%" class="top-align fw-bold">Kecamatan</td>
                                        <td width="3%" class="top-align"> : </td>
                                        <td class="top-align text-start"><?= $realAccount['ACC_DISTRICT'] ?></td>
                                    </tr>
                                    <tr>
                                        <td width="30%" class="top-align fw-bold">Desa</td>
                                        <td width="3%" class="top-align"> : </td>
                                        <td class="top-align text-start"><?= $realAccount['ACC_VILLAGE'] ?></td>
                                    </tr>
                                    <tr>
                                        <td width="30%" class="top-align fw-bold">RW</td>
                                        <td width="3%" class="top-align"> : </td>
                                        <td class="top-align text-start"><?= $realAccount['ACC_RW'] ?></td>
                                    </tr>
                                    <tr>
                                        <td width="30%" class="top-align fw-bold">RT</td>
                                        <td width="3%" class="top-align"> : </td>
                                        <td class="top-align text-start"><?= $realAccount['ACC_RT'] ?></td>
                                    </tr>
                                    <tr>
                                        <td width="30%" class="top-align fw-bold">Kode Pos</td>
                                        <td width="3%" class="top-align"> : </td>
                                        <td class="top-align text-start"><?= $realAccount['ACC_ZIPCODE'] ?></td>
                                    </tr>
                                    <tr>
                                        <td width="30%" class="top-align fw-bold">Tipe Identitas</td>
                                        <td width="3%" class="top-align"> : </td>
                                        <td class="top-align text-start"><?= $realAccount['ACC_TYPE_IDT'] ?></td>
                                    </tr>
                                    <tr>
                                        <td width="30%" class="top-align fw-bold">No. Identitas</td>
                                        <td width="3%" class="top-align"> : </td>
                                        <td class="top-align text-start"><?= $realAccount['ACC_NO_IDT'] ?></td>
                                    </tr>
                                    <tr>
                                        <td width="30%" class="top-align fw-bold">No. Acc</td>
                                        <td width="3%" class="top_aling"> : </td>
                                        <td class="top-align text-start"><?= $realAccount['ACC_LOGIN'] ?></td>
                                    </tr>
                                </table>
                            </div> -->
                            <p>Yang mengisi formulir di bawah ini :</p>
                            <div style="margin:10px 10px 5px 10px;">
                                <div class="row dash_bottom mb-25">
                                    <label for="kodeakses_namalengkap" class="col-sm-4 col-form-label unset_padding fw-bold">Nama Lengkap: </label>
                                    <div class="col-sm-8">
                                        <input type="text" disabled value="<?= preg_replace('/\d+/i', '', $realAccount['ACC_FULLNAME']) ?>" class="form-control">
                                    </div>
                                </div>
                                <div class="row dash_bottom mb-25">
                                    <label for="" class="col-sm-4 col-form-label unset_padding fw-bold">Tempat Lahir :</label>
                                    <div class="col-sm-8">
                                        <input type="text" disabled value="<?= $realAccount['ACC_TEMPAT_LAHIR'] ?>" class="form-control">
                                    </div>
                                </div>
                                <div class="row dash_bottom mb-25">
                                    <label for="" class="col-sm-4 col-form-label unset_padding fw-bold">Tanggal Lahir :</label>
                                    <div class="col-sm-8">
                                        <input type="text" disabled value="<?= $realAccount['ACC_TANGGAL_LAHIR'] ?>" class="form-control">
                                    </div>
                                </div>
                                <div class="row dash_bottom mb-25">
                                    <label for="" class="col-sm-4 col-form-label unset_padding fw-bold">Alamat Rumah :</label>
                                    <div class="col-sm-8">
                                        <input type="text" disabled value="<?= $realAccount['ACC_ADDRESS'] ?>" class="form-control">
                                    </div>
                                </div>
                                <div class="row dash_bottom mb-25">
                                    <label for="" class="col-sm-4 col-form-label unset_padding fw-bold">Provinsi :</label>
                                    <div class="col-sm-8">
                                        <input type="text" disabled value="<?= $realAccount['ACC_PROVINCE'] ?>" class="form-control">
                                    </div>
                                </div>
                                <div class="row dash_bottom mb-25">
                                    <label for="" class="col-sm-4 col-form-label unset_padding fw-bold">Kabupaten/Kota :</label>
                                    <div class="col-sm-8">
                                        <input type="text" disabled value="<?= $realAccount['ACC_REGENCY'] ?>" class="form-control">
                                    </div>
                                </div>
                                <div class="row dash_bottom mb-25">
                                    <label for="" class="col-sm-4 col-form-label unset_padding fw-bold">Kecamatan :</label>
                                    <div class="col-sm-8">
                                        <input type="text" disabled value="<?= $realAccount['ACC_DISTRICT'] ?>" class="form-control">
                                    </div>
                                </div>
                                <div class="row dash_bottom mb-25">
                                    <label for="" class="col-sm-4 col-form-label unset_padding fw-bold">Desa :</label>
                                    <div class="col-sm-8">
                                        <input type="text" disabled value="<?= $realAccount['ACC_VILLAGE'] ?>" class="form-control">
                                    </div>
                                </div>
                                <div class="row dash_bottom mb-25">
                                    <label for="" class="col-sm-4 col-form-label unset_padding fw-bold">RT :</label>
                                    <div class="col-sm-8">
                                        <input type="text" disabled value="<?= $realAccount['ACC_RT'] ?>" class="form-control">
                                    </div>
                                </div>
                                <div class="row dash_bottom mb-25">
                                    <label for="" class="col-sm-4 col-form-label unset_padding fw-bold">RW :</label>
                                    <div class="col-sm-8">
                                        <input type="text" disabled value="<?= $realAccount['ACC_RW'] ?>" class="form-control">
                                    </div>
                                </div>
                                <div class="row dash_bottom mb-25">
                                    <label for="" class="col-sm-4 col-form-label unset_padding fw-bold">Kode Pos :</label>
                                    <div class="col-sm-8">
                                        <input type="text" disabled value="<?= $realAccount['ACC_ZIPCODE'] ?>" class="form-control">
                                    </div>
                                </div>
                                <div class="row dash_bottom mb-25">
                                    <label for="" class="col-sm-4 col-form-label unset_padding fw-bold">Tipe Identitas :</label>
                                    <div class="col-sm-8">
                                        <input type="text" disabled value="<?= $realAccount['ACC_TYPE_IDT'] ?>" class="form-control">
                                    </div>
                                </div>
                                <div class="row dash_bottom mb-25">
                                    <label for="" class="col-sm-4 col-form-label unset_padding fw-bold">No. Identitas :</label>
                                    <div class="col-sm-8">
                                        <input type="text" disabled value="<?= $realAccount['ACC_NO_IDT'] ?>" class="form-control">
                                    </div>
                                </div>
                                <div class="row dash_bottom mb-25">
                                    <label for="" class="col-sm-4 col-form-label unset_padding fw-bold">No. Demo Acc :</label>
                                    <div class="col-sm-8">
                                        <input type="text" disabled value="<?= $realAccount['ACC_DEMO'] ?>" class="form-control">
                                    </div>
                                </div>
                                <div class="row dash_bottom mb-25">
                                    <label for="" class="col-sm-4 col-form-label unset_padding fw-bold">Pernyataan Pengalaman :</label>
                                    <div class="col-sm-8">
                                        <select name="pengalaman" id="pengalaman" class="form-control">
                                            <option value="Ya" <?= (strtolower($realAccount['ACC_F_PENGLAMAN_PERYT_YA'] ?? "") == "ya")? "selected" : ""; ?>>Ya</option>
                                            <option value="Tidak" <?= (strtolower($realAccount['ACC_F_PENGLAMAN_PERYT_YA'] ?? "") == "tidak")? "selected" : ""; ?>>Tidak</option>
                                        </select>
                                    </div>
                                </div>
                                <?php if(strtolower($realAccount['ACC_F_PENGLAMAN_PERYT_YA'] ?? "") == "ya") : ?>
                                    <div class="row dash_bottom mb-25">
                                        <label for="" class="col-sm-4 col-form-label unset_padding fw-bold">Nama Perusahaan Pialang Berjangka :</label>
                                        <div class="col-sm-8">
                                            <input type="text" disabled value="<?= $realAccount['ACC_F_PENGLAMAN_PERSH'] ?>" class="form-control">
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12 text-center">
                            <p>
                                Dengan mengisi kolom “YA” di bawah ini, 
                                saya menyatakan bahwa saya bertanggungjawab sepenuhnya terhadap kode akses transaksi Nasabah (Personal Access Password) 
                                dan tidak menyerahkan kode akses transaksi Nasabah (Personal Access Password) ke pihak lain, 
                                terutama kepada pegawai Pialang Berjangka atau pihak yang memiliki kepentingan dengan Pialang Berjangka.
                            </p>
                        </div>
                        <div class="col-md-12 text-center">
                            <div style="border:2px solid black;">
                                <strong >
                                    <span style="color:red">PERINGATAN !!!</span><br>
                                    Pialang Berjangka, Wakil Pialang Berjangka, pegawai Pialang Berjangka, atau Pihak
                                    yang memiliki kepentingan dengan dengan Pialang Berjangka dilarang menerima kode
                                    akses transaksi Nasabah (Personal Access Password).
                                </strong>
                            </div>
                            <p class="mt-3">Demikian Pernyataan ini dibuat dengan sebenarnya dalam keadaan sadar, sehat jasmani dan rohani serta tanpa paksaan apapum dari pihak manapun</p>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2 col-sm-12">
                                Pernyataan menerima / tidak<br>
                                <input type="radio" name="aggree" value="Ya" class="form-check-input radio_css" style="margin-top: 10px;" required <?= !empty($realAccount['ACC_F_KODE'])? "checked" : "" ?>>
                                <label style="top: 0.5rem;position: relative;margin-bottom: 0;vertical-align: top;margin-right:1.5rem;">Ya</label>
                                <input type="radio" name="aggree" value="Tidak" class="form-check-input radio_css" style="margin-top: 10px;" required>
                                <label style="top: 0.5rem;position: relative;margin-bottom: 0;vertical-align: top;margin-right:1.5rem;">Tidak</label>
                            </div>
                            <div class="col-md-6 col-sm-12">
                                <div class="text-cemter">Menerima pada Tanggal</div>
                                <input type="text" name="agg_date" readonly required value="<?= retnull("ACC_F_KODE_DATE", date('Y-m-d H:i:s')) ?>" class="form-control text-center mb-3 <?= empty($realAccount['ACC_F_KODE_DATE'])? "realtime-date" : "" ?>">
                            </div>
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
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $("#form-pernyataan-bertanggung-jawab").on("submit", function(event) {
            event.preventDefault();
            let data = Object.fromEntries(new FormData(this).entries());
           
            Swal.fire({
                text: "Please wait...",
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            })
            
            $.post("/ajax/regol/pernyataanBertanggungJawab", data, function(resp) {
                if(!resp.success) {
                    Swal.fire(resp.alert);
                    return;
                }
                
                location.href = resp.redirect
            }, 'json')
        });
    });
</script>