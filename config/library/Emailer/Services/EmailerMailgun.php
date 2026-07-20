<?php
namespace App\Library\Emailer\Services;

use App\Library\Emailer\EmailerAbstract;
use Config\Core\SystemInfo;
use Exception;
use Mailgun\Mailgun;

class EmailerMailgun extends EmailerAbstract {
    protected string $apiKey;
    protected string $mgReceiverEmail;
    protected string $mgReceiverName;
    protected string $domain;
    protected string $email;
    protected string $name;
    protected array $bcc = [];
    protected array $stringAttachment = [];
    protected array $internalEmails = []; 

    public function setCredential() {
        $this->domain = $_ENV['MAILGUN_DOMAIN'];
        $this->apiKey = $_ENV['MAILGUN_APIKEY'];
        $this->email = $_ENV['MAILGUN_EMAIL'];
        $this->name = $_ENV['MAILGUN_NAME'];
    }

    public function setReceiver(string $receiverEmail, string $receiverName) {
        $this->mgReceiverEmail = $receiverEmail;
        $this->mgReceiverName = $receiverName;
    }

    public function useInternal() {
        $this->internalEmails = require_once CONFIG_ROOT . "/email/config/internal_email.php";
    }

    public function addBcc(string $email, string $name, string $subject) {
        $this->bcc[] = ["email" => $email, "name" => $name, "subject" => $subject];
    }

    public function addStringAttachment(string $filename, string $url, ?string $type = "application/pdf", ?string $encoding = "base64") {
        $content = file_get_contents($url);
        if($content) {
            $this->stringAttachment[] = [
                'filename' => $filename, 
                'string' => $content,
                'encoding' => $encoding,
                'type' => $type
            ];
        }
    }

    public function send(): bool {
        if (empty($this->mgReceiverEmail)) {
            throw new Exception("[SEND] Email tujuan belum di set");
        }

        $contents = $this->parseFileContent($this->filepath, $this->fileData);
        if (empty($contents)) {
            throw new Exception("[SEND] Email Body Kosong");
        }

        try {
            $mg = Mailgun::create($this->apiKey);
            $params = [
                'from' => "{$this->name} <{$this->email}>",
                'to' => "{$this->mgReceiverName} <{$this->mgReceiverEmail}>",
                'subject' => $this->subject,
                'html' => $contents,
            ];

            /** Attachment */
            if($this->stringAttachment) {
                $params['attachment'] = [];
                foreach($this->stringAttachment as $stringAttachment) {
                    $params['attachment'][] = [
                        'filename' => $stringAttachment['filename'],
                        'fileContent' => $stringAttachment['string']
                    ];
                }
            }

            /** Pengiriman ke email utama */
            $result = $mg->messages()->send($this->domain, $params);
            $statusCode = $result->getStatusCode() ?? 0;
            
            /** Pengiriman ke email lainnya */
            if($statusCode == 200) {
                /** Pengiriman Email BCC (opsional) */
                if(count($this->bcc) > 0) {
                    foreach($this->bcc as $bcc) {
                        $params['to'] = "{$bcc['name']} <{$bcc['email']}>";
                        $params['subject'] = $bcc['subject'];
                        $mg->messages()->send($this->domain, $params);
                    }
                }

                /** Pengiriman Email Internal (opsional) */
                if(count($this->internalEmails) > 0) {
                    foreach($this->internalEmails as $internal) {
                        /** Hapus CC Sebelumnya */
                        unset($params['cc']);
                        
                        $params['to'] = "{$internal['name']} <{$internal['email']}>";
                        $params['subject'] = "[Internal] {$this->subject}";
                        
                        /** Menambahkan CC (jika ada) */
                        if(is_array($internal['cc'])) {
                            $params['cc'] = [];
                            foreach($internal['cc'] as $cc) {
                                if(!empty($cc['email'])) {
                                    $params['cc'][] = "{$cc['email']}";
                                }
                            }

                        }

                        $mg->messages()->send($this->domain, $params);
                    }
                }
            }

            return ($statusCode == 200);

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

}