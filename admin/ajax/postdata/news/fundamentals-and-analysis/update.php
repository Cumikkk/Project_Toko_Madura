<?php
    
    use App\Factory\FileUploadFactory;
    use App\Models\Account;
    use App\Models\Dpwd;
    use App\Models\Helper;
    use App\Models\Admin;
use App\Models\Blog;
use App\Models\Logger;
    use App\Models\FileUpload;
    use Config\Core\Database;
    
    $listGrup = $adminPermissionCore->availableGroup();
    $adminRoles = Admin::adminRoles();
    if(!$adminPermissionCore->hasPermission($authorizedPermission, "/news/news-corner/update")) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Authorization Failed",
            'data'      => []
        ]);
    }

    $REQ_POST = [
        "edt-btn",
        "title",
        "author",
        "content",
    ];
    $data = Helper::getSafeInput($_POST);
    foreach($REQ_POST as $req) {
        if(in_array($req, ["k-fax-kantor"])){
            if(!isset($data[ $req ])) {
                $req = str_replace("add_", "", $req);
                JsonResponse([
                    'code'      => 402,
                    'success'   => false,
                    'message'   => "{$req} diperlukan",
                    'data'      => []
                ]);
            }
        }else{
            if(empty($data[ $req ])) {
                $req = str_replace("add_", "", $req);
                JsonResponse([
                    'code'      => 402,
                    'success'   => false,
                    'message'   => "{$req} diperlukan",
                    'data'      => []
                ]);
            }
        }
    }

    /** Check ID */
    $blog = Blog::findById($data['edt-btn']);
    if(!$blog){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "News data not found.",
            'data'      => []
        ]);
    }

    /** create new slug */
    $slug = Blog::createSlug($data['title'], $blog['ID_BLOG']);
    if(!$slug) {
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "News data not found.",
            'data'      => []
        ]);
    }

    if(isset($data['highlight'])){
        $highlight = -1;
    } else {
        $highlight = 0;
    }

    if(isset($data['protected'])){
        $protected = -1;
    } else {
        $protected = 0;
    }

    /**Stored data for update*/
    $UPDATE_DATA = [
        "BLOG_TITLE"      => $data["title"],
        "BLOG_MESSAGE"    => htmlentities($_POST["content"]),
        "BLOG_AUTHOR"     => $data["author"],
        "BLOG_SLUG"       => $slug,
        "BLOG_HIGHLIGHT"  => $highlight,
        "BLOG_PROTECTED"  => $protected
    ];

    /** Cek file post */
    if((isset($_FILES["files"])) && $_FILES["files"]["error"] == 0){

        /** Upload file*/ 
        $PRCSF = FileUploadFactory::aws()->upload_single($_FILES["files"], 'news');
        if(!is_array($PRCSF)){
            JsonResponse([
                'success'   => false,
                'message'   => "Failed to upload file. Please try again!. ErrMessage: ".$PRCSF,
                'data'      => []
            ]);
        }
        $UPDATE_DATA["BLOG_IMG"] = $PRCSF["filename"];

    }
    

    /**Eksekusi database*/
    $update = Database::update('tb_blog', $UPDATE_DATA, ["ID_BLOG" => $blog["ID_BLOG"]]);
    if(!$update){
        JsonResponse([
            'code'      => 200,
            'success'   => false,
            'message'   => "Failed to update data.",
            'data'      => []
        ]);
    }

    Logger::admin_log([
        'admid' => $user['ADM_ID'],
        'module' => "news/news-corner",
        'message' => "Update news",
        'data'  => $data
    ]);

    JsonResponse([
        'code'      => 200,
        'success'   => true,
        'message'   => "Success Update News",
        'data'      => []
    ]);