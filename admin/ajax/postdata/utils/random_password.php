<?php 

use App\Models\Helper;

JsonResponse([
    'success' => true,
    'message' => "Berhasil",
    'data' => [
        'password' => Helper::generatePassword(8)
    ]
    ]);