<?php
    
    namespace App\Models;
    
    use Config\Core\Database;
    use Config\Core\SystemInfo;
    use App\Models\Account;
    use App\Models\Dpwd;
    use Exception;

    class Apuppt{

        public static $evNasParam = 'par';
        public static $evNasParamPrp = 'x';
        public static $eddParam = 'vl';
        public static $evaluasiCalonNasabahProp = 'prm';
        public static $ACCRCJTEVCNSP = [0, 1];
        public static $PENRISKBGPRP = array(
            "bg-dark text-white",
            "bg-secondary text-white",
            "bg-primary text-white",
            "bg-warning",
            "bg-success",
            "bg-info",
            "bg-danger",
            "bg-dark text-white",
            "bg-secondary"
        );


        public static function dataEvaluasiNasabah(string $mbr_id): array|bool {
            try {
                if(empty($mbr_id)) {
                    return false;
                }
                $db = Database::connect();
                $mbr_id = $db->real_escape_string($mbr_id);
                $sqlGet = $db->query('
                    SELECT
                        IFNULL(tb_racc.ACC_FULLNAME, tb_member.MBR_NAME) AS ACC_F_APP_PRIBADI_NAMA,
                        IFNULL(tb_racc.ACC_LOGIN, "-") AS ACC_LOGIN,
                        IFNULL(tb_racc.ACC_DATETIME, "-") AS ACC_DATETIME,
                        (
                            SELECT
                                CONCAT(tb_racctype.RTYPE_NAME, " ", tb_racctype.RTYPE_TYPE_AS)
                            FROM tb_racctype
                            WHERE tb_racctype.ID_RTYPE = tb_racc.ACC_TYPE
                            LIMIT 1
                        ) AS PRD,
                        IFNULL(
                            tb_racc.ACC_INITIALMARGIN,
                            IFNULL((
                                SELECT
                                    tb_dpwd.DPWD_AMOUNT_SOURCE
                                FROM tb_dpwd
                                WHERE tb_dpwd.DPWD_RACC = tb_racc.ID_ACC
                                AND tb_dpwd.DPWD_TYPE = "'.Dpwd::$typeDepositNewAccount.'"
                                LIMIT 1
                            ), 0)
                        ) AS ACC_INITIALMARGIN,
                        IFNULL(tb_racc.ACC_F_APP_KRJ_TYPE, "-") AS ACC_F_APP_KRJ_TYPE,
                        IFNULL(tb_racc.ACC_ADDRESS, tb_member.MBR_ADDRESS) AS ACC_F_APP_PRIBADI_ALAMAT,
                        IFNULL(tb_racc.ACC_ZIPCODE, tb_member.MBR_ZIP) AS ACC_F_APP_PRIBADI_ZIP,
                        IFNULL(tb_racc.ACC_NO_IDT, tb_member.MBR_NO_IDT) ACC_F_APP_PRIBADI_ID,
                        tb_racc.ACC_F_APP_FILE_IMG,
                        tb_racc.ACC_MBR,
                        tb_racc.ACC_DEMO,
                        tb_racc.ACC_F_APP_PRIBADI_NPWP,
                        tb_racc.ACC_TEMPAT_LAHIR AS ACC_F_APP_PRIBADI_TMPTLHR,
                        tb_racc.ACC_TANGGAL_LAHIR AS ACC_F_APP_PRIBADI_TGLLHR,
                        tb_racc.ACC_F_APP_FILE_FOTO,
                        tb_racc.ACC_F_APP_FILE_ID,
                        tb_racc.ACC_F_APP_FILE_IMG2,
                        IFNULL((
                            SELECT
                            tb_dpwd.DPWD_PIC
                            FROM tb_dpwd
                            WHERE tb_dpwd.DPWD_RACC = tb_racc.ID_ACC
                            LIMIT 1
                        ), "unknown-file.png") AS DPWD_PIC,
                        tb_racc.ID_ACC,
                        tb_member.MBR_ID,
                        (
                            SELECT
                                JSON_OBJECTAGG(
                                    tb_test.APU_RNGNSB,
                                    tb_test.APU_RNGNSB_VAL
                                )
                            FROM	(
                                SELECT
                                    tb_apuppt.APU_ACC,
                                    tb_apuppt.APU_RNGNSB,
                                    tb_apuppt.APU_RNGNSB_VAL
                                FROM tb_apuppt
                            ) AS tb_test
                            WHERE tb_test.APU_ACC = tb_racc.ID_ACC
                            LIMIT 1
                        )	AS JSN_DT_APU,
                        (
                            SELECT
                                JSON_OBJECTAGG(
                                    tb_test.APU_RNGNSB,
                                    tb_test.ID_APU
                                )
                            FROM	(
                                SELECT
                                    tb_apuppt.APU_ACC,
                                    tb_apuppt.APU_RNGNSB,
                                    tb_apuppt.ID_APU
                                FROM tb_apuppt
                            ) AS tb_test
                            WHERE tb_test.APU_ACC = tb_racc.ID_ACC
                            LIMIT 1
                        )	AS JSN_ID_APU,
                        IFNULL((
                            SELECT
                                0
                            FROM tb_rangetype
                            WHERE NOT EXISTS(
                                SELECT 
                                    1 
                                FROM tb_apuppt 
                                WHERE tb_apuppt.APU_ACC = tb_racc.ID_ACC 
                                AND tb_apuppt.APU_RNGNSB = tb_rangetype.ID_RATYP
                            )
                            LIMIT 1
                        ), 1) AS APU_CMPLT,
                        (	
                            SELECT
                                tb_apuppt.APU_ACC
                            FROM tb_apuppt
                            WHERE tb_apuppt.APU_ACC = tb_racc.ID_ACC
                            LIMIT 1
                        ) AS APU_X
                    FROM tb_member
                    LEFT JOIN tb_racc
                    ON(tb_member.MBR_ID = tb_racc.ACC_MBR
                    AND tb_racc.ACC_DERE = 1)
                    WHERE MD5(MD5(tb_racc.ID_ACC)) = "'.$mbr_id.'"
                    LIMIT 1
                ');
                return $sqlGet->fetch_assoc() ?? false;       

            } catch (Exception $e) {
                if(SystemInfo::isDevelopment()) {
                    throw $e;
                }

                return false;
            }
        }

        public static function evaluasiCalonNasabah(string $mbr_id): array|bool {
            try {
                if(empty($mbr_id)) {
                    return false;
                }
                $db = Database::connect();
                $mbr_id = $db->real_escape_string($mbr_id);
                $sqlGet = $db->query("
                    SELECT
                        IFNULL(tb_racc.ACC_FULLNAME, tb_member.MBR_NAME) AS ACC_F_APP_PRIBADI_NAMA,
                        IFNULL(tb_racc.ACC_LOGIN, '-') AS ACC_LOGIN,
                        IFNULL(tb_racc.ACC_DATETIME, '-') AS ACC_DATETIME,
                        (
                            SELECT
                                CONCAT(tb_racctype.RTYPE_NAME, ' ', tb_racctype.RTYPE_TYPE_AS)
                            FROM tb_racctype
                            WHERE tb_racctype.ID_RTYPE = tb_racc.ACC_TYPE
                            LIMIT 1
                        ) AS PRD,
                        IFNULL(
                            tb_racc.ACC_INITIALMARGIN,
                            IFNULL((
                                SELECT
                                    tb_dpwd.DPWD_AMOUNT_SOURCE
                                FROM tb_dpwd
                                WHERE tb_dpwd.DPWD_RACC = tb_racc.ID_ACC
                                AND tb_dpwd.DPWD_TYPE = '".Dpwd::$typeDepositNewAccount."'
                                LIMIT 1
                            ), 0)
                        ) AS ACC_INITIALMARGIN,
                        IFNULL(tb_racc.ACC_F_APP_KRJ_TYPE, '-') AS ACC_F_APP_KRJ_TYPE,
                        IFNULL(tb_racc.ACC_ADDRESS, tb_member.MBR_ADDRESS) AS ACC_F_APP_PRIBADI_ALAMAT,
                        IFNULL(tb_racc.ACC_ZIPCODE, tb_member.MBR_ZIP) AS ACC_F_APP_PRIBADI_ZIP,
                        IFNULL(tb_racc.ACC_NO_IDT, tb_member.MBR_NO_IDT) ACC_F_APP_PRIBADI_ID,
                        tb_racc.ACC_F_APP_FILE_IMG,
                        tb_racc.ACC_F_APP_FILE_FOTO,
                        tb_racc.ACC_F_APP_FILE_ID,
                        tb_racc.ACC_F_APP_FILE_IMG2,
                        IFNULL((
                            SELECT
                            tb_dpwd.DPWD_PIC
                            FROM tb_dpwd
                            WHERE tb_dpwd.DPWD_RACC = tb_racc.ID_ACC
                            LIMIT 1
                        ), 'unknown-file.png') AS DPWD_PIC,
                        tb_member.MBR_ID,
                        tb_racc.ACC_DEMO

                    FROM tb_member
                    LEFT JOIN tb_racc
                    ON(tb_member.MBR_ID = tb_racc.ACC_MBR
                    AND tb_racc.ACC_DERE = 1)
                    WHERE (MD5(MD5(tb_member.MBR_ID)) = '{$mbr_id}' OR tb_member.MBR_ID = '{$mbr_id}')
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

        public static function get_prov_nik($kdp, $purp){
            $db = Database::connect();
            
            try {
                if(!is_null($kdp)){
                    if(is_numeric($kdp)){
                        $SQL_PROV = mysqli_query($db,'
                            SELECT
                                tb_province_code.PRV_NAME
                            FROM tb_province_code
                            WHERE tb_province_code.PRV_CODE = '.substr($kdp, 0, 2).'
                            LIMIT 1
                        ');
                        if($SQL_PROV && mysqli_num_rows($SQL_PROV) > 0){
                            $RSLT_PROV = mysqli_fetch_assoc($SQL_PROV);
                            if($purp == 'name'){
                                return '('.$RSLT_PROV["PRV_NAME"].')';
                            }else if($purp == 'match'){
                                return $RSLT_PROV["PRV_NAME"];
                            }else{ return false; }
                        }else{ return false; }
                    }else{ return false; }
                }else{ return false; }  

            } catch (Exception $e) {
                if(SystemInfo::isDevelopment()) {
                    throw $e;
                }

                return false;
            }
        }

        public static function get_prov($kdp, $purp){
            $db = Database::connect();
            try {
                if(!is_null($kdp)){
                    $SQL_PROV = mysqli_query($db,'
                        SELECT
                            tb_kodepos.KDP_PROV
                        FROM tb_kodepos
                        WHERE tb_kodepos.KDP_POS = '.$kdp.'
                        LIMIT 1
                    ');
                    if($SQL_PROV && mysqli_num_rows($SQL_PROV) > 0){
                        $RSLT_PROV = mysqli_fetch_assoc($SQL_PROV);
                        if($purp == 'name'){
                            return '('.$RSLT_PROV["KDP_PROV"].')';
                        }else if($purp == 'match'){
                            return $RSLT_PROV["KDP_PROV"];
                        }else{ return false; }
                    }else{ return false; }
                }else{ return false; }

            } catch (Exception $e) {
                if(SystemInfo::isDevelopment()) {
                    throw $e;
                }

                return false;
            }
        }

        public static function ddttotChk($nama, $niknum, $purp){
            try {
                // use PhpOffice\PhpSpreadsheet\Spreadsheet;
                // use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

                $inputFileName = 'assets/ddtot.xls';
                //code...
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
                $testAgainstFormats = [
                    \PhpOffice\PhpSpreadsheet\IOFactory::READER_XLS,
                    \PhpOffice\PhpSpreadsheet\IOFactory::READER_HTML,
                ];

                $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
                /**  Create a new Reader of the type that has been identified  **/
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                /**  Load $inputFileName to a Spreadsheet Object  **/
                $spreadsheet = $reader->load($inputFileName);
                // print_r($spreadsheet->getActiveSheet()->getHighestRow());
                $B = [];
                $A = [];
                foreach($spreadsheet->getActiveSheet()->getRowIterator() as $KEY1 => $row) {
                    foreach ($row->getCellIterator() as $key => $value) {
                        if($key == 'A'){
                            $A[] = $value->getValue();
                        }
                        if($key == 'B'){
                            $B[] = $value->getValue();
                        }
                    }
                }
            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                throw $e;
            }
            $jumPtnsi = 0;
            $name = preg_replace('/^\s+|\s+$/', '', strtoupper($nama));
            $ARR_EQSTR = array_map(function($val){ return strtoupper(($val ?? '')); }, $A);
            $ARR_EXPLD = array_map(function($val){ return preg_replace('/^\s+|\s+$/', '', explode('ALIAS', $val)); }, $ARR_EQSTR);
            $name_srch = array_keys(array_filter(array_slice($ARR_EXPLD, 1), function($ARR) use ($name){
                foreach($ARR as $ARR_KEY => $ARR_VAL){
                    return (array_search($name,$ARR) !== FALSE) ? ["FKEY" => $ARR_KEY, "SKEY" => array_search($name,$ARR)] : [];
                }
            }));
            $rsltA = (count($name_srch) == 0) ? NULL : $name_srch[0];
            if(!is_null($rsltA)){ $jumPtnsi += 1; }

            $nik        = $niknum;
            $ARR_B      = array_map(function($val){ return strtoupper(($val ?? '')); }, $B);
            $idnum_srch = array_keys(preg_grep("/(([^0-9]|^)$nik([^0-9]|$)|([^A-Z0-9]|^)$nik([^A-Z0-9]|$))/", array_slice($ARR_B, 1)));
            $rsltB      = (count($idnum_srch) == 0) ? NULL : $idnum_srch[0];
            if(!is_null($rsltB)){ $jumPtnsi += 1; }

            if($purp == 'jumlah'){
                return $jumPtnsi;
            }else if($purp == 'link'){
                return '&nm='.base64_encode(($nama ?? 'nan')).'&nms='.base64_encode((!is_null($rsltA)) ? $rsltA : 'nan').'&nkm='.base64_encode(($niknum ?? 'nan')).'&nks='.base64_encode((!is_null($rsltB)) ? $rsltB : 'nan').'';
            }else{ return NULL; }

        }

        public static function dttotTabel(){
            try {
                // use PhpOffice\PhpSpreadsheet\Spreadsheet;
                // use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

                $inputFileName = 'assets/ddtot.xls';
                //code...
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
                $testAgainstFormats = [
                    \PhpOffice\PhpSpreadsheet\IOFactory::READER_XLS,
                    \PhpOffice\PhpSpreadsheet\IOFactory::READER_HTML,
                ];

                $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($inputFileName);
                /**  Create a new Reader of the type that has been identified  **/
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                /**  Load $inputFileName to a Spreadsheet Object  **/
                $spreadsheet = $reader->load($inputFileName);
                // print_r($spreadsheet->getActiveSheet()->getHighestRow());
                $i = 1;
                $thead = null;
                $tbody = [];
                foreach($spreadsheet->getActiveSheet()->getRowIterator() as $KEY1 => $row) {
                    // if($i == 5) break;
                    $tbody[] = $row;
                    foreach ($row->getCellIterator() as $key => $value) {
                        $thead = $row->getCellIterator();
                    }
                    // if($i == 5){ break; }
                    $i++;
                }

                return $tbody;

            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                // throw $e;
                return false;
            }
        }

        public static function srchEvaluasiCalonNasabah(string $mbr_id): array|bool {
            try {
                if(empty($mbr_id)) {
                    return false;
                }
                $db = Database::connect();
                $mbr_id = $db->real_escape_string($mbr_id);
                $sqlGet = $db->query('
                    SELECT
                        tb_apuppt_evcannas.EVCAN_MBR,
                        tb_apuppt_evcannas.EVCAN_CONF,
                        tb_apuppt_evcannas.EVCAN_DATETIME,
                        IFNULL(tb_racc.ACC_FULLNAME, tb_member.MBR_NAME) AS ACC_F_APP_PRIBADI_NAMA,
                        IFNULL(tb_racc.ACC_LOGIN, "-") AS ACC_LOGIN,
                        IFNULL(tb_racc.ACC_DATETIME, "-") AS ACC_DATETIME,
                        (
                            SELECT
                                CONCAT(tb_racctype.RTYPE_NAME, " ", tb_racctype.RTYPE_TYPE_AS)
                            FROM tb_racctype
                            WHERE tb_racctype.ID_RTYPE = tb_racc.ACC_TYPE
                            LIMIT 1
                        ) AS PRD,
                        IFNULL(
                            tb_racc.ACC_INITIALMARGIN,
                            IFNULL((
                                SELECT
                                    tb_dpwd.DPWD_AMOUNT_SOURCE
                                FROM tb_dpwd
                                WHERE tb_dpwd.DPWD_RACC = tb_racc.ID_ACC
                                AND tb_dpwd.DPWD_TYPE = "'.Dpwd::$typeDepositNewAccount.'"
                                LIMIT 1
                            ), 0)
                        ) AS ACC_INITIALMARGIN,
                        IFNULL(tb_racc.ACC_F_APP_KRJ_TYPE, "-") AS ACC_F_APP_KRJ_TYPE,
                        IFNULL(tb_racc.ACC_ADDRESS, tb_member.MBR_ADDRESS) AS ACC_F_APP_PRIBADI_ALAMAT,
                        IFNULL(tb_racc.ACC_ZIPCODE, tb_member.MBR_ZIP) AS ACC_F_APP_PRIBADI_ZIP,
                        IFNULL(tb_racc.ACC_NO_IDT, tb_member.MBR_NO_IDT) ACC_F_APP_PRIBADI_ID,
                        tb_racc.ACC_F_APP_FILE_IMG,
                        tb_racc.ACC_F_APP_FILE_FOTO,
                        tb_racc.ACC_F_APP_FILE_ID,
                        tb_racc.ACC_F_APP_FILE_IMG2,
                        IFNULL((
                            SELECT
                            tb_dpwd.DPWD_PIC
                            FROM tb_dpwd
                            WHERE tb_dpwd.DPWD_RACC = tb_racc.ID_ACC
                            LIMIT 1
                        ), "unknown-file.png") AS DPWD_PIC,
                        (
                            SELECT
                                JSON_OBJECTAGG(tb_als.EVCAN_TYPE, tb_als.EVCAN_VAL)
                            FROM tb_apuppt_evcannas tb_als
                            WHERE tb_als.EVCAN_MBR = tb_apuppt_evcannas.EVCAN_MBR
                            LIMIT 1
                        ) AS JSON_VL
                    FROM tb_apuppt_evcannas
                    JOIN tb_member ON(tb_member.MBR_ID = tb_apuppt_evcannas.EVCAN_MBR)
                    LEFT JOIN tb_racc ON(tb_racc.ACC_MBR = tb_apuppt_evcannas.EVCAN_MBR AND tb_apuppt_evcannas.EVCAN_MBR = tb_member.MBR_ID AND tb_racc.ACC_DERE = 1)
                    WHERE MD5(MD5(MD5(tb_apuppt_evcannas.EVCAN_MBR))) = "'.$mbr_id.'"
                    LIMIT 1
                ');
                return $sqlGet->fetch_assoc() ?? false;       

            } catch (Exception $e) {
                if(SystemInfo::isDevelopment()) {
                    throw $e;
                }

                return false;
            }
        }

        public static function checkRangeId(string $rid): array|bool {
            try {
                if(empty($rid)) {
                    return false;
                }
                $db = Database::connect();
                $rid = $db->real_escape_string($rid);
                $sqlGet = $db->query('
                    SELECT
                        *
                    FROM tb_range
                    WHERE (tb_range.ID_RNG = "'.$rid.'" OR MD5(MD5(tb_range.ID_RNG)) = "'.$rid.'")
                ');
                return $sqlGet->fetch_assoc() ?? false;       

            } catch (Exception $e) {
                if(SystemInfo::isDevelopment()) {
                    throw $e;
                }

                return false;
            }
        }

        public static function checkTypeId(string $mtid): array|bool {
            try {
                if(empty($mtid)) {
                    return false;
                }
                $db = Database::connect();
                $mtid = $db->real_escape_string($mtid);
                $sqlGet = $db->query('
                    SELECT
                        *
                    FROM tb_rangetype
                    WHERE (tb_rangetype.ID_RATYP = "'.$mtid.'" OR MD5(MD5(tb_rangetype.ID_RATYP)) = "'.$mtid.'")
                ');
                return $sqlGet->fetch_assoc() ?? false;       

            } catch (Exception $e) {
                if(SystemInfo::isDevelopment()) {
                    throw $e;
                }

                return false;
            }
        }

        public static function checkRangeTypeId(string $id): array|bool {
            try {
                if(empty($id)) {
                    return false;
                }
                $db = Database::connect();
                $id = $db->real_escape_string($id);
                $sqlGet = $db->query('
                    SELECT
                        *
                    FROM tb_rangetype
                    WHERE (tb_rangetype.ID_RATYP = "'.$id.'" OR MD5(MD5(tb_rangetype.ID_RATYP)) = "'.$id.'")
                ');
                return $sqlGet->fetch_assoc() ?? false;       

            } catch (Exception $e) {
                if(SystemInfo::isDevelopment()) {
                    throw $e;
                }

                return false;
            }
        }

        public static function checkRangeNsbhId(string $id): array|bool {
            try {
                if(empty($id)) {
                    return false;
                }
                $db = Database::connect();
                $id = $db->real_escape_string($id);
                $sqlGet = $db->query('
                    SELECT
                        *
                    FROM tb_rangensb
                    WHERE (tb_rangensb.ID_NSBR = "'.$id.'" OR MD5(MD5(tb_rangensb.ID_NSBR)) = "'.$id.'")
                ');
                return $sqlGet->fetch_assoc() ?? false;       

            } catch (Exception $e) {
                if(SystemInfo::isDevelopment()) {
                    throw $e;
                }

                return false;
            }
        }

        
        public static function returnSelectString($vl = false){
            return (($vl) ? 'selected' : false);
        }
        
        public static function stringAligner($str = ''){
            return strtoupper(trim($str));
        }

        public static function ret_dat($dat_comp, $dat_nas = 0){
            if(!is_null($dat_comp) && $dat_nas > 0){
                $comp_value = preg_replace( '/[^\d+-]/', '',$dat_comp);
                $comp_sign  = preg_replace( '/([^><])+$/', '', $dat_comp );
                if(strpos($comp_value, '-') > 0){
                    $ARR_VAL = explode("-",$comp_value);
                    if(count($ARR_VAL) == 2){
                        if((int)$dat_nas > (int)$ARR_VAL[0] && (int)$dat_nas <= (int)$ARR_VAL[1]){
                            return true;
                        }else{ return false; }
                    }else{ return false; }
                }else if(strpos($comp_value, '-') == false && $comp_sign == '<'){
                    if((int)$dat_nas <= (int)$comp_value){
                        return true;
                    }else{ return false; }
                }else if(strpos($comp_value, '-') == false && $comp_sign == '>'){
                    if((int)$dat_nas > (int)$comp_value){
                        return true;
                    }else{ return false; }
                }else { return false; }
            }else{ return false; }
        }

        public static function matchingToRacc(string $id, string $opt_val, string $typ): string|bool {
            try {
                if(empty($id)) {
                    return false;
                }
                $db = Database::connect();
                $id = $db->real_escape_string($id);

                $ID_CHECK = Account::realAccountDetail($id);
                if(!$ID_CHECK){ return false; }

                switch ($typ) {
                    case 'Risiko Daftar Produk Investasi':
                        $prd_name = (strtoupper(($ID_CHECK["RTYPE_TYPE_AS"] ?? '')) == "MULTILATERAL" ? $ID_CHECK["RTYPE_TYPE_AS"] : $ID_CHECK["RTYPE_TYPE_AS"].' - '.$ID_CHECK["RTYPE_TYPE"]);
                        return self::returnSelectString((self::stringAligner($prd_name) == self::stringAligner($opt_val)));
                        break;

                    case 'Risiko Besaran Investasi Awal':
                        return self::returnSelectString(self::ret_dat($opt_val ,$ID_CHECK["ACC_INITIALMARGIN"]));
                        break;

                    case 'Risiko Metode Pembiayaan Investasi':
                        $DPWPD = Account::getDepositNewAccount_data($ID_CHECK["ID_ACC"]);
                        if(!$DPWPD){ return false; }

                        return self::returnSelectString(((!empty($DPWPD["DPWD_PIC"])) && self::stringAligner($opt_val) == self::stringAligner("Bank Transfer")));
                        break;

                    case 'Risiko Tipe Nasabah':
                        $ARR_BANK = json_decode(($ID_CHECK["MBR_BKJSN"] ?? '[]'), true);
                        $ARR_FLTR = array_filter($ARR_BANK, function($VAL, $key) use ($ID_CHECK, $opt_val){
                            return (($VAL["MBANK_HOLDER"] == $ID_CHECK["ACC_FULLNAME"]) && self::stringAligner($opt_val) == self::stringAligner("Perseorangan"));
                        }, ARRAY_FILTER_USE_BOTH);
                        return self::returnSelectString((count($ARR_FLTR)));
                        break;

                    case 'Risiko Profesi/Pekerjaan Nasabah':
                        $pkrj_name = $ID_CHECK["ACC_F_APP_KRJ_TYPE"];
                        return self::returnSelectString((self::stringAligner($pkrj_name) == self::stringAligner($opt_val)));
                        break;

                    case 'Risiko Asal Daerah Nasabah':
                        $daerah_name = self::get_prov($ID_CHECK["ACC_ZIPCODE"], "match");
                        return self::returnSelectString((self::stringAligner($daerah_name) == self::stringAligner($opt_val)));
                        break;

                    case 'Delivery Channel':
                        return false;
                        break;
                    
                    default:
                        return false;
                    break;
                }

                return false;

            } catch (Exception $e) {
                if(SystemInfo::isDevelopment()) {
                    throw $e;
                }

                return false;
            }
        }
        
        public static function get_lv_range($num_range): string|bool {
            try {
                if(empty($num_range)) {
                    return false;
                }
                $db = Database::connect();

                $SQL_RANGE = mysqli_query($db,'
                    SELECT
                        tb_range.RNG_LEVEL
                    FROM tb_range
                    WHERE tb_range.RNG_TYPE = 2
                    AND '.$num_range.' BETWEEN tb_range.RNG_MIN AND CAST(CASE WHEN tb_range.RNG_MAX = -1 THEN ~0 ELSE tb_range.RNG_MAX END AS UNSIGNED)
                    LIMIT 1
                ');
                if($SQL_RANGE && mysqli_num_rows($SQL_RANGE) > 0){
                    $RSLT_RANGE = mysqli_fetch_assoc($SQL_RANGE);
                    return $RSLT_RANGE["RNG_LEVEL"];    
                }
                return false;

            } catch (Exception $e) {
                if(SystemInfo::isDevelopment()) {
                    throw $e;
                }

                return false;
            }
        }

        public static function thrplce($str, $ord){
            $patt = ($ord == 3) ? "/(risiko\s+)/i" : "/(risiko\s+|\s+nasabah)/i";
            return preg_replace($patt, '', $str);
        }

        public static function comp($ordr, $nasval, $compval){
            if($ordr == 1){
                $pn = preg_replace('/[^0-9]+/', '', $nasval);
                $pc = preg_replace('/[^0-9]+/', '', $compval);
                $dc = true;
                if($pn == $pc){
                    return $compval;
                    $dc = false; 
                }else if((!in_array($pn, [1, 2]))){
                    if($pn >= $pc){
                        return $compval;
                    }else{ return NULL; }
                }
                // return ($pn == $pc) ? $compval : (($compval != 0) ? $compval : NULL);
            }elseif($ordr == 2){
                $nasval2 = preg_replace('/[^0-9]/', '', $nasval);
                if(!is_null($compval) && $nasval2 >= 0){
                    $comp_value = preg_replace( '/[^\d+-]/', '',$compval);
                    $comp_sign  = preg_replace( '/([^><])+/', '', $compval );
                    if(strpos($comp_value, '-') > 0){
                        $ARR_VAL = explode("-",$comp_value);
                        if(count($ARR_VAL) == 2){
                            if((int)$nasval2 > (int)$ARR_VAL[0] && (int)$nasval2 <= (int)$ARR_VAL[1]){
                                return $compval;
                            }else{ return NULL; }
                        }else{ return NULL; }
                    }else if(strpos($comp_value, '-') == false && $comp_sign == '<'){
                        if((int)$nasval2 <= (int)$comp_value){
                            return $compval;
                        }else{ return NULL; }
                    }else if(strpos($comp_value, '-') == false && $comp_sign == '>'){
                        if((int)$nasval2 > (int)$comp_value){
                            return $compval;
                        }else{ return NULL; }
                    }else { return NULL; }
                }else{ return NULL; }
                
            }else{ return NULL; }
        }

        public static function getHstrId(): string|bool {
            try {
                $db = Database::connect();
                $sqlGet = $db->query("
                    SELECT 
                        IFNULL(MAX(tb_apuppt_edd.ADD_HSTRID) + 1, 1) AS XD 
                    FROM tb_apuppt_edd 
                    LIMIT 1
                ");
                return $sqlGet->fetch_assoc()["XD"] ?? false;       

            } catch (Exception $e) {
                if(SystemInfo::isDevelopment()) {
                    throw $e;
                }

                return false;
            }
        }

        public static function getEddData($edd_id): array|bool {
            try {
                if(empty($edd_id)) {
                    return false;
                }
                $db = Database::connect();
                $sqlGet = $db->query("
                    SELECT
                        JSON_OBJECTAGG(tb_apuppt_edd.ADD_TYP, tb_apuppt_edd.ADD_VAL) AS EDD_DT,
                        (
                            SELECT
                                JSON_OBJECTAGG(tb_equity.EQTY_LOGIN, tb_equity.EQTY_VAL)
                            FROM tb_equity
                            WHERE tb_equity.EQTY_APU_ID = tb_apuppt_edd.ADD_HSTRID
                            LIMIT 1
                        ) AS EQT_DT,
                        (
                            SELECT
                                JSON_ARRAYAGG(tb_equity.EQTY_LOGIN)
                            FROM tb_equity
                            WHERE tb_equity.EQTY_APU_ID = tb_apuppt_edd.ADD_HSTRID
                            LIMIT 1
                        ) AS JSN_EQT,
                        (
                            SELECT
                                MD5(MD5(tb_racc.ID_ACC))
                            FROM tb_racc
                            WHERE tb_racc.ACC_MBR = tb_apuppt_edd.ADD_MBR
                            AND tb_racc.ACC_DERE = 1
                            AND tb_racc.ACC_WPCHECK = 6
                            AND (tb_racc.ACC_LOGIN != '0' AND tb_racc.ACC_LOGIN IS NOT NULL)
                            LIMIT 1	
                        ) AS ID_ACC,
                        (
                            SELECT
                                (
                                    SELECT
                                        JSON_ARRAYAGG(
                                            JSON_OBJECT(
                                                'T_DESC', tb_range_edtype.EDTYPE_DESC,
                                                'N_DESC', tb_range_edd.EDD_DESC,
                                                'TNGRIS', tb_range_edd.EDD_LV
                                            )
                                        )
                                    FROM tb_range_edtype
                                    JOIN tb_range_edd
                                    ON(tb_range_edtype.ID_EDTYPE = tb_range_edd.EDD_TYPE)
                                    WHERE JSON_CONTAINS(JSON_ARRAYAGG(tb_rplc.ADD_VAL), tb_range_edd.ID_EDD)
                                ) AS JSN_DT
                            FROM tb_apuppt_edd tb_rplc
                            WHERE tb_rplc.ADD_HSTRID = tb_apuppt_edd.ADD_HSTRID 
                        ) AS JSN_DT_PDF,
                        tb_apuppt_edd.ADD_ARF,
                        tb_apuppt_edd.ADD_RKM,
                        tb_apuppt_edd.ADD_DATTIME
                    FROM tb_apuppt_edd
                    WHERE MD5(MD5(MD5(tb_apuppt_edd.ADD_HSTRID))) = '$edd_id'
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

    }