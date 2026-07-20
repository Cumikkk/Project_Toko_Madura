<?php
namespace App\Models;

use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;

class Product {

    public static $status = [
        '-1' => [
            'text' => "Active",
            'html' => '<span class="badge bg-success">Active</span>'
        ],
        '0' => [
            'text' => "Pending",
            'html' => '<span class="badge bg-warning">Pending</span>'
        ],
        '1' => [
            'text' => "Disabled",
            'html' => '<span class="badge bg-danger">Disabled</span>'
        ]
    ];

    public static $type = ['ULTRA-LOW', 'STANDARD', 'STANDARD-PLUS'];
    public static $rates = [10000, 14000, "FLOATING"];
    public static $currency = ["IDR", "USD"];
    public static $typeAs = ["SPA", "MULTILATERAL"];

    public static function findBySuffix(string $suffix) {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_racctype WHERE RTYPE_SUFFIX = '{$suffix}' LIMIT 1");
            if($sqlGet->num_rows != 1) {
                return false;
            }

            return $sqlGet->fetch_assoc() ?? false;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function findById(string $md5id) {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("SELECT * FROM tb_racctype WHERE MD5(MD5(ID_RTYPE)) = '{$md5id}' LIMIT 1");
            if($sqlGet->num_rows != 1) {
                return false;
            }

            return $sqlGet->fetch_assoc() ?? false;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }
}