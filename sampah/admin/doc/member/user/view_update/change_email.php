<div class="row">
    <div class="col-md-12 mb-3">
        <form action="/ajax/post<?= $subPermission['link'] ?>" method="post" id="form-change-email" enctype="multipart/form-data">
            <input type="hidden" name="code" value="<?= $userCode; ?>">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        Document Approval Change Email
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="email" class="form-control-label required">Email</label>
                                <input type="email" name="email" id="email" class="form-control" placeholder="Email Baru" value="<?= $userData['MBR_EMAIL'] ?>" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="image-dokumen" class="form-control-label required">Dokumen</label>
                                <input type="file" name="image" id="image-dokumen" class="dropify-dokumen" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-info">Submit</button>
                </div>
            </div>
        </form>
    </div>
    <div class="col-md-12 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered" id="table-history-change-email" data-code="<?= $userCode ?>">
                        <thead>
                            <tr>
                                <th>Date Requested</th>
                                <th>Previous Email</th>
                                <th>New Email</th>
                                <th>Document</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        const docInput = $('.dropify-dokumen');
        if ($.fn.dropify && docInput.length) {
            const setDroppedFile = function(input, file) {
                try {
                    if (typeof DataTransfer !== 'undefined') {
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        input.files = dt.files;
                    } else {
                        input.files = [file];
                    }
                } catch (err) {
                    return;
                }

                $(input).trigger('change');
            };

            docInput.dropify().each(function() {
                const input = this;
                const dropify = $(input).data('dropify');
                if (!dropify || !dropify.wrapper) return;

                $(dropify.wrapper).on('dragenter dragover drop', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    if (e.type !== 'drop') return;

                    const files = e.originalEvent && e.originalEvent.dataTransfer ? e.originalEvent.dataTransfer.files : null;
                    if (!files || !files.length) return;
                    setDroppedFile(input, files[0]);
                });
            });
        }

        table = $('#table-history-change-email').DataTable( {
            dom: 'Blfrtip',
            processing: true,
            serverSide: true,
            deferRender: true,
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
                    action: function (e, dt, node, config) {
                        const btn = dt.button('refresh:name');
                        const nodeEl = $(btn.node());

                        if (!nodeEl.data('original-text')) {
                            nodeEl.data('original-text', nodeEl.html());
                        }

                        btn.enable(false);
                        btn.text('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Loading...');

                        dt.ajax.reload(null, false);
                    }
                }
			],
            lenghtMenu: [[10, 50, 100], [10, 50, 100]],
            order: [[0, 'desc']],
            ajax: {
                url: "/ajax/datatable/member/update/change_email",
                data: {
                    code: $('#table-history-change-email').data('code')
                }
            }
        })

        $('#form-change-email').on('submit', function(e) {
            e.preventDefault();
            let button = $(this).find('button[type="submit"]');
            button.addClass('loading');

            $.ajax({
                url: $(this).attr('action'),
                type: "post",
                dataType: "json",
                data: new FormData(this),
                contentType: false,
                processData: false,
                cache: false
            }).done(function(resp) {
                button.removeClass('loading');
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        location.reload();
                    }
                })
            })
        })
        try {
            const btnRef = table.button('refresh:name');
            const nodeRef = $(btnRef.node && btnRef.node() || []);
            const originalRefText = nodeRef.data('original-text') || 'Refresh';

            table.on('processing.dt', function (e, settings, processing) {
                const btn = table.button('refresh:name');
                if (!btn) return;
                const nodeEl = $(btn.node());
                if (processing) {
                    btn.enable(false);
                    btn.text('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Loading...');
                } else {
                    btn.enable(true);
                    const original = nodeEl.data('original-text') || originalRefText;
                    btn.text(original);
                }
            });

            table.on('xhr.dt', function () {
                const btn = table.button('refresh:name');
                if (!btn) return;
                const nodeEl = $(btn.node());
                const original = nodeEl.data('original-text') || originalRefText;
                btn.enable(true).text(original);
            });
        } catch (e) {
            console && console.warn && console.warn('Refresh button toggler skipped:', e);
        }
    })
</script>