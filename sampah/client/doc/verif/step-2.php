<?php

use App\Factory\FileUploadFactory;
die("<script>location.href = '/dashboard';</script>");
use App\Models\FileUpload;
use App\Models\User;

$isVerified = User::isVerified($user['MBR_ID']);
// if(isset($_GET['lewati'])) {
//     $update = mysqli_query($db, "UPDATE tb_member SET MBR_VERIF = -1, MBR_STS = -1 WHERE MBR_ID = ".$user['MBR_ID']."");
//     if(!$update || !mysqli_affected_rows($db)) {
//         die("<script>alert('Gagal melewati. mohon coba lagi'); location.href= '/verif/step-2'; </script>");
//     }

//     newInsertLog([
//         'mbrid' => $user['MBR_ID'],
//         'module' => "verification",
//         'message' => "Selesai verifikasi data diri (lewati)",
//         'data'  => json_encode($_POST)
//     ]);

//     die("<script>location.href= '/dashboard'; </script>");
// }

?>

<style>
    .dropify-message .file-icon p {
        font-size: 20px;
    }
</style>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="panel">
            <div class="card">
                <div class="card-body">
                    <form action="" method="POST" id="form-step-2">
                        <input type="hidden" name="csrf_token" value="">
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <label for="fullname" class="form-label required">Fullname</label>
                                <input type="text" class="form-control" name="fullname" id="fullname" placeholder="Fullname" value="<?= $user['MBR_NAME'] ?>" required>
                                <small class="text-sm" style="font-size: 0.7em;"><b class="text-info">*</b>This column will also replace the full name when registering</small>
                            </div>

                            <div class="col-md-8" mb-2>
                                <label for="place_of_birth" class="form-label required">Place of birth</label>
                                <input type="text" name="place_of_birth" id="place_of_birth" class="form-control" value="<?php echo $user['MBR_TMPTLAHIR'] ?? ""; ?>" placeholder="Place of birth" required>
                            </div>
    
                            <div class="col-md-4" mb-2>
                                <label for="date_of_birth" class="form-label required">Date of birth</label>
                                <input type="date" name="date_of_birth" id="date_of_birth" class="form-control datepicker" value="<?= date("m/d/Y", strtotime($user['MBR_TGLLAHIR'])) ?? ""; ?>" placeholder="Date of birth" required>
                            </div>
    
                            <div class="col-md-6 mb-2">
                                <label for="" class="form-label required">Type Identity</label>
                                <select name="type_idt" class="form-control" required>
                                    <option value="" disabled>Select</option>
                                    <option value="KTP" selected>KTP</option>
                                </select>
                            </div>
    
                            <div class="col-md-6 mb-2">
                                <label for="" class="form-label required">No. Identity</label>
                                <input type="number" name="no_idt" class="form-control" value="<?= $isVerified['MBRFILE_NUMBER'] ?? ""; ?>" placeholder="NIK" required>
                            </div>

                            <div class="col-md-6 mb-2">
                                <label for="ktp_photo" class="form-label required">KTP photo</label>
                                <input type="file" class="dropify" name="ktp_photo" id="ktp_photo" <?= (!empty($isVerified['MBRFILE_PHOTO1']))? 'data-default-file="'.(FileUploadFactory::aws()->awsFile($isVerified['MBRFILE_PHOTO1'])).'"' : ""; ?> data-min-width="480" data-min-height="640" data-max-file-size="2M" ata-allowed-file-extensions="png jpg jpeg">
                            </div>

                            <div class="col-md-6 mb-2">
                                <label for="selfie_photo" class="form-label required">Selfie photo</label>
                                <input type="file" class="dropify" name="selfie_photo" id="selfie_photo" <?= (!empty($isVerified['MBRFILE_PHOTO2']))? 'data-default-file="'.(FileUploadFactory::aws()->awsFile($isVerified['MBRFILE_PHOTO2'])).'"' : ""; ?> data-min-width="480" data-min-height="640" data-max-file-size="4M" ata-allowed-file-extensions="png jpg jpeg">
                            </div>
            
                            <div class="col-md-12 w-100">
                                <button type="submit" name="next" class="btn btn-primary float-end">Next</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('.dropify').dropify();
        $('#form-step-2').on('submit', function(event) {
            event.preventDefault();
            
            let object = Object.fromEntries(new FormData(this).entries());
            let button = $(this).find('button[type="submit"]');
            button.addClass('loading');
            $.ajax({
                url: "/ajax/post/verif/step-2",
                type: "post",
                dataType: "json",
                data: new FormData(this),
                cache: false,
                processData: false,
                contentType: false
            })
            .done(function(resp) {
                button.removeClass('loading');
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        location.href = resp.data.redirect;
                    }
                })
            })
        })
    })
</script>