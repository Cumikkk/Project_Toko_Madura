<?php
namespace App\Library\InternalTransfer\Services;

use App\Factory\MetatraderFactory;
use App\Library\InternalTransfer\AbstractTransfer;
use App\Library\InternalTransfer\TransferData;
use App\Models\Account;
use App\Models\Dpwd;
use App\Models\Helper;
use App\Models\Rate;
use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;
use mysqli_sql_exception;

class AccountToWalletTransfer extends AbstractTransfer {

    public function execute(): TransferData {
        try {
            $db = Database::connect();
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            mysqli_begin_transaction($db);
    
            /** insert data */
            $idInternalTransfer = $this->insertData();
            if(!is_int($idInternalTransfer) || $idInternalTransfer == 0) {
                $db->rollback();
                return new TransferData(false, [], "Internal Transfer failed");
            }
    
            /** Insert Dpwd untuk menambah user wallet */
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
                return new TransferData(false, [], "Failed transfer to wallet");
            } 
    
            /** withdrawal source */
            $withdrawalData = [
                'login' => $this->from->login,
                'amount' => ($this->amountReceived() * -1),
                'comment' => $this->commentFrom()
            ];
    
            $withdrawal = $this->apiManager->deposit($withdrawalData);
            if(!is_object($withdrawal) || !property_exists($withdrawal, "ticket")) {
                $db->rollback();
                return new TransferData(false, (array) $withdrawal, "Failed transfer from {$this->from->login}");
            }
    
            /** update ticket */
            Database::update("tb_internal_transfer", ['IT_TICKET_FROM' => $withdrawal->ticket], ['ID_IT' => $idInternalTransfer]);
            
            
            $db->commit();
            $this->logger(['mbrid' => $this->mbrid]);
            return new TransferData(true, (array) $withdrawal, "");

        } catch (Exception|mysqli_sql_exception $e) {
            $message = (SystemInfo::isDevelopment()) ? "Exception: " . $e->getMessage() : "Internal Server Error";
            return new TransferData(false, [], $message);
        }
    }    

}