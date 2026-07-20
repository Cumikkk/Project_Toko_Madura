<?php
namespace App\Library\Sales;

use App\Models\Dpwd;
use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;

class NmiData {

    protected int $receiverMbrId;
    protected array $listMbrId;
    protected string $dateStart;
    protected string $dateEnd;
    protected float $totalDeposit;
    protected float $totalWithdrawal;
    protected float $rateNmi;

    public function __construct(int $_receiverMbrId, array $_listMbrId, ?string $_dateStart = null, ?string $_dateEnd = null, ?float $defaultRateNmi = 0) {
        $this->receiverMbrId = $_receiverMbrId;
        $this->listMbrId = $_listMbrId;
        $this->dateStart = $_dateStart;
        $this->dateEnd = $_dateEnd;
        $this->rateNmi = $defaultRateNmi;
        $this->deposit();
        $this->withdrawal();
    }

    public function listMbrId(): array {
        return $this->listMbrId;
    }

    public function deposit(): array {
        try {
            $this->totalDeposit = 0;
            if(empty($this->listMbrId())) {
                return [];
            }

            $db = Database::connect();
            $sqlGetDeposit = $db->query("
                SELECT
                    tm.MBR_ID,
                    tm.MBR_CODE,
                    tm.MBR_NAME,
                    tm.MBR_EMAIL,
                    td.ID_DPWD,
                    td.DPWD_AMOUNT,
                    td.DPWD_AMOUNT_SOURCE,
                    td.DPWD_CURR_FROM,
                    td.DPWD_CURR_TO,
                    td.DPWD_RATE,
                    td.DPWD_RATE_IDR
                FROM tb_dpwd td
                JOIN tb_member tm ON (tm.MBR_ID = td.DPWD_MBR)
                WHERE td.DPWD_TYPE = ".Dpwd::$typeDeposit."
                AND td.DPWD_MBR IN (".implode(",", $this->listMbrId).")
                AND DATE(td.DPWD_DATETIME) BETWEEN '{$this->dateStart}' AND '{$this->dateEnd}'
                AND td.DPWD_STS = -1
                AND NOT EXISTS (
                    SELECT 
                        HNMI_IDDPWD 
                    FROM tb_nmi_history 
                    WHERE HNMI_IDDPWD = td.ID_DPWD
                    AND HNMI_MBR = {$this->receiverMbrId}
                )
            ");

            $result1 = [];
            foreach($sqlGetDeposit->fetch_all(MYSQLI_ASSOC) as $deposit) {
                $depositObject = [
                    'user' => [
                        'id' => $deposit['MBR_ID'],
                        'code' => $deposit['MBR_CODE'],
                        'name' => $deposit['MBR_NAME'],
                        'email' => $deposit['MBR_EMAIL']
                    ],
                    'id' => $deposit['ID_DPWD'],
                    'amount' => $deposit['DPWD_AMOUNT'],
                    'amount_source' => $deposit['DPWD_AMOUNT_SOURCE'],
                    'currency_from' => $deposit['DPWD_CURR_FROM'],
                    'currency_to' => $deposit['DPWD_CURR_TO'],
                    'rate' => $deposit['DPWD_RATE'],
                    'rate_idr' => $deposit['DPWD_RATE_IDR'],
                    'amount_idr' => 0
                ];

                switch(true) {
                    case ($depositObject['currency_from'] == "IDR"):
                        $depositObject['amount_idr'] = $depositObject['amount_source'];
                        break;

                    case ($depositObject['currency_from'] == "USD"):
                        /** Jika rate deposit != 1 (artinya currency tidak sama) */
                        if($depositObject['rate'] != 1) {
                            $depositObject['amount_idr'] = ($depositObject['amount_source'] * $depositObject['rate']);
                            break;
                        }
                        
                        $depositObject['amount_idr'] = $this->rateNmi;
                        break;
                }

                $this->totalDeposit += $depositObject['amount_idr'];
                $result1[] = $depositObject;
            }

            return $result1;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public function withdrawal() {
        try {
            $this->totalWithdrawal = 0;
            if(empty($this->listMbrId())) {
                return [];
            }

            $db = Database::connect();
            $sqlGetWithdrawal = $db->query("
                SELECT
                    tm.MBR_ID,
                    tm.MBR_CODE,
                    tm.MBR_NAME,
                    tm.MBR_EMAIL,
                    td.ID_DPWD,
                    td.DPWD_AMOUNT,
                    td.DPWD_AMOUNT_SOURCE,
                    td.DPWD_CURR_FROM,
                    td.DPWD_CURR_TO,
                    td.DPWD_RATE,
                    td.DPWD_RATE_IDR
                FROM tb_dpwd td
                JOIN tb_member tm ON (tm.MBR_ID = td.DPWD_MBR)
                WHERE td.DPWD_TYPE = ".Dpwd::$typeWithdrawal."
                AND td.DPWD_MBR IN (".implode(",", $this->listMbrId).")
                AND DATE(td.DPWD_DATETIME) BETWEEN '{$this->dateStart}' AND '{$this->dateEnd}'
                AND td.DPWD_STS = -1
                AND NOT EXISTS (
                    SELECT 
                        HNMI_IDDPWD 
                    FROM tb_nmi_history 
                    WHERE HNMI_IDDPWD = td.ID_DPWD
                    AND HNMI_MBR = {$this->receiverMbrId}
                )
            ");

            $result = [];
            foreach($sqlGetWithdrawal->fetch_all(MYSQLI_ASSOC) as $withdrawal) {
                $withdrawalObject = [
                    'user' => [
                        'id' => $withdrawal['MBR_ID'],
                        'code' => $withdrawal['MBR_CODE'],
                        'name' => $withdrawal['MBR_NAME'],
                        'email' => $withdrawal['MBR_EMAIL']
                    ],
                    'id' => $withdrawal['ID_DPWD'],
                    'amount' => $withdrawal['DPWD_AMOUNT'],
                    'amount_source' => $withdrawal['DPWD_AMOUNT_SOURCE'],
                    'currency_from' => $withdrawal['DPWD_CURR_FROM'],
                    'currency_to' => $withdrawal['DPWD_CURR_TO'],
                    'rate' => $withdrawal['DPWD_RATE'],
                    'rate_idr' => $withdrawal['DPWD_RATE_IDR'],
                    'amount_idr' => 0
                ];

                switch(true) {
                    case ($withdrawalObject['currency_to'] == "IDR"):
                        $withdrawalObject['amount_idr'] = $withdrawalObject['amount'];
                        break;

                    case ($withdrawalObject['currency_to'] == "USD"):
                        /** Jika rate deposit != 1 (artinya currency tidak sama) */
                        if($withdrawalObject['rate'] != 1) {
                            $withdrawalObject['amount_idr'] = ($withdrawalObject['amount_source'] * $withdrawalObject['rate']);
                            break;
                        }
                        
                        // $withdrawalObject['amount_idr'] = ($withdrawalObject['amount_source'] * $withdrawalObject['rate_idr']);
                        $withdrawalObject['amount_idr'] = $this->rateNmi;
                        break;
                }

                $this->totalWithdrawal += $withdrawalObject['amount_idr'];
                $result[] = $withdrawalObject;
            }

            return $result;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public function totalDeposit() {
        return $this->totalDeposit;
    }

    public function totalWithdrawal() {
        return $this->totalWithdrawal;
    }

    public function nmi(): float {
        /** Get Deposit Amount */
        $totalDeposit = $this->totalDeposit;

        // /** Get Withdrawal amount */
        $totalWithdrawal = $this->totalWithdrawal;

        $nmi = $totalDeposit - $totalWithdrawal;
        return $nmi;
    }
}