<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card text-center h-100">
            <a target="_blank" href="<?= ($progressAccount['ACC_F_APP_FILE_IMG'] == '') ? 'javascript:void(0);' : App\Factory\FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_IMG']); ?>">
                <img width="75%" src="<?= ($progressAccount['ACC_F_APP_FILE_IMG'] == '') ? '/assets/img/no-image-available.jpg' : App\Factory\FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_IMG']); ?>">
            </a>
            <div class="card-body">
                <a target="_blank" href="<?php echo App\Factory\FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_IMG']); ?>">
                    Rekening Koran Bank / Tagihan Kartu Kredit / Rekening Listrik, Telepon / NPWP
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card text-center h-100">
            <a target="_blank" href="<?php echo App\Factory\FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_FOTO']); ?>">
                <img width="75%" src="<?= ($progressAccount['ACC_F_APP_FILE_FOTO'] == '') ? '/assets/img/no-image-available.jpg' : App\Factory\FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_FOTO']); ?>">
            </a>
            <div class="card-body">
                <a target="_blank" href="<?php echo App\Factory\FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_FOTO']); ?>">
                    Foto Terbaru (Selfie)
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card text-center h-100">
            <a target="_blank" href="<?php echo App\Factory\FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_ID']); ?>">
                <img width="75%" src="<?= ($progressAccount['ACC_F_APP_FILE_ID'] == '') ? '/assets/img/no-image-available.jpg' : App\Factory\FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_ID']); ?>">
            </a>
            <div class="card-body">
                <a target="_blank" href="<?php echo App\Factory\FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_ID']); ?>">
                    Foto Identitas
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card text-center h-100">
            <a target="_blank" href="<?php echo App\Factory\FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_IMG3']); ?>">
                <img width="75%" src="<?= ($progressAccount['ACC_F_APP_FILE_IMG3'] == '') ? '/assets/img/no-image-available.jpg' : App\Factory\FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_IMG3']); ?>">
            </a>
            <div class="card-body">
                <a target="_blank" href="<?php echo App\Factory\FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_IMG3']); ?>">
                    Dokumen Lainnya
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card text-center h-100">
            <a target="_blank" href="<?php echo App\Factory\FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_IMG4']); ?>">
                <img width="75%" src="<?= ($progressAccount['ACC_F_APP_FILE_IMG4'] == '') ? '/assets/img/no-image-available.jpg' : App\Factory\FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_IMG4']); ?>">
            </a>
            <div class="card-body">
                <a target="_blank" href="<?php echo App\Factory\FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_IMG4']); ?>">
                    Dokumen Lainnya
                </a>
            </div>
        </div>
    </div>
</div>