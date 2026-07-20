<?php
namespace App\Models;

use Config\Core\SystemInfo;

if(!class_exists('Aws\S3\S3Client')) {
    require_once __DIR__ . "/../vendor/autoload.php";
}

use Exception;
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

class FileUpload {
    
    public const PNG = ["png", "image/png"];
    public const JPG = ["jpg", "image/jpeg"];
    public const JPEG = ["jpeg", "image/jpeg"];
    public const WEBP = ["webp", "image/webp"];
    public const PDF = ["pdf", "application/pdf"];


    public static $maxFileSize = 5 * 1024 * 1024; // 5MB
    public static $defaultUploadFolder= "/assets/uploads";
    public static $error_messages     = array(
        UPLOAD_ERR_OK         => 'There is no error, the file uploaded with success',
        UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload',
    );

    /** Aws Credential */
    public static $awsCredential = [
        'region'    => "",
        'bucketName' => "",
        'folder' => "",
        'key' => "",
        'secretKey' => ""
    ];

    public function __construct()
    {
        //Do your magic here
    }

    public static function credential() {
        Self::$awsCredential = [
            'region'    => $_ENV['AWS_REGION'] ?? "",
            'bucketName' => $_ENV['AWS_BUCKET'] ?? "",
            'folder' => $_ENV['AWS_FOLDER'] ?? "",
            'key' => $_ENV['AWS_KEY'] ?? "",
            'secretKey' => $_ENV['AWS_SECRET'] ?? ""
        ]; 

        return Self::$awsCredential;
    }

    public static function awsUrl(): string {
        Self::credential();
        $awsUrl = "https://";
        $awsUrl .= Self::$awsCredential['bucketName'];
        $awsUrl .= ".s3." . Self::$awsCredential['region'];
        $awsUrl .= ".amazonaws.com";
        $awsUrl .= "/" . Self::$awsCredential['folder'];

        return $awsUrl;
    }

    public static function awsFile(?string $filename): string {
        return Self::awsUrl() . "/" . $filename;
    }

    public static function displayAllowedFileType($allowedExt = []): string {
        if(empty($allowedExt)) {
            $allowedExt = array_merge(
                self::PNG, 
                self::JPG, 
                self::JPEG, 
                self::WEBP, 
                self::PDF, 
            );
        }

        $displayAllowed = array_filter($allowedExt, fn($ext) => !str_contains($ext, "image/") && !str_contains($ext, "application/"));
        return implode(", ", $displayAllowed);
    }

    public static function upload_myfile($files, $dir = "uploads", bool $compress = false, int $quality = 25, $allowedExt = []): string|array {
        try {
            Self::credential();
            if(empty($files) || $files['error'] != 0) {
                return self::$error_messages[ $files['error'] ] ?? "[ERROR] Upload file gagal";
            }
    
            $target_dir     = WEB_ROOT . Self::$defaultUploadFolder;
            $file_info      = pathinfo($files['name']); #file info
            $new_file_name  = $dir."_".time().rand(1000000, 9999999).".".$file_info['extension']; #create new file name
    
            /** check if extension allowed */
            if(empty($allowedExt)) {
                $allowedExt = array_merge(
                    self::PNG, 
                    self::JPG, 
                    self::JPEG, 
                    self::WEBP, 
                    self::PDF, 
                );
            }

            $displayAllowed = self::displayAllowedFileType($allowedExt);
            if(!in_array($files['type'], $allowedExt) && !in_array($file_info['extension'], $allowedExt)) {
                return "[Invalid file type], Mohon upload file {$displayAllowed}";
            }
            
            if(strpos($files['type'], "image/") !== false || in_array($file_info['extension'], ["png", "jpg", "jpeg", "webp"])) {
                $compress = false;
                $image_size = getimagesize($files['tmp_name']);
                if(!is_array($image_size)) {
                    return "Fail to get detail of image";
                }

                $image_width    = $image_size[0] ?? 0;
                $image_height   = $image_size[1] ?? 0;
                if(!$image_width || !$image_height) { // Cek Dimensi
                    return "Invalid Dimension";    
                } 
            }
            
            /** Cek Size Document */
            if($files['size'] > self::$maxFileSize) {
                return "[Invalid file size], Max ukuran file " . (self::$maxFileSize / (1024 * 1024)) . "mb";
            }

            /** check directory */
            if(!file_exists($target_dir)) {
                return "[Invalid Directory], Folder tidak ditemukan";
            }
    
            /** Compress image ?? */
            $status_upload  = false;
            $destination    = $target_dir . "/" . $new_file_name;
            switch ($compress) {
                case (true): 
                    $status_upload = Self::compressImage($files['tmp_name'], $destination, $quality); 
                    break;
    
                case (false): 
                    $status_upload = move_uploaded_file($files['tmp_name'], $destination); 
                    break;
    
                default: return "[Invalid Action] Unknown Action";
            }
    
            
            /** Check Upload File */
            if($status_upload !== TRUE) {
                return "Gagal upload file, mohon coba lagi";
            }
    
            /** New File Path for AWS */
            $credential  = Self::$awsCredential;
            $file_path   = $credential['folder'] . "/" . $new_file_name;
            $local_dir   = $destination;
    
            
            /** Get File Mime Type */
            $mime = mime_content_type($local_dir);
            if($mime === FALSE) {
                return "Invalid File Type";
            }
    
            /** Upload to AWS */
            $s3 = new S3Client([
                'region'  => $credential['region'],
                'version' => 'latest',
                'credentials' => [
                    'key'    => $credential['key'],
                    'secret' => $credential['secretKey'],
                ]
            ]);

            $result = $s3->putObject([
                'Bucket' => $credential['bucketName'],
                'Key'    => $file_path,
                'Body'   => fopen($local_dir, 'r'),
                'ACL'    => 'public-read', // make file 'public'
            ]);

            /** Delete file from local disk */
            unlink($local_dir);

            return [
                'filename'  => $new_file_name,
                'dir'       => $target_dir,
                'size'      => $files['size'],
                'extension' => $files['type'],
                'mime'      => $mime
            ];
            
        } catch (Exception $e) {
            return "Internal Server Error: FileUpload";

        } catch (S3Exception $e) {
            if(ini_get("display_errors") == 1) {
                throw $e;
            }
            
            return "Internal Server Error: FileUploadAWS";
        }
    }

