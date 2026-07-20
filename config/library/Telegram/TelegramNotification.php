<?php
namespace App\Library\Telegram;

use Exception;

/**
 * Telegram Notification Service
 * 
 * Handles sending notifications via Telegram Bot API
 * 
 * @package App\Library\Telegram
 */
class TelegramNotification {

    private const API_BASE_URL = 'https://api.telegram.org/bot';
    private const TIMEOUT = 10;
    
    protected TRequestData $requestData;
    public array $logSend = [];
    public string $error = ""; 

    public function __construct(TRequestData $requestData) {
        $this->requestData = $requestData;
    }

    /**
     * Send notification to all receivers
     * 
     * @return bool True if all messages sent successfully
     */
    public function send(): bool {
        try {
            if (!$this->hasValidReceivers()) {
                return false;
            }

            $template = $this->getTemplatePath();
            
            foreach ($this->requestData->receiver as $receiver) {
                if ($this->isValidReceiver($receiver)) {
                    $this->sendToReceiver($receiver, $template);
                }
            }

            return true;

        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * Check if request has valid receivers
     */
    private function hasValidReceivers(): bool {
        return !empty($this->requestData->receiver);
    }

    /**
     * Validate receiver data
     */
    private function isValidReceiver(object $receiver): bool {
        return !empty($receiver->chatId) && !empty($receiver->botToken);
    }

    /**
     * Get template file path
     * 
     * @return string Template file path
     * @throws Exception If template file not found
     */
    private function getTemplatePath(): string {
        $filePath = CONFIG_ROOT . "/telegram/{$this->requestData->template}.php";
        
        if (!file_exists($filePath)) {
            throw new Exception("Template file '{$this->requestData->template}' not found");
        }

        return $filePath;
    }

    /**
     * Send message to single receiver
     */
    private function sendToReceiver(object $receiver, string $template): void {
        $message = $this->renderTemplate($template);
        $url = $this->buildApiUrl($receiver->botToken);
        $payload = $this->buildPayload($receiver->chatId, $message);
        
        $response = $this->sendViaCurl($url, $payload);
        
        $this->logSend[] = array_merge($response, [
            'url' => $url,
            'payload' => $payload
        ]);
    }

    /**
     * Render template with data
     * 
     * @param string $template Template file path
     * @return string Rendered message
     */
    private function renderTemplate(string $template): string {
        extract($this->requestData->templateData, EXTR_OVERWRITE);
        
        ob_start();
        require $template;
        return ob_get_clean();
    }

    /**
     * Build Telegram API URL
     */
    private function buildApiUrl(string $botToken): string {
        return self::API_BASE_URL . "{$botToken}/sendMessage";
    }

    /**
     * Build message payload
     */
    private function buildPayload(string $chatId, string $message): array {
        return [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];
    }

    /**
     * Send message via cURL
     * 
     * @param string $url API endpoint URL
     * @param array $payload Message payload
     * @return array Response data including status and errors
     */
    private function sendViaCurl(string $url, array $payload): array {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => self::TIMEOUT
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        return [
            'response' => $response,
            'http_code' => $httpCode,
            'error' => $curlError
        ];
    }
}