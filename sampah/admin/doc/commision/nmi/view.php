<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-treegrid@0.3.0/css/jquery.treegrid.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.23.2/dist/bootstrap-table.min.css">

<script src="https://cdn.jsdelivr.net/npm/jquery-treegrid@0.3.0/js/jquery.treegrid.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.23.2/dist/bootstrap-table.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-table@1.23.2/dist/extensions/treegrid/bootstrap-table-treegrid.min.js"></script>


<div class="page-header">
	<div>
		<h2 class="main-content-title tx-24 mg-b-5">Share NMI</h2>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="/dashboard">Home</a></li>
			<li class="breadcrumb-item"><a href="javascript:void(0);">Commission</a></li>
			<li class="breadcrumb-item active" aria-current="page"><a href="javascript:void(0);">NMI</a></li>
		</ol>
	</div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="input-group">
                    <span class="input-group-text">DateStart</span>
                    <input type="date" name="datestart" value="<?= date("Y-m-01", strtotime("-1 month")); ?>" class="form-control">
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="input-group">
                    <span class="input-group-text">DateEnd</span>
                    <input type="date" name="dateend" value="<?= date("Y-m-t", strtotime("-1 month")); ?>" class="form-control">
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table 
                    id="table"
                    data-search="true"
                    class="table table-hover table-bordered">

                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var $table = $('#table')

    $(document).ready(function() {
        let dateStart = $('input[name="datestart"]').val();
        let dateEnd = $('input[name="dateend"]').val();

        document.querySelectorAll('input[name="datestart"], input[name="dateend"]').forEach((el) => {
            el.addEventListener('change', (event) => {
                dateStart = $('input[name="datestart"]').val();
                dateEnd = $('input[name="dateend"]').val();
                
                $table.bootstrapTable('refresh', {url: `/ajax/datatable/commision/nmi/view?start=${dateStart}&end=${dateEnd}`});
            })
        })

        $(function() {
            $table.bootstrapTable({
                url: `/ajax/datatable/commision/nmi/view?start=${dateStart}&end=${dateEnd}`,
                idField: 'id',
                // showColumns: true,
                columns: [
                    {
                        field: 'id',
                        title: 'ID',
                        formatter: function(value, row, index) {
                            return (row.pid == 0)
                                ? `<a target="_blank" class="btn btn-sm btn-primary" href="/commision/nmi/detail/${value}?start=${dateStart}&end=${dateEnd}">Detail</a>`
                                : value;
                        }
                    },
                    {
                        field: 'structure',
                        title: 'Structure'
                    },
                    {
                        field: 'email',
                        title: 'Email',
                    },
                    {
                        field: 'nmi',
                        title: 'NMI',
                    },
                    {
                        field: 'percentage',
                        title: 'Persentase',
                    },
                    {
                        field: 'result',
                        title: 'Total',
                        sortable: true,
                        align: 'right',
                    },
                ],
                treeShowField: 'id',
                parentIdField: 'pid',
                onPostBody: function() {
                    var columns = $table.bootstrapTable('getOptions').columns
    
                    if (columns && columns[0][1].visible) {
                        $table.treegrid({
                            treeColumn: 1,
                            onChange: function() {
                                $table.bootstrapTable('resetView')
                            }
                        })
                    }
                }
            })
        })
    })
</script>