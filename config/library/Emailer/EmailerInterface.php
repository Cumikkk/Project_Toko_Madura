<?php
namespace App\Library\Emailer;

interface EmailerInterface {

    public function setCredential();
    
    public function setReceiver(string $receiverEmail, string $receiverName);

    public function useInternal();

    public function addBcc(string $email, string $name, string $subject);
    
    public function addStringAttachment(string $filename, string $url, ?string $type = "application/pdf", ?string $encoding = "base64");
    
    public function send(): bool;

}