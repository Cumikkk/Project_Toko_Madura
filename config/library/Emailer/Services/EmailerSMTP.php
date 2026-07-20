<?php
namespace App\Library\Emailer\Services;

use App\Library\Emailer\EmailerAbstract;
use Config\Core\SystemInfo;
use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;
use PHPMailer\PHPMailer\PHPMailer;

class EmailerSMTP extends EmailerAbstract {
    protected string $host = "";
    protected string $email = "";
    protected string $password = "";
    protected string $port = "";
    protected string $name = "";
    protected string $secure = "";
    protected string $smtpReceiverName = "";
    protected string $smtpReceiverEmail = "";
    protected array $bcc = [];
    protected array $stringAttachment = [];
    protected array $internalEmails = []; 

    public function setCredential(): self {
        try {
            $this->host = $_ENV['EMAIL_HOST'];
            $this->email = $_ENV['EMAIL_USER'];
            $this->password = $_ENV['EMAIL_PASSWORD'];
            $this->port = $_ENV['EMAIL_PORT'];
            $this->name = $_ENV['EMAIL_NAME'];
            $this->secure = ($_ENV['EMAIL_SECURE'] == "default")? PHPMailer::ENCRYPTION_SMTPS : $_ENV['EMAIL_SECURE'];  
            
            return $this;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw new Exception("Failed to set credential of EmailSender");
            }

            return $this;
        }
    }

    public function setReceiver(string $receiverEmail, string $receiverName) {
        $this->smtpReceiverName = $receiverName;
        $this->smtpReceiverEmail = $receiverEmail;
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
        $phpMailer = new PHPMailer();
        if(!$phpMailer->validateAddress($this->smtpReceiverEmail)) {
            throw new Exception("[SEND] Email {$this->smtpReceiverEmail} Tidak Valid");
        }

        /** Parse Content */
        $contents = $this->parseFileContent($this->filepath, $this->fileData);
        if(empty($contents)) {
            throw new Exception("[SEND] Email Body Kosong");
        }
        
        try {
            $phpMailer->isHTML(true);
            $phpMailer->isSMTP();
            $phpMailer->SMTPSecure = $this->secure;
            $phpMailer->SMTPAuth = true;
            $phpMailer->Host = $this->host;
            $phpMailer->Username = $this->email;
            $phpMailer->Password = $this->password;
            $phpMailer->Port = $this->port;

            /** Destination */
            $phpMailer->setFrom($this->email, $this->name);
            $phpMailer->addAddress($this->smtpReceiverEmail, $this->smtpReceiverName);

            /** Body */
            $phpMailer->Subject = $this->subject;
            $phpMailer->Body = $contents;

            /** Attachment */
            if($this->stringAttachment) {
                foreach($this->stringAttachment as $stringAttachment) {
                    $phpMailer->addStringAttachment($stringAttachment['string'], $stringAttachment['filename'], $stringAttachment['encoding'], $stringAttachment['type']);
                }
            }

            /** Send */
            $sendFirstEmail = $phpMailer->send();

            if($sendFirstEmail) {
                /** Pengiriman Email Internal Broadcast (opsional) */
                if(count($this->bcc) > 0) {
                    foreach($this->bcc as $bcc) {
                        if(!empty($bcc['email']) && !empty($bcc['name']) && !empty($bcc['subject'])) {
                            /** Clear pervious address */
                            $phpMailer->clearAddresses();

                            /** Destination */
                            $phpMailer->setFrom($this->email, $this->name);
                            $phpMailer->addAddress(($bcc["email"] ?? ""), $bcc["name"]);
                            
                            /** Body */
                            $phpMailer->Subject = $bcc['subject'];
                            $phpMailer->Body = $contents;
    
                            /** Send */
                            $phpMailer->send();
                        }
                    }
                }

                /** Pengiriman Email Internal (opsional) */
                if(count($this->internalEmails) > 0) {
                    foreach($this->internalEmails as $internalEmail) {
                        if(!empty($internalEmail['email']) && !empty($internalEmail['name'])) {
                            /** Clear pervious address */
                            $phpMailer->clearAddresses();
                            $phpMailer->clearCCs();

                            /** Destination */
                            $phpMailer->setFrom($this->email, $this->name);
                            $phpMailer->addAddress(($internalEmail["email"] ?? ""), $internalEmail["name"]);
                            
                            /** Body */
                            $phpMailer->Subject = "[Internal] {$this->subject}";
                            $phpMailer->Body = $contents;

                            /** Add CC (opsional) */
                            if(is_array($internalEmail['cc'])) {
                                foreach($internalEmail['cc'] as $ccEmail) {
                                    if(!empty($ccEmail['email']) && !empty($ccEmail['name'])) {
                                        $phpMailer->addCC($ccEmail['email'], $ccEmail['name']);
                                    }
                                }
                            }

                            /** Send */
                            $phpMailer->send();
                        }
                    }
                }
            }

            return $sendFirstEmail;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    } 

}