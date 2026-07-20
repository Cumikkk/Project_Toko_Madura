<?php
namespace App\Factory;

class ErrorCodeFactory {

    /**
     * Summary of FAILED_UPDATE_PASSCODE_ATTEMPT
     * @var int
     * Gagal memperbarui passcode_attempt setelah salah 
     */
    const FAILED_UPDATE_PASSCODE_ATTEMPT = 100;

    /**
     * Summary of FAILED_UPDATE_USER_STATUS_TO_LOCKED
     * @var int
     * Gagal memperbarui status user ke locked saat verifikasi passcode
     */
    const FAILED_UPDATE_USER_STATUS_TO_LOCKED = 101;

    /**
     * Summary of FAILED_SOFT_DELETE_PASSCODE
     * @var int
     * Gagal melakukan soft delete ke member_passcode saat verifikasi percobaan terakhir
     */
    const FAILED_SOFT_DELETE_PASSCODE = 102;

    /**
     * Summary of FAILED_UPDATE_USER_STATUS_TO_ACTIVE
     * @var int
     * Gagal memperbarui status user ke active 
     */
    const FAILED_UPDATE_USER_STATUS_TO_ACTIVE = 103;


    /**
     * Summary of FAILED_USER_PASSWORD_ATTEMPT_IS_NOT_NUMERIC
     * @var int
     * MBR_PASS_ATTEMPT is not numeric
     */
    const FAILED_PASSWORD_ATTEMPT_IS_NOT_NUMERIC = 104;

    /**
     * Summary of FAILED_LOCK_TRANSACTION_ROW
     * @var int
     * Gagal menemukan row untuk di lock dalam transaction
     */
    const FAILED_LOCK_TRANSACTION_ROW = 105;

    /**
     * Summary of INVALID_ACCOUNT_STATUS
     * @var int
     * Invalid Account Status
     */
    const INVALID_ACCOUNT_STATUS = 106;

    /**
     * Summary of INVALID_DUPLICATE_ACCOUNT
     * @var int
     * Invalid Duplicate Account
     */
    const INVALID_DUPLICATE_ACCOUNT = 107;

}