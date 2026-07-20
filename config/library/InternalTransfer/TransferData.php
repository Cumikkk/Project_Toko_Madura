<?php
namespace App\Library\InternalTransfer;

class TransferData {
 
    protected bool $success = false;
    protected array $result;
    protected string $error;

    public function __construct(bool $success, array $apiResponse, string $error) {
        $this->success = $success;
        $this->result = $apiResponse;
        $this->error = $error;
    }

    public function success(): bool {
        return $this->success;
    }

    public function getResult(): array {
        return $this->result;
    }

    public function getError(): string {
        return $this->error;
    }
    
}