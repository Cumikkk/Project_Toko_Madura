<?php 
use App\Models\User;
?>
<link rel="stylesheet" href="/assets/css/my-teams.css">
<section class="section">
    <div class="section-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center w-100 gap-3">
            <h2 class="section-title">Wallet</h2>
            <a href="/wallet/withdrawal" class="btn btn-primary"><i class="fa fa-plus"></i> Withdrawal</a>
        </div>
    </div>
    <hr>

    <div class="row mb-25">
        <div class="col-lg-3 col-md-4 col-xs-12">
            <div class="panel header-color">
                <div class="panel-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <p class="d-flex justify-content-between text-uppercase fw-bold text-muted">Balance</p>
                    </div>
                    <div class="flex-1">
                        <h5 class="fw-bold text-success">$<?= User::wallet($user['MBR_ID']); ?></h5>
                        <p class="text-muted m-0"><?= date("F Y") ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="section-header">
        <h2 class="section-title">History</h2>
    </div>
    <hr>
    <p>Detailed information regarding wallet in/out is displayed here</p>

    <div class="panel">
        <div class="panel-body">
            <div class="data-table">
                <table class="table table-bordered table-dashed table-hover digi-dataTable dataTable-resize table-striped" id="table_wallet_history">
                    <thead>
                        <tr>
                            <th>DateTime</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" class="text-center">No data available.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
    $(document).ready(function() {
        $('#table_wallet_history').DataTable({
            processing: true,
            serverSide: true,
            order: [0, 'desc'],
            lengthMenu: [[50, 100, -1], [50, 100, "All"]],
            ajax: {
                url: "/ajax/datatable/wallet_history",
                type: "GET"
            },
            columns: [
                { data: "datetime" },
                { data: "type" },
                { data: "description" },
                { data: "amount" },
                { data: "status" },
            ],
            columnDefs: [
                {
                    targets: 3,
                    render: function(data) {
                        return new Intl.NumberFormat('en-US', {currency: 'USD', style: 'currency'}).format(data);
                    }
                }
            ]
        });
    })
</script>