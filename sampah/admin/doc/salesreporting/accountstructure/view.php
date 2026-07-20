<!-- AG Grid CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community@31.0.0/styles/ag-grid.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community@31.0.0/styles/ag-theme-alpine.min.css">

<style>
    .ag-theme-alpine {
        --ag-grid-size: 4px;
        --ag-font-size: 13px;
    }
    
    #accountGrid {
        height: 600px;
        width: 100%;
    }
    
    /* Custom styling for account rows */
    .account-row-style {
        background-color: #f8f9fa !important;
        font-style: italic;
    }
    
    .member-row-style {
        font-weight: 500;
    }
    
    /* Cell icons */
    .cell-icon {
        margin-right: 8px;
    }
    
    .member-icon {
        color: #007bff;
    }
    
    .account-icon {
        color: #28a745;
    }
</style>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Account Structure</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item">Sales Reporting</li>
            <li class="breadcrumb-item active" aria-current="page">Account Structure</li>
        </ol>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="mb-3">
                    <div class="row">
                        <div class="col-md-6">
                            <button class="btn btn-primary btn-sm" id="expandAll"><i class="fa fa-expand"></i> Expand All</button>
                            <button class="btn btn-secondary btn-sm" id="collapseAll"><i class="fa fa-compress"></i> Collapse All</button>
                            <button class="btn btn-success btn-sm" id="exportExcel"><i class="fa fa-file-csv"></i> Export CSV</button>
                        </div>
                        <div class="col-md-6 text-end">
                            <span class="text-muted" id="gridStatus">Loading...</span>
                        </div>
                    </div>
                </div>
                <div id="accountGrid" class="ag-theme-alpine"></div>
            </div>
        </div>
    </div>
</div>

<!-- AG Grid JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/ag-grid-community@31.0.0/dist/ag-grid-community.min.js"></script>

