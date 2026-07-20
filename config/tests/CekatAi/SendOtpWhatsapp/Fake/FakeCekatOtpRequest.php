<?php
namespace App\Tests\CekatAi\SendOtpWhatsapp\Fake;

use App\Library\CekatAI\Services\CekatOtpRequest;

/**
 * Fake CekatOtpRequest untuk testing tanpa memanggil API production
 * 
 * Class ini mengubride method sendOtp untuk return mock response
 * sehingga tidak perlu melakukan HTTP request ke API production
 */
class FakeCekatOtpRequest extends CekatOtpRequest {

    private array $lastPayload = [];
    private string $mockResponse = '{"success":true,"status_code":200,"data":{"id":"9246aa0d-4f92-4043-be5c-bdc142410f9e","message":"******** adalah kode verifikasi Anda. Demi keamanan, jangan bagikan kode ini.","sent_by":"f403f425-f1c3-48f6-b5f8-4075cca303cf","sent_by_name":"Marcom RRFX","sent_by_type":"api","conversation_id":"8b9d7280-42ca-4ff4-9669-954ae19d7678","media_url":null,"media_type":"text","platform_mid":"wamid.HBgNNjI4NTk1NDUzNjU5MxUCABEYEjczMDlGNEJDRDZCN0E5M0ZENQA=","status":"sent","token_usage":null,"message_history":null,"system_msg":null,"business_id":"b22de907-7c2f-4cf0-a64e-53e0abf81059","error":null}}';
    private bool $shouldThrowError = false;
    private ?string $errorMessage = null;

    /**
     * Set mock response yang akan di-return
     */
    public function setMockResponse(string $response): self {
        $this->mockResponse = $response;
        return $this;
    }

    /**
     * Set fake error
     */
    public function setErrorMode(string $errorMessage): self {
        $this->shouldThrowError = true;
        $this->errorMessage = $errorMessage;
        return $this;
    }

    /**
     * Disable error mode
     */
    public function disableErrorMode(): self {
        $this->shouldThrowError = false;
        $this->errorMessage = null;
        return $this;
    }

    /**
     * Get last payload yang di-send
     */
    public function getLastPayload(): array {
        return $this->lastPayload;
    }

    /**
     * Override sendOtp untuk tidak panggil API production
     */
    public function sendOtp(string $otpCode, string $name, ?string $phoneNumber = null, ?string $phoneCode = null) {
        // Panggil phoneValidation via reflection jika diperlukan untuk testing
        // Tapi di sini kita bisa langsung test endpoint tanpa validation
        // Untuk tetap realistic, kita simulasi phoneValidation logic
        
        // Simple validation
        if (empty($phoneNumber)) {
            throw new \Exception("Nomor Telepon tidak boleh kosong");
        }

        // Normalize phone
        $phone = $this->normalizePhoneNumber($phoneNumber, $phoneCode);
        
        // Create payload
        $this->lastPayload = [
            'phone_number' => $phone,
            'otp_code' => $otpCode,
            'name' => $name,
        ];

        // Log the action
        $this->log("Sending OTP to phone number: {$phone} with OTP code: {$otpCode} for name: {$name}");

        // If error mode is enabled, throw exception
        if ($this->shouldThrowError) {
            $this->log("Error: {$this->errorMessage}");
            throw new \Exception($this->errorMessage);
        }

        // Save log
        $this->saveLog();

        // Return mock response
        return json_decode($this->mockResponse, true);
    }

    /**
     * Simulate phone normalization logic (dari CekatOtpRequest)
     */
    private function normalizePhoneNumber(?string $phone, ?string $phoneCode = '62'): string {
        $this->log("Validating phone number: {$phone} with code {$phoneCode}");
        
        if (empty($phone)) {
            throw new \Exception("Nomor Telepon tidak boleh kosong");
        }

        // Remove + symbol
        if (strpos($phone, '+') === 0) {
            $this->log("Removing '+' from phone number: {$phone}");
            $phone = substr($phone, 1);
        }

        // Remove non-digits
        $phone = preg_replace('/[^\d]/', '', $phone);

        // Handle country code
        if ($phoneCode !== null) {
            $phoneCode = preg_replace('/[^\d]/', '', $phoneCode);

            // If starts with 0, replace with country code
            if (preg_match('/^0\d+$/', $phone)) {
                $this->log("Phone number starts with 0, replacing with country code: {$phoneCode}");
                $phone = $phoneCode . substr($phone, 1);
            }
            // If doesn't start with country code, add it
            elseif (!preg_match('/^' . preg_quote($phoneCode) . '/', $phone)) {
                $this->log("Phone number does not start with country code, adding country code: {$phoneCode}");
                $phone = $phoneCode . $phone;
            }
        }

        // Validate length
        if (strlen($phone) < 9) {
            $this->log("Phone number length is invalid: {$phone}");
            throw new \Exception("Panjang Nomor Telepon tidak valid");
        }

        $this->log("Phone number validated: {$phone}");
        return $phone;
    }
}
