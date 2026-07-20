<?php
namespace App\Factory;

use App\Models\Helper;
use App\Models\User;
use Config\Core\Database;
use Config\Core\SystemInfo;
use DateTime;
use Exception;

class UserOtpFactory {

    public const MAX_OTP_ATTEMPT = 5;

    public static function setOtp(int $otp, string $expired_at, ?int $mbrid = null): bool {
        try {
            $user = (!$mbrid)? User::user() : User::findByMemberId($mbrid);
            if(!$user) {
                return false;
            }

            return Database::update("tb_member", ['MBR_OTP' => $otp, 'MBR_OTP_EXPIRED' => $expired_at], ['MBR_ID' => $user['MBR_ID']]);

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function useOtp(int $otp, ?int $mbrid = null): bool|string {
        try {
            $user = (!$mbrid)? User::user() : User::findByMemberId($mbrid);
            if(!$user) {
                return false;
            }

            $deliveryDate = $user['MBR_OTP_EXPIRED'];
            $userOtp = $user['MBR_OTP'];

            if(empty($deliveryDate)) {
                return "Invalid DateRequest";
            }

            if(strtotime($deliveryDate) < time()) {
                return "Kode OTP kadaluarsa";
            }
            
            if($otp != $userOtp) {
                return "Kode OTP Salah";
            }

            Database::update("tb_member", ['MBR_OTP_EXPIRED' => date("Y-m-d H:i:s")], ['MBR_ID' => $user['MBR_ID']]);
            return true;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function isDelay(?int $mbrid = null): bool|string {
        try {
            $user = (is_null($mbrid))? User::user() : User::findByMemberId($mbrid);
            if(!$user) {
                return false;
            }

            $deliveryDate = $user['MBR_OTP_EXPIRED'];
            if(empty($deliveryDate)) {
                return true;
            }

            if(strtotime($deliveryDate) > time()) {
                $diff = (new DateTime())->diff(new DateTime($deliveryDate));
                if(!$diff) {
                    return false;
                }
    
                switch(true) {
                    case (!empty($diff->i)):
                        return "Anda harus menunggu {$diff->i} menit {$diff->s} detik sebelum mengirim ulang";
    
                    case (!empty($diff->s)):
                        return "Anda harus menunggu {$diff->s} detik sebelum mengirim ulang";
                }
            }

            return true;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

}