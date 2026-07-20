<?php
    
    use App\Factory\FileUploadFactory;
    use App\Models\FileUpload;


?>
<div class="page-header">
	<div>
		<h2 class="main-content-title tx-24 mg-b-5"><?php echo $vp = 'Banner'; ?></h2>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
			<li class="breadcrumb-item active" aria-current="page"><?php echo $vp; ?></li>
		</ol>
	</div>
    <div class="d-flex">
        <div class="justify-content-center">
            <button type="button" class="btn btn-primary my-2 btn-icon-text" data-bs-target="#modaldemo3" data-bs-toggle="modal">
                <i class="fe fe-plus me-2"></i> Add Banner
            </button>
        </div>
    </div>
</div>

<div class="modal" id="modaldemo3">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content modal-content-demo">
            <form method="post" id="add-banner-form">
                <div class="modal-header">
                    <h6 class="modal-title">Add Banner</h6><button aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Desc</label>
                                <input type="text" name="desc" class="form-control text-center" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <input type="file" name="file" accept="image/jpeg, image/png, image/jpg" class="dropify" data-height="200" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn ripple btn-primary" name="" type="submit">Submit</button>
                    <button class="btn ripple btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="row row-sm">
    <?php
        $SQL_BANNERS = mysqli_query($db, '
            SELECT
                tb_banner.BAN_FILE,
                tb_banner.BAN_DESC,
                tb_banner.BAN_STS,
                MD5(MD5(tb_banner.ID_BAN)) AS X
            FROM tb_banner
            ORDER BY tb_banner.ID_BAN DESC
        ');
        if($SQL_BANNERS && mysqli_num_rows($SQL_BANNERS) > 0){
            foreach(mysqli_fetch_all($SQL_BANNERS, MYSQLI_ASSOC) as $RSLT_BANNERS){
    ?>
        <div class="col-md-6 col-lg-3">
            <div class="card overflow-hidden custom-card ">
                <img alt="Image" class="img-fluid b-img" src="<?= FileUploadFactory::aws()->awsFile($RSLT_BANNERS["BAN_FILE"]) ?>" data-responsive="<?= FileUploadFactory::aws()->awsFile($RSLT_BANNERS["BAN_FILE"]) ?>" data-src="<?= FileUploadFactory::aws()->awsFile($RSLT_BANNERS["BAN_FILE"]) ?>" data-sub-html="<h4>Gallery Image 1</h4><p> Many desktop publishing packages and web page editors now use Lorem Ipsum</p>">
                <div class="card-body">
                    <p class="card-text"><?= $RSLT_BANNERS["BAN_DESC"] ?></p>
                </div>
                <div class="card-footer text-end">
                    <button type="button" class="btn btn-info showhide" value="<?= $RSLT_BANNERS["X"] ?>">
                        <?php
                            switch ($RSLT_BANNERS["BAN_STS"]) {
                                case -1:
                                    echo '<i class="fas fa-eye"></i>';
                                    break;
                                
                                default:
                                    echo '<i class="fas fa-eye-slash"></i>';
                                break;
                            }
                        ?>
                    </button>
                    <button type="button" class="btn btn-danger dltBtn" value="<?= $RSLT_BANNERS["X"] ?>"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>
    <?php
            }
        }
    ?>
</div>
<script>
    $(document).ready(() => {

        $('.showhide').on('click', function(e){
            $.post("/ajax/post/banner/change_status", {x: $(this).val()}, function(resp) {
                if(resp.success){
                    $(e.delegateTarget).html((resp?.data?.icon?.length) ? atob(resp?.data?.icon) : '');
                }
            }, 'json');
        });

        $('.dltBtn').on('click', function(e){
            Swal.fire({
                title: `Delete banner`,
                text: `Are you sure to delete this banner?`,
                icon: 'question',
                showCancelButton: true,
                reverseButtons: true
            }).then((result) => {
                if(result.isConfirmed) {
                    $.post("/ajax/post/banner/delete", {x: $(this).val()}, function(resp) {
                        Swal.fire(resp.alert).then(() => {
                            if(resp.success) {
                                location.reload();
                            }
                        })
                    }, 'json');
                }
            });
        });

        $('#add-banner-form').on('submit', function(ev){
            event.preventDefault();

            let data = new FormData(this);
            $.ajax({
                url         : '/ajax/post/banner/create',
                type        : 'POST',
                dataType    : 'JSON',
                enctype     : 'multipart/form-data',
                data        : data,
                contentType : false,
                chache      : false,
                processData : false
            }).done((resp) => {
                if(!resp.success) {
                    $('#modaldemo3').modal('hide');
                    Swal.fire(resp.alert);
                    return false;
                }else{
                    $('#modaldemo3').modal('hide');
                    Swal.fire(resp.alert).then(() => {
                        if(resp.success) {
                            location.reload();
                        }
                    });
                }

            });
        });
    });
</script>