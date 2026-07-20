<?php
namespace App\Factory;

use App\Library\CekatAI\ApiConfig;
use App\Library\CekatAI\CekatAbstract;
use App\Library\CekatAI\Services\CekatBoardingService;
use App\Library\CekatAI\Services\CekatOtpRequest;
use Config\Core\Database;
use Exception;

class CekatAiFactory {

    public static function createApiConfig(): ApiConfig {
        global $_ENV;
        
        // Get values from $_ENV
        $baseUrl = $_ENV['CEKATAI_API_BASE_URL'] ?? null;
        $otpUrl = $_ENV['CEKATAI_API_OTP_URL'] ?? null;
        $apiKey = $_ENV['CEKATAI_API_KEY'] ?? null;
        $defaultBoardsId = $_ENV['CEKATAI_DEFAULT_BOARDS_ID'] ?? null;
        
        // Validate that values are actually set
        if (!$baseUrl || !$otpUrl || !$apiKey || !$defaultBoardsId) {
            $missingVars = [];
            if (!$baseUrl) $missingVars[] = 'CEKATAI_API_BASE_URL';
            if (!$otpUrl) $missingVars[] = 'CEKATAI_API_OTP_URL';
            if (!$apiKey) $missingVars[] = 'CEKATAI_API_KEY';
            if (!$defaultBoardsId) $missingVars[] = 'CEKATAI_DEFAULT_BOARDS_ID';
            
            throw new Exception(
                "Missing CekatAI configuration: " . implode(', ', $missingVars) . 
                ". Please check your .env file."
            );
        }
        
        return new ApiConfig($baseUrl, $otpUrl, $apiKey, $defaultBoardsId);
    }

    public static function createOtpService(): CekatOtpRequest {
        $apiConfig = self::createApiConfig();
        return new CekatOtpRequest($apiConfig, Database::connect(), "otp_services.log");
    }

    public static function createBoardingService(): CekatBoardingService {
        $apiConfig = self::createApiConfig();
        return new CekatBoardingService($apiConfig, Database::connect(), "boarding_services.log");
    }
        
}