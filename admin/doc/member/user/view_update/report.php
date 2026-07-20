<?php 

// Query untuk mengambil semua member data
$queryMembers = "
    SELECT
        MBR_ID,
        MBR_CODE,
        MBR_NAME,
        MBR_EMAIL,
        MBR_IDSPN,
        MBR_TYPE
    FROM tb_member
    WHERE MBR_EMAIL NOT LIKE '%_deleted'
    ORDER BY MBR_ID
";

$resultMembers = $db->query($queryMembers);
$members = [];
while ($row = $resultMembers->fetch_assoc()) {
    $members[$row['MBR_ID']] = $row;
    $members[$row['MBR_ID']]['accounts'] = [];
}

// Query untuk mengambil account data dengan balance dari MT5
$queryAccounts = "
    SELECT
        r.ACC_MBR,
        r.ACC_LOGIN,
        tb_racctype.RTYPE_NAME,
        tb_racctype.RTYPE_RATE,
        tb_racctype.RTYPE_KOMISI,
        IFNULL(mt5.equity_prevday, 0) as EQUITY_PREVDAY,
        IFNULL(mt5.balance, 0) as BALANCE,
        IFNULL(mt5.floating, 0) as FLOATING,
        IFNULL(mt5.equity, 0) as EQUITY,
        IFNULL(mt5.margin_free, 0) as MARGIN_FREE,
        SUM(IF(mt5d.profit > 0 AND mt5d.action = 2 AND mt5d.`time` >= UNIX_TIMESTAMP(DATE_FORMAT(CURDATE(), '%Y-%m-01')) AND mt5d.`time` <  UNIX_TIMESTAMP(DATE_FORMAT(CURDATE() + INTERVAL 1 MONTH, '%Y-%m-01')), mt5d.profit, 0)) as TOTAL_DEPOSIT,
        SUM(IF(mt5d.profit > 0 AND mt5d.action = 2 AND DATE(FROM_UNIXTIME(mt5d.`time`)) = DATE(NOW()), mt5d.profit, 0)) as TOTAL_DEPOSIT_TODAY,
        SUM(IF(mt5d.profit < 0 AND mt5d.action = 2 AND mt5d.`time` >= UNIX_TIMESTAMP(DATE_FORMAT(CURDATE(), '%Y-%m-01')) AND mt5d.`time` <  UNIX_TIMESTAMP(DATE_FORMAT(CURDATE() + INTERVAL 1 MONTH, '%Y-%m-01')), mt5d.profit, 0)) as TOTAL_WITHDRAWAL,
        SUM(IF(mt5d.profit < 0 AND mt5d.action = 2 AND DATE(FROM_UNIXTIME(mt5d.`time`)) = DATE(NOW()), mt5d.profit, 0)) as TOTAL_WITHDRAWAL_TODAY,
        SUBSTRING_INDEX(
            GROUP_CONCAT(
                IF(mt5d.`action` = 2 AND mt5d.profit > 0, CONCAT(mt5d.time, ':', mt5d.profit), NULL) 
                ORDER BY mt5d.time DESC 
                SEPARATOR ','
            ), 
            ':', 
            -1
        ) as DEPOSIT_NEWACCOUNT,
        DATE(FROM_UNIXTIME(SUBSTRING_INDEX(
            GROUP_CONCAT(
                IF(mt5d.`action` = 2 AND mt5d.profit > 0, CONCAT(mt5d.time, ':', mt5d.`time`), NULL) 
                ORDER BY mt5d.time DESC 
                SEPARATOR ','
            ), 
            ':', 
            -1
        ))) as DEPOSIT_NEWACCOUNT_DATE,
        SUM(IF(mt5d.action IN (0,1), mt5d.volume / 10000, 0)) AS TURN_OVER,
        SUM(IF(tb_symbolcat.SYMCAT_NAME = 'KOMODITI' AND mt5d.action IN (0,1) AND mt5d.entry = 1, mt5d.volume / 10000, 0)) AS TOTAL_KOMODITI,
        SUM(IF(tb_symbolcat.SYMCAT_NAME = 'FOREX' AND mt5d.action IN (0,1) AND mt5d.entry = 1, mt5d.volume / 10000, 0)) AS TOTAL_FOREX,
        SUM(IF(tb_symbolcat.SYMCAT_NAME = 'INDEX' AND mt5d.action IN (0,1) AND mt5d.entry = 1, mt5d.volume / 10000, 0)) AS TOTAL_INDEX,
        SUM(IF(mt5d.entry = 0 AND mt5d.action IN (0,1), mt5d.commission, 0)) AS TOTAL_COMMISSION,
        SUM(IF(mt5d.entry = 1 AND mt5d.action IN (0,1), mt5d.`storage`, 0)) AS TOTAL_SWAP
    FROM tb_racc r
    LEFT JOIN tb_racctype ON tb_racctype.ID_RTYPE = r.ACC_TYPE
    LEFT JOIN meta_rrfxreal.mt5_users mt5 ON r.ACC_LOGIN = mt5.login
    LEFT JOIN meta_rrfxreal.mt5_deals mt5d ON r.ACC_LOGIN = mt5d.login
    LEFT JOIN tb_symbol ON(tb_symbol.SYM_NAME = mt5d.symbol)
    LEFT JOIN tb_symbolcat ON(tb_symbolcat.ID_SYMCAT = tb_symbol.ID_SYMCAT)
    WHERE r.ACC_DERE = 1
    GROUP BY r.ACC_LOGIN
    ORDER BY r.ACC_MBR, r.ACC_LOGIN
