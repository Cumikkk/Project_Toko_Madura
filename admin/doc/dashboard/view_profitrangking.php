<?php $permDesc = $adminPermissionCore->isHavePermission($moduleId, $permission)['desc']; ?>
<div class="col-md-6">
    <div class="card custom-card">
        <div class="card-header">
            <h6 class="main-content-label mb-1"><?= $permDesc ?></h6>
            <p class="text-muted card-sub-title">Top 10 <?= $permDesc ?></p>
        </div>
        <div class="card-body" style="overflow-y: hidden; height: 485px;">
            <div class="table-responsive">
                <table class="table table-bordered mg-b-0 table-striped table-hover" id="tabel_ProfitRangking">
                    <thead>
                        <tr class="text-center">
                            <th>Name</th>
                            <th>Account</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="3" class="text-center">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    (function() {
        'use strict';
        
        let tabel_ProfitRangking;
        
        function initProfitRangking() {
            if (tabel_ProfitRangking) {
                tabel_ProfitRangking.destroy();
            }
            
            tabel_ProfitRangking = $('#tabel_ProfitRangking').DataTable({
                scrollX: true,
                processing: false,
                serverSide: true,
                deferRender: true,
                lengthChange: false,
                searching: false,
                ordering: false,
                paging: false,
                info: false,
                ajax: {
                    url: "/ajax/datatable/dashboard/profitrangking/view",
                    contentType: "application/json",
                    type: "GET",
                    error: function(xhr, error, code) {
                        console.error('Profit Ranking fetch error:', error);
                    }
                },
                columns: [
                    { data: 'FULLNAME', defaultContent: '-' },
                    { data: 'LOGIN', defaultContent: '-' },
                    { data: 'AMOUNT', className: 'text-end', defaultContent: '0' }
                ],
                language: {
                    emptyTable: "No data available",
                    loadingRecords: "Loading...",
                    processing: "Processing..."
                }
            });
        }
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initProfitRangking);
        } else {
            // Delay initialization to prevent blocking main thread
            setTimeout(initProfitRangking, 100);
        }
    })();
</script>