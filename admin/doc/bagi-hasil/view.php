<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();

// Fetch rekap bagi hasil list
$rekapList = $db->query("
    SELECT r.*, u.nama_lengkap as nama_investor, u.no_hp as no_hp_investor
    FROM rekap_bagi_hasil r
    JOIN investor i ON (i.id_investor = r.id_investor)
    JOIN users u ON (u.id_users = i.id_users)
    ORDER BY r.periode_rekap DESC
");
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Rekapitulasi Bagi Hasil</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Bagi Hasil</li>
        </ol>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="main-content-label mb-1">Rekapitulasi Pembagian Keuntungan Investor & Outlet</h6>
                    <p class="text-muted card-sub-title mb-0">Rincian hak keuntungan yang menjadi bagian Investor dan Pengelola Outlet.</p>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Periode Rekap</th>
                                <th>Nama Investor</th>
                                <th>Akumulasi Omzet (Rp)</th>
                                <th>Potongan Platform (Rp)</th>
                                <th>Hak Investor (Rp)</th>
                                <th>Hak Outlet (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($rekapList && $rekapList->num_rows > 0) : ?>
                                <?php $no = 1; while ($row = $rekapList->fetch_assoc()) : ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><span class="badge bg-primary"><?= htmlspecialchars($row['periode_rekap']) ?></span></td>
                                        <td><strong><?= htmlspecialchars($row['nama_investor']) ?></strong></td>
                                        <td class="text-end fw-bold">Rp <?= number_format($row['akumulasi_omzet'], 0, ',', '.') ?></td>
                                        <td class="text-end text-danger">Rp <?= number_format($row['akumulasi_potongan'], 0, ',', '.') ?></td>
                                        <td class="text-end fw-bold text-success">Rp <?= number_format($row['hak_investor'], 0, ',', '.') ?></td>
                                        <td class="text-end fw-bold text-info">Rp <?= number_format($row['hak_outlet'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Belum ada rekapitulasi bagi hasil.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
