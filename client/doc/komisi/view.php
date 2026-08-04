<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();
$masterId = (int)($user['MBR_ID'] ?? 0);

// 1. Fetch statistics
$sqlStats = $db->query("
    SELECT 
        IFNULL(SUM(nominal), 0) as total_komisi,
        IFNULL(SUM(CASE WHEN MONTH(tanggal_komisi) = MONTH(CURRENT_DATE()) AND YEAR(tanggal_komisi) = YEAR(CURRENT_DATE()) THEN nominal ELSE 0 END), 0) as komisi_bulan_ini
    FROM komisi_master 
    WHERE id_master = {$masterId}
");
$stats = $sqlStats ? $sqlStats->fetch_assoc() : ['total_komisi' => 0, 'komisi_bulan_ini' => 0];

// 2. Fetch total active outlets under this master
$sqlOutlet = $db->query("
    SELECT COUNT(o.id_outlet) as total_active_outlet
    FROM outlet o
    JOIN investor inv ON inv.id_investor = o.id_investor
    WHERE inv.id_master = {$masterId} AND o.status = 'active'
");
$activeOutlets = $sqlOutlet ? ($sqlOutlet->fetch_assoc()['total_active_outlet'] ?? 0) : 0;

// 3. Fetch list of komisi for this master
$sqlList = $db->query("
    SELECT * 
    FROM komisi_master 
    WHERE id_master = {$masterId} 
    ORDER BY tanggal_komisi DESC, id_komisi DESC
");
?>

<div class="main-content">
    <div class="dashboard-breadcrumb mb-25">
        <div class="row align-items-center g-3">
            <div class="col-12 col-sm">
                <h4 class="fw-bold mb-0">Komisi & Reward Master</h4>
                <p class="text-muted mb-0 small">Rekapitulasi bonus & apresiasi dari Admin atas apresiasi kemitraan investor Anda.</p>
            </div>
        </div>
    </div>

    <!-- Header Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-gradient text-white" style="background: linear-gradient(135deg, #198754 0%, #0f5132 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-white-50 small text-uppercase fw-semibold d-block">Total Komisi Diterima</span>
                        <h3 class="fw-extrabold text-white mb-0 mt-1">Rp <?= number_format($stats['total_komisi'], 0, ',', '.') ?></h3>
                    </div>
                    <div class="rounded-circle bg-white bg-opacity-20 p-3 text-white">
                        <i class="fa-solid fa-trophy fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-gradient text-white" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-white-50 small text-uppercase fw-semibold d-block">Komisi Bulan Ini</span>
                        <h3 class="fw-extrabold text-white mb-0 mt-1">Rp <?= number_format($stats['komisi_bulan_ini'], 0, ',', '.') ?></h3>
                    </div>
                    <div class="rounded-circle bg-white bg-opacity-20 p-3 text-white">
                        <i class="fa-solid fa-calendar-check fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-gradient text-white" style="background: linear-gradient(135deg, #0dcaf0 0%, #087990 100%);">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-white-50 small text-uppercase fw-semibold d-block">Total Toko Binaan Aktif</span>
                        <h3 class="fw-extrabold text-white mb-0 mt-1"><?= number_format($activeOutlets) ?> Toko</h3>
                    </div>
                    <div class="rounded-circle bg-white bg-opacity-20 p-3 text-white">
                        <i class="fa-solid fa-store fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-transparent border-0 p-3 pb-0">
            <h5 class="fw-bold text-dark mb-0">Riwayat Penyerahan Komisi & Reward</h5>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle w-100" id="table-client-komisi">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th class="text-center" style="width: 5%;">No</th>
                            <th class="text-center">Tanggal Transfer</th>
                            <th class="text-center">Periode / Keterangan</th>
                            <th class="text-center">Nominal Komisi</th>
                            <th class="text-center">Catatan dari Admin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($sqlList && $sqlList->num_rows > 0) : ?>
                            <?php $no = 1; while ($row = $sqlList->fetch_assoc()) : ?>
                                <tr>
                                    <td class="text-center"><?= $no++ ?></td>
                                    <td class="text-center fw-semibold"><?= date("d/m/Y H:i", strtotime($row['tanggal_komisi'])) ?></td>
                                    <td class="text-start">
                                        <strong class="text-primary"><?= htmlspecialchars($row['periode']) ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success-subtle text-success border border-success px-3 py-2 fs-6 rounded-pill">
                                            + Rp <?= number_format($row['nominal'], 0, ',', '.') ?>
                                        </span>
                                    </td>
                                    <td class="text-start">
                                        <span class="text-muted small"><?= htmlspecialchars($row['catatan'] ?: '-') ?></span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Belum ada riwayat penerimaan komisi master.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#table-client-komisi')) {
        $('#table-client-komisi').DataTable({
            processing: true,
            deferRender: true,
            scrollX: true,
            lengthMenu: [[10, 50, 100, -1], [10, 50, 100, "All"]],
            language: {
                searchPlaceholder: 'Cari komisi...',
                sSearch: '',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                paginate: { first: 'First', last: 'Last', next: 'Next', previous: 'Previous' }
            },
            order: [[1, 'desc']]
        });
    }
});
</script>
