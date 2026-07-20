<?php
namespace App\Library\CekatAI;

use Config\Core\Database;
use mysqli;

abstract class CekatAbstract {
    
    protected string $logString;

    public function __construct(protected ApiConfig $apiConfig, protected mysqli $db, protected ?string $logFileName = null) {
        $this->model = $db;
        $this->apiConfig = $apiConfig;
        $this->logString = "";
        $this->logFileName = $logFileName ?? "cekat_ai.log";
    }

    protected function log(string $message) {
        $this->logString .= sprintf("[%s] - %s \n", date('Y-m-d H:i:s'), $message);
    }

    abstract public function getLogString(): string;

    protected function saveLog() {
        /** Save log to file */
        $filepath = __DIR__ . "/../../logs/cekat_ai/{$this->logFileName}";
        if(!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0777, true);
        }

        file_put_contents($filepath, sprintf("\n%s", $this->logString), FILE_APPEND);
    } 

    protected function sendRequest(string $endpoint, array $payload = [], bool $requireKey = false) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                $requireKey ? 'api_key: ' . $this->apiConfig->apiKey : ''
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'response' => $response,
            'error' => $error,
            'http_code' => $httpCode
        ];
    }

}