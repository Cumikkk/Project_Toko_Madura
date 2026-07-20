<?php 
namespace App\Models;

use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;

class Ticket {

    private static array $topic = [
        "account" => [
            'color' => "primary"
        ],
        "trade" => [
            'color' => "dark"
        ],
        "withdrawal" => [
            'color' => "danger"
        ],
        "deposit" => [
            'color' => "success"
        ],
        "transfer" => [
            'color' => "success"
        ],
        "security" => [
            'color' => "danger"
        ],
        "bank" => [
            'color' => "primary"
        ],
        "registration" => [
            'color' => "primary"
        ],
        "close_account" => [
            'color' => "dark"
        ],
    ];

    public static array $status = [
        "-1" => [
            'html' => '<span class="badge bg-success">Open</span>',
            'text' => "Open"
        ],
        "1" => [
            'html' => '<span class="badge bg-danger">Closed</span>',
            'text' => "Closed"
        ]
    ];

    public static function findByCode(string $code): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_ticket WHERE TICKET_CODE = '{$code}' LIMIT 1");
            return $sqlGet->fetch_assoc() ?? false;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function historyChatByTicketCode(string $code, int $maxDayHistory = 3): array {
        try {
            $dateMax = date("Y-m-d", strtotime("-{$maxDayHistory} day"));
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_ticket_detail WHERE TDETAIL_TCODE = '{$code}' AND DATE(TDETAIL_DATETIME) >= '{$dateMax}'  ORDER BY TDETAIL_DATETIME");
            return $sqlGet->fetch_all(MYSQLI_ASSOC) ?? [];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function memberUserTicketList(string $id): array {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_ticket WHERE (TICKET_MBR = '{$id}' OR MD5(MD5(TICKET_MBR)) = '{$id}' ) ORDER BY tb_ticket.ID_TICKET DESC");
            return $sqlGet->fetch_all(MYSQLI_ASSOC) ?? [];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function topicList(): array {
        return array_keys(self::$topic);
    }

    public static function topicColor(string $topic): string {
        return self::$topic[$topic]['color'] ?? "primary";
    }

}