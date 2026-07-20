<?php
namespace App\Models;

use Config\Core\Database;
use Config\Core\SystemInfo;
use Exception;

class Regol {

    public static $listPekerjaan = [
        'Pejabat Lembaga Legislatif dan Pemerintah',
        'Pegawai BUMN / BUMD (termasuk pensiunan)',
        'Pengusaha / Wiraswasta',
        'Pegawai Swasta',
        'PNS (termasuk pensiunan)',
        'Profesional dan Konsultan',
        'TNI / Polri (termasuk pensiunan)',
        'Pegawai Bank',
        'Pengurus Parpol',
        'Pengajar dan Dosen',
        'Pedagang',
        'Pegawai Money Changer',
        'Ibu Rumah Tangga',
        'Pengurus / Pegawai LSM / Organisasi tidak berbadan hukum lainnya',
        'Pengurus dan pegawai yayasan / lembaga berbadan hukum lainnya',
        'Ulama / Pendeta / Pimpinan organisasi dan kelompok keagamaan',
        'Pelajar / Mahasiswa',
        'Buruh, Pembantu Rumah Tangga dan Tenaga Keamanan',
        'Petani dan Nelayan',
        'Pengrajin',
        'Freelance',
        'Lainnya'
    ];

    public static $listPendapatan = [
        'Antara 100-250 juta', 
        'Antara 250-500 juta', 
        '> 500 juta'
    ];

    public static $kantorPenyelesaian = [
        'bakti' => "Badan Arbitrase Perdagangan Berjangka Komoditi (BAKTI) berdasarkan Peraturan dan Prosedur Badan Arbitrase Perdagangan Berjangka Komoditi (BAKTI)",
        'pengadilan_negeri_jakarta' => "Pengadilan Negeri Jakarta"
    ];

    public static $typeIdentitas = ['KTP', 'PASSPORT', 'KITAS'];

    public static $kurang1mLebih5m = [
        'Kurang dari 1 Miliar rupiah',
        'Antara 1 - 5 Miliar rupiah',
        'Lebih dari 5 Miliar rupiah'
    ];
    
    public static $kurang500jtLebih2m = [
        'Kurang dari 500 juta rupiah',
        'Antara 500 juta - 2 Milliar rupiah',
        'Lebih dari 2 Miliar rupiah'
    ];

    public static $jenisHubunganPihakDarurat = [
        'Keluarga',
        'Teman'
    ];

    public static $pendidikanTerakhir = [
        "SD",
        "SMP",
        "SMA",
        "D1-D3",
        "S1 Sarjana",
        "S2 Master",
        "S3 Doktor"
    ];

    public static $tujuanPembukaan = [
        "Lindung Nilai", 
        "Gain", 
        "Spekulasi", 
        "Lainnya"
    ];

    public static $sumberPenghasilan = [
        "Gaji",
        "Laba Usaha",
        "Uang Pensiun",
        "Warisan",
        "Hibah",
        "Pasangan",
        "Orang Tua"
    ];

    public static $sidNumberPattern = [
        'tipe_investor' => ['ID', 'SC', 'MF', 'CP', 'PF', 'OT'],
        'status_investor' => ['D', 'F']
    ];

    public static int $cddTypeStandard = 1;

    public static int $cddTypeSederhana = 2;

    public static int $statusWPCheckBankVerification = 0;
    public static int $statusWPCheckRegister = 1;
    public static int $statusWPCheckGoodFund = 5;
    public static int $statusWPCheckActive = 6;

