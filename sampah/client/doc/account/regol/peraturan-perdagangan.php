<div class="row">
    <div class="col-md-9 mx-auto">
        <form method="post" id="form-peraturan-perdagangan">
            <input type="hidden" name="csrf_token" value="<?= uniqid(); ?>">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="text-center">
                                <h5>PERATURAN PERDAGANGAN<br><i>(TRADING RULES)</i></h5>
                            </div>

                            <object data="<?= App\Models\Regol::urlTradingRule($realAccount['RTYPE_FILE']); ?>" width="100%" height="100vh" style="min-height: 500px; max-height: 720px;" type="application/pdf">
                                <embed src="<?= App\Models\Regol::urlTradingRule($realAccount['RTYPE_FILE']); ?>" type="application/pdf" />
                            </object>

                            <p class="mt-4 mb-0">Jika dokumen gagal memuat, baca PERATURAN PERDAGANGAN (TRADING RULES) <a target="_blank" href="<?= App\Models\Regol::urlTradingRule($realAccount['RTYPE_FILE']); ?>" class="fw-bold text-decoration-underline">Disini</a></p>
                           
                            <p class="mt-4 mb-0">Biaya yang dikenakan setiap pelaksanaan transaksi: <b>$<?= $realAccount['RTYPE_KOMISI'] ?></b></p>
                            <p>Dengan mengisi kolom “YA” di bawah ini, saya menyatakan bahwa saya telah membaca tentang PERATURAN PERDAGANGAN (TRADING  RULES), mengerti dan menerima ketentuan dalam bertransaksi.</p>
                            <div class="row mt-3">
                                <div class="col-6 mt-3">
                                    Pernyataan menerima/tidak<br>
                                    <input type="radio" name="aggree" value="Ya" class="form-check-input radio_css" style="margin-top: 10px;" required <?= !empty($realAccount['ACC_F_TRDNGRULE']) ? "checked" : "" ?>>
                                    <label style="top: 0.5rem;position: relative;margin-bottom: 0;vertical-align: top;margin-right:1.5rem;">Ya</label>
                                    <input type="radio" name="aggree" value="Tidak" class="form-check-input radio_css" style="margin-top: 10px;" required>
                                    <label style="top: 0.5rem;position: relative;margin-bottom: 0;vertical-align: top;margin-right:1.5rem;">Tidak</label>
                                </div>
                                <div class="col-6 mt-3">
                                    <div class="text-cemter">Menerima pada Tanggal</div>
                                    <input type="text" name="agg_date" readonly required value="<?= retnull("ACC_F_TRDNGRULE_DATE", date('Y-m-d H:i:s')) ?>" class="form-control text-center mb-3 <?= empty($realAccount['ACC_F_TRDNGRULE_DATE'])? "realtime-date" : "" ?>">
                                </div>
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
        $('#form-peraturan-perdagangan').on('submit', function(event) {
            event.preventDefault();
            let data = Object.fromEntries(new FormData(this).entries());

            Swal.fire({
                text: "Please wait...",
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            })
            
            $.post("/ajax/regol/peraturanPerdagangan", data, function(resp) {
                if(!resp.success) {
                    Swal.fire(resp.alert);
                    return;
                }
                
                location.href = resp.redirect
            }, 'json')
        })
    })
</script>