<?php
namespace App\Factory;

use Allmedia\Shared\FileUpload\UploadAWS;

class FileUploadFactory {

    public static function aws(): UploadAWS
    {   
        $config = [
            'region' => @$_ENV['AWS_REGION'],
            'bucket' => @$_ENV['AWS_BUCKET'],
            'folder' => @$_ENV['AWS_FOLDER'],
        ];
        
        return new UploadAWS($config);
    }

}