<?php

use App\Library\MT5\Mt5Trades;
use App\Library\Sales\SalesMain;
use App\Models\AccountType;
use App\Models\Helper;
use App\Models\Ib;
use App\Models\Rebate;
use App\Models\Symbols;
use App\Models\User;

$userCode = Helper::form_input($_GET['d'] ?? "");
$dateStart = Helper::form_input($_GET['start'] ?? date("Y-m-01"));
$dateEnd = Helper::form_input($_GET['end'] ?? date("Y-m-t"));
$userdata = User::findByCode($userCode);
if(!$userdata) {
    die("<script>alert('Invalid Code'); location.href = '/commision/nmi/view';</script>");
}

$salesData = SalesMain::getUserType($userdata['MBR_TYPE']);
if(!$salesData) {
    die("<script>alert('Invalid Sales'); location.href = '/commision/nmi/view';</script>");
}

/** Get list of downlines */
$downlines = Ib::getNetworks($userdata['MBR_ID'], "downline");

/** Share permission */
$permisShare = $adminPermissionCore->isHavePermission($moduleId, "share");
?>

<div class="page-header">
	<div>
		<h2 class="main-content-title tx-24 mg-b-5">Share NMI</h2>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
			<li class="breadcrumb-item"><a href="javascript:void(0);">Commisison</a></li>
			<li class="breadcrumb-item active" aria-current="page"><a href="javascript:void(0);">Share</a></li>
		</ol>
	</div>
    <div>
        <form action="/ajax/post<?= $permisShare['link'] ?? "" ?>" method="post" id="share-rebate">
            <div class="row">
                <div class="col-md-5 mb-3">
                    <div class="input-group">
                        <span class="input-group-text">DateStart</span>
                        <input type="date" name="datestart" value="<?= date("Y-m-d", strtotime($dateStart)); ?>" class="form-control date">
                    </div>
                </div>
                <div class="col-md-5 mb-3">
                    <div class="input-group">
                        <span class="input-group-text">DateEnd</span>
                        <input type="date" name="dateend" value="<?= date("Y-m-d", strtotime($dateEnd)); ?>" class="form-control date">
                    </div>
                </div>
    
                <div class="col-md-2 mb-3">
                    <input type="hidden" name="code" value="<?= $userCode ?>">
                    <button type="submit" class="btn btn-primary" <?= (!$permisShare)? "disabled" : ""; ?>><i class="fas fa-share"></i> Share</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-2">
        <div class="card custom-card">
            <div class="card-body">
                <h5 class="card-title text-primary mb-3">User Detail</h5>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <p class="mb-0"><b>Fullname</b></p>
                        <p class="mb-0"><?= $userdata['MBR_NAME']; ?></p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <p class="mb-0"><b>Email</b></p>
                        <p class="mb-0"><?= $userdata['MBR_EMAIL']; ?></p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <p class="mb-0"><b>Date Registered</b></p>
                        <p class="mb-0"><?= date("Y-m-d", strtotime($userdata['MBR_DATETIME'])); ?></p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <p class="mb-0"><b>Position</b></p>
                        <p class="mb-0"><?= $salesData->salesDetail['SLSSTRC_NAME']; ?></p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <p class="mb-0"><b>Total Downline</b></p>
                        <p class="mb-0"><?= count($downlines) - 1; ?></p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <p class="mb-0"><b>Total NMI</b></p>
                        <p class="mb-0"><span id="totalNMI">Loading...</span></p>
                    </div>
                    <div class="col-md-3 mb-3">
                        <p class="mb-0"><b>Estimated acquisition</b></p>
                        <p class="mb-0" id="estimatedAcquisition">Loading...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12 mb-2">
        <div class="card custom-card">
            <div class="card-body">
                <h5 class="card-title text-primary mb-3">List NMI Downlines</h5>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered" id="table-downlines-nmi">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Position</th>
                                <th>Total Deposit</th>
                                <th>Total Withdrawal</th>
                                <th>NMI</th>
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
        let table_downlines = $('#table-downlines-nmi').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            order: [[0,'desc']],
            lengthMenu: [[-1], ['All']],
            ajax: {
                url: "/ajax/datatable/commision/nmi/list_downlines_nmi",
                data: function(d) {
                    d.datestart = $('input[name="datestart"]').val();
                    d.dateend = $('input[name="dateend"]').val();
                    d.code = $('input[name="code"]').val();
                }
            },
            drawCallback: function(a) {
                $('#totalNMI').text(a.json.totalNMI)
                $('#estimatedAcquisition').text(a.json.estimatedAcquisition)
                Swal.close();
            },
            columnDefs: [
                { targets: 3, className: "text-end" },
                { targets: 4, className: "text-end" },
                { targets: 5, className: "text-end" },
            ]
        })

        $('.date').on('change', function() {
            Swal.fire({
                text: "Please wait...",
                allowOutsideClick: false,
                didOpen: function() {
                    Swal.showLoading();
                }
            })

            $('#totalNMI').text("Loading...")
            $('#estimatedAcquisition').text("Loading...")
            table_downlines.ajax.reload();
        })

        $("#share-rebate").on('submit', function(event) {
            event.preventDefault();
            let data = $(this).serialize();
            let button = $(this).find('button[type="submit"]');
            let url = $(this).attr('action')

            button.addClass('loading');
            $.post(url, data, (resp) => {
                Swal.fire(resp.alert).then(() => {
                    if(resp.success) {
                        location.reload();
                    }
                })
            }, 'json')
        })
    })
</script>