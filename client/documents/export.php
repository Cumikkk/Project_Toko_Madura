<?php 

use Config\Core\SystemInfo;
require_once(__DIR__ . "/../../config/setting.php");

use App\Models\FileUpload;
use App\Models\Helper;
use App\Models\ProfilePerusahaan;
use Dompdf\Dompdf;
use Dompdf\Options;

if(!isset($_GET['filename'])) {
	exit(json_encode([
		'success'	=> false,
		'message'	=> 'Invalid Request'
	]));
}

function parseHtmlText(string $filename, array $data) {
    try {
        global $db;
        extract($data, EXTR_OVERWRITE);
        ob_start();
        require(CRM_ROOT . "/documents/$filename.php");
        return ob_get_clean();

    } catch (Exception $e) {
        return false;
    }
}

try {
    $filename = Helper::form_input($_GET['filename'] ?? "-");
    if(!file_exists(CRM_ROOT . "/documents/{$filename}.php")) {
        exit(json_encode([
            'success'	=> false,
            'message'	=> 'Invalid Route'
        ]));
    }

    $profile_perusahaan = ProfilePerusahaan::get();
    $profile_perusahaan['setting_telp_pmbr'] = $setting_telp_pmbr ?? 0;

    $_GET['logo_pdf'] = SystemInfo::app('CLIENT_URL') . "/assets/images/logo-document";
    $_GET['profile_perusahaan'] = $profile_perusahaan;
    $_GET['company_name'] = $profile_perusahaan['COMPANY_NAME'];
    $_GET['company_address'] = $profile_perusahaan['OFFICE']['OFC_ADDRESS'] ?? "-";
    $_GET['wpb'] = ProfilePerusahaan::list_wpb() ?? [];
    $_GET['wpb_verifikator'] = ProfilePerusahaan::wpb_verifikator() ?? [];

    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    $dompdf = new Dompdf($options);
    $html = parseHtmlText($filename, [...$_GET, 'dompdf' => $dompdf]);
    $dompdf->loadHtml($html);
    $dompdf->render();
    $dompdf->stream("{$filename}.pdf", array("Attachment" => 0));
	// $output = $dompdf->output();

} catch (Exception $e) {
    throw $e;
}