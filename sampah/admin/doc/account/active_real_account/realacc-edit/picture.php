<?php
    use App\Factory\FileUploadFactory;
    use App\Models\FileUpload;
?>
<div class="card">
    <form action="" method="post" enctype="multipart/form-data" id="form-picture">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-2">
                    <div class="form-group text-center">
                        <label for="p-dokument-rekkor" class="form-label">Rekening Koran Bank / Tagihan Kartu Kredit / Rekening Listrik, Telepon / NPWP</label>
                        <input type="file" name="p-dokument-rekkor" id="p-dokument-rekkor" class="dropify form-control" value="<?php echo FileUploadFactory::aws()->awsFile(($account['ACC_F_APP_FILE_IMG'] ?? '' )); ?>" data-default-file="<?php echo FileUploadFactory::aws()->awsFile(($account['ACC_F_APP_FILE_IMG'] ?? '' )); ?>" data-height="300" data-max-file-size="5M" data-allowed-file-extensions="jpg jpeg png">
                    </div>
                </div>
    
                <div class="col-md-6 mb-2">
                    <div class="form-group text-center">
                        <label for="p-foto-terbaru" class="form-label">Foto Terbaru</label>
                        <input type="file" name="p-foto-terbaru" id="p-foto-terbaru" class="dropify form-control" value="<?php echo FileUploadFactory::aws()->awsFile(($account['ACC_F_APP_FILE_FOTO'] ?? '' )); ?>" data-default-file="<?php echo FileUploadFactory::aws()->awsFile(($account['ACC_F_APP_FILE_FOTO'] ?? '' )); ?>" data-height="300" data-max-file-size="5M" data-allowed-file-extensions="jpg jpeg png">
                    </div>
                </div>
    
                <div class="col-md-6 mb-2">
                    <div class="form-group text-center">
                        <label for="p-ktp&passport" class="form-label">KTP / PASSPORT</label>
                        <input type="file" name="p-ktp&passport" id="p-ktp&passport" class="dropify form-control" value="<?php echo FileUploadFactory::aws()->awsFile(($account['ACC_F_APP_FILE_ID'] ?? '' )); ?>"data-default-file="<?php echo FileUploadFactory::aws()->awsFile(($account['ACC_F_APP_FILE_ID'] ?? '' )); ?>" data-height="300" data-max-file-size="5M" data-allowed-file-extensions="jpg jpeg png">
                    </div>
                </div>

                <div class="col-md-6 mb-2">
                    <div class="form-group text-center">
                        <label for="p-dokument-pendukung" class="form-label">Dokument Lainnya</label>
                        <input type="file" name="p-dokument-pendukung" id="p-dokument-pendukung" class="dropify form-control" value="<?php echo FileUploadFactory::aws()->awsFile(($account['ACC_F_APP_FILE_IMG3'] ?? '' )); ?>" data-default-file="<?php echo FileUploadFactory::aws()->awsFile(($account['ACC_F_APP_FILE_IMG3'] ?? '' )); ?>" data-height="300" data-max-file-size="5M" data-allowed-file-extensions="jpg jpeg png">
                    </div>
                </div>
    
                <div class="col-md-6 mb-2">
                    <div class="form-group text-center">
                        <label for="p-dokument-pendukung-lainnya" class="form-label">Dokument Lainnya</label>
                        <input type="file" name="p-dokument-pendukung-lainnya" id="p-dokument-pendukung-lainnya" class="dropify form-control" value="<?php echo FileUploadFactory::aws()->awsFile(($account['ACC_F_APP_FILE_IMG4'] ?? '' )); ?>" data-default-file="<?php echo FileUploadFactory::aws()->awsFile(($account['ACC_F_APP_FILE_IMG4'] ?? '' )); ?>" data-height="300" data-max-file-size="5M" data-allowed-file-extensions="jpg jpeg png">
                    </div>
                </div>
            </div>  
        </div>
    
        <div class="card-footer">
            <button type="submit" name="submit-picture" class="btn btn-primary">Edit Data</button>
        </div>
    </form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/js/dropify.min.js" integrity="sha512-8QFTrG0oeOiyWo/VM9Y8kgxdlCryqhIxVeRpWSezdRRAvarxVtwLnGroJgnVW9/XBRduxO/z1GblzPrMQoeuew==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('.dropify').dropify();
        $('#form-picture').on('submit', function(ev){
            ev.preventDefault();
            let data = new FormData(this);
            data.append('sbmt_id', '<?= ($id_acc ?? '') ?>');
            $.ajax({
                url         : '/ajax/post/account/edit_document',
                type        : 'POST',
                dataType    : 'JSON',
                enctype     : 'multipart/form-data',
                data        : data,
                contentType : false,
                chache      : false,
                processData : false
            }).done((resp) => {
                $('#modal-datepicker').modal('hide');
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        if(resp?.data?.reloc?.length){
                            location.href = resp?.data?.reloc;
                        }else{ location.reload(); }
                    }
                });

            });
        });
    })
</script>