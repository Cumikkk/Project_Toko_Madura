<?php
namespace App\Factory;

use Config\Core\SystemInfo;
use Exception;

class ApiGatewayRrfx {

    public static function getPromotions(string $category = "rewards", string $type = "PROMOTION", int $limit = 3, bool $highlight = true): array 
    {
        try {
            $isHighlight = ($highlight)? "true" : "false";
            $apiUrl = "https://gateway.rrfx.co.id/api/v1/contents/category/{$category}?type={$type}&limit={$limit}&highlight={$isHighlight}";
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
    
            $response = curl_exec($curl);
            $error = curl_error($curl);
            $resp = json_decode($response, true);
            $result = [];

            if(!empty($error)) {
                return [];
            }
    
            if(isset($resp['data']) && is_array($resp['data'])) {
                if(!empty($resp['data']['list'])) {
                    foreach($resp['data']['list'] as $data) {
                        $result[] = $data;
                    }
                }
            }

            return $result;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

}