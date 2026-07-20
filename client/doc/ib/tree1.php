<?php

use App\Models\Account;
use App\Models\Ib;
use App\Models\Refferal;
use App\Models\User;

$isIB = User::get_ib_data($user['MBR_ID'], [-1]); 
$upline = Ib::userUpline($user['MBR_IDSPN']);
if(!$upline) {
    die("<script>alert('Setup Failed'); location.href = '/ib/become'; ;</script>");
}    
?>

<?php if($isIB) : ?>
    <?php 
    $totalDownline = count(Ib::getNetworks($user['MBR_ID'], "downline"));
    $userRefferal = Refferal::createUserReferral($user['MBR_ID']);
    $accountRefferal = Refferal::createAccountReferralV2($user['MBR_ID']);
    ?>
    
    <style>
        .tree-table {
            width: 100%;
            border-collapse: collapse;
        }
        .tree-table th,
        .tree-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
            vertical-align: middle;
        }
        .tree-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .tree-table tbody tr:hover {
            background-color: #f5f5f5;
        }
        .tree-row {
            transition: background-color 0.2s ease;
        }
        .tree-toggle {
            display: inline-block;
            width: 20px;
            text-align: center;
            cursor: pointer;
            user-select: none;
            font-weight: bold;
            transition: transform 0.2s ease;
        }
        .tree-toggle.collapsed::before {
            content: '▶';
            color: #666;
        }
        .tree-toggle.expanded::before {
            content: '▼';
            color: #666;
        }
        .tree-toggle.no-children {
            visibility: hidden;
        }
        .tree-toggle:hover:not(.no-children) {
            color: #007bff;
        }
        .tree-icon {
            margin-right: 8px;
            color: #007bff;
        }
        .hidden-row {
            display: none;
        }
        .member-name {
            font-weight: 500;
        }
        .panel-header-right {
            display: flex;
            gap: 8px;
        }
        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 600;
            line-height: 1;
            border-radius: 12px;
            text-align: center;
            white-space: nowrap;
        }
        .badge-primary {
            background-color: #007bff;
            color: #fff;
        }
        .text-center {
            text-align: center !important;
        }
    </style>
    <div class="row">
        <div class="col-md-12">
            <div class="dashboard-breadcrumb">
                <h2>Member Tree View</h2>
                <div class="input-group-a dashboard-filter">
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-25">
            <div class="panel">
                <div class="panel-header">
                    <h5>Upline Profile</h5>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" style="table-layout: fixed; word-break: break-word;">
                            <tbody>
                                <tr>
                                    <td width="30%">Upline</td>
                                    <td width="70%"><?= $upline['MBR_NAME'] ?? "-" ?></td>
                                </tr>
                                <tr>
                                    <td width="30%">IB Status</td>
                                    <td width="70%"><?= Ib::$status[ $isIB['BECOME_STS'] ]['html']; ?></td>
                                </tr>
                                <tr>
                                    <td width="30%">Total Downline</td>
                                    <td width="70%"><?= $totalDownline ?></td>
                                </tr>

                                <?php if($salesData && $salesData->isCanShareRefferal()) : ?>
                                <?php   if($user['MBR_REFFERALALL'] == -1) : ?>
                                    <tr>
                                        <td width="30%">User Refferal</td>
                                        <td width="70%"><a href="javascript:void(0)" class="copytext"><?= $userRefferal ?></a></td>
                                    </tr>
                                <?php   endif; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <?php if($salesData && $salesData->isCanShareRefferal()) : ?>
            <div class="col-md-12 mb-3">
                <div class="panel">
                    <div class="panel-header">
                        <h5>Account Refferal</h5>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" style="table-layout: fixed; word-break: break-word;">
                                <tbody>
                                    <?php foreach($accountRefferal as $accRef) : ?>
                                        <tr>
                                            <td width="30%"><?= implode("/", [$accRef['name'], $accRef['rate'], $accRef['commission']]); ?></td>
                                            <td width="70%" class="text-start">
                                                <a href="javascript:void(0)" class="copytext"><?= $accRef['link'] ?></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="col-md-12">
            <div class="panel">
                <div class="panel-header">
                    <h5>Member Tree</h5>
                    <div class="panel-header-right">
                        <button type="button" class="btn btn-sm btn-primary" id="expandAll">
                            <i class="fa fa-expand"></i> Expand All
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary" id="collapseAll">
                            <i class="fa fa-compress"></i> Collapse All
                        </button>
                    </div>
                </div>
                <div class="panel-body" style="min-height: 300px;">
                    <div class="table-responsive">
                        <table class="tree-table" id="memberTreeTable">
                            <thead>
                                <tr>
                                    <th width="40%">Member Name</th>
                                    <th width="15%">Total Account</th>
                                    <th width="20%">Sales Type</th>
                                    <th width="25%">Email</th>
                                </tr>
                            </thead>
                            <tbody id="treeTableBody">
                                <tr>
                                    <td colspan="4" class="text-center">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        let treeData = [];
        
        $(document).ready(function() {
            // Load tree data from server
            $.post("/ajax/post/ib/treeview1", {}, function(resp) {
                treeData = resp;
                renderTreeTable(resp);
            }, 'json')
        })

        function renderTreeTable(data) {
            const tbody = $('#treeTableBody');
            tbody.empty();
            
            if (!data || data.length === 0) {
                tbody.html('<tr><td colspan="4" class="text-center">No data available</td></tr>');
                return;
            }
            
            data.forEach((item, index) => {
                renderTreeRow(item, tbody, 0, index, 'root');
            });
        }

        function renderTreeRow(item, container, level, index, parentPath) {
            const hasChildren = item.nodes && item.nodes.length > 0;
            const toggleClass = hasChildren ? 'tree-toggle collapsed' : 'tree-toggle no-children';
            const indent = '&nbsp;'.repeat(level * 4);
            const path = parentPath + '-' + index;
            
            // Parse member info from response data
            const textParts = item.text.split(' - ');
            const memberName = textParts[0] || '';
            
            const row = $('<tr>')
                .addClass('tree-row')
                .attr('data-level', level)
                .attr('data-path', path)
                .attr('data-has-children', hasChildren)
                .attr('data-member-id', item.memberId || '');
            
            const nameCell = $('<td>')
                .html(
                    indent +
                    '<span class="' + toggleClass + '" data-path="' + path + '"></span>' +
                    '<i class="' + item.icon + ' tree-icon"></i>' +
                    '<span class="member-name">' + memberName + '</span>'
                );
            
            const totalAccountCell = $('<td>')
                .addClass('text-center')
                .html('<span class="badge badge-primary">' + (item.totalAccount || 0) + '</span>');
            const typeCell = $('<td>').text(item.salesType || 'Trader');
            const emailCell = $('<td>').text(item.email || '-');
            
            row.append(nameCell, totalAccountCell, typeCell, emailCell);
            container.append(row);
            
            // Add children rows (hidden by default)
            if (hasChildren) {
                item.nodes.forEach((child, childIndex) => {
                    renderTreeRow(child, container, level + 1, childIndex, path);
                });
                
                // Hide children initially
                hideChildren(path);
            }
        }

        function hideChildren(parentPath) {
            $('tr[data-path^="' + parentPath + '-"]').each(function() {
                if ($(this).attr('data-path') !== parentPath) {
                    $(this).addClass('hidden-row');
                }
            });
        }

        function showChildren(parentPath) {
            $('tr[data-path^="' + parentPath + '-"]').each(function() {
                const currentPath = $(this).attr('data-path');
                const pathParts = currentPath.split('-');
                const parentParts = parentPath.split('-');
                
                // Only show direct children
                if (pathParts.length === parentParts.length + 1) {
                    $(this).removeClass('hidden-row');
                }
            });
        }

        // Toggle handler
        $(document).on('click', '.tree-toggle:not(.no-children)', function(e) {
            e.stopPropagation();
            const path = $(this).data('path');
            const isExpanded = $(this).hasClass('expanded');
            
            if (isExpanded) {
                // Collapse
                $(this).removeClass('expanded').addClass('collapsed');
                hideChildren(path);
            } else {
                // Expand
                $(this).removeClass('collapsed').addClass('expanded');
                showChildren(path);
            }
        });

        // Expand all button
        $('#expandAll').on('click', function() {
            $('.tree-toggle:not(.no-children)').removeClass('collapsed').addClass('expanded');
            $('tr.tree-row').removeClass('hidden-row');
        });

        // Collapse all button
        $('#collapseAll').on('click', function() {
            $('.tree-toggle:not(.no-children)').removeClass('expanded').addClass('collapsed');
            $('tr.tree-row[data-level!="0"]').addClass('hidden-row');
        });

        $('.copytext').on('click', function() {
            const textToCopy = $(this).text();
            const $this = $(this);
            const originalText = $this.html();
            
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(textToCopy)
                    .then(() => {
                        $this.html('<span class="text-success">Copied!</span>');
                        setTimeout(() => {
                            $this.html(originalText);
                        }, 1500);
                    })
                    .catch(err => {
                        copyTextFallback(textToCopy, $this, originalText);
                    });
            } else {
                copyTextFallback(textToCopy, $this, originalText);
            }
        });

        function copyTextFallback(text, element, originalText) {
            try {
                const tempInput = $('<textarea>');
                tempInput.val(text);
                $('body').append(tempInput);
                
                tempInput.select();
                const successful = document.execCommand('copy');
                tempInput.remove();
                
                if (successful) {
                    element.html('<span class="text-success">Copied!</span>');
                    setTimeout(() => {
                        element.html(originalText);
                    }, 1500);
                } else {
                    throw new Error('Copy failed');
                }
            } catch (err) {
                console.error('Failed to copy text: ', err);
                alert('Failed to copy to clipboard');
            }
        }
    </script>

<?php else : ?>
    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-warning">
                You need to be an IB to access this feature. Please apply to become an IB first.
            </div>
        </div>
    </div>
<?php endif; ?>
