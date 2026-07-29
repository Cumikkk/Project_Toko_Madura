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
        i.kecamatan,
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
            <p class="text-muted fs-14 mb-0">Daftar investor mitra di bawah naungan Master Owner.</p>
        </div>
    </div>
</div>

<div class="card custom-card border-0 shadow-sm" style="border-radius: 16px;">
    <div class="card-body p-2 p-md-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle w-100" id="table-master-investor">
                <thead class="bg-body-secondary text-uppercase small text-body-secondary">
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th>Nama Investor</th>
                        <th>Kecamatan & Detail Alamat</th>
                        <th class="text-center">Jumlah Outlet</th>
                        <th class="text-center">Tanggal Bergabung</th>
                        <th class="text-center" style="width: 15%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($investors && $investors->num_rows > 0) : ?>
                        <?php $no = 1; while ($inv = $investors->fetch_assoc()) : ?>
                            <tr>
                                <td class="text-center fw-bold text-body-secondary"><?= $no++ ?></td>
                                <td>
                                    <strong class="text-body-emphasis fs-6"><?= htmlspecialchars($inv['nama_lengkap']) ?></strong>
                                    <br><small class="text-body-secondary"><i class="fa-light fa-phone me-1"></i><?= htmlspecialchars($inv['no_hp'] ?? '-') ?></small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="badge bg-light text-body-emphasis border"><i class="fa-light fa-location-dot me-1 text-danger"></i><?= htmlspecialchars($inv['kecamatan'] ?: 'Kecamatan N/A') ?></span>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-detail-alamat-investor rounded-pill px-2 py-0" style="font-size: 11px;"
                                                data-nama="<?= htmlspecialchars($inv['nama_lengkap']) ?>"
                                                data-kecamatan="<?= htmlspecialchars($inv['kecamatan'] ?: '-') ?>"
                                                data-alamat="<?= htmlspecialchars($inv['alamat_investor'] ?: '-') ?>">
                                            <i class="fa-light fa-eye me-1"></i> Detail Alamat
                                        </button>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1 fw-semibold"><?= $inv['total_outlet'] ?> Outlet</span>
                                </td>
                                <td class="text-center small text-body-secondary">
                                    <?= !empty($inv['tanggal_bergabung']) ? date("d M Y", strtotime($inv['tanggal_bergabung'])) : '-' ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm btn-lihat-outlet rounded-pill px-3" data-id="<?= $inv['id_investor'] ?>" data-nama="<?= htmlspecialchars($inv['nama_lengkap']) ?>">
                                        <i class="fa-light fa-store me-1"></i> Lihat Outlet
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-body-secondary">Belum ada data investor terdaftar.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $(document).on('click', '.btn-detail-alamat-investor', function() {
        const nama = $(this).data('nama');
        const kec = $(this).data('kecamatan');
        const alamat = $(this).data('alamat');

        let html = `
            <div class="text-start fs-14">
                <div class="bg-body-tertiary p-3 rounded-3 border mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-body-secondary"><i class="fa-solid fa-user-tie text-danger me-2"></i>Nama Investor</span>
                        <span class="fw-bold text-body-emphasis">${nama}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-body-secondary"><i class="fa-solid fa-map-location-dot text-primary me-2"></i>Kecamatan</span>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3">${kec}</span>
                    </div>
                    <div class="pt-1">
                        <span class="text-body-secondary d-block mb-1"><i class="fa-solid fa-location-dot text-danger me-2"></i>Detail Alamat Lengkap:</span>
                        <p class="fw-semibold text-body-emphasis mb-0 bg-body p-2 rounded border">${alamat}</p>
                    </div>
                </div>
            </div>
        `;

        Swal.fire({
            title: 'Detail Alamat Investor',
            html: html,
            icon: 'info',
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#7D0A0A'
        });
    });
});
</script>

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
                                <th>Lokasi</th>
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
                    let locParts = [];
                    if (item.kecamatan) locParts.push('Kec. ' + item.kecamatan);
                    if (item.alamat_outlet) locParts.push(item.alamat_outlet);
                    let locText = locParts.length > 0 ? locParts.join(' - ') : '-';

                    html += `
                        <tr>
                            <td class="text-center">${idx + 1}</td>
                            <td><strong class="text-primary">${item.nama_outlet}</strong></td>
                            <td>${locText}</td>
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
