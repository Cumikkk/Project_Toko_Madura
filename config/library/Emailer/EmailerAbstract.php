<?php
namespace App\Library\Emailer;

use App\Models\ProfilePerusahaan;
use Config\Core\SystemInfo;
use Exception;

abstract class EmailerAbstract implements EmailerInterface {

    protected string $emailTemplatefolder;
    protected string $filepath;
    protected string $filename;
    protected string $subject;
    protected array $fileData = [];
    protected string $emailer;


    public function __construct(?string $_emailer = null) {
        $_emailer ??= $_ENV['APP_EMAILER'];
        $this->emailer = $_emailer;
        $this->emailTemplatefolder ??= (CONFIG_ROOT . "/email");
    }

    protected function getFilepath() {
        return "{$this->emailTemplatefolder}/{$this->filename}.php";
    }

    public function useFile(string $filename, array $data) {
        $this->filename = $filename;
        $this->filepath = $this->getFilepath();
        if(!file_exists($this->filepath)) {
            throw new Exception("[USEFILE] File tidak ditemukan");
        }

        if(!array_key_exists("subject", $data)) {
            throw new Exception("[USEFILE] Subject diperlukan");
        }

        $this->fileData = $data;
        $this->subject  = $data['subject'];
    }

    protected function parseFileContent(string $path, array $data) {
        if(!file_exists($path)) throw new Exception("[GET] Can't Parsing Files Not Found");

        /** Extract Array */
        $profile = ProfilePerusahaan::get();
        $data['content'] = $path;
        $data['app_url'] = SystemInfo::app('CLIENT_URL');
        $data['profile'] = [
            'name' => $profile['COMPANY_NAME'],
            'phone' => $profile['OFFICE']['OFC_PHONE'],
            'support' => $profile['OFFICE']['OFC_EMAIL'],
            'website' => $profile['PROF_HOMEPAGE'],
            'address' => $profile['OFFICE']['OFC_ADDRESS'],
            'no_bappebti' => $profile['PROF_NO_IZIN_USAHA'],
        ];
        
        extract($data, EXTR_OVERWRITE);
        ob_start();
        require_once  "{$this->emailTemplatefolder}/template.php";
        return ob_get_clean();
    }

    public function getHtml() {
        if(empty($this->filepath)) throw new Exception("[GET] Mohon daftarkan nama file terlebih dahulu");
        return $this->parseFileContent($this->filepath, $this->fileData);
    }
    
}