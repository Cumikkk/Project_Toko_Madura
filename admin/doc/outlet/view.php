<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();

// Fetch outlets list with investor and user details
$outlets = $db->query("
    SELECT o.*, u.nama_lengkap as pengelola_toko, u.no_hp as no_hp_toko, 
           inv_user.nama_lengkap as nama_investor, inv.persen_bagian_investor
    FROM outlet o
    LEFT JOIN users u ON (u.id_users = o.id_users)
    LEFT JOIN investor inv ON (inv.id_investor = o.id_investor)
    LEFT JOIN users inv_user ON (inv_user.id_users = inv.id_users)
    ORDER BY o.nama_outlet ASC
");
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Daftar Cabang Toko Madura</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Outlet</li>
        </ol>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="main-content-label mb-1">Daftar Outlet & Pemetaan Pemodal</h6>
                    <p class="text-muted card-sub-title mb-0">Daftar seluruh cabang Toko Madura beserta investor pemodal di belakangnya.</p>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Outlet</th>
                                <th>Nama Toko / Cabang</th>
                                <th>Pengelola (Kasir)</th>
                                <th>No. HP</th>
                                <th>Investor Pemodal</th>
                                <th>Alamat Toko</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($outlets && $outlets->num_rows > 0) : ?>
                                <?php $no = 1; while ($row = $outlets->fetch_assoc()) : ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($row['kode_outlet']) ?></span></td>
                                        <td><strong class="text-primary"><?= htmlspecialchars($row['nama_outlet']) ?></strong></td>
                                        <td><?= htmlspecialchars($row['pengelola_toko'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($row['no_hp_toko'] ?? '-') ?></td>
                                        <td>
                                            <?php if (!empty($row['nama_investor'])) : ?>
                                                <span class="badge bg-info"><?= htmlspecialchars($row['nama_investor']) ?> (<?= number_format($row['persen_bagian_investor'], 0) ?>%)</span>
                                            <?php else : ?>
                                                <span class="badge bg-warning">Belum Ada Pemodal</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($row['alamat_outlet'] ?? '-') ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Belum ada data cabang toko terdaftar.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
