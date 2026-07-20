<div class="row">
    <div class="col-md-9 mx-auto">
        <form method="post" id="form-verifikasi-identitas">
            <input type="hidden" name="csrf_token" value="<?= uniqid(); ?>">
            <div class="card">
                <div class="card-body">
                    <div class="text-center"><h5>VERIFIKASI IDENTITAS</h5></div>
                    <hr>
                    <div class="alert alert-info mb-3">
                        <small>Dokumen yang telah diverifikasi, tidak dapat dirubah</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3 h-100">
                            <label for="app_foto_terbaru" class="form-label required">Foto Terbaru (Selfie)</label>
                            <input type="file" class="dropify" id="app_foto_terbaru" name="app_foto_terbaru" <?= empty($realAccount['ACC_F_APP_FILE_FOTO'])? "required" : "skip" ?>
                                data-max-file-size="4M"
                                data-min-width="480"
                                data-min-height="640"
                                data-show-remove="false"
                                data-allowed-file-extensions="png jpg jpeg" 
                                data-default-file="<?= App\Factory\FileUploadFactory::aws()->awsFile($realAccount['ACC_F_APP_FILE_FOTO'] ?? ""); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="app_foto_identitas" class="form-label required">Foto KTP</label>
                            <input type="file" class="dropify" id="app_foto_identitas" name="app_foto_identitas" <?= empty($realAccount['ACC_F_APP_FILE_ID'])? "required" : "skip" ?>
                                data-max-file-size="2M"
                                data-min-width="480"
                                data-min-height="320"
                                data-show-remove="false"
                                data-allowed-file-extensions="png jpg jpeg" 
                                data-default-file="<?= App\Factory\FileUploadFactory::aws()->awsFile($realAccount['ACC_F_APP_FILE_ID'] ?? ""); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <p class="">*Foto Selfie</p>
                        <ul>
                            <li class="form-label">- Minimal ukuran file 100KB dan Maksimal 4MB</li>
                            <li class="form-label">- Minimal dimensi file 480x640 (p x l)</li>
                        </ul>
                    </div>
                    <div class="mb-3">
                        <p class="">*Foto KTP</p>
                        <ul>
                            <li class="form-label">- Minimal ukuran file 100KB dan Maksimal 2MB</li>
                            <li class="form-label">- Minimal dimensi file 480x320 (p x l)</li>
                        </ul>
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
        $('.dropify').dropify();
        $('#form-verifikasi-identitas').on('submit', function(event) {
            event.preventDefault();
            Swal.fire({
                text: "Please wait...",
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            })

            $.ajax({
                url: "/ajax/regol/verifikasiIdentitas",
                type: "POST",
                dataType: "json",
                data: new FormData(this),
                processData: false,
                contentType: false,
                cache: false
            }).done(function(resp) {
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        location.href = resp.redirect;
                    }
                })
            })
        })
    })
</script>