    public static function getLastAccount(string $userid): array|bool {
        try {
            $db = Database::connect();
            $sqlGet = $db->query("
                SELECT 
                    tr.*,
                    tra.*
                FROM tb_racc tr 
                JOIN tb_racctype tra ON (tra.ID_RTYPE = tr.ACC_TYPE) 
                WHERE UPPER(tra.RTYPE_TYPE) != 'DEMO'
                AND tr.ACC_LOGIN != 0
                AND tr.ACC_WPCHECK = 6
                AND MD5(MD5(tr.ACC_MBR)) = '{$userid}'
                ORDER BY ID_ACC DESC 
                LIMIT 1
            ");

            if($sqlGet->num_rows == 0) {
                return false;
            }

            return $sqlGet->fetch_assoc();

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function duplicateLastAccount(string $userid) {
        try {
            /** Get Last Account */
            $lastAccount = self::getLastAccount($userid);
            unset(
                $lastAccount['ID_ACC'], 
                $lastAccount['ACC_WPCHECK_DATE'], 
                $lastAccount['ACC_PASS'], 
                $lastAccount['ACC_INVESTOR'], 
                $lastAccount['ACC_PASS'], 
                $lastAccount['ACC_PASSPHONE'], 
                $lastAccount['ACC_INITIALMARGIN'],
                $lastAccount['ACC_LAST_STEP'],
                $lastAccount['ACC_F_CMPLT'],
                $lastAccount['ACC_F_CMPLT_IP'],
                $lastAccount['ACC_F_CMPLT_PERYT'],
                $lastAccount['ACC_F_CMPLT_DATE'],
                $lastAccount['ACC_KODE_NASABAH'],
            );

            $lastAccount['ACC_DATETIME'] = date("Y-m-d H:i:s");
            $lastAccount['ACC_STS'] = 0; 
            $lastAccount['ACC_LOGIN'] = 0;
            $lastAccount['ACC_WPCHECK'] = 0;

            /** Insert New Record */
            return Database::insert("tb_racc", $lastAccount);

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    } 

    public static function isAllowToEdit(int $status): bool {
        if(in_array($status, [1, -1])) {
            JsonResponse([
                'success' => false,
                'message' => "Akun sedang dalam prosess verifikasi",
                'data' => []
            ]);
        }

        return true;
    }

    public static function urlTradingRule($filename) {
        return FileUpload::awsFile($filename);
    }

    public static function getAccountHistoryNote(int $idAcc): array  {
        try {
            global $db;
            $sqlGet = $db->query("
                SELECT 
                    tn.*
                FROM tb_note tn
                JOIN tb_racc tr ON (tr.ID_ACC = tn.NOTE_RACC)
                WHERE tr.ID_ACC = {$idAcc}
                ORDER BY tn.ID_NOTE DESC
            ");

            return $sqlGet->fetch_all(MYSQLI_ASSOC) ?? [];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function getAccountHistoryNoteReject(int $idAcc): array  {
        try {
            global $db;
            $sqlGet = $db->query("
                SELECT 
                    tn.*
                FROM tb_note tn
                JOIN tb_racc tr ON (tr.ID_ACC = tn.NOTE_RACC)
                WHERE tr.ID_ACC = {$idAcc}
                AND NOTE_TYPE IN('WP VER REJECT', 'BANK VERIFICATION REJECT')
                ORDER BY tn.ID_NOTE DESC
            ");

            return $sqlGet->fetch_all(MYSQLI_ASSOC) ?? [];

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return [];
        }
    }

    public static function getAccountHistoryLastNote(int $idAcc): array|bool  {
        try {
            global $db;
            $sqlGet = $db->query("
                SELECT 
                    tn.*
                FROM tb_note tn
                JOIN tb_racc tr ON (tr.ID_ACC = tn.NOTE_RACC)
                WHERE tr.ID_ACC = {$idAcc}
                ORDER BY tn.ID_NOTE DESC
                LIMIT 1
            ");

            return $sqlGet->fetch_assoc() ?? false;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    }

    public static function cddTypeArray() {
        return [
            self::$cddTypeStandard, 
            self::$cddTypeSederhana
        ];
    }

    public static function cddType(int $type) {
        switch($type) {
            case self::$cddTypeStandard: 
                return [
                    'text' => "Standart",
                    'html' => '<span class="badge bg-info">Standard</span>'
                ];

            case self::$cddTypeSederhana: 
                return [
                    'text' => "Sederhana",
                    'html' => '<span class="badge bg-info">Sederhana</span>'
                ];

            default: 
                return [
                    'text' => "Unknown",
                    'html' => '<span class="badge bg-dark">Unknown</span>'
                ];
        }
    }

    public static function wpCheckArray(): array {
        return [
            self::$statusWPCheckBankVerification,
            self::$statusWPCheckRegister,
            self::$statusWPCheckGoodFund,
            self::$statusWPCheckActive,
        ];
    }
    
    public static function wpCheckStatus(int $type) {
        switch($type) {
            case self::$statusWPCheckBankVerification: 
                return [
                    'text' => "Bank Verification",
                    'html' => '<span class="badge bg-secondary h-50 d-inline-block bg-opacity-15 text-white">Bank Verification</span>'
                ];

            case self::$statusWPCheckRegister: 
                return [
                    'text' => "Register",
                    'html' => '<span class="badge bg-success h-50 d-inline-block bg-opacity-15 text-white">Register</span>'
                ];

            case self::$statusWPCheckGoodFund: 
                return [
                    'text' => "GoodFund",
                    'html' => '<span class="badge bg-info h-50 d-inline-block bg-opacity-15 text-white">GoodFund</span>'
                ];

            case self::$statusWPCheckActive: 
                return [
                    'text' => "Active",
                    'html' => '<span class="badge bg-success h-50 d-inline-block bg-opacity-15 text-white">Active</span>'
                ];

            default: 
                return [
                    'text' => "Unknown",
                    'html' => '<span class="badge bg-dark">Unknown</span>'
                ];
        }
    }

    public static function SidValidation(string $number, string $dateOfBirth): bool {
        try {
            $separator = " - ";
            $parts = explode($separator, $number);

            if(count($parts) != 5) {
                return false;
            }
            
            /** validasi tipe investor */
            if(!in_array(strtoupper($parts[0]), self::$sidNumberPattern['tipe_investor'])) {
                return false;
            }

            /** validasi status investor */
            if(!in_array(strtoupper($parts[1]), self::$sidNumberPattern['status_investor'])) {
                return false;
            }

            // /** validasi tanggal dan bulan */
            // if(strlen($parts[2]) != 4 || $parts[2] != date("md", strtotime($dateOfBirth))) {
            //     return false;
            // }

            /** validasi trading id */
            if(strlen($parts[3]) != 6 || !preg_match('/^[a-zA-Z0-9]+$/', $parts[3])) {
                return false;
            }

            /** validasi nomor uniq */
            if(strlen($parts[4]) != 2 || !preg_match('/^[0-9]+$/', $parts[4])) {
                return false;
            }
            
            return true;

        } catch (Exception $e) {
            if(SystemInfo::isDevelopment()) {
                throw $e;
            }

            return false;
        }
    } 

    public static function isRequiredNpwp(int $cddType): bool {
        return $cddType == self::$cddTypeStandard;
    }
}