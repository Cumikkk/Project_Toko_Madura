<?php
namespace App\Models;

use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;

class Rate {

    public static int $isFixedRate = 1;
    public static int $isLiveRate = 2;

    public static function type(int $type): array {
        switch($type) {
            case self::$isFixedRate: 
                return [
                    'text' => "Fixed Rate",
                    'html' => '<span class="bg badge-info">Fixed Rate</span>'
                ];

            case self::$isLiveRate: 
                return [
                    'text' => "Live Rate",
                    'html' => '<span class="bg badge-info">Live Rate</span>'
                ];
        }

        return [
            'text' => "Unknown",
            'html' => '<span class="bg badge-dark">Unknown</span>'
        ];
    }

    public static function findByCurrency(string $from, string $to): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_rate WHERE UPPER(RATE_FROM) = UPPER('{$from}') AND UPPER(RATE_TO) = UPPER('{$to}') LIMIT 1");
            if($sqlGet->num_rows != 1) {
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

    public static function findById(string $id): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_rate WHERE MD5(MD5(ID_RATE)) = '{$id}' LIMIT 1");
            if($sqlGet->num_rows != 1) {
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

    public static function autoCheckRate(string $from, string $to): float|bool {
        try {
            $from = strtoupper($from);
            $to = strtoupper($to);

            if($from == $to) {
                return 1;
            }

            /** check manual configuration */
            $checkRate = self::findByCurrency($from, $to);
            if(!$checkRate) {
                return false;
            }

            /** check live / fixed */
            $rate = false;
            switch(true) {
                case ($checkRate['RATE_TYPE'] == self::$isFixedRate) :
                    $rate = $checkRate['RATE_AMOUNT'];
                    break;

                case ($checkRate['RATE_TYPE'] == self::$isLiveRate) :
                    $getFloatingRate = self::getFloatingRate_jisdor($from, $to);
                    if($getFloatingRate != 0) {
                        $rate = $getFloatingRate;
                    }
                    break;
            }

            return $rate;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function getFloatingRate(string $from, string $to) {
        $from = strtoupper($from ?? "");
        $to = strtoupper($to ?? "");

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://v6.exchangerate-api.com/v6/967f9b7d0a85b78ca3bc19e2/latest/{$from}",
            CURLOPT_TIMEOUT         => 30,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_ENCODING        => "",
            CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        $result = 0;

        if(!empty($error)) {
            return $error;
        }

        $resp = json_decode($response, true);
        if(is_array($resp) && array_key_exists("conversion_rates", $resp)) {
            foreach($resp['conversion_rates'] as $key => $val) {
                if(strtoupper($key) == strtoupper($to)) {
                    $result = rtrim(number_format($val, 10, ".", ""), "0");
                    break;
                }
            }
        }

        return $result;
    }

    public static function getFloatingRate_jisdor(string $from, string $to) {
        $from = strtoupper($from ?? "");
        $to = strtoupper($to ?? "");

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api-crm.techcrm.net/rate/?provider=jisdor",
            CURLOPT_TIMEOUT         => 30,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_ENCODING        => "",
            CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        $result = 0;

        if(!empty($error)) {
            return $error;
        }

        $resp = json_decode($response, true);
        if(is_array($resp) && array_key_exists("data", $resp)) {
            foreach($resp['data'] as $val) {
                $key = strtolower($to.$from);
                if(array_key_exists($key, $val)) {
                    $result = $val[ $key ];
                    break;
                }
            }
        }

        return $result;
    }

}