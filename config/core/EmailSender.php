<?php
namespace Config\Core;

use App\Library\Emailer\EmailerAbstract;
use App\Library\Emailer\Services\EmailerMailgun;
use App\Library\Emailer\Services\EmailerSMTP;
use Exception;

class EmailSender {

    public static function init(array $receiver, ?string $type = null): EmailerAbstract|bool {
        try {
            if (empty($receiver['name'])) {
                throw new Exception("Receiver Name is required");
            }
            
            if (empty($receiver['email'])) {
                throw new Exception("Receiver Email is required");
            }
    
            $type ??= $_ENV['APP_EMAILER'];
            switch($type) {
                case "smtp": 
                    $instance = new EmailerSMTP($type);
                    break;

                case "mailgun": 
                    $instance = new EmailerMailgun($type);
                    break;

                default: return false;
            }

            $instance->setCredential();
            $instance->setReceiver($receiver['email'], $receiver['name']);
            return $instance;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

}