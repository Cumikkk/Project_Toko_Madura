<?php
use Allmedia\Shared\Regol\Steps\RegolController;
use Allmedia\Shared\Regol\Steps\RegolCollection;
use Allmedia\Shared\Regol\Steps\StepAccountType;
use Allmedia\Shared\Regol\Steps\StepCreateDemo;
use App\Models\Helper;

$collection = new RegolCollection();
$collection->add(new StepCreateDemo([
    'fullname' => $user['MBR_NAME'],
    'email' => $user['MBR_EMAIL'],
    'phone' => $user['MBR_PHONE'],
    'date_create_demo' => date("Y-m-d"),
    'demo' => []
]));

$collection->add(new StepAccountType([
    'fullname' => "test",
    'email' => $user['MBR_EMAIL'],
    'phone' => $user['MBR_PHONE'],
    'date_create_demo' => date("Y-m-d"),
    'demo' => []
]));

$currentPage = Helper::form_input($_GET['page'] ?? "");
$createSteps = new RegolController($collection);
$createSteps->render($currentPage);