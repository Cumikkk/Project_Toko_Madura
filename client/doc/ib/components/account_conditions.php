<?php $_SESSION['modal'] = ['modal-account-conditions'] ?>
<section class="section">
    <div class="panel">
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table table-bordered table-dashed table-hover digi-dataTable dataTable-resize table-striped" id="table_available_account_conditions">
                    <thead>
                        <tr>
                            <th width="15%">Active Date</th>
                            <th>Client Name</th>
                            <th>Account</th>
                            <th>Product</th>
                            <th>Rate</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
    $(document).ready(function() {
        $('#table_available_account_conditions').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "/ajax/datatable/available_account_conditions",
                type: "get",
                dataType: "json"
            },
            lengthMenu: [50, 100],
            columns: [
                { data: "active_date" },
                { data: "client_name" },
                { data: "account" },
                { data: "product" },
                { data: "rate" },
                { data: "status" },
                { data: "action" }
            ],
        })
        $('#table_available_account_conditions').on('click', '.buttonModalAccountConditions', function (event) {
            Swal.fire({
                title: 'Loading...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });
            
            const account   = $(this).data('account');
            const action  = $(this).data('action');
            
            $.post('/ajax/post/ib/my-teams/account-commission', { mbr : account, racc : action }, function(response) {
                Swal.close();
                const conditions = response.data.conditions || {};

                $('#racc').val(action);
                $('#mbr').val(account);
                $('#max').val(response.data.max_commission ?? 0);
                $('#maxCharge').val(response.data.max_commission ?? 0);
                $('#slscondition_branch').val(conditions.branch ?? '');
                $('#slscondition_sales').val(conditions.sales_name ?? '');

                var htmlCondition = '';
                response.data.commission.forEach(function(item) {
                    const mbrEmail = item.email ?? '';
                    const salesStructure = item.sales_structure ?? '';
                    const forexValue = item.forex ?? '';
                    const goldValue = item.gold ?? '';
                    const indexValue = item.index ?? '';

                    htmlCondition += `<div class="card client-condition-card border">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 client-order"></h6>
                                <!-- <button type="button" class="btn btn-sm btn-danger remove-row">Delete</button> -->
                            </div>
                            <div class="row g-2">
                                <div class="col-12 col-md-6">
                                    <label class="form-label mb-1">Client Name</label>
                                    <input type="email" class="form-control" name="email_client[]" placeholder="Email Client" value="${mbrEmail}" readonly>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label mb-1">Posisi</label>
                                    <input type="text" class="form-control" name="posisi_client[]" placeholder="Posisi Client" value="${salesStructure}" readonly>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label mb-1">Forex</label>
                                    <input type="text" class="form-control input-number" name="slscom_forex[]" placeholder="Forex" value="${forexValue}">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label mb-1">Gold</label>
                                    <input type="text" class="form-control input-number" name="slscom_gold[]" placeholder="Gold" value="${goldValue}">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label mb-1">Index</label>
                                    <input type="text" class="form-control input-number" name="slscom_index[]" placeholder="Index" value="${indexValue}">
                                </div>
                            </div>
                        </div>
                    </div>`;
                });

                if(conditions.status === "rejected") {
                    $('#reject-message').show();
                    $('#reject-message').text(conditions.note || 'Your sales condition has been rejected. Please contact support for more information.');
                }else {
                    $('#reject-message').hide();
                    $('#reject-message').text('');
                }

                $('#modalAccountConditions').modal('show');
                $('#modalAccountConditionsBody').html(htmlCondition);
                $('#modalAccountConditionsBody .client-order').each(function(index) {
                    $(this).text(`Client ${index + 1}`);
                });
                $('#emptyClientNotice').toggle($('#modalAccountConditionsBody .client-condition-card').length === 0);
            }, 'json');
        });
    })
</script>