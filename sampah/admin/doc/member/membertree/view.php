<?php
// Query untuk mengambil data member menggunakan MBR_ID dan MBR_IDSPN
global $db;

if (!$db) {
    die('Database connection not available');
}

$query = "SELECT 
    m.MBR_ID, 
    m.MBR_CODE, 
    m.MBR_IDSPN, 
    m.MBR_NAME, 
    m.MBR_EMAIL,
    r.ID_ACC,
    mt5.login as ACC_LOGIN,
    mt5.balance as ACC_BALANCE,
    COALESCE(SUM(CASE WHEN d.action = 2 AND d.profit > 0 THEN d.profit ELSE 0 END), 0) as ACC_DEPOSIT,
    COALESCE(ABS(SUM(CASE WHEN d.action = 2 AND d.profit < 0 THEN d.profit ELSE 0 END)), 0) as ACC_WITHDRAWAL
FROM tb_member m
LEFT JOIN tb_racc r ON r.ACC_MBR = m.MBR_ID
LEFT JOIN meta_rrfxreal.mt5_users mt5 ON mt5.login = r.ACC_LOGIN
LEFT JOIN meta_rrfxreal.mt5_deals d ON d.login = mt5.login
GROUP BY m.MBR_ID, m.MBR_CODE, m.MBR_IDSPN, m.MBR_NAME, m.MBR_EMAIL, r.ID_ACC, mt5.login, mt5.balance
ORDER BY m.MBR_ID, mt5.login";
$result = $db->query($query);

$members = [];
$memberAccounts = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $memberId = $row['MBR_ID'];
        
        // Add member data only once
        if (!isset($members[$memberId])) {
            $members[$memberId] = [
                'id' => $row['MBR_ID'],
                'code' => $row['MBR_CODE'],
                'parent' => $row['MBR_IDSPN'],
                'name' => $row['MBR_NAME'],
                'email' => $row['MBR_EMAIL']
            ];
        }
        
        // Collect accounts for this member
        if ($row['ACC_LOGIN']) {
            if (!isset($memberAccounts[$memberId])) {
                $memberAccounts[$memberId] = [];
            }
            
            $deposit = floatval($row['ACC_DEPOSIT']);
            $withdrawal = floatval($row['ACC_WITHDRAWAL']);
            $nmi = $deposit - $withdrawal;
            
            // Generate double MD5 hash for ID_ACC
            $accIdHash = md5(md5($row['ID_ACC']));
            
            $memberAccounts[$memberId][] = [
                'login' => $row['ACC_LOGIN'],
                'balance' => $row['ACC_BALANCE'],
                'deposit' => $deposit,
                'withdrawal' => $withdrawal,
                'nmi' => $nmi,
                'acc_id_hash' => $accIdHash
            ];
        }
    }
    
    // Merge accounts into members array
    $members = array_values($members);
    foreach ($members as &$member) {
        if (isset($memberAccounts[$member['id']])) {
            $member['accounts'] = $memberAccounts[$member['id']];
        } else {
            $member['accounts'] = null;
        }
    }
    unset($member);
    
} else {
    // Log error jika query gagal
    error_log("Member Tree Query Error: " . ($db->error ? $db->error : 'No results'));
}
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5"><?php echo $vp = 'Member Tree'; ?></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= pathbreadcrumb(0) ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item">Member</li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo $vp; ?></li>
        </ol>
    </div>
</div>

