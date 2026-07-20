<?php
require_once __DIR__ . "/../../config/setting.php";

use App\Factory\FileUploadFactory;
use App\Factory\LoggingFactory;
use App\Factory\MetatraderFactory;
use App\Factory\VerihubFactory;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\Admin;
use App\Models\BankList;
use App\Models\FileUpload;
use App\Models\Firebase;
use App\Models\Helper;
use App\Models\Logger;
use App\Models\MemberBank;
use App\Models\ProfilePerusahaan;
use App\Models\Rate;
use App\Models\Regol;
use App\Models\SendEmail;
use App\Models\User;
use Config\Core\Database;
use Config\Core\EmailSender;
use Config\Core\Logging;

class AppPost {
    private $db;
    private $ip_address;

    public function __construct()
    {
        //Do your magic here
        $this->db = Database::connect(); 
    }
    
    private function required(array $key, $from)
    {
        foreach($key as $k) {
            if(!array_key_exists($k, $from)) {
                return "Required data  [{$k}] not found.";
            }
        }

        return true;
    }

    private function checkCsrfToken(array $data = []) {
        if(empty($data['csrf_token'])) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Invalid CSRF_TOKEN",
                    'icon'  => "error"
                ]
            ]));
        }

        // if(!isValidCSRFToken($data['csrf_token'])) {
        //     exit(json_encode([
        //         'success'   => false,
        //         'alert'     => [
        //             'title' => "Gagal",
        //             'text'  => "CSRF_TOKEN Expired",
        //             'icon'  => "error"
        //         ]
        //     ]));
        // }

        return true;
    }

    private function checkProgressAccount(string $userid) {
        $progressAccount = Account::getProgressRealAccount($userid);
        if(empty($progressAccount)) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Invalid account",
                    'icon'  => "error"
                ]
            ]));  
        }

        return $progressAccount;
    }

    private function isAllowToEdit(int $status) {
        if(in_array($status, [1, -1])) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Akun sedang dalam prosess verifikasi",
                    'icon'  => "error"
                ]
            ]));  
        }

        return true;
    }

    public function getRegency($data, $user) {
        $progressAccount = $this->checkProgressAccount(md5(md5($user['MBR_ID'])));
        if(empty($data['province'])) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Province is required",
                    'icon'  => "error"
                ]
            ]));  
        }

        $province = base64_decode($data['province']);
        $sqlGet = $this->db->query("SELECT KDP_KABKO FROM tb_kodepos WHERE UPPER(KDP_PROV) = UPPER('{$province}') GROUP BY KDP_KABKO ORDER BY KDP_KABKO");
        if($sqlGet->num_rows == 0) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Province not found",
                    'icon'  => "error"
                ]
            ]));  
        }

        $list = [];
        foreach($sqlGet->fetch_all(MYSQLI_ASSOC) as $prov) {
            $list[] = [
                'name' => $prov['KDP_KABKO'],
                'selected' => (($progressAccount['ACC_REGENCY'] ?? $user['MBR_CITY']) == $prov['KDP_KABKO'])
            ];
        }

        exit(json_encode([
            'success'   => true,
            'data'      => $list,
            'alert'     => [
                'title' => "Berhasil",
                'text'  => "Province found",
                'icon'  => "success"
            ]
        ]));  
    }

    public function getDistrict($data, $user) {
        $progressAccount = $this->checkProgressAccount(md5(md5($user['MBR_ID'])));
        if(empty($data['regency'])) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Regency is required",
                    'icon'  => "error"
                ]
            ]));  
        }

        $regency = $data['regency'];
        $sqlGet = $this->db->query("SELECT KDP_KECAMATAN FROM tb_kodepos WHERE UPPER(KDP_KABKO) = UPPER('{$regency}') GROUP BY KDP_KECAMATAN ORDER BY KDP_KECAMATAN");
        if($sqlGet->num_rows == 0) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Regency not found",
                    'icon'  => "error"
                ]
            ]));  
        }

        $list = [];
        foreach($sqlGet->fetch_all(MYSQLI_ASSOC) as $prov) {
            $list[] = [
                'name' => $prov['KDP_KECAMATAN'],
                'selected' => (($progressAccount['ACC_DISTRICT'] ?? $user['MBR_DISTRICT']) == $prov['KDP_KECAMATAN'])
            ];
        }

        exit(json_encode([
            'success'   => true,
            'data'      => $list,
            'alert'     => [
                'title' => "Berhasil",
                'text'  => "Regency found",
                'icon'  => "success"
            ]
        ]));  
    }

    public function getVillages($data, $user) {
        $progressAccount = $this->checkProgressAccount(md5(md5($user['MBR_ID'])));
        if(empty($data['district'])) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "District is required",
                    'icon'  => "error"
                ]
            ]));  
        }

        $district = $data['district'];
        $sqlGet = $this->db->query("SELECT KDP_KELURAHAN, KDP_POS FROM tb_kodepos WHERE UPPER(KDP_KECAMATAN) = UPPER('{$district}') GROUP BY KDP_KELURAHAN ORDER BY KDP_KELURAHAN");
        if($sqlGet->num_rows == 0) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "District not found",
                    'icon'  => "error"
                ]
            ]));  
        }

        $list = [];
        foreach($sqlGet->fetch_all(MYSQLI_ASSOC) as $vil) {
            $list[] = [
                'village'   => $vil['KDP_KELURAHAN'],
                'selected'  => (($progressAccount['ACC_VILLAGE'] ?? $user['MBR_VILLAGES']) == $vil['KDP_KELURAHAN']),
                'postalCode'=> $vil['KDP_POS'] 
            ];
        }

        exit(json_encode([
            'success'   => true,
            'data'      => $list,
            'alert'     => [
                'title' => "Berhasil",
                'text'  => "District found",
                'icon'  => "success"
            ]
        ]));  
    }

    public function createDemo($data, $user) {
        global $web_name_full;
        // $this->checkCsrfToken($data);
        
        $mbrid = $user['MBR_ID'] ?? 0;
        $type = $data['type'] ?? "SPA";

        if(!in_array($type, ["SPA", "MULTILATERAL"])) {
            JsonResponse([
                'success' => false,
                'message' => "Tipe akun demo tidak valid",
                'data' => []
            ]);
        }

        $demoAccount = Account::getDemoAccount(md5(md5($mbrid)), $type);
        if(!empty($demoAccount)) {
            JsonResponse([
                'success' => false,
                'message' => "Sudah memiliki akun demo",
                'data' => []
            ]);
        }

        /** create demo account */
        $createDemo = MetatraderFactory::createDemo($user['MBR_NAME'], $user['MBR_EMAIL'], $type);
        if(!$createDemo['success']) {
            JsonResponse([
                'success'   => false,
                'message'   => $createDemo['message'] ?? "Gagal",
                'data'      => []
            ]);
        }

        /** Insert Demo */
        $demoData = $createDemo['data'];
        $insertDemo = Database::insert("tb_racc", [
            'ACC_MBR' => $user['MBR_ID'],
            'ACC_DERE' => 2,
            'ACC_TYPE' => $demoData['type'],
            'ACC_LOGIN' => $demoData['login'],
            'ACC_PASS' => $demoData['password'],
            'ACC_INVESTOR' => $demoData['investor'],
            'ACC_PASSPHONE' => $demoData['passphone'],
            'ACC_INITIALMARGIN' => MetatraderFactory::$initMarginDemo,
            'ACC_FULLNAME' => $user['MBR_NAME'],
            'ACC_DATETIME' => date("Y-m-d H:i:s"),
        ]);

        /** Send Notification Email */
        $emailData = [
            "subject"       => "Demo Account Information ". ProfilePerusahaan::get()['COMPANY_NAME'] ." ".date('Y-m-d H:i:s'),
            "name"          => $user["MBR_NAME"],
            "login"         => $demoData['login'],
            "metaPassword"  => $demoData['password'],
            "metaInvestor"  => $demoData['investor'],
            "metaPassPhone" => $demoData['passphone'],
        ];

        $emailSender = EmailSender::init(['email' => $user['MBR_EMAIL'], 'name' => $user['MBR_NAME']]);
        $emailSender->useFile("create-demo", $emailData);
        $send = $emailSender->send();

        Logger::client_log([
            'mbrid' => $mbrid,
            'module' => "create-demo",
            'message' => "Create Demo Account ".$demoData['login'],
            'data'  => json_encode($_POST)
        ]);

        exit(json_encode([
            'success' => true,
            'message' => "Buat akun demo berhasil, silakan periksa email Anda untuk melihat informasi akun demo Anda",
            'data' => [
                'login' => $demoData['login'],
            ]
        ]));
        // exit(json_encode([
        //     'success'   => true,
        //     'error'     => "",
        //     'message'   => "Buat akun demo berhasil",
        //     'data'      => [
        //         'login' => $demoData['login'],
        //         'passw' => $demoData['password'],
        //         'invst' => $demoData['investor'],
        //         'phone' => $demoData['passphone'],
        //         'mails' => "Silakan periksa email Anda.Dan jangan beri tahu password, investor, passphone Anda kepada siapa pun!"
        //     ]
        // ]));
    }

    public function updateAccountMarginLimit(int $accountId, array $productInfo, $user) {
        if(!$productInfo || !$accountId) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Informasi produk tidak valid",
                    'icon'  => "error"
                ]
            ]));
        }

        $limitMargin = ProfilePerusahaan::limitAccountMargin();
        $rate = $productInfo['RTYPE_RATE'] ?? 1;
        if($productInfo['RTYPE_ISFLOATING']) {
            $rate = Rate::autoCheckRate($productInfo['RTYPE_CURR'], "IDR");
            if(!$rate) {
                exit(json_encode([
                    'success'   => false,
                    'alert'     => [
                        'title' => "Gagal",
                        'text'  => "Gagal memperbarui info produk",
                        'icon'  => "error"
                    ]
                ]));
            }
        }

        $limitMarginUSD = round($limitMargin / $rate, 2);
        if($limitMarginUSD < 0) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Rate produk tidak valid",
                    'icon'  => "error"
                ]
            ]));
        }

        $db = Database::connect();
        $sqlUpdate = $db->prepare("UPDATE tb_racc SET ACC_LIMIT_MARGIN = ?, ACC_METADATA = JSON_SET(ACC_METADATA, '$.rate_limit_margin', ?) WHERE ID_ACC = ?");
        $sqlUpdate->bind_param("ddi", $limitMarginUSD, $rate, $accountId);
        if(!$sqlUpdate->execute()) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Gagal memperbarui tipe akun",
                    'icon'  => "error"
                ]
            ]));
        }
    }

    public function markAsUpdated($data, $user) {
        $progressAccount = $this->checkProgressAccount(md5(md5($user['MBR_ID'])));
        if($progressAccount && $progressAccount['ACC_NEEDS_UPGRADE']) {
            $db = Database::connect();
            $sqlUpdate = $db->prepare("UPDATE tb_racc SET ACC_METADATA = JSON_SET(ACC_METADATA, '$.is_updated', true) WHERE ID_ACC = '{$progressAccount['ID_ACC']}'");
            if(!$sqlUpdate->execute()) {
                throw new Exception("Failed to mark account {$progressAccount['ACC_LOGIN']} as updated");
            }
        }
    }

    public function updateAccountType($data, $user) {
        if(empty($data['product'])) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Jenis akun diperlukan",
                    'icon'  => "error"
                ]
            ]));
        }

        /** Check Progress Account */
        $progressAccount = Account::getProgressRealAccount($user['userid']);
        if(empty($progressAccount)) {
            exit(json_encode([
                'success'   => true,
                'alert'     => [
                    'title' => "Berhasil",
                    'text'  => "Available Product",
                    'icon'  => "success"
                ]
            ]));
        }
        
        /** Check Status */
        $this->isAllowToEdit($progressAccount['ACC_STS']);

        /** Account Suffix */
        $suffix = $data['product'];

        /** tidak bisa update produk jika regol lanjutan */
        if($suffix != $progressAccount['RTYPE_SUFFIX']) {
            if($progressAccount['ACC_NEEDS_UPGRADE']) {
                exit(json_encode([
                    'success'   => false,
                    'alert'     => [
                        'title' => "Gagal",
                        'text'  => "Tidak bisa memperbarui jenis akun",
                        'icon'  => "error"
                    ]
                ]));
            }
        }

        $raccType = Account::checkAccountSuffix($suffix);
        if(empty($raccType)) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Jenis Akun Tidak Ditemukan",
                    'icon'  => "error"
                ]
            ]));
        }
        
        /** Cek apakah produk compatibel dengan akunnya */
        $accountAvailable = Account::getAvailableProduct_list($user['userid']);
        if(!in_array($suffix, array_column($accountAvailable, "RTYPE_SUFFIX"))) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Jenis akun tidak valid",
                    'icon'  => "error"
                ]
            ]));
        }

        if(!empty($user['MBR_SUFFIX_EXCLUDE'])) {
            $explode = explode(",", $user['MBR_SUFFIX_EXCLUDE']);
            if(in_array($suffix, $explode)) {
                exit(json_encode([
                    'success'   => false,
                    'alert'     => [
                        'title' => "Gagal",
                        'text'  => "Jenis akun tidak valid (2)",
                        'icon'  => "error"
                    ]
                ]));
            }
        }
        
        /** Update Account Margin Limit */
        if($progressAccount['ACC_CDD'] == Regol::$cddTypeSederhana) {
            $this->updateAccountMarginLimit($progressAccount['ID_ACC'], $raccType, $user);
        }

        /** Update Account Type */
        $update = Database::update("tb_racc", ['ACC_TYPE' => $raccType['ID_RTYPE']], ['ID_ACC' => $progressAccount['ID_ACC']]);
        if(!$update) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Gagal memperbarui jenis akun",
                    'icon'  => "error"
                ]
            ]));
        }

        exit(json_encode([
            'success'   => true,
            'alert'     => [
                'title' => "Success",
                'text'  => "berhasil",
                'icon'  => "success"
            ]
        ]));
    }

    public function accountType($data, $user) {
        $this->checkCsrfToken($data);

        $isHaveCddsAccount = Account::isHaveCddsAccount($user['MBR_ID']);
        $isHaveCddAccount = Account::isHaveCddAccount($user['MBR_ID']);
        /** Jika sudah memiliki akun dengan cdd tipe sederhana, tidak bisa membuka akun lain sampai melakukan upgrade ke CDD Standar */
        if($isHaveCddsAccount) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Anda sudah memiliki akun dengan tipe CDD Sederhana, untuk membuka akun lain, silahkan melakukan upgrade ke CDD Standar",
                    'icon'  => "error"
                ]
            ]));
        }

        if(empty($data['cdd-type'])) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "CDD Tipe diperlukan",
                    'icon'  => "error"
                ]
            ]));
        }

        if(!in_array($data['cdd-type'], Regol::cddTypeArray())) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Mohon pilih tipe CDD Standar / Sederhana",
                    'icon'  => "error"
                ]
            ]));
        }

        if($isHaveCddAccount && $data['cdd-type'] == Regol::$cddTypeSederhana) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Anda tidak bisa membuka akun dengan tipe CDD Sederhana",
                    'icon'  => "error"
                ]
            ]));
        }
        
        /** Jika sudah punya akun, dan bertipe cdd standart, tidak bisa buat akun dengan cdd tipe sederhana */
        $myAccounts = Account::myAccount($user['MBR_ID']);
        if(count($myAccounts) > 0) {
            if($data['cdd-type'] == Regol::$cddTypeSederhana) {
                exit(json_encode([
                    'success'   => false,
                    'alert'     => [
                        'title' => "Gagal",
                        'text'  => "Tidak bisa membuka akun dengan tipe CDD Sederhana",
                        'icon'  => "error"
                    ]
                ]));
            }
        }

        if(empty($data['account-type'])) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Jenis akun diperlukan",
                    'icon'  => "error"
                ]
            ]));
        }

        $option = empty($data['option'])? false : true;

        /** Account Suffix */
        $suffix = $data['account-type'];
        $raccType = Account::checkAccountSuffix($suffix);
        if(empty($raccType)) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Jenis Akun Tidak Ditemukan",
                    'icon'  => "error"
                ]
            ]));
        }
        
        /** Cek apakah produk compatibel dengan akunnya */
        $accountAvailable = Account::getAvailableProduct_list(md5(md5($user['MBR_ID'])));
        if(!in_array($suffix, array_column($accountAvailable, "RTYPE_SUFFIX"))) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Jenis akun tidak valid",
                    'icon'  => "error"
                ]
            ]));
        }

        if(!empty($user['MBR_SUFFIX_EXCLUDE'])) {
            $explode = explode(",", $user['MBR_SUFFIX_EXCLUDE']);
            if(in_array($suffix, $explode)) {
                exit(json_encode([
                    'success'   => false,
                    'alert'     => [
                        'title' => "Gagal",
                        'text'  => "Jenis akun tidak valid (2)",
                        'icon'  => "error"
                    ]
                ]));
            }
        }

        /** Check create akun multi */
        if(strtoupper($raccType['RTYPE_TYPE_AS']) == "MULTILATERAL") {
            if($user['MBR_ACCMULTI'] != -1) {
                exit(json_encode([
                    'success'   => false,
                    'alert'     => [
                        'title' => "Gagal",
                        'text'  => "Mohon hubungi CS untuk membuat akun Multilateral",
                        'icon'  => "error"
                    ]
                ]));
            }
        }

        /** Check max account */
        $realAcc = Account::all($user['MBR_ID']);
        $microAcc = [];
        foreach($realAcc as $acc) {
            if(strtolower($acc['RTYPE_TYPE']) == "micro") {
                $microAcc[] = $acc;
            }
        }

        if(count($realAcc) >= $user['MBR_ACCMAX']) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Sudah mencapai limit pembuatan real account",
                    'icon'  => "error"
                ]
            ]));
        }

        if(strtoupper($raccType['RTYPE_TYPE']) == "MICRO") {
            if(count($microAcc) >= $user['MBR_ACCMAX_MICRO']) {
                exit(json_encode([
                    'success'   => false,
                    'alert'     => [
                        'title' => "Gagal",
                        'text'  => "Sudah mencapai limit pembuatan real account (micro)",
                        'icon'  => "error"
                    ]
                ]));
            }
        }

        /** Get Progress Real Account */
        $progressAccount = Account::getProgressRealAccount($user['userid']);
        if(empty($progressAccount)) {
            /** Jika sebelumnya sudah punya akun, dapat diduplicate */
            if(!empty(Account::getLastAccount($user['userid']))) {
                $duplicate = Account::duplicateLastAccount($user['userid']);
                if(empty($duplicate) || !is_array($duplicate)) {
                    exit(json_encode([
                        'success'   => false,
                        'alert'     => [
                            'title' => "Gagal",
                            'text'  => "Status salinan tidak valid",
                            'icon'  => "error"
                        ]
                    ]));
                }

            }else {
                /** Insert row baru, Jika belum punya akun sama sekali / baru pertama create akun */
                $insert = Database::insert("tb_racc", [
                    'ACC_MBR'   => $user['MBR_ID'],
                    'ACC_TYPE'  => $raccType['ID_RTYPE'],
                    'ACC_DERE'  => 1,
                    'ACC_LOGIN' => 0,
                    'ACC_STS'   => 0
                ]);

                if(empty($insert)) {
                    exit(json_encode([
                        'success'   => false,
                        'alert'     => [
                            'title' => "Gagal",
                            'text'  => "Gagal membuat akun",
                            'icon'  => "error"
                        ]
                    ]));
                }
            }
        }
        
        /** Get Ulang Progress real account */
        $progressAccount = Account::getProgressRealAccount($user['userid']);

        /** tidak bisa update produk jika regol lanjutan */
        if($suffix != $progressAccount['RTYPE_SUFFIX']) {
            if($progressAccount['ACC_NEEDS_UPGRADE']) {
                exit(json_encode([
                    'success'   => false,
                    'alert'     => [
                        'title' => "Gagal",
                        'text'  => "Tidak bisa memperbarui jenis akun",
                        'icon'  => "error"
                    ]
                ]));
            }
        }
        

        /** Check Status */
        $this->isAllowToEdit(status: $progressAccount['ACC_STS']);

        /** update limit margin */
        if($data['cdd-type'] == Regol::$cddTypeSederhana) {
            $this->updateAccountMarginLimit($progressAccount['ID_ACC'], $raccType, $user);
        }

        /** Update Type */
        // if($progressAccount['ACC_TYPE'] != $raccType['ID_RTYPE']) {
            $updateData = [
                'ACC_SKIP' => $option,
                'ACC_TYPE' => $raccType['ID_RTYPE'],
                'ACC_CDD' => $data['cdd-type'],
                'ACC_LAST_STEP' => "profile-perusahaan",
            ];

            $update = Database::update("tb_racc", $updateData, ['ID_ACC' => $progressAccount['ID_ACC']]);
            if(!$update) {
                exit(json_encode([
                    'success'   => false,
                    'alert'     => [
                        'title' => "Gagal",
                        'text'  => "Perbarui Jenis Akun Gagal",
                        'icon'  => "error"
                    ]
                ]));
            }
        // }

        Logger::client_log([
            'mbrid' => $user['MBR_ID'],
            'module' => "create-account",
            'message' => "Progress Real Account (Account Type)",
            'data'  => ($data)
        ]);

        $this->markAsUpdated($data, $user);
        exit(json_encode([
            'success'   => true,
            'redirect'  => ($option)? "/account/create?page=kelengkapan-formulir" : "/account/create?page=profile-perusahaan",
            'alert'     => [
                'title' => "Berhasil",
                'text'  => "Pilih Jenis Akun Sukses",
                'icon'  => "success"
            ]
        ]));
    }

    public function profilePerusahaan($data, $user) {
        $this->checkCsrfToken($data);
        
        $agree = $data['aggree'] ?? "Tidak";
        if(strtolower($agree) != "ya") {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Anda harus menyetujui untuk melanjutkan ke tahap berikutnya",
                    'icon'  => "error"
                ]
            ]));  
        }

        /** Get Account */
        $progressAccount = $this->checkProgressAccount($user['userid']);
        
        /** Check Status */
        $this->isAllowToEdit( $progressAccount['ACC_STS']);

        /** Update */
        $updateData = [
            'ACC_F_PROFILE' => 1,
            'ACC_F_PROFILE_IP' => Helper::get_ip_address(),
            'ACC_F_PROFILE_PERYT' => "Yes",
            'ACC_F_PROFILE_DATE' => date("Y-m-d H:i:s"),
            'ACC_LAST_STEP' => "pernyataan-simulasi",
        ];

        $update = Database::update("tb_racc", $updateData, ['ID_ACC' => $progressAccount['ID_ACC']]);
        if(!$update) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Perbarui Jenis Akun Gagal",
                    'icon'  => "error"
                ]
            ]));
        }

        Logger::client_log([
            'mbrid' => $user['MBR_ID'],
            'module' => "create-account",
            'ref' => $progressAccount['ID_ACC'],
            'message' => "Progress Real Account (Profile Perusahaan)",
            'data'  => json_encode($data)
        ]);

        $this->markAsUpdated($data, $user);
        exit(json_encode([
            'success'   => true,
            'redirect'  => "/account/create?page=pernyataan-simulasi",
            'alert'     => [
                'title' => "Berhasil",
                'text'  => "Successfull",
                'icon'  => "success"
            ]
        ]));
    }

    public function pernyataanSimulasi($data, $user) {
        $this->checkCsrfToken($data);
        $progressAccount = $this->checkProgressAccount(md5(md5($user['MBR_ID'])));
        
        /** Check Status */
        $this->isAllowToEdit(status: $progressAccount['ACC_STS']);

        $required = [
            "smls_namleng"  => "Nama Lengkap", 
            "smls_tmptlhr" => "Tempat Lahir", 
            "smls_tgllhr" => "Tanggal Lahir", 
            "smls_almtrmh" => "Alamat Rumah", 
            "smls_almtrmh_prov" => "Provinsi", 
            "smls_almtrmh_kabkot" => "Kabupaten", 
            "smls_almtrmh_kcmtn" => "Kecamatan", 
            "smls_almtrmh_desa" => "Kelurahan", 
            "smls_kodepos" => "Kode Pos", 
            // "smls_almtrmh_rw" => "RW",
            // "smls_almtrmh_rt" => "RT",
            "aggree" => "Persetujuan"
        ];

        foreach($required as $r => $text) {
            if(empty($data[ $r ])) {
                exit(json_encode([
                    'success'   => false,
                    'alert'     => [
                        'title' => "Gagal",
                        'text'  => "{$text} diperlukan",
                        'icon'  => "error"
                    ]
                ]));
            }
        }

        /** Check Peresetujuan */
        $agree = $data['aggree'] ?? "Tidak";
        if(strtolower($agree) != "ya") {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Anda harus menyetujui untuk melanjutkan ke tahap berikutnya",
                    'icon'  => "error"
                ]
            ]));  
        }

        $demoAccount = Account::getDemoAccount(md5(md5($user['MBR_ID'])));
        if(empty($demoAccount)) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Akun demo tidak valid",
                    'icon'  => "error"
                ]
            ]));  
        }

        /** check apakah sudah pernah transaksi dengan akun demo */
        $sqlGet = $this->db->query("SELECT deal_id FROM mt4_trades WHERE `login` = " . $demoAccount['ACC_LOGIN']);
        if($sqlGet->num_rows == 0) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Anda belum melakukan transaksi dengan akun demo",
                    'icon'  => "error"
                ]
            ]));  
        }
        
        /** Upload File */
        // if(empty($progressAccount['ACC_F_SIMULASI_IMG'])) {
        //     /** Check file */
        //     if(empty($_FILES['smls_demofile']) || $_FILES['smls_demofile']['error'] != 0) {
        //         exit(json_encode([
        //             'success'   => false,
        //             'alert'     => [
        //                 'title' => "Gagal",
        //                 'text'  => "Mohon upload file demo account",
        //                 'icon'  => "error"
        //             ]
        //         ]));
        //     }

        //     $uploadFile = FileUploadFactory::aws()->upload_single($_FILES['smls_demofile'], "demo_img_");
        //     if(!is_array($uploadFile) || !array_key_exists("filename", $uploadFile)) {
        //         exit(json_encode([
        //             'success'   => false,
        //             'alert'     => [
        //                 'title' => "Gagal",
        //                 'text'  => "Gagal mengunggah file, {$uploadFile}",
        //                 'icon'  => "error"
        //             ]
        //         ]));
        //     }

        //     /** Update Image */
        //     $updateImage = Database::update("tb_racc", ['ACC_F_SIMULASI_IMG' => $uploadFile['filename']], ['ID_ACC' => $progressAccount['ID_ACC']]);
        //     if($updateImage !== TRUE) {
        //         exit(json_encode([
        //             'success'   => false,
        //             'alert'     => [
        //                 'title' => "Gagal",
        //                 'text'  => "Gagal menyimpan file, {$updateImage}",
        //                 'icon'  => "error"
        //             ]
        //         ]));
        //     }
        // }

        /** Check nama */
        if(preg_match('/\d+/i', $data['smls_namleng'])){
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Nama lengkap tidak bisa berisi angka",
                    'icon'  => "error"
                ]
            ])); 
        }

        /** Check Alamat */
        $province = base64_decode($data['smls_almtrmh_prov']);
        $regency = $data['smls_almtrmh_kabkot'];
        $district = $data['smls_almtrmh_kcmtn'];
        $village = $data['smls_almtrmh_desa'];
        $postalCode = $data['smls_kodepos'];
        $sqlCheckAddress = $this->db->query("SELECT ID_KDP FROM tb_kodepos WHERE UPPER(KDP_KELURAHAN) = '{$village}' AND KDP_KECAMATAN = '{$district}' AND KDP_KABKO = '{$regency}' AND KDP_PROV = '{$province}' AND KDP_POS = $postalCode LIMIT 1");
        if($sqlCheckAddress->num_rows != 1) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Kode pos tidak ditemukan / salah",
                    'icon'  => "error"
                ]
            ]));
        }

        $updateData = [
            'ACC_F_SIMULASI' => 1,
            'ACC_F_SIMULASI_IP' => Helper::get_ip_address(),
            'ACC_F_SIMULASI_PERYT' => "Ya",
            'ACC_F_SIMULASI_DATE' => date("Y-m-d H:i:s"),
            'ACC_PROVINCE' => $province,
            'ACC_REGENCY' => $regency,
            'ACC_DISTRICT' => $district,
            'ACC_VILLAGE' => $village,
            'ACC_ZIPCODE' => $postalCode,
            'ACC_RW' => $data['smls_almtrmh_rw'],
            'ACC_RT' => $data['smls_almtrmh_rt'],
            'ACC_ADDRESS' => $data['smls_almtrmh'],
            'ACC_FULLNAME' => $data['smls_namleng'],
            'ACC_TEMPAT_LAHIR' => $data['smls_tmptlhr'],
            'ACC_TANGGAL_LAHIR' => date("Y-m-d", strtotime($data['smls_tgllhr'])),
            'ACC_DEMO' => $demoAccount['ACC_LOGIN'],
            'ACC_LAST_STEP' => "pernyataan-pengalaman",
        ];

        /** Check Identitas berdasarkan akun, jika sebelumnya sudah punya akun maka skip */
        $isHaveFirstAccount = Account::myFirstAccount($user['MBR_ID']);
        if(!$isHaveFirstAccount) {
            if(empty($data['smls_tipeidt'])) {
                exit(json_encode([
                    'success'   => false,
                    'alert'     => [
                        'title' => "Gagal",
                        'text'  => "Tipe identitas diperlukan",
                        'icon'  => "error"
                    ]
                ]));
            }

            if(empty($data['smls_nomidt'])) {
                exit(json_encode([
                    'success'   => false,
                    'alert'     => [
                        'title' => "Gagal",
                        'text'  => "Nomor Identitas diperlukan",
                        'icon'  => "error"
                    ]
                ]));
            }

            /** Check Tipe identitas */
            if(!in_array($data['smls_tipeidt'], Regol::$typeIdentitas)) {
                exit(json_encode([
                    'success'   => false,
                    'alert'     => [
                        'title' => "Gagal",
                        'text'  => "Tipe identitas tidak didukung",
                        'icon'  => "error"
                    ]
                ]));
            }

                
            /** Check No Identitas */
            switch(strtoupper($data['smls_tipeidt'])) {
                case "KTP":
                    $noKtp = $data['smls_nomidt'];
                    if(is_numeric($noKtp) === FALSE) {
                        exit(json_encode([
                            'success'   => false,
                            'alert'     => [
                                'title' => "Gagal",
                                'text'  => "Nomor KTP tidak valid",
                                'icon'  => "error"
                            ]
                        ]));
                    }
    
                    if(strlen($noKtp) < 16) {
                        exit(json_encode([
                            'success'   => false,
                            'alert'     => [
                                'title' => "Gagal",
                                'text'  => "Nomor KTP harus 16 digit atau lebih",
                                'icon'  => "error"
                            ]
                        ]));
                    }
                    break;
    
                case "PASSPORT":
                    $noPsprt = $data['smls_nomidt'];
                    if(!preg_match("/^[0-9A-Za-zÀ-ÖØ-öø-ÿ .,'’\-]{7,9}$/i", $noPsprt)) {
                        exit(json_encode([
                            'success'   => false,
                            'alert'     => [
                                'title' => "Gagal",
                                'text'  => "Nomor PASSPORT tidak valid",
                                'icon'  => "error"
                            ]
                        ]));
                    }
                    break;
    
                case "KITAS":
                    if(strlen($data['number']) < 11) {
                        exit(json_encode([
                            'success'   => false,
                            'alert'     => [
                                'title' => "Gagal",
                                'text'  => "Nomor KITAS harus 11 digit atau lebih",
                                'icon'  => "error"
                            ]
                        ]));
                    }
                    break;
            }
    
            /** Check No Identitas apakah sudah digunakan user lain */
            $noIdentitas = $data['smls_nomidt'];
            $sqlCheckIdentitas = $this->db->query("
                SELECT 
                    ID_ACC 
                FROM tb_racc
                JOIN tb_member ON (ACC_MBR = MBR_ID)
                WHERE ACC_NO_IDT = '{$noIdentitas}' 
                AND ACC_MBR != '".$user['MBR_ID']."' 
                AND (MBR_EMAIL NOT LIKE '%deleted%' OR MBR_STS != 1)
                LIMIT 1
            ");
    
            if($sqlCheckIdentitas->num_rows != 0) {
                exit(json_encode([
                    'success'   => false,
                    'alert'     => [
                        'title' => "Gagal",
                        'text'  => "Nomor ".strtoupper($data['smls_tipeidt'])." telah terdaftar/digunakan",
                        'icon'  => "error"
                    ]
                ]));
            }

            /** Add identity column to update data */
            $updateData['ACC_TYPE_IDT'] = $data['smls_tipeidt'];
            $updateData['ACC_NO_IDT'] = strtoupper($data['smls_nomidt']);
        }

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        mysqli_begin_transaction($this->db);

        /** Update tb_racc */
        $updateRacc = Database::update("tb_racc", $updateData, ['ID_ACC' => $progressAccount['ID_ACC']]);
        if($updateRacc !== TRUE) {
            $this->db->rollback();
            throw new Exception("Gagal memperbarui informasi real account");
        }

        if(!$isHaveFirstAccount) {
            /** update personal informasi jika diperlukan */
            $updateMemberData = [
                'MBR_PROVINCE' => $province,
                'MBR_CITY' => $regency,
                'MBR_DISTRICT' => $district,
                'MBR_VILLAGES' => $village,
                'MBR_ZIP' => $postalCode,
                'MBR_ADDRESS' => $data['smls_almtrmh'],
                'MBR_TMPTLAHIR' => $data['smls_tmptlhr'],
                'MBR_TGLLAHIR' => date("Y-m-d", strtotime($data['smls_tgllhr'])),
            ];

            $updateMember = Database::update("tb_member", $updateMemberData, ['MBR_ID' => $user['MBR_ID']]);
            if($updateMember !== TRUE) {
                $this->db->rollback();
                throw new Exception("Gagal memperbarui informasi pribadi pengguna");
            }
        }

        $this->db->commit();
        Logger::client_log([
            'mbrid' => $user['MBR_ID'],
            'module' => "create-account",
            'message' => "Progress Real Account (Pernyataan Simulasi)",
            'data'  => ($data)
        ]);

        $this->markAsUpdated($data, $user);
        exit(json_encode([
            'success'   => true,
            'redirect'  => "/account/create?page=pernyataan-pengalaman",
            'alert'     => [
                'title' => "Berhasil",
                'text'  => "Berhasil disimpan",
                'icon'  => "success"
            ]
        ]));
    }

    public function pernyataanPengalaman($data, $user) {
        $this->checkCsrfToken($data);
        $progressAccount = $this->checkProgressAccount(md5(md5($user['MBR_ID'])));

        /** Check Status */
        $this->isAllowToEdit(status: $progressAccount['ACC_STS']);

        if(empty($data['pengalaman'])) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Pernyataan pengalaman diperlukan",
                    'icon'  => "error"
                ]
            ]));
        }

        /** Check Peresetujuan */
        $agree = $data['aggree'] ?? "Tidak";
        if(strtolower($agree) != "ya") {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Anda harus menyetujui untuk melanjutkan ke tahap berikutnya",
                    'icon'  => "error"
                ]
            ]));  
        }

        // if(strtolower($data['pengalaman']) == "ya") {
        //     if(empty($data['perusahaan'])) {
        //         exit(json_encode([
        //             'success'   => false,
        //             'alert'     => [
        //                 'title' => "Gagal",
        //                 'text'  => "Nama Perusahaan Berjangka diperlukan",
        //                 'icon'  => "error"
        //             ]
        //         ])); 
        //     }
        // }

        // if(strtolower($data['pengalaman']) != "ya") {
        //     exit(json_encode([
        //         'success'   => false,
        //         'alert'     => [
        //             'title' => "Gagal",
        //             'text'  => "Anda harus berpengalaman dalam bidang investasi",
        //             'icon'  => "error"
        //         ]
        //     ])); 
        // }

        if(empty($data['perusahaan'])) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Nama Perusahaan Berjangka diperlukan",
                    'icon'  => "error"
                ]
            ])); 
        }

        $update = Database::update("tb_racc", [
            'ACC_F_PENGLAMAN'       => 1,
            'ACC_F_PENGLAMAN_IP'    => Helper::get_ip_address(),
            'ACC_F_PENGLAMAN_PERYT' => "Ya",
            'ACC_F_PENGLAMAN_PERYT_YA' => $data['pengalaman'],
            'ACC_F_PENGLAMAN_PERSH' => $data['perusahaan'] ?? NULL,
            'ACC_F_PENGLAMAN_DATE'  => date("Y-m-d H:i:s"),
            'ACC_LAST_STEP'         => "pernyataan-pengalaman",
        ], [
            'ID_ACC' => $progressAccount['ID_ACC']
        ]);

        if($update !== TRUE) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => $update ?? "Gagal Memperbarui kemajuan Akun Real",
                    'icon'  => "error"
                ]
            ])); 
        }

        Logger::client_log([
            'mbrid' => $user['MBR_ID'],
            'module' => "create-account",
            'ref' => $progressAccount['ID_ACC'],
            'message' => "Progress Real Account (Pernyataan Pengalaman Transaksi Perdagangan Berjangka)",
            'data'  => ($data)
        ]);

        $this->markAsUpdated($data, $user);
        exit(json_encode([
            'success'   => true,
            'redirect'  => "/account/create?page=pernyataan-pengungkapan-1",
            'alert'     => [
                'title' => "Berhasil",
                'text'  => "Berhasil disimpan",
                'icon'  => "success"
            ]
        ])); 
    }

    public function pernyataanPengungkapan_1($data, $user) {
        $this->checkCsrfToken($data);
        $progressAccount = $this->checkProgressAccount(md5(md5($user['MBR_ID'])));  
        
        /** Check Status */
        $this->isAllowToEdit(status: $progressAccount['ACC_STS']);

        /** Check Peresetujuan */
        $agree = $data['aggree'] ?? "Tidak";
        if(strtolower($agree) != "ya") {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Anda harus menyetujui untuk melanjutkan ke tahap berikutnya",
                    'icon'  => "error"
                ]
            ]));  
        }

        
        
        $update = Database::update("tb_racc", [
            'ACC_F_DISC'        => 1,
            'ACC_F_DISC_IP'     => Helper::get_ip_address(),
            'ACC_F_DISC_PERYT'  => "Ya",
            'ACC_F_DISC_DATE'   => date("Y-m-d H:i:s"),
            'ACC_LAST_STEP'     => "pernyataan-pengungkapan-1",
        ], [
            'ID_ACC' => $progressAccount['ID_ACC']
        ]);

        if($update !== TRUE) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => $update ?? "Gagal Memperbarui kemajuan Akun Real",
                    'icon'  => "error"
                ]
            ])); 
        }

        Logger::client_log([
            'mbrid' => $user['MBR_ID'],
            'module' => "create-account",
            'ref' => $progressAccount['ID_ACC'],
            'message' => "Progress Real Account (Pernyataan Pengungkapan 1)",
            'data'  => ($data)
        ]);

        $this->markAsUpdated($data, $user);
        exit(json_encode([
            'success'   => true,
            'redirect'  => "/account/create?page=aplikasi-pembukaan-rekening",
            'alert'     => [
                'title' => "Berhasil",
                'text'  => "Berhasil disimpan",
                'icon'  => "success"
            ]
        ])); 
    }

    public function pernyataanPengungkapan_2($data, $user) {
        $this->checkCsrfToken($data);
        $progressAccount = $this->checkProgressAccount(md5(md5($user['MBR_ID'])));  
        
        /** Check Status */
        $this->isAllowToEdit(status: $progressAccount['ACC_STS']);

        /** Check Peresetujuan */
        $agree = $data['aggree'] ?? "Tidak";
        if(strtolower($agree) != "ya") {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Anda harus menyetujui untuk melanjutkan ke tahap berikutnya",
                    'icon'  => "error"
                ]
            ]));  
        }

        $update = Database::update("tb_racc", [
            'ACC_F_DISC2'        => 1,
            'ACC_F_DISC_IP2'     => Helper::get_ip_address(),
            'ACC_F_DISC_PERYT2'  => "Ya",
            'ACC_F_DISC_DATE2'   => date("Y-m-d H:i:s"),
            'ACC_LAST_STEP'     => "pernyataan-pengungkapan-2",
        ], [
            'ID_ACC' => $progressAccount['ID_ACC']
        ]);

        if($update !== TRUE) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => $update ?? "Gagal Memperbarui kemajuan Akun real",
                    'icon'  => "error"
                ]
            ])); 
        }

        Logger::client_log([
            'mbrid' => $user['MBR_ID'],
            'module' => "create-account",
            'ref' => $progressAccount['ID_ACC'],
            'message' => "Progress Real Account (Pernyataan Pengungkapan 2)",
            'data'  => ($data)
        ]);

        $this->markAsUpdated($data, $user);
        exit(json_encode([
            'success'   => true,
            'redirect'  => "/account/create?page=formulir-dokumen-resiko",
            'alert'     => [
                'title' => "Berhasil",
                'text'  => "Berhasil disimpan",
                'icon'  => "success"
            ]
        ])); 
    }

    public function pernyataanPengungkapan_3($data, $user) {
        $this->checkCsrfToken($data);
        $progressAccount = $this->checkProgressAccount(md5(md5($user['MBR_ID'])));  
        
        /** Check Status */
        $this->isAllowToEdit(status: $progressAccount['ACC_STS']);

        /** Check Peresetujuan */
        $agree = $data['aggree'] ?? "Tidak";
        if(strtolower($agree) != "ya") {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Anda harus menyetujui untuk melanjutkan ke tahap berikutnya",
                    'icon'  => "error"
                ]
            ]));  
        }
        
        $update = Database::update("tb_racc", [
            'ACC_F_DISC3'        => 1,
            'ACC_F_DISC_IP3'     => Helper::get_ip_address(),
            'ACC_F_DISC_PERYT3'  => "Ya",
            'ACC_F_DISC_DATE3'   => date("Y-m-d H:i:s"),
            'ACC_LAST_STEP'     => "pernyataan-pengungkapan-3",
        ], [
            'ID_ACC' => $progressAccount['ID_ACC']
        ]);

        if($update !== TRUE) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => $update ?? "Gagal Memperbarui kemajuan Akun real",
                    'icon'  => "error"
                ]
            ])); 
        }

        Logger::client_log([
            'mbrid' => $user['MBR_ID'],
            'module' => "create-account",
            'ref' => $progressAccount['ID_ACC'],
            'message' => "Progress Real Account (Pernyataan Pengungkapan 3)",
            'data'  => ($data)
        ]);

        $this->markAsUpdated($data, $user);
        exit(json_encode([
            'success'   => true,
            'redirect'  => "/account/create?page=perjanjian-pemberian-amanat",
            'alert'     => [
                'title' => "Berhasil",
                'text'  => "Berhasil disimpan",
                'icon'  => "success"
            ]
        ])); 
    }

    public function pernyataanPengungkapan_4($data, $user) {
        $this->checkCsrfToken($data);
        $progressAccount = $this->checkProgressAccount(md5(md5($user['MBR_ID'])));  
        
        /** Check Status */
        $this->isAllowToEdit(status: $progressAccount['ACC_STS']);

        /** Check Peresetujuan */
        $agree = $data['aggree'] ?? "Tidak";
        if(strtolower($agree) != "ya") {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Anda harus menyetujui untuk melanjutkan ke tahap berikutnya",
                    'icon'  => "error"
                ]
            ]));  
        }

        
        
        $update = Database::update("tb_racc", [
            'ACC_F_DISC4'        => 1,
            'ACC_F_DISC_IP4'     => Helper::get_ip_address(),
            'ACC_F_DISC_PERYT4'  => "Ya",
            'ACC_F_DISC_DATE4'   => date("Y-m-d H:i:s"),
            'ACC_LAST_STEP'     => "pernyataan-pengungkapan-4",
        ], [
            'ID_ACC' => $progressAccount['ID_ACC']
        ]);

        if($update !== TRUE) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => $update ?? "Gagal Memperbarui kemajuan Akun real",
                    'icon'  => "error"
                ]
            ])); 
        }

        Logger::client_log([
            'mbrid' => $user['MBR_ID'],
            'module' => "create-account",
            'ref' => $progressAccount['ID_ACC'],
            'message' => "Progress Real Account (Pernyataan Pengungkapan 4)",
            'data'  => ($data)
        ]);

        exit(json_encode([
            'success'   => true,
            // 'redirect'  => "/account/create?page=verifikasi-identitas",
            'redirect'  => "/account/create?page=kelengkapan-formulir",
            'alert'     => [
                'title' => "Berhasil",
                'text'  => "Berhasil disimpan",
                'icon'  => "success"
            ]
        ])); 
    }
    
    public function formulirSID($data, $user) {
        $this->checkCsrfToken($data);
        $progressAccount = $this->checkProgressAccount(md5(md5($user['MBR_ID'])));  
        
        /** Check Status */
        $this->isAllowToEdit(status: $progressAccount['ACC_STS']);

        /** Check Peresetujuan */
        $agree = $data['aggree'] ?? "Tidak";
        if(strtolower($agree) != "ya") {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Anda harus menyetujui untuk melanjutkan ke tahap berikutnya",
                    'icon'  => "error"
                ]
            ]));  
        }

        if(!in_array($data['acc_ket'], [1,2])) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Keterangan SID tidak valid",
                    'icon'  => "error"
                ]
            ]));  
        }

        $STORED_DATA = [
            'ACC_F_SID'        => 1,
            'ACC_F_SID_IP'     => Helper::get_ip_address(),
            'ACC_F_SID_PERYT'  => "Ya",
            'ACC_F_SID_DATE'   => date("Y-m-d H:i:s"),
            'ACC_LAST_STEP'    => "draft-sid",
        ];

        if($data['acc_ket'] == 1) {
            if(empty($data['sid_nmbr'])) {
                exit(json_encode([
                    'success'   => false,
                    'alert'     => [
                        'title' => "Gagal",
                        'text'  => "Nomor SID diperlukan",
                        'icon'  => "error"
                    ]
                ]));  
            }

            /** validasi sid */
            if(!Regol::SidValidation($data['sid_nmbr'], $progressAccount['ACC_TANGGAL_LAHIR'])) {
                exit(json_encode([
                    'success'   => false,
                    'alert'     => [
                        'title' => "Gagal",
                        'text'  => "Nomor SID tidak valid",
                        'icon'  => "error"
                    ]
                ]));  
            }

            $STORED_DATA["ACC_F_SID_KET"]   = $data["acc_ket"];
            $STORED_DATA["ACC_F_SID_NOMER"] = str_replace(" - ", "", $data["sid_nmbr"]);
        }

        if($data['acc_ket'] == 2) {
            $STORED_DATA["ACC_F_SID_KET"]   = $data["acc_ket"];
        }

        $update = Database::update("tb_racc", $STORED_DATA, ['ID_ACC' => $progressAccount['ID_ACC']]);
        if($update !== TRUE) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => $update ?? "Gagal Memperbarui kemajuan Akun real",
                    'icon'  => "error"
                ]
            ])); 
        }

        Logger::client_log([
            'mbrid' => $user['MBR_ID'],
            'module' => "create-account",
            'ref' => $progressAccount['ID_ACC'],
            'message' => "Progress Real Account (Draft SID)",
            'data'  => ($data)
        ]);

        $this->markAsUpdated($data, $user);
        exit(json_encode([
            'success'   => true,
            'redirect'  => "/account/create?page=aplikasi-pembukaan-rekening",
            'alert'     => [
                'title' => "Berhasil",
                'text'  => "Berhasil disimpan",
                'icon'  => "success"
            ]
        ])); 
    }

    public function aplikasiPembukaanRekening($data, $user) {
        $this->checkCsrfToken($data);
        $progressAccount = $this->checkProgressAccount(md5(md5($user['MBR_ID'])));  
    
        /** Check Status */
        $this->isAllowToEdit( $progressAccount['ACC_STS']);

        /** Check Peresetujuan */
        $agree = $data['aggree'] ?? "Tidak";
        if(strtolower($agree) != "ya") {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Anda harus menyetujui untuk melanjutkan ke tahap berikutnya",
                    'icon'  => "error"
                ]
            ]));  
        }

        /** Validasi APP Data Pribadi */
        $this->aplikasiPembukaanRekening_DataPribadi($data, $user, $progressAccount);

        /** Validasi APP Pihak Darurat */
        $this->aplikasiPembukaanRekening_PihakDarurat($data, $user, $progressAccount);

        /** Validasi APP Pekerjaan */
        $this->aplikasiPembukaanRekening_Pekerjaan($data, $user, $progressAccount);
        
        /** Validasi APP Bank Nasabah */
        $this->aplikasiPembukaanRekening_RekeningBank($data, $user, $progressAccount);
        
        /** Validasi APP Daftar Kekayaan */
        $this->aplikasiPembukaanRekening_DaftarKekayaan($data, $user, $progressAccount);

        /** Validasi APP Dokumen Pendukung */
        $this->aplikasiPembukaanRekening_DokumenPendukung($data, $user, $progressAccount);
        
        /** Upload Selfie Photo */
        // $this->uploadSelfiePhoto($data, $user);
        $this->uploadSelfiePhoto_simple($data, $user);

        /** Upload KTP Photo */
        // $this->uploadKtpPhoto($data, $user);
        $this->uploadKtpPhoto_simple($data, $user);

        /** Validasi Bank */
        $userBanks = User::myBank($progressAccount['ACC_MBR']);
        if(count($userBanks) <= 0) {
            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => "Mohon menambahkan setidaknya 1 bank",
                    'icon'  => "error"
                ] 
            ]));
        }
        
        $updateAppData = [
            'ACC_F_APP' => 1,
            'ACC_F_APP_IP' => Helper::get_ip_address(),
            'ACC_F_APPPEMBUKAAN_IP' => Helper::get_ip_address(),
            'ACC_F_APP_PERYT' => "Ya",
            'ACC_F_APPPEMBUKAAN_PERYT' => "Ya",
            'ACC_F_APP_DATE' => date("Y-m-d H:i:s"),
            'ACC_F_APPPEMBUKAAN_DATE' => date("Y-m-d H:i:s"),
            'ACC_LAST_STEP' => "aplikasi-pembukaan-rekening",
        ];

        $updateApp = Database::update("tb_racc", $updateAppData, ['ID_ACC' => $progressAccount['ID_ACC']]);
        if(!$updateApp) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Gagal memperbarui kemajuan Akun Real",
                    'icon'  => "error"
                ]
            ]));
        }

        Logger::client_log([
            'mbrid' => $user['MBR_ID'],
            'module' => "create-account",
            'ref' => $progressAccount['ID_ACC'],
            'message' => "Progress Real Account (Aplikasi Pembukaan Rekening)",
            'data'  => ($data)
        ]);

        $this->markAsUpdated($data, $user);
        exit(json_encode([
            'success'   => true,
            'redirect'  => "/account/create?page=pernyataan-pengungkapan-2",
            'alert'     => [
                'title' => "Berhasil",
                'text'  => "Aplikasi Pembukaan Rekening berhasil",
                'icon'  => "success"
            ]
        ]));  
    }

    private function aplikasiPembukaanRekening_DataPribadi($data, $user, $progressAccount) {
        $required = [
            'datpri_kwngrn'  => "Kewarganegaraan",
            'app_no_handphone_code' => "Kode Negara Nomor Telepon",
            'app_no_handphone' => "Nomor Telepon",
            'app_gender' => "Jenis Kelamin",
            'app_nama_ibu' => "Nama Ibu Kandung",
            'app_status_perkawinan' => "Status Perkawinan",
            'datpri_pendidikan' => "Pendidikan Terakhir",
            'app_status_rumah' => "Status Kepemilikan Rumah",
            'app_tujuan_pembukaan_rek' => "Tujuan Pembukaan Rekening",
            'app_pengalaman_investasi' => "Pengalaman Investasi",
        ];

        foreach($required as $key => $text) {
            if(empty($data[ $key ])) {
                exit(json_encode([
                    'success' => false,
                    'alert' => [
                        'title' => "Gagal",
                        'text'  => "{$text} diperlukan",
                        'icon'  => "error"
                    ] 
                ]));
            }
        }

        if(strtolower($data['app_status_perkawinan']) == "kawin") {
            if(empty($data['acc_app_nama_istri'])) {
                exit(json_encode([
                    'success' => false,
                    'alert' => [
                        'title' => "Gagal",
                        'text'  => "Nama Istri/Suami diperlukan",
                        'icon'  => "error"
                    ] 
                ]));
            }
        }

        /** check nomor telepon */
        $verihub = VerihubFactory::init();
        $app_no_handphone = $verihub->phoneValidation(str_replace("+", "", $data['app_no_handphone_code']), $data['app_no_handphone']);
        if(!$app_no_handphone) {
            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => "Nomor Telepon tidak valid",
                    'icon'  => "error"
                ] 
            ]));
        }

        $app_telepon_rumah = $data['app_telepon_rumah'] ?? 0;
        $app_faksimili_rumah = $data['app_faksimili_rumah'] ?? 0;
        $acc_app_nama_istri = $data['acc_app_nama_istri'] ?? $progressAccount['ACC_F_APP_PRIBADI_NAMAISTRI'] ?? null;
        $bidang_investasi = null;
        
        /** Bidang Investasi */
        if(strtolower($data['app_pengalaman_investasi']) == "ya") {
            if(empty($data['bidang_investasi'])) {
                exit(json_encode([
                    'success' => false,
                    'alert' => [
                        'title' => "Gagal",
                        'text'  => "Bidang investasi diperlukan",
                        'icon'  => "error"
                    ] 
                ]));
            }

            $bidang_investasi = $data['bidang_investasi'];
        }

        /** Menyetujui tidak Memiliki Anggota keluarga bekerja di bappebti / bursa berjangka */
        // if(empty($data['app_anggota_berjangka'])) {
        //     exit(json_encode([
        //         'success' => false,
        //         'alert' => [
        //             'title' => "Gagal",
        //             'text'  => "Anda tidak bisa mendaftar jika memiliki Anggota keluarga yang bekerja di BAPPEBTI / Bursa Berjangka",
        //             'icon'  => "error"
        //         ] 
        //     ]));
        // }

        // /** Menyetujui tidak dinyatakan pailit oleh pengadilan */
        // if(empty($data['app_pailit'])) {
        //     exit(json_encode([
        //         'success' => false,
        //         'alert' => [
        //             'title' => "Gagal",
        //             'text'  => "Anda tidak bisa mendaftar jika dinyatakan pailit oleh Pengadilan",
        //             'icon'  => "error"
        //         ] 
        //     ]));
        // }

        $npwp = $data['app_npwp'] ?? 0;
        if(Regol::isRequiredNpwp($progressAccount["ACC_CDD"])){
            if(is_numeric($npwp) === FALSE) {
                exit(json_encode([
                    'success' => false,
                    'alert' => [
                        'title' => "Gagal",
                        'text'  => "Nomor NPWP Tidak Valid",
                        'icon'  => "error"
                    ] 
                ]));
            }

            if(strlen($npwp) != 16) {
                exit(json_encode([
                    'success' => false,
                    'alert' => [
                        'title' => "Gagal",
                        'text'  => "Nomor NPWP harus 16 digit",
                        'icon'  => "error"
                    ] 
                ]));
            }
        }

        /** Update Data Pribadi */
        $updateDataPribadi = Database::update("tb_racc", [
            'ACC_F_APP_KEWARGANEGARAAN' => $data['datpri_kwngrn'],
            'ACC_F_APP_PRIBADI_NPWP'    => $npwp,
            'ACC_F_APP_PRIBADI_KELAMIN' => $data['app_gender'],
            'ACC_F_APP_PRIBADI_IBU'     => $data['app_nama_ibu'],
            'ACC_F_APP_PRIBADI_STSKAWIN'=> $data['app_status_perkawinan'],
            'ACC_F_APP_PENDIDIKAN'      => $data['datpri_pendidikan'],
            'ACC_F_APP_PRIBADI_NAMAISTRI' => $acc_app_nama_istri,
            'ACC_F_APP_PRIBADI_TLP'     => $app_telepon_rumah,
            'ACC_F_APP_PRIBADI_FAX'     => $app_faksimili_rumah,
            'ACC_F_APP_PRIBADI_HP'      => $app_no_handphone,
            'ACC_F_APP_PRIBADI_STSRMH'  => $data['app_status_rumah'],
            'ACC_F_APP_TUJUANBUKA'      => $data['app_tujuan_pembukaan_rek'],
            'ACC_F_APP_PENGINVT'        => $data['app_pengalaman_investasi'],
            'ACC_F_APP_PENGINVT_BIDANG' => $bidang_investasi,
            'ACC_F_APP_PAILIT'          => "ya",
        ], [
            'ID_ACC' => $progressAccount['ID_ACC']
        ]);

        if($updateDataPribadi !== TRUE) {
            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => $updateDataPribadi ?? "Gagal memperbarui data pribadi",
                    'icon'  => "error"
                ] 
            ]));
        }

        return true;
    }
    
    private function aplikasiPembukaanRekening_PihakDarurat($data, $user, $progressAccount) {
        $required = [
            'app_darurat_nama'  => "Nama Lengkap Pihak Darurat",
            'app_darurat_alamat' => "Alamat Pihak Darurat",
            'app_darurat_telepon' => "No. Telepon Pihak Darurat",
            'app_darurat_hubungan' => "Status Hubungan dengan Pihak Darurat",
        ];

        foreach($required as $key => $text) {
            if(empty($data[ $key ])) {
                exit(json_encode([
                    'success' => false,
                    'alert' => [
                        'title' => "Gagal",
                        'text'  => "{$text} diperlukan",
                        'icon'  => "error"
                    ] 
                ]));
            }
        }

        // $data['app_darurat_kodepos'] = $data['app_darurat_kodepos'] ?? 0;
        // if(is_numeric($data['app_darurat_kodepos']) === FALSE) {
        //     exit(json_encode([
        //         'success' => false,
        //         'alert' => [
        //             'title' => "Gagal",
        //             'text'  => "Kode Pos tidak valid",
        //             'icon'  => "error"
        //         ] 
        //     ]));
        // }

        if(is_numeric($data['app_darurat_telepon']) === FALSE) {
            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => "No, Telepon Pihak Darurat tidak valid",
                    'icon'  => "error"
                ] 
            ]));
        }

        
        
        $updatePihakDarurat = Database::update("tb_racc", [
            'ACC_F_APP_DRRT_NAMA'   => $data['app_darurat_nama'],
            'ACC_F_APP_DRRT_ALAMAT' => $data['app_darurat_alamat'],
            'ACC_F_APP_DRRT_ZIP'    => $data['app_darurat_kodepos'],
            'ACC_F_APP_DRRT_TLP'    => $data['app_darurat_telepon'],
            'ACC_F_APP_DRRT_HUB'    => $data['app_darurat_hubungan']
        ], [
            'ID_ACC' => $progressAccount['ID_ACC']
        ]);

        if($updatePihakDarurat !== TRUE) {
            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => "Gagal memperbarui Data Pihak Darurat Yang Dapat Dihubungi",
                    'icon'  => "error"
                ] 
            ]));
        }

        return true;
    }

    private function aplikasiPembukaanRekening_Pekerjaan($data, $user, $progressAccount) {
        $required = [
            'app_pekerjaan'  => "Pekerjaan",
            'app_nama_perusahaan' => "Nama Perusahaan",
            'app_bidang_usaha' => "Bidang Usaha",
            'app_jabatan_pekerjaan' => "Jabatan",
            'app_lama_bekerja' => "Lama Bekerja",
            'app_lama_bekerja_sebelumnya' => "Lama Bekerja (Kantor Sebelumnya)",
            'app_alamat_kantor' => "Alamat Kantor",
        ];

        foreach($required as $key => $text) {
            if(empty($data[ $key ])) {
                exit(json_encode([
                    'success' => false,
                    'alert' => [
                        'title' => "Gagal",
                        'text'  => "{$text} diperlukan",
                        'icon'  => "error"
                    ] 
                ]));
            }
        }

        $app_kodepos_kantor = $data['app_kodepos_kantor'] ?? null;
        $app_nomor_kantor   = $data['app_nomor_kantor'] ?? 0;
        $app_nomor_fax_kantor   = $data['app_nomor_fax_kantor'] ?? 0;

        /** Kode Pos pekerjaan */
        // if(is_numeric($app_kodepos_kantor) === FALSE) {
        //     exit(json_encode([
        //         'success' => false,
        //         'alert' => [
        //             'title' => "Gagal",
        //             'text'  => "Kode Pos Kantor tidak valid",
        //             'icon'  => "error"
        //         ] 
        //     ]));
        // } 

        // if(strlen($app_kodepos_kantor) != 5) {
        //     exit(json_encode([
        //         'success' => false,
        //         'alert' => [
        //             'title' => "Gagal",
        //             'text'  => "KodePos harus berisi 5 digit",
        //             'icon'  => "error"
        //         ] 
        //     ]));
        // } 
        
        /** Nomor telepon kantor */
        // if(is_numeric($app_nomor_kantor) === FALSE) {
        //     exit(json_encode([
        //         'success' => false,
        //         'alert' => [
        //             'title' => "Gagal",
        //             'text'  => "No. Telepon Kantor tidak valid",
        //             'icon'  => "error"
        //         ] 
        //     ]));
        // }

        /** Nomor faksimili kantor */
        // if(is_numeric($app_nomor_fax_kantor) === FALSE) {
        //     exit(json_encode([
        //         'success' => false,
        //         'alert' => [
        //             'title' => "Gagal",
        //             'text'  => "No. Faksimili Kantor tidak valid",
        //             'icon'  => "error"
        //         ] 
        //     ]));
        // }

        
        
        $updatePekerjaan = Database::update("tb_racc", [
            'ACC_F_APP_KRJ_TYPE' => $data['app_pekerjaan'],
            'ACC_F_APP_KRJ_NAMA' => $data['app_nama_perusahaan'],
            'ACC_F_APP_KRJ_BDNG' => $data['app_bidang_usaha'],
            'ACC_F_APP_KRJ_JBTN' => $data['app_jabatan_pekerjaan'],
            'ACC_F_APP_KRJ_LAMA' => $data['app_lama_bekerja'],
            'ACC_F_APP_KRJ_LAMASBLM' => $data['app_lama_bekerja_sebelumnya'],
            'ACC_F_APP_KRJ_ALAMAT' => $data['app_alamat_kantor'],
            'ACC_F_APP_KRJ_ZIP' => $app_kodepos_kantor,
            'ACC_F_APP_KRJ_TLP' => $app_nomor_kantor,
            'ACC_F_APP_KRJ_FAX' => $app_nomor_fax_kantor
        ], [
            'ID_ACC' => $progressAccount['ID_ACC']
        ]);

        if($updatePekerjaan !== TRUE) {
            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => $updatePekerjaan ?? "Gagal memperbarui data pekerjaan",
                    'icon'  => "error"
                ] 
            ]));
        }

        return true;
    }

    private function aplikasiPembukaanRekening_RekeningBank($data, $user, $progressAccount) {
        for($i = 1; $i <= 2; $i++) {
            $userBanks = MemberBank::list($user['MBR_ID']);
            $bankData = ($userBanks[ $i-1 ] ?? false);

            if(!$bankData && count($userBanks) >= 2) {
                exit(json_encode([
                    'success'   => false,
                    'alert'     => [
                        'title' => "Gagal",
                        'text'  => "Gagal menambahkan bank {$i}, sudah mencapai limit pembuatan bank",
                        'icon'  => "error"
                    ]
                ]));
            }

            if(isset($data["bank_name{$i}"]) && !empty($data["bank_name{$i}"])) {
                $bank_name = $data["bank_name{$i}"];
                $bank_number = $data["bank_number{$i}"] ?? 0;

                if(is_numeric($bank_number) === FALSE || $bank_number <= 0) {
                    JsonResponse([
                        'success' => false,
                        'message' => "Nomor rekening tidak valid",
                        'data' => []
                    ]);
                }
    
                $checkBankName = BankList::findByName($bank_name);
                if(!$checkBankName) {
                    exit(json_encode([
                        'success'   => false,
                        'alert'     => [
                            'title' => "Gagal",
                            'text'  => "Mohon upload Cover buku tabungan bank {$i}",
                            'icon'  => "error"
                        ]
                    ]));
                }
    
                /** check nomor rekening */
                if(!$bank_number || $bank_number < 10) {
                    exit(json_encode([
                        'success'   => false,
                        'alert'     => [
                            'title' => "Gagal",
                            'text'  => "Nomor Rekening bank {$i} tidak valid",
                            'icon'  => "error"
                        ]
                    ]));
                }
    
                if(!$bankData) {
                    $insertBank = Database::insert("tb_member_bank", [
                        'MBANK_MBR' => $progressAccount['ACC_MBR'],
                        'MBANK_HOLDER' => $progressAccount['ACC_FULLNAME'],
                        'MBANK_NAME' => $bank_name,
                        'MBANK_STS' => MemberBank::$statusPending,
                        'MBANK_REGOL' => 1,
                        'MBANK_ACCOUNT' => $bank_number,
                        'MBANK_DATETIME' => date("Y-m-d H:i:s")
                    ]);
        
                    if(!$insertBank) {
                        exit(json_encode([
                            'success'   => false,
                            'alert'     => [
                                'title' => "Gagal",
                                'text'  => "Gagal membuat bank {$i}",
                                'icon'  => "error"
                            ]
                        ])); 
                    }
                
                } else {
                    $status = $bankData['MBANK_STS'];
                    if($bankData['MBANK_ACCOUNT'] != $bank_number || strtolower($bankData['MBANK_NAME']) != strtolower($bank_name)) {
                        $status = MemberBank::$statusPending;
                    }

                    $updateData = [
                        'MBANK_NAME' => $bank_name,
                        'MBANK_STS' => $status,
                        'MBANK_REGOL' => 1,
                        'MBANK_ACCOUNT' => $bank_number
                    ];

                    $update = Database::update("tb_member_bank", $updateData, ['ID_MBANK' => $bankData['ID_MBANK']]);
                    if(!$update) {
                        exit(json_encode([
                            'success'   => false,
                            'alert'     => [
                                'title' => "Gagal",
                                'text'  => "Gagal memperbarui bank {$i}",
                                'icon'  => "error"
                            ]
                        ])); 
                    }
                }
            }
        }

        return true;
    }

    private function aplikasiPembukaanRekening_DaftarKekayaan($data, $user, $progressAccount) {
        $required = [
            'dftrkkyan_sumpeng' => "Sumber Penghasilan",
            'app_penghasilan'   => "Jumlah Penghasilan",
            'app_lokasi_rumah'  => "Lokasi Rumah",
        ];

        foreach($required as $key => $text) {
            if(empty($data[ $key ])) {
                exit(json_encode([
                    'success' => false,
                    'alert' => [
                        'title' => "Gagal",
                        'text'  => "{$text} diperlukan",
                        'icon'  => "error"
                    ] 
                ]));
            }
        }

        $app_nilai_njop = ($data['app_nilai_njop'] ?? '');
        $app_deposit_bank = ($data['app_deposit_bank'] ?? '');
        $app_kekayaan_lainnya = ($data['app_kekayaan_lainnya'] ?? '');
        $app_jumlah = ($data['app_jumlah'] ?? '');

        // if(is_numeric($app_nilai_njop) === FALSE) {
        //     exit(json_encode([
        //         'success' => false,
        //         'alert' => [
        //             'title' => "Gagal",
        //             'text'  => "Jumlah NJOP tidak valid",
        //             'icon'  => "error"
        //         ] 
        //     ]));
        // }
        
        // if(is_numeric($app_deposit_bank) === FALSE) {
        //     exit(json_encode([
        //         'success' => false,
        //         'alert' => [
        //             'title' => "Gagal",
        //             'text'  => "Jumlah Deposit Bank tidak valid",
        //             'icon'  => "error"
        //         ] 
        //     ]));
        // }

        // if(is_numeric($app_kekayaan_lainnya) === FALSE) {
        //     exit(json_encode([
        //         'success' => false,
        //         'alert' => [
        //             'title' => "Gagal",
        //             'text'  => "Jumlah Kekayaan Lainnya tidak valid",
        //             'icon'  => "error"
        //         ] 
        //     ]));
        // }
        
        $updateKekayaan = Database::update("tb_racc", [
            'ACC_F_APP_KEKYAN_SUMBER_PENGHASILAN'  => $data['dftrkkyan_sumpeng'],
            'ACC_F_APP_KEKYAN'  => $data['app_penghasilan'],
            'ACC_F_APP_KEKYAN_RMHLKS' => $data['app_lokasi_rumah'],
            'ACC_F_APP_KEKYAN_NJOP' => $app_nilai_njop,
            'ACC_F_APP_KEKYAN_DPST' => $app_deposit_bank,
            'ACC_F_APP_KEKYAN_LAIN' => $app_kekayaan_lainnya,
            'ACC_F_APP_KEKYAN_NILAI' => $app_jumlah
        ], [
            'ID_ACC' => $progressAccount['ID_ACC']
        ]);

        if($updateKekayaan !== TRUE) {
            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => $updateKekayaan ?? "Gagal memperbarui Daftar kekayaan",
                    'icon'  => "error"
                ] 
            ]));
        }

        return true;
    }

    private function aplikasiPembukaanRekening_DokumenPendukung($data, $user, $progressAccount) {
        $verihub = VerihubFactory::init();

        /** Upload Dokumen 1 */
        if(empty($_FILES['app_image_1']) || $_FILES['app_image_1']['error'] != 0) {
            if(empty($progressAccount['ACC_F_APP_FILE_IMG'])) {
                exit(json_encode([
                    'success' => false,
                    'alert' => [
                        'title' => "Gagal",
                        'text'  => "Mohon upload dokumen pendukung",
                        'icon'  => "error"
                    ] 
                ]));
            }
        
        }else {
            $uploadDokumenPendukung = FileUploadFactory::aws()->upload_single($_FILES['app_image_1'], "regol");
            if(!is_array($uploadDokumenPendukung) || !array_key_exists("filename", $uploadDokumenPendukung)) {
                exit(json_encode([
                    'success' => false,
                    'alert' => [
                        'title' => "Gagal",
                        'text'  => $uploadDokumenPendukung ?? "Gagal mengunggah file dokumen pendukung",
                        'icon'  => "error"
                    ] 
                ]));
            }
    
            $updateImage = Database::update("tb_racc", ['ACC_F_APP_FILE_IMG' => $uploadDokumenPendukung['filename']], ['ID_ACC' => $progressAccount['ID_ACC']]);
            if($updateImage !== TRUE) {
                exit(json_encode([
                    'success' => false,
                    'alert' => [
                        'title' => "Gagal",
                        'text'  => $updateImage ?? "Gagal memperbarui dokumen pendukung, mohon coba lagi",
                        'icon'  => "error"
                    ] 
                ]));
            }
        }

        // /** Upload Dokumen 2 */
        // if(empty($_FILES['app_image_2']) || $_FILES['app_image_2']['error'] != 0) {
        //     if(empty($progressAccount['ACC_F_APP_FILE_IMG2'])) {
        //         exit(json_encode([
        //             'success' => false,
        //             'alert' => [
        //                 'title' => "Gagal",
        //                 'text'  => "Mohon upload dokumen pendukung",
        //                 'icon'  => "error"
        //             ] 
        //         ]));
        //     }
        
        // }else {
        //     $uploadDokumenPendukung = FileUploadFactory::aws()->upload_single($_FILES['app_image_2'], "regol");
        //     if(!is_array($uploadDokumenPendukung) || !array_key_exists("filename", $uploadDokumenPendukung)) {
        //         exit(json_encode([
        //             'success' => false,
        //             'alert' => [
        //                 'title' => "Gagal",
        //                 'text'  => $uploadDokumenPendukung ?? "Gagal mengunggah file dokumen pendukung",
        //                 'icon'  => "error"
        //             ] 
        //         ]));
        //     }
    
        //     $updateImage = Database::update("tb_racc", ['ACC_F_APP_FILE_IMG2' => $uploadDokumenPendukung['filename']], ['ID_ACC' => $progressAccount['ID_ACC']]);
        //     if($updateImage !== TRUE) {
        //         exit(json_encode([
        //             'success' => false,
        //             'alert' => [
        //                 'title' => "Gagal",
        //                 'text'  => $updateImage ?? "Gagal memperbarui dokumen pendukung, mohon coba lagi",
        //                 'icon'  => "error"
        //             ] 
        //         ]));
        //     }
        // }

        // /** Upload Dokumen NPWP */
        // if(empty($_FILES['app_image_npwp']) || $_FILES['app_image_npwp']['error'] != 0) {
        //     if(empty($progressAccount['ACC_F_APP_FILE_NPWP']) && $progressAccount['ACC_CDD'] == Regol::$cddTypeStandard) {
        //         exit(json_encode([
        //             'success' => false,
        //             'alert' => [
        //                 'title' => "Gagal",
        //                 'text'  => "Mohon upload dokumen NPWP",
        //                 'icon'  => "error"
        //             ] 
        //         ]));
        //     }
        
        // }else {
        //     $uploadDokumenPendukung = FileUploadFactory::aws()->upload_single($_FILES['app_image_npwp'], "regol");
        //     if(!is_array($uploadDokumenPendukung) || !array_key_exists("filename", $uploadDokumenPendukung)) {
        //         exit(json_encode([
        //             'success' => false,
        //             'alert' => [
        //                 'title' => "Gagal",
        //                 'text'  => $uploadDokumenPendukung ?? "Gagal mengunggah file dokumen NPWP",
        //                 'icon'  => "error"
        //             ] 
        //         ]));
        //     }
    
        //     $updateImage = Database::update("tb_racc", ['ACC_F_APP_FILE_NPWP' => $uploadDokumenPendukung['filename']], ['ID_ACC' => $progressAccount['ID_ACC']]);
        //     if($updateImage !== TRUE) {
        //         exit(json_encode([
        //             'success' => false,
        //             'alert' => [
        //                 'title' => "Gagal",
        //                 'text'  => $updateImage ?? "Gagal memperbarui dokumen NPWP, mohon coba lagi",
        //                 'icon'  => "error"
        //             ] 
        //         ]));
        //     }
        // }

        /** Upload Dokumen 3 (Optional) */
        if(!empty($_FILES['app_image_3']) && $_FILES['app_image_3']['error'] == 0) {
            $uploadDokumenPendukung = FileUploadFactory::aws()->upload_single($_FILES['app_image_3'], "regol");
            if(!is_array($uploadDokumenPendukung) || !array_key_exists("filename", $uploadDokumenPendukung)) {
                exit(json_encode([
                    'success' => false,
                    'alert' => [
                        'title' => "Gagal",
                        'text'  => $uploadDokumenPendukung ?? "Gagal mengunggah file dokumen pendukung 3",
                        'icon'  => "error"
                    ] 
                ]));
            }
    
            $updateImage = Database::update("tb_racc", ['ACC_F_APP_FILE_IMG3' => $uploadDokumenPendukung['filename']], ['ID_ACC' => $progressAccount['ID_ACC']]);
            if($updateImage !== TRUE) {
                exit(json_encode([
                    'success' => false,
                    'alert' => [
                        'title' => "Gagal",
                        'text'  => $updateImage ?? "Gagal memperbarui dokumen pendukung, mohon coba lagi",
                        'icon'  => "error"
                    ] 
                ]));
            }
        }

        /** Upload Dokumen 4 (Optional) */
        if(!empty($_FILES['app_image_4']) && $_FILES['app_image_4']['error'] == 0) {
            $uploadDokumenPendukung = FileUploadFactory::aws()->upload_single($_FILES['app_image_4'], "regol");
            if(!is_array($uploadDokumenPendukung) || !array_key_exists("filename", $uploadDokumenPendukung)) {
                exit(json_encode([
                    'success' => false,
                    'alert' => [
                        'title' => "Gagal",
                        'text'  => $uploadDokumenPendukung ?? "Gagal mengunggah file dokumen pendukung 4",
                        'icon'  => "error"
                    ] 
                ]));
            }
    
            $updateImage = Database::update("tb_racc", ['ACC_F_APP_FILE_IMG4' => $uploadDokumenPendukung['filename']], ['ID_ACC' => $progressAccount['ID_ACC']]);
            if($updateImage !== TRUE) {
                exit(json_encode([
                    'success' => false,
                    'alert' => [
                        'title' => "Gagal",
                        'text'  => $updateImage ?? "Gagal memperbarui dokumen pendukung, mohon coba lagi",
                        'icon'  => "error"
                    ] 
                ]));
            }
        }

        return true;
    }

    public function formulirDokumenResiko($data, $user) {
        $this->checkCsrfToken($data);
        $progressAccount = $this->checkProgressAccount(md5(md5($user['MBR_ID'])));  
    
        /** Check Status */
        $this->isAllowToEdit(status: $progressAccount['ACC_STS']);

        /** Check Peresetujuan */
        $agree = $data['aggree'] ?? "Tidak";
        if(strtolower($agree) != "ya") {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Anda harus menyetujui untuk melanjutkan ke tahap berikutnya",
                    'icon'  => "error"
                ]
            ]));  
        }

        /** Validasi jumlah checkbox */
        if(count($_POST['box']) < 13) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Mohon menyetujui semua persyaratan yang tertera",
                    'icon'  => "error"
                ]
            ]));  
        }

        
        
        $update = Database::update("tb_racc", [
            'ACC_F_RESK' => 1,
            'ACC_F_RESK_PERYT' => "Ya" ,
            'ACC_F_RESK_IP' => Helper::get_ip_address(),
            'ACC_F_RESK_DATE' => date("Y-m-d H:i:s"),
            'ACC_LAST_STEP' => "formulir-dokumen-resiko",
        ], [
            'ID_ACC' => $progressAccount['ID_ACC']
        ]);

        if($update !== TRUE) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => $update ?? "Gagal memperbarui progress account",
                    'icon'  => "error"
                ]
            ])); 
        }

        Logger::client_log([
            'mbrid' => $user['MBR_ID'],
            'module' => "create-account",
            'ref' => $progressAccount['ID_ACC'],
            'message' => "Progress Real Account (Formulir Dokumen Resiko)",
            'data'  => ($data)
        ]);

        $this->markAsUpdated($data, $user);
        exit(json_encode([
            'success'   => true,
            'redirect'  => "/account/create?page=pernyataan-pengungkapan-3",
            'alert'     => [
                'title' => "Berhasil",
                'text'  => "Berhasil Disimpan",
                'icon'  => "success"
            ]
        ])); 
    }

    public function perjanjianPemberianAmanat($data, $user) {
        $this->checkCsrfToken($data);
        $progressAccount = $this->checkProgressAccount(md5(md5($user['MBR_ID'])));  
    
        /** Check Status */
        $this->isAllowToEdit(status: $progressAccount['ACC_STS']);

        /** Check Peresetujuan */
        $agree = $data['aggree'] ?? "Tidak";
        if(strtolower($agree) != "ya") {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Anda harus menyetujui untuk melanjutkan ke tahap berikutnya",
                    'icon'  => "error"
                ]
            ]));  
        }

        /** Validasi jumlah checkbox */
        if(count($_POST['box']) < 24) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Mohon menyetujui semua persyaratan yang tertera",
                    'icon'  => "error"
                ]
            ]));  
        }

        $data['step07_kotapenyelesaian'] = "BAKTI"; // Otomatis pemilihan ke BAKTI
        if(empty($data['step07_kotapenyelesaian']) || !in_array($data['step07_kotapenyelesaian'], ["BAKTI", "Pengadilan Negeri Jakarta Utara"])) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Mohon Pilih tempat untuk Penyelesaian Perselisihan",
                    'icon'  => "error"
                ]
            ])); 
        }

        $office = ProfilePerusahaan::office();
        $listOffice = array_values(array_column($office, "OFC_CITY"));
        $listOffice = array_map(fn($ar): string => strtoupper($ar), $listOffice);
        $wpbVerifikator = ProfilePerusahaan::wpb_verifikator();

        if(empty($data['step07_kantorpenyelesaian'])) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Mohon Pilih kantor terdekat untuk Penyelesaian Perselisihan",
                    'icon'  => "error"
                ]
            ])); 
        }

        if(!in_array(strtoupper($data['step07_kantorpenyelesaian']), $listOffice)) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Kantor yang dipilih tidak valid/tersedia",
                    'icon'  => "error"
                ]
            ])); 
        }

        if(empty($wpbVerifikator)) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Belum ada WPB Verifikator yang ditunjuk",
                    'icon'  => "error"
                ]
            ])); 
        }

        
        
        $update = Database::update("tb_racc", [
            'ACC_F_PERJ' => 1,
            'ACC_F_PERJ_IP' => Helper::get_ip_address(),
            'ACC_F_PERJ_PERYT' => "Ya",
            'ACC_F_PERJ_DATE' => date("Y-m-d H:i:s"),
            'ACC_F_PERJ_WPB' => $wpbVerifikator['WPB_NAMA'],
            'ACC_F_PERJ_KANTOR' => $data['step07_kantorpenyelesaian'],
            'ACC_F_PERJ_PERSLISIHAN' => $data['step07_kotapenyelesaian'],
            'ACC_LAST_STEP' => "perjanjian-pemberian-amanat",
        ], [
            'ID_ACC' => $progressAccount['ID_ACC']
        ]);

        if($update !== TRUE) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => $update ?? "Gagal memperbarui progress account",
                    'icon'  => "error"
                ]
            ])); 
        }

        Logger::client_log([
            'mbrid' => $user['MBR_ID'],
            'module' => "create-account",
            'ref' => $progressAccount['ID_ACC'],
            'message' => "Progress Real Account (Formulir Perjanjian Pemberian Amanat)",
            'data'  => json_encode($data)
        ]);

        $this->markAsUpdated($data, $user);
        exit(json_encode([
            'success'   => true,
            'redirect'  => "/account/create?page=peraturan-perdagangan",
            'alert'     => [
                'title' => "Berhasil",
                'text'  => "Berhasil disimpan",
                'icon'  => "success"
            ]
        ])); 
    }

    public function peraturanPerdagangan($data, $user) {
        $this->checkCsrfToken($data);
        $progressAccount = $this->checkProgressAccount(md5(md5($user['MBR_ID'])));  
    
        /** Check Status */
        $this->isAllowToEdit(status: $progressAccount['ACC_STS']);

        /** Check Peresetujuan */
        $agree = $data['aggree'] ?? "Tidak";
        if(strtolower($agree) != "ya") {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Anda harus menyetujui untuk melanjutkan ke tahap berikutnya",
                    'icon'  => "error"
                ]
            ]));  
        }

        
        
        $update = Database::update("tb_racc", [
            'ACC_F_TRDNGRULE' => 1,
            'ACC_F_TRDNGRULE_IP' => Helper::get_ip_address(),
            'ACC_F_TRDNGRULE_PERYT' => "Ya",
            'ACC_F_TRDNGRULE_DATE' => date("Y-m-d H:i:s"),
            'ACC_LAST_STEP' => "peraturan-perdagangan",
        ], [
            'ID_ACC' => $progressAccount['ID_ACC']
        ]);

        if($update !== TRUE) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => $update ?? "Gagal memperbarui progress account",
                    'icon'  => "error"
                ]
            ])); 
        }

        Logger::client_log([
            'mbrid' => $user['MBR_ID'],
            'module' => "create-account",
            'ref' => $progressAccount['ID_ACC'],
            'message' => "Progress Real Account (Peraturan Perdagangan / Trading Rules)",
            'data'  => ($data)
        ]);

        $this->markAsUpdated($data, $user);
        exit(json_encode([
            'success'   => true,
            'redirect'  => "/account/create?page=pernyataan-bertanggung-jawab",
            'alert'     => [
                'title' => "Berhasil",
                'text'  => "Berhasil disimpan",
                'icon'  => "success"
            ]
        ])); 
    }

    public function pernyataanBertanggungJawab($data, $user) {
        $this->checkCsrfToken($data);
        $progressAccount = $this->checkProgressAccount(md5(md5($user['MBR_ID'])));  
    
        /** Check Status */
        $this->isAllowToEdit(status: $progressAccount['ACC_STS']);

        /** Check Peresetujuan */
        $agree = $data['aggree'] ?? "Tidak";
        if(strtolower($agree) != "ya") {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Anda harus menyetujui untuk melanjutkan ke tahap berikutnya",
                    'icon'  => "error"
                ]
            ]));  
        }

        
        
        $update = Database::update("tb_racc", [
            'ACC_F_KODE' => 1,
            'ACC_F_KODE_IP' => Helper::get_ip_address(),
            'ACC_F_KODE_PERYT' => "Ya",
            'ACC_F_KODE_DATE' => date("Y-m-d H:i:s"),
            'ACC_LAST_STEP' => "pernyataan-bertanggung-jawab",
        ], [
            'ID_ACC' => $progressAccount['ID_ACC']
        ]);

        if($update !== TRUE) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => $update ?? "Gagal memperbarui progress account",
                    'icon'  => "error"
                ]
            ])); 
        }

        Logger::client_log([
            'mbrid' => $user['MBR_ID'],
            'module' => "create-account",
            'ref' => $progressAccount['ID_ACC'],
            'message' => "Progress Real Account (Pernyataan Bertanggung Jawab)",
            'data'  => ($data)
        ]);

        $this->markAsUpdated($data, $user);
        exit(json_encode([
            'success'   => true,
            'redirect'  => "/account/create?page=pernyataan-dana-nasabah",
            'alert'     => [
                'title' => "Berhasil",
                'text'  => "Berhasil disimpan",
                'icon'  => "success"
            ]
        ])); 
    }

    public function pernyataanDanaNasabah($data, $user) {
        $this->checkCsrfToken($data);
        $progressAccount = $this->checkProgressAccount(md5(md5($user['MBR_ID'])));  
    
        /** Check Status */
        $this->isAllowToEdit(status: $progressAccount['ACC_STS']);

        /** Check Peresetujuan */
        $agree = $data['aggree'] ?? "Tidak";
        if(strtolower($agree) != "ya") {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Anda harus menyetujui untuk melanjutkan ke tahap berikutnya",
                    'icon'  => "error"
                ]
            ]));  
        }
        
        $update = Database::update("tb_racc", [
            'ACC_F_DANA' => 1,
            'ACC_F_DANA_IP' => Helper::get_ip_address(), 
            'ACC_F_DANA_PERYT' => "Ya",
            'ACC_F_DANA_DATE' => date("Y-m-d H:i:s"),
            'ACC_LAST_STEP' => "pernyataan-dana-nasabah",
        ], [
            'ID_ACC' => $progressAccount['ID_ACC']
        ]);

        if($update !== TRUE) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => $update ?? "Gagal memperbarui progress account",
                    'icon'  => "error"
                ]
            ])); 
        }

        Logger::client_log([
            'mbrid' => $user['MBR_ID'],
            'module' => "create-account",
            'ref' => $progressAccount['ID_ACC'],
            'message' => "Progress Real Account (Pernyataan Dana Nasabah)",
            'data'  => ($data)
        ]);

        $this->markAsUpdated($data, $user);
        exit(json_encode([
            'success'   => true,
            'redirect'  => "/account/create?page=pernyataan-pengungkapan-4",
            'alert'     => [
                'title' => "Berhasil",
                'text'  => "Berhasil disimpan",
                'icon'  => "success"
            ]
        ])); 
    }

    private function uploadSelfiePhoto($data, $user) {
        $verihub = VerihubFactory::init();
        $progressAccount = $this->checkProgressAccount(md5(md5($user['MBR_ID'])));

        /** Upload Dokumen Foto Terbaru */
        if(empty($_FILES['app_foto_terbaru']) || $_FILES['app_foto_terbaru']['error'] != 0) {
            if(!empty($progressAccount['ACC_F_APP_FILE_FOTO'])) {
                return false;
            }
            
            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => "Mohon upload foto terbaru",
                    'icon'  => "error"
                ] 
            ]));
        }

        if($progressAccount['ACC_DOC_VERIF'] == -1) {
            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => "Dokumen telah diverifikasi, tidak dapat dirubah",
                    'icon'  => "error"
                ] 
            ]));
        }

        /** validasi file sebelum di upload */
        $checkSelfie = $verihub->validate_photoSelfie($_FILES['app_foto_terbaru']);
        if(!is_array($checkSelfie)) {
            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => "Terdapat kesalahan pada foto Selfie Anda",
                    'icon'  => "error"
                ] 
            ]));
        }

        if(empty($checkSelfie['image_scaling'])) {
            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => "Invalid Data",
                    'icon'  => "error"
                ] 
            ]));
        }

        $newFileName    = "regol_selfie_".time().rand(1000000, 9999999).".jpeg";
        $target_dir     = $_SERVER['DOCUMENT_ROOT'] . "/assets/uploads/{$newFileName}";
        $upload_local   = file_put_contents($target_dir, $checkSelfie['image_scaling']);
        if(!$upload_local) {
            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => "Gagal saat mengunggah foto",
                    'icon'  => "error"
                ] 
            ]));
        }


        $fileUploadFactory = FileUploadFactory::aws();
        $s3 = new Aws\S3\S3Client([
            'region'  => $fileUploadFactory->getRegion(),
            'version' => 'latest',
            'credentials' => [
                'key'    => $fileUploadFactory->getKey(),
                'secret' => $fileUploadFactory->getSecretKey(),
            ]
        ]);

        try {
            /** Upload to AWS */
            $result = $s3->putObject([
                'Bucket' => $fileUploadFactory->getBucket(),
                'Key'    => $fileUploadFactory->getFolder() ."/".$newFileName,
                'Body'   => fopen($target_dir, 'r'),
                'ACL'    => 'public-read', // make file 'public'
            ]);

            /** Delete file from local disk */
            unlink($target_dir);

            
        } catch (Aws\S3\Exception\S3Exception $e) {
            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => "Gagal mengunggah foto Selfie (400)",
                    'icon'  => "error"
                ] 
            ]));
        }

        // /** Detect FaceLiveness */
        // if(strtoupper($progressAccount['RTYPE_TYPE']) == "MICRO") {
        //     $base64Image = file_get_contents(FileUploadFactory::aws()->awsFile($newFileName));
        //     $base64Image = ("data:image/jpeg;base64,".base64_encode($base64Image));
        //     $validSelfie = $verihub->detectFaceLiveness(['mbrid' => $progressAccount['ACC_MBR'], 'image' => $base64Image]);
        //     if($validSelfie['success'] !== TRUE || $validSelfie['code'] != 200) {
        //         exit(json_encode([
        //             'success' => false,
        //             'alert' => [
        //                 'title' => "Gagal",
        //                 'text'  => $validSelfie['message'] ?? "(Foto Selfie) File yang diupload tidak valid",
        //                 'icon'  => "error"
        //             ] 
        //         ]));
        //     }
        // }
        
        $data = [
            'ACC_F_APP_FILE_FOTO' => $newFileName,
            'ACC_F_APP_FILE_FOTO_MIME' => $checkSelfie['type']
        ];

        $updateImage = Database::update("tb_racc", $data, ['ID_ACC' => $progressAccount['ID_ACC']]);
        if($updateImage !== TRUE) {
            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => $updateImage ?? "Gagal memperbarui foto terbaru, mohon coba lagi",
                    'icon'  => "error"
                ] 
            ]));
        }

        // exit(json_encode([
        //     'success' => true,
        //     'alert' => [
        //         'title' => "Berhasil",
        //         'text'  => "Foto selfie berhasil disimpan",
        //         'icon'  => "success"
        //     ] 
        // ]));
    }

    private function uploadKtpPhoto($data, $user) {
        $verihub = VerihubFactory::init();
        $progressAccount = $this->checkProgressAccount(md5(md5($user['MBR_ID'])));
        
        /** Upload Dokumen Foto KTP */
        if(empty($_FILES['app_foto_identitas']) || $_FILES['app_foto_identitas']['error'] != 0) {
            if(!empty($progressAccount['ACC_F_APP_FILE_ID'])) {
                return false;
            }

            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => "Mohon upload foto KTP",
                    'icon'  => "error"
                ] 
            ]));
        }

        if($progressAccount['ACC_DOC_VERIF'] == -1) {
            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => "Dokumen telah diverifikasi, tidak dapat dirubah",
                    'icon'  => "error"
                ] 
            ]));

        }

        /** validasi file sebelum di upload */
        $validKtp = $verihub->validate_photoKtp($_FILES['app_foto_identitas']);
        if(!is_array($validKtp)) {
            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => "Terdapat kesalahan pada foto KTP Anda",
                    'icon'  => "error"
                ] 
            ]));
        }

        if(empty($validKtp['image_scaling'])) {
            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => "Invalid Data",
                    'icon'  => "error"
                ] 
            ]));
        }

        // $uploadFotoKtp = upload_myfile($validKtp, "regol_ktp");
        $newFileName    = "regol_ktp_".time().rand(1000000, 9999999).".jpeg";
        $target_dir     = $_SERVER['DOCUMENT_ROOT'] . "/assets/uploads/{$newFileName}";
        $upload_local   = file_put_contents($target_dir, $validKtp['image_scaling']);
        if(!$upload_local) {
            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => "Gagal saat mengunggah foto",
                    'icon'  => "error"
                ] 
            ]));
        }


        $fileUploadFactory = FileUploadFactory::aws();
        $s3 = new Aws\S3\S3Client([
            'region'  => $fileUploadFactory->getRegion(),
            'version' => 'latest',
            'credentials' => [
                'key'    => $fileUploadFactory->getKey(),
                'secret' => $fileUploadFactory->getSecretKey(),
            ]
        ]);

        try {
            /** Upload to AWS */
            $result = $s3->putObject([
                'Bucket' => $fileUploadFactory->getBucket(),
                'Key'    => $fileUploadFactory->getFolder() ."/".$newFileName,
                'Body'   => fopen($target_dir, 'r'),
                'ACL'    => 'public-read', // make file 'public'
            ]);

            /** Delete file from local disk */
            unlink($target_dir);

            
        } catch (Aws\S3\Exception\S3Exception $e) {
            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => "Gagal mengunggah foto KTP (402)",
                    'icon'  => "error"
                ] 
            ]));
        }
        

        $data = [
            'ACC_F_APP_FILE_ID' => $newFileName,
            'ACC_F_APP_FILE_ID_MIME' => "image/jpeg"
        ];

        $updateImage = Database::update("tb_racc", $data, ['ID_ACC' => $progressAccount['ID_ACC']]);
        if($updateImage !== TRUE) {
            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => $updateImage ?? "Gagal memperbarui foto KTP, mohon coba lagi",
                    'icon'  => "error"
                ] 
            ]));
        }

        // exit(json_encode([
        //     'success' => true,
        //     'alert' => [
        //         'title' => "Berhasil",
        //         'text'  => "Foto KTP berhasil disimpan",
        //         'icon'  => "success"
        //     ] 
        // ]));
    }

    private function uploadSelfiePhoto_simple($data, $user) {
        $progressAccount = $this->checkProgressAccount(md5(md5($user['MBR_ID'])));
        
        /** Upload Dokumen Foto KTP */
        if(empty($_FILES['app_foto_terbaru']) || $_FILES['app_foto_terbaru']['error'] != 0) {
            if(!empty($progressAccount['ACC_F_APP_FILE_ID'])) {
                return false;
            }

            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => "Mohon upload foto Selfie",
                    'icon'  => "error"
                ] 
            ]));
        }

        if($progressAccount['ACC_DOC_VERIF'] == -1) {
            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => "Dokumen telah diverifikasi, tidak dapat dirubah",
                    'icon'  => "error"
                ] 
            ]));
        }

        /** check File Size */
        $verihub = VerihubFactory::init();
        $bits = filesize($_FILES['app_foto_terbaru']['tmp_name']) ?? 0;
        $fileSize = $verihub->getFileSize($bits, "KB");
        if($fileSize < 100 || $fileSize > 4000) {
            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => "Minimal ukuran file selfie 100kb dan Maksimal 4MB",
                    'icon'  => "error"
                ] 
            ]));
        }

        $uploadSelfie = FileUploadFactory::aws()->upload_single($_FILES['app_foto_terbaru'], "regol_ktp");
        if(!is_array($uploadSelfie) || !array_key_exists("filename", $uploadSelfie)) {
            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => $uploadSelfie ?? "Gagal mengunggah file dokumen pendukung",
                    'icon'  => "error"
                ] 
            ]));
        }

        $data = [
            'ACC_F_APP_FILE_FOTO' => $uploadSelfie['filename'],
            'ACC_F_APP_FILE_FOTO_MIME' => $uploadSelfie['mime']
        ];

        $updateImage = Database::update("tb_racc", $data, ['ID_ACC' => $progressAccount['ID_ACC']]);
        if($updateImage !== TRUE) {
            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => $updateImage ?? "Gagal memperbarui foto Selfie, mohon coba lagi",
                    'icon'  => "error"
                ] 
            ]));
        }

        // exit(json_encode([
        //     'success' => true,
        //     'alert' => [
        //         'title' => "Berhasil",
        //         'text'  => "Foto KTP berhasil disimpan",
        //         'icon'  => "success"
        //     ] 
        // ]));
    }

    private function uploadKtpPhoto_simple($data, $user) {
        $progressAccount = $this->checkProgressAccount(md5(md5($user['MBR_ID'])));
        
        /** Upload Dokumen Foto KTP */
        if(empty($_FILES['app_foto_identitas']) || $_FILES['app_foto_identitas']['error'] != 0) {
            if(!empty($progressAccount['ACC_F_APP_FILE_ID'])) {
                return false;
            }

            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => "Mohon upload foto KTP",
                    'icon'  => "error"
                ] 
            ]));
        }

        if($progressAccount['ACC_DOC_VERIF'] == -1) {
            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => "Dokumen telah diverifikasi, tidak dapat dirubah",
                    'icon'  => "error"
                ] 
            ]));
        }

        /** check File Size */
        $verihub = VerihubFactory::init();
        $bits = filesize($_FILES['app_foto_identitas']['tmp_name']) ?? 0;
        $fileSize = $verihub->getFileSize($bits, "KB");
        if($fileSize < 100 || $fileSize > 2000) {
            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => "Minimal ukuran file ktp 100kb dan Maksimal 2MB",
                    'icon'  => "error"
                ] 
            ]));
        }

        $uploadKtp = FileUploadFactory::aws()->upload_single($_FILES['app_foto_identitas'], "regol_ktp");
        if(!is_array($uploadKtp) || !array_key_exists("filename", $uploadKtp)) {
            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => $uploadKtp ?? "Gagal mengunggah file dokumen pendukung",
                    'icon'  => "error"
                ] 
            ]));
        }

        $data = [
            'ACC_F_APP_FILE_ID' => $uploadKtp['filename'],
            'ACC_F_APP_FILE_ID_MIME' => $uploadKtp['mime']
        ];

        $updateImage = Database::update("tb_racc", $data, ['ID_ACC' => $progressAccount['ID_ACC']]);
        if($updateImage !== TRUE) {
            exit(json_encode([
                'success' => false,
                'alert' => [
                    'title' => "Gagal",
                    'text'  => $updateImage ?? "Gagal memperbarui foto KTP, mohon coba lagi",
                    'icon'  => "error"
                ] 
            ]));
        }

        // exit(json_encode([
        //     'success' => true,
        //     'alert' => [
        //         'title' => "Berhasil",
        //         'text'  => "Foto KTP berhasil disimpan",
        //         'icon'  => "success"
        //     ] 
        // ]));
    }

    public function verifikasiIdentitas($data, $user) {
        $this->checkCsrfToken($data);
        exit(json_encode([
            'success'   => false,
            'alert'     => [
                'title' => "Gagal",
                'text'  => "Maintenance",
                'icon'  => "error"
            ]
        ])); 

        $progressAccount = $this->checkProgressAccount(md5(md5($user['MBR_ID'])));

        /** Check Status */
        $this->isAllowToEdit($progressAccount['ACC_STS']);
        
        /** Upload Selfie Photo */
        // $this->uploadSelfiePhoto($data, $user);
        $this->uploadSelfiePhoto_simple($data, $user);

        /** Upload KTP Photo */
        // $this->uploadKtpPhoto($data, $user);
        $this->uploadKtpPhoto_simple($data, $user);
        
        /** Verifikasi ke Verihub (Jika belum pernah berhasil) */
        $statusVerifikasiVerihub = $progressAccount['ACC_DOC_VERIF'] ?? 0;
        if($statusVerifikasiVerihub == 0) {
            $verif = 1;
            // $verif = $this->verifikasiVerihub($data, $user, $progressAccount);
            $statusVerifikasiVerihub = -1;
            $data['reference_id'] = $verif;
        }

        /** Update Status Verifikasi (Jika berbeda dari sebelumnya) */
        if($statusVerifikasiVerihub != $progressAccount['ACC_DOC_VERIF']) {
            $updateData = [
                'ACC_DOC_VERIF' => $statusVerifikasiVerihub,
                'ACC_LAST_STEP' => "verifikasi-identitas",
            ];

            $update = Database::update("tb_racc", $updateData, ['ID_ACC' => $progressAccount['ID_ACC']]);
            if($update !== TRUE) {
                exit(json_encode([
                    'success'   => false,
                    'alert'     => [
                        'title' => "Gagal",
                        'text'  => $update ?? "Gagal memperbarui progress account",
                        'icon'  => "error"
                    ]
                ])); 
            }
        }

        Logger::client_log([
            'mbrid' => $user['MBR_ID'],
            'module' => "create-account",
            'ref' => $progressAccount['ID_ACC'],
            'message' => "Progress Real Account (Verifikasi Identitas)",
            'data'  => ($data)
        ]);

        exit(json_encode([
            'success'   => true,
            'redirect'  => "/account/create?page=kelengkapan-formulir",
            'alert'     => [
                'title' => "Berhasil",
                'text'  => "Berhasil disimpan",
                'icon'  => "success"
            ]
        ])); 
    }

    private function verifikasiVerihub($data, $user, $progressAccount) {
        $verihub            = VerihubFactory::init();
        $uniqid             = uniqid();
        $reference_id       = md5($user['MBR_ID'] . $uniqid);
        $fileContentKTP     = file_get_contents(FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_ID']));
        $fileContentSelfie  = file_get_contents(FileUploadFactory::aws()->awsFile($progressAccount['ACC_F_APP_FILE_FOTO']));
        $sendVerification   = $verihub->send_idVerification([
            'mbrid' => $user['MBR_ID'],
            'nik'   => strtoupper($progressAccount['ACC_NO_IDT']),
            'name'  => $progressAccount['ACC_FULLNAME'],
            'birth_date' => $progressAccount['ACC_TANGGAL_LAHIR'],
            'email' => $user['MBR_EMAIL'], 
            'phone' => $user['MBR_PHONE'], 
            'ktp_photo' => ("data:".$progressAccount['ACC_F_APP_FILE_ID_MIME'].";base64,".base64_encode($fileContentKTP)), 
            'selfie_photo' => ("data:".$progressAccount['ACC_F_APP_FILE_FOTO_MIME'].";base64,".base64_encode($fileContentSelfie)), 
            'reference_id' => $reference_id
        ]);

        if(!$sendVerification['success']) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => $sendVerification['message'] ?? "Verifikasi Gagal",
                    'icon'  => "error"
                ]
            ])); 
        }

        return $reference_id;
    }

    public function kelengkapanFormulir($data, $user) {
        $this->checkCsrfToken($data);
        $progressAccount = $this->checkProgressAccount(md5(md5($user['MBR_ID'])));  
    
        /** Check Status */
        $this->isAllowToEdit(status: $progressAccount['ACC_STS']);
        
        /** Check Peresetujuan */
        $agree = $data['aggree'] ?? "Tidak";
        if(strtolower($agree) != "ya") {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Anda harus menyetujui untuk melanjutkan ke tahap berikutnya",
                    'icon'  => "error"
                ]
            ]));  
        }

        $statusWpCheck = Regol::$statusWPCheckBankVerification;
        $optionSkip = $progressAccount['ACC_SKIP'];
        $datetime = date("Y-m-d H:i:s");
        if($optionSkip) {
            $statusWpCheck = Regol::$statusWPCheckGoodFund;
            
            /** check apakah ada bank yang belum diverifikasi, jika iya maka status wpcheck ke bank verification */
            if(count(MemberBank::list($user['MBR_ID'], [MemberBank::$statusPending])) > 0) {
                $statusWpCheck = Regol::$statusWPCheckBankVerification;
                $optionSkip = 0;
            }

            /** Insert Account Condition */
            $insertAcccnd = Database::insert("tb_acccond", [
                "ACCCND_MBR" => $user["MBR_ID"],
                "ACCCND_ACC" => $progressAccount["ID_ACC"],
                "ACCCND_AMOUNTMARGIN" => 0,
                "ACCCND_CASH_FOREX" => 0,
                "ACCCND_LOGIN" => 0,
                "ACCCND_DATEMARGIN" => $datetime
            ]);

            if(!$insertAcccnd) {
                exit(json_encode([
                    'success'   => false,
                    'alert'     => [
                        'title' => "Gagal",
                        'text'  => "Gagal membuat akun",
                        'icon'  => "error"
                    ]
                ])); 
            }
        }

        $dataUpdate = [
            'ACC_F_CMPLT' => 1,
            'ACC_F_CMPLT_IP' => Helper::get_ip_address(), 
            'ACC_F_CMPLT_PERYT' => "Ya",
            'ACC_F_CMPLT_DATE' => date("Y-m-d H:i:s"),
            'ACC_STS' => 1,
            'ACC_KODE' => uniqid(),
            'ACC_LAST_STEP' => "kelengkapan-formulir",
            'ACC_SKIP' => $optionSkip,
            'ACC_WPCHECK' => $statusWpCheck,
            'ACC_WPCHECK_DATE' => $datetime,
            'ACC_DATETIME' => $datetime
        ];

        $update = Database::update("tb_racc", $dataUpdate, ['ID_ACC' => $progressAccount['ID_ACC']]);
        if($update !== TRUE) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => $update ?? "Gagal memperbarui progress account",
                    'icon'  => "error"
                ]
            ])); 
        }

        Logger::client_log([
            'mbrid' => $user['MBR_ID'],
            'module' => "create-account",
            'ref' => $progressAccount['ID_ACC'],
            'message' => "Progress Real Account (Kelengkapan Formulir)",
            'data'  => ($data)
        ]);

        $commentFirebase = "Permintaan pembuatan Real Account oleh email " . $user['MBR_EMAIL'];
        $pushOpsEvent = Firebase::pushOpsEvent('progress_real_account', $user['MBR_ID'], $commentFirebase, 'Web');

        $this->markAsUpdated($data, $user);
        exit(json_encode([
            'success'   => true,
            'redirect'  => "/account/create?page=selesai",
            'alert'     => [
                'title' => "Berhasil",
                'text'  => "Berhasil disimpan",
                'icon'  => "success"
            ]
        ])); 
    }

    public function depositNewAccount($data, $user) {
        // $this->checkCsrfToken($data);
        $progressAccount = $this->checkProgressAccount(md5(md5($user['MBR_ID'])));  
    
        /** Check Status */
        // $this->isAllowToEdit(status: $progressAccount['ACC_STS']);
        
        /** Check Status */
        if($progressAccount['ACC_STS'] != 1 || $progressAccount['ACC_WPCHECK'] != 1) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Status akun tidak valid",
                    'icon'  => "error"
                ]
            ]));  
        }


        /** Required form */
        $required = [
            'dpnewacc_bankusr' => "Bank Nasabah",
            'dpnewacc_bankcmpy' => "Bank Penerima",
            'dpnewacc_dpstval' => "Jumlah Deposit",
        ];

        foreach($required as $key => $text) {
            if(empty($data[ $key ])) {
                exit(json_encode([
                    'success'   => false,
                    'alert'     => [
                        'title' => "Gagal",
                        'text'  => "{$text} diperlukan",
                        'icon'  => "error"
                    ]
                ]));  
            }
        }

        /** Check File upload */
        if(empty($_FILES['dpnewacc_tfprove']) || $_FILES['dpnewacc_tfprove']['error'] != 0) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Mohon upload file bukti transfer deposit",
                    'icon'  => "error"
                ]
            ]));  
        }
        
        $amountSource = Helper::stringTonumber($data['dpnewacc_dpstval']);
        $amountFinal = 0;
        $currencyFrom = $progressAccount['RTYPE_CURR'];
        $currencyTo = "USD";

        /** Check Bank nasabah */
        $userBank = User::myBank($user['MBR_ID'], $data['dpnewacc_bankusr']);
        if(empty($userBank)) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Data Bank nasabah tidak ditemukan",
                    'icon'  => "error"
                ]
            ]));  
        }

        /** Check bank admin */
        $adminBank = Admin::getAdminBank($data['dpnewacc_bankcmpy']);
        if(empty($adminBank)) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Data Bank admin tidak ditemukan",
                    'icon'  => "error"
                ]
            ]));
        }

        /** Check Amount */
        if(is_numeric($amountSource) === FALSE || $amountSource <= 0) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Jumlah deposit tidak valid",
                    'icon'  => "error"
                ]
            ]));  
        }

        /** Check Minimum */
        if($amountSource < $progressAccount['RTYPE_MINDEPOSIT']) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Minimum deposit " . implode(" ", [$progressAccount['RTYPE_CURR'], Helper::formatCurrency($progressAccount['RTYPE_MINDEPOSIT'])]),
                    'icon'  => "error"
                ]
            ]));  
        }

        /** Check Maximum */
        if($amountSource > $progressAccount['RTYPE_MAXDEPOSIT']) {
            exit(json_encode(value: [
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Maximum deposit " . implode(" ", [$progressAccount['RTYPE_CURR'], Helper::formatCurrency($progressAccount['RTYPE_MAXDEPOSIT'])]),
                    'icon'  => "error"
                ]
            ]));  
        }


        /** Convertsation */
        $convert = Account::accountConvertation([
            'account_id' => $progressAccount['ID_ACC'],
            'amount' => $amountSource,
            'from' => $currencyFrom,
            'to' => $currencyTo
        ]);

        if(!is_array($convert)) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => $convert,
                    'icon'  => "error"
                ]
            ])); 
        }

        /** Set Amount Final */
        $amountFinal = ($amountSource / $convert['rate']);
        
        /** Upload File */
        $uploadFile = FileUploadFactory::aws()->upload_single($_FILES['dpnewacc_tfprove'], "deposit_new_account");
        if(!is_array($uploadFile) || !array_key_exists("filename", $uploadFile)) {
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => $uploadFile ?? "Gagal upload file",
                    'icon'  => "error"
                ]
            ])); 
        }

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        mysqli_begin_transaction($this->db);

        /** Insert DPWD */
        $insert = Database::insert("tb_dpwd", [
            'DPWD_MBR' => $user['MBR_ID'],
            'DPWD_TYPE' => 3,
            'DPWD_RACC' => $progressAccount['ID_ACC'],
            'DPWD_BANKSRC' => implode("/", [$userBank['MBANK_NAME'], $userBank['MBANK_ACCOUNT'], $userBank['MBANK_HOLDER']]),
            'DPWD_BANK' => implode("/", [$adminBank['BKADM_NAME'], $adminBank['BKADM_ACCOUNT'], $adminBank['BKADM_HOLDER']]),
            'DPWD_AMOUNT' => $amountFinal,
            'DPWD_AMOUNT_SOURCE' => $amountSource,
            'DPWD_CURR_FROM' => $currencyFrom,
            'DPWD_CURR_TO' => $currencyTo,
            'DPWD_RATE' => $convert['rate'],
            'DPWD_PIC' => $uploadFile['filename'],
            'DPWD_NOTE' => "Deposit New Account",
            'DPWD_IP' => Helper::get_ip_address(),
            'DPWD_DATETIME' => date("Y-m-d H:i:s")
        ]);

        if(!$insert) {
            $this->db->rollback();
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => "Failed to create transaction",
                    'icon'  => "error"
                ]
            ])); 
        }

        /** Update tb_racc */
        $update = Database::update("tb_racc", ['ACC_WPCHECK' => 2], ['ID_ACC' => $progressAccount['ID_ACC']]);
        if(!$update) {
            $this->db->rollback();
            exit(json_encode([
                'success'   => false,
                'alert'     => [
                    'title' => "Gagal",
                    'text'  => $update ?? "Failed to update account status",
                    'icon'  => "error"
                ]
            ])); 
        }

        $data['filename'] = $uploadFile['filename'];
        Logger::client_log([
            'mbrid' => $user['MBR_ID'],
            'module' => "create-account",
            'ref' => $progressAccount['ID_ACC'],
            'ip' => Helper::get_ip_address(),
            'message' => "Progress Real Account (Deposit New Account)",
            'data'  => json_encode($data)
        ]);


        $this->db->commit();
        exit(json_encode([
            'success'   => true,
            'redirect'  => "/account/create?page=deposit-new-account",
            'alert'     => [
                'title' => "Berhasil",
                'text'  => "Deposit new account sedang diprosess",
                'icon'  => "success"
            ]
        ])); 
    }

}

