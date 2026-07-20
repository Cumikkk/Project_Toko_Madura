<div class="row">
    <div class="col-md-9 mx-auto mb-3">
        <form method="post" id="form-pernyataan-dana-nasabah">
            <input type="hidden" name="csrf_token" value="<?= uniqid(); ?>">
            <div class="card">
                <div class="card-body">
                    <div class="text-center"><h5>PERNYATAAN BAHWA DANA YANG DIGUNAKAN SEBAGAI MARGIN MERUPAKAN DANA MILIK NASABAH SENDIRI</h5></div>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive">
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
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12 text-center">
                            <p>
                            Dengan mengisi kolom “YA” di bawah ini, Bersama ini saya menyatakan bahwa dana yang saya gunakan
                            untuk bertransaksi di <?= App\Models\ProfilePerusahaan::get()['COMPANY_NAME'] ?> adalah milik saya pribadi dan bukan dana pihak lain,
                            serta tidak diperoleh dari hasil kejahatan, penipuan, penggelapan, tindak pidana korupsi, tindak pidan
                            narkotika, tindak pidana di bidang kehutanan, hasil pencucian uang, dan perbuatan melawan hukum lainnya
                            serta tidak dimaksudkan untuk melakukan pencucian uang dan/atau pendanaan terorisme.
                            </p>
                        </div>
                        <div class="col-md-12 text-center">
                            <p class="mt-3">Demikian Pernyataan ini dibuat dengan sebenarnya dalam keadaan sadar, sehat jasmani dan rohani serta tanpa paksaan apapum dari pihak manapun</p>
                        </div>
                        <div class="col-6 mt-3">
                            Pernyataan menerima/tidak<br>
                            <input type="radio" name="aggree" value="Ya" class="form-check-input radio_css" style="margin-top: 10px;" required <?= !empty($realAccount['ACC_F_DANA'])? "checked" : "" ?>>
                            <label style="top: 0.5rem;position: relative;margin-bottom: 0;vertical-align: top;margin-right:1.5rem;">Ya</label>
                            <input type="radio" name="aggree" value="Tidak" class="form-check-input radio_css" style="margin-top: 10px;" required>
                            <label style="top: 0.5rem;position: relative;margin-bottom: 0;vertical-align: top;margin-right:1.5rem;">Tidak</label>
                        </div>
                        <div class="col-6 mt-3">
                            <div class="text-cemter">Menerima pada Tanggal</div>
                            <input type="text" name="agg_date" readonly required value="<?= retnull("ACC_F_DANA_DATE", date('Y-m-d H:i:s')) ?>" class="form-control text-center mb-3 <?= empty($realAccount['ACC_F_DANA_DATE'])? "realtime-date" : "" ?>">
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
        $("#form-pernyataan-dana-nasabah").on("submit", function(event) {
            event.preventDefault();
            let data = Object.fromEntries(new FormData(this).entries());
           
            Swal.fire({
                text: "Please wait...",
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            })
            
            $.post("/ajax/regol/pernyataanDanaNasabah", data, function(resp) {
                if(!resp.success) {
                    Swal.fire(resp.alert);
                    return;
                }
                
                location.href = resp.redirect
            }, 'json')
        });
    });
</script>