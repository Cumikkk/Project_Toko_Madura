<?php
namespace App\Library\CekatAI;

final class ApiConfig {

    public function __construct(
        public string $apiBaseUrl,
        public string $apiOtpUrl,
        public string $apiKey,
        public string $defaultBoardsId,
    ) {
        
    }

}