<style>
    .light-theme .swal2-popup .swal2-input {
        color: black;
    }

    .dark-theme .swal2-popup .swal2-input {
        color: white;
    }
</style>

<style>
    .dash_bottom {
        font-size: 14px;
        margin-bottom: .5rem !important;
    }
    .unset_padding {
        padding-top: 5px !important;
        padding-bottom: unset !important;
    }
</style>
<div class="row">
    <div class="col-md-8 mx-auto mb-3">
        <form method="post" id="form-pernyataan-pengalaman">
            <input type="hidden" name="csrf_token" value="<?= uniqid(); ?>">
            <div class="card">
                <div class="card-body">
                    <div class="text-center"><h5>FORMULIR PERNYATAAN TELAH BERPENGALAMAN MELAKSANAKAN TRANSAKSI  PERDAGANGAN BERJANGKA KOMODITI</h5></div>
                    <hr>
                    <p>Yang mengisi formulir di bawah ini :</p>
                    <div style="margin:10px 10px 5px 10px;">
                        <div class="row dash_bottom">
                            <label class="col-xs-5 col-sm-6 col-md-4 col-form-label fw-bold unset_padding">Nama Lengkap</label>
                            <div class="col-xs-7 col-sm-6 col-md-8 text-wrap">
                                : <?= preg_replace('/\d+/i', '', $realAccount['ACC_FULLNAME']) ?>
                            </div>
                        </div>
                        <div class="row dash_bottom">
                            <label class="col-xs-5 col-sm-6 col-md-4 col-form-label fw-bold unset_padding">Tempat Lahir</label>
                            <div class="col-xs-7 col-sm-6 col-md-8 text-nowrap">
                                : <?= $realAccount['ACC_TEMPAT_LAHIR'] ?>
                            </div>
                        </div>
                        <div class="row dash_bottom">
                            <label class="col-xs-5 col-sm-6 col-md-4 col-form-label fw-bold unset_padding">Tanggal Lahir</label>
                            <div class="col-xs-7 col-sm-6 col-md-8 text-nowrap">
                                : <?= $realAccount['ACC_TANGGAL_LAHIR'] ?>
                            </div>
                        </div>
                        <div class="row dash_bottom">
                            <label class="col-xs-5 col-sm-6 col-md-4 col-form-label fw-bold unset_padding">Alamat Rumah</label>
                            <div class="col-xs-7 col-sm-6 col-md-8 text-wrap">
                                : <?= $realAccount['ACC_ADDRESS'] ?>
                            </div>
                        </div>
                        <div class="row dash_bottom">
                            <label class="col-xs-5 col-sm-6 col-md-4 col-form-label fw-bold unset_padding">Provinsi</label>
                            <div class="col-xs-7 col-sm-6 col-md-8 text-nowrap">
                                : <?= $realAccount['ACC_PROVINCE'] ?>
                            </div>
                        </div>
                        <div class="row dash_bottom">
                            <label class="col-xs-5 col-sm-6 col-md-4 col-form-label fw-bold unset_padding">Kabupaten/Kota</label>
                            <div class="col-xs-7 col-sm-6 col-md-8 text-nowrap">
                                : <?= $realAccount['ACC_REGENCY'] ?>
                            </div>
                        </div>
                        <div class="row dash_bottom">
                            <label class="col-xs-5 col-sm-6 col-md-4 col-form-label fw-bold unset_padding">Kecamatan</label>
                            <div class="col-xs-7 col-sm-6 col-md-8 text-nowrap">
                                : <?= $realAccount['ACC_DISTRICT'] ?>
                            </div>
                        </div>
                        <div class="row dash_bottom">
                            <label class="col-xs-5 col-sm-6 col-md-4 col-form-label fw-bold unset_padding">Desa</label>
                            <div class="col-xs-7 col-sm-6 col-md-8 text-nowrap">
                                : <?= $realAccount['ACC_VILLAGE'] ?>
                            </div>
                        </div>
                        <div class="row dash_bottom">
                            <label class="col-xs-5 col-sm-6 col-md-4 col-form-label fw-bold unset_padding">RW</label>
                            <div class="col-xs-7 col-sm-6 col-md-8 text-nowrap">
                                : <?= $realAccount['ACC_RW'] ?>
                            </div>
                        </div>
                        <div class="row dash_bottom">
                            <label class="col-xs-5 col-sm-6 col-md-4 col-form-label fw-bold unset_padding">RT</label>
                            <div class="col-xs-7 col-sm-6 col-md-8 text-nowrap">
                                : <?= $realAccount['ACC_RT'] ?>
                            </div>
                        </div>
                        <div class="row dash_bottom">
                            <label class="col-xs-5 col-sm-6 col-md-4 col-form-label fw-bold unset_padding">Kode Pos</label>
                            <div class="col-xs-7 col-sm-6 col-md-8 text-nowrap">
                                : <?= $realAccount['ACC_ZIPCODE'] ?>
                            </div>
                        </div>
                        <div class="row dash_bottom">
                            <label class="col-xs-5 col-sm-6 col-md-4 col-form-label fw-bold unset_padding">Tipe Identitas</label>
                            <div class="col-xs-7 col-sm-6 col-md-8 text-nowrap">
                                : <?= $realAccount['ACC_TYPE_IDT'] ?>
                            </div>
                        </div>
                        <div class="row dash_bottom">
                            <label class="col-xs-5 col-sm-6 col-md-4 col-form-label fw-bold unset_padding">No. Identitas</label>
                            <div class="col-xs-7 col-sm-6 col-md-8 text-nowrap">
                                : <?= $realAccount['ACC_NO_IDT'] ?>
                            </div>
                        </div>
                        <div class="row dash_bottom">
                            <label class="col-xs-5 col-sm-6 col-md-4 col-form-label fw-bold unset_padding">No. Demo Acc</label>
                            <div class="col-xs-7 col-sm-6 col-md-8 text-nowrap">
                                : <?= $realAccount['ACC_DEMO'] ?>
                            </div>
                        </div>
                        <div class="row dash_bottom">
                            <label class="col-xs-5 col-sm-6 col-md-4 col-form-label fw-bold">Pernyataan Pengalaman</label>
                            <div class="col-xs-7 col-sm-6 col-md-8 text-nowrap">
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="mb-0">:</span>
                                    <select name="pengalaman" id="pengalaman" class="form-control w-50">
                                        <option value="Ya" <?= (strtolower($realAccount['ACC_F_PENGLAMAN_PERYT_YA'] ?? "") == "ya")? "selected" : ""; ?>>Ya</option>
                                        <option value="Tidak" <?= (strtolower($realAccount['ACC_F_PENGLAMAN_PERYT_YA'] ?? "") == "tidak")? "selected" : ""; ?>>Tidak</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <?php if(strtolower($realAccount['ACC_F_PENGLAMAN_PERYT_YA'] ?? "") == "ya") : ?>
                            <div class="row dash_bottom">
                                <label class="col-xs-5 col-sm-6 col-md-4 col-form-label fw-bold">Nama Perusahaan Pialang Berjangka</label>
                                <div class="col-xs-7 col-sm-6 col-md-8 text-wrap">
                                    : <?= $realAccount['ACC_F_PENGLAMAN_PERSH'] ?? "-" ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="row mt-3">
                            <div class="col-md-12 mb-3">
                                <p class="mt-3">
                                    Dengan mengisi kolom "YA" di bawah ini, saya menyatakan bahwa saya telah memiliki pengalaman yang mencukupi dalam melaksanakan 
                                    transaksi Perdagangan Berjangka karena pernah bertransaksi pada Perusahaan Pialang Berjangka dan telah memahami tentang tata cara bertransaksi Perdagangan Berjangka.
                                </p>
                                <p>Demikian Pernyataan ini dibuat dengan sebenarnya dalam keadaan sadar, sehat jasmani dan rohani serta tanpa paksaan apapun dari pihak manapun.</p>
                            </div>
                            <div class="col-md-6 col-sm-12 mt-3">
                                Pernyataan menerima/tidak<br>
                                <input type="radio" name="aggree" value="Ya" class="form-check-input radio_css" style="margin-top: 10px;" required <?= !empty($realAccount['ACC_F_PENGLAMAN'])? "checked" : "" ?>>
                                <label style="top: 0.5rem;position: relative;margin-bottom: 0;vertical-align: top;margin-right:1.5rem;">Ya</label>
                                <input type="radio" name="aggree" value="Tidak" class="form-check-input radio_css" style="margin-top: 10px;" required>
                                <label style="top: 0.5rem;position: relative;margin-bottom: 0;vertical-align: top;margin-right:1.5rem;">Tidak</label>
                            </div>
                            <div class="col-md-6 col-sm-12 mt-3">
                                <div class="text-cemter">Menerima pada Tanggal</div>
                                <input type="text" name="agg_date" readonly required value="<?= retnull("ACC_F_PENGLAMAN_DATE", date('Y-m-d H:i:s')) ?>" class="form-control text-center mb-3 <?= empty($realAccount['ACC_F_PENGLAMAN_DATE'])? "realtime-date" : "" ?>">
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
        $("#form-pernyataan-pengalaman").on("submit", function(event) {
            event.preventDefault();
            let data = Object.fromEntries(new FormData(this).entries());
            if($("#pengalaman").val()?.toLowerCase() == "ya") {
                Swal.fire({
                    title: "Pernyataan Pengalaman",
                    input: "text",
                    inputLabel: "Perusahaan pialang berjangka",
                    inputValue: "<?= App\Models\ProfilePerusahaan::get()['COMPANY_NAME']; ?>",
                    customClass: {
                        inputLabel: 'swal2-title fs-6',
                        inputValue: 'swal2-title'
                    },
                    showCancelButton: true,
                    reverseButtons: true,
                    inputValidator: (value) => {
                        if (!value) {
                            return "You need to write something!";
                        }
                    }
                }).then((value) => {
                    if(value.isConfirmed) {
                        data.perusahaan = value.value;
                        Swal.fire({
                            text: "Please wait...",
                            allowOutsideClick: false,
                            didOpen: function() {
                                Swal.showLoading();
                            }
                        })
                        
                        $.post("/ajax/regol/pernyataanPengalaman", data, function(resp) {
                            if(!resp.success) {
                                Swal.fire(resp.alert);
                                return;
                            }
                            
                            location.href = resp.redirect
                        }, 'json')
                    }
                });
            
            }else {
                Swal.fire({
                    text: "Please wait...",
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                })
                
                $.post("/ajax/regol/pernyataanPengalaman", data, function(resp) {
                    Swal.fire(resp.alert).then(() => {
                        if(resp.success) {
                            location.href = resp.redirect
                        }
                    })
                }, 'json')
            }
        });
    });
</script>