<?php
namespace Config\Core;

use Exception;

class ApiWapanel {
    private $appkey = "d68d4809-d8e1-4d6d-baef-ad945e1d17bf";
    private $authkey = "EKPcyLZAeecp7g9DMKfc6gNTWayIFFsSHJPb8c9Q2e89FNyz4v";

    public function __construct()
    {
        //Do your magic here
    }

    private function request(string $endpoint = "", array $data = []) {
        try {

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $endpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array(
                    'appkey' => $this->appkey,
                    'authkey' => $this->authkey,
                    'to' => $data['phone'],
                    'message' => $data['message'],
                    'sandbox' => 'false'
                ),
            ]);

            $json = curl_exec($curl);
            $error = curl_error($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if(!empty($error)) {
                return [
                    'status'    => false,
                    'message'   => $error,
                    'data'      => []
                ];
            }

            $response = json_decode($json, true);
            if(!is_array($response)) {
                return [
                    'status'    => false,
                    'message'   => $response['message'],
                    'data'      => []
                ];
            }

            if(array_key_exists("error", $response)) {
                return [
                    'status'    => false,
                    'message'   => $response['error'],
                    'data'      => []
                ];
            }

            return [
                'status'    => (strtolower($response['message_status']) == "success")? true : false,
                'message'   => $response['message_status'] ?? '',
                'data'      => $response['data']
            ];

        } catch (Exception $e) {
            if(ini_get("display_errors") == "1") {
                throw $e;
            }

            return [
                'status'    => false,
                'message'   => "Internal Server Error",
                'data'      => []
            ];
        }
    }

    public function sendMessage(array $data = []): string|array {
        try {
            $required = ['phone', 'message'];
            foreach($required as $req) {
                if(empty($data[ $req ])) {
                    return "required parameter {$req}";
                }
            }

            $phone = $data['phone'];
            if(substr($phone, 0, 1) == "0") {
                $prefix = "62";
                $phone = "+".$prefix . substr($phone, 1);
            }

            $data['phone'] = $phone;
            $request = $this->request("https://app.wapanels.com/api/create-message", $data);
            return $request;

        } catch (Exception $e) {
            if(ini_get("display_errors") == "1") {
                throw $e;
            }

            return "Internal Server Error";
        }
    }
}