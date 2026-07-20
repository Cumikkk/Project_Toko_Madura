<?php
namespace App\Library\Telegram;

class TRequestData {

    /** @var TReceiverData[] */
    public array $receiver;

    public string $template;
    public array $templateData;

    public function __construct(array $receiver, string $template, array  $templateData) {
        $this->receiver = $receiver;
        $this->template = $template;
        $this->templateData = $templateData;
    }

}