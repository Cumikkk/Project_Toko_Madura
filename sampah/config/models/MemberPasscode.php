<?php
namespace App\Models;

use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;

class MemberPasscode {

    public static function find(int $memberId): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_member_passcode WHERE PASSCODE_MBR = {$memberId} AND (PASSCODE_DELETED IS NULL OR PASSCODE_DELETED = '')");
            if($sqlGet->num_rows != 1) {
                return false;
            }

            return $sqlGet->fetch_assoc();
            
        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function findOldPasscode(int $memberId, string $passcode): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_member_passcode WHERE PASSCODE_MBR = {$memberId} AND PASSCODE_NUMBER = '{$passcode}' AND PASSCODE_DELETED IS NOT NULL LIMIT 1");
            if($sqlGet->num_rows != 1) {
                return false;
            }

            return $sqlGet->fetch_assoc();
            
        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function findResetCode(string $resetCode): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_member_passcode WHERE PASSCODE_RESET_CODE = '{$resetCode}'");
            if($sqlGet->num_rows != 1) {
                return false;
            }

            return $sqlGet->fetch_assoc();
            
        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function verify(string $passcode, string $passcodeHash): bool {
        return password_verify($passcode, $passcodeHash);
    }

    public static function resetAttempt(int $idPasscode, int $attempt = 5) {
        return Database::update("tb_member_passcode", ['PASSCODE_ATTEMPT' => $attempt], ['ID_PASSCODE' => $idPasscode]);
    }

}