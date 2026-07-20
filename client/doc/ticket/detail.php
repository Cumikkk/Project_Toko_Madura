<?php
use App\Models\Helper;
use App\Models\Ticket;

$ticketCode = Helper::form_input($_GET['code'] ?? "-");
$ticket = Ticket::findByCode($ticketCode);
if(!$ticket) {
    die("<script>alert('Invalid Code'); location.href = '/ticket';</script>");
}


?>

<style>
    body {
        --chat-color-text: black; 
        --bg-color-chat: #e9e9e9;
        --bg-color-outgoing: #3dff445e;
        --bg-color-incoming: #fff;
        --chat-border-color: #d1d1d1;
    }

    body.dark-theme {
        --chat-color-text: white; 
        --bg-color-chat: #141414;
        --bg-color-outgoing: #242526;
        --bg-color-incoming: #0D99FF;
        --chat-border-color: #fff;
    }

    .chat-area {
        min-height: 350px;
        max-height: 60vh;
        padding: 1rem;
        background-color: var(--bg-color-chat);
        border-radius: 5px;
        overflow-y: auto;
    }

    /* width */
    .chat-area::-webkit-scrollbar {
        width: 6px;
    }

    /* Track */
    .chat-area::-webkit-scrollbar-track {
        background: transparent;
    }

    /* Handle */
    .chat-area::-webkit-scrollbar-thumb {
        background: #848484;
        border-radius: 10px;
    }

    .chat-area .single-message-outgoing {
        justify-content: flex-end !important;
    }

    .chat-area .single-message-outgoing,
    .chat-area .single-message {
        display: flex;
        flex-direction: row;
    }

    .chat-area .single-message-outgoing .msg-box-inner {
        margin-inline-start: 15%;
        display: flex;
        margin-bottom: 1rem;
        flex-direction: column;
        justify-content: start;
        width: fit-content;
        background: var(--bg-color-outgoing);
        padding: 3px 10px;
        border-radius: 3px;
        border-right-color: var(--chat-border-color);
        border-right-width: 3px;
        border-right-style: solid;
    }

    .chat-area .single-message .msg-box-inner {
        margin-inline-end: 15%;
        display: flex;
        margin-bottom: 1rem;
        flex-direction: column;
        justify-content: start;
        width: fit-content;
        background: var(--bg-color-incoming);
        padding: 3px 10px;
        border-radius: 3px;
        border-left-color: var(--chat-border-color);
        border-left-width: 3px;
        border-left-style: solid;
    }

    .chat-area .single-message .msg-box-inner:has(.msg-img),
    .chat-area .single-message-outgoing .msg-box-inner:has(.msg-img) {
        max-width: 250px;
    }


    .chat-area .single-message-outgoing .msg-box-inner .msg-img,
    .chat-area .single-message .msg-box-inner .msg-img {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
        height: 100%;
        padding-top: 5px;
    }

    .chat-area .single-message-outgoing .msg-box-inner .msg-img img,
    .chat-area .single-message .msg-box-inner .msg-img img {
        max-width: 100%;
        height: max-content;
        margin-bottom: 1rem;
        border-radius: 5px;
    }

    .chat-area .single-message-outgoing .msg-box-inner .msg-option .msg-time,
    .chat-area .single-message .msg-box-inner .msg-option .msg-time {
        float: inline-end;
        font-size: 10px !important;
        font-style: italic;
    }


    .chat-area .single-message-outgoing .msg-box-inner .msg-option .msg-text, 
    .chat-area .single-message-outgoing .msg-box-inner .msg-option p, 
    .chat-area .single-message-outgoing .msg-box-inner .msg-option .msg-time,

    .chat-area .single-message .msg-box-inner .msg-option .msg-text, 
    .chat-area .single-message .msg-box-inner .msg-option p, 
    .chat-area .single-message .msg-box-inner .msg-option .msg-time 
    {
        text-align: justify;
        font-size: 13px;
        letter-spacing: 0;
        color: var(--chat-color-text);
    }

    .msg-type-area {
        width: 100%;
    }

    .msg-type-area form {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .msg-type-area form input[type="file"] {
        display: none;
    }

    .msg-input-quill {
        flex: 1;
        min-height: 46px;
        max-height: 140px;
        overflow: auto;
        border: 1px solid #d9dde7;
        border-radius: 8px;
        background: #fff;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .msg-input-quill:focus-within {
        border-color: #86b7fe;
        box-shadow: 0 0 0 .15rem rgba(13, 110, 253, .15);
    }

    .msg-input-quill .ql-container {
        border: 0 !important;
        font-size: 14px;
        font-family: inherit;
    }

    .msg-input-quill .ql-editor {
        min-height: 46px;
        max-height: 132px;
        padding: 10px 12px;
        line-height: 1.45;
        white-space: pre-wrap;
    }

    .msg-type-area .btn-icon {
        width: 46px;
        min-width: 46px;
        height: 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .msg-input-quill .ql-editor.ql-blank::before {
        left: 12px;
        right: 12px;
        color: #98a2b3;
        font-style: normal;
    }
</style>

<div class="dashboard-breadcrumb mb-25">
    <h2>Tiket <b class="text-primary">#<?= $ticketCode ?></b></h2>
    <span class="badge bg-<?= App\Models\Ticket::topicColor($ticket['TICKET_TOPIC']) ?>"><?= ucwords(str_replace('_', ' ', $ticket['TICKET_TOPIC'])) ?></span>
</div>

<div class="panel">
    <div class="panel-body">
        <div class="panel-header px-0">
            <div class="d-flex w-100 justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar">
                        <img src="/assets/images/logo-icon.png" alt="User">
                    </div>
                    <div class="small d-flex flex-column align-items-start ms-2">
                        <span class="user-name fw-bold">Customer Service</span>
                        <small class="text-success">Online</small>
                    </div>
                </div>
                <?php if($ticket['TICKET_STS'] == -1) : ?>
                    <div>
                        <a href="javascript:void(0)" class="btn btn-sm btn-danger" id="close">Tutup Tiket</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="chat-area">
            <div class="chat-content">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

<!-- 
                <div class="single-message-outgoing">
                    <div class="msg-box-inner">
                        <div class="msg-option">
                            <p>Saya mengirim foto</p>
                            <span class="msg-time">2 Apr 2024</span>
                        </div>
                    </div>
                </div>


                <div class="single-message-outgoing">
                    <div class="msg-box-inner">
                        <div class="msg-option">
                            <p>
                            Omnis distinctio eaque voluptatibus. Reiciendis natus harum ea ipsam, et facere? Omnis distinctio eaque voluptatibus. Reiciendis natus harum ea ipsam, et facere? Omnis distinctio eaque voluptatibus. Reiciendis natus harum ea ipsam, et facere? Omnis distinctio eaque voluptatibus. Reiciendis natus harum ea ipsam, et facere? Omnis distinctio eaque voluptatibus. Reiciendis natus harum ea ipsam, et facere? Omnis distinctio eaque voluptatibus. Reiciendis natus harum ea ipsam, et facere?
                            </p>
                            <span class="msg-time">2 Apr 2024</span>
                        </div>
                    </div>
                </div> -->
            </div>
        </div>
        <div class="panel-body msg-type-area">
            <?php if($ticket['TICKET_STS'] == -1) : ?>
                <form id="form-send-message" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="code" value="<?= $ticketCode ?>">
                    <input type="hidden" name="message" id="chat-message">
                    <button type="button" class="btn btn-icon btn-outline-primary" data-bs-toggle="modal" data-bs-target="#attachmentModal">
                        <i class="fa-light fa-link"></i>
                    </button>
                    <div class="msg-input-quill" id="chat-input"></div>
                    <button class="btn btn-icon btn-send btn-outline-primary"><i class="fa-light fa-paper-plane"></i></button>
                </form>

                <div class="modal fade" id="attachmentModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Upload Gambar</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input
                                    type="file"
                                    name="attachment"
                                    class="dropify chat-attachment"
                                    id="chatAttachment"
                                    accept="image/png,image/jpeg"
                                    data-allowed-file-extensions="png jpg jpeg"
                                    data-show-remove="true"
                                    data-height="160"
                                    form="form-send-message"
                                >
                            </div>
                            <!-- <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Selesai</button>
                            </div> -->
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <input type="hidden" name="code" value="<?= $ticketCode ?>">
                <p class="text-center text-decoration-underline"><i>Ticket Closed at <?= date("Y-m-d", strtotime($ticket['TICKET_DATETIME_CLOSE'])) ?></i></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        let code = $('input[name="code"]').val();
        const quillContainer = document.getElementById('chat-input');
        let quill = null;

        $('.chat-attachment.dropify').dropify({
            messages: {
                default: 'Upload Image',
                replace: 'Upload Image',
                remove: 'Hapus',
                error: 'Oops, file not valid.'
            }
        });
        function clearChatAttachment() {
            const attachmentInput = $('#chatAttachment');
            const dropifyInstance = attachmentInput.data('dropify');

            if (dropifyInstance) {
                dropifyInstance.resetPreview();
                dropifyInstance.clearElement();
                return;
            }

            attachmentInput.val('');
        }

        $('#chatAttachment').on('change', async function() {
            await validateImageInput(this);
        });

        if(quillContainer) {
            quill = new Quill('#chat-input', {
                theme: 'snow',
                placeholder: 'Type your message...',
                modules: {
                    toolbar: false
                }
            });

            const syncMessageField = () => {
                const plainText = quill.getText();
                const normalizedText = plainText.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
                const messageForSave = normalizedText.endsWith('\n') ? normalizedText.slice(0, -1) : normalizedText;
                $('#chat-message').val(messageForSave.trim() ? messageForSave : '');
            };

            quill.on('text-change', function() {
                syncMessageField();
            });

            quill.focus();
        }

        load_chat(code);

        // Send Message
        $('#form-send-message').on('submit', function(event) {
            event.preventDefault();

            if(quill) {
                const plainText = quill.getText();
                const normalizedText = plainText.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
                const messageForSave = normalizedText.endsWith('\n') ? normalizedText.slice(0, -1) : normalizedText;
                $('#chat-message').val(messageForSave.trim() ? messageForSave : '');
            }
            
            if(!$('#chat-message').val()?.length && !$('#chatAttachment').val()?.length) {
                Swal.fire("Failed", "Mohon isi pesan", "error")
                return false
            }
            
            let formData = new FormData(this)
            $.ajax({
                url: "/ajax/post/ticket/send-chat",
                method: "POST",
                dataType: "json",
                data: formData,
                processData: false,
                contentType: false,
                cache: false
            })
            .done(function(resp) {
                if(!resp?.success) {
                    Swal.fire("Error", (resp?.error || "Gagal mengirim pesan"), 'error')
                    return false
                }

                $('#chat-message').val("");
                clearChatAttachment();
                if(quill) {
                    quill.setContents([]);
                }
                load_chat(code)
            })
        })

        $('#close').on('click', function() {
            Swal.fire({
                title: "Tutup Tiket?",
                text: "Konfirmasi untuk melanjutkan",
                icon: "question",
                showCancelButton: true,
                reverseButtons: true
            }).then((result) => {
                if(result.isConfirmed) {
                    Swal.fire({
                        text: "Loading...",
                        allowOutsideClick: false,
                        didOpen: function() {
                            Swal.showLoading();
                        }
                    })

                    $.ajax({
                        url: "/ajax/post/ticket/close",
                        method: "POST",
                        data: {code: code},
                        dataType: "json",
                        success: function(resp) {
                            Swal.fire(resp.alert).then(() => {
                                if(resp.success) {
                                    location.reload();
                                }
                            })
                        },
                        error: function() {
                            Swal.fire("Error", "An error occurred while closing the ticket", 'error')
                        }
                    })
                }
            })
        })
    })

    async function load_chat(code) {
        $.ajax({
            url: "/ajax/post/ticket/load-chat",
            type: "post",
            dataType: "html",
            data: {
                code: code
            }
        }).done((html) => {
            $('.chat-content').empty().html(html)
            $('.chat-area').animate({scrollTop: $('.chat-content').height()})
        })
    }
</script>
