<?php
namespace App\Library\InternalTransfer\Services;

use App\Factory\MetatraderFactory;
use App\Library\InternalTransfer\AbstractTransfer;
use App\Library\InternalTransfer\TransferData;
use App\Models\Account;
use App\Models\Dpwd;
use App\Models\Helper;
use App\Models\User;
use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;
use mysqli_sql_exception;

class WalletToAccountTransfer extends AbstractTransfer {

    public function execute(): TransferData {
        try {
            $db = Database::connect();
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            mysqli_begin_transaction($db);
    
            /** insert data internal transfer */
            $idInternalTransfer = $this->insertData();
            if(!is_int($idInternalTransfer) || $idInternalTransfer == 0) {
                $db->rollback();
                return new TransferData(false, [], "Failed to save");
            }
    
            /** Insert Dpwd untuk mengurangi user wallet */
            $insertDpwd = Database::insert("tb_dpwd", [
                'DPWD_MBR' => $this->mbrid,
                'DPWD_CODE' => $idInternalTransfer,
                'DPWD_TYPE' => Dpwd::$typeInternalTransfer,
                'DPWD_AMOUNT' => $this->amount,
                'DPWD_AMOUNT_SOURCE' => $this->amount,
                'DPWD_CURR_FROM' => $this->from->currency,
                'DPWD_CURR_TO' => $this->to->currency,
                'DPWD_RATE' => $this->transferRate,
                'DPWD_RATE_IDR' => $this->rateIDR,
                'DPWD_NOTE' => "Internal Transfer from {$this->from->login} to {$this->to->login} {$this->amount} {$this->from->currency}",
                'DPWD_NOTE1' => "Internal Transfer from {$this->from->login} to {$this->to->login} {$this->amount} {$this->from->currency}",
                'DPWD_STS' => -1,
                'DPWD_IP' => Helper::get_ip_address(),
                'DPWD_DATETIME' => date("Y-m-d H:i:s"),
            ]);
    
            if(!$insertDpwd) {
                $db->rollback();
                return new TransferData(false, [], "Failed transfer from {$this->from->login}");
            }
    
            /** deposit metatrader account */
            $depositData = [
                'login' => $this->to->login,
                'amount' => $this->amountReceived(),
                'comment' => $this->commentTo()
            ];
    
            $deposit = $this->apiManager->deposit($depositData);
            if(!is_object($deposit) || !property_exists($deposit, "ticket")) {
                $db->rollback();
                return new TransferData(false, (array) $deposit, "Failed transfer to {$this->to->login}");
            }
    
            /** update ticket */
            Database::update("tb_internal_transfer", ['IT_TICKET_TO' => $deposit->ticket], ['ID_IT' => $idInternalTransfer]);
    
            $db->commit();    
            $this->logger();
            return new TransferData(true, (array) $deposit, "");

        } catch (Exception|mysqli_sql_exception $e) {
            $message = (SystemInfo::isDevelopment()) ? "Exception: " . $e->getMessage() : "Internal Server Error";
            return new TransferData(false, [], $message);
        }
    }    
    
}