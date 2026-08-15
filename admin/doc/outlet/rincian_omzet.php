<?php
use Config\Core\Database;
use Config\Core\SystemInfo;
use App\Models\Helper;
use App\Models\Outlet;

$queryParam = Helper::getSafeInput($_GET);
$idOutlet     = intval($queryParam['id'] ?? 0);
$selectedBulan = isset($queryParam['bulan']) ? intval($queryParam['bulan']) : 0;
$selectedTahun = isset($queryParam['tahun']) ? intval($queryParam['tahun']) : 0;

if ($idOutlet <= 0) {
    echo '<div class="alert alert-danger"><i class="fa fa-exclamation-circle me-2"></i>ID Outlet tidak valid.</div>';
    return;
}

$result  = Outlet::getOutletOmzetDetail($idOutlet, $selectedBulan, $selectedTahun);
$outlet  = $result['outlet'] ?? null;
$summary = $result['summary'] ?? [];
$transaksi = $result['transaksi'] ?? [];

if (!$outlet) {
    echo '<div class="alert alert-danger"><i class="fa fa-exclamation-circle me-2"></i>Outlet tidak ditemukan.</div>';
    return;
}

$db = Database::connect();
$resTahunRincian = $db->query("SELECT DISTINCT YEAR(tanggal_omzet) as tahun FROM laporan_omzet WHERE id_outlet = {$idOutlet} AND tanggal_omzet IS NOT NULL AND tanggal_omzet != '0000-00-00' ORDER BY tahun DESC");
$listTahunRincian = [];
if ($resTahunRincian && $resTahunRincian->num_rows > 0) {
    while ($rowT = $resTahunRincian->fetch_assoc()) {
        if (!empty($rowT['tahun'])) {
            $listTahunRincian[] = intval($rowT['tahun']);
        }
    }
}
if (empty($listTahunRincian)) {
    $listTahunRincian[] = intval(date('Y'));
}

$namaBulan = [
    1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
    7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
];

