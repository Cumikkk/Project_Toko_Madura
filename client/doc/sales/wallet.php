<?php 
use App\Models\Dpwd;
use App\Models\Helper;

$_SESSION['modal'] = ['modal-wd-rebate', 'modal-wd-nmi'];
?>
<div class="row">
    <div class="col-lg-4 col-6 col-xs-12 mb-3">
        <div class="dashboard-top-box dashboard-top-box-2 rounded border-0 panel-bg h-100">
            <div class="left h-100 d-flex flex-column">
                <p class="d-flex justify-content-between mb-2">Rebate Commission</p>
                <div class="d-flex flex-column">
                    <small class="fw-normal mb-0">$<?= Helper::formatCurrency(App\Models\User::wallet($user['MBR_ID'], [Dpwd::$typeRebateCommission])) ?></small>
                </div>
                <p class="text-muted mt-auto"><a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modal-wd-rebate"><small>Withdrawal</small></a></p>
            </div>
            <div class="right">
                <a href="deposit">
                    <div class="part-icon text-light rounded">
                        <span><i class="fa-light fa-wallet"></i></span>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-6 col-xs-12 mb-3">
        <div class="dashboard-top-box dashboard-top-box-2 rounded border-0 panel-bg h-100">
            <div class="left h-100 d-flex flex-column">
                <p class="d-flex justify-content-between mb-2">NMI Share</p>
                <div class="d-flex flex-column">
                    <small class="fw-normal mb-0 dp-idr">Rp <?= Helper::formatCurrency(App\Models\User::wallet($user['MBR_ID'], [Dpwd::$typeNmiCommission])) ?></small>
                </div>
                <p class="text-muted mt-auto"><a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#modal-wd-nmi"><small>Withdrawal</small></a></p>
            </div>
            <div class="right">
                <a href="deposit">
                    <div class="part-icon text-light rounded">
                        <span><i class="fa-light fa-wallet"></i></span>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-12 mb-3">
        <div class="panel">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        History Commission
                    </h5>
                    <hr>
                    <div class="table-responsive">
                        <table class="table table-bordered table-dashed table-hover digi-dataTable dataTable-resize table-striped" id="commission-table">
                            <thead>
                                <tr>
                                    <th class="text-center">Date</th>
                                    <th class="text-center">Type</th>
                                    <th class="text-center">Amount</th>
                                    <th class="text-center">Description</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12 mb-3">
        <div class="panel">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        History Withdrawal
                    </h5>
                    <hr>
                    <div class="table-responsive">
                        <table class="table table-bordered table-dashed table-hover digi-dataTable dataTable-resize table-striped" id="withdrawal-table">
                            <thead>
                                <tr>
                                    <th class="text-center">Date</th>
                                    <th class="text-center">Type</th>
                                    <th class="text-center">Amount</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Description</th>
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
        let table = $('#commission-table').DataTable({
            scrollX: true,
            processing: true,
            serverSide: true,
            order: [[0, 'desc']],
            ajax: {
                url: "/ajax/datatable/wallet_commission",
                data: function(d){
                    return d;
                }
            },
            columnDefs: [
                { targets: 2, className: "text-end"},
                { targets: 3, className: "text-start"},
            ],
        });

        let table_withdrawal = $('#withdrawal-table').DataTable({
            scrollX: true,
            processing: true,
            serverSide: true,
            order: [[0, 'desc']],
            ajax: {
                url: "/ajax/datatable/wallet_withdrawal",
                data: function(d){
                    return d;
                }
            },
            columnDefs: [
                { targets: 2, className: "text-end"},
                { targets: 4, className: "text-start"},
            ],
        });
    });
</script>