<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();

// Fetch omzet reports - semua data tanpa filter (programmer lihat semua)
$laporanOmzet = $db->query("
    SELECT lo.*, o.nama_outlet, o.alamat_outlet,
           u.nama_lengkap as pengelola,
           inv_user.nama_lengkap as nama_investor
    FROM laporan_omzet lo
    JOIN outlet o ON (o.id_outlet = lo.id_outlet)
    LEFT JOIN users u ON (u.id_users = o.id_users)
    LEFT JOIN investor inv ON (inv.id_investor = o.id_investor)
    LEFT JOIN users inv_user ON (inv_user.id_users = inv.id_users)
    ORDER BY lo.periode_laporan DESC
");
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Monitoring Omzet Toko Madura</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Omzet</li>
        </ol>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="main-content-label mb-1">Rekap Laporan Omzet Harian / Bulanan</h6>
                    <p class="text-muted card-sub-title mb-0">Laporan omzet penjualan bersih yang dimasukkan oleh pengelola toko dari seluruh outlet.</p>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100 align-middle" id="omzet-table">
                        <thead>
                            <tr class="text-center">
                                <th style="width: 5%;">No</th>
                                <th>Periode Laporan</th>
                                <th>Nama Outlet</th>
                                <th>Pengelola (Kasir)</th>
                                <th>Investor Pemodal</th>
                                <th class="text-end">Total Omzet (Rp)</th>
                                <th class="text-center">Potongan (%)</th>
                                <th class="text-end">Potongan (Rp)</th>
                                <th class="text-center">Waktu Input</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($laporanOmzet && $laporanOmzet->num_rows > 0) : ?>
                                <?php $no = 1; while ($row = $laporanOmzet->fetch_assoc()) : ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td><strong><?= date('d M Y', strtotime($row['periode_laporan'])) ?></strong></td>
                                        <td><strong class="text-primary"><?= htmlspecialchars($row['nama_outlet']) ?></strong></td>
                                        <td><?= htmlspecialchars($row['pengelola'] ?? '-') ?></td>
                                        <td>
                                            <?php if (!empty($row['nama_investor'])) : ?>
                                                <span class="badge bg-info"><?= htmlspecialchars($row['nama_investor']) ?></span>
                                            <?php else : ?>
                                                <span class="badge bg-warning">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end fw-bold text-success">Rp <?= number_format($row['omzet'], 0, ',', '.') ?></td>
                                        <td class="text-center"><?= number_format($row['presentase_potongan'], 2, ',', '.') ?>%</td>
                                        <td class="text-end text-danger">Rp <?= number_format($row['nominal_potongan'], 0, ',', '.') ?></td>
                                        <td class="text-center"><?= date('d/m/Y H:i', strtotime($row['waktu_input'])) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">Belum ada laporan omzet toko yang dimasukkan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#omzet-table')) {
        $('#omzet-table').DataTable({
            processing: true,
            deferRender: true,
            scrollX: true,
            lengthMenu: [
                [10, 50, 100, -1],
                [10, 50, 100, "All"]
            ],
            language: {
                searchPlaceholder: 'Cari omzet...',
                sSearch: '',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                paginate: {
                    first: 'First',
                    last: 'Last',
                    next: 'Next',
                    previous: 'Previous'
                }
            },
            order: [[1, 'desc']]
        });
    }
});
</script>
