<section class="section">
    <div class="row mb-25">
        <div class="col-lg-3 col-md-4 col-xs-12">
            <div class="panel header-color h-100">
                <div class="panel-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <p class="d-flex justify-content-between text-uppercase fw-bold text-muted">Total Active</p>
                        <i class="fa fa-check-circle fa-2x"></i>
                    </div>
                    <div class="flex-1">
                        <h5 class="fw-bold positive" id="total_rebate">Loading...</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-4 col-xs-12">
            <div class="panel header-color h-100">
                <div class="panel-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <p class="d-flex justify-content-between text-uppercase fw-bold text-muted">Total Rejected</p>
                        <i class="fa fa-times-circle fa-2x"></i>
                    </div>
                    <div class="flex-1">
                        <h5 class="fw-bold positive" id="total_downline">Loading...</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-4 col-xs-12">
            <div class="panel header-color h-100">
                <div class="panel-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <p class="d-flex justify-content-between text-uppercase fw-bold text-muted">Total Pending</p>
                        <i class="fa fa-clock fa-2x"></i>
                    </div>
                    <div class="flex-1">
                        <h5 class="fw-bold positive" id="total_pending">Loading...</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-4 col-xs-12">
            <div class="panel header-color h-100">
                <div class="panel-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <p class="d-flex justify-content-between text-uppercase fw-bold text-muted">Total Not Activated</p>
                        <i class="fa fa-ban fa-2x"></i>
                    </div>
                    <div class="flex-1">
                        <h5 class="fw-bold positive" id="total_not_activated">Loading...</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
    function summaryDataStore() {
        let totalActive = document.getElementById('total_rebate');
        let totalRejected = document.getElementById('total_downline');
        let totalPending = document.getElementById('total_pending');
        let totalNotActivated = document.getElementById('total_not_activated');

        function load() {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: "/ajax/post/ib/my-teams/summary",
                    type: "get",
                    dataType: "json",
                    success: function(resp) {
                        if(!resp.success) reject(resp.message || "Failed to load summary data");
                        else {
                            let data = resp.data;
                            totalActive.innerText = data.totalActive;
                            totalRejected.innerText = data.totalRejected;
                            totalPending.innerText = data.totalPending;
                            totalNotActivated.innerText = data.totalNotActivated;
                            resolve();
                        }
                    },
                    error: function() {
                        reject("Failed to load summary data");
                    }
                })
            })
        }

        return {
            totalActive,
            totalRejected,
            totalPending,
            totalNotActivated,
            load
        }
    }

    $(document).ready(function() {
        const summaryData = summaryDataStore();
        summaryData.load();    
    });
</script>