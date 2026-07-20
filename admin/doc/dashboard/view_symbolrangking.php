<?php $permDesc = $adminPermissionCore->isHavePermission($moduleId, $permission)['desc']; ?>
<div class="col-md-6">
    <div class="card custom-card">
        <div class="card-header">
            <h6 class="main-content-label mb-1"><?= $permDesc ?></h6>
            <p class="text-muted card-sub-title">Top 10 <?= $permDesc ?></p>
        </div>
        <div class="card-body" style="overflow-y: auto; height: 485px;">
            <div class="table-responsive">
                <table class="table table-bordered mg-b-0 table-striped table-hover" id="tabel_SymbolRangking">
                    <thead>
                        <tr class="text-center">
                            <th>Symbol</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="2" class="text-center">
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
        
        let tabel_SymbolRangking;
        
        function initSymbolRangking() {
            if (tabel_SymbolRangking) {
                tabel_SymbolRangking.destroy();
            }
            
            tabel_SymbolRangking = $('#tabel_SymbolRangking').DataTable({
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
                    url: "/ajax/datatable/dashboard/symbolrangking/view",
                    contentType: "application/json",
                    type: "GET",
                    error: function(xhr, error, code) {
                        console.error('Symbol Ranking fetch error:', error);
                    }
                },
                columns: [
                    { data: 'SYMBOL', defaultContent: '-' },
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
            document.addEventListener('DOMContentLoaded', initSymbolRangking);
        } else {
            // Delay initialization to prevent blocking main thread
            setTimeout(initSymbolRangking, 100);
        }
    })();
</script>