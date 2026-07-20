<?php
use App\Models\FileUpload;
use App\Models\Helper;
use App\Models\Ticket;

$ticketCode = Helper::form_input($_POST['code'] ?? "-");
$ticket = Ticket::findByCode($ticketCode);
if(!$ticket) {
    JsonResponse([
        'success' => false,
        'message' => "Invalid Code",
        'data' => []
    ]);
}

function render_chat_message($content) {
    $text = html_entity_decode((string)$content, ENT_QUOTES, 'UTF-8');
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    return nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));
}

$history = [];
$sqlGet = $db->query("SELECT * FROM tb_ticket_detail WHERE TDETAIL_TCODE = '{$ticketCode}' ORDER BY TDETAIL_DATETIME ");
if($sqlGet && $sqlGet->num_rows > 0) {
    $chatHistory = $sqlGet->fetch_all(MYSQLI_ASSOC);
}
?>

<?php foreach($chatHistory as $chat) : ?>
    <?php $timestamp = (date("Y-m-d", strtotime($chat['TDETAIL_DATETIME'])) == date("Y-m-d"))? date("H:i", strtotime($chat['TDETAIL_DATETIME'])) : date("F, d Y H:i", strtotime($chat['TDETAIL_DATETIME'])); ?>
    <?php if($chat['TDETAIL_TYPE'] == "member") : ?>
        <div class="single-message-outgoing">
            <div class="msg-box-inner">
                <?php if($chat['TDETAIL_CONTENT_TYPE'] == "image") : ?>
                    <div class="msg-img">
                        <img src="<?= $chat['TDETAIL_CONTENT']; ?>" alt="Image" />
                    </div>
                    <div class="msg-option">
                        <span class="msg-time"><?= $timestamp ?></span>
                    </div>

                <?php else : ?>
                    <div class="msg-option">
                        <p><?= render_chat_message($chat['TDETAIL_CONTENT']) ?></p>
                        <span class="msg-time"><?= $timestamp ?></span>
                    </div>

                <?php endif; ?>
            </div>        
        </div>

    <?php elseif($chat['TDETAIL_TYPE'] == "admin") : ?>
        <div class="single-message">
            <div class="msg-box-inner">
                <?php if($chat['TDETAIL_CONTENT_TYPE'] == "image") : ?>
                    <div class="msg-img">
                        <img src="<?= $chat['TDETAIL_CONTENT']; ?>" alt="Image" />
                    </div>

                <?php else : ?>
                    <div class="msg-option">
                        <p><?= render_chat_message($chat['TDETAIL_CONTENT']) ?></p>
                        <span class="msg-time"><?= $timestamp ?></span>
                    </div>

                <?php endif; ?>
            </div>        
        </div>
    <?php endif; ?>
<?php endforeach; ?>