";

$resultAccounts = $db->query($queryAccounts);
while ($row = $resultAccounts->fetch_assoc()) {
    if (isset($members[$row['ACC_MBR']])) {
        $members[$row['ACC_MBR']]['accounts'][] = $row;
    }
}

// Fungsi untuk render tree rows secara rekursif
function renderTreeRows($data, $parentId, $level = 0) {
    global $db;
    $html = '';
    foreach ($data as $item) {
        if ($item['MBR_IDSPN'] == $parentId) {
            $hasChildren = false;
            foreach ($data as $child) {
                if ($child['MBR_IDSPN'] == $item['MBR_ID']) {
                    $hasChildren = true;
                    break;
                }
            }
            
            $hasAccounts = !empty($item['accounts']);
            $hasChildrenOrAccounts = $hasChildren || $hasAccounts;
            
            $paddingLeft = ($level * 30) + 10;
            $toggleIcon = $hasChildrenOrAccounts ? '<i class="fas fa-plus-square toggle-icon" style="cursor:pointer; margin-right:5px;"></i>' : '<i class="fas fa-minus" style="margin-right:5px; opacity:0.3;"></i>';
            
            // Format parent ID untuk konsistensi - tambahkan prefix "member-" jika parent adalah angka
            $formattedParent = is_numeric($parentId) ? 'member-' . $parentId : $parentId;
                        
            $html .= '<tr class="tree-row member-row" data-id="member-' . $item['MBR_ID'] . '" data-parent="' . $formattedParent . '" data-level="' . $level . '" data-type="member" data-member-id="' . $item['MBR_ID'] . '">';
            $html .= '<td class="sticky-col first-col" style="padding-left:' . $paddingLeft . 'px;white-space: nowrap;">' . $toggleIcon . '<i class="fas fa-user"></i> ' . $item['MBR_NAME'] . '</td>';
            $html .= '<td class="sticky-col second-col"><a href="/member/user/update/' . $item['MBR_CODE'] . '" target="_blank">' . ($item['MBR_EMAIL'] ?? '-') . '</a></td>';
            $html .= '<td>' . ($item['MBR_TYPE'] ?? '-') . '</td>';
            $html .= '<td></td>';
            $html .= '<td></td>';
            $html .= '<td></td>';
            $html .= '<td class="text-end member-total-depositnewaccount" data-member="' . $item['MBR_ID'] . '"><span class="text-muted">-</span></td>';
            $html .= '<td class="text-end member-total-deposittoday" data-member="' . $item['MBR_ID'] . '"><span class="text-muted">-</span></td>';
            $html .= '<td class="text-end member-total-deposit" data-member="' . $item['MBR_ID'] . '"><span class="text-muted">-</span></td>';
            $html .= '<td class="text-end member-total-withdrawaltoday" data-member="' . $item['MBR_ID'] . '"><span class="text-muted">-</span></td>';
            $html .= '<td class="text-end member-total-withdrawal" data-member="' . $item['MBR_ID'] . '"><span class="text-muted">-</span></td>';
            $html .= '<td class="text-end member-total-equityprev" data-member="' . $item['MBR_ID'] . '"><span class="text-muted">-</span></td>';
            $html .= '<td class="text-end member-total-balance" data-member="' . $item['MBR_ID'] . '"><span class="text-muted">-</span></td>';
            $html .= '<td class="text-end member-total-floating" data-member="' . $item['MBR_ID'] . '"><span class="text-muted">-</span></td>';
            $html .= '<td class="text-end member-total-equity" data-member="' . $item['MBR_ID'] . '"><span class="text-muted">-</span></td>';
            $html .= '<td class="text-end member-total-nmi" data-member="' . $item['MBR_ID'] . '"><span class="text-muted">-</span></td>';
            $html .= '<td class="text-end member-total-turnover" data-member="' . $item['MBR_ID'] . '"><span class="text-muted">-</span></td>';
            $html .= '<td class="text-end member-total-settle" data-member="' . $item['MBR_ID'] . '"><span class="text-muted">-</span></td>';
            $html .= '<td class="text-end member-total-komoditi" data-member="' . $item['MBR_ID'] . '"><span class="text-muted">-</span></td>';
            $html .= '<td class="text-end member-total-forex" data-member="' . $item['MBR_ID'] . '"><span class="text-muted">-</span></td>';
            $html .= '<td class="text-end member-total-index" data-member="' . $item['MBR_ID'] . '"><span class="text-muted">-</span></td>';
            $html .= '<td class="text-end member-total-commission" data-member="' . $item['MBR_ID'] . '"><span class="text-muted">-</span></td>';
            $html .= '<td class="text-end member-total-swap" data-member="' . $item['MBR_ID'] . '"><span class="text-muted">-</span></td>';
            $html .= '<td class="text-end member-total-grosspl" data-member="' . $item['MBR_ID'] . '"><span class="text-muted">-</span></td>';
            $html .= '</tr>';
            
            // Render accounts for this member
            if ($hasAccounts) {
                $accountLevel = $level + 1;
                $accountPaddingLeft = ($accountLevel * 30) + 10;
                foreach ($item['accounts'] as $account) {
                    $html .= '<tr class="tree-row account-row" 
                        data-id="account-' . $account['ACC_LOGIN'] . '" 
                        data-parent="member-' . $item['MBR_ID'] . '" 
                        data-level="' . $accountLevel . '" 
                        data-type="account" 
                        data-owner-member="' . $item['MBR_ID'] . '" 
                        data-depositnewaccount="' . $account['DEPOSIT_NEWACCOUNT'] . '"
                        data-deposittoday="' . $account['TOTAL_DEPOSIT_TODAY'] . '"
                        data-deposit="' . $account['TOTAL_DEPOSIT'] . '" 
                        data-withdrawaltoday="' . abs($account['TOTAL_WITHDRAWALTODAY']) . '"
                        data-withdrawal="' . abs($account['TOTAL_WITHDRAWAL']) . '"
                        data-equityprev="' . $account['EQUITY_PREVDAY'] . '" 
                        data-balance="' . $account['BALANCE'] . '"
                        data-floating="' . $account['FLOATING'] . '"
                        data-equity="' . $account['EQUITY'] . '" 
                        data-nmi="' . ($account['TOTAL_DEPOSIT'] - abs($account['TOTAL_WITHDRAWAL'])) . '"
                        data-turnover="' . $account['TURN_OVER'] . '"
                        data-settle="' . $account['TOTAL_KOMODITI']+$account['TOTAL_FOREX']+$account['TOTAL_INDEX'] . '"
                        data-komoditi="' . $account['TOTAL_KOMODITI'] . '"
                        data-forex="' . $account['TOTAL_FOREX'] . '"
                        data-index="' . $account['TOTAL_INDEX'] . '"
                        data-commission="' . $account['TOTAL_COMMISSION'] . '"
                        data-swap="' . $account['TOTAL_SWAP'] . '"
                        data-grosspl="' . ($account['EQUITY_PREVDAY'] - $account['EQUITY'] - ($account['TOTAL_DEPOSIT'] - abs($account['TOTAL_WITHDRAWAL']))) . '"
                        >';
                    $html .= '<td class="sticky-col first-col" style="padding-left:' . $accountPaddingLeft . 'px;"><i class="fas fa-wallet" style="margin-right:5px;"></i> Account: ' . $account['ACC_LOGIN'] . '</td>';
                    $html .= '<td class="sticky-col second-col">' . ($account['RTYPE_NAME'] ?? '-') . '</td>';
                    $html .= '<td></td>';
                    $html .= '<td class="text-center" style="white-space: nowrap;">' . $account['DEPOSIT_NEWACCOUNT_DATE'] . '</td>';
                    $html .= '<td class="text-center" style="white-space: nowrap;">' . $account['RTYPE_RATE'] . '</td>';
                    $html .= '<td class="text-center" style="white-space: nowrap;">' . $account['RTYPE_KOMISI'] . '</td>';
                    $html .= '<td class="text-end">$' . number_format($account['DEPOSIT_NEWACCOUNT'], 2) . '</td>';
                    $html .= '<td class="text-end">$' . number_format($account['TOTAL_DEPOSIT_TODAY'], 2) . '</td>';
                    $html .= '<td class="text-end">$' . number_format($account['TOTAL_DEPOSIT'], 2) . '</td>';
                    $html .= '<td class="text-end">$' . number_format(abs($account['TOTAL_WITHDRAWALTODAY']), 2) . '</td>';
                    $html .= '<td class="text-end">$' . number_format(abs($account['TOTAL_WITHDRAWAL']), 2) . '</td>';
                    $html .= '<td class="text-end">$' . number_format($account['EQUITY_PREVDAY'], 2) . '</td>';
                    $html .= '<td class="text-end">$' . number_format($account['BALANCE'], 2) . '</td>';
                    $html .= '<td class="text-end">$' . number_format($account['FLOATING'], 2) . '</td>';
                    $html .= '<td class="text-end">$' . number_format($account['EQUITY'], 2) . '</td>';
                    $html .= '<td class="text-end">$' . number_format($account['TOTAL_DEPOSIT']-(abs($account['TOTAL_WITHDRAWAL'])) ?? '-' , 2) . '</td>';
                    $html .= '<td class="text-end">' . number_format($account['TURN_OVER'], 2) . '</td>';
                    $html .= '<td class="text-end">' . number_format($account['TOTAL_KOMODITI']+$account['TOTAL_FOREX']+$account['TOTAL_INDEX'], 2) . '</td>';
                    $html .= '<td class="text-end">' . number_format($account['TOTAL_KOMODITI'], 2) . '</td>';
                    $html .= '<td class="text-end">' . number_format($account['TOTAL_FOREX'], 2) . '</td>';
                    $html .= '<td class="text-end">' . number_format($account['TOTAL_INDEX'], 2) . '</td>';
                    $html .= '<td class="text-end">$' . number_format($account['TOTAL_COMMISSION'], 2) . '</td>';
                    $html .= '<td class="text-end">$' . number_format($account['TOTAL_SWAP'], 2) . '</td>';
                    $html .= '<td class="text-end">$' . number_format($account['EQUITY_PREVDAY'] - $account['EQUITY'] - ($account['TOTAL_DEPOSIT'] - abs($account['TOTAL_WITHDRAWAL'])), 2) . '</td>';
                    $html .= '</tr>';
                }
            }
            
            if ($hasChildren) {
                $html .= renderTreeRows($data, $item['MBR_ID'], $level + 1);
            }
        }
    }
    return $html;
}

