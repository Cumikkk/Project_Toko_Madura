<?php
namespace App\Library\InternalTransfer\Services;

use App\Factory\MetatraderFactory;
use App\Library\InternalTransfer\AbstractTransfer;
use App\Library\InternalTransfer\TransferData;
use App\Models\Account;
use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;
use mysqli_sql_exception;

class AccountToAccountTransfer extends AbstractTransfer {

    public function execute(): TransferData {
        try {
            $db = Database::connect();
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            mysqli_begin_transaction($db);
    
            /** insert data */
            $idInternalTransfer = $this->insertData();
            if(!is_int($idInternalTransfer) || $idInternalTransfer == 0) {
                $db->rollback();
                return new TransferData(false, [], "Internal Transfer Failed");
            }
    
            /** withdrawal source */
            $withdrawalData = [
                'login' => $this->from->login,
                'amount' => ($this->amount * -1),
                'comment' => $this->commentFrom()
            ];
    
            $withdrawal = $this->apiManager->deposit($withdrawalData);
            if(!is_object($withdrawal) || !property_exists($withdrawal, "ticket")) {
                $db->rollback();
                return new TransferData(false, (array) $withdrawal, "Failed transfer from {$this->from->login}" );
            }
    
            /** deposit destination, success/gagal tetap process, karena tidak bisa dirollback */
            $deposit = $this->apiManager->deposit([
                'login' => $this->to->login,
                'amount' => $this->amountReceived(),
                'comment' => $this->commentTo()
            ]);
    
            /** update ticket */
            Database::update("tb_internal_transfer", ['IT_TICKET_FROM' => $withdrawal->ticket], ['ID_IT' => $idInternalTransfer]);
            Database::update("tb_internal_transfer", ['IT_TICKET_TO' => ($deposit->ticket ?? NULL)], ['ID_IT' => $idInternalTransfer]);
            
            /** Logger */
            $this->logger(['mbrid' => $this->mbrid]);
    
            $db->commit();
            $this->logger();
            return new TransferData(true, (array) $withdrawal, "");

        } catch (Exception|mysqli_sql_exception $e) {
            $message = (SystemInfo::isDevelopment()) ? "Exception: " . $e->getMessage() : "Internal Server Error";
            return new TransferData(false, [], $message);
        }
    }    

}