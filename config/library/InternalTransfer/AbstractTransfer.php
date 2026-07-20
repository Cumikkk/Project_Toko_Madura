<?php
namespace App\Library\InternalTransfer;

use Allmedia\Shared\Metatrader\ApiManager;
use App\Factory\MetatraderFactory;
use App\Models\Dpwd;
use App\Models\Logger;
use App\Models\Rate;
use Config\Core\Database;

abstract class AbstractTransfer implements TransferInterface {

    protected $mbrid;
    protected TransferAdapter $from;
    protected TransferAdapter $to;
    protected float $amount;
    protected float $transferRate = 0;
    protected ApiManager $apiManager;
    protected string $idempotencyKey;

    /** Opstional Data */
    public string $device = "Web";
    public float $rateIDR = 0;

    public function __construct(int $_mbrid, TransferAdapter $_from, TransferAdapter $_to, float $_amount, string $idempotencyKey) {
        $this->mbrid = $_mbrid;
        $this->from = $_from;
        $this->to = $_to;
        $this->amount = $_amount;   
        $this->transferRate = $this->rate();
        $this->apiManager = MetatraderFactory::apiManager();
        $this->idempotencyKey = $idempotencyKey;
    }

    public function commentFrom(): string {
        return "Withdrawal IT To {$this->to->login}";
    }

    public function commentTo(): string {
        return "Deposit IT From {$this->from->login}";
    }

    public function rate(): float {
        /** Rate Info */
        if($this->from->currency == $this->to->currency) {
            return 1;
        }

        if($this->transferRate > 0) {
            return $this->transferRate;
        }

        $rate = Rate::autoCheckRate($this->from->currency, $this->to->currency);
        if(!$rate) {
            return 0;
        }

        return $this->transferRate = $rate;
    }

    public function amountReceived(): float {
        if($this->transferRate <= 0) {
            return 0;
        }

        $finalAmount = $this->transferRate * $this->amount;
        if($finalAmount <= 0) {
            return 0;
        }

        return $finalAmount;
    }

    public function insertData() {
        global $db;
        Database::insert("tb_internal_transfer", [
            'IT_CODE' => uniqid(),
            'IT_IDEMPOTENCY_KEY' => $this->idempotencyKey,
            
            'IT_FROM' => $this->from->login,
            'IT_COMMENT_FROM' => $this->commentFrom(),
            'IT_CURR_FROM' => $this->from->currency,
            'IT_RATE_FROM' => $this->from->rate,
            'IT_AMOUNT_SOURCE' => $this->amount,
            
            'IT_TO' => $this->to->login,
            'IT_COMMENT_TO' => $this->commentTo(),
            'IT_CURR_TO' => $this->to->currency,
            'IT_RATE_TO' => $this->to->rate,
            'IT_AMOUNT' => $this->amountReceived(),

            'IT_TICKET_FROM' => null,
            'IT_TICKET_TO' => null,
            'IT_DATETIME' => date("Y-m-d H:i:s"),
        ]);

        return $db->insert_id;
    }

    public function logger() {
        Logger::client_log([
            'module' => "internal-transfer",
            'message' => "Internal Transfer from {$this->from->login} to {$this->to->login} \${$this->amount}",
            'data' => array_merge([
                'from' => $this->from,
                'to' => $this->to,
                'amount' => $this->amount,
                'datetime' => date("Y-m-d H:i:s")
            ])
        ]);
    }

}