<script>
    let gridApi;
    let gridColumnApi;
    
    $(document).ready(() => {
        initAGGrid();
    });

    function initAGGrid() {
        // Column definitions
        const columnDefs = [
            {
                field: 'name',
                headerName: 'Member / Account',
                cellRenderer: params => {
                    if (!params.data) return '';
                    
                    const isAccount = params.data.is_account;
                    const icon = isAccount ? 
                        '<i class="fas fa-wallet cell-icon account-icon"></i>' : 
                        '<i class="fas fa-user cell-icon member-icon"></i>';
                    
                    // Add indentation based on level
                    const level = params.data.level || 0;
                    const indent = level * 20;
                    
                    // Add expand/collapse button if has children
                    let expandButton = '';
                    if (params.data.hasChildren && !isAccount) {
                        const isExpanded = expandedNodes.has(params.data.rowId);
                        const icon = isExpanded ? '▼' : '▶';
                        expandButton = '<span class="tree-expand-btn" style="margin-right: 5px; cursor: pointer; user-select: none; font-weight: bold;">' + icon + '</span>';
                    } else if (!isAccount) {
                        expandButton = '<span style="margin-right: 5px; display: inline-block; width: 14px;"></span>';
                    }
                    
                    return '<div style="padding-left: ' + indent + 'px;">' + expandButton + icon + params.value + '</div>';
                },
                flex: 2,
                filter: 'agTextColumnFilter',
                floatingFilter: true
            },
            {
                field: 'code',
                headerName: 'Code',
                flex: 1,
                filter: 'agTextColumnFilter',
                floatingFilter: true
            },
            {
                field: 'email',
                headerName: 'Email',
                flex: 1.5,
                filter: 'agTextColumnFilter',
                floatingFilter: true
            },
            {
                field: 'balance',
                headerName: 'Balance',
                flex: 1,
                type: 'numericColumn',
                valueFormatter: params => params.value ? '$' + parseFloat(params.value).toFixed(2) : '-',
                filter: 'agNumberColumnFilter',
                floatingFilter: true
            },
            {
                field: 'deposit',
                headerName: 'Deposit',
                flex: 1,
                type: 'numericColumn',
                valueFormatter: params => params.value ? '$' + parseFloat(params.value).toFixed(2) : '-',
                filter: 'agNumberColumnFilter',
                floatingFilter: true
            },
            {
                field: 'withdrawal',
                headerName: 'Withdrawal',
                flex: 1,
                type: 'numericColumn',
                valueFormatter: params => params.value ? '$' + parseFloat(params.value).toFixed(2) : '-',
                filter: 'agNumberColumnFilter',
                floatingFilter: true
            },
            {
                field: 'parent_id',
                headerName: 'Parent ID',
                flex: 0.8,
                filter: 'agTextColumnFilter'
            },
            {
                field: 'type',
                headerName: 'Type',
                flex: 1,
                filter: 'agTextColumnFilter',
                floatingFilter: true
            }
        ];

        // Grid options
        const gridOptions = {
            columnDefs: columnDefs,
            defaultColDef: {
                sortable: true,
                resizable: true,
                filter: true,
            },
            animateRows: false,
            suppressScrollOnNewData: true,
            getRowId: params => 'row-' + params.data.rowId,
            isExternalFilterPresent: () => true,
            doesExternalFilterPass: node => {
                if (!node.data) return true;
                if (node.data.level === 0) return true;
                return expandedNodes.has(node.data.parentRowId);
            },
            getRowStyle: params => {
                if (params.data && params.data.is_account) {
                    return { background: '#f8f9fa', fontStyle: 'italic' };
                }
                return null;
            },
            onFirstDataRendered: params => {
                params.api.sizeColumnsToFit();
            },
            onCellClicked: params => {
                const target = params.event.target;
                if (target.classList.contains('tree-expand-btn')) {
                    toggleTreeNode(params.node);
                }
            }
        };

        // Create grid using createGrid (v31+)
        const gridDiv = document.querySelector('#accountGrid');
        gridApi = agGrid.createGrid(gridDiv, gridOptions);
        
        // Load data after grid is created
        loadTreeData();
    }

    let treeDataCache = [];
    let expandedNodes = new Set();

    function loadTreeData() {
        $('#gridStatus').html('<span class="spinner-border spinner-border-sm"></span> Loading data...');
        
        $.post("/ajax/post/salesreporting/accountstructure/treeview", {}, function(resp) {
            console.log('Tree response:', resp);
            
            if(resp && resp.length > 0) {
                treeDataCache = flattenTreeData(resp, null, 0);
                console.log('Flat data:', treeDataCache);
                
                // Show all data initially, but mark visible property
                treeDataCache.forEach(row => {
                    row.visible = row.level === 0;
                });
                
                gridApi.updateGridOptions({ rowData: treeDataCache });
                
                $('#gridStatus').html(`<i class="fa fa-check-circle text-success"></i> ${treeDataCache.length} records loaded`);
            } else {
                gridApi.updateGridOptions({ rowData: [] });
                $('#gridStatus').html('<i class="fa fa-info-circle text-info"></i> No data available');
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error('Tree error:', xhr.responseText);
            gridApi.updateGridOptions({ rowData: [] });
            $('#gridStatus').html('<i class="fa fa-exclamation-circle text-danger"></i> Failed to load data');
        });
    }

    function toggleTreeNode(node) {
        const rowId = node.data.rowId;
        
        if (expandedNodes.has(rowId)) {
            // Collapse
            expandedNodes.delete(rowId);
            collapseDescendants(rowId);
        } else {
            // Expand
            expandedNodes.add(rowId);
        }
        
        // Reapply external filter without losing scroll position
        gridApi.onFilterChanged();
        
        // Update button icon - use data attribute to find button
        setTimeout(() => {
            gridApi.forEachNode(n => {
                if (n.data && n.data.rowId === rowId) {
                    gridApi.refreshCells({ rowNodes: [n], force: true });
                }
            });
        }, 50);
    }

    function collapseDescendants(parentRowId) {
        treeDataCache.forEach(row => {
            if (row.parentRowId === parentRowId) {
                expandedNodes.delete(row.rowId);
                collapseDescendants(row.rowId);
            }
        });
    }

    let rowIdCounter = 0;

    function flattenTreeData(nodes, parentRowId = null, level = 0) {
        let result = [];
        
        nodes.forEach((node, index) => {
            // Determine if this is an account or member
            const isAccount = node.is_account || false;
            const hasChildren = node.nodes && node.nodes.length > 0;
            
            const currentRowId = rowIdCounter++;
            
            let rowData;
            
            if (isAccount) {
                // Account row
                rowData = {
                    rowId: currentRowId,
                    parentRowId: parentRowId,
                    name: node.text || '',
                    code: node.account_login || '-',
                    email: '-',
                    balance: node.account_balance ? parseFloat(node.account_balance.replace(/,/g, '')) : null,
                    deposit: node.account_deposit ? parseFloat(node.account_deposit.replace(/,/g, '')) : null,
                    withdrawal: node.account_withdrawal ? parseFloat(node.account_withdrawal.replace(/,/g, '')) : null,
                    parent_id: '-',
                    type: 'Account',
                    is_account: true,
                    hasChildren: false,
                    level: level
                };
            } else {
                // Member row - parse text format: "Name (Code) - Type"
                const textParts = node.text.match(/^(.+?)\s+\((.+?)\)\s+-\s+(.+)$/);
                const memberName = textParts ? textParts[1] : node.text;
                const memberCode = textParts ? textParts[2] : '';
                const memberType = textParts ? textParts[3] : '';
                
                rowData = {
                    rowId: currentRowId,
                    parentRowId: parentRowId,
                    name: memberName,
                    code: memberCode,
                    email: node.email || '-',
                    balance: null,
                    deposit: null,
                    withdrawal: null,
                    parent_id: node.parent_id || '-',
                    type: memberType,
                    is_account: false,
                    hasChildren: hasChildren,
                    level: level
                };
            }
            
            result.push(rowData);
            
            // Process children recursively
            if (hasChildren) {
                const childData = flattenTreeData(node.nodes, currentRowId, level + 1);
                result = result.concat(childData);
            }
        });
        
        return result;
    }

    // Button handlers
    $('#expandAll').on('click', function() {
        expandedNodes.clear();
        treeDataCache.forEach(row => {
            if (row.hasChildren) {
                expandedNodes.add(row.rowId);
            }
        });
        gridApi.onFilterChanged();
        gridApi.refreshCells({ force: true });
    });

    $('#collapseAll').on('click', function() {
        expandedNodes.clear();
        gridApi.onFilterChanged();
        gridApi.refreshCells({ force: true });
    });

    $('#exportExcel').on('click', function() {
        gridApi.exportDataAsCsv({
            fileName: 'account-structure-' + new Date().toISOString().split('T')[0] + '.csv'
        });
    });
</script>
