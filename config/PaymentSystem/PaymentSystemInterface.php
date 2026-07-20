<?php
namespace App\PaymentSystem;

interface PaymentSystemInterface {

    public static function detail(): array|bool;

    public static function data(string $code): array|bool;

}