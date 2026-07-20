<?php
    use App\Models\Helper;
    use App\Models\User;
    
    $data = Helper::getSafeInput($_GET);
    $SQL_TCKTD = mysqli_query($db, '
        SELECT
            tb_ticket.TICKET_SUBJECT AS TIC_TITLE,
            tb_ticket.TICKET_STS AS TIC_STS,
            tb_ticket.TICKET_CODE AS TIC_CATEGORY,
            tb_ticket.TICKET_DATETIME_CLOSE AS TIC_CLOSE_AT,
            tb_member.MBR_EMAIL,
            tb_member.MBR_NAME,
            tb_member.MBR_AVATAR
        FROM tb_ticket
        JOIN tb_member
        ON(tb_ticket.TICKET_MBR = tb_member.MBR_ID)
        WHERE MD5(MD5(tb_ticket.ID_TICKET)) = "'.$data["d"].'"
        LIMIT 1
    ');
    if($SQL_TCKTD && mysqli_num_rows($SQL_TCKTD) > 0){
        $RSLT_TCKTD = mysqli_fetch_assoc($SQL_TCKTD);
    }
?>

<style>
    #chtFtr {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    #chtFtr .nav {
        margin-bottom: 0;
        display: flex;
        align-items: center;
    }

    #chtFtr .nav-link,
    #chtFtr .main-msg-send {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .chat-input-quill {
        flex: 1;
        min-height: 44px;
        max-height: 140px;
        overflow: auto;
        border: 1px solid #d9dde7;
        border-radius: 8px;
        background: #fff;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .chat-input-quill:focus-within {
        border-color: #86b7fe;
        box-shadow: 0 0 0 .15rem rgba(13, 110, 253, .15);
    }

    .chat-input-quill .ql-container {
        border: 0 !important;
        font-size: 14px;
        font-family: inherit;
    }

    .chat-input-quill .ql-editor {
        min-height: 44px;
        max-height: 132px;
        padding: 10px 12px;
        line-height: 1.45;
        white-space: pre-wrap;
    }

    .chat-input-quill .ql-editor.ql-blank::before {
        left: 12px;
        right: 12px;
        color: #98a2b3;
        font-style: normal;
    }

    #chtClosedFtr {
        display: block;
        padding: 0 !important;
        min-height: 0 !important;
        height: auto !important;
        line-height: normal;
    }

    #chtClosedFtr .alert {
        margin-bottom: 0 !important;
        border-radius: 0;
    }

</style>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Ticket Detail</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item">Support</li>
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(2) ?>/view">Ticket</a></li>
            <li class="breadcrumb-item active" aria-current="page">Ticket Detail</li>
        </ol>
    </div>
</div>

<?php if($RSLT_TCKTD["TIC_STS"] == -1) : ?>
    <!-- <div class="mb-3 text-end">
        <a href="javascript:void(0)" class="btn btn-sm btn-danger" id="close">Tutup Tiket</a>
    </div> -->
