<?php
use App\Factory\FileUploadFactory;
use App\Models\FileUpload;
use App\Models\Helper;
use App\Models\User;
use Config\Core\Database;
use Config\Core\EmailSender;

$data = Helper::getSafeInput($_POST);
if(empty($data['email_to'])) {
    JsonResponse([
        'success' => false,
        'message' => "Recipient email is required",
        'data' => []
    ]);
}

$userdata = User::findbyEmail($data['email_to']);
if(!$userdata) {
    JsonResponse([
        'success' => false,
        'message' => "Recipient email not found",
        'data' => []
    ]);
}

if(empty($data['subject'])) {
    JsonResponse([
        'success' => false,
        'message' => "Email subject is required",
        'data' => []
    ]);
}

$content = $_POST['content'] ?? '';
if(!$content || $content == '') {
    JsonResponse([
        'success' => false,
        'message' => "Email content is required",
        'data' => []
    ]);
}

$attachments = [];
if(!empty($_FILES['attachments'])) {
    foreach($_FILES['attachments']['tmp_name'] as $key => $tmpName) {
        if($_FILES['attachments']['error'][$key] === 0) {
            $uploadFile = FileUploadFactory::aws()->upload_single([
                'name' => $_FILES['attachments']['name'][$key],
                'tmp_name' => $tmpName,
                'type' => $_FILES['attachments']['type'][$key],
                'size' => $_FILES['attachments']['size'][$key],
                'error' => $_FILES['attachments']['error'][$key],
            ]);
    
            $attachmentObject = [
                'original_name' => $_FILES['attachments']['name'][$key],
                'saved_name' => '',
                'link' => '',
                'type' => $_FILES['attachments']['type'][$key],
                'size' => $_FILES['attachments']['size'][$key],
                'path' => $tmpName
            ];
    
            if(is_array($uploadFile) && array_key_exists('filename', $uploadFile)) {
                $attachmentObject['saved_name'] = $uploadFile['filename'];
                $attachmentObject['link'] = FileUploadFactory::aws()->awsFile($uploadFile['filename']);
            } 
    
            $attachments[] = $attachmentObject;
        }
    }
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_begin_transaction($db);

/** insert log */
$insert = Database::insert("tb_log_sendemail", [
    'REQUEST_BY' => $user['ADM_ID'],
    'RECIPIENT' => $data['email_to'],
    'SUBJECT' => $data['subject'],
    'CONTENT' => base64_encode($content),
    'DATETIME' => date("Y-m-d H:i:s"),
    'ATTACHMENT' => json_encode($attachments)
]);

if(!$insert) {
    $db->rollback();
    JsonResponse([
        'success' => false,
        'message' => "Failed email request",
        'data' => []
    ]);
}

/** send email */
$emailSender = EmailSender::init(['name' => $userdata['MBR_NAME'], 'email' => $userdata['MBR_EMAIL']]);
$emailSender->useFile('custom_content', [
    'subject' => $data['subject'],
    'contents' => $content,
]);

if(isset($data['send_copy'])) {
    $emailSender->useInternal();
}

if(!empty($attachments)) {
    foreach($attachments as $attachment) {
        if($attachment['link']) {
            $emailSender->addStringAttachment($attachment['original_name'], $attachment['link'], $attachment['type']);
        } else {
            $emailSender->addStringAttachment($attachment['original_name'], $attachment['path'], $attachment['type']);
        }
    }
}

$send = $emailSender->send();
if(!$send) {
    $db->rollback();
    JsonResponse([
        'success' => false,
        'message' => "Failed to send email",
        'data' => []
    ]);
}

$db->commit();
JsonResponse([
    'success' => true,
    'message' => "Email sent successfully",
    'data' => []
]);