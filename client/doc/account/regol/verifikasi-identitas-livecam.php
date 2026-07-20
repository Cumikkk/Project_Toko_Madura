<?php $_SESSION['modal'] = ['modal-verifikasi-identitas', 'modal-verifikasi-ktp']; ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pica/9.0.1/pica.min.js" integrity="sha512-FH8Ofw1HLbwK/UTvlNBxsfICDXYZBr9dPuTh3j17E5n1QZjaucKikW6UwMREFo7Z42AlIigHha3UVwWepr0Ujw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pica/9.0.1/pica.js" integrity="sha512-7vHtytbFByrP/eYEQWNj6cvI1Skm1bw/Yta8SIuy9SykDcVvTUmMR+vGf1S3aNM8CusU1NlpA9nJGGsIGI76kg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
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
                            <label for="app_foto_terbaru" class="form-label">Foto Terbaru (Selfie) <span class="text-danger">*</span></label>
                            <?php if(!empty($realAccount['ACC_F_APP_FILE_FOTO'])) : ?>
                                <input type="file" class="dropify" id="app_foto_identitas" name="app_foto_identitas" data-allowed-file-extensions="png jpg jpeg" data-default-file="<?= $aws_folder . $realAccount['ACC_F_APP_FILE_FOTO'] ?>" disabled>
                            <?php endif; ?>
                            <button type="button" class="btn btn-primary btn-full mt-2 w-100" data-bs-toggle="modal" data-bs-target="#createacc_verifikasi_identitas_foto_selfie">Ambil Gambar</button>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="app_foto_identitas" class="form-label">Foto KTP <span class="text-danger">*</span></label>
                            <?php if(!empty($realAccount['ACC_F_APP_FILE_ID'])) : ?>
                                <input type="file" class="dropify" id="app_foto_identitas" name="app_foto_identitas" data-allowed-file-extensions="png jpg jpeg" data-default-file="<?= $aws_folder . $realAccount['ACC_F_APP_FILE_ID'] ?? ""; ?>" disabled>
                            <?php endif; ?>
                            <button type="button" class="btn btn-primary btn-full mt-2 w-100" data-bs-toggle="modal" data-bs-target="#createacc_verifikasi_identitas_foto_ktp">Ambil Gambar</button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <p class="text-white">*Foto Selfie</p>
                        <ul>
                            <li class="form-label">- Minimal ukuran file 100KB dan Maksimal 4MB</li>
                            <li class="form-label">- Minimal dimensi file 480x640 (p x l)</li>
                        </ul>
                    </div>
                    <div class="mb-3">
                        <p class="text-white">*Foto KTP</p>
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