<?php
namespace App\Factory;

use App\Library\Telegram\TelegramNotification;
use App\Library\Telegram\TReceiverData;
use App\Library\Telegram\TRequestData;
use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;

class TelegramFactory {

    private static function receiver() {
        global $_ENV;
        $listBot = [];
        $envName = ['TELEGRAM_BOT'];

        foreach($envName as $name) {
            if(!empty($_ENV[ $name ])) {
                $explode = explode(",", $_ENV[ $name ]);
                if(is_array($explode) && count($explode) == 2) {
                    $listBot[] = new TReceiverData($explode[0], $explode[1]);
                }
            }
        }
        
        return $listBot;
    }

    private static function saveLog(TelegramNotification $telegramNotification) {
        try {
            foreach($telegramNotification->logSend as $log) {
                Database::insert("tb_log_telegram", [
                    'LOGTELE_URL' => $log['url'],
                    'LOGTELE_PAYLOAD' => json_encode($log['payload'] ?? []),
                    'LOGTELE_RESPONSE' => json_encode($log ?? []),
                    'LOGTELE_ERROR' => $log['error'],
                    'LOGTELE_CODE' => $log['code'],
                    'LOGTELE_DATETIME' => date("Y-m-d H:i:s"),
                ]);
            }

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }
        }
    }

    public static function internalTransferNotification(array $data) {
        try {
            $requestData = new TRequestData(self::receiver(), "internal-transfer", $data);
            $telegramNotification = new TelegramNotification($requestData);
            $telegramNotification->send();

            /** Save Log */
            self::saveLog($telegramNotification);

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }
}