<!-- Row -->
<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-body">
                <style>
                    .mbrtree-container {
                        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                        font-size: 13px;
                        display: block !important;
                        visibility: visible !important;
                    }
                    
                    .mbrtree-table {
                        width: 100%;
                        border-collapse: collapse;
                        background: white;
                    }
                    
                    .mbrtree-table thead th {
                        background: #f8f9fa;
                        padding: 12px;
                        text-align: left;
                        border: 1px solid #dee2e6;
                        font-weight: 600;
                        position: sticky;
                        top: 0;
                        z-index: 10;
                    }
                    
                    .mbrtree-table tbody tr {
                        border: 1px solid #dee2e6;
                        transition: background-color 0.2s ease;
                    }
                    
                    .mbrtree-table tbody tr:hover {
                        background: #f8f9fa;
                    }
                    
                    .mbrtree-table tbody tr.mbrtree-highlight {
                        background: #fff3cd;
                    }
                    
                    .mbrtree-table tbody td {
                        padding: 8px 12px;
                        border: 1px solid #dee2e6;
                        vertical-align: middle;
                    }
                    
                    .mbrtree-name-cell {
                        display: flex;
                        align-items: center;
                        gap: 6px;
                        position: relative;
                    }
                    
                    /* Tree lines styling */
                    .mbrtree-table tbody tr td:first-child {
                        position: relative;
                    }
                    
                    .mbrtree-table tbody tr td:first-child::before {
                        content: '';
                        position: absolute;
                        top: 0;
                        height: 50%;
                        width: 16px;
                        border-left: 1px solid #d0d7de;
                        border-bottom: 1px solid #d0d7de;
                    }
                    
                    .mbrtree-table tbody tr td:first-child::after {
                        content: '';
                        position: absolute;
                        top: 50%;
                        bottom: -1px;
                        width: 1px;
                        border-left: 1px solid #d0d7de;
                    }
                    
                    /* Remove lines for root level (level-0) */
                    .mbrtree-table tbody tr td.level-0::before,
                    .mbrtree-table tbody tr td.level-0::after {
                        display: none;
                    }
                    
                    /* Hide bottom line for last child in each group */
                    .mbrtree-table tbody tr.last-child td:first-child::after {
                        display: none;
                    }
                    
                    /* Adjust line positions based on indentation level */
                    .level-1::before { left: 24px; }
                    .level-1::after { left: 24px; }
                    .level-2::before { left: 44px; }
                    .level-2::after { left: 44px; }
                    .level-3::before { left: 64px; }
                    .level-3::after { left: 64px; }
                    .level-4::before { left: 84px; }
                    .level-4::after { left: 84px; }
                    .level-5::before { left: 104px; }
                    .level-5::after { left: 104px; }
                    .level-6::before { left: 124px; }
                    .level-6::after { left: 124px; }
                    .level-7::before { left: 144px; }
                    .level-7::after { left: 144px; }
                    .level-8::before { left: 164px; }
                    .level-8::after { left: 164px; }
                    .level-9::before { left: 184px; }
                    .level-9::after { left: 184px; }
                    .level-10::before { left: 204px; }
                    .level-10::after { left: 204px; }
                    
                    .mbrtree-toggle {
                        cursor: pointer;
                        width: 16px;
                        height: 16px;
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        user-select: none;
                        transition: transform 0.2s ease;
                    }
                    
                    .mbrtree-toggle.no-children {
                        cursor: default;
                        opacity: 0;
                    }
                    
                    .mbrtree-toggle:not(.no-children):hover {
                        color: #0056b3;
                        transform: scale(1.1);
                    }
                    
                    .mbrtree-toggle.no-children {
                        visibility: hidden;
                    }
                    
                    .mbrtree-icon {
                        width: 16px;
                        height: 16px;
                        flex-shrink: 0;
                        font-size: 14px;
                    }
                    
                    .mbrtree-member-name {
                        font-weight: 500;
                        color: #333;
                    }
                    
                    .mbrtree-open-link {
                        cursor: pointer;
                        padding: 4px 8px;
                        border-radius: 3px;
                        transition: all 0.2s ease;
                        font-size: 14px;
                        opacity: 0.6;
                        display: inline-block;
                    }
                    
                    .mbrtree-open-link:hover {
                        opacity: 1;
                        background-color: #e3f2fd;
                        transform: scale(1.1);
                    }
                    
                    .mbrtree-badge {
                        display: inline-flex;
                        align-items: center;
                        padding: 2px 8px;
                        border-radius: 3px;
                        font-size: 11px;
                        white-space: nowrap;
                    }
                    
                    .mbrtree-badge-count {
                        background: #e8f5e9;
                        color: #388e3c;
                        font-weight: 600;
                    }
                    
                    .mbrtree-badge-balance {
                        background: #fff3e0;
                        color: #f57c00;
                        font-weight: 600;
                    }
                    
                    .mbrtree-badge-deposit {
                        background: #e8f5e9;
                        color: #2e7d32;
                        font-weight: 600;
                    }
                    
                    .mbrtree-badge-withdrawal {
                        background: #ffebee;
                        color: #c62828;
                        font-weight: 600;
                    }
                    
                    .mbrtree-badge-nmi {
                        background: #e3f2fd;
                        color: #1565c0;
                        font-weight: 600;
                    }
                    
                    .mbrtree-account-balance {
                        color: #1976d2;
                        font-weight: 600;
                        font-size: 12px;
                    }
                    
                    .mbrtree-account-deposit {
                        color: #2e7d32;
                        font-weight: 600;
                        font-size: 12px;
                    }
                    
                    .mbrtree-account-withdrawal {
                        color: #c62828;
                        font-weight: 600;
                        font-size: 12px;
                    }
                    
                    .mbrtree-account-nmi {
                        color: #1565c0;
                        font-weight: 600;
                        font-size: 12px;
                    }
                    
                    .mbrtree-account-row {
                        background: #f8f9fa;
                        font-style: italic;
                    }
                    
                    .mbrtree-account-row:hover {
                        background: #e9ecef;
                    }
                    
                    .mbrtree-account-login {
                        color: #1976d2;
                        font-weight: 500;
                        font-size: 12px;
                    }
                    
                    .mbrtree-hidden {
                        display: none !important;
                    }
                    
                    .text-center {
                        text-align: center;
                    }
                    
                    /* Indentation levels */
                    .level-0 { padding-left: 12px !important; }
                    .level-1 { padding-left: 44px !important; }
                    .level-2 { padding-left: 64px !important; }
                    .level-3 { padding-left: 84px !important; }
                    .level-4 { padding-left: 104px !important; }
                    .level-5 { padding-left: 124px !important; }
                    .level-6 { padding-left: 144px !important; }
                    .level-7 { padding-left: 164px !important; }
                    .level-8 { padding-left: 184px !important; }
                    .level-9 { padding-left: 204px !important; }
                    .level-10 { padding-left: 224px !important; }
                    
                    .mbrtree-empty-state {
                        text-align: center;
                        padding: 40px 20px;
                        color: #999;
                        font-size: 14px;
                    }
                </style>
                
                <div class="mb-3">
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fe fe-search"></i></span>
                                <input type="text" class="form-control" id="mbrTreeSearch" placeholder="Search by name, ID, or email..." onkeyup="mbrTreeSearch()">
                                <button class="btn btn-outline-secondary" type="button" onclick="mbrTreeClearSearch()">
                                    <i class="fe fe-x"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <span class="badge bg-primary" style="font-size: 14px; padding: 8px 12px;">
                                Total Members: <strong><?= count($members) ?></strong>
                            </span>
                            <span class="badge bg-success ms-2" style="font-size: 14px; padding: 8px 12px;" id="mbrTreeMatchCount">
                                Showing: <strong id="mbrTreeMatchNumber">-</strong>
                            </span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-start align-items-center">
                        <button type="button" class="btn btn-sm btn-primary" onclick="mbrTreeExpandAll()">
                            <i class="fe fe-maximize"></i> Expand All
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary ms-2" onclick="mbrTreeCollapseAll()">
                            <i class="fe fe-minimize"></i> Collapse All
                        </button>
                        <button type="button" class="btn btn-sm btn-info ms-2" onclick="mbrTreeToggleFilter()" id="mbrTreeFilterBtn">
                            <i class="fe fe-filter"></i> Show Full Tree
                        </button>
                    </div>
                </div>
                
                <?php if (empty($members)): ?>
                    <div class="mbrtree-empty-state">No data available</div>
                <?php else: ?>
                    <div id="mbrtree-container" class="mbrtree-container" style="min-height: 400px; overflow: auto; background: white; padding: 15px; border: 1px solid #e0e0e0; border-radius: 4px;"></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Data dari PHP
    const mbrTreeData = <?= json_encode($members) ?>;
    let mbrTreeShowFull = false; // Toggle state for showing full tree
    
    // Count total descendants recursively
    function mbrTreeCountDescendants(node) {
        if (!node.children || node.children.length === 0) {
            return 0;
        }
        let count = node.children.length;
        node.children.forEach(child => {
            count += mbrTreeCountDescendants(child);
        });
        return count;
    }
    
    // Build tree structure
    function mbrTreeBuildStructure(data, showFull = false) {
        const map = {};
        const roots = [];
        
        // First pass: create map of all items
        data.forEach(item => {
            map[item.id] = {...item, children: []};
        });
        
        // Second pass: build parent-child relationships
        data.forEach(item => {
            const node = map[item.id];
            
            // Check if parent is NULL, empty, or not exists
            if (!item.parent || item.parent === null || item.parent === '' || item.parent === '0') {
                // MBR_IDSPN is NULL or empty, add to root (paling atas)
                roots.push(node);
            } else if (map[item.parent]) {
                // Parent exists in map, add as child to parent
                map[item.parent].children.push(node);
            } else {
                // Parent not found in map, treat as root (orphan)
                roots.push(node);
            }
        });
        
        let finalRoots;
        
        if (showFull) {
            // Show full tree - all nodes including leaf nodes
            finalRoots = roots.filter(root => root.children && root.children.length > 0);
        } else {
            // Filtered view - remove leaf nodes
            // Recursive function to remove leaf nodes (nodes without children)
            function removeLeafNodes(node) {
                if (!node.children || node.children.length === 0) {
                    // This is a leaf node, return null to remove it
                    return null;
                }
                
                // Process children recursively and filter out leaf nodes
                const filteredChildren = node.children
                    .map(child => removeLeafNodes(child))
                    .filter(child => child !== null);
                
                // Return node with filtered children
                return {...node, children: filteredChildren};
            }
            
            // Filter: only keep root nodes that have children
            let filteredRoots = roots.filter(root => root.children && root.children.length > 0);
            
            // Apply leaf node removal to each root
            finalRoots = filteredRoots.map(root => {
                const filteredChildren = root.children
                    .map(child => removeLeafNodes(child))
                    .filter(child => child !== null);
                return {...root, children: filteredChildren};
            });
        }
        
        // Sort by total descendants count (highest first)
        finalRoots.forEach(root => {
            root.totalDescendants = mbrTreeCountDescendants(root);
        });
        finalRoots.sort((a, b) => b.totalDescendants - a.totalDescendants);
        
        // Calculate totals recursively for all nodes
        function calculateNodeTotals(node) {
            let totals = {
                balance: 0,
                deposit: 0,
                withdrawal: 0,
                nmi: 0
            };
            
            // Add own account totals
            if (node.accounts && Array.isArray(node.accounts)) {
                node.accounts.forEach(acc => {
                    totals.balance += parseFloat(acc.balance) || 0;
                    totals.deposit += parseFloat(acc.deposit) || 0;
                    totals.withdrawal += parseFloat(acc.withdrawal) || 0;
                    totals.nmi += parseFloat(acc.nmi) || 0;
                });
            }
            
            // Add children totals recursively
            if (node.children && node.children.length > 0) {
                node.children.forEach(child => {
                    const childTotals = calculateNodeTotals(child);
                    totals.balance += childTotals.balance;
                    totals.deposit += childTotals.deposit;
                    totals.withdrawal += childTotals.withdrawal;
                    totals.nmi += childTotals.nmi;
                });
            }
            
            // Store totals in node
            node.totalBalance = totals.balance;
            node.totalDeposit = totals.deposit;
            node.totalWithdrawal = totals.withdrawal;
            node.totalNMI = totals.nmi;
            
            return totals;
        }
        
        // Calculate totals for all root nodes
        finalRoots.forEach(root => {
            calculateNodeTotals(root);
        });
        
        return finalRoots;
    }
    
    // Render tree node as table row
    function mbrTreeRenderNode(node, level = 0, parentId = '') {
        const hasChildren = node.children && node.children.length > 0;
        const childCount = hasChildren ? node.children.length : 0;
        const memberCode = node.code || node.id;
        const updateUrl = '<?= pathbreadcrumb(0) ?>/member/user/update/' + encodeURIComponent(memberCode);
        const rowId = 'row-' + node.id;
        const toggleClass = hasChildren ? '' : 'no-children';
        
        let html = '<tr class="mbrtree-row" id="' + rowId + '" data-level="' + level + '" data-parent="' + parentId + '" data-id="' + node.id + '" data-has-children="' + hasChildren + '">';
        
        // Name column with tree structure
        html += '<td class="level-' + level + '" data-level-num="' + level + '">';
        html += '<div class="mbrtree-name-cell">';
        html += '<span class="mbrtree-toggle ' + toggleClass + '" onclick="mbrTreeToggleRow(this)" title="Expand/Collapse">';
        html += hasChildren ? '▼' : '';
        html += '</span>';
        html += '<span class="mbrtree-icon">' + (hasChildren ? '📁' : '📄') + '</span>';
        html += '<span class="mbrtree-member-name">' + mbrTreeEscapeHtml(node.name || 'Unknown') + '</span>';
        html += '</div>';
        html += '</td>';
        
        // Code column
        html += '<td>' + mbrTreeEscapeHtml(memberCode) + '</td>';
        
        // Email column
        html += '<td>' + mbrTreeEscapeHtml(node.email || '-') + '</td>';
        
        // Balance column (total for member + all children)
        html += '<td class="text-end">';
        if (node.totalBalance !== undefined && node.totalBalance > 0) {
            html += '<span class="mbrtree-badge mbrtree-badge-balance" title="Total Balance (including children)">$' + mbrTreeFormatNumber(node.totalBalance) + '</span>';
        } else {
            html += '-';
        }
        html += '</td>';
        
        // Deposit column (total for member + all children)
        html += '<td class="text-end">';
        if (node.totalDeposit !== undefined && node.totalDeposit > 0) {
            html += '<span class="mbrtree-badge mbrtree-badge-deposit" title="Total Deposit (including children)">$' + mbrTreeFormatNumber(node.totalDeposit) + '</span>';
        } else {
            html += '-';
        }
        html += '</td>';
        
        // Withdrawal column (total for member + all children)
        html += '<td class="text-end">';
        if (node.totalWithdrawal !== undefined && node.totalWithdrawal > 0) {
            html += '<span class="mbrtree-badge mbrtree-badge-withdrawal" title="Total Withdrawal (including children)">$' + mbrTreeFormatNumber(node.totalWithdrawal) + '</span>';
        } else {
            html += '-';
        }
        html += '</td>';
        
        // NMI column (total for member + all children)
        html += '<td class="text-end">';
        if (node.totalNMI !== undefined) {
            html += '<span class="mbrtree-badge mbrtree-badge-nmi" title="Total NMI (including children)">$' + mbrTreeFormatNumber(node.totalNMI) + '</span>';
        } else {
            html += '-';
        }
        html += '</td>';
        
        // Children count column
        html += '<td class="text-center">';
        if (hasChildren) {
            html += '<span class="mbrtree-badge mbrtree-badge-count">' + childCount + '</span>';
        } else {
            html += '-';
        }
        html += '</td>';
        
        // Action column
        html += '<td class="text-center">';
        html += '<span class="mbrtree-open-link" onclick="window.open(\'' + updateUrl + '\', \'_blank\')" title="Open member details in new tab">🔗</span>';
        html += '</td>';
        
        html += '</tr>';
        
        // Render account rows if accounts exist
        if (node.accounts && Array.isArray(node.accounts)) {
            node.accounts.forEach(account => {
                const balance = parseFloat(account.balance) || 0;
                const deposit = parseFloat(account.deposit) || 0;
                const withdrawal = parseFloat(account.withdrawal) || 0;
                const nmi = parseFloat(account.nmi) || 0;
                const accountUrl = '<?= pathbreadcrumb(0) ?>/account/active_real_account/document/' + encodeURIComponent(account.acc_id_hash);
                
                html += '<tr class="mbrtree-account-row" data-parent="' + node.id + '" data-level="' + (level + 1) + '">';
                html += '<td class="level-' + (level + 1) + '">';
                html += '<div class="mbrtree-name-cell">';
                html += '<span class="mbrtree-toggle no-children"></span>';
                html += '<span class="mbrtree-icon">💳</span>';
                html += '<span class="mbrtree-account-login">' + mbrTreeEscapeHtml(account.login) + '</span>';
                html += '</div>';
                html += '</td>';
                html += '<td>-</td>';
                html += '<td>-</td>';
                html += '<td class="text-end"><span class="mbrtree-account-balance">$' + mbrTreeFormatNumber(balance) + '</span></td>';
                html += '<td class="text-end"><span class="mbrtree-account-deposit">$' + mbrTreeFormatNumber(deposit) + '</span></td>';
                html += '<td class="text-end"><span class="mbrtree-account-withdrawal">$' + mbrTreeFormatNumber(withdrawal) + '</span></td>';
                html += '<td class="text-end"><span class="mbrtree-account-nmi">$' + mbrTreeFormatNumber(nmi) + '</span></td>';
                html += '<td class="text-center">-</td>';
                html += '<td class="text-center">';
                html += '<span class="mbrtree-open-link" onclick="window.open(\'' + accountUrl + '\', \'_blank\')" title="Open account document in new tab">📄</span>';
                html += '</td>';
                html += '</tr>';
            });
        }
        
        // Render children
        if (hasChildren) {
            node.children.forEach(child => {
                html += mbrTreeRenderNode(child, level + 1, node.id);
            });
        }
        
        return html;
    }
    
    // Escape HTML
    function mbrTreeEscapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Format number with thousand separator
    function mbrTreeFormatNumber(num) {
        return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
    
    // Toggle row expand/collapse
    function mbrTreeToggleRow(element) {
        const row = element.closest('tr');
        const rowId = row.getAttribute('data-id');
        const level = parseInt(row.getAttribute('data-level'));
        
        // Find all child rows
        let nextRow = row.nextElementSibling;
        let isCollapsed = element.textContent.trim() === '▶';
        
        // Update toggle icon
        element.textContent = isCollapsed ? '▼' : '▶';
        
        // Show/hide direct children
        while (nextRow && nextRow.tagName === 'TR') {
            const nextLevel = parseInt(nextRow.getAttribute('data-level'));
            const nextParent = nextRow.getAttribute('data-parent');
            
            // If it's a direct child
            if (nextParent === rowId) {
                if (isCollapsed) {
                    nextRow.style.display = '';
                    // If this child was expanded, show its children too
                    const childToggle = nextRow.querySelector('.mbrtree-toggle');
                    if (childToggle && childToggle.textContent.trim() === '▼') {
                        mbrTreeShowChildRows(nextRow);
                    }
                } else {
                    nextRow.style.display = 'none';
                    // Also hide all descendants
                    mbrTreeHideChildRows(nextRow);
                }
            }
            
            // Stop when we reach a sibling or parent level
            if (nextLevel <= level && nextParent !== rowId) {
                break;
            }
            
            nextRow = nextRow.nextElementSibling;
        }
    }
    
    // Helper function to show child rows
    function mbrTreeShowChildRows(parentRow) {
        const parentId = parentRow.getAttribute('data-id');
        const parentLevel = parseInt(parentRow.getAttribute('data-level'));
        let nextRow = parentRow.nextElementSibling;
        
        while (nextRow && nextRow.tagName === 'TR') {
            const nextLevel = parseInt(nextRow.getAttribute('data-level'));
            const nextParent = nextRow.getAttribute('data-parent');
            
            if (nextLevel <= parentLevel) break;
            
            if (nextParent === parentId) {
                nextRow.style.display = '';
                // Recursively show children if expanded
                const childToggle = nextRow.querySelector('.mbrtree-toggle');
                if (childToggle && childToggle.textContent.trim() === '▼') {
                    mbrTreeShowChildRows(nextRow);
                }
            }
            
            nextRow = nextRow.nextElementSibling;
        }
    }
    
    // Helper function to hide child rows
    function mbrTreeHideChildRows(parentRow) {
        const parentId = parentRow.getAttribute('data-id');
        const parentLevel = parseInt(parentRow.getAttribute('data-level'));
        let nextRow = parentRow.nextElementSibling;
        
        while (nextRow && nextRow.tagName === 'TR') {
            const nextLevel = parseInt(nextRow.getAttribute('data-level'));
            
            if (nextLevel <= parentLevel) break;
            
            nextRow.style.display = 'none';
            nextRow = nextRow.nextElementSibling;
        }
    }
    
    // Expand all
    function mbrTreeExpandAll() {
        // Show all rows including account rows
        document.querySelectorAll('.mbrtree-row, .mbrtree-account-row').forEach(row => {
            row.style.display = '';
        });
        // Update all toggle icons to expanded
        document.querySelectorAll('.mbrtree-toggle:not(.no-children)').forEach(toggle => {
            toggle.textContent = '▼';
        });
    }
    
    // Collapse all
    function mbrTreeCollapseAll() {
        // Hide all non-root rows including account rows
        document.querySelectorAll('.mbrtree-row, .mbrtree-account-row').forEach(row => {
            if (row.getAttribute('data-level') !== '0') {
                row.style.display = 'none';
            }
        });
        // Update all toggle icons to collapsed
        document.querySelectorAll('.mbrtree-toggle:not(.no-children)').forEach(toggle => {
            toggle.textContent = '▶';
        });
    }
    
    // Toggle filter between full tree and filtered view
    function mbrTreeToggleFilter() {
        mbrTreeShowFull = !mbrTreeShowFull;
        const btn = document.getElementById('mbrTreeFilterBtn');
        
        if (mbrTreeShowFull) {
            btn.innerHTML = '<i class="fe fe-filter"></i> Hide Leaf Nodes';
            btn.classList.remove('btn-info');
            btn.classList.add('btn-warning');
        } else {
            btn.innerHTML = '<i class="fe fe-filter"></i> Show Full Tree';
            btn.classList.remove('btn-warning');
            btn.classList.add('btn-info');
        }
        
        // Rebuild and render tree
        mbrTreeRenderTree();
    }
    
    // Render tree function
    function mbrTreeRenderTree() {
        const container = document.getElementById('mbrtree-container');
        
        if (!container) {
            console.error('Container #mbrtree-container not found!');
            return;
        }
        
        if (mbrTreeData && mbrTreeData.length > 0) {
            const treeData = mbrTreeBuildStructure(mbrTreeData, mbrTreeShowFull);
            console.log('Tree structure built:', treeData.length, 'root nodes');
            
            if (treeData.length === 0) {
                container.innerHTML = '<div class="mbrtree-empty-state" style="display:block;">No tree data to display (all nodes filtered out)</div>';
                return;
            }
            
            let html = '<table class="mbrtree-table">';
            html += '<thead>';
            html += '<tr>';
            html += '<th>Member Name</th>';
            html += '<th>Code</th>';
            html += '<th>Email</th>';
            html += '<th class="text-center">Balance</th>';
            html += '<th class="text-center">Deposit</th>';
            html += '<th class="text-center">Withdrawal</th>';
            html += '<th class="text-center">NMI</th>';
            html += '<th class="text-center">Children</th>';
            html += '<th class="text-center">Actions</th>';
            html += '</tr>';
            html += '</thead>';
            html += '<tbody>';
            treeData.forEach(node => {
                html += mbrTreeRenderNode(node, 0, '');
            });
            html += '</tbody>';
            html += '</table>';
            
            container.innerHTML = html;
            console.log('HTML inserted, container height:', container.offsetHeight);
            
            // Mark last children for proper tree line styling
            mbrTreeMarkLastChildren();
        } else {
            container.innerHTML = '<div class="mbrtree-empty-state" style="display:block;">No data found</div>';
        }
    }
    
    // Mark last child of each parent for tree line styling
    function mbrTreeMarkLastChildren() {
        const allRows = document.querySelectorAll('.mbrtree-row, .mbrtree-account-row');
        
        // Group rows by parent
        const parentGroups = {};
        allRows.forEach(row => {
            const parentId = row.getAttribute('data-parent');
            if (!parentGroups[parentId]) {
                parentGroups[parentId] = [];
            }
            parentGroups[parentId].push(row);
        });
        
        // Mark last child in each group
        Object.keys(parentGroups).forEach(parentId => {
            const children = parentGroups[parentId];
            if (children.length > 0) {
                const lastChild = children[children.length - 1];
                lastChild.classList.add('last-child');
            }
        });
    }
    
    // Search functionality
    let mbrTreeSearchTimeout;
    
    function showAllDescendants(parentRow) {
        // Show all descendants recursively
        const parentId = parentRow.getAttribute('data-id');
        const parentLevel = parseInt(parentRow.getAttribute('data-level'));
        let nextRow = parentRow.nextElementSibling;
        
        // Get toggle button and update icon
        const toggle = parentRow.querySelector('.mbrtree-toggle');
        if (toggle && !toggle.classList.contains('no-children')) {
            toggle.textContent = '▼';
        }
        
        while (nextRow && nextRow.tagName === 'TR') {
            const nextLevel = parseInt(nextRow.getAttribute('data-level'));
            const nextParent = nextRow.getAttribute('data-parent');
            
            // Stop when we reach a sibling or parent level
            if (nextLevel <= parentLevel) break;
            
            // Show all descendants
            nextRow.style.display = '';
            
            // Expand all descendants
            const childToggle = nextRow.querySelector('.mbrtree-toggle');
            if (childToggle && !childToggle.classList.contains('no-children')) {
                childToggle.textContent = '▼';
            }
            
            nextRow = nextRow.nextElementSibling;
        }
    }
    
    function mbrTreeSearch() {
        clearTimeout(mbrTreeSearchTimeout);
        
        mbrTreeSearchTimeout = setTimeout(function() {
            const searchTerm = document.getElementById('mbrTreeSearch').value.toLowerCase().trim();
            const allRows = document.querySelectorAll('.mbrtree-row, .mbrtree-account-row');
            let matchCount = 0;
            
            if (searchTerm === '') {
                // Clear search - show all rows
                allRows.forEach(row => {
                    row.style.display = '';
                    row.classList.remove('mbrtree-highlight');
                });
                // Reset all toggles to collapsed except level 0
                document.querySelectorAll('.mbrtree-toggle:not(.no-children)').forEach(toggle => {
                    const row = toggle.closest('tr');
                    if (row.getAttribute('data-level') !== '0') {
                        toggle.textContent = '▶';
                    }
                });
                document.getElementById('mbrTreeMatchNumber').textContent = 'All';
            } else {
                // First pass: hide all rows
                allRows.forEach(row => {
                    row.style.display = 'none';
                    row.classList.remove('mbrtree-highlight');
                });
                
                // Second pass: show matching rows and their hierarchy
                allRows.forEach(row => {
                    const content = row.textContent.toLowerCase();
                    
                    if (content.includes(searchTerm)) {
                        // Match found
                        matchCount++;
                        row.style.display = '';
                        row.classList.add('mbrtree-highlight');
                        
                        // If this is an account row, show its parent member
                        if (row.classList.contains('mbrtree-account-row')) {
                            const parentId = row.getAttribute('data-parent');
                            const parentRow = document.getElementById('row-' + parentId);
                            if (parentRow) {
                                parentRow.style.display = '';
                            }
                        }
                        
                        // Show all parent rows (ancestors)
                        let parentId = row.getAttribute('data-parent');
                        while (parentId) {
                            const parentRow = document.getElementById('row-' + parentId);
                            if (parentRow) {
                                parentRow.style.display = '';
                                
                                // Expand parent
                                const toggle = parentRow.querySelector('.mbrtree-toggle');
                                if (toggle && !toggle.classList.contains('no-children')) {
                                    toggle.textContent = '▼';
                                }
                                
                                parentId = parentRow.getAttribute('data-parent');
                            } else {
                                break;
                            }
                        }
                        
                        // Show all descendants (children, grandchildren, etc.)
                        if (row.classList.contains('mbrtree-row')) {
                            showAllDescendants(row);
                        }
                    }
                });
                
                document.getElementById('mbrTreeMatchNumber').textContent = matchCount;
            }
        }, 300); // 300ms debounce
    }
    
    function mbrTreeClearSearch() {
        document.getElementById('mbrTreeSearch').value = '';
        mbrTreeSearch();
    }
    
    // Initialize tree
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Member Tree Data:', mbrTreeData);
        console.log('Total members:', mbrTreeData ? mbrTreeData.length : 0);
        
        if (!mbrTreeData || mbrTreeData.length === 0) {
            console.error('No member data available');
            document.getElementById('mbrtree-container').innerHTML = '<div class="mbrtree-empty-state">No member data available</div>';
            return;
        }
        
        mbrTreeRenderTree();
        document.getElementById('mbrTreeMatchNumber').textContent = 'All';
        console.log('Tree rendered successfully');
    });
</script>