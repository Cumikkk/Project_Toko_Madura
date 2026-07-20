<?php 

use App\Factory\FileUploadFactory;
use App\Models\Blog;
use App\Models\FileUpload;
use App\Models\Helper;
$newsSlug = Helper::form_input($_GET['detail'] ?? "");
$blog = Blog::findBySlug($newsSlug);
if(!$blog) {
    die("<script>alert('Invalid'); location.href = '/dashboard'; </script>");
}

?>
<div class="row mt-2">
    <div class="col-md-8">
        <div class="panel">
            <div class="card">
                <img src="<?= FileUploadFactory::aws()->awsFile($blog['BLOG_IMG']) ?>" class="card-img-top rounded" alt="">
                <div class="card-body">
                    <h5 class="card-title mb-3"><?= $blog['BLOG_TITLE'] ?></h5>
                    <hr>
                    <div style="text-align: justify;" class="small">
                        <?= str_replace(['\r\n', '&amp;', 'nbsp;'], ["<br>", ' ', ' '], html_entity_decode($blog['BLOG_MESSAGE'])) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="panel">
            <div class="card card-body">
                <h5 class="card-title mb-3">Related History</h5>
                <hr>
                <?php foreach(App\Models\Blog::get($blog['BLOG_TYPE'], 8) as $other) : ?>
                    <?php if($other['ID_BLOG'] != $blog['ID_BLOG']) : ?>
                        <a href="/news?detail=<?= $other['BLOG_SLUG'] ?>">
                            <div class="card mb-2">
                                <div class="row g-0">
                                    <div class="col-md-4">
                                        <img src="<?= FileUploadFactory::aws()->awsFile($other['BLOG_IMG']) ?>" class="img-fluid rounded-start h-100" alt="...">
                                    </div>
                                    <div class="col-md-8">
                                        <div class="card-body">
                                            <h5 class="small text-muted"><?php echo substr($other['BLOG_TITLE'], 0, 40); ?>...</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>