$flatData = array_values($members);
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Member Tree Structure</h5>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-info" id="expandAll">
                        <i class="fas fa-expand-alt"></i> Expand All
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary" id="collapseAll">
                        <i class="fas fa-compress-alt"></i> Collapse All
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive table-wrapper">
                    <table class="table table-bordered table-hover table-sm" id="member-tree-table">
                        <thead class="sticky-header">
                            <tr>
                                <th width="250" class="sticky-col first-col">Name / Login</th>
                                <th width="200" class="sticky-col second-col">Email / Type</th>
                                <th width="80">Type</th>
                                <th width="100" style="white-space: nowrap;">TANGGAL FTD</th>
                                <th width="100" style="white-space: nowrap;">RATE (IDR)</th>
                                <th width="100" style="white-space: nowrap;">CHARGE ($)</th>
                                <th width="100">FTD</th>
                                <th width="100" style="white-space: nowrap;">DEPOSIT TODAY</th>
                                <th width="100">DEPOSIT</th>
                                <th width="100" style="white-space: nowrap;">WITHDRAWAL TODAY</th>
                                <th width="100">WITHDRAWAL</th>
                                <th width="100" style="white-space: nowrap;">PREV EQUITY</th>
                                <th width="100">BALANCE</th>
                                <th width="100">FLOATING</th>
                                <th width="100">EQUITY</th>
                                <th width="100">NMI</th>
                                <th width="100" style="white-space: nowrap;">TURN OVER</th>
                                <th width="100" style="white-space: nowrap;">SETTLE</th>
                                <th width="100">Komoditi</th>
                                <th width="100">FOREX</th>
                                <th width="100">INDEX</th>
                                <th width="100">COMM</th>
                                <th width="100">SWAP</th>
                                <th width="100" style="white-space: nowrap;">GROSS P/L</th>
                                <!-- <th width="100" style="white-space: nowrap;">REFUND SWAP<br><?= date("F", strtotime("-1 month")) ?></th> -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php echo renderTreeRows($flatData, $userData['MBR_ID']); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .table-wrapper {
        position: relative;
        max-height: 80vh;
        overflow: auto;
        width: 100%;
    }
    
    #member-tree-table {
        width: 100%;
        position: relative;
        border-collapse: separate;
        border-spacing: 0;
    }
    
    /* Sticky header */
    #member-tree-table thead.sticky-header {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #212529;
    }
    
    #member-tree-table thead.sticky-header th {
        background-color: #212529;
        color: white;
        position: sticky;
        top: 0;
    }
    
    /* Sticky columns - untuk TD dan TH */
    .sticky-col {
        position: -webkit-sticky;
        position: sticky;
        background-color: white;
        z-index: 2;
    }
    
    .sticky-col.first-col {
        left: 0 !important;
        min-width: 250px;
    }
    
    .sticky-col.second-col {
        left: 250px !important;
        min-width: 200px;
    }
    
    /* Sticky header + sticky column intersection - TH yang sticky horizontal dan vertical */
    thead.sticky-header th.sticky-col {
        z-index: 20 !important;
        background-color: #212529 !important;
        position: sticky;
        top: 0;
    }
    
    thead.sticky-header th.sticky-col.first-col {
        left: 0 !important;
    }
    
    thead.sticky-header th.sticky-col.second-col {
        left: 250px !important;
    }
    
    /* Account row background for sticky columns */
    .account-row .sticky-col {
        background-color: #f8f9fa !important;
        color: #000 !important;
    }
    
    .member-row .sticky-col {
        background-color: #ffffff !important;
        color: #000 !important;
    }
    
    /* Hover effect for rows with sticky columns */
    .tree-row:hover .sticky-col {
        background-color: #f5f5f5 !important;
    }
    
    .account-row:hover .sticky-col {
        background-color: #e9ecef !important;
    }
    
    .tree-row {
        transition: background-color 0.2s;
    }
    .tree-row:hover {
        background-color: #f5f5f5;
    }
    .tree-row.hidden {
        display: none;
    }
    .toggle-icon {
        color: #007bff;
        font-size: 14px;
    }
    .toggle-icon:hover {
        color: #0056b3;
    }
    .badge {
        padding: 4px 8px;
        font-size: 11px;
        font-weight: bold;
    }
    .badge-success {
        background-color: #28a745;
        color: white;
    }
    .badge-danger {
        background-color: #dc3545;
        color: white;
    }
    .member-row {
        background-color: #ffffff;
    }
    .member-row td {
        background-color: #ffffff;
        color: #000;
    }
    .account-row {
        background-color: #f8f9fa;
        font-size: 0.9em;
    }
    .account-row td {
        background-color: #f8f9fa;
        color: #000;
    }
    .account-row:hover {
        background-color: #e9ecef;
    }
    .account-row:hover td {
        background-color: #e9ecef;
    }
    .text-end {
        text-align: right;
    }
    .member-row .member-total-depositnewaccount,
    .member-row .member-total-deposittoday,
    .member-row .member-total-deposit,
    .member-row .member-total-withdrawaltoday,
    .member-row .member-total-withdrawal,
    .member-row .member-total-equityprev,
    .member-row .member-total-balance,
    .member-row .member-total-floating,
    .member-row .member-total-equity,
    .member-row .member-total-nmi,
    .member-row .member-total-turnover,
    .member-row .member-total-settle,
    .member-row .member-total-komoditi,
    .member-row .member-total-forex,
    .member-row .member-total-index,
    .member-row .member-total-commission,
    .member-row .member-total-swap,
    .member-row .member-total-grosspl {
        font-weight: bold;
        color: #0d6efd;
        background-color: #e7f3ff;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Hide all children on load
        const allRows = document.querySelectorAll('.tree-row');
        allRows.forEach(row => {
            const level = parseInt(row.getAttribute('data-level'));
            if (level > 0) {
                row.classList.add('hidden');
            }
        });

        // Toggle function
        function toggleChildren(parentId, show) {
            const children = document.querySelectorAll('.tree-row[data-parent="' + parentId + '"]');
            children.forEach(child => {
                if (show) {
                    child.classList.remove('hidden');
                } else {
                    child.classList.add('hidden');
                    // Also hide all descendants (only for member rows)
                    if (child.getAttribute('data-type') === 'member') {
                        const childId = child.getAttribute('data-id');
                        toggleChildren(childId, false);
                        // Reset icon
                        const icon = child.querySelector('.toggle-icon');
                        if (icon) {
                            icon.classList.remove('fa-minus-square');
                            icon.classList.add('fa-plus-square');
                        }
                    }
                }
            });
        }

        // Click event for toggle icons
        document.querySelectorAll('.toggle-icon').forEach(icon => {
            icon.addEventListener('click', function(e) {
                e.stopPropagation();
                const row = this.closest('tr');
                const id = row.getAttribute('data-id');
                
                if (this.classList.contains('fa-plus-square')) {
                    this.classList.remove('fa-plus-square');
                    this.classList.add('fa-minus-square');
                    toggleChildren(id, true);
                } else {
                    this.classList.remove('fa-minus-square');
                    this.classList.add('fa-plus-square');
                    toggleChildren(id, false);
                }
            });
        });

        // Expand all button
        document.getElementById('expandAll').addEventListener('click', function() {
            document.querySelectorAll('.tree-row').forEach(row => {
                row.classList.remove('hidden');
            });
            document.querySelectorAll('.toggle-icon').forEach(icon => {
                icon.classList.remove('fa-plus-square');
                icon.classList.add('fa-minus-square');
            });
        });

        // Collapse all button
        document.getElementById('collapseAll').addEventListener('click', function() {
            const allRows = document.querySelectorAll('.tree-row');
            allRows.forEach(row => {
                const level = parseInt(row.getAttribute('data-level'));
                if (level > 0) {
                    row.classList.add('hidden');
                }
            });
            document.querySelectorAll('.toggle-icon').forEach(icon => {
                icon.classList.remove('fa-minus-square');
                icon.classList.add('fa-plus-square');
            });
        });

        // Function to calculate totals for each member
        function calculateMemberTotals() {
            // Get all member rows and sort by level (deepest first)
            const memberRows = Array.from(document.querySelectorAll('.member-row'));
            
            // Sort by level descending (calculate from bottom to top)
            memberRows.sort((a, b) => {
                return parseInt(b.getAttribute('data-level')) - parseInt(a.getAttribute('data-level'));
            });
            
            memberRows.forEach(memberRow => {
                const memberId = memberRow.getAttribute('data-member-id');
                
                // Initialize totals
                let totalDepositNewAccount = 0;
                let totalDepositToday = 0;
                let totalDeposit = 0;
                let totalWithdrawalToday = 0;
                let totalWithdrawal = 0;
                let totalEquityPrev = 0;
                let totalBalance = 0;
                let totalFloating = 0;
                let totalEquity = 0;
                let totalNMI = 0;
                let totalTurnOver = 0;
                let totalSettle = 0;
                let totalKomoditi = 0;
                let totalForex = 0;
                let totalIndex = 0;
                let totalCommission = 0;
                let totalSwap = 0;
                let totalGrossPL = 0;
                
                // Function to recursively get all descendant member IDs
                function getDescendantMembers(parentId) {
                    let descendants = [parentId];
                    const childMembers = document.querySelectorAll('.member-row[data-parent="member-' + parentId + '"]');
                    
                    childMembers.forEach(child => {
                        const childId = child.getAttribute('data-member-id');
                        // Recursively get all descendants
                        descendants = descendants.concat(getDescendantMembers(childId));
                    });
                    
                    return descendants;
                }
                
                // Get all descendant member IDs including current member
                const allMemberIds = getDescendantMembers(memberId);
                
                // Calculate totals from all accounts belonging to this member and ALL descendants
                // This ensures parent totals include everything from their downline tree
                allMemberIds.forEach(id => {
                    const accountRows = document.querySelectorAll('.account-row[data-owner-member="' + id + '"]');
                    
                    accountRows.forEach(accountRow => {
                        const depositNewAccount = parseFloat(accountRow.getAttribute('data-depositnewaccount')) || 0;
                        const depositToday = parseFloat(accountRow.getAttribute('data-deposittoday')) || 0;
                        const deposit = parseFloat(accountRow.getAttribute('data-deposit')) || 0;
                        const withdrawalToday = parseFloat(accountRow.getAttribute('data-withdrawaltoday')) || 0;
                        const withdrawal = parseFloat(accountRow.getAttribute('data-withdrawal')) || 0;
                        const equityPrev = parseFloat(accountRow.getAttribute('data-equityprev')) || 0;
                        const balance = parseFloat(accountRow.getAttribute('data-balance')) || 0;
                        const floating = parseFloat(accountRow.getAttribute('data-floating')) || 0;
                        const equity = parseFloat(accountRow.getAttribute('data-equity')) || 0;
                        const nmi = parseFloat(accountRow.getAttribute('data-nmi')) || 0;
                        const turnover = parseFloat(accountRow.getAttribute('data-turnover')) || 0;
                        const settle = parseFloat(accountRow.getAttribute('data-settle')) || 0;
                        const komoditi = parseFloat(accountRow.getAttribute('data-komoditi')) || 0;
                        const forex = parseFloat(accountRow.getAttribute('data-forex')) || 0;
                        const index = parseFloat(accountRow.getAttribute('data-index')) || 0;
                        const commission = parseFloat(accountRow.getAttribute('data-commission')) || 0;
                        const swap = parseFloat(accountRow.getAttribute('data-swap')) || 0;
                        const grossPL = parseFloat(accountRow.getAttribute('data-grosspl')) || 0;
                        
                        totalDepositNewAccount += depositNewAccount;
                        totalDepositToday += depositToday;
                        totalDeposit += deposit;
                        totalWithdrawalToday += withdrawalToday;
                        totalWithdrawal += withdrawal;
                        totalEquityPrev += equityPrev;
                        totalBalance += balance;
                        totalFloating += floating;
                        totalEquity += equity;
                        totalNMI += nmi;
                        totalTurnOver += turnover;
                        totalSettle += settle;
                        totalKomoditi += komoditi;
                        totalForex += forex;
                        totalIndex += index;
                        totalCommission += commission;
                        totalSwap += swap;
                        totalGrossPL += grossPL;
                    });
                });
                
                // Update the member row with totals
                const depositNewAccountCell = memberRow.querySelector('.member-total-depositnewaccount');
                const depositTodayCell = memberRow.querySelector('.member-total-deposittoday');
                const depositCell = memberRow.querySelector('.member-total-deposit');
                const withdrawalTodayCell = memberRow.querySelector('.member-total-withdrawaltoday');
                const withdrawalCell = memberRow.querySelector('.member-total-withdrawal');
                const equityPrevCell = memberRow.querySelector('.member-total-equityprev');
                const balanceCell = memberRow.querySelector('.member-total-balance');
                const floatingCell = memberRow.querySelector('.member-total-floating');
                const equityCell = memberRow.querySelector('.member-total-equity');
                const nmiCell = memberRow.querySelector('.member-total-nmi');
                const turnoverCell = memberRow.querySelector('.member-total-turnover');
                const settleCell = memberRow.querySelector('.member-total-settle');
                const komoditiCell = memberRow.querySelector('.member-total-komoditi');
                const forexCell = memberRow.querySelector('.member-total-forex');
                const indexCell = memberRow.querySelector('.member-total-index');
                const commissionCell = memberRow.querySelector('.member-total-commission');
                const swapCell = memberRow.querySelector('.member-total-swap');
                const grossPLCell = memberRow.querySelector('.member-total-grosspl');
                
                // Check if there's any data
                const hasData = totalDepositNewAccount !== 0 || 
                                totalDepositToday !== 0 || 
                                totalDeposit !== 0 || 
                                totalWithdrawalToday !== 0 || 
                                totalWithdrawal !== 0 || 
                                totalEquityPrev !== 0 || 
                                totalBalance !== 0 || 
                                totalFloating !== 0 || 
                                totalEquity !== 0 || 
                                totalNMI !== 0 || 
                                totalTurnOver !== 0 || 
                                totalSettle !== 0 || 
                                totalKomoditi !== 0 || 
                                totalForex !== 0 || 
                                totalIndex !== 0 || 
                                totalCommission !== 0 || 
                                totalSwap !== 0 || 
                                totalGrossPL !== 0;
                
                if (hasData) {
                    depositNewAccountCell.innerHTML = '$' + totalDepositNewAccount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    depositTodayCell.innerHTML = '$' + totalDepositToday.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    depositCell.innerHTML = '$' + totalDeposit.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    withdrawalTodayCell.innerHTML = '$' + totalWithdrawalToday.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    withdrawalCell.innerHTML = '$' + totalWithdrawal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    equityPrevCell.innerHTML = '$' + totalEquityPrev.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    balanceCell.innerHTML = '$' + totalBalance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    floatingCell.innerHTML = '$' + totalFloating.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    equityCell.innerHTML = '$' + totalEquity.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    nmiCell.innerHTML = '$' + totalNMI.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    turnoverCell.innerHTML = totalTurnOver.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    settleCell.innerHTML = totalSettle.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    komoditiCell.innerHTML = totalKomoditi.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    forexCell.innerHTML = totalForex.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    indexCell.innerHTML = totalIndex.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    commissionCell.innerHTML = '$' + totalCommission.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    swapCell.innerHTML = '$' + totalSwap.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    grossPLCell.innerHTML = '$' + totalGrossPL.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                } else {
                    depositNewAccountCell.innerHTML = '<span class="text-muted">$0.00</span>';
                    depositTodayCell.innerHTML = '<span class="text-muted">$0.00</span>';
                    depositCell.innerHTML = '<span class="text-muted">$0.00</span>';
                    withdrawalTodayCell.innerHTML = '<span class="text-muted">$0.00</span>';
                    withdrawalCell.innerHTML = '<span class="text-muted">$0.00</span>';
                    equityPrevCell.innerHTML = '<span class="text-muted">$0.00</span>';
                    balanceCell.innerHTML = '<span class="text-muted">$0.00</span>';
                    floatingCell.innerHTML = '<span class="text-muted">$0.00</span>';
                    equityCell.innerHTML = '<span class="text-muted">$0.00</span>';
                    nmiCell.innerHTML = '<span class="text-muted">$0.00</span>';
                    turnoverCell.innerHTML = '<span class="text-muted">0.00</span>';
                    settleCell.innerHTML = '<span class="text-muted">0.00</span>';
                    komoditiCell.innerHTML = '<span class="text-muted">0.00</span>';
                    forexCell.innerHTML = '<span class="text-muted">0.00</span>';
                    indexCell.innerHTML = '<span class="text-muted">0.00</span>';
                    commissionCell.innerHTML = '<span class="text-muted">$0.00</span>';
                    swapCell.innerHTML = '<span class="text-muted">$0.00</span>';
                    grossPLCell.innerHTML = '<span class="text-muted">$0.00</span>';
                }
            });
        }

        // Calculate totals on page load
        calculateMemberTotals();
    });
</script>
