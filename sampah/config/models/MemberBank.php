<?php
namespace App\Models;

use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;

class MemberBank {

    public static $statusPending = 0;    
    public static $statusAccepted = -1;    
    public static $statusRejected = 1;  
    
    public static function status(int $status) {
        switch($status) {
            case self::$statusPending: 
                return [
                    'text' => "Pending",
                    'html' => '<span class="badge bg-warning">Pending</span>'
                ];

            case self::$statusAccepted: 
                return [
                    'text' => "Aktif",
                    'html' => '<span class="badge bg-success">Aktif</span>'
                ];

            case self::$statusRejected: 
                return [
                    'text' => "Ditolak",
                    'html' => '<span class="badge bg-danger">Ditolak</span>'
                ];
        }
    }

    public static function findByIdHash(string $idHash): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_member_bank WHERE MD5(MD5(ID_MBANK)) = '{$idHash}' LIMIT 1");
            if($sqlGet->num_rows == 0) {
                return false;
            }

            return $sqlGet->fetch_assoc() ?? false;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function activeBanks(int $mbrid): array {
        try {
            $userBanks = User::myBank($mbrid);
            $activeBanks = array_filter($userBanks, fn($ar): bool => ($ar['MBANK_STS'] == MemberBank::$statusAccepted));
            return $activeBanks;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function list(int $mbrid, array $status = []): array {
        try {
            $listOfStatus = [self::$statusPending, self::$statusAccepted, self::$statusRejected];
            if(empty($status)) {
                $status = $listOfStatus;
            }

            $db = Database::connect();
            $filteredStatus = array_filter($status, fn($sts): int => (in_array($sts, $listOfStatus)));
            $sqlGet = $db->query("SELECT * FROM tb_member_bank WHERE MBANK_MBR = $mbrid AND MBANK_STS IN (".implode(',', $filteredStatus).") ORDER BY MBANK_SORT");
            return $sqlGet->fetch_all(MYSQLI_ASSOC) ?? [];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }
}
