<?php
use Config\Core\Database;
use App\Models\User;
use Config\Core\SystemInfo;

$user = User::user();
$db = Database::connect();
$userId = (int)($user['MBR_ID'] ?? $user['id_users'] ?? 0);

// Filter Periode (Bulan & Tahun)
$selectedBulan = $_GET['bulan'] ?? date('m');
$selectedTahun = $_GET['tahun'] ?? date('Y');

$periodeFilter = "{$selectedTahun}-" . str_pad($selectedBulan, 2, '0', STR_PAD_LEFT);

// Fetch Master profit calculation per outlet
$sqlKeuntungan = "
    SELECT 
        o.id_outlet,
        o.nama_outlet,
        u_out.kecamatan,
        u_inv.nama_lengkap as nama_investor,
        5.00 as persen_master,
        IFNULL(SUM(lo.omzet), 0) as total_omzet,
        IFNULL(SUM(lo.nominal_potongan), 0) as total_potongan
    FROM outlet o
    JOIN investor i ON i.id_investor = o.id_investor
    JOIN users u_inv ON u_inv.id_users = i.id_users
    LEFT JOIN users u_out ON u_out.id_users = o.id_users
    LEFT JOIN laporan_omzet lo ON (lo.id_outlet = o.id_outlet AND DATE_FORMAT(lo.waktu_input, '%Y-%m') = '{$periodeFilter}')
    WHERE i.id_master = {$userId} OR i.id_master IS NULL
    GROUP BY o.id_outlet
    ORDER BY o.id_outlet DESC
";

$reports = $db->query($sqlKeuntungan);

$totalOmzetMaster = 0;
$totalPotonganMaster = 0;
$totalHakMaster = 0;
?>

<div class="row row-sm mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-1">Keuntungan Master Owner</h3>
            <p class="text-muted fs-14 mb-0">Kalkulasi otomatis porsi keuntungan Master Owner berdasarkan akumulasi omzet outlet.</p>
        </div>
        <!-- Periode Filter -->
        <form method="GET" action="" class="d-flex align-items-center gap-2">
            <select name="bulan" class="form-select form-select-sm">
                <?php
                $bulanList = [
                    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                ];
                foreach ($bulanList as $k => $v) {
                    $selected = ($k == str_pad($selectedBulan, 2, '0', STR_PAD_LEFT)) ? 'selected' : '';
                    echo "<option value='{$k}' {$selected}>{$v}</option>";
                }
                ?>
            </select>
            <select name="tahun" class="form-select form-select-sm">
                <?php
                $currentYear = (int)date('Y');
                for ($y = $currentYear; $y >= $currentYear - 3; $y--) {
                    $selected = ($y == $selectedTahun) ? 'selected' : '';
                    echo "<option value='{$y}' {$selected}>{$y}</option>";
                }
                ?>
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa-light fa-filter me-1"></i> Filter</button>
        </form>
    </div>
</div>

<!-- Output Data Table -->
<div class="card custom-card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle w-100" id="table-keuntungan-master">
                <thead class="bg-light text-center">
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th>Nama Outlet</th>
                        <th>Investor Pemodal</th>
                        <th class="text-end">Omzet Kotor</th>
                        <th class="text-end">Potongan (10%)</th>
                        <th class="text-end">Omzet Bersih</th>
                        <th class="text-center">Bagian Master (%)</th>
                        <th class="text-end">Hak Master (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($reports && $reports->num_rows > 0) : ?>
                        <?php 
                        $no = 1; 
                        while ($row = $reports->fetch_assoc()) :
                            $omzet = (float)$row['total_omzet'];
                            $potongan = (float)$row['total_potongan'];
                            $omzetBersih = $omzet - $potongan;
                            $persenMaster = (float)$row['persen_master'];
                            $hakMaster = $omzetBersih * ($persenMaster / 100.0);

                            $totalOmzetMaster += $omzet;
                            $totalPotonganMaster += $potongan;
                            $totalHakMaster += $hakMaster;
                        ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td>
                                    <strong class="text-primary"><?= htmlspecialchars($row['nama_outlet']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($row['nama_investor']) ?></td>
                                <td class="text-end fw-bold">Rp <?= number_format($omzet, 0, ',', '.') ?></td>
                                <td class="text-end text-danger">Rp <?= number_format($potongan, 0, ',', '.') ?></td>
                                <td class="text-end fw-bold text-dark">Rp <?= number_format($omzetBersih, 0, ',', '.') ?></td>
                                <td class="text-center"><span class="badge bg-info text-white fs-6"><?= number_format($persenMaster, 2) ?>%</span></td>
                                <td class="text-end fw-bold text-success fs-6">Rp <?= number_format($hakMaster, 0, ',', '.') ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Belum ada data omzet untuk periode terpilih.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="bg-light fw-bold">
                    <tr>
                        <td colspan="3" class="text-end text-uppercase">Total Akumulasi:</td>
                        <td class="text-end text-primary">Rp <?= number_format($totalOmzetMaster, 0, ',', '.') ?></td>
                        <td class="text-end text-danger">Rp <?= number_format($totalPotonganMaster, 0, ',', '.') ?></td>
                        <td class="text-end text-dark">Rp <?= number_format($totalOmzetMaster - $totalPotonganMaster, 0, ',', '.') ?></td>
                        <td></td>
                        <td class="text-end text-success fs-6">Rp <?= number_format($totalHakMaster, 0, ',', '.') ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#table-keuntungan-master')) {
        $('#table-keuntungan-master').DataTable({
            processing: true,
            scrollX: true
        });
    }
});
</script>