    public static function compressImage($source, $destination, $quality) {
        try {
            // Get image info 
            $imgInfo = getimagesize($source); 
            $mime = $imgInfo['mime']; 
            
            // Create a new image from file 
            switch($mime){ 
                case 'image/jpeg': 
                    $image = imagecreatefromjpeg($source); 
                    break; 
                case 'image/png': 
                    $image = imagecreatefrompng($source); 
                    break; 
                case 'image/gif': 
                    $image = imagecreatefromgif($source); 
                    break; 
                default: 
                    $image = imagecreatefromjpeg($source); 
            } 

            if(is_bool($image)) {
                return false;
            }
            
            // Return compressed image 
            return imagejpeg($image, $destination, $quality); 
            
        } catch (Exception $e) {
            if(ini_get("display_errors") == "1") {
                throw $e;
            }

            return "Internal Server Error: Compress Upload";
        }
    }

    public static function upload_pdf($files, $dir = "uploads"): string|array {
        try {
            if(empty($files) || $files['error'] != 0) {
                return $error_messages[ $files['error'] ] ?? "[ERROR] Upload file gagal";
            }
    
            $target_dir     = WEB_ROOT . Self::$defaultUploadFolder;
            $file_info      = pathinfo($files['name']); #file info
            $new_file_name  = $dir."_".time().rand(1000000, 9999999).".".$file_info['extension']; #create new file name
    
            /** check if extension allowed */
            $image_ext = ["application/pdf", "pdf"];
            if(!in_array($files['type'], $image_ext) && !in_array($file_info['extension'], $image_ext)) {
                return "[Invalid file type], Mohon upload file ".implode(", ", $image_ext);
            }
            
            /** Cek file size , max 5mb */
            $image_size = $files["size"];
            if(!$image_size || $image_size > (5 * 1024 * 1024)) {
                return "[Invalid file size], Max ukuran file 5mb";
            }
    
            /** check directory */
            if(!file_exists($target_dir)) {
                return "[Invalid Directory], Folder tidak ditemukan";
            }

            /** upload file to local */
            $destination = $target_dir . "/" . $new_file_name;
            $uploadLocal = move_uploaded_file($files['tmp_name'], $destination); 
            if(!$uploadLocal) {
                return "Gagal upload file";
            }

            /** New File Path for AWS */
            Self::credential();
            $credential = Self::$awsCredential;
            $file_path = $credential['folder'] . "/" . $new_file_name;
            $local_dir = $destination;
    
            
            /** Get File Mime Type */
            $mime = mime_content_type($local_dir);
            if($mime === FALSE) {
                return "Invalid File Type";
            }
    
            /** Upload to AWS */
            $s3 = new S3Client([
                'region'  => $credential['region'],
                'version' => 'latest',
                'credentials' => [
                    'key'    => $credential['key'],
                    'secret' => $credential['secretKey'],
                ]
            ]);

            $result = $s3->putObject([
                'Bucket' => $credential['bucketName'],
                'Key'    => $file_path,
                'Body'   => fopen($local_dir, 'r'),
                'ACL'    => 'public-read', // make file 'public'
            ]);

            /** Delete file from local disk */
            unlink($local_dir);

            return [
                'filename'  => $new_file_name,
                'dir'       => $target_dir,
                'size'      => $files['size'],
                'extension' => $files['type'],
                'mime'      => $mime
            ];


        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return "Internal Server Error [400]";

        } catch (S3Exception $s3) {
            if(SystemInfo::isDevelopment()) {
                throw $s3;
            }
            
            return "Internal Server Error: FileUploadAWS";
        }
    }
}