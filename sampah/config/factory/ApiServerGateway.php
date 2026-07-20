<?php
namespace App\Factory;

use Config\Core\SystemInfo;
use Exception;

class ApiServerGateway {
    Public static function gettoken() {
        try {
            $apiUrl = 'https://restapi-rrfx.techcrm.dev/auth/login';
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode([
                    "username" => "admin",
                    "password" => "admin123"
                ]),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json'
                ],
            ]);
    
            $response = curl_exec($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if(!empty($error)) {
                return null;
            }

            $resp = json_decode($response, true);
            return $resp['token'] ?? null;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return null;
        }
    }
}