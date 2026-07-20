<?php
    $CP = App\Models\CompanyProfile::profilePerusahaan();
?>
<style>
.sales-tree-item {
    transition: all 0.2s ease;
}
.sales-tree-item:hover {
    color: #0d6efd;
}
.sales-tree-item.text-primary {
    font-weight: bold;
}
</style>
<div class="page-header">
	<div>
		<h2 class="main-content-title tx-24 mg-b-5">Stucture</h2>
		<ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
			<li class="breadcrumb-item">Commision</li>
			<li class="breadcrumb-item active" aria-current="page">Stucture</li>
		</ol>
	</div>
</div>
<div class="row">
    <div class="col-md-4 mb-3">
        <?php if($cnfgUpdt = $adminPermissionCore->isHavePermission($moduleId, "structure.configure.update")){ ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title text-priamry">Configure</h5>
                </div>
                <form method="post" action="<?= $cnfgUpdt["link"] ?>" id="config_form">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="struc_upline" class="form-control-label">Dorman Period</label>
                            <div class="input-group">
                                <input type="number" name="dorman_period" id="dorman_period" value="<?= $CP["PROF_DORMAN"] ?>" class="form-control" placeholder="Dorman Period" required>
                                <span class="input-group-text transaction-currency">Days</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="struc_name" class="form-control-label">Retention Extent Period</label>
                            <div class="input-group">
                                <input type="number" name="retextp" id="retextp" value="<?= $CP["PROF_DORMAN_EXTEND"] ?>" class="form-control" placeholder="Retention Extent Period" required>
                                <span class="input-group-text transaction-currency">Days</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        <?php } ?>
    </div>
    <div class="col-md-8 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title text-priamry">Structure</h5>
            </div>
            <div class="card-body">
                <?php
                    $query = $db->query("SELECT ID_SLSSTRC, SLSSTRC_UP, SLSSTRC_NAME
                            FROM tb_sales_structure
                            ORDER BY (SLSSTRC_UP IS NULL) DESC, SLSSTRC_UP, SLSSTRC_NAME");
                    $result = $query->fetch_all(MYSQLI_ASSOC);
                    if($result) {
                        $map = [];
                        foreach ($result as $row) {
                            $map[$row['ID_SLSSTRC']] = $row;
                        }

                        $tree = [];
                        foreach ($result as $row) {
                            if ($row['SLSSTRC_UP'] == 0 || $row['SLSSTRC_UP'] === null) {
                                $tree[] = &$map[$row['ID_SLSSTRC']];
                            } else {
                                $parentId = $row['SLSSTRC_UP'];
                                if (isset($map[$parentId])) {
                                    if (!isset($map[$parentId]['children'])) {
                                        $map[$parentId]['children'] = [];
                                    }
                                    $map[$parentId]['children'][] = &$map[$row['ID_SLSSTRC']];
                                }
                            }
                        }

                        // Ubah fungsi renderNode untuk menambahkan data-attributes dan class yang diperlukan
                        function renderNode($node) {
                            $html = "<li>";
                            $html .= "<span class='sales-tree-item' style='margin: 5px; cursor: pointer;' ";
                            $html .= "data-id='" . htmlspecialchars($node['ID_SLSSTRC']) . "' ";
                            $html .= "data-name='" . htmlspecialchars($node['SLSSTRC_NAME']) . "'>";
                            $html .= "<i class='fa fa-user-secret me-2'></i>";
                            $html .= htmlspecialchars($node['SLSSTRC_NAME']);
                            $html .= "</span>";
                            
                            if (isset($node['children'])) {
                                $html .= "<ul style='margin-top:5px;'>";
                                foreach ($node['children'] as $child) {
                                    $html .= renderNode($child);
                                }
                                $html .= "</ul>";
                            }
                            $html .= "</li>";
                            return $html;
                        }

                        echo "<ul>";
                        foreach ($tree as $rootNode) {
                            echo renderNode($rootNode);
                        }
                        echo "</ul>";
                    } else {
                        echo "<em>Data kosong.</em>";
                    }
                ?>
            </div>
        </div>
    </div>
    

    <div class="col-md-12 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Pencarian saat ini: <b id="currentSearch">HEAD OF SALES</b></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered" id="table-structure">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Nama Lengkap</th>
                                <th>No. Telepon</th>
                                <th>Tipe</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    let tableStructure;
    let currentSearch = {
        name: "",
        id: -1
    };

    $(document).ready(function() {
        $('#config_form').on('submit', function(event) {
            event.preventDefault();
            let data = $(this).serialize(),
                button = $(this).find('button[type="submit"]'),
                url = "/ajax/post".concat($(this).attr('action'));

            Swal.fire({
                title: 'Loading',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                    $.post(url, data, (resp) => {
                        Swal.fire(resp.alert).then(() => {
                            if(resp.success) {
                                location.reload();
                            }
                        })
                    }, 'json');
                }
            });
        });

        $('summary.sales-search').on('click', function(evt) {
            let target = $(evt.currentTarget);
            console.log(target.data('id'));
            if(target.data('id')) {
                currentSearch.id = target.data('id');
                currentSearch.name = target.data('name');
                $('#currentSearch').text(currentSearch.name);
                tableStructure?.ajax?.reload();
            }
        })

        tableStructure = $('#table-structure').DataTable({
            dom: 'Blfrtip',
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
            lengthMenu: [[10, 50, 100], [10, 50, 100]],
            order: [[0, 'desc']],
            ajax: {
                url: "/ajax/datatable/commision/structure/view",
                data: function(d) {
                    d.currentSearch = currentSearch.id
                    return d;
                }
            }
        })
        try {
            const btnRef = tableStructure.button('refresh:name');
            const $nodeRef = $(btnRef.node && btnRef.node() || []);
            const originalRefText = $nodeRef.data('original-text') || 'Refresh';

            tableStructure.on('processing.dt', function (e, settings, processing) {
                const btn = tableStructure.button('refresh:name');
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

            tableStructure.on('xhr.dt', function () {
                const btn = tableStructure.button('refresh:name');
                if (!btn) return;
                const $node = $(btn.node());
                const original = $node.data('original-text') || originalRefText;
                btn.enable(true).text(original);
            });
        } catch (e) {
            console && console.warn && console.warn('Refresh button toggler skipped:', e);
        }

        $('.sales-tree-item').on('click', function(evt) {
            let target = $(evt.currentTarget);
            let id = target.data('id');
            let name = target.data('name');
            
            if(id) {
                currentSearch.id = id;
                currentSearch.name = name;
                $('#currentSearch').text(name);
                tableStructure?.ajax?.reload();
            }
            
            // Highlight item yang dipilih
            $('.sales-tree-item').removeClass('text-primary');
            target.addClass('text-primary');
        });
    })
</script>