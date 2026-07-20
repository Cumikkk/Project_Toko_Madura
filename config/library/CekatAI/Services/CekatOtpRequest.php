<?php
namespace App\Library\CekatAI\Services;

use App\Library\CekatAI\CekatAbstract;
use App\Library\CekatAI\Object\CekatOtpRequestObject;
use Exception;

class CekatOtpRequest extends CekatAbstract {

    public function getLogString(): string {
        return $this->logString;
    }

    public function normalizePhoneNumber(?string $phone, ?string $phoneCode = '62'): string 
    {
        $this->log("Normalize phone number: {$phone} with code {$phoneCode}");
        if(empty($phone)) {
            throw new Exception("Nomor Telepon tidak boleh kosong");
        }

        /** Hapus simbol + jika ada di awal */
        if (strpos($phone, '+') === 0) {
            $this->log("Removing '+' from phone number: {$phone}");
            $phone = substr($phone, 1);
        }

        $phone = preg_replace('/[^\d]/', '', $phone);

        /** Jika phoneCode ada, validasi dengan kode negara */
        if ($phoneCode !== null) {
            $phoneCode = preg_replace('/[^\d]/', '', $phoneCode);

            /** Jika diawali 0, buang 0 dan tambahkan kode negara */
            if (preg_match('/^0\d+$/', $phone)) {
                $this->log("Phone number starts with 0, replacing with country code: {$phoneCode}");
                $phone = $phoneCode . substr($phone, 1);
            }

            /** Jika tidak diawali dengan $phoneCode atau 0, langsung tambahkan kode negara */
            elseif (!preg_match('/^' . preg_quote($phoneCode) . '/', $phone)) {
                $this->log("Phone number does not start with country code, adding country code: {$phoneCode}");
                $phone = $phoneCode . $phone;
            }
        }

        /** Cek validitas format [kode][nomor minimal 9 digit] */
        if (strlen($phone) < 9) {
            $this->log("Phone number length is invalid: {$phone}");
            throw new Exception("Panjang Nomor Telepon tidak valid");
        }

        $this->log("Phone number validated: {$phone}");
        return $phone;
    }

    private function validatePhone(string $phone): bool 
    {
        $this->log("Validating phone number format: {$phone}");

        $payload = [
            'condition' => "AND",
            'search' => [
                [
                    'column_name' => "Phone",
                    'operator' => "equals",
                    'value' => $phone
                ]
            ]
        ];

        $sendRequest = $this->sendRequest("{$this->apiConfig->apiBaseUrl}/api/crm/boards/{$this->apiConfig->defaultBoardsId}/items/search", $payload, true);
        $response = $sendRequest['response'];
        $error = $sendRequest['error'];
        $httpCode = $sendRequest['http_code'];
        if(!empty($error)) {
            $this->log("Curl ERROR during phone validation: {$error}");
            throw new Exception("Failed to validate phone number");
        }

        $this->log("Phone validation response received. Code: {$httpCode}, Response: {$response}");
        $responseArray = json_decode($response, true);
        if(!is_array($responseArray)) {
            throw new Exception("Invalid response from phone validation service");
        }

        if(!$responseArray['message'] || $responseArray['message'] !== "success") {
            $this->log("Phone number validation failed: {$responseArray['message']}");
            throw new Exception("Phone number validation failed");
        }

        $this->log("Phone number validation successful for: {$phone}");
        return true;
    }
    
    public function sendOtp(string $otpCode, string $name, ?string $phoneNumber = null, ?string $phoneCode = null): CekatOtpRequestObject 
    {
        $phoneNumber = $this->normalizePhoneNumber($phoneNumber, $phoneCode);
        if(!$this->validatePhone($phoneNumber)) {
            throw new Exception("Nomor Telepon tidak ditemukan");
        }

        $payload = [
            'phone_number' => $phoneNumber,
            'otp_code' => $otpCode,
            'name' => $name,
        ];
            
        $this->log("Sending OTP to phone number: {$phoneNumber} with OTP code: {$otpCode} for name: {$name}");
        $sendRequest = $this->sendRequest($this->apiConfig->apiOtpUrl, $payload);
        $response = $sendRequest['response'];
        $error = $sendRequest['error'];
        $httpCode = $sendRequest['http_code'];

        if(!empty($error)) {
            $this->log("Curl ERROR: {$error}");
            throw new Exception("Failed to send OTP");
        }

        $this->log("OTP sent successfully. Code: {$httpCode}, Response: {$response}");
        $this->saveLog();
        $responseArray = json_decode($response, true);
        if(!is_array($responseArray)) {
            throw new Exception("Invalid response from OTP service");
        }

        return new CekatOtpRequestObject(
            success: $responseArray['success'] ?? false,
            status_code: $responseArray['status_code'] ?? 0,
            http_code: $httpCode,
            id: $responseArray['data']['id'] ?? null,
            message: $responseArray['data']['message'] ?? null,
            sent_by: $responseArray['data']['sent_by'] ?? null,
            sent_by_name: $responseArray['data']['sent_by_name'] ?? null,
            sent_by_type: $responseArray['data']['sent_by_type'] ?? null,
            conversation_id: $responseArray['data']['conversation_id'] ?? null,
            media_url: $responseArray['data']['media_url'] ?? null,
            media_type: $responseArray['data']['media_type'] ?? null,
            platform_mid: $responseArray['data']['platform_mid'] ?? null,
            status: $responseArray['data']['status'] ?? null,
            token_usage: $responseArray['data']['token_usage'] ?? null,
            message_history: $responseArray['data']['message_history'] ?? null,
            system_msg: $responseArray['data']['system_msg'] ?? null,
            business_id: $responseArray['data']['business_id'] ?? null,
            error: $responseArray['data']['error'] ?? null
        );
    }
}