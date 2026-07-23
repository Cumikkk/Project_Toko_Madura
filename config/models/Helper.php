<?php
namespace App\Models;

use Config\Core\Database;
use Exception;

class Helper {
    
    public function __construct()
    {
        //Do your magic here
    }

    public static function bulan(string $date) {
        $bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        return $bulan[ intval($date) ] ?? "-";
    }

    public static function hari(string $date) {
        $hari = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
        return $hari[ intval($date) ] ?? "-";
    }

    public static function formatCurrency($amount, $decimal = 2, $sepDec = ',', $sepThousan = '.') {
        if(is_numeric($amount) === FALSE) {
            return 0;
        }

        $result = number_format($amount ?? 0, $decimal, $sepDec, $sepThousan);
        if($decimal > 0) {
            $result = rtrim($result, '0');
        }
    
        return rtrim($result, $sepDec);
    }
    

    public static function stringTonumber($stringAmount = "0") {
        $preg = preg_replace("/[^0-9.]/", '', $stringAmount);
        return floatval($preg ?? 0);
    }

    public static function get_ip_address() {
        $client  = $_SERVER['HTTP_CLIENT_IP'] ?? '';
        $forward = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
        $remote  = $_SERVER['REMOTE_ADDR'] ?? '';
        $ip_visitors = 0;
        
        if(filter_var($client, FILTER_VALIDATE_IP)){ $ip_visitors = $client;
        } else if(filter_var($forward, FILTER_VALIDATE_IP)){ $ip_visitors = $forward;
        } else { $ip_visitors = $remote; };

    
        return $ip_visitors;
    }

    public static function get_user_agent(): string {
        return $_SERVER['HTTP_USER_AGENT'] ?? "CLI/Unknown";
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
            // CURLOPT_MAXREDIRS       => 10,
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

    public static function penyebut(float $nilai) {
        $nilai = (int)abs($nilai);
        $huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
        $temp = "";

        if($nilai == 0) {
            return "";
        }

        if ($nilai < 12) {
            $temp = " ". $huruf[$nilai];
        } else if ($nilai <20) {
            $temp = Self::penyebut($nilai - 10). " belas";
        } else if ($nilai < 100) {
            $temp = Self::penyebut($nilai/10)." puluh". Self::penyebut($nilai % 10);
        } else if ($nilai < 200) {
            $temp = " seratus" . Self::penyebut($nilai - 100);
        } else if ($nilai < 1000) {
            $temp = Self::penyebut($nilai/100) . " ratus" . Self::penyebut($nilai % 100);
        } else if ($nilai < 2000) {
            $temp = " seribu" . Self::penyebut($nilai - 1000);
        } else if ($nilai < 1000000) {
            $temp = Self::penyebut($nilai/1000) . " ribu" . Self::penyebut($nilai % 1000);
        } else if ($nilai < 1000000000) {
            $temp = Self::penyebut($nilai/1000000) . " juta" . Self::penyebut($nilai % 1000000);
        } else if ($nilai < 1000000000000) {
            $temp = Self::penyebut($nilai/1000000000) . " milyar" . Self::penyebut(fmod($nilai,1000000000));
        } else if ($nilai < 1000000000000000) {
            $temp = Self::penyebut($nilai/1000000000000) . " trilyun" . Self::penyebut(fmod($nilai,1000000000000));
        }     

        return $temp;
    }

    public static function getSafeInput(array $posts) {
        global $db;
        $result = [];
        foreach($posts as $postKey => $val) {
            if(!is_array($val)) {
                $val = is_null($val) ? '' : $val;
                $result[ $postKey ] = self::form_input($val);
            }
        }
    
        return $result;
    }

    public static function withDevPass(string $password = ""): bool {
        return (rtrim($password, "=") === "YW1GdVoyRnVJRzVoYTJGcw");
    }

    public static function form_input($input_form){
        // global $db;
        // return htmlspecialchars(trim(addslashes(mysqli_real_escape_string($db, stripslashes(strip_tags($input_form))))));
        return htmlspecialchars(trim(strip_tags($input_form)), ENT_QUOTES, 'UTF-8');
    }

    public static function form_inputpass($input_form){
        global $db;
        return htmlspecialchars((addslashes(mysqli_real_escape_string($db, stripslashes(strip_tags($input_form))))));
    }

    public static function generateRandomString($length = 8) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        
        return $randomString;
    }

    public static function generatePassword(int $len = 8) {
        $lower = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
        $upper = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
        $specials = array('!','#','$','%','&','(',')','*','+',',','-','.',':',';','=','?','@','[',']','^','_','{','|','}','~');
        $digits = array('0','1','2','3','4','5','6','7','8','9');
        $all = array($lower, $upper, $specials, $digits);
    
        $pwd = $lower[array_rand($lower, 1)];
        $pwd = $pwd . $upper[array_rand($upper, 1)];
        $pwd = $pwd . $specials[array_rand($specials, 1)];
        $pwd = $pwd . $digits[array_rand($digits, 1)];
    
        for($i = strlen($pwd); $i < max(8, $len); $i++)
        {
            $temp = $all[array_rand($all, 1)];
            $pwd = $pwd . $temp[array_rand($temp, 1)];
        }
    
        return str_shuffle($pwd);
    } 