<?php endif; ?>

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="main-content-app pt-0">
                <div class="main-content-body main-content-body-chat">
                    <div class="main-chat-header pt-3" id="chtHdr" style="justify-content: center;">
                        <div class="main-chat-msg-name text-center">
                            <h3><?= $RSLT_TCKTD["MBR_EMAIL"] ?></h3>
                            <h6 id="chtname" class="text-center"><?= $RSLT_TCKTD["TIC_TITLE"] ?></h6>
                            <small class="text-center"><?= $RSLT_TCKTD["TIC_CATEGORY"] ?></small>
                        </div>
                    </div><!-- main-chat-header -->
                    <div class="main-chat-body" id="ChatBody" style="overflow: auto !important;">
                        <div class="content-inner">
                            <div class="row sidemenu-height">
                                <div class="col-md-12">
                                    <div class="construction1 text-center details">
                                        <div class="">
                                            <div class="col-lg-12">
                                                <h1 class="tx-140 mb-0">
                                                    <i class="ti-comment-alt icon"></i>
                                                </h1>
                                            </div>
                                            <div class="col-lg-12 ">
                                                <h1>Please send something to start the conversation</h1>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if($RSLT_TCKTD["TIC_STS"] == -1){ ?>
                        <form method="POST" id="chtFrm">
                            <div class="main-chat-footer" id="chtFtr">
                                <nav class="nav">
                                    <a class="nav-link" data-bs-target="#modal-datepicker" data-bs-toggle="modal" href="javascript:void(0);" title="Add Photo"><i class="fe fe-image"></i></a>
                                </nav>
                                <div class="chat-input-quill" id="chtInpt"></div>
                                <a class="main-msg-send" id="sendBtn" href="javascript:void(0);"><i class="far fa-paper-plane"></i></a>
                            </div>
                        </form>
                    <?php } else { ?>
                        <div class="main-chat-footer" id="chtClosedFtr">
                            <div class="alert alert-warning d-flex align-items-center w-100" role="alert">
                                <img src="<?= App\Models\User::avatar($RSLT_TCKTD['MBR_AVATAR']); ?>" alt="Admin Avatar" class="rounded-circle me-2" style="width: 36px; height: 36px; object-fit: cover;">
                                <div>
                                    <div class="fw-semibold">Tiket ini telah ditutup oleh <?= !empty($RSLT_TCKTD['MBR_NAME']) ? $RSLT_TCKTD['MBR_NAME'] : 'Admin RRFX' ?></div>
                                    <div class="small text-muted"><?= !empty($RSLT_TCKTD['TIC_CLOSE_AT']) ? date('d M Y, H:i', strtotime($RSLT_TCKTD['TIC_CLOSE_AT'])) . ' WIB' : 'Waktu penutupan tidak tersedia' ?></div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-datepicker">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-content-demo">
            <form method="post" enctype="multipart/form-data" id="detail-form">
                <div class="modal-body">
                    <div class="form-group" id="uloadMutasi">
                        <label>Upload Gambar</label>
                        <input type="file" name="mutasi" class="dropify dropify1" id="fileMutasi" accept="image/png, image/jpg, image/jpeg" data-height="200">
                    </div>
                </div>
                <div class="modal-footer text-center" style="display : none;">
                    <input type="hidden" name="sbmt_id" value="<?= $data["d"] ?>">
                    <input type="hidden" name="messg" id="acc-act">
                    <button type="submit" class="btn btn-primary ripple btn-block text-white" type="button" id="sendButton2">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(() => {
        // $('#close').on('click', function() {
        //     Swal.fire({
        //         title: "Tutup Tiket?",
        //         text: "Konfirmasi untuk melanjutkan",
        //         icon: "question",
        //         showCancelButton: true,
        //         reverseButtons: true
        //     }).then((result) => {
        //         if(result.isConfirmed) {
        //             Swal.fire({
        //                 text: "Loading...",
        //                 allowOutsideClick: false,
        //                 didOpen: function() {
        //                     Swal.showLoading();
        //                 }
        //             })

        //             $.post("/ajax/post/support/ticket/close", {code: "<?= $RSLT_TCKTD["TIC_CATEGORY"] ?>"}, (resp) => {
        //                 Swal.fire(resp.alert).then(() => {
        //                     if(resp.success) {
        //                         location.reload();
        //                     }
        //                 })
        //             }, 'json')
        //         }
        //     })
        // })

        function scrollChatToBottom(smooth = false) {
            const $chatBody = $('#ChatBody');
            if(!$chatBody.length) return;

            if(smooth){
                $chatBody.stop().animate({ scrollTop: $chatBody.prop('scrollHeight') }, 220);
            }else{
                $chatBody.scrollTop($chatBody.prop('scrollHeight'));
            }
        }

        async function refreshChat(scrollToBottom = false) {
            $.ajax({
                url: "/ajax/post/support/ticket/view_chats",
                type: "post",
                dataType: "html",
                data: {
                    code: '<?= $RSLT_TCKTD["TIC_CATEGORY"] ?>'
                }
            }).done((html) => {
                $('#ChatBody').empty().html(html);
                if(scrollToBottom){
                    setTimeout(() => scrollChatToBottom(true), 0);
                }
                Swal.close();
            });
        }

        refreshChat();

        const quillContainer = $('#chtInpt');
        let quill = null;

        if(quillContainer.length){
            quill = new Quill('#chtInpt', {
                theme: 'snow',
                placeholder: 'Type your message here...',
                modules: {
                    toolbar: false
                }
            });

            const syncMessageField = () => {
                const plainText = quill.getText();
                const normalizedText = plainText.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
                const messageForSave = normalizedText.endsWith('\n') ? normalizedText.slice(0, -1) : normalizedText;
                $('#acc-act').val(messageForSave.trim() ? messageForSave : '');
            };

            quill.on('text-change', function(){
                syncMessageField();
            });
        }

        let DPF = $('.dropify').dropify();
        DPF.on('dropify.fileReady', function(e){
            $('#acc-act').prop('required', false);
        });
        DPF.on('dropify.afterClear', function(e){
            $('#acc-act').prop('required', true);
        });

        $('#sendBtn').on('click', function(e){
            if(!quill){
                return;
            }

            const plainText = quill.getText();
            const normalizedText = plainText.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
            const messageForSave = normalizedText.endsWith('\n') ? normalizedText.slice(0, -1) : normalizedText;
            const message = messageForSave.trim();
            const hasFile = Boolean($('#fileMutasi').val());

            if(!message && !hasFile) {
                Swal.fire({
                    icon: 'warning',
                    text: 'Isi pesan atau upload gambar terlebih dahulu.'
                });
                quill.focus();
                return;
            }

            $('#acc-act').val(message ? messageForSave : '');
            $('#sendButton2').click();
        });

        $('#chtFrm').on('submit', function(e){
            e.preventDefault();
            $('#sendBtn').trigger('click');
        });
        
        $('#detail-form').on('submit', function(ev){
            ev.preventDefault();
            let data = new FormData(this);
            $.ajax({
                url         : '/ajax/post/support/ticket/send_chats',
                type        : 'POST',
                dataType    : 'JSON',
                enctype     : 'multipart/form-data',
                data        : data,
                contentType : false,
                chache      : false,
                processData : false
            }).done((resp) => {
                if(!resp.success){
                    Swal.fire(resp.alert)
                }else{ 
                    if(quill){
                        quill.setContents([]);
                    }
                    DPF.data('dropify').resetPreview();
                    DPF.data('dropify').clearElement();
                    refreshChat(true);
                }
            });
        });

    });
</script>