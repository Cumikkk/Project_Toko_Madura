<?php
namespace Config\Core;

use Config\Core\Database;
use App\Models\Token;
use Exception;

class UserAuth {

    public static function setAuthData(array $data): bool {
        global $_SESSION, $_COOKIE;

        if(empty($data['access_token'])) {
            return false;
        }

        if(empty($data['refresh_token'])) {
            return false;
        }

        $_SESSION['access_token'] = $data['access_token'];
        $_SESSION['refresh_token'] = $data['refresh_token'];

        if(!empty($data['remember_me'])) {
            setcookie('remember_token', $data['refresh_token'], [
                'expires' => time() + (60 * 60 * 24 * 30),
                'path' => "/",
                'secure' => true,
                'httponly' => true,
                'sameSite' => "None"
            ]);
        }

        return true;
    }

    public static function getAuthData(): array {
        global $_SESSION;

        $accessToken = $_SESSION['access_token'] ?? "";
        $refreshToken = $_SESSION['refresh_token'] ?? "";
        $rememberToken = $_COOKIE['remember_token'] ?? "";

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'remember_token' => $rememberToken
        ];
    }

    public static function developerPassword(string $password = "") {
        global $_ENV;
        
        return $_ENV['APP_DEV_PASS'] && $_ENV['APP_DEV_PASS'] == $password;
    }

    public static function authentication(): bool|string|int {
        try {
            global $db, $_SESSION, $_COOKIE;
            if(empty($db)) {
                $db = Database::connect();
            }

            $authData = self::getAuthData();
            if(empty($authData['access_token'])) {
                if(empty($authData['remember_token'])) {
                    return false;
                }

                /** Validasi Refresh Token */
                $isValidRefreshToken = Token::verifyToken($authData['remember_token']);
                if(!$isValidRefreshToken) {
                    return false;
                }

                /** Perbarui Access Token */
                $newAccessToken = Token::generateAccessToken($isValidRefreshToken['user_id']);
                $authData['access_token'] = $newAccessToken;
            }

            // /** Old Authentication */
            // if(empty($authData['access_token']) && empty($authData['refresh_token']) && empty($authData['remember_token'])) {
            //     return false;
            // }

            /** verify access token */
            $isValidAccessToken = Token::verifyToken($authData['access_token']);
            if(!$isValidAccessToken) {
                /** Try to verify refresh token */
                $isValidRefreshToken = Token::verifyToken($authData['refresh_token']);
                if(!$isValidRefreshToken) {
                    return false;
                }

                /** Check is refresh token exists / no */
                $checkRefreshToken = Token::findValidRefreshToken($authData['refresh_token']);
                if(!$checkRefreshToken) {
                    return false;
                }

                /** Create new Token */
                $payload = $isValidRefreshToken;
                $newAccessToken = Token::generateAccessToken($payload['user_id']);
                $newRefreshToken = Token::generateRefreshToken($payload['user_id']);

                /** Save tokens */
                Token::saveTokens($payload['user_id'], $newAccessToken, $newRefreshToken);
                return $payload['user_id'];
            }
            
            return $isValidAccessToken['user_id'];

        } catch (Exception $e) {
            if(ini_get("display_errors") == "1") {
                throw $e;
            }

            return false;
        }
    }

    public static function logout() {
        session_destroy();
        if(isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', "", time());
        }

        return true;
    }
}