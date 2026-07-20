<?php

use App\Models\Helper;
use App\Models\Regol;
use Config\Core\Database;
use Config\Core\EmailSender;
use Config\Core\SystemInfo;

require_once __DIR__ . "/../../setting.php";

class AccountCddEliminationRunCronJob {

    private string $codeRun;
    private array $accounts = [];
    private int $sleepTime = 3; // waktu tidur dalam detik antara setiap proses eliminasi

    private float $percentage_limit = 100; // contoh threshold untuk eliminasi (100% equity)
    private float $percentage_limit_notification = 92; // contoh threshold untuk notifikasi (92% dari equity)


    public function __construct() {
        $this->codeRun = "run_" . date("Ymd");
    }

    private function fetchAccounts() {
        $db = Database::connect();
        $this->log("Starting account CDDS elimination process...");

        $sqlGetAccounts = $db->query("
            SELECT   
                account.ID_ACC as id,
                account.ACC_MBR as member_id,
                account.MBR_EMAIL as email,
                account.MBR_NAME as fullname,
                account.RTYPE_TYPE as product_type,
                account.ACC_NEEDS_UPGRADE as needs_upgrade,
                mt5u.LOGIN as login,
                mt5u.EQUITY as equity,
                mt5u.BALANCE as balance,
                account.RTYPE_RATE as rate,
                account.RTYPE_ISFLOATING as is_floating,
                account.ACC_LIMIT_MARGIN as limit_margin,
                account.RTYPE_META_CURR as currency,
                JSON_EXTRACT(account.ACC_METADATA, '$.is_updated') as is_updated
            FROM mt4_users as mt5u
            JOIN (
                SELECT 
                    tr.*,
                    trt.*,
                    tm.*
                FROM tb_racc as tr
                JOIN tb_racctype as trt ON (trt.ID_RTYPE = tr.ACC_TYPE)
                JOIN tb_member as tm ON (tm.MBR_ID = tr.ACC_MBR)
                WHERE tr.ACC_DERE = 1
                AND ((tr.ACC_CDD = 2 AND tr.ACC_STS = -1) OR (tr.ACC_CDD = 1 AND tr.ACC_NEEDS_UPGRADE = 1))
                GROUP BY tr.ACC_LOGIN, tm.MBR_ID
            ) as account ON (account.ACC_LOGIN = mt5u.LOGIN)
        ");

        if(!$sqlGetAccounts) {
            throw new Exception("Failed to fetch accounts: " . $db->error);
        }

        $this->accounts = $sqlGetAccounts->fetch_all(MYSQLI_ASSOC);
    }

    private function eliminateAccounts() {
        if(empty($this->accounts)) {
            $this->log("No accounts found for elimination.");
            return;
        }

        if($this->percentage_limit_notification <= 0) {
            $this->log("Invalid percentage limit for notification. It must be greater than 0.");
            return;
        }

        foreach($this->accounts as $key => $account) {
            $equity = (float) $account['equity'];
            $limitMargin = (float) $account['limit_margin'] * ($this->percentage_limit / 100);
            $limitNotification = $limitMargin * ($this->percentage_limit_notification / 100);

            switch(true) {
                case $equity > $limitMargin:
                    // Eliminate account
                    if($account['needs_upgrade']) {
                        $this->log("Account already marked for elimination: " . $account['login']);
                        break;
                    }

                    $this->accounts[$key]['eliminated'] = true; // Mark account as eliminated
                    break;

                case $equity >= $limitNotification && $equity <= $limitMargin:
                    // Notify about account nearing elimination
                    $this->sendEmailNotification($account);
                    $this->accounts[$key]['notified'] = true; // Mark account as notified
                    break;

                default: 
                    // Account is safe, no action needed
                    $this->log("Account is safe: " . $account['login'] . " with equity: " . $equity);
                    break;
            }
        }
    }

    private function sendEmailNotification($account) {
        // Implementation for sending email notification
        $this->log("Account nearing elimination: " . $account['login'] . " with equity: " . $account['equity']);
        $sendEmail = EmailSender::init(['email' => $account['email'], 'name' => $account['fullname']]);
        $sendEmail->useFile("account_cdds_notifiication", [
            'subject' => "Pemberitahuan Limit Margin Hampir Tercapai",
            'name' => $account['fullname'],
            'accountNumber' => $account['login'],
            'accountType' => sprintf("CDDS - %s", strtoupper(str_replace(" ", "_", $account['product_type']))),
            'equity' => sprintf('%s%s', Helper::currencyToSymbol($account['currency']), $account['equity']),
            'limitMargin' => sprintf('%s%s', Helper::currencyToSymbol($account['currency']), $account['limit_margin']),
            'upgradeUrl' => SystemInfo::app('CLIENT_URL') . "/account/create?page=aplikasi-pembukaan-rekening"
        ]);
      
        $sendEmail->send();
    }

    private function updateEliminatedAccounts() {
        $eliminatedAccounts = array_filter($this->accounts, fn($ar) => ($ar['eliminated'] ?? false) === true);
        if(empty($eliminatedAccounts)) {
            $this->log("No accounts marked for elimination.");
            return;
        }

        $db = Database::connect();
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        mysqli_begin_transaction($db);

        /** 
         * Update racc
         * - Set ACC_STS to 0 (progress real account)
         * - Set ACC_CDD to CDD Standar
         * - Set ACC_NEEDS_UPGRADE to 1 (flag for upgrade) 
         * */
        $updatedColumns = [
            'ACC_STS' => 0,
            'ACC_CDD' => Regol::$cddTypeStandard,
            'ACC_NEEDS_UPGRADE' => 1,
            'ACC_F_APP' => 0,
            'ACC_F_RESK' => 0,
            'ACC_F_PERJ' => 0,
            'ACC_F_DISC2' => 0,
            'ACC_F_DISC3' => 0,
            'ACC_F_TRDNGRULE' => 0,
            'ACC_F_KODE' => 0,
            'ACC_F_DANA' => 0,
            'ACC_F_DISC4' => 0,
            'ACC_F_CMPLT' => 0,
            'ACC_LAST_STEP' => "'aplikasi-pembukaan-rekening'"
        ];

        $accountsId = array_map(fn($ar) => $ar['id'], $eliminatedAccounts);
        $arrayToStringQuery = implode(", ", array_map(fn($key, $val) => sprintf("%s = %s", $key, $val), array_keys($updatedColumns), $updatedColumns));
        $sqlQueryUpdate = $db->prepare("UPDATE tb_racc SET {$arrayToStringQuery} WHERE ID_ACC IN (" . implode(",", $accountsId) . ")");
        if(!$sqlQueryUpdate->execute()) {
            $db->rollback();
            throw new Exception("Failed to update eliminated accounts: " . $sqlQueryUpdate->error);
        }

        /** Lock withdrawal */
        $userIds = array_unique(array_map(fn($ar) => $ar['member_id'], $eliminatedAccounts));
        $sqlQueryUpdateUser = $db->prepare("UPDATE tb_member SET MBR_WITHDRAWAL_STATUS = 0 WHERE MBR_ID IN (" . implode(",", $userIds) . ")");
        if(!$sqlQueryUpdateUser->execute()) {
            $db->rollback();
            throw new Exception("Failed to lock withdrawal for eliminated accounts: " . $sqlQueryUpdateUser->error);
        }

        $db->commit();

        /** send email notifications for eliminated accounts */
        foreach($eliminatedAccounts as $account) {
            $this->log("Eliminating account: " . $account['login'] . " with equity: " . $account['equity']);
            $sendEmail = EmailSender::init(['email' => $account['email'], 'name' => $account['fullname']]);
            $sendEmail->useFile("account_cdds_limit", [
                'subject' => "Pemberitahuan Limit Margin Tercapai",
                'name' => $account['fullname'],
                'accountNumber' => $account['login'],
                'accountType' => sprintf("CDDS - %s", strtoupper(str_replace(" ", "_", $account['product_type']))),
                'limitMargin' => sprintf('%s%s', Helper::currencyToSymbol($account['currency']), $account['limit_margin']),
                'upgradeUrl' => SystemInfo::app('CLIENT_URL') . "/account/create?page=aplikasi-pembukaan-rekening"
            ]);
        
            $sendEmail->send();
        }
    }

    private function updateUnEliminatedAccounts() {
        $unEliminatedAccounts = array_filter($this->accounts, fn($ar) => $ar['needs_upgrade'] && $ar['is_updated'] != "true");
        if(empty($unEliminatedAccounts)) {
            $this->log("No accounts marked for un-elimination.");
            return;
        }

        $db = Database::connect();
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        mysqli_begin_transaction($db);

        /** 
         * Update racc
         * - Set ACC_STS to -1 (active account)
         * - Set ACC_CDD to CDDS
         * - Set ACC_NEEDS_UPGRADE to 0 (flag for upgrade) 
         * */
        $updatedColumns = [
            'ACC_STS' => -1,
            'ACC_CDD' => Regol::$cddTypeSederhana,
            'ACC_NEEDS_UPGRADE' => 0,
            'ACC_F_APP' => 1,
            'ACC_F_RESK' => 1,
            'ACC_F_PERJ' => 1,
            'ACC_F_DISC2' => 1,
            'ACC_F_DISC3' => 1,
            'ACC_F_TRDNGRULE' => 1,
            'ACC_F_KODE' => 1,
            'ACC_F_DANA' => 1,
            'ACC_F_DISC4' => 1,
            'ACC_F_CMPLT' => 1,
            'ACC_LAST_STEP' => "'kelengkapan-formulir'"
        ];

        $accountsId = array_map(fn($ar) => $ar['id'], $unEliminatedAccounts);
        $arrayToStringQuery = implode(", ", array_map(fn($key, $val) => sprintf("%s = %s", $key, $val), array_keys($updatedColumns), $updatedColumns));
        $sqlQueryUpdate = $db->prepare("UPDATE tb_racc SET {$arrayToStringQuery} WHERE ID_ACC IN (" . implode(",", $accountsId) . ")");
        if(!$sqlQueryUpdate->execute()) {
            $db->rollback();
            throw new Exception("Failed to update un-eliminated accounts: " . $sqlQueryUpdate->error);
        }

        /** Unlock withdrawal */
        $userIds = array_unique(array_map(fn($ar) => $ar['member_id'], $unEliminatedAccounts));
        $sqlQueryUpdateUser = $db->prepare("UPDATE tb_member SET MBR_WITHDRAWAL_STATUS = 1 WHERE MBR_ID IN (" . implode(",", $userIds) . ")");
        if(!$sqlQueryUpdateUser->execute()) {
            $db->rollback();
            throw new Exception("Failed to unlock withdrawal for un-eliminated accounts: " . $sqlQueryUpdateUser->error);
        }

        $db->commit();
    }

    public function log(?string $message = null) {
        // Implementation for logging
        $filepath = __DIR__ . sprintf("/logs/%s.log", $this->codeRun);
        if(!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0777, true);
        }

        if($message !== null) {
            file_put_contents($filepath, $message . PHP_EOL, FILE_APPEND);
            return;
        }
        
        $logData = [
            'eliminated_at' => date("Y-m-d H:i:s"),
            'accounts' => $this->accounts
        ];

        file_put_contents($filepath, json_encode($logData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL, FILE_APPEND);
    }

    public function run() {
        try {
            $this->fetchAccounts();
            $this->eliminateAccounts();
            $this->updateEliminatedAccounts();
            $this->updateUnEliminatedAccounts();
            $this->log("Account CDDS elimination process completed.");
            $this->log();

        } catch (Exception $e) {
            $this->log("[ERROR]" . $e->getMessage());
        }
    }

}

$cronJob = new AccountCddEliminationRunCronJob();
$cronJob->run();