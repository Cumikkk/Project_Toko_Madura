<?php
namespace App\Models;

require_once(__DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php');
require_once(__DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php');
require_once(__DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php');
require_once(__DIR__ . "/../vendor/autoload.php");

use PHPmailer\PHPMailer\Exception;
use PHPmailer\PHPMailer\PHPMailer;
use PHPmailer\PHPMailer\SMTP;
use Dotenv\Dotenv;

class SendEmail {
    /** Default Setting class */
    protected string $emailFolder       = (__DIR__ . "/../email/");

    /** Class Variable */
    protected $mail;
    protected $debug;
    protected $config;
    protected $file     = "";
    protected $fileData = [];
    protected $contents = "";
    protected $emailDestination = "";
    protected $fullnameDestination = "";
    protected $subject  = ""; 


    public function __construct(bool $debug = false) {
        $this->debug = $debug;
        $this->mail = new PHPMailer(true);
    }

    public function useDefault() {
        $this->config = (object) [
            'host'          => $_ENV['EMAIL_HOST'],
            'user'          => $_ENV['EMAIL_USER'],
            'password'      => $_ENV['EMAIL_PASSWORD'],
            'encryptMode'   => ($_ENV['EMAIL_SECURE'] == "default"? PHPMailer::ENCRYPTION_SMTPS : $_ENV['EMAIL_SECURE']),
            'port'          => $_ENV['EMAIL_PORT'],
            'name'          => $_ENV['EMAIL_NAME']
        ];

        return $this;
    }

    public function initialize(array $config = []) {
        if(!empty($this->config)) {
            throw new Exception("[INITIALIZE] Anda tidak dapat mengatur ulang konfigurasi, gunakan salah satu fungsi useDefault() atau initialize()");
        }

        foreach(['host', 'user', 'password', 'encryptMode', 'port'] as $data) {
            if(!array_key_exists($data, $config) || empty($config[ $data ])) {
                throw new Exception("[INITIALIZE] key {$data} is required");
            }

            $this->config[$data] = $config[ $data ];
        }

        return $this;
    }

    private function getFilepath($filename) {
        return $this->emailFolder . $filename . ".php";
    }

    public function getConfiguration() {
        return $this->config;
    }

    public function useFile(string $filename, array $data) {
        $path   = $this->getFilepath($filename);
        if(!file_exists($path)) {
            throw new Exception("[USEFILE] File tidak ditemukan");
        }

        if(!array_key_exists("subject", $data)) {
            throw new Exception("[USEFILE] Subject diperlukan");
        }

        $this->file     = $path;
        $this->fileData = $data;
        $this->subject  = $data['subject'];
        return $this;

    }

    private function parseFileContent(string $path, array $data) {
        if(!file_exists($path)) throw new Exception("[GET] Can't Parsing Files Not Found");

        /** Extract Array */
        $data['content'] = $path;
        extract($data, EXTR_OVERWRITE);
        ob_start();
        require_once $this->emailFolder . "template.php";

        return ob_get_clean();
    }

    public function get() {
        if(empty($this->fileData)) throw new Exception("[GET] Mohon daftarkan nama file terlebih dahulu");
        return $this->parseFileContent($this->file, $this->fileData);
    }

    public function destination($emailDestination, $fullnameDestination) {
        $this->emailDestination = $emailDestination;
        $this->fullnameDestination = $fullnameDestination;

        return $this;
    }

    public function send() {
        if(empty($this->config)) {
            throw new Exception("[SEND] Konfigurasi belum diatur, atur terlebih dahulu");
        }

        if(empty($this->emailDestination) || empty($this->fullnameDestination)) {
            throw new Exception("[SEND] Email tujuan belum diatur");
        }

        if(!$this->mail->validateAddress($this->emailDestination)) {
            throw new Exception("[SEND] Email {$this->emailDestination} Tidak Valid");
        }

        /** Parse Content */
        $this->contents = $this->parseFileContent($this->file, $this->fileData);
        if(empty($this->contents)) {
            throw new Exception("[SEND] Email Body Kosong");
        }
        
        try {
            $this->mail->isHTML(true);
            $this->mail->isSMTP();
            $this->mail->SMTPSecure = $this->config->encryptMode;
            $this->mail->SMTPAuth   = true;
            $this->mail->Host       = $this->config->host;
            $this->mail->Username   = $this->config->user;
            $this->mail->Password   = $this->config->password;
            $this->mail->Port       = $this->config->port;

            /** Destination */
            $this->mail->setFrom($this->config->user, $this->config->name);
            $this->mail->addAddress($this->emailDestination, $this->fullnameDestination);
            
            /** Body */
            $this->mail->Subject    = $this->subject;
            $this->mail->Body       = $this->contents;
            
            /** Send */
            return $this->mail->send();
            
        } catch (Exception $e) {
            throw $e;
        } 
    }
}