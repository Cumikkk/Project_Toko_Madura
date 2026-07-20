<?php
namespace App\Library\CekatAI\Object;

final class CekatOtpRequestObject {

    public function __construct(
        public bool $success,
        public ?int $status_code,
        public ?int $http_code,
        public ?string $id,
        public ?string $message,
        public ?string $sent_by,
        public ?string $sent_by_name,
        public ?string $sent_by_type,
        public ?string $conversation_id,
        public ?string $media_url,
        public ?string $media_type,
        public ?string $platform_mid,
        public ?string $status,
        public ?string $token_usage,
        public ?string $message_history,
        public ?string $system_msg,
        public ?string $business_id,
        public ?string $error,
    ) {
        
    }

}