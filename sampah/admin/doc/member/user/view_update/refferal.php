<?php 

use App\Library\Sales\SalesMain;
use App\Models\Ib;
use App\Models\Refferal;
use App\Models\User;

$upline = User::findUplineByMembeId($userData['MBR_IDSPN']); 
$userRefferal = Refferal::createUserReferral($userData['MBR_ID']);
$specificRefferal = Refferal::createAccountReferralV2($userData['MBR_ID']);
$salesData = SalesMain::getUserType($userData['MBR_TYPE']);
$downlines = Ib::getNetworks($userData['MBR_ID'], "downline");
$idDownline = array_map(fn($ar): int => $ar['MBR_ID'], $downlines) ?? [];
$userLevel = -1;
if($salesData) {
    $userLevel = $salesData->level();
}

$sqlGetActiveUser = $db->query("
    SELECT 
        tm.*,
        tss.*
    FROM tb_member tm 
    JOIN tb_sales_structure tss ON (tss.ID_SLSSTRC = tm.MBR_TYPE)
    WHERE tm.MBR_EMAIL NOT LIKE '%_deleted'
    AND tm.MBR_ID NOT IN (".implode(",", $idDownline).")
    AND tm.MBR_ID != ".$userData['MBR_ID']."
    ORDER BY tm.ID_MBR ASC
");
?>


<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Upline Info</h5>
            </div>
            <div class="card-body">
                <form action="" method="post" id="form-update-upline">
                    <input type="hidden" name="code" value="<?= $userCode; ?>">
                    <div class="form-group">
                        <label for="upline" class="form-control-label required">Upline</label>
                        <select name="upline" id="upline" class="form-control select2">
                            <option value="">Pilih</option>
                            <?php if($sqlGetActiveUser && $sqlGetActiveUser->num_rows > 0) : ?>
                                <?php foreach($sqlGetActiveUser->fetch_all(MYSQLI_ASSOC) as $upl) : ?>
                                    <option value="<?= $upl['MBR_CODE'] ?>" <?= $upl['MBR_ID'] == $userData['MBR_IDSPN']? "selected" : ""; ?> data-division="<?= $upl['SLSSTRC_DIV'] ?>">
                                        <?= $upl['MBR_EMAIL'] ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group text-end">
                        <button type="submit" class="btn btn-block btn-info">Submit</button>
                    </div>
                </form>
                <hr>
                <?php if($upline) : ?>
                    <p class="mb-0"><b>Full Name:</b> <?= $upline['MBR_NAME'] ?></p>
                    <p class="mb-0"><b>Email:</b> <a href="/member/user/update/<?= $upline['MBR_CODE'] ?>"><?= $upline['MBR_EMAIL'] ?></a></p>
                    <p class="mb-0"><b>Account Type:</b> <?= App\Models\SalesStructure::findById($upline['MBR_TYPE'])['SLSSTRC_NAME'] ?? "-" ?></p>
                    
                <?php else : ?>
                    <div class="alert alert-warning">
                        <p>User ini tidak memiliki upline</p>
                    </div>
                <?php endif ?>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Update Type</h5>
            </div>
            <div class="card-body">
                <form action="/ajax/post/member/update/refferal_update_type" method="post" id="form-update-type">
                    <input type="hidden" name="code" value="<?= $userCode; ?>">
                    <div class="form-group">
                        <label for="type" class="form-control-label required">Type User</label>
                        <div class="input-group">
                            <select name="type" id="type" class="form-control form-select" required>
                                <option value="" selected disabled>Choose one</option>
                            </select>
                            <button type="submit" class="input-group-text bg-info">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Refferal Info</h5>
            </div>
            <div class="card-body">
                <?php if(!$salesData || !$salesData->isCanShareRefferal()) : ?>
                    <div class="alert alert-danger">
                        <p>This user cannot share referrals</p>
                    </div>

                <?php else : ?>
                    <div class="form-group">
                        <label class="form-control-label">User Refferal</label>
                        <div class="input-group">
                            <input type="text" class="form-control" disabled value="<?= $userRefferal ?>">
                            <a href="javascript:void(0)" class="input-group-text bg-success"><i class="fas fa-copy text-white"></i></a>
                        </div>
                    </div>
    
                    <div class="form-group">
                        <label class="form-control-label">Account Refferal</label>
                        <?php foreach($specificRefferal as $ref) : ?>
                            <div class="input-group mb-2">
                                <span class="input-group-text"><?= implode("/", [$ref['type'], $ref['rate'], $ref['commission']]) ?></span>
                                <input type="text" class="form-control" disabled value="<?= $ref['link'] ?>">
                                <a href="javascript:void(0)" class="input-group-text bg-success"><i class="fas fa-copy text-white"></i></a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-12 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Upline</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <button class="btn btn-primary btn-sm" id="expandAllUpline">
                                <i class="fas fa-expand"></i> Expand All
                            </button>
                            <button class="btn btn-secondary btn-sm" id="collapseAllUpline">
                                <i class="fas fa-compress"></i> Collapse All
                            </button>
                            <button class="btn btn-success btn-sm" id="refreshUpline">
                                <i class="fas fa-sync"></i> Refresh
                            </button>
                        </div>
                        <div class="col-md-6 text-end">
                            <span class="text-muted" id="uplineStatus">Ready</span>
                        </div>
                    </div>
                </div>
                
                <!-- Search will be placed here by DataTable -->
                <div id="uplineTableSearch" class="mb-3"></div>
                
                <div class="table-responsive">
                    <table id="uplineTable" class="table table-striped table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Refferal Code</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Structure</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <button class="btn btn-primary btn-sm" id="expandAll">
                                <i class="fas fa-expand"></i> Expand All
                            </button>
                            <button class="btn btn-secondary btn-sm" id="collapseAll">
                                <i class="fas fa-compress"></i> Collapse All
                            </button>
                            <button class="btn btn-success btn-sm" id="refreshStructure">
                                <i class="fas fa-sync"></i> Refresh
                            </button>
                        </div>
                        <div class="col-md-6 text-end">
                            <span class="text-muted" id="structureStatus">Ready</span>
                        </div>
                    </div>
                </div>
                
                <!-- Search will be placed here by DataTable -->
                <div id="structureTableSearch" class="mb-3"></div>
                
                <div class="table-responsive">
                    <table id="structureTable" class="table table-striped table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Email</th>
                                <th>Type</th>
                                <th>Refferal Code</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Structure TreeView Styling */
#structureTable {
    font-size: 14px;
}

