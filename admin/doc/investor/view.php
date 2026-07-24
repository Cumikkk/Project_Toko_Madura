<?php
use Config\Core\Database;
use Config\Core\SystemInfo;

$db = Database::connect();

// Fetch investors list
$investors = $db->query("
    SELECT i.*, u.nama_lengkap, u.username, u.email, u.no_hp 
    FROM investor i
    JOIN users u ON (u.id_users = i.id_users)
    ORDER BY u.nama_lengkap ASC
");
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Daftar Investor</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Investor</li>
        </ol>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="main-content-label mb-1">List Investor Toko Madura</h6>
                    <p class="text-muted card-sub-title mb-0">Daftar semua investor beserta persentase pembagian hasil mereka.</p>
                </div>
                <?php if($adminPermissionCore->isHavePermission($moduleId, "create")) : ?>
                    <a href="<?= SystemInfo::app('ADMIN_URL') ?>/investor/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Tambah Investor</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th class="text-center">No</th>
                                <th>Nama Lengkap</th>
                                <th>Username</th>
                                <th>No HP</th>
                                <th>Email</th>
                                <th>Alamat Domisili</th>
                                <th class="text-center">Bagi Hasil (%)</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($investors && $investors->num_rows > 0) : ?>
                                <?php $no = 1; while ($row = $investors->fetch_assoc()) : ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td><strong><?= htmlspecialchars($row['nama_lengkap']) ?></strong></td>
                                        <td><code><?= htmlspecialchars($row['username']) ?></code></td>
                                        <td><?= htmlspecialchars($row['no_hp'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($row['email'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($row['alamat_investor'] ?? '-') ?></td>
                                        <td class="text-center"><span class="badge bg-primary fs-6"><?= number_format($row['persen_bagian_investor'], 2, ',', '.') ?>%</span></td>
                                        <td class="text-center">
                                            <?php if($adminPermissionCore->isHavePermission($moduleId, "update")) : ?>
                                                <a href="<?= SystemInfo::app('ADMIN_URL') ?>/investor/create?id=<?= $row['id_investor'] ?>" class="btn btn-warning btn-sm me-1" title="Edit Investor"><i class="fas fa-edit"></i> Edit Investor</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Belum ada data investor terdaftar.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
