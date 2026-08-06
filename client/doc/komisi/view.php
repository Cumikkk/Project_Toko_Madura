<?php
use Config\Core\Database;
use App\Models\User;
use Config\Core\SystemInfo;

$user = User::user();
$db = Database::connect();
$masterId = (int)($user['MBR_ID'] ?? $user['id_users'] ?? 0);

// Pagination setup for Client Komisi view
$limit  = 10;
$page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Count Total Komisi Records for Master
$resTotalRec = $db->query("SELECT COUNT(id_komisi) as total FROM komisi_master WHERE id_master = {$masterId}");
$totalRecords = ($resTotalRec && $rowT = $resTotalRec->fetch_assoc()) ? (int)$rowT['total'] : 0;
$totalPages   = ceil($totalRecords / $limit);

// Fetch Komisi Statistics
$sqlStats = $db->query("
    SELECT 
        IFNULL(SUM(nominal), 0) as total_komisi,
        IFNULL(SUM(CASE WHEN MONTH(tanggal_komisi) = MONTH(CURRENT_DATE()) AND YEAR(tanggal_komisi) = YEAR(CURRENT_DATE()) THEN nominal ELSE 0 END), 0) as komisi_bulan_ini
    FROM komisi_master 
    WHERE id_master = {$masterId}
");
$stats = $sqlStats ? $sqlStats->fetch_assoc() : ['total_komisi' => 0, 'komisi_bulan_ini' => 0];

// Fetch Paginated Komisi List
$sqlList = "
    SELECT * 
    FROM komisi_master 
    WHERE id_master = {$masterId} 
    ORDER BY tanggal_komisi DESC, id_komisi DESC
    LIMIT {$limit} OFFSET {$offset}
";
$resKomisi = $db->query($sqlList);
$komisiList = [];
if ($resKomisi && $resKomisi->num_rows > 0) {
    while ($row = $resKomisi->fetch_assoc()) {
        $komisiList[] = $row;
    }
}

if (!function_exists('buildKomisiPageUrl')) {
    function buildKomisiPageUrl($p) {
        $params = $_GET;
        $params['page'] = $p;
        return '?' . http_build_query($params);
    }
}
?>

<div class="main-content-inner py-3 py-md-4">
    <!-- 1. Header Banner Card (Maroon Gradient Style - Matching Client Standard) -->
    <div class="row mb-3 mb-md-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 16px; background: linear-gradient(135deg, #7D0A0A 0%, #4D0709 100%); color: #fff;">
                <div class="card-body p-4 p-md-5">
                    <div class="row align-items-center g-3">
                        <div class="col-12">
                            <span class="badge bg-white text-danger fw-bold px-3 py-2 rounded-pill mb-2 text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                <i class="fa-solid fa-crown text-warning me-1"></i> Master Access
                            </span>
                            <h2 class="fw-bold mb-2 text-white fs-3 fs-md-2">Komisi Master</h2>
                            <p class="text-white-50 small mb-0">Rekapitulasi komisi & apresiasi dari Admin atas kontribusi kemitraan investor Anda.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Metrics Summary Cards (Clean Border & Gradient Icons - Matching Outlet & Investor Page) -->
    <div class="row g-2 g-md-3 mb-4">
        <!-- Card 1: Total Komisi Diterima -->
        <div class="col-md-6 col-12">
            <div class="card border border-body-subtle shadow-sm h-100" style="border-radius: 14px; border-left: 5px solid #198754 !important;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: linear-gradient(135deg, #198754 0%, #0d5132 100%);">
                        <i class="fa-solid fa-trophy fs-4"></i>
                    </div>
                    <div>
                        <div class="text-body-secondary small fw-semibold">Total Komisi Diterima</div>
                        <div class="fs-4 fw-bold text-success mb-0">Rp <?= number_format($stats['total_komisi'], 0, ',', '.'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Komisi Bulan Ini -->
        <div class="col-md-6 col-12">
            <div class="card border border-body-subtle shadow-sm h-100" style="border-radius: 14px; border-left: 5px solid #0d6efd !important;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                        <i class="fa-solid fa-calendar-check fs-4"></i>
                    </div>
                    <div>
                        <div class="text-body-secondary small fw-semibold">Komisi Bulan Ini</div>
                        <div class="fs-4 fw-bold text-primary mb-0">Rp <?= number_format($stats['komisi_bulan_ini'], 0, ',', '.'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Table Card Data Komisi (Matching Investor Page Standard) -->
    <div class="row">
        <div class="col-12">
            <div class="card border border-body-subtle shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-body py-3 px-4 d-flex align-items-center justify-content-between border-bottom border-body-subtle">
                    <h5 class="fw-bold text-body-emphasis mb-0 fs-6"><i class="fa-solid fa-award me-2 text-danger"></i>Riwayat Penyerahan Komisi</h5>
                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1.5 fw-semibold fs-12">
                        <i class="fa-solid fa-shield-halved me-1"></i>Master Owner View
                    </span>
                </div>
                <div class="card-body p-2 p-md-4">
                    <div class="table-responsive">
                        <table id="table-client-komisi" class="table table-hover align-middle mb-0 w-100">
                            <thead class="table-group-divider bg-body-secondary">
                                <tr class="text-uppercase small text-body-secondary">
                                    <th class="ps-3 text-center" style="width: 50px;">No</th>
                                    <th class="text-center">Tanggal Transfer</th>
                                    <th>Periode / Keterangan</th>
                                    <th class="text-center">Nominal Komisi</th>
                                    <th class="text-center">Bukti Bayar</th>
                                    <th class="text-start">Catatan Admin</th>
                                </tr>
                            </thead>
                            <tbody class="border-0">
                                <?php if (!empty($komisiList)) : ?>
                                    <?php $no = $offset + 1; foreach ($komisiList as $km) : ?>
                                        <tr>
                                            <td class="ps-3 text-center fw-bold text-body-secondary"><?= $no++ ?></td>
                                            <td class="text-center small text-body-secondary fw-semibold">
                                                <?= date("d M Y H:i", strtotime($km['tanggal_komisi'])) ?>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-body-emphasis mb-0 fs-6"><?= htmlspecialchars($km['periode']) ?></div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-bold fs-12">
                                                    + Rp <?= number_format($km['nominal'], 0, ',', '.') ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if (!empty($km['bukti_pembayaran'])) : ?>
                                                    <?php $fileExt = strtolower(pathinfo($km['bukti_pembayaran'], PATHINFO_EXTENSION)); ?>
                                                    <?php if ($fileExt === 'pdf') : ?>
                                                        <a href="<?= SystemInfo::app('CLIENT_URL') ?>/<?= htmlspecialchars($km['bukti_pembayaran']) ?>" target="_blank" class="btn btn-outline-info btn-sm">
                                                            <i class="fas fa-file-pdf me-1"></i> Lihat PDF
                                                        </a>
                                                    <?php else : ?>
                                                        <button type="button" class="btn btn-outline-info btn-sm btn-client-view-bukti-komisi" 
                                                                data-img="<?= SystemInfo::app('CLIENT_URL') ?>/<?= htmlspecialchars($km['bukti_pembayaran']) ?>" 
                                                                data-master="<?= htmlspecialchars($user['MBR_NAME'] ?? 'Master Owner') ?>"
                                                                data-periode="<?= htmlspecialchars($km['periode']) ?>" 
                                                                data-nominal="Rp <?= number_format($km['nominal'], 0, ',', '.') ?>">
                                                            <i class="fas fa-image me-1"></i> Lihat Bukti
                                                        </button>
                                                    <?php endif; ?>
                                                <?php else : ?>
                                                    <span class="badge bg-light text-dark">Belum ada</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-start small text-body-secondary">
                                                <?= htmlspecialchars($km['catatan'] ?: '-') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-body-secondary">
                                            <i class="fa-solid fa-receipt fs-1 text-muted opacity-50 mb-2 d-block"></i>
                                            Belum ada riwayat penerimaan komisi master terdaftar.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Controls & Record Summary Footer -->
                    <?php if ($totalRecords > 0) : ?>
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-3 border-top border-body-subtle mt-2">
                            <div class="small text-body-secondary fw-semibold ms-1">
                                Menampilkan <span class="text-body-emphasis fw-bold"><?= ($totalRecords > 0) ? ($offset + 1) : 0; ?></span> - <span class="text-body-emphasis fw-bold"><?= min($offset + $limit, $totalRecords); ?></span> dari <span class="text-body-emphasis fw-bold"><?= $totalRecords; ?></span> komisi
                            </div>

                            <?php if ($totalPages > 1) : ?>
                                <nav aria-label="Navigasi Halaman Komisi">
                                    <ul class="pagination pagination-sm mb-0">
                                        <!-- Previous Page -->
                                        <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                                            <a class="page-link rounded-start-pill text-body-emphasis px-3" href="<?= buildKomisiPageUrl($page - 1); ?>">
                                                <i class="fa-solid fa-chevron-left me-1"></i> Prev
                                            </a>
                                        </li>

                                        <!-- Page Numbers -->
                                        <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                                            <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                                                <a class="page-link px-3 <?= ($i == $page) ? 'fw-bold' : 'text-body-emphasis'; ?>" href="<?= buildKomisiPageUrl($i); ?>"><?= $i; ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <!-- Next Page -->
                                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                            <a class="page-link rounded-end-pill text-body-emphasis px-3" href="<?= buildKomisiPageUrl($page + 1); ?>">
                                                Next <i class="fa-solid fa-chevron-right ms-1"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $(document).on('click', '.btn-client-view-bukti-komisi', function() {
        let imgUrl  = $(this).data('img');
        let master  = $(this).data('master');
        let periode = $(this).data('periode');
        let nominal = $(this).data('nominal');

        var infoHtml = '<div class="text-start bg-light p-3 rounded mb-3" style="font-size:13.5px; border:1px solid #e9ecef;">'
            + '<div class="d-flex align-items-center mb-2">'
            + '  <i class="fa fa-user-circle text-primary me-2" style="width:20px; text-align:center;"></i>'
            + '  <span style="min-width:140px;" class="fw-bold">Master Owner:</span>'
            + '  <span class="text-dark fw-semibold">' + (master || '-') + '</span>'
            + '</div>'
            + '<div class="d-flex align-items-center mb-2">'
            + '  <i class="fa fa-calendar-check-o text-success me-2" style="width:20px; text-align:center;"></i>'
            + '  <span style="min-width:140px;" class="fw-bold">Keterangan:</span>'
            + '  <span class="text-dark">' + (periode || '-') + '</span>'
            + '</div>'
            + '<div class="d-flex align-items-center">'
            + '  <i class="fa fa-money text-warning me-2" style="width:20px; text-align:center;"></i>'
            + '  <span style="min-width:140px;" class="fw-bold">Nominal Komisi:</span>'
            + '  <span class="text-success fw-bold">' + nominal + '</span>'
            + '</div>'
            + '</div>';

        Swal.fire({
            title: '<i class="fa fa-file-text-o me-2 text-info"></i>Bukti Pembayaran Komisi Master',
            html: infoHtml
                + '<img src="' + imgUrl + '" '
                + 'style="max-width:100%;max-height:60vh;border-radius:8px;border:1px solid #dee2e6;object-fit:contain;" '
                + 'onerror="this.outerHTML=\'<p class=\\\'text-danger mt-2\\\'><i class=\\\'fa fa-exclamation-triangle me-1\\\'></i> Gambar gagal dimuat</p>\'">',
            showCloseButton: true,
            showConfirmButton: false,
            scrollbarPadding: false,
            heightAuto: false,
            width: 640
        });
    });
});
</script>
