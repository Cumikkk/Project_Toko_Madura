<?php 

use App\Models\ProfilePerusahaan;
use Config\Core\SystemInfo;
$profilePerusahaan = ProfilePerusahaan::get();
?>
<form method="post" id="form-sid">
    <input type="hidden" name="csrf_token" value="<?= uniqid() ?>">
    <div class="card">
        <div class="card-header text-center">Single Investor Identification (SID) Khusus Transaksi Produk Derivatif Keuangan</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <p>Apakah anda sudah memiliki Single Investor Identification (SID)?</p>
                    <div class="mb-3" id="acc_type_cardp">
                        <div class="row" id="acc_type_list">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <input type="radio" class="form-check-input" name="acc_ket" value="1" <?php  echo (retnull("ACC_F_SID_KET") == 1 ? 'checked' : NULL) ?> id="mini" required><span for="mini" style="cursor: pointer;font-weight: bolder;vertical-align: sub;margin-left: 10px;">Ya, saya sudah memiliki SID <input type="text" class="text-center form-control" name="sid_nmbr" placeholder="Nomor SID" <?php  echo (retnull("ACC_F_SID_KET") == 1 ? 'required' : NULL) ?>></span><br>
                                        <label for="mini" style="cursor: pointer;margin-top:5px;">
                                            Dengan ini Nasabah menyetujui <?= $profilePerusahaan['COMPANY_NAME'] ?> dapat mengakses data Nasabah menggunakan Single Investor Identification (SID) dan/atau melakukan pengkinian data melalui sistem yang disediakan oleh KSEI apabila terdapat perubahan data Nasabah sebagai bagian dari penerapan Prinsip Mengenal Nasabah sesuai dengan ketentuan KSEI dan/atau Otoritas Jasa Keuangan (OJK) yang berlaku.
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <input type="radio" class="form-check-input" name="acc_ket" value="2" <?php echo (retnull("ACC_F_SID_KET") == 2 ? 'checked' : NULL) ?> id="regular" required> <span for="regular" style="cursor: pointer;font-weight: bolder;vertical-align: sub;margin-left: 10px;">Tidak, saya belum memiliki SID</span><br>
                                        <label for="regular" style="cursor: pointer;margin-top:5px;">
                                            <ol>
                                                <li>Dengan ini Nasabah menyetujui <?= $profilePerusahaan['COMPANY_NAME'] ?> akan melakukan pembuatan Single Investor Identification (SID) sesuai dengan ketentuan KSEI dan/atau Otoritas Jasa Keuangan (OJK) yang berlaku.</li>
                                                <li>Nasabah mengerti dan menyetujui bahwa <?= $profilePerusahaan['COMPANY_NAME'] ?> menyampaikan kepada KSEI data dan/atau dokumen pribadi namun tidak terbatas pada informasi yang telah Nasabah sampaikan kepada <?= $profilePerusahaan['COMPANY_NAME'] ?> dalam Aplikasi Pembukaan Rekening Transaksi untuk proses pembuatan Single Investor Identification (SID) dan/atau pengkinian data.</li>
                                            </ol>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-6 mt-3">
                        Pernyataan menerima/tidak <span class="text-danger">*</span><br>
                        <input type="radio" name="aggree" value="ya" class="form-check-input radio_css" style="margin-top: 10px;" required <?php echo (retnull("ACC_F_DISC", 1)) ? 'checked' : NULL ?>>
                        <label style="top: 0.5rem;position: relative;margin-bottom: 0;vertical-align: top;margin-right:1.5rem;">Ya</label>
                        <input type="radio" name="aggree" value="tidak" class="form-check-input radio_css" style="margin-top: 10px;" required>
                        <label style="top: 0.5rem;position: relative;margin-bottom: 0;vertical-align: top;margin-right:1.5rem;">Tidak</label>
                    </div>
                    <div class="col-6 mt-3">
                        <div class="text-cemter">Menerima pada Tanggal</div>
                        <input type="text" name="agg_date" readonly required value="<?php echo (retnull("ACC_F_DISC", 1)) ? retnull("ACC_F_DISC_DATE") : date('Y-m-d H:i:s') ?>" class="form-control text-center mb-3">
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
<script src="<?= SystemInfo::app('CLIENT_URL'); ?>/assets/plugins/jquery.maskedinput/jquery.maskedinput.js"></script>
<script>
    $(document).ready(() => {
        $(`[name="acc_ket"]`).on('change', function(e){
            if($(this).val() == '1'){
                $(`[name="sid_nmbr"]`).prop('required', true);
            }else{ $(`[name="sid_nmbr"]`).prop('required', false); }
        });

        $('input[name="sid_nmbr"]').mask('** - * - **** - ****** - **');
        $('input[name="sid_nmbr"]').on('input', function() {
            this.value = this.value.toUpperCase();
        })

        $('#form-sid').on('submit', function(event) {
            event.preventDefault();

            Swal.fire({
                text: "Please wait...",
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            })
            
            $.ajax({
                url: "/ajax/regol/formulirSID",
                type: "POST",
                dataType: "json",
                data: new FormData(this),
                processData: false,
                contentType: false,
                cache: false
            }).done(function(resp) {
                if(!resp.success) {
                    Swal.fire(resp.alert);
                    return;
                }
                
                location.href = resp.redirect
            });
        });
    });
</script>