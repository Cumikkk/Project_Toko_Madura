<?php
use Allmedia\Shared\Regol\Steps\RegolController;
use Allmedia\Shared\Regol\Steps\RegolCollection;
use Allmedia\Shared\Regol\Steps\StepAccountType;
use Allmedia\Shared\Regol\Steps\StepCreateDemo;
use App\Models\Account;
use App\Models\Helper;

/** Account Information */
$userid = md5(md5($user["MBR_ID"]));
$demoAccount = Account::getDemoAccount($userid);
$accountSpa = Account::getAvailableProduct($userid, "spa");
$accountMultilateral = Account::getAvailableProduct($userid, "multilateral");
$realAccount = Account::getProgressRealAccount($userid);

/** Create regol step */
$collection = new RegolCollection();
$collection->add(new StepCreateDemo([
    'pageNext' => "/account/create?page=rate-jenis-account", 
    'fullname' => $user['MBR_NAME'],
    'email' => $user['MBR_EMAIL'],
    'phone' => $user['MBR_PHONE'],
    'date_create_demo' => date("Y-m-d"),
    'demo' => [
        'login' => $demoAccount['ACC_LOGIN'],
        'master' => $demoAccount['ACC_PASS'],
        'investor' => $demoAccount['ACC_INVESTOR'],
        'phone' => $demoAccount['ACC_PASSPHONE']
    ],
]));

/** Step 2 */
$collection->add(new StepAccountType([
    'pagePrev' => "/account/create",
    'demo_login' => $demoAccount['ACC_LOGIN'],
    'real_category' => $realAccount['RTYPE_TYPE_AS'] ?? "-",
    'real_rtype' => $realAccount['RTYPE_TYPE'] ?? "-",
    'categories' => [
        'spa' => array_map(function($array) {
            return [
                'type' => $array['type'],
                'products' => array_map(function($prd) {
                    return [
                        'id' => $prd['ID_RTYPE'],
                        'suffix' => $prd['RTYPE_SUFFIX'],
                        'name' => $prd['RTYPE_NAME'],
                        'type' => $prd['RTYPE_TYPE'],
                        'type_as' => $prd['RTYPE_TYPE_AS'],
                        'rate' => $prd['RTYPE_RATE'],
                        'currency' => $prd['RTYPE_CURR'],
                        'leverage' => $prd['RTYPE_LEVERAGE'],
                        'commission' => $prd['RTYPE_KOMISI'],
                    ];
                }, $array['products'])
            ];
        }, $accountSpa),
        'multilateral' => array_map(function($array) {
            return [
                'type' => $array['type'],
                'products' => array_map(function($prd) {
                    return [
                        'id' => $prd['ID_RTYPE'],
                        'suffix' => $prd['RTYPE_SUFFIX'],
                        'name' => $prd['RTYPE_NAME'],
                        'type' => $prd['RTYPE_TYPE'],
                        'type_as' => $prd['RTYPE_TYPE_AS'],
                        'rate' => $prd['RTYPE_RATE'],
                        'currency' => $prd['RTYPE_CURR'],
                        'leverage' => $prd['RTYPE_LEVERAGE'],
                        'commission' => $prd['RTYPE_KOMISI'],
                    ];
                }, $array['products'])
            ];
        }, $accountMultilateral),
    ],
]));

/** Step 3 */
$collection->add(new Step);

$currentPage = Helper::form_input($_GET['page'] ?? "");
$createSteps = new RegolController($collection);
$createSteps->render($currentPage);