<div class="row">
    <div class="col-md-9 mx-auto">
        <form method="post" id="form-pernyataan-pengungkapan-4">
            <input type="hidden" name="csrf_token" value="<?= uniqid(); ?>">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <h4 class="card-title text-center">SURAT PERNYATAAN</h4>
                            <hr>
                            <div class="text-justify">
                            <p>Saya, yang bertanda tangan di bawah ini :</p>
                            <div class="table-responsive mb-3">
                                <table class="table table-hover" style="text-align: left; table-layout: fixed;" width="100%">
                                    <tr>
                                        <td width="30%" class="top-align fw-bold">Nama</td>
                                        <td width="3%" class="top-align"> : </td>
                                        <td class="top-align text-start"><?= $realAccount['ACC_FULLNAME'] ?></td>
                                    </tr>
                                    <tr>
                                        <td width="30%" class="top-align fw-bold">Alamat</td>
                                        <td width="3%" class="top-align"> : </td>
                                        <td class="top-align text-start"><?= $realAccount['ACC_ADDRESS'] ?></td>
                                    </tr>
                                    <tr>
                                        <td width="30%" class="top-align fw-bold">Jenis Identitas / No. Identitas</td>
                                        <td width="3%" class="top-align"> : </td>
                                        <td class="top-align text-start"><?= $realAccount['ACC_TYPE_IDT'] ?>, <?= $realAccount['ACC_NO_IDT'] ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="mb-3">
                                Dengan ini menerangkan dan menyatakan dengan sebenar-benarnya bahwa saya telah mendapat
                                penjelasan dari : <strong><?= App\Models\ProfilePerusahaan::get()['COMPANY_NAME']; ?></strong> yang berkedudukan di Jakarta, melalui Wakil Pialang
                                Berjangka yang bernama <strong><?= App\Models\ProfilePerusahaan::wpb_verifikator()['WPB_NAMA'] ?? "-" ?></strong> mengenai mekanisme transaksi perdagangan
                                berjangka yang akan saya lakukan sendiri. Saya juga :
                            </div>
                            <ol>
                                <li>Telah sepenuhnya membaca, mengerti, serta memahami penjelasan mengenai isi dokumen
                                Perjanjian Pemberian Amanat Nasabah, dokumen Pemberitahuan Adanya Risiko, serta semua
                                ketentuan dan peraturan perdagangan (<i>tradingrules</i>);</li>
                                <li>Telah menerima penjelasan dan mengerti bahwa hanya Wakil Pialang Berjangka yang berhak
                                menjelaskan dokumen Pemberitahuan Adanya Risiko, dokumen Perjanjian Pemberian Amanat,
                                serta peraturan perdagangan (<i>tradingrules</i>);</li>
                                <li>Telah menerima penjelasan dan mengerti bahwa <i>user id</i> dan <i>password</i> bersifat pribadi dan rahasia
                                sehingga tidak akan menyerahkan kepada pihak manapun termasuk kepada Wakil Pialang
                                Berjangka, pihak yang dipekerjakan maupun pihak yang diberdayakan Pialang Berjangka, segala
                                risiko akibat penyerahan <i>user id</i> dan <i>password</i> kepada pihak lain menjadi tanggung jawab saya; dan</li>
                                <li>Telah menerima penjelasan dan mengerti mekanisme penyelesaian perselisihan dan pilihan tempat
                                penyelesaian perselisihan yakni melalui Badan Arbitrase atau Pengadilan Negeri.</li>
                            </ol>
                            <div class="mb-3">
                                Terhadap apa yang saya jalankan dalam transaksi ini berikut segala risiko yang akan timbul akibat
                                transaksi sepenuhnya akan menjadi tanggung jawab saya.
                            </div>
                            <div class="mb-3">
                                Bersama ini saya menyatakan bahwa dana yang saya gunakan untuk bertransaksi di <strong><?= App\Models\ProfilePerusahaan::get()['COMPANY_NAME']; ?></strong> 
                                adalah milik saya pribadi dan bukan dana pihak lain, serta tidak d iperoleh dari
                                hasil kejahatan, penipuan, penggelapan, tindak pidana korupsi, tindak pidana narkotika , tindak pidana di
                                bidang kehutanan, hasil pencucian uang, dan perbuatan melawan hukum lainnya serta tidak dimaksudkan
                                untuk melakukan pencucian uang dan/atau pendanaan terorisme.
                            </div>
                            <div class="mb-3">
                                Demikian surat pernyataan ini saya buat dalam keadaan sadar, sehat jasmani dan rohani serta tanpa paksaan dari pihakmanapun.
                            </div>
                            <div class="row mt-3">
                                <div class="col-6 mt-3">
                                    Pernyataan menerima/tidak<br>
                                    <input type="radio" name="aggree" value="Ya" class="form-check-input radio_css" style="margin-top: 10px;" required <?= !empty($realAccount['ACC_F_DISC4'])? "checked" : ""; ?>>
                                    <label style="top: 0.5rem;position: relative;margin-bottom: 0;vertical-align: top;margin-right:1.5rem;">Ya</label>
                                    <input type="radio" name="aggree" value="Tidak" class="form-check-input radio_css" style="margin-top: 10px;" required>
                                    <label style="top: 0.5rem;position: relative;margin-bottom: 0;vertical-align: top;margin-right:1.5rem;">Tidak</label>
                                </div>
                                <div class="col-6 mt-3">
                                    <div class="text-center">Menerima pada Tanggal</div>
                                    <input type="text" name="agg_date" readonly required value="<?= retnull("ACC_F_DISC_DATE4", date('Y-m-d H:i:s')) ?>" class="form-control text-center mb-3 <?= empty($realAccount['ACC_F_DISC_DATE4'])? "realtime-date" : "" ?>">
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
        $('#form-pernyataan-pengungkapan-4').on('submit', function(event) {
            event.preventDefault();
            let data = Object.fromEntries(new FormData(this).entries());

            Swal.fire({
                text: "Please wait...",
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            })
            
            $.post("/ajax/regol/pernyataanPengungkapan_4", data, function(resp) {
                if(!resp.success) {
                    Swal.fire(resp.alert);
                    return;
                }
                
                location.href = resp.redirect
            }, 'json')
        })
    })
</script>