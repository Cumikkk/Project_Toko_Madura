<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Sales Conditions</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item">Account</li>
            <li class="breadcrumb-item active" aria-current="page">Sales Conditions</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Pending Conditions</h5>
            </div>
            <div class="card-body">
                <table class="table table-hover table-bordered table-striped" id="table_pending_conditions">
                    <thead>
                        <tr>
                            <th style="vertical-align: middle">Active Date</th>
                            <th style="vertical-align: middle">Request By Partner</th>
                            <th style="vertical-align: middle">Client Name / Account</th>
                            <th style="vertical-align: middle">Product</th>
                            <th style="vertical-align: middle">Rate</th>
                            <th style="vertical-align: middle">Status</th>
                            <th style="vertical-align: middle">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-12 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Active Conditions</h5>
            </div>
            <div class="card-body">
                <table class="table table-hover table-bordered table-striped" id="table_active_conditions">
                    <thead>
                        <tr>
                            <th style="vertical-align: middle" class="text-center">Response Date</th>
                            <th style="vertical-align: middle" class="text-center">Request By Partner</th>
                            <th style="vertical-align: middle" class="text-center">Client Name / Account</th>
                            <th style="vertical-align: middle" class="text-center">Product</th>
                            <th style="vertical-align: middle" class="text-center">Rate</th>
                            <th style="vertical-align: middle" class="text-center">Note</th>
                            <th style="vertical-align: middle" class="text-center">Status</th>
                            <th style="vertical-align: middle" class="text-center">Response By</th>
                            <th style="vertical-align: middle" class="text-center">Print</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-12 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">History</h5>
            </div>
            <div class="card-body">
                <table class="table table-hover table-bordered table-striped" id="table_history_conditions">
                    <thead>
                        <tr>
                            <th style="vertical-align: middle" class="text-center">Response Date</th>
                            <th style="vertical-align: middle" class="text-center">Request By Partner</th>
                            <th style="vertical-align: middle" class="text-center">Client Name / Account</th>
                            <th style="vertical-align: middle" class="text-center">Product</th>
                            <th style="vertical-align: middle" class="text-center">Rate</th>
                            <th style="vertical-align: middle" class="text-center">Note</th>
                            <th style="vertical-align: middle" class="text-center">Status</th>
                            <th style="vertical-align: middle" class="text-center">Response By</th>
                            <th style="vertical-align: middle" class="text-center">Print</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-filter-active-conditions" tabindex="-1" role="dialog" aria-labelledby="modalFilterActiveConditionsLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFilterActiveConditionsLabel">Filter History Conditions</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="post" id="formFilterHistoryConditions">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="filterPartner" class="form-label">Partner</label>
                        <input type="text" class="form-control" id="filterPartner" placeholder="Enter partner name">
                    </div>
                    <div class="mb-3">
                        <label for="filterAccount" class="form-label">Account</label>
                        <input type="text" class="form-control" id="filterAccount" placeholder="Enter account Number">
                    </div>
                    <div class="mb-3">
                        <label for="filterStatus" class="form-label">Status</label>
                        <select class="form-control" id="filterStatus">
                            <option value="">Select status</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Apply Filter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    let tablePendingConditions;
    let tableActiveConditions;
    let tableHistoryConditions;
    let filterTableHistoryConditions = {
        partner: "",
        account: "",
        status: "",
    };
    
    $(document).ready(function() {
        let modalFilterActive = $('#modal-filter-active-conditions');
        tablePendingConditions = $('#table_pending_conditions').DataTable({
            dom: "Brl<'table-responsive't>p",
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
                        const $node = $(btn.node());

                        if (!$node.data('original-text')) {
                            $node.data('original-text', $node.html());
                        }

                        btn.enable(false);
                        btn.text('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Loading...');

                        dt.ajax.reload(null, false);
                    }
                }
			],
            ajax: {
                url: '/ajax/datatable/account/pending_sales_conditions',
                type: 'get'
            },
            columns: [
                { data: 'active_date' },
                { data: 'request_by_partner' },
                { data: 'client_name' },
                { data: 'product' },
                { data: 'rate' },
                { data: 'status' },
                { data: 'action_id', orderable: false, searchable: false }
            ]
        })

        tableActiveConditions = $('#table_active_conditions').DataTable({
            dom: "Brl<'table-responsive't>p",
            processing: true,
            serverSide: true,
            deferRender: true,
            lengthMenu: [[50, 100, -1], [50, 100, "All"]],
            order: [[0, 'desc']],
            ajax: {
                url: '/ajax/datatable/account/history_sales_conditions',
                type: 'get',
                data: function(d) {
                    d.partner = filterTableHistoryConditions.partner;
                    d.account = filterTableHistoryConditions.account;
                    d.status = filterTableHistoryConditions.status;
                }
            },
            columns: [
                { data: 'response_date' },
                { data: 'request_by_partner' },
                { data: 'client_name' },
                { data: 'product' },
                { data: 'rate' },
                { data: 'note' },
                { data: 'status' },
                { data: 'response_by' },
                { data: 'id' },
            ],
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
                        const $node = $(btn.node());

                        if (!$node.data('original-text')) {
                            $node.data('original-text', $node.html());
                        }

                        btn.enable(false);
                        btn.text('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Loading...');

                        dt.ajax.reload(null, false);
                    }
                },
                {
                    text: '<i class="fa fa-filter"></i>',
                    name: 'filter',
                    action: function (e, dt, node, config) {
                        modalFilterActive.modal('show');
                    }
                }
			],
        })

        // tableHistoryConditions = $('#table_history_conditions').DataTable({
        //     dom: "Brl<'table-responsive't>p",
        //     processing: true,
        //     serverSide: true,
        //     deferRender: true,
        //     lengthMenu: [[50, 100, -1], [50, 100, "All"]],
        //     order: [[0, 'desc']],
        //     ajax: {
        //         url: '/ajax/datatable/account/history_sales_conditions',
        //         type: 'get',
        //         data: function(d) {
        //             d.partner = filterTableHistoryConditions.partner;
        //             d.account = filterTableHistoryConditions.account;
        //             d.status = filterTableHistoryConditions.status;
        //         }
        //     },
        //     columns: [
        //         { data: 'response_date' },
        //         { data: 'request_by_partner' },
        //         { data: 'client_name' },
        //         { data: 'product' },
        //         { data: 'rate' },
        //         { data: 'note' },
        //         { data: 'status' },
        //         { data: 'response_by' },
        //         { data: 'id' },
        //     ],
        //     buttons: [
		// 		{
		// 			extend: 'excel',
		// 			text: 'Excel',
		// 		},
		// 		{
		// 			extend: 'copy',
		// 			text: 'Copy'
		// 		},
        //         {
        //             text: 'Refresh',
        //             name: 'refresh',
        //             action: function (e, dt, node, config) {
        //                 const btn = dt.button('refresh:name');
        //                 const $node = $(btn.node());

        //                 if (!$node.data('original-text')) {
        //                     $node.data('original-text', $node.html());
        //                 }

        //                 btn.enable(false);
        //                 btn.text('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Loading...');

        //                 dt.ajax.reload(null, false);
        //             }
        //         },
        //         {
        //             text: '<i class="fa fa-filter"></i>',
        //             name: 'filter',
        //             action: function (e, dt, node, config) {
        //                 modalFilterActive.modal('show');
        //             }
        //         }
		// 	],
        // })

        $('#formFilterHistoryConditions').on('submit', function(e) {
            e.preventDefault();
            filterTableHistoryConditions.partner = $('#filterPartner').val();
            filterTableHistoryConditions.account = $('#filterAccount').val();
            filterTableHistoryConditions.status = $('#filterStatus').val();
            tableActiveConditions.ajax.reload();
            // tableHistoryConditions.ajax.reload();
            modalFilterActive.modal('hide');
        })

        try {
            const btnRef = tablePendingConditions.button('refresh:name');
            const $nodeRef = $(btnRef.node && btnRef.node() || []);
            const originalRefText = $nodeRef.data('original-text') || 'Refresh';

            tablePendingConditions.on('processing.dt', function (e, settings, processing) {
                const btn = tablePendingConditions.button('refresh:name');
                if (!btn) return;
                const $node = $(btn.node());
                if (processing) {
                    btn.enable(false);
                    btn.text('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Loading...');
                } else {
                    btn.enable(true);
                    const original = $node.data('original-text') || originalRefText;
                    btn.text(original);
                }
            });

            tablePendingConditions.on('xhr.dt', function () {
                const btn = tablePendingConditions.button('refresh:name');
                if (!btn) return;
                const $node = $(btn.node());
                const original = $node.data('original-text') || originalRefText;
                btn.enable(true).text(original);
            });

            const btnRef2 = tableActiveConditions.button('refresh:name');
            const $nodeRef2 = $(btnRef2.node && btnRef2.node() || []);
            const originalRefText2 = $nodeRef2.data('original-text') || 'Refresh';

            tableActiveConditions.on('processing.dt', function (e, settings, processing) {
                const btn = tableActiveConditions.button('refresh:name');
                if (!btn) return;
                const $node = $(btn.node());
                if (processing) {
                    btn.enable(false);
                    btn.text('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Loading...');
                } else {
                    btn.enable(true);
                    const original = $node.data('original-text') || originalRefText2;
                    btn.text(original);
                }
            });

            tableActiveConditions.on('xhr.dt', function () {
                const btn = tableActiveConditions.button('refresh:name');
                if (!btn) return;
                const $node = $(btn.node());
                const original = $node.data('original-text') || originalRefText2;
                btn.enable(true).text(original);

                /** filter marker */
                const btnFilter = tableActiveConditions.button('filter:name');
                if(btnFilter) {
                    if(filterTableHistoryConditions.partner || filterTableHistoryConditions.account || filterTableHistoryConditions.status) {
                        $(btnFilter.node()).addClass('btn-warning').removeClass('btn-primary');
                    } else {
                        $(btnFilter.node()).removeClass('btn-warning').addClass('btn-primary');
                    }
                }
            });

            const btnRef3 = tableHistoryConditions.button('refresh:name');
            const $nodeRef3 = $(btnRef3.node && btnRef3.node() || []);
            const originalRefText3 = $nodeRef3.data('original-text') || 'Refresh';

            tableHistoryConditions.on('processing.dt', function (e, settings, processing) {
                const btn = tableHistoryConditions.button('refresh:name');
                if (!btn) return;
                const $node = $(btn.node());
                if (processing) {
                    btn.enable(false);
                    btn.text('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Loading...');
                } else {
                    btn.enable(true);
                    const original = $node.data('original-text') || originalRefText3;
                    btn.text(original);
                }
            });

            tableHistoryConditions.on('xhr.dt', function () {
                const btn = tableHistoryConditions.button('refresh:name');
                if (!btn) return;
                const $node = $(btn.node());
                const original = $node.data('original-text') || originalRefText3;
                btn.enable(true).text(original);

                /** filter marker */
                const btnFilter = tableHistoryConditions.button('filter:name');
                if(btnFilter) {
                    if(filterTableHistoryConditions.partner || filterTableHistoryConditions.account || filterTableHistoryConditions.status) {
                        $(btnFilter.node()).addClass('btn-warning').removeClass('btn-primary');
                    } else {
                        $(btnFilter.node()).removeClass('btn-warning').addClass('btn-primary');
                    }
                }
            });

        } catch (e) {
            console && console.warn && console.warn('Refresh button toggler skipped:', e);
        }
    })