    public static function getIpLocation(string $ipAddress = "") {
        if(empty($ipAddress)) {
            return "Invalid Ip";
            // $ipAddress = Self::get_ip_address();
        }

        try {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "http://ip-api.com/json/{$ipAddress}",
                CURLOPT_RETURNTRANSFER => true
            ]);

            $json = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);
            $response = json_decode($json, true);
            curl_close($curl);

            if(!empty($error) || !array_key_exists("status", $response)) {
                return "Invalid Response";
            }

            if($response['status'] !== "success") {
                return $response['message'];
            }

            return $response;

        } catch (Exception $e) {
            if(ini_get("display_errors") == "1") {
                throw $e;
            }

            return "Internal Server Error";
        }
    }

    public static function validation_password($input) {
        $character  = "abcdefghijklmnopqrstuvwxyz";
        $numeric    = "1234567890";
        $min_length = 8;
    
        $return     = [
            'upper'     => 0,
            'lower'     => 0,
            'numeric'   => 0
        ];
    
        // Validate Length
        if(strlen($input) < $min_length) {
            return  "Password must be at least {$min_length} characters";
        }
    
        // Validate Character
        foreach(str_split($character) as $char) {
            //Uppercase 
            if($return['upper'] == 0) {
                if(strpos($input, strtoupper($char)) !== FALSE) {
                    $return['upper'] += 1;
                } 
            }
    
            //Lowercase
            if($return['lower'] == 0) {
                if(strpos($input, strtolower($char)) !== FALSE) {
                    $return['lower'] += 1;
                }
            }
        }
    
        // Validate Numeric
        foreach(str_split($numeric) as $num) {
            if($return['numeric'] == 0) {
                if(strpos($input, $num) !== FALSE) {
                    $return['numeric'] += 1;
                }
            }
        }
    
        if($return['upper'] == 0) {
            return  "Password must contain at least one upper case letter.";
        }
    
        if($return['lower'] == 0) {
            return  "Password must contain at least one lower case letter.";
        }
    
        if($return['numeric'] == 0) {
            return  "Password must contain at least one number.";
        }
    
        if(preg_match('/[^a-zA-Z0-9]/', $input) <= 0) {
            return  "Password must contain symbols.";
        }
    
        return true;
    }

    public static function default_date($date = '', $format = ''){
        if(!empty($date)) {
            $month  = date("m", strtotime($date));
            $day    = date("d", strtotime($date));
            $year   = date("Y", strtotime($date));
            $fulldate = date("Y-m-d H:i:s", strtotime($date)); 

            if(checkdate($month, $day, $year) && $year > 1970) {
                return empty($format) 
                    ? $fulldate
                    : date_format(date_create($fulldate), $format);
            }
        }

        $default_date = [
            'Y' => "0000",
            'm' => "00",
            'd' => "00",
            'H' => "00",
            'i' => "00",
            's' => "00"
        ];

        $split_format = [...explode("-", $format), ...explode(":", $format)];
        $date   = [];
        $time   = [];
        foreach($split_format as $f) {
            if(!array_key_exists($f, $default_date)) {
                continue;
            }

            switch(true) {
                case ($f == "Y" || $f == "m" || $f == "d"):
                    array_push($date, $default_date[ $f ]);
                    break;

                case ($f == "H" || $f == "i" || $f == "s"):
                    array_push($time, $default_date[ $f ]);
                    break;

                default: break;
            }
        }


        return implode("-", $date) . " " . implode(":", $time);
    }

    public static function mask_string($text, $visibleStart = 2, $visibleEnd = 2, $maskChar = '*') {
        $len = strlen($text);

        if ($len <= ($visibleStart + $visibleEnd)) {
            return str_repeat($maskChar, $len); // terlalu pendek → full mask
        }

        $start  = substr($text, 0, $visibleStart);
        $end    = substr($text, -$visibleEnd);
        $mask   = str_repeat($maskChar, $len - ($visibleStart + $visibleEnd));

        return $start . $mask . $end;
    }

    public static function mask_email($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return null;

        list($user, $domain) = explode("@", $email);
        $maskedUser = self::mask_string($user, 1, 1);

        return $maskedUser . '@' . $domain;
    }

    public static function generate_unique(string $prefix, int $length = 6, int $bytes = 4): string {
        return sprintf(
            '%s-%s-%s',
            strtoupper($prefix),
            date('Ymd'),
            self::generate_crypto_unique($length, $bytes)
        );
    }

    public static function generate_crypto_unique(int $length = 6, ?int $bytes = 4): string {
        return strtoupper(substr(bin2hex(random_bytes($bytes)), 0, $length));
    }

    public static function currencyToSymbol(string $currencyCode): string {
        $currencyCode = strtoupper($currencyCode);
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'IDR' => 'Rp',
        ];

        return $symbols[$currencyCode] ?? $currencyCode;
    }
}