<?php 
$sqlGetMember = $db->query("SELECT * FROM tb_member WHERE MBR_STS = -1");
?>
<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Send Email</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item">Support</li>
            <li class="breadcrumb-item active" aria-current="page">Send Email</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card custom-card">
            <div class="card-header">
                <h6 class="card-title mb-0">Custom Email to Client</h6>
            </div>
            <div class="card-body">
                <form id="form-send-email" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Recipient <span class="text-danger">*</span></label>
                            <select class="form-control select2" name="email_to" id="email_to" required>
                                <?php foreach($sqlGetMember->fetch_all(MYSQLI_ASSOC) as $member): ?>
                                    <option value="<?= htmlspecialchars($member['MBR_EMAIL']) ?>">
                                        <?= htmlspecialchars($member['MBR_NAME'] . ' (' . $member['MBR_EMAIL'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Select recipient email address</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="subject" id="subject" placeholder="Enter email subject" required>
                        </div>

                        <div class="col-md-12 mb-5">
                            <label class="form-label">Email Content <span class="text-danger">*</span></label>
                            <small class="text-muted">Use HTML tags for formatting (e.g., &lt;b&gt;bold&lt;/b&gt;, &lt;br&gt; for line break)</small>
                            <div class="quill-full" data-for="email_content"></div>
                            <textarea class="form-control" name="content" id="email_content" rows="10" placeholder="Enter email content here..." style="display: none;" required></textarea>
                        </div>

                        <div class="col-md-12 mt-5 mb-3">
                            <label class="form-label">Attachment (Optional)</label>
                            <input type="file" class="form-control" name="attachments[]" id="attachments" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip">
                            <small class="text-muted">Allowed: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, ZIP. Max size: 10MB per file. Multiple files allowed.</small>
                            <div id="file-preview" class="mt-2"></div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="send_copy" id="send_copy" value="1">
                                <label class="form-check-label" for="send_copy">
                                    Send a copy to my email
                                </label>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="col-md-12">
                            <button type="button" class="btn btn-secondary" id="btn-preview">
                                <i class="fe fe-eye"></i> Preview
                            </button>
                            <button type="submit" class="btn btn-primary" id="btn-send">
                                <i class="fe fe-send"></i> Send Email
                            </button>
                            <button type="reset" class="btn btn-light">
                                <i class="fe fe-x"></i> Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Email History -->
<div class="row mt-3">
    <div class="col-lg-12">
        <div class="card custom-card">
            <div class="card-header">
                <h6 class="card-title mb-0">Email History</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tbl_email_history" class="table table-striped table-hover table-bordered">
                        <thead>
                            <tr class="text-center">
                                <th>Date</th>
                                <th>Subject</th>
                                <th>Recipient</th>
                                <th>Send By</th>
                                <th>#</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">Email Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>To:</strong> <span id="preview-to"></span>
                </div>
                <div class="mb-3">
                    <strong>Subject:</strong> <span id="preview-subject"></span>
                </div>
                <div class="mb-3">
                    <strong>Attachments:</strong> <span id="preview-attachments">None</span>
                </div>
                <div class="mb-3">
                    <strong>Content:</strong>
                    <div class="border p-3 mt-2" id="preview-content" style="background: #f8f9fa;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btn-send-from-preview">
                    <i class="fe fe-send"></i> Send Now
                </button>
            </div>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
let tbl_email_history;
$(document).ready(function() {
    // Email History DataTable
    tbl_email_history = $('#tbl_email_history').DataTable({
        dom: 'Blfrtip',
        processing: true,
        serverSide: true,
        deferRender: true,
        order: [[0, 'desc']],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
        buttons: [
            {
                extend: 'excel',
                text: 'Excel',
            },
            {
                extend: 'copy',
                text: 'Copy'
            },
            {
                text: 'Refresh',
                name: 'refresh',
                action: function(e, dt, node, config) {
                    dt.ajax.reload();
                }
            }
        ],
        ajax: {
            url: '/ajax/datatable/support/sendemail/view',
            type: 'POST',
            data: function(d) {
                return d;
            }
        },
        columns: [
            { data: 'created_at' },
            { data: 'subject' },
            { data: 'recipient' },
            { data: 'sender' },
            { 
                data: 'id',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `<a target="_blank" href="/support/sendemail/detail?id=${data}" class="btn btn-sm btn-info text-white btn-view"><i class="fe fe-eye"></i> View</a>`;
                } 
            },
        ],
    });

    
    $('#attachments').on('change', function() {
        const files = this.files;
        let preview = '';
        
        if (files.length > 0) {
            preview = '<div class="alert alert-info"><strong>Selected Files:</strong><ul class="mb-0 mt-2">';
            for (let i = 0; i < files.length; i++) {
                const size = (files[i].size / 1024).toFixed(2);
                preview += `<li>${files[i].name} (${size} KB)</li>`;
            }
            preview += '</ul></div>';
        }
        
        $('#file-preview').html(preview);
    });

    // Preview Button
    $('#btn-preview').on('click', function() {
        const emailTo = $('#email_to option:selected').text();
        const subject = $('#subject').val();
        const content = $('#email_content').val();
        const files = $('#attachments')[0].files;
        
        if (!subject || !content) {
            Swal.fire({
                icon: 'warning',
                title: 'Incomplete Form',
                text: 'Please fill in subject and content'
            });
            return;
        }
        
        $('#preview-to').text(emailTo);
        $('#preview-subject').text(subject);
        $('#preview-content').html(content);
        
        if (files.length > 0) {
            let fileList = '<ul class="mb-0">';
            for (let i = 0; i < files.length; i++) {
                fileList += `<li>${files[i].name}</li>`;
            }
            fileList += '</ul>';
            $('#preview-attachments').html(fileList);
        } else {
            $('#preview-attachments').text('None');
        }
        
        $('#previewModal').modal('show');
    });

    // Send from Preview
    $('#btn-send-from-preview').on('click', function() {
        $('#previewModal').modal('hide');
        $('#form-send-email').submit();
    });

    // Form Submit
    $('#form-send-email').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const btnSend = $('#btn-send');
        
        // Disable button
        btnSend.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
        
        $.ajax({
            url: '/ajax/post/support/sendemail/create',
            type: 'POST',
            dataType: "json",
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                btnSend.prop('disabled', false).html('<i class="fe fe-send"></i> Send Email');
                Swal.fire(response.alert);
                if(response.success) {
                    $('#form-send-email')[0].reset();
                    $('#file-preview').html('');
                    $('#subject').val(null).trigger('change');
                    
                    // Reload history table
                    tbl_email_history.ajax.reload();
                }
            },
            error: function(xhr, status, error) {
                btnSend.prop('disabled', false).html('<i class="fe fe-send"></i> Send Email');
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while sending email'
                });
                
                console.error('Error:', error);
            }
        });
    });

    const QuillEditor = function() {


        //
        // Setup module components
        //

        // Quill editor
        const _componentQuill = function() {
            if (typeof Quill == 'undefined') {
                console.warn('Warning - summernote.min.js is not loaded.');
                return;
            }

            // Full features example
            $('.quill-full').each((i, e) => {
                new Quill(e, {
                    modules: {
                        toolbar: [
                            [{ 'font': [] }],
                            [{ 'size': ['small', false, 'large', 'huge'] }],
                            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            ['blockquote', 'code-block'],
                            [{ 'header': 1 }, { 'header': 2 }],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            [{ 'script': 'sub'}, { 'script': 'super' }],
                            [{ 'indent': '-1'}, { 'indent': '+1' }],
                            [{ 'direction': 'rtl' }],
                            [{ 'color': [] }, { 'background': [] }],
                            [{ 'align': [] }],
                            [ 'formula', 'image', 'video' ],
                            ['clean']
                        ]
                    },
                    bounds: '.content-inner',
                    placeholder: 'Please add your text here...',
                    scrollingContainer: 'quill-scrollable-container',
                    theme: 'snow'
                });
            });
        };


        //
        // Return objects assigned to module
        //

        return {
            init: function() {
                _componentQuill();
            }
        }
    }();

    QuillEditor.init();

    $('.ql-container').bind('keyup', function(e){
        // console.log($(this).find('div[class="ql-editor"]').html());
        $(`#${$(this).data('for')}`).html($(this).find('div[class="ql-editor"]').html());
    });
});
</script>

<style>
    .select2-container {
        width: 100% !important;
    }
    
    .select2-selection {
        min-height: 38px;
    }
    
    #email_content {
        font-family: 'Courier New', monospace;
        font-size: 14px;
    }
    
    #preview-content {
        min-height: 200px;
        white-space: pre-wrap;
        word-wrap: break-word;
    }
    
    .badge {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }
</style>
