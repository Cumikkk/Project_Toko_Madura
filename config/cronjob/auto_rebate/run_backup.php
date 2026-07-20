<?php

use App\Models\Dpwd;
use App\Models\Helper;
use Config\Core\Database;

require_once __DIR__ . "/../../setting.php";

class AutoRebate {

    public string $codeRun;
    public string $datetime;
    public string $dateStart;
    public string $dateEnd;
    public array $tradingHistory = [];
    public array $calculatedTrades = [];
    public array $result = [];
    public ?int $memberId;

    public function __construct(string $dateStart, string $dateEnd, int $memberId = 0) {
        $this->codeRun = Helper::generate_unique("AR");
        $this->dateStart = $dateStart;
        $this->dateEnd = $dateEnd;
        $this->datetime = date('Y-m-d H:i:s');
        $this->memberId = $memberId;
    }

    private function getAllTradingHistory(): array {
        $db = Database::connect();
        $queryWithMemberFilter = "";
        if($this->memberId !== null && $this->memberId > 0) {
            $queryWithMemberFilter = "AND tm.MBR_ID = " . intval($this->memberId);
        }

        // AND (UNIX_TIMESTAMP(deal.`time_min7h`) >= UNIX_TIMESTAMP('{$this->dateStart}') AND UNIX_TIMESTAMP(deal.`time_min7h`) < UNIX_TIMESTAMP('{$this->dateEnd}'))
        $sqlGetTradingHistory = $db->query("
            SELECT 
                tm.MBR_ID as user_id,
                tm.MBR_EMAIL as email,
                tm.MBR_NAME as fulname,
                tm.MBR_CODE as user_code,
                tr.ID_ACC as account_id,
                t.login,
                t.ticket,
                t.volume,
                t.symbol,
                t.symbol_category,
                t.profit,
                t.`time_min7h` as `time`
            FROM tb_racc as tr
            JOIN tb_member as tm ON (tm.MBR_ID = tr.ACC_MBR)
            JOIN (
                SELECT
                    ID_SLSCONDITION,
                    SLSCONDITION_IDACC
                FROM tb_sales_conditions
                WHERE SLSCONDITION_STS = -1
            ) as sales_condition ON (sales_condition.SLSCONDITION_IDACC = tr.ID_ACC)
            JOIN (
                SELECT 
                    deal.login,
                    deal.deal_id as ticket,
                    (deal.volume / 10000) as volume,
                    deal.profit,
                    deal.time_min7h,
                    deal.symbol,
                    symbol_info.symbol_name,
                    symbol_info.symbol_category
                FROM mt5_deals as deal
                JOIN (
                    SELECT
                        ts.SYM_NAME as symbol_name,
                        tsc.SYMCAT_NAME as symbol_category
                    FROM tb_symbol as ts
                    JOIN tb_symbolcat as tsc ON (tsc.ID_SYMCAT = ts.ID_SYMCAT)
                    GROUP BY ts.SYM_NAME
                ) as symbol_info ON (symbol_info.symbol_name = deal.symbol)
                WHERE deal.entry = 1
                AND deal.action IN (0,1)
                AND deal.`time_min7h` BETWEEN '{$this->dateStart} 00:00:00' AND '{$this->dateEnd} 00:00:00'
                AND NOT EXISTS (
                    SELECT 
                        trh.H_TICKET
                    FROM tb_rebate_history as trh
                    WHERE trh.H_TICKET = deal.deal_id
                )
                GROUP BY deal.deal_id, deal.login
            ) as t ON (t.login = tr.ACC_LOGIN)
            WHERE tr.ACC_DERE = 1
            AND tr.ACC_STS = -1
            AND tm.MBR_IDSPN != 1000000000
            $queryWithMemberFilter
        ");

        if(!$sqlGetTradingHistory) {
            throw new Exception("Database query failed sqlGetTradingHistory: " . $db->error);
        }

        if($sqlGetTradingHistory->num_rows === 0) {
            return [];
        }

        $this->tradingHistory = $sqlGetTradingHistory->fetch_all(MYSQLI_ASSOC);
        return $this->tradingHistory;
    }

    private function assignRebateCommission(): array {
        $db = Database::connect();
        $listAccountId = array_unique(array_column($this->tradingHistory, 'account_id'));

        /** get all commission where SLSCOM_IDACC in $listAccountId */
        $sqlGetCommission = $db->query("
            SELECT 
                *
            FROM tb_sales_commission
            WHERE SLSCOM_IDACC IN (" . implode(',', $listAccountId) . ")
        ");

        if(!$sqlGetCommission) {
            throw new Exception("Database query failed sqlGetCommission: " . $db->error);
        }

        if($sqlGetCommission->num_rows === 0) {
            return [];
        }

        /** initial value */
        foreach($listAccountId as $accId) {
            if(!array_key_exists($accId, $this->result)) {
                $this->result[$accId] = [
                    'fullname' => '',
                    'login' => 0,
                    'total_volume' => 0,
                    'code' => Helper::generate_unique("AR", 6),
                    'volumes' => [
                        'forex' => 0,
                        'index' => 0,
                        'komoditi' => 0,
                        'other' => 0
                    ],
                    'settings' => [],
                ];
            }

            /** assign volume */
            foreach($this->tradingHistory as $trade) {
                if($trade['account_id'] == $accId) {
                    $this->result[$accId]['login'] = $trade['login'];
                    $this->result[$accId]['fullname'] = $trade['fulname'];
                    $this->result[$accId]['total_volume'] += $trade['volume'];

                    switch(strtolower($trade['symbol_category'] ?? "")) {
                        case 'forex':
                            $this->result[$accId]['volumes']['forex'] += $trade['volume'];
                            break;
                        
                        case 'index':
                            $this->result[$accId]['volumes']['index'] += $trade['volume'];
                            break;

                        case 'komoditi':
                            $this->result[$accId]['volumes']['komoditi'] += $trade['volume'];
                            break;

                        default:
                            $this->result[$accId]['volumes']['other'] += $trade['volume'];
                            break;
                    }

                    $this->calculatedTrades[] = array_merge($trade, [
                        'rebate_code' => $this->result[$accId]['code'],
                    ]);
                }
            }
        }

        /** assign settings */
        foreach($sqlGetCommission->fetch_all(MYSQLI_ASSOC) as $comm) {
            $volumes = $this->result[ $comm['SLSCOM_IDACC'] ]['volumes'];
            $this->result[ $comm['SLSCOM_IDACC'] ]['settings'][ $comm['SLSCOM_MBR'] ] = [
                'forex' => [
                    'base' => $comm['SLSCOM_FOREX'],
                    'volume' => $volumes['forex'],
                    'rebate' => $this->calculateRebate($volumes['forex'], $comm['SLSCOM_FOREX']),
                ],
                'index' => [
                    'base' => $comm['SLSCOM_INDEX'],
                    'volume' => $volumes['index'],
                    'rebate' => $this->calculateRebate($volumes['index'], $comm['SLSCOM_INDEX']),
                ],
                'komoditi' => [
                    'base' => $comm['SLSCOM_GOLD'],
                    'volume' => $volumes['komoditi'],
                    'rebate' => $this->calculateRebate($volumes['komoditi'], $comm['SLSCOM_GOLD']),
                ],
            ];
        }

        return $this->result;
    }

    private function calculateRebate(float $volume, float $commission): float {
        return $volume * $commission;
    }

    private function insertTicketHistory() {
        $columns = [
            'H_TICKET' => 'i',
            'H_MBR' => 'i',
            'H_LOGIN' => 's',
            'H_CODE' => 's',
            'H_AMOUNT' => 'd',
            'H_EXECUTE_AT' => 's',
            'H_SYMBOL' => 's',
        ];

        $placeholders = [];
        $types = '';
        $values = [];
        $column = array_keys($columns);
        foreach($this->calculatedTrades as $trade) {
            $placeholders[] = '(' . implode(',', array_fill(0, count($columns), '?')) . ')';
            foreach($columns as $col => $type) {
                $types .= $type;
                switch($col) {
                    case 'H_TICKET':
                        $values[] = $trade['ticket'];
                        break;
                    case 'H_MBR':
                        $values[] = $trade['user_id'];
                        break;
                    case 'H_LOGIN':
                        $values[] = $trade['login'];
                        break;
                    case 'H_CODE':
                        $values[] = $trade['rebate_code'];
                        break;
                    case 'H_AMOUNT':
                        $values[] = $trade['volume'];
                        break;
                    case 'H_EXECUTE_AT':
                        $values[] = $this->datetime;
                        break;
                    case 'H_SYMBOL':
                        $values[] = $trade['symbol'];
                        break;
                }
            }
        }

        $db = Database::connect();
        $sqlPrepare = $db->prepare("
            INSERT INTO tb_rebate_history (" . implode(',', $column) . ")
            VALUES " . implode(',', $placeholders) . "
        ");

        $sqlPrepare->bind_param($types, ...$values);
        $sqlPrepare->execute();
    }

    private function insertRebateCommission() {
        // Implementation for inserting rebate commission into the database
        $columns = [
            'DPWD_IDEMPOTENCY_KEY' => 's',
            'DPWD_MBR' => 's',
            'DPWD_CODE' => 's',
            'DPWD_TYPE' => 'i',
            'DPWD_AMOUNT' => 'd',
            'DPWD_AMOUNT_SOURCE' => 'd',
            'DPWD_CURR_FROM' => 's',
            'DPWD_CURR_TO' => 's',
            'DPWD_RACC' => 'i',
            'DPWD_DEVICE' => 's',
            'DPWD_NOTE' => 's',
            'DPWD_NOTE1' => 's',
            'DPWD_STS' => 'i',
            'DPWD_STSACC' => 'i',
            'DPWD_STSVER' => 'i',
            'DPWD_DATETIME' => 's',
            'DPWD_METADATA' => 's',
        ];

        $placeholders = [];
        $types = '';
        $values = [];
        $column = array_keys($columns);
        $order = 0;
        foreach($this->result as $result) {
            foreach($result['settings'] as $receiver_id => $commission) {
                foreach($commission as $commissionType => $amount) {
                    $totalRebate = max(0, $amount['rebate']);
                    
                    /** skip if total rebate is less than or equal to zero */
                    if($totalRebate <= 0) {
                        continue;
                    }

                    $placeholders[] = '(' . implode(',', array_fill(0, count($columns), '?')) . ')';
                    foreach($columns as $col => $type) {
                        $types .= $type;
                        switch($col) {
                            case 'DPWD_IDEMPOTENCY_KEY':
                                $values[] = $result['code'] . $order++;
                                break;
                                
                            case 'DPWD_MBR':
                                $values[] = $receiver_id;
                                break;
    
                            case 'DPWD_CODE':
                                $values[] = $result['code'];
                                break;
    
                            case 'DPWD_TYPE':
                                $values[] = Dpwd::$typeRebateCommission;
                                break;
    
                            case 'DPWD_AMOUNT':
                                $values[] = $totalRebate;
                                break;
    
                            case 'DPWD_AMOUNT_SOURCE':
                                $values[] = $totalRebate;
                                break;
    
                            case 'DPWD_CURR_FROM':
                                $values[] = "USD";
                                break;
    
                            case 'DPWD_CURR_TO':
                                $values[] = "USD";
                                break;
    
                            case 'DPWD_NOTE':
                                $values[] = sprintf("Rebate commission from %s (%s), volume: %s, commission: %s", $result['fullname'], $result['login'], $amount['volume'], $amount['base']);
                                break;
    
                            case 'DPWD_NOTE1':
                                $values[] = $commissionType;
                                break;

                            case 'DPWD_RACC':
                                $values[] = $result['login'];
                                break;

                            case 'DPWD_DEVICE':
                                $values[] = "cron";
                                break;
    
                            case 'DPWD_STS':
                                $values[] = -1;
                                break;
    
                            case 'DPWD_STSACC':
                                $values[] = -1;
                                break;
    
                            case 'DPWD_STSVER':
                                $values[] = -1;
                                break;
    
                            case 'DPWD_DATETIME':
                                $values[] = $this->datetime;
                                break;  

                            case 'DPWD_METADATA':
                                $values[] = json_encode([
                                    'group' => $commissionType,
                                    'commission' => $amount['base'],
                                    'volume' => $amount['volume'],
                                ]);
                                break;
                        }
                    }
                }
            }
        }

        $db = Database::connect();
        $sqlPrepare = $db->prepare("
            INSERT INTO tb_dpwd (" . implode(',', $column) . ")
            VALUES " . implode(',', $placeholders) . "
        ");

        $sqlPrepare->bind_param($types, ...$values);
        $sqlPrepare->execute();
    }

    public function shareRebateCommission() {
        /** fetch trading history */
        $this->getAllTradingHistory();
        if(empty($this->tradingHistory)) {
            throw new Exception("No trading history to process rebate commission.");
        }
           
        /** assign rebate commission */
        $this->assignRebateCommission();
        if(empty($this->result)) {
            throw new Exception("No rebate commission assigned.");
        }

        // echo "<pre>";
        // print_r($this->calculatedTrades);
        // echo "</pre>";
        // die;

        try {
            $db = Database::connect();
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            mysqli_begin_transaction($db);

            /** insert ticket history  */
            $this->insertTicketHistory();

            /** insert rebate commission */
            $this->insertRebateCommission();

            // echo "<pre>";
            // print_r($this->result);
            // echo "</pre>";
            $db->commit();

        } catch (Exception $e) {
            $db->rollback();
            throw new Exception("Failed to share rebate commission: " . $e->getMessage());
        }
    }

    public function log(?string $message = null) {
        // Implementation for logging
        $filepath = __DIR__ . sprintf("/logs/%s.json", str_replace("-", "_", $this->codeRun));
        if(!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0777, true);
        }

        if($message !== null) {
            file_put_contents($filepath, $message . PHP_EOL, FILE_APPEND);
            return;
        }
        
        $logData = [
            'code_run' => $this->codeRun,
            'date_start' => $this->dateStart,
            'date_end' => $this->dateEnd,
            'trading_history_count' => count($this->tradingHistory),
            'trading_history' => $this->tradingHistory,
            'result' => $this->result,
        ];

        file_put_contents($filepath, json_encode($logData, JSON_PRETTY_PRINT));
    } 
}

try {
    // Example: php run.php --start="2026-02-01" --end="2026-02-28"
    $options = getopt("", ['start:', 'end:', 'userid:']);
    $dateStart = date('2026-02-01 00:00:00', strtotime('-1 day'));
    $dateEnd = date('Y-m-d 00:00:00');
    $userid = null;
    
    if(isset($options['start'])) {
        if(empty($options['start'])) {
            throw new Exception("Start date cannot be empty.");
        }

        $dateStart = date('Y-m-d 00:00:00', strtotime($options['start']));
    }

    if(isset($options['end'])) {
        if(empty($options['end'])) {
            throw new Exception("End date cannot be empty.");
        }

        $dateEnd = date('Y-m-d 00:00:00', strtotime($options['end']));
    }

    if(isset($options['userid'])) {
        if(!empty($options['userid'])) {
            $userid = $options['userid'];
        }
    }
    
    $autoRebateClass = new AutoRebate($dateStart, $dateEnd, $userid ?? 0);
    $autoRebateClass->shareRebateCommission();
    // $autoRebateClass->log();

} catch (Exception $e) {
    $autoRebateClass->log("Error: " . $e->getMessage());
    exit;
}