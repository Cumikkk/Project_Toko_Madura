<?php
namespace App\Factory;

use Allmedia\Shared\Metatrader\ApiManager;
use Allmedia\Shared\Metatrader\ApiTerminal;
use App\Models\Account;
use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;

class MetatraderFactory {

    public static float $initMarginDemo = 10000;

    public static function credential(?string $server = null) {
        global $_ENV;
        $server ??= "RRFX-Live";
        $managerEndpoint = ($_ENV['MANAGER_ENDPOINT'] ?? "");
        $terminalEndpoint = ($_ENV['TERMINAL_ENDPOINT'] ?? "");

        $credential = [
            'managerEndpoint' => $managerEndpoint,
            'terminalEndpoint' => $terminalEndpoint,
            'tokenManager' => "",
            'serverName' => "",
        ];

        if($server == "RRFX-Live") {
            return array_merge($credential, [
                'tokenManager' => ($_ENV['MANAGER_TOKEN_LIVE'] ?? ""),
                'serverName' => ($_ENV['SERVER_NAME_LIVE'] ?? ""),
            ]);
        }

        if($server == "RRFX-Demo") {
            return array_merge($credential, [
                'tokenManager' => ($_ENV['MANAGER_TOKEN_DEMO'] ?? ""),
                'serverName' => ($_ENV['SERVER_NAME_DEMO'] ?? ""),
            ]);
        }

        return $credential;
    }

    public static function apiManager(?string $server = null): ApiManager {
        $server ??= "RRFX-Live";
        $credential = self::credential($server);
        return new ApiManager($credential['tokenManager'], $credential['managerEndpoint']);
    }

    public static function apiTerminal(?string $server = null): ApiTerminal {
        $server ??= "RRFX-Live";
        $credential = self::credential($server);
        return new ApiTerminal($credential['serverName'], $credential['terminalEndpoint']);
    }

    public static function createDemo(?string $fullname, ?string $email, ?string $typeAs = "SPA"): array {
        try {
            /** Get Demo Type */
            $db = Database::connect();
            $sqlGetType = $db->query("SELECT ID_RTYPE, RTYPE_SERVER, RTYPE_GROUP, RTYPE_LEVERAGE FROM tb_racctype WHERE UPPER(RTYPE_TYPE) = 'DEMO' AND UPPER(RTYPE_TYPE_AS) = '{$typeAs}' LIMIT 1");
            $demoType = $sqlGetType->fetch_assoc() ?? [];
            if($sqlGetType->num_rows == 0 || empty($demoType)) {
                return [
                    'success' => false,
                    'message' => "Invalid Demo Account",
                    'data' => []
                ];
            }

            /** check type */
            $meta_pass = Account::generatePassword();
            $meta_investor = Account::generatePassword();
            $meta_phone = Account::generatePassword();

            /** Create Demo */
            $apiManager = self::apiManager($demoType['RTYPE_SERVER']);
            $apiData = [
                'master_pass' => $meta_pass, 
                'investor_pass' => $meta_investor, 
                'group' => $demoType['RTYPE_GROUP'], 
                'fullname' => ($fullname ?? "-"), 
                'email' => ($email ?? "-"), 
                'leverage' => $demoType['RTYPE_LEVERAGE'],
                'comment' => "metaapi"
            ];

            $createDemo = $apiManager->createAccount($apiData);
            if(!is_object($createDemo) || !property_exists($createDemo, "Login")) {
                return [
                    'success' => false,
                    'message' => "Gagal membuat akun demo",
                    'data' => []
                ];
            }

            /** deposit demo margin */
            $deposit = $apiManager->deposit([
                'login' => $createDemo->Login,
                'amount' => self::$initMarginDemo,
                'comment' => "metaapi"
            ]);

            return [
                'success' => true,
                'message' => "Successfull",
                'data' => [
                    'login' => $createDemo->Login,
                    'password' => $meta_pass,
                    'investor' => $meta_investor,
                    'passphone' => $meta_phone,
                    'type' => $demoType['ID_RTYPE']
                ]
            ];


        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [
                'success' => false,
                'message' => "Internal Server Error",
                'data' => []
            ];
        }
    }

    public static function autoConnect(int $login): string|bool {
        try {
            /** Check Account */
            $account = Account::findByLogin($login);
            if(empty($account)) { 
                return false;
            }

            $apiTerminal = self::apiTerminal($account['RTYPE_SERVER']);
            $isEmptyToken = empty($account['ACC_TOKEN']);
            $token = "";
            switch($isEmptyToken) {
                case true:
                    /** Connect meta */
                    $connectData = [
                        'login' => $account['ACC_LOGIN'], 
                        'password' => $account['ACC_PASS']
                    ];
                    
                    $token = $apiTerminal->connect($connectData);
                    if(!$token) {
                        return false;
                    }

                    Database::update("tb_racc", ['ACC_TOKEN' => $token], ['ID_ACC' => $account['ID_ACC']]);
                    break;

                case false:
                    /** check connection with available token */
                    $token = $account['ACC_TOKEN'];
                    $summary = $apiTerminal->accountSummary(['id' => $account['ACC_TOKEN']]);
                    if(!$summary->success) {
                        /** get new token */
                        $connectData = [
                            'login' => $account['ACC_LOGIN'], 
                            'password' => $account['ACC_PASS']
                        ];
                        
                        $token = $apiTerminal->connect($connectData);
                        if(!$token) {
                            return false;
                        }

                        Database::update("tb_racc", ['ACC_TOKEN' => $token], ['ID_ACC' => $account['ID_ACC']]);
                    }


                default: break;
            }

            return $token;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function createLoginByPrefix(string|int $prefix): int|bool {
        try {
            $db = Database::connect();
            if($prefix == 0) {
                return 0;
            }

            $sqlGetLastAccountByPrefix = $db->query("SELECT IFNULL(MAX(`LOGIN`), 0) as `LOGIN` FROM meta_firststatelive.`MT4_USERS` LIMIT 1");
            if($sqlGetLastAccountByPrefix->num_rows != 1) {
                return false;
            }

            $lastLogin = $sqlGetLastAccountByPrefix->fetch_assoc()['LOGIN'];
            if($lastLogin < 0) {
                return false;
            }

            if($lastLogin == 0) {
                return str_pad($prefix, 8, "0") + 1;
            }

            return $lastLogin + 1;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

}