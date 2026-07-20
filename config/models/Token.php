<?php
namespace App\Models;

use Config\Core\Database;
use Config\Core\TokenGenerator;
use Exception;

class Token extends TokenGenerator {
    public function __construct() {

    }

    public static function saveTokens(int $userId, $accessToken, $refreshToken, ?string $deviceId = null, ?string $deviceName = null): array|bool {
        try {
            global $db;
            if(!$db) {
                return false;
            }

            /** Set token for specific device */
            $idDevice = null; // id From tb_member_device
            if(!empty($deviceId)) {
                $device = self::setForDevice($userId, $deviceId, $deviceName);
                if(is_array($device) && array_key_exists("id", $device)) {
                    $idDevice = $device['id'];
                }
            }
    
            $accessTokenExpires = date('Y-m-d H:i:s', time() + ACCESS_TOKEN_LIFETIME);
            $refreshTokenExpires = date('Y-m-d H:i:s', time() + REFRESH_TOKEN_LIFETIME);
    
            return Database::insert("tb_member_token", [
                'mbr_id' => $userId,
                'device_id' => $idDevice,
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'access_token_expires' => $accessTokenExpires,
                'refresh_token_expires' => $refreshTokenExpires,
            ]);

        } catch (Exception $e) {
            if(ini_get("display_errors") == "1") {
                throw $e;
            }

            return false;
        }
    }

    public static function findValidRefreshToken($token): bool|array {
        global $db;
        if(!$db) {
            return false;
        }

        $sqlGet = $db->query("SELECT * FROM tb_member_token WHERE refresh_token = '$token' AND refresh_token_expires > NOW() AND is_revoked = 0");
        return $sqlGet->fetch_assoc() ?? false;
    }

    public static function revokeToken(string $token): bool {
        global $db;
        if(!$db) {
            return false;
        }

        $sqlUpdate = $db->prepare("DELETE FROM tb_member_token WHERE refresh_token = '$token'");
        return $sqlUpdate->execute();
    }

    public static function revokeAccessToken(string $accessToken): bool {
        global $db;
        if(!$db) {
            return false;
        }

        $sqlUpdate = $db->prepare("UPDATE tb_member_token SET is_revoked = 1 WHERE access_token = '$accessToken'");
        return $sqlUpdate->execute();
    }

    public static function clearToken(int $userId): bool {
        global $db;
        if(!$db) {
            return false;
        }

        $sqlUpdate = $db->prepare("DELETE FROM tb_member_token WHERE mbr_id = {$userId} AND is_revoked = 1");
        return $sqlUpdate->execute();
    }

    public static function setForDevice(string $userid, string $deviceId, string $deviceName = "unknown"): bool|array {
        /** Sql Create Or Update */
        global $db;
        $sqlQuery = $db->prepare("
            INSERT INTO tb_member_device (
                device_mbr, 
                device_name, 
                device_id, 
                last_ip_address, 
                created_at
            ) VALUES (?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE
                last_ip_address = ?,
                updated_at = ?
        ");

        $lastIpAddress = Helper::get_ip_address();
        $createdAt = date("Y-m-d H:i:s");
        $sqlQuery->bind_param("issssss", $userid, $deviceName, $deviceId, $lastIpAddress, $createdAt, $lastIpAddress, $createdAt);
        if(!$sqlQuery->execute()) {
            return false;
        }

        return [
            'id' => $db->insert_id,
            'device_id' => $deviceId,
            'device_name' => $deviceName,
            'ip_address' => $lastIpAddress,
            'created_at' => $createdAt
        ];
    }
}