</script>

<?php if($adminPermissionCore->isHavePermission($moduleId, "action")) : ?>
    <div class="modal fade" id="modal-action-pending-conditions" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Action Pending Sales Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="modal-action-pending-conditions-content">Loading...</div>
                </div>
                <div class="modal-footer">
                    <a href="javascript:void(0)" class="btn-action btn btn-danger" data-type="reject" data-title="Reject">Reject</a>
                    <a href="javascript:void(0)" class="btn-action btn btn-success" data-type="accept" data-title="Accept">Accept</a>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {
            let modal = $('#modal-action-pending-conditions');
            if(tablePendingConditions) {
                tablePendingConditions.on('draw', async function() {
                    await $.each($('#table_pending_conditions tbody tr'), (i, tr) => {
                        let td = $(tr).find('td').eq(6);
                        if(td) {
                            let actionArea = td.find('.action');
                            if(actionArea && !actionArea.find('.button-info').length) {
                                let id = actionArea.data('actionid');
                                actionArea.append(`<a href="javascript:void(0)" class="button-info btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modal-action-pending-conditions" data-actionid="${id}"><i class="fa fa-info-circle"></i> Detail</a>`)
                            }
                        }
                    })
                })
            }

            modal.on('show.bs.modal', function(event) {
                let button = $(event.relatedTarget);
                let actionId = button.closest('.action').data('actionid');
                let modalContent = modal.find('#modal-action-pending-conditions-content');

                $.ajax({
                    url: `/ajax/post/account/detail_conditions`,
                    type: 'post',
                    dataType: "html",
                    data: {
                        actionId
                    },
                    success: function(response) {
                        modalContent.html(response);
                        modal.attr('data-actionid', actionId);
                    },
                    error: function() {
                        modalContent.html('<p class="text-danger">Failed to load content. Please try again.</p>');
                    }
                });
            });

            $('.btn-action').on('click', function(event) {
                let target = $(event.currentTarget);
                if(target) {
                    modal.modal('hide');
                    Swal.fire({
                        title: `${target.data('title')} Sales Conditions`,
                        text: `Are you sure you want to ${target.data('type')} this sales conditions request?`,
                        icon: 'warning',
                        showCancelButton: true,
                        reverseButtons: true,
                        input: 'text',
                        inputLabel: 'Reason',
                        inputPlaceholder: 'Type your reason here...',
                        inputValidator: (value) => {
                            if (!value && target.data('type') == 'reject') {
                                return 'You need to write something!'
                            }
                        },
                    })
                    .then((result) => {
                        console.log(target)
                        if(result.isConfirmed) {
                            $.ajax({
                                url: "/ajax/post/account/action_sales_conditions",
                                type: "post",
                                dataType: "json",
                                data: {
                                    actionId: modal.data('actionid'),
                                    reason: result.value,
                                    type: target.data('type')
                                },
                                success: function(resp) {
                                    Swal.fire(resp.alert).then(() => {
                                        if(resp.success) {
                                            tablePendingConditions.ajax.reload();
                                            tableActiveConditions.ajax.reload();
                                        }
                                    })
                                },
                                error: function() {
                                    Swal.fire("Error", "Failed to process the request. Please try again.", "error");
                                }
                            })
                        }
                    })
                }
            })
        })
    </script>
<?php endif; ?>