<?php
namespace App\Library\Firebase;

abstract class FirebaseAbstract
{
    protected $firebase;

    public function __construct($firebase)
    {
        $this->firebase = $firebase;
    }
}