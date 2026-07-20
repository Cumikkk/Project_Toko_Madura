<?php
use App\Factory\FileUploadFactory;
use App\Models\FileUpload;
use App\Models\Helper;
use App\Models\Ticket;
use Config\Core\Database;

$ticketCode = Helper::form_input($_POST['code'] ?? "");
$message = Helper::form_input($_POST['message']);
$message = str_replace(["\r\n", "\r"], "\n", (string)$message);
$message = preg_replace('/\n$/', '', $message);
$messageTrimmed = trim($message);

/** check code */
$ticket = Ticket::findByCode($ticketCode);
if(!$ticket) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Code",
        'data' => []
    ]);
}

/** Check status */
if($ticket['TICKET_STS'] != -1) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Status",
        'data' => []
    ]);
}

/** check Attachment */
$isSendImages = !empty($_FILES['attachment']) && $_FILES['attachment']['error'] == 0;
if($isSendImages) {
    $uploadAttachment = FileUploadFactory::aws()->upload_single($_FILES['attachment'], "ticket_attachment");
    // $uploadAttachment = FileUploadFactory::aws()->upload_single($_FILES['attachment'], array_merge(FileUpload::PNG, FileUpload::JPG, FileUpload::JPEG, FileUpload::WEBP));
    if(!is_array($uploadAttachment) || !array_key_exists("filename", $uploadAttachment)) {
        JsonResponse([
            'success' => false,
            'message' => $uploadAttachment ?? "Upload file gagal",
            'data' => []
        ]);
    }

    /** Insert Attachment */
    $insertAttachment = Database::insert("tb_ticket_detail", [
        'TDETAIL_TCODE' => $ticketCode,
        'TDETAIL_FROM' => $user['MBR_ID'],
        'TDETAIL_TYPE' => "member",
        'TDETAIL_CONTENT_TYPE' => "image",
        'TDETAIL_CONTENT' => FileUploadFactory::aws()->awsFile($uploadAttachment['filename']),
        'TDETAIL_DATETIME' => date("Y-m-d H:i:s")
    ]);

    if(!$insertAttachment) {
        JsonResponse([
            'success' => false,
            'message' => "File gagal disimpan",
            'data' => []
        ]);
    }
}

/** Insert message */
if($messageTrimmed !== '') {  
    $insert = Database::insert("tb_ticket_detail", [
        'TDETAIL_TCODE' => $ticketCode,
        'TDETAIL_FROM' => $user['MBR_ID'],
        'TDETAIL_TYPE' => "member",
        'TDETAIL_CONTENT_TYPE' => "message",
        'TDETAIL_CONTENT' => $message,
        'TDETAIL_DATETIME' => date("Y-m-d H:i:s")
    ]);
    
    if(!$insert) {
        JsonResponse([
            'success' => false,
            'message' => "Pesan gagal disimpan",
            'data' => []
        ]);
    }
}

if(!$isSendImages && $messageTrimmed === '') {
    JsonResponse([
        'success' => false,
        'message' => "Tulis pesan atau lampirkan gambar untuk dikirim",
        'data' => []
    ]);
}

JsonResponse([
    'success' => true,
    'message' => "Berhasil",
    'data' => []
]);