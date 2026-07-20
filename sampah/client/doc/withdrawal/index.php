<?php if(count(App\Models\Account::myAccount($user['MBR_ID'])) > 0) : ?>
    <div class="dashboard-breadcrumb mb-25">
        <h2>Withdawal</h2>
        <div class="input-group-a dashboard-filter">
            <?php if($user['MBR_EMAIL'] == "kholidhikam1@gmail.com") : ?>
                <p class="mb-0">
                    <strong>Current Status: </strong> 
                    <?php if($user['MBR_WITHDRAWAL_STATUS']) : ?>
                        <span class="badge bg-success">Enabled</span>
                    <?php else : ?>
                        <span class="badge bg-danger">Disabled</span>
                    <?php endif; ?>
                </p>
                <div class="d-flex align-items-center gap-2">
                    <a href="javascript:void(0)" data-type="0" class="btnStatus btn btn-sm btn-danger"><i class="fas fa-lock"></i> Disable</a>
                    <a href="javascript:void(0)" data-type="1" class="btnStatus btn btn-sm btn-success"><i class="fas fa-unlock"></i> Enable</a>
                    <a href="/withdrawal/create" class="btn btn-sm btn-primary"><i class="fa-light fa-arrow-right-to-bracket"></i> Tambah Withdawal</a>
                </div>

                <script type="text/javascript">
                    $(document).ready(function() {
                        $('.btnStatus').on('click', function() {
                            let type = $(this).data('type');
                            let typeString = (type == 1) ? "Enable" : "Disable";
                            Swal.fire({
                                title: `${typeString} Withdrawal`,
                                text: `Are you sure you want to ${typeString.toLowerCase()} the Withdrawal feature on all user?`,
                                icon: "question",
                                showCancelButton: true,
                                reverseButtons: true
                            }).then((result) => {
                                if(result.isConfirmed) {
                                    Swal.fire({
                                        text: `Update in progress...`,
                                        allowOutsideClick: false,
                                        didOpen: () => {
                                            Swal.showLoading();
                                        }
                                    });

                                    $.post("/ajax/post/withdrawal/toggle-status", {type: type}, (resp) => {
                                        Swal.fire(resp.alert).then(() => {
                                            if(resp.success) {
                                                location.reload();
                                            }
                                        });
                                    }, 'json')
                                }
                            })
                        })
                    })
                </script>

            <?php else : ?>
                <a href="/withdrawal/create" class="btn btn-sm btn-primary"><i class="fa-light fa-arrow-right-to-bracket"></i> Tambah Withdawal</a>
            <?php endif; ?>
            
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="panel">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-dashed table-hover digi-dataTable dataTable-resize table-striped" id="withdrawal-table">
                                <thead>
                                    <tr>
                                        <th rowspan="2" class="text-center">Date</th>
                                        <th rowspan="2" class="text-center">Account</th>
                                        <th colspan="2" class="text-center">Amount</th>
                                        <th rowspan="2" class="text-center">Rate</th>
                                        <th rowspan="2" class="text-center">Status</th>
                                        <th rowspan="2" class="text-center">Note</th>
                                    </tr>
                                    <tr>
                                        <th class="text-center">Request</th>
                                        <th class="text-center">Received</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {
            let prm = {
                "dte" : 0
            };
            let table = $('#withdrawal-table').DataTable({
                scrollX: true,
                processing: true,
                serverSide: true,
                order: [[0, 'desc']],
                ajax: {
                    url: "/ajax/datatable/withdrawal",
                    data: function(d){
                        return $.extend(d, prm);
                    }
                },
                columnDefs: [
                    { targets: 5, className: "text-center", searchable: false }
                ],
                drawCallback : (tbl) => {
                    // $(`#fbd`).remove();
                    if(!$(`#fbd`).length){
                        $(`input[aria-controls="${$(tbl.nTable).attr('id')}"]`).parent().before(`
                            <label id="fbd">
                                <input type="text" class="filterDate datepicker" ${(prm.dte.length) ? `value="${prm.dte}"` : ``} style="width: 225px;" placeholder="filter by date">
                            </label>
                        `);
                    }
                    $('.filterDate').daterangepicker({
                        locale: {
                            format: 'YYYY-MM-DD'
                        }
                    }).on('apply.daterangepicker', function(ev, picker){
                        prm.dte = `${picker.startDate.format('YYYY-MM-DD')},${picker.endDate.format('YYYY-MM-DD')}`;
                        console.log(prm.dte);
                        table.ajax.reload();
                    });
                }
            })
        })
    </script>

<?php else : ?>
    <div class="row">
        <div class="col-md-3"></div>
        <div class="col-md-6">
            <div class="panel">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center">
                            <a href="/account/create" class="text-center btn btn-md btn-primary mt-3 mb-3">Create Real Account</a>
                            <div class="alert alert-warning mt-3" role="alert">
                                You don't have any real account yet. Please create a real account to access the withdrawal feature.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