/* Fix DataTable search overflow issue */
.table-responsive .dataTables_wrapper .dataTables_filter {
    overflow: visible;
}

.table-responsive .dataTables_wrapper {
    overflow: visible;
}

.dataTables_filter {
    margin-bottom: 15px;
}

.dataTables_filter input {
    min-width: 200px;
    max-width: 100%;
}

#structureTable .tree-expand-btn {
    transition: transform 0.2s ease;
}

#structureTable .tree-expand-btn:hover {
    transform: scale(1.2);
    color: #0056b3 !important;
}

#structureTable tbody tr:hover {
    background-color: #f8f9fa;
}

#structureTable .badge {
    font-size: 0.75rem;
}

#structureTable .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

/* Custom badge colors */
.badge-info {
    background-color: #17a2b8;
    color: white;
}

.badge-success {
    background-color: #28a745;
    color: white;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    #structureTable {
        font-size: 12px;
    }
    
    #structureTable .btn {
        padding: 0.2rem 0.3rem;
        font-size: 0.7rem;
    }
}
</style>

<script type="text/javascript">
    $(document).ready(function() {
        // Konstanta konfigurasi
        const TREE_CONSTANTS = {
            INDENT_SIZE: 20,
            ICONS: {
                EXPANDED: '▼',
                COLLAPSED: '▶',
                USER: '<i class="fas fa-user text-primary me-2"></i>'
            },
            CSS_CLASSES: {
                EXPAND_BTN: 'tree-expand-btn',
                BADGE_INFO: 'badge badge-info'
            },
            API_ENDPOINTS: {
                TREE_DATA: '/ajax/post/member/update/table_treeview',
                REFERRAL_UPDATE_UPLINE: '/ajax/post/member/update/refferal_update_upline',
                REFERRAL_UPDATE_TYPE: '/ajax/post/member/update/refferal_update_type',
                REFERRAL_LIST_STRUCTURE: '/ajax/post/member/update/refferal_list_structure'
            }
        };

        // Konfigurasi tabel
        const tableConfigurations = {
            structure: {
                table: null,
                treeData: [],
                expandedNodes: new Set(),
                tableId: '#structureTable',
                searchId: '#structureTableSearch',
                statusId: '#structureStatus',
                type: null
            },
            upline: {
                table: null,
                treeData: [],
                expandedNodes: new Set(),
                tableId: '#uplineTable', 
                searchId: '#uplineTableSearch',
                statusId: '#uplineStatus',
                type: 'upline'
            }
        };

        /**
         * Setup upline change handler
         */
        function setupUplineHandler() {
            $('#upline').on('change', handleUplineChange).trigger('change');
        }

        /**
         * Handle upline selection change
         * @param {Event} event - Change event
         */
        function handleUplineChange(event) {
            const selectedOption = $(this).find('option:selected');
            
            if (selectedOption.length) {
                resetTypeSelector();
                loadStructureTypes(selectedOption.data('division'));
            }
        }

        /**
         * Reset type selector to default state
         */
        function resetTypeSelector() {
            $('#type').empty().append('<option value="">Pilih</option>');
        }

        /**
         * Load structure types based on division
         * @param {string} division - Division code
         */
        function loadStructureTypes(division) {
            const payload = {
                code: $('input[name="code"]').val(),
                division: division
            };

            $.post(TREE_CONSTANTS.API_ENDPOINTS.REFERRAL_LIST_STRUCTURE, payload, function(response) {
                if (response.success) {
                    populateTypeSelector(response.data);
                }
            }, 'json');
        }

        /**
         * Populate type selector with options
         * @param {Array} data - Type options data
         */
        function populateTypeSelector(data) {
            data.forEach(item => {
                const selected = item.selected ? 'selected' : '';
                $('#type').append(`<option value="${item.id}" ${selected}>${item.name}</option>`);
            });
        }

        /**
         * Setup form event handlers
         */
        function setupFormHandlers() {
            $('#form-update-type').on('submit', handleTypeUpdateForm);
            $('#form-update-upline').on('submit', handleUplineUpdateForm);
        }

        /**
         * Handle type update form submission
         * @param {Event} event - Form submit event
         */
        function handleTypeUpdateForm(event) {
            event.preventDefault();
            const form = $(this);
            const action = form.attr('action');
            submitFormWithLoading(form, action);
        }

        /**
         * Handle upline update form submission
         * @param {Event} event - Form submit event
         */
        function handleUplineUpdateForm(event) {
            event.preventDefault();
            const form = $(this);
            submitFormWithLoading(form, TREE_CONSTANTS.API_ENDPOINTS.REFERRAL_UPDATE_UPLINE);
        }

        /**
         * Submit form with loading state and response handling
         * @param {jQuery} form - Form element
         * @param {string} action - Form action URL
         */
        function submitFormWithLoading(form, action) {
            const button = form.find('button[type="submit"]');
            
            setButtonLoading(button, true);
            
            $.post(action, form.serialize(), function(response) {
                setButtonLoading(button, false);
                handleFormResponse(response);
            }, 'json').fail(function() {
                setButtonLoading(button, false);
                showErrorMessage('An error occurred while processing your request.');
            });
        }

        /**
         * Set button loading state
         * @param {jQuery} button - Button element
         * @param {boolean} loading - Loading state
         */
        function setButtonLoading(button, loading) {
            if (loading) {
                button.addClass('loading');
            } else {
                button.removeClass('loading');
            }
        }

        /**
         * Handle form response
         * @param {Object} response - Server response
         */
        function handleFormResponse(response) {
            Swal.fire(response.alert).then(() => {
                if (response.success) {
                    location.reload();
                }
            });
        }

        /**
         * Show error message
         * @param {string} message - Error message
         */
        function showErrorMessage(message) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message
            });
        }

        /**
         * Inisialisasi DataTable dengan fungsi struktur pohon
         * @param {string} tableType - Jenis tabel ('structure' atau 'upline')
         */
        function initTable(tableType) {
            const config = tableConfigurations[tableType];
            
            config.table = $(config.tableId).DataTable({
                data: [],
                dom: `<"${config.searchId.substring(1)}"f><"table-responsive"t>`,
                columns: createTableColumns(),
                paging: false,
                searching: true,
                ordering: false,
                info: false,
                responsive: true,
                language: {
                    emptyTable: `No ${tableType} data available`,
                    search: `Search ${tableType}:`
                }
            });

            attachTreeEventHandlers(config, tableType);
            loadData(tableType);
        }

        /**
         * Buat definisi kolom tabel
         * @returns {Array} Array konfigurasi kolom
         */
        function createTableColumns() {
            return [
                {
                    data: 'name',
                    render: renderMemberColumn
                },
                { 
                    data: 'email',
                    render: renderEmailColumn
                },
                { 
                    data: 'type',
                    render: renderTypeColumn
                },
                { data: 'refferalCode' }
            ];
        }

        /**
         * Render kolom nama member dengan struktur pohon
         * @param {string} data - Data kolom
         * @param {string} type - Jenis render
         * @param {Object} row - Data baris
         * @returns {string} HTML yang dirender
         */
        function renderMemberColumn(data, type, row) {
            if (type !== 'display') return data;
            
            const level = row.level || 0;
            const indent = level * TREE_CONSTANTS.INDENT_SIZE;
            const expandButton = createExpandButton(row);
            
            return `<div style="padding-left: ${indent}px;">${expandButton}${TREE_CONSTANTS.ICONS.USER}${data}</div>`;
        }

        /**
         * Buat tombol expand/collapse untuk node pohon
         * @param {Object} row - Data baris
         * @returns {string} HTML tombol
         */
        function createExpandButton(row) {
            if (!row.hasChildren) {
                return '<span style="margin-right: 20px;"></span>';
            }
            
            const icon = TREE_CONSTANTS.ICONS.COLLAPSED;
            return `<span class="${TREE_CONSTANTS.CSS_CLASSES.EXPAND_BTN}" data-id="${row.id}" style="cursor: pointer; margin-right: 8px; user-select: none; font-weight: bold; color: #007bff;">${icon}</span>`;
        }

        /**
         * Render kolom email dengan link
         * @param {string} data - Data email
         * @param {string} type - Jenis render
         * @param {Object} row - Data baris
         * @returns {string} HTML yang dirender
         */
        function renderEmailColumn(data, type, row) {
            if (type !== 'display') return data;
            return `<a href="/member/user/update/${row.originalId}" target="_blank" style="text-decoration: none; color: #007bff;">${data}</a>`;
        }

        /**
         * Render kolom tipe dengan badge
         * @param {string} data - Data tipe
         * @returns {string} HTML yang dirender
         */
        function renderTypeColumn(data) {
            return `<span class="${TREE_CONSTANTS.CSS_CLASSES.BADGE_INFO}">${data}</span>`;
        }

        /**
         * Pasang event handler untuk fungsi pohon
         * @param {Object} config - Konfigurasi tabel
         * @param {string} tableType - Jenis tabel
         */
        function attachTreeEventHandlers(config, tableType) {
            $(config.tableId + ' tbody').on('click', `.${TREE_CONSTANTS.CSS_CLASSES.EXPAND_BTN}`, function() {
                const nodeId = $(this).data('id');
                toggleNode(nodeId, tableType);
            });
        }

        /**
         * Load tree data from server
         * @param {string} tableType - Table type to load data for
         */
        function loadData(tableType) {
            const config = tableConfigurations[tableType];
            
            updateStatus(config.statusId, 'loading');
            
            const payload = createRequestPayload(config);
            
            $.post(TREE_CONSTANTS.API_ENDPOINTS.TREE_DATA, payload, function(response) {
                handleDataResponse(response, config, tableType);
            }, 'json').fail(function() {
                handleDataError(config);
            });
        }

        /**
         * Create request payload for API call
         * @param {Object} config - Table configuration
         * @returns {Object} Request payload
         */
        function createRequestPayload(config) {
            const payload = { code: $('input[name="code"]').val() };
            if (config.type) payload.type = config.type;
            return payload;
        }

        /**
         * Handle successful data response
         * @param {Object} response - Server response
         * @param {Object} config - Table configuration
         * @param {string} tableType - Table type
         */
        function handleDataResponse(response, config, tableType) {
            if (response && response.success) {
                config.treeData = flattenTreeData(response.data, null, 0, tableType);
                config.expandedNodes.clear();
                
                // Auto expand hanya untuk tabel upline
                if (tableType === 'upline') {
                    config.treeData.forEach(row => {
                        if (row.hasChildren) {
                            config.expandedNodes.add(row.id);
                        }
                    });
                }
                
                updateTableDisplay(tableType);
                updateStatus(config.statusId, 'success', config.treeData.length);
            } else {
                handleDataError(config);
            }
        }

        /**
         * Handle data loading errors
         * @param {Object} config - Table configuration
         */
        function handleDataError(config) {
            updateStatus(config.statusId, 'error');
            config.table.clear().draw();
        }

        /**
         * Update status indicator
         * @param {string} statusSelector - Status element selector
         * @param {string} type - Status type ('loading', 'success', 'error')
         * @param {number} count - Record count for success status
         */
        function updateStatus(statusSelector, type, count = 0) {
            const statusMessages = {
                loading: '<span class="spinner-border spinner-border-sm"></span> Loading...',
                success: `<i class="fas fa-check-circle text-success"></i> ${count} records loaded`,
                error: '<i class="fas fa-exclamation-circle text-danger"></i> Failed to load data'
            };
            
            $(statusSelector).html(statusMessages[type]);
        }

        /**
         * Flatten hierarchical tree data into a flat array
         * @param {Array} nodes - Tree nodes
         * @param {string|null} parentId - Parent node ID
         * @param {number} level - Tree level (depth)
         * @param {string} tableType - Table type for ID generation
         * @returns {Array} Flattened tree data
         */
        function flattenTreeData(nodes, parentId = null, level = 0, tableType = 'structure') {
            const result = [];
            
            nodes.forEach((node, index) => {
                const currentId = generateNodeId(tableType, level, index);
                const hasChildren = Array.isArray(node.children) && node.children.length > 0;
                
                result.push(createTreeNode(node, currentId, level, parentId, hasChildren));
                
                if (hasChildren) {
                    const childNodes = flattenTreeData(node.children, currentId, level + 1, tableType);
                    result.push(...childNodes);
                }
            });
            
            return result;
        }

        /**
         * Generate unique node ID
         * @param {string} tableType - Table type
         * @param {number} level - Tree level
         * @param {number} index - Node index
         * @returns {string} Generated node ID
         */
        function generateNodeId(tableType, level, index) {
            return `${tableType}_node_${level}_${index}`;
        }

        /**
         * Create tree node object
         * @param {Object} node - Original node data
         * @param {string} currentId - Generated node ID
         * @param {number} level - Tree level
         * @param {string|null} parentId - Parent node ID
         * @param {boolean} hasChildren - Whether node has children
         * @returns {Object} Tree node object
         */
        function createTreeNode(node, currentId, level, parentId, hasChildren) {
            return {
                id: currentId,
                originalId: node.id,
                name: node.name,
                email: node.email,
                type: node.type,
                refferalCode: node.refferalCode || '-',
                level: level,
                parentId: parentId,
                hasChildren: hasChildren
            };
        }

        /**
         * Get currently visible tree data (expanded nodes only)
         * @param {string} tableType - Table type
         * @returns {Array} Visible tree data
         */
        function getVisibleData(tableType) {
            const config = tableConfigurations[tableType];
            return config.treeData.filter(row => {
                return row.level === 0 || config.expandedNodes.has(row.parentId);
            });
        }

        /**
         * Toggle node expansion state
         * @param {string} nodeId - Node ID to toggle
         * @param {string} tableType - Table type
         */
        function toggleNode(nodeId, tableType) {
            const config = tableConfigurations[tableType];
            
            if (config.expandedNodes.has(nodeId)) {
                collapseNode(nodeId, tableType);
            } else {
                expandNode(nodeId, tableType);
            }
            
            updateTableDisplay(tableType);
        }

        /**
         * Expand a tree node
         * @param {string} nodeId - Node ID to expand
         * @param {string} tableType - Table type
         */
        function expandNode(nodeId, tableType) {
            const config = tableConfigurations[tableType];
            config.expandedNodes.add(nodeId);
        }

        /**
         * Collapse a tree node and all its descendants
         * @param {string} nodeId - Node ID to collapse
         * @param {string} tableType - Table type
         */
        function collapseNode(nodeId, tableType) {
            const config = tableConfigurations[tableType];
            config.expandedNodes.delete(nodeId);
            collapseDescendants(nodeId, tableType);
        }

        /**
         * Recursively collapse all descendant nodes
         * @param {string} parentId - Parent node ID
         * @param {string} tableType - Table type
         */
        function collapseDescendants(parentId, tableType) {
            const config = tableConfigurations[tableType];
            
            config.treeData.forEach(row => {
                if (row.parentId === parentId) {
                    config.expandedNodes.delete(row.id);
                    collapseDescendants(row.id, tableType);
                }
            });
        }

        /**
         * Update table display with current visible data
         * @param {string} tableType - Table type to update
         */
        function updateTableDisplay(tableType) {
            const config = tableConfigurations[tableType];
            const visibleData = getVisibleData(tableType);
            config.table.clear().rows.add(visibleData).draw();
        }

        /**
         * Setup event handlers for all tree control buttons
         */
        function setupButtonHandlers() {
            const buttonMappings = {
                '#expandAll': () => expandAllNodes('structure'),
                '#collapseAll': () => collapseAllNodes('structure'),
                '#refreshStructure': () => refreshTable('structure'),
                '#expandAllUpline': () => expandAllNodes('upline'),
                '#collapseAllUpline': () => collapseAllNodes('upline'),
                '#refreshUpline': () => refreshTable('upline')
            };

            Object.entries(buttonMappings).forEach(([selector, handler]) => {
                $(selector).on('click', handler);
            });
        }

        /**
         * Expand all nodes in a table
         * @param {string} tableType - Table type to expand
         */
        function expandAllNodes(tableType) {
            const config = tableConfigurations[tableType];
            config.expandedNodes.clear();
            
            config.treeData
                .filter(row => row.hasChildren)
                .forEach(row => config.expandedNodes.add(row.id));
            
            updateTableDisplay(tableType);
        }

        /**
         * Collapse all nodes in a table
         * @param {string} tableType - Table type to collapse
         */
        function collapseAllNodes(tableType) {
            const config = tableConfigurations[tableType];
            config.expandedNodes.clear();
            updateTableDisplay(tableType);
        }

        /**
         * Refresh table data from server
         * @param {string} tableType - Table type to refresh
         */
        function refreshTable(tableType) {
            const config = tableConfigurations[tableType];
            config.expandedNodes.clear();
            loadData(tableType);
        }

        /**
         * Initialize the application
         */
        function initializeApplication() {
            setupUplineHandler();
            setupFormHandlers();
            initTable('structure');
            initTable('upline');
            setupButtonHandlers();
        }

        // Initialize application
        initializeApplication();


    })
</script>