<?php
namespace Config\Core;

use App\Models\Helper;

class Logging {
    private string $logDir;

    public function __construct(?string $logDir = null)
    {
        $this->logDir = $logDir ?? __DIR__ . '/logs';

        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }

    public function log(string $message, array $context = [], string $level = 'INFO'): void
    {
        $date = date('Y-m-d H:i:s');

        // Default context (biar konsisten)
        $context = array_merge([
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'url' => $_SERVER['REQUEST_URI'] ?? null,
            'method' => $_SERVER['REQUEST_METHOD'] ?? null,
            'user_agent' => Helper::get_user_agent(),
        ], $context);

        $contextStr = json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $log = sprintf(
            "[%s] [%s] %s | %s%s",
            $date,
            strtoupper($level),
            $message,
            $contextStr,
            PHP_EOL
        );

        $file = $this->getLogFile($level);
        if(!file_exists($this->logDir . '/' . $file)) {
            file_put_contents($this->logDir . '/' . $file, ""); // Create empty log file if not exists
        }

        file_put_contents($this->logDir . '/' . $file, $log, FILE_APPEND | LOCK_EX);
    }

    private function getLogFile(string $level): string
    {
        return match (strtoupper($level)) {
            'ERROR' => 'error.log',
            'DEBUG' => 'debug.log',
            'WARNING' => 'warning.log',
            default => 'app.log',
        };
    }

    // Shortcut methods
    public function info($msg, $ctx = []) { $this->log($msg, $ctx, 'INFO'); }
    public function error($msg, $ctx = []) { $this->log($msg, $ctx, 'ERROR'); }
    public function warning($msg, $ctx = []) { $this->log($msg, $ctx, 'WARNING'); }
    public function debug($msg, $ctx = []) { $this->log($msg, $ctx, 'DEBUG'); }
}