try {
    $db         = Database::connect();
    $appPost    = new AppPost();
    $method     = $_POST['method'] ?? $_GET['method'] ?? "error"; 
    
    /** Check Session & Cookie */
    $user = User::user();
    if(!$user) {
        exit(json_encode([
            'success'   => true,
            'redirect'  => "/",
            'alert'     => [
                'title' => "Gagal",
                'text'  => "Invalid Credential, Please re-login",
                'icon'  => "error"
            ]
        ]));
    }
    
    if(empty($user) || empty($user['MBR_ID'])) {
        exit(json_encode([
            'success'   => true,
            'redirect'  => "/",
            'alert'     => [
                'title' => "Gagal",
                'text'  => "Invalid Session",
                'icon'  => "error"
            ]
        ]));
    }

    if(empty($method) || !method_exists($appPost, $method)) {
        exit(json_encode([
            'success'   => false,
            'alert'     => [
                'title' => "Gagal",
                'text'  => "Invalid Method",
                'icon'  => "error"
            ]
        ]));
    }

    // call_user_func_array([$appPost, $method], $postData);
    $user['userid'] = md5(md5($user['MBR_ID']));
    call_user_func_array([$appPost, $method], [$_POST, $user]);

} catch(Throwable $th) {
    LoggingFactory::make()->debug("Error Processing Request: " . $th->getMessage(), [
        'file' => $th->getFile(),
        'line' => $th->getLine(),
        'trace' => $th->getTraceAsString()
    ]);

    exit(json_encode([
        'success' => false,
        'error' => "Terjadi kesalahan saat memproses data",
        'alert' => [
            'title' => "Error",
            'text'  => "Terjadi kesalahan saat memproses data",
            'icon'  => "error"
        ],
    ]));
}