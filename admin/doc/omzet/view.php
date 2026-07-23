<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();

// Fetch omzet reports
$laporanOmzet = $db->query("
    SELECT lo.*, o.nama_outlet, o.kode_outlet, u.nama_lengkap as pengelola
    FROM laporan_omzet lo
    JOIN outlet o ON (o.id_outlet = lo.id_outlet)
    LEFT JOIN users u ON (u.id_users = o.id_users)
    ORDER BY lo.periode_laporan DESC
");
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Monitoring Omzet Toko</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Omzet</li>
        </ol>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="main-content-label mb-1">Rekap Laporan Omzet Harian / Bulanan</h6>
                    <p class="text-muted card-sub-title mb-0">Laporan omzet penjualan bersih yang dimasukkan oleh pengelola toko.</p>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Periode Laporan</th>
                                <th>Nama Outlet</th>
                                <th>Pengelola</th>
                                <th>Total Omzet (Rp)</th>
                                <th>Potongan Platform (%)</th>
                                <th>Nominal Potongan (Rp)</th>
                                <th>Waktu Input</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($laporanOmzet && $laporanOmzet->num_rows > 0) : ?>
                                <?php $no = 1; while ($row = $laporanOmzet->fetch_assoc()) : ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><strong><?= date('d M Y', strtotime($row['periode_laporan'])) ?></strong></td>
                                        <td><span class="badge bg-primary"><?= htmlspecialchars($row['nama_outlet']) ?></span></td>
                                        <td><?= htmlspecialchars($row['pengelola'] ?? '-') ?></td>
                                        <td class="text-end fw-bold text-success">Rp <?= number_format($row['omzet'], 0, ',', '.') ?></td>
                                        <td class="text-center"><?= number_format($row['presentase_potongan'], 2, ',', '.') ?>%</td>
                                        <td class="text-end text-danger">Rp <?= number_format($row['nominal_potongan'], 0, ',', '.') ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($row['waktu_input'])) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Belum ada laporan omzet toko yang dimasukkan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
