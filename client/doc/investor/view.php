<?php
use Config\Core\Database;
use App\Models\User;
use Config\Core\SystemInfo;

$user = User::user();
$db = Database::connect();
$userId = (int)($user['MBR_ID'] ?? $user['id_users'] ?? 0);

// Fetch all investors for Master Owner (Non-Financial)
$sqlInv = "
    SELECT 
        i.id_investor,
        u.nama_lengkap,
        u.no_hp,
        i.alamat_investor,
        i.tanggal_bergabung,
        COUNT(o.id_outlet) as total_outlet
    FROM investor i
    JOIN users u ON u.id_users = i.id_users
    LEFT JOIN outlet o ON o.id_investor = i.id_investor
    WHERE i.id_master = {$userId} OR i.id_master IS NULL
    GROUP BY i.id_investor
    ORDER BY i.id_investor DESC
";

$investors = $db->query($sqlInv);
?>

<div class="row row-sm mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold text-dark mb-1">Data Investor Pemodal</h3>
            <p class="text-muted fs-14 mb-0">Daftar investor mitra di bawah naungan Master Owner (Fokus Non-Keuangan).</p>
        </div>
    </div>
</div>

<div class="card custom-card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle w-100" id="table-master-investor">
                <thead class="bg-light text-center">
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th>Nama Lengkap</th>
                        <th>No. HP</th>
                        <th>Lokasi Investor</th>
                        <th class="text-center">Jumlah Outlet</th>
                        <th class="text-center">Tanggal Bergabung</th>
                        <th class="text-center" style="width: 15%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($investors && $investors->num_rows > 0) : ?>
                        <?php $no = 1; while ($inv = $investors->fetch_assoc()) : ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><strong class="text-primary"><?= htmlspecialchars($inv['nama_lengkap']) ?></strong></td>
                                <td><i class="fa-light fa-phone me-1 text-muted"></i><?= htmlspecialchars($inv['no_hp'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($inv['alamat_investor'] ?? '-') ?></td>
                                <td class="text-center">
                                    <span class="badge bg-primary rounded-pill px-3 fs-13"><?= $inv['total_outlet'] ?> Outlet</span>
                                </td>
                                <td class="text-center">
                                    <?= !empty($inv['tanggal_bergabung']) ? date("d/m/Y", strtotime($inv['tanggal_bergabung'])) : '-' ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-info btn-sm btn-lihat-outlet text-white" data-id="<?= $inv['id_investor'] ?>" data-nama="<?= htmlspecialchars($inv['nama_lengkap']) ?>">
                                        <i class="fa-light fa-store me-1"></i> Lihat Outlet
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Belum ada data investor terdaftar.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detail Outlet Investor -->
<div class="modal fade" id="modalDetailOutlet" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fa-light fa-store me-2"></i>Daftar Outlet Investor: <span id="modal-investor-nama" class="fw-bold"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-center">
                            <tr>
                                <th>No</th>
                                <th>Nama Outlet</th>
                                <th>Lokasi (Kecamatan)</th>
                                <th>Nama Investor (Pemilik)</th>
                                <th>Tanggal Bergabung</th>
                            </tr>
                        </thead>
                        <tbody id="container-detail-outlet">
                            <tr><td colspan="5" class="text-center py-3 text-muted">Memuat data outlet...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#table-master-investor')) {
        $('#table-master-investor').DataTable({
            processing: true,
            scrollX: true
        });
    }

    $('.btn-lihat-outlet').on('click', function() {
        let idInv = $(this).data('id');
        let namaInv = $(this).data('nama');

        $('#modal-investor-nama').text(namaInv);
        $('#container-detail-outlet').html('<tr><td colspan="5" class="text-center py-3 text-muted">Memuat data outlet...</td></tr>');
        $('#modalDetailOutlet').modal('show');

        $.get("<?= SystemInfo::app('CLIENT_URL') ?>/ajax/get/investor/outlets", { id_investor: idInv }, function(resp) {
            if (resp.success && resp.data.length > 0) {
                let html = '';
                $.each(resp.data, function(idx, item) {
                    html += `
                        <tr>
                            <td class="text-center">${idx + 1}</td>
                            <td><strong class="text-primary">${item.nama_outlet}</strong> <small class="text-muted">(${item.kode_outlet})</small></td>
                            <td>${item.kecamatan || item.alamat_outlet || '-'}</td>
                            <td><span class="badge bg-light text-dark border">${namaInv}</span></td>
                            <td class="text-center">${item.tanggal_bergabung || '-'}</td>
                        </tr>
                    `;
                });
                $('#container-detail-outlet').html(html);
            } else {
                $('#container-detail-outlet').html('<tr><td colspan="5" class="text-center py-4 text-muted">Investor ini belum memiliki outlet terdaftar.</td></tr>');
            }
        }, 'json');
    });
});
</script>
