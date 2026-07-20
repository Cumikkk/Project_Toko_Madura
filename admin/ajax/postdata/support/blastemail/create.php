<?php
use App\Models\Helper;
use App\Models\User;
use Config\Core\Database;
use Config\Core\EmailSender;

$data = Helper::getSafeInput($_POST);
$data['email_to'] = $_ENV['MAILGUN_EMAIL'] ?? '';
if(empty($data['email_to'])) {
    JsonResponse([
        'success' => false,
        'message' => "Recipient email is required",
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

$content = $_FILES['email_content'] ?? '';
if(!$content || $content == '') {
    JsonResponse([
        'success' => false,
        'message' => "Email content is required",
        'data' => []
    ]);
}
print_r($_FILES);die;

$attachments = [];
if(!empty($_FILES['email_content'])) {
    foreach($_FILES['email_content']['tmp_name'] as $key => $tmpName) {
        if(is_uploaded_file($tmpName)) {
            $attachments[] = [
                'name' => $_FILES['email_content']['name'][$key],
                'path' => $tmpName,
                'type' => $_FILES['email_content']['type'][$key]
            ];
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

if($data['send_copy']) {
    $emailSender->useInternal();
}

if($attachments) {
    foreach($attachments as $attachment) {
        $emailSender->addStringAttachment($attachment['name'], $attachment['path'], $attachment['type']);
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