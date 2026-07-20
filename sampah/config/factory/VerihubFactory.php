<?php
namespace App\Factory;

use Allmedia\Shared\Verihubs\Verihubs;
use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;

class VerihubFactory {

    public static function init(?Database $db = null): Verihubs {
        $db ??= new Database();
        return new Verihubs($db);
    }

    public static function findAccountLog(int $mbrid, string $idAccHash): array {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_log_verihub WHERE LOGVER_MBR = {$mbrid} AND LOGVER_ACC = '{$idAccHash}'");
            return $sqlGet->fetch_all(MYSQLI_ASSOC) ?? [];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }
}