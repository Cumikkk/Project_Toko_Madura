<?php
namespace App\Library\Sales;

interface SalesInterface {

    public function code(): string;
    
    public function getId(): bool|int;

    public function isCanShareRefferal(): bool;

    public function isCanConfigureCommission(): bool;
    
    public function isCanKeepDorman(): bool;
    
    public function level(): int;

    public function getUp(): int;

    public function division(): bool|array;
    
    public function isHeadOfStructure(): bool;

}