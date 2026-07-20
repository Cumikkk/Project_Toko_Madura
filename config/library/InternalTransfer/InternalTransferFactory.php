<?php
namespace App\Library\InternalTransfer;

use App\Library\InternalTransfer\AbstractTransfer;
use App\Library\InternalTransfer\Services\AccountToAccountTransfer;
use App\Library\InternalTransfer\Services\AccountToWalletTransfer;
use App\Library\InternalTransfer\Services\WalletToAccountTransfer;
use App\Library\InternalTransfer\TransferInterface;
use App\Library\InternalTransfer\TransferAdapter;

class InternalTransferFactory {

    /**
     * Create transfer instance based on source and destination type
     * 
     * @param int $mbrid User ID
     * @param TransferAdapter $from Source transfer adapter
     * @param TransferAdapter $to Destination transfer adapter
     * @param float $amount Transfer amount
     * 
     * @return AbstractTransfer Transfer service instance
     * 
     * @throws \InvalidArgumentException If transfer adapters are invalid
     * @throws \Exception If transfer type is not supported
     * 
     * @see WalletToAccountTransfer
     * @see AccountToWalletTransfer
     * @see AccountToAccountTransfer
     */
    public static function create(int $mbrid, TransferAdapter $from, TransferAdapter $to, float $amount, string $idempotencyKey): AbstractTransfer|TransferInterface {
        self::validateTransferAdapters($from, $to);
        
        if (self::isWalletToAccount($from, $to)) {
            return new WalletToAccountTransfer($mbrid, $from, $to, $amount, $idempotencyKey);
        }
        
        if (self::isAccountToWallet($from, $to)) {
            return new AccountToWalletTransfer($mbrid, $from, $to, $amount, $idempotencyKey);
        }
        
        if (self::isAccountToAccount($from, $to)) {
            return new AccountToAccountTransfer($mbrid, $from, $to, $amount, $idempotencyKey);
        }

        throw new \Exception("Transfer type {$from} -> {$to} is not supported.");
    }

    /**
     * Validate transfer adapters
     * 
     * @param TransferAdapter $from Source adapter
     * @param TransferAdapter $to Destination adapter
     * 
     * @throws \InvalidArgumentException If adapters are invalid
     */
    private static function validateTransferAdapters(TransferAdapter $from, TransferAdapter $to): void {
        if (!$from->login || !$to->login) {
            throw new \InvalidArgumentException("Invalid transfer adapters: source or destination login is missing.");
        }
    }

    /**
     * Check if transfer is from wallet to account
     */
    private static function isWalletToAccount(TransferAdapter $from, TransferAdapter $to): bool {
        return $from == "wallet" && $to != "wallet";
    }

    /**
     * Check if transfer is from account to wallet
     */
    private static function isAccountToWallet(TransferAdapter $from, TransferAdapter $to): bool {
        return $from != "wallet" && $to == "wallet";
    }

    /**
     * Check if transfer is from account to account
     */
    private static function isAccountToAccount(TransferAdapter $from, TransferAdapter $to): bool {
        return $from != "wallet" && $to != "wallet";
    }

}