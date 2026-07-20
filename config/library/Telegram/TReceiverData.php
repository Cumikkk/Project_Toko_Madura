<?php
namespace App\Library\Telegram;

class TReceiverData {

    public string $chatId = "";
    public string $botToken = "";
    
    public function __construct(string $chatId, string $botToken) {
        $this->chatId = $chatId;
        $this->botToken = $botToken;
    }
    
}