<?php
    use App\Models\Admin;
    use App\Models\Helper;
    use App\Models\FileUpload;
    
    $listGrup = $adminPermissionCore->availableGroup();
    $adminRoles = Admin::adminRoles();
    if(!$adminPermissionCore->hasPermission($authorizedPermission, "/support/ticket/view_chats")) {
        exit("Invalid Authorization");
    }
    $code = Helper::form_input($_POST['code'] ?? "");
    if(empty($code)) {
        exit("Invalid Code");
    }

    function render_ticket_message($content) {
        $text = html_entity_decode((string)$content, ENT_QUOTES, 'UTF-8');
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        return nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));
    }

    
    $lis_tanggal = [];
    $sql_get_chat = mysqli_query($db, "
        SELECT
            tb_ticket_detail.TDETAIL_TYPE,
            tb_ticket_detail.TDETAIL_CONTENT,
            LENGTH(tb_ticket_detail.TDETAIL_CONTENT) AS LEN,
            tb_ticket_detail.TDETAIL_CONTENT_TYPE,
            tb_ticket_detail.TDETAIL_DATETIME
        FROM tb_ticket
        JOIN tb_ticket_detail
        ON(tb_ticket.TICKET_CODE = tb_ticket_detail.TDETAIL_TCODE)
        WHERE tb_ticket.TICKET_CODE = '$code'
    ");
    if($sql_get_chat && mysqli_num_rows($sql_get_chat) > 0){
        $RSLT_CHAT = mysqli_fetch_all($sql_get_chat, MYSQLI_ASSOC);
        foreach ($RSLT_CHAT as $VALUE){
            $chat_date = date("Y-m-d", strtotime($VALUE['TDETAIL_DATETIME']))
?>
    <?php if(!in_array($chat_date, $lis_tanggal)) : ?>
        <?php $lis_tanggal[] = $chat_date; ?>
        <label class="main-chat-time mt-3">
            <?php if(date("Y-m-d") == $chat_date) : ?>
                <span>Today</span>
            <?php else : ?>
                <span><?php echo date("d M Y", strtotime($VALUE['TDETAIL_DATETIME'])); ?></span>
            <?php endif; ?>
        </label>
    <?php endif; ?>
    <?php if($VALUE['LEN']) : ?>
        <?php if($VALUE['TDETAIL_TYPE'] == 'admin') : ?>
            <div class="media flex-row-reverse">
                <div class="media-body">
    
                    <?php if($VALUE['TDETAIL_CONTENT_TYPE'] == 'image') : ?>
                        <div class="pd-0">
                            <a target="_blank" href="<?php echo $VALUE['TDETAIL_CONTENT'] ?>">
                                <img alt="avatar" class="wd-150 mb-1" src="<?php echo $VALUE['TDETAIL_CONTENT'] ?>">
                            </a>
                        </div>
                    <?php else :  ?>
                        <div class="main-msg-wrapper">
                            <?php echo render_ticket_message($VALUE['TDETAIL_CONTENT']) ?>
                        </div>
                    <?php endif;  ?>
                    
                    <div>
                        <span><?php echo date("H:i", strtotime($VALUE['TDETAIL_DATETIME'])) ?></span> <a href="javascript:void(0);"><i class="icon ion-android-more-horizontal"></i></a>
                    </div>
                </div>
            </div>
    
        <?php else : ?>
            <div class="media">
                <div class="media-body">
    
                    <?php if($VALUE['TDETAIL_CONTENT_TYPE'] == 'image') : ?>
                        <div class="pd-0">
                            <a target="_blank" href="<?php echo $VALUE['TDETAIL_CONTENT'] ?>">
                                <img alt="avatar" class="wd-150 mb-1" src="<?php echo $VALUE['TDETAIL_CONTENT'] ?>">
                            </a>
                        </div>
                    <?php else :  ?>
                        <div class="main-msg-wrapper">
                            <?php echo render_ticket_message($VALUE['TDETAIL_CONTENT']) ?>
                        </div>
                    <?php endif;  ?>
                    
                    <div>
                        <span><?php echo date("H:i", strtotime($VALUE['TDETAIL_DATETIME'])) ?></span> <a href="javascript:void(0);"><i class="icon ion-android-more-horizontal"></i></a>
                    </div>
                </div>
            </div>
    
        <?php endif; ?>
    <?php endif; ?>
<?php
        }
    }
?>