if ($selectedBulan > 0 && $selectedTahun > 0) {
    $periodeLabel = ($namaBulan[$selectedBulan] ?? '-') . ' ' . $selectedTahun;
} elseif ($selectedBulan > 0) {
    $periodeLabel = ($namaBulan[$selectedBulan] ?? '-') . ' (Semua Tahun)';
} elseif ($selectedTahun > 0) {
    $periodeLabel = 'Semua Bulan ' . $selectedTahun;
} else {
    $periodeLabel = 'Semua Periode';
}
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Rincian Omzet Outlet</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/outlet/omzet?bulan=<?= $selectedBulan ?>&tahun=<?= $selectedTahun ?>">Monitoring Omzet</a></li>
            <li class="breadcrumb-item active" aria-current="page">Rincian Omzet</li>
        </ol>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-header">
                <div class="d-flex justify-content-between mb-2">
                    <h5 class="card-title mb-0">
                        <i class="fa fa-bar-chart me-2 text-info"></i>
                        <?= htmlspecialchars($outlet['nama_outlet']) ?>
                        <small class="text-muted fw-normal ms-2">— Periode: <?= $periodeLabel ?></small>
                    </h5>
                    <a href="<?= SystemInfo::app('ADMIN_URL') ?>/outlet/omzet?bulan=<?= $selectedBulan ?>&tahun=<?= $selectedTahun ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">

                <!-- Filter Periode -->
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <div class="d-flex align-items-start mb-2">
                                <i class="fa fa-building text-primary me-2 mt-1" style="width:18px;"></i>
                                <div>
                                    <div class="text-muted small">Outlet</div>
                                    <strong class="text-dark"><?= htmlspecialchars($outlet['nama_outlet']) ?></strong>
                                </div>
                            </div>
                            <div class="d-flex align-items-start">
                                <i class="fa fa-user text-info me-2 mt-1" style="width:18px;"></i>
                                <div>
                                    <div class="text-muted small">Pengelola</div>
                                    <span class="text-dark"><?= htmlspecialchars($outlet['pengelola_toko'] ?? '-') ?></span>
                                    <?php if (!empty($outlet['no_hp_toko'])) : ?>
                                        <br><small class="text-muted"><i class="fab fa-whatsapp text-success me-1"></i><?= htmlspecialchars($outlet['no_hp_toko']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-start mb-2">
                                <i class="fa fa-handshake-o text-success me-2 mt-1" style="width:18px;"></i>
                                <div>
                                    <div class="text-muted small">Investor</div>
                                    <?php if (!empty($outlet['nama_investor'])) : ?>
                                        <strong class="text-primary"><?= htmlspecialchars($outlet['nama_investor']) ?></strong>
                                        <?php if (!empty($outlet['username_investor'])) : ?>
                                            <br><small class="text-muted"><code>@<?= htmlspecialchars($outlet['username_investor']) ?></code></small>
                                        <?php endif; ?>
                                    <?php else : ?>
                                        <span class="text-muted">Belum Ada Investor</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="d-flex align-items-start">
                                <i class="fa fa-money text-warning me-2 mt-1" style="width:18px;"></i>
                                <div>
                                    <div class="text-muted small">Tarif Langganan</div>
                                    <strong class="text-success">Rp <?= number_format($outlet['biaya_langganan_outlet'] ?? 0, 0, ',', '.') ?> / Bln</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <form method="GET" action="<?= SystemInfo::app('ADMIN_URL') ?>/outlet/rincian_omzet">
                                <input type="hidden" name="id" value="<?= $idOutlet ?>">
                                <label class="form-label small fw-bold mb-1">Ganti Periode</label>
                                <div class="input-group input-group-sm">
                                    <select name="bulan" class="form-select">
                                        <option value="0" <?= $selectedBulan === 0 ? 'selected' : '' ?>>Semua Bulan</option>
                                        <?php foreach ($namaBulan as $mNum => $mName) : ?>
                                            <option value="<?= $mNum ?>" <?= $selectedBulan === $mNum ? 'selected' : '' ?>><?= $mName ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select name="tahun" class="form-select">
                                        <option value="0" <?= $selectedTahun === 0 ? 'selected' : '' ?>>Semua Tahun</option>
                                        <?php foreach ($listTahunRincian as $y) : ?>
                                            <option value="<?= $y ?>" <?= $selectedTahun === $y ? 'selected' : '' ?>><?= $y ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fe fe-filter"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- 4 Stat Cards -->
                <div class="row row-sm g-2 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="card custom-card mb-0">
                            <div class="card-body p-3 text-center">
                                <h6 class="text-muted tx-12 mb-1">Total Omzet</h6>
                                <h5 class="text-success fw-bold mb-0">Rp <?= number_format($summary['total_omzet'] ?? 0, 0, ',', '.') ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card custom-card mb-0">
                            <div class="card-body p-3 text-center">
                                <h6 class="text-muted tx-12 mb-1">Potongan Sistem</h6>
                                <h5 class="text-danger fw-bold mb-0">Rp <?= number_format($summary['total_potongan'] ?? 0, 0, ',', '.') ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card custom-card mb-0">
                            <div class="card-body p-3 text-center">
                                <h6 class="text-muted tx-12 mb-1">Hak Investor</h6>
                                <h5 class="text-primary fw-bold mb-0">Rp <?= number_format($summary['total_hak_investor'] ?? 0, 0, ',', '.') ?></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card custom-card mb-0">
                            <div class="card-body p-3 text-center">
                                <h6 class="text-muted tx-12 mb-1">Hak Outlet</h6>
                                <h5 class="text-warning fw-bold mb-0">Rp <?= number_format($summary['total_hak_outlet'] ?? 0, 0, ',', '.') ?></h5>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DataTable Transaksi Omzet -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100 align-middle" id="table-rincian-omzet">
                        <thead>
                            <tr class="text-center">
                                <th class="text-center" style="width:5%">NO</th>
                                <th class="text-center">TANGGAL OMZET</th>
                                <th class="text-center">OMZET KOTOR</th>
                                <th class="text-center">POTONGAN SISTEM</th>
                                <th class="text-center">HAK INVESTOR</th>
                                <th class="text-center">HAK OUTLET</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($transaksi)) : ?>
                                <?php $no = 1; foreach ($transaksi as $t) :
                                    $tgl = !empty($t['tanggal_omzet'])
                                        ? implode('/', array_reverse(explode('-', $t['tanggal_omzet'])))
                                        : '-';
                                ?>
                                    <tr class="text-center">
                                        <td><?= $no++ ?></td>
                                        <td><strong><?= $tgl ?></strong></td>
                                        <td class="text-end text-success fw-bold">Rp <?= number_format($t['nominal_omzet'] ?? 0, 0, ',', '.') ?></td>
                                        <td class="text-end text-danger">
                                            Rp <?= number_format($t['nominal_potongan'] ?? 0, 0, ',', '.') ?>
                                            <small class="text-muted">(<?= number_format($t['persentase_potongan'] ?? 0, 0) ?>%)</small>
                                        </td>
                                        <td class="text-end text-primary fw-bold">
                                            Rp <?= number_format($t['nominal_hak_investor'] ?? 0, 0, ',', '.') ?>
                                            <small class="text-muted">(<?= number_format($t['persentase_hak_investor'] ?? 0, 0) ?>%)</small>
                                        </td>
                                        <td class="text-end text-warning fw-bold">Rp <?= number_format($t['nominal_hak_outlet'] ?? 0, 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="fa fa-info-circle me-2"></i>Tidak ada transaksi omzet pada periode <?= $periodeLabel ?>.
                                    </td>
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
    if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#table-rincian-omzet')) {
        var dtRincian = $('#table-rincian-omzet').DataTable({
            processing: true,
            deferRender: true,
            scrollX: true,
            lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
            pageLength: 5,
            language: {
                searchPlaceholder: 'Cari transaksi...',
                sSearch: '',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                paginate: { first: 'First', last: 'Last', next: 'Next', previous: 'Previous' },
                emptyTable: 'Tidak ada transaksi omzet pada periode ini.'
            },
            order: [[1, 'desc']]
        });

        if ($.fn.select2) {
            setTimeout(function() {
                $('#table-rincian-omzet_wrapper .dataTables_length select').select2({
                    minimumResultsForSearch: Infinity,
                    width: 'auto'
                });
            }, 50);
        }
    }
});
</script>
