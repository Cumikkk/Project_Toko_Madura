<?php 

use Config\Core\SystemInfo;
require_once(__DIR__ . "/../../config/setting.php");

use App\Models\Account;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\Helper;

if(!isset($_GET['filename'])) {
	exit(json_encode([
		'success'	=> false,
		'message'	=> 'Invalid Request'
	]));
}

// function parseHtmlText(string $filename, array $data) {
//     try {
//         global $db;
//         extract($data, EXTR_OVERWRITE);
//         ob_start();
//         require(__DIR__ . "/$filename.php");
//         return ob_get_clean();

//     } catch (Exception $e) {
//         return false;
//     }
// }

function parseHtmlText(string $flname, array $data) {
    try {
        global $db;
        extract($data, EXTR_OVERWRITE);
        $filename = $flname;
        ob_start();
        require(__DIR__ . "/$flname.php");
        return ob_get_clean();

    } catch (Exception $e) {
        return false;
    }
}

function DOMinnerHTML(DOMNode $element) { 
    $innerHTML = ""; 
    $children  = $element->childNodes;

    foreach ($children as $child) 
    { 
        $innerHTML .= $element->ownerDocument->saveHTML($child);
    }

    return $innerHTML; 
} 

try {
    $cdd = Helper::form_input($_GET['cdd'] ?? "-");
    $filename = Helper::form_input($_GET['filename'] ?? "-");
    if(!file_exists(__DIR__ . "/{$filename}.php") && $filename != 'all') {
        exit(json_encode([
            'success'	=> false,
            'message'	=> 'Invalid Route'
        ]));
    }

    $profile_perusahaan = App\Models\ProfilePerusahaan::get();
    $profile_perusahaan['setting_telp_pmbr'] = $setting_telp_pmbr ?? 0;

    $_GET['logo_pdf'] = SystemInfo::app('CLIENT_URL') . "/assets/images/logo-document.png";
    $_GET['profile'] = $profile_perusahaan;
    $_GET['company_name'] = $web_name ?? SystemInfo::app('APP_NAME');
    $_GET['company_address'] = $address_company ?? "-";
    $_GET['wpb'] = App\Models\ProfilePerusahaan::list_wpb() ?? [];
    $_GET['wpb_verifikator'] = App\Models\ProfilePerusahaan::wpb_verifikator() ?? [];

    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    $dompdf = new Dompdf($options);

    if($filename == 'all'){
        $appender = '';
        foreach (App\Models\Documents::$ALL_DOCS as $ky => $vl) {
            $filename  = $ky;
            $temp_html = parseHtmlText($filename, [...$_GET, 'dompdf' => $dompdf]);
            $loadDOM = new DOMDocument();
            $loadDOM->loadHTML($temp_html, LIBXML_NOERROR);
            $appender .= DOMinnerHTML($loadDOM->getElementsByTagName('body')->item(0));
            $appender .= '<div class="break-before"></div>';
        }
        $html = '
            <!DOCTYPE html>
            <html>
                <head>
                    '.file_get_contents(__DIR__.'/style.php').'
                </head>
                <body>
                    '.str_replace(["â", "â", "â"], ['"', '"', '&#10004;'], $appender).'
                </body>
            </html>
        ';

    }else{ $html = parseHtmlText($filename, [...$_GET, 'dompdf' => $dompdf]); }

    $dompdf->loadHtml($html);
    $dompdf->render();
    $dompdf->stream("{$filename}.pdf", array("Attachment" => 0));
	// $output = $dompdf->output();

} catch (Exception $e) {
    throw $e;
}