<?php
use Config\Core\Database;
use Config\Core\SystemInfo;
use App\Models\Helper;

$queryParam = Helper::getSafeInput($_GET);
$idOutlet = intval($queryParam['id'] ?? 0);

if ($idOutlet <= 0) {
    echo '<div class="alert alert-danger"><i class="fe fe-alert-circle me-2"></i>ID Outlet tidak valid.</div>';
    return;
}

$db = Database::connect();

// Fetch outlet info
$resOutlet = $db->query("
    SELECT o.id_outlet, o.nama_outlet, o.tgl_jatuh_tempo, o.status,
           u_kasir.nama_lengkap as pengelola_toko, u_kasir.no_hp as no_hp_toko,
           inv.id_investor, inv.biaya_langganan_outlet,
           u_inv.nama_lengkap as nama_investor, u_inv.username as username_investor, u_inv.no_hp as no_hp_investor
    FROM outlet o
    LEFT JOIN users u_kasir ON u_kasir.id_users = o.id_users
    LEFT JOIN investor inv ON inv.id_investor = o.id_investor
    LEFT JOIN users u_inv ON u_inv.id_users = inv.id_users
    WHERE o.id_outlet = {$idOutlet}
    LIMIT 1
");
$outlet = ($resOutlet && $resOutlet->num_rows > 0) ? $resOutlet->fetch_assoc() : null;

if (!$outlet) {
    echo '<div class="alert alert-danger"><i class="fe fe-alert-circle me-2"></i>Outlet tidak ditemukan.</div>';
    return;
}

// Fetch riwayat langganan
$riwayat = [];
$resRiwayat = $db->query("SELECT * FROM riwayat_langganan WHERE id_outlet = {$idOutlet} ORDER BY id_riwayat DESC");
if ($resRiwayat) {
    while ($r = $resRiwayat->fetch_assoc()) {
        $riwayat[] = $r;
    }
}

// Format status jatuh tempo
$jatuhTempoBadge = '-';
if (!empty($outlet['tgl_jatuh_tempo'])) {
    $jatuhTempoStr = date('d/m/Y', strtotime($outlet['tgl_jatuh_tempo']));
    $isExpired = strtotime($outlet['tgl_jatuh_tempo']) < time();
    $jatuhTempoBadge = $isExpired
        ? $jatuhTempoStr . ' <span class="badge bg-danger ms-1">Expired</span>'
        : $jatuhTempoStr . ' <span class="badge bg-success ms-1">Aktif</span>';
} else {
    $jatuhTempoBadge = '<span class="badge bg-secondary">Belum Diatur</span>';
}
?>

<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Riwayat Pembayaran Langganan</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/dashboard">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= SystemInfo::app('ADMIN_URL') ?>/outlet/view">Data Outlet</a></li>
            <li class="breadcrumb-item active" aria-current="page">Riwayat Pembayaran</li>
        </ol>
    </div>
</div>

<div class="row row-sm">
    <div class="col-lg-12">
        <div class="card custom-card overflow-hidden">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="card-title mb-0">Riwayat Pembayaran: <?= htmlspecialchars($outlet['nama_outlet']) ?></h5>
                    <a href="<?= SystemInfo::app('ADMIN_URL') ?>/outlet/view" class="btn btn-secondary btn-sm">
                        <i class="fe fe-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">

                <!-- Info Outlet Box -->
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <div class="text-muted small mb-1">Informasi Outlet</div>
                            <div class="d-flex align-items-center mb-1">
                                <strong class="text-dark fs-15"><?= htmlspecialchars($outlet['nama_outlet']) ?></strong>
                            </div>
                            <div class="small text-muted">
                                Pengelola: <strong class="text-dark"><?= htmlspecialchars($outlet['pengelola_toko'] ?? '-') ?></strong>
                                <?php if (!empty($outlet['no_hp_toko'])) : ?>
                                    <span class="mx-1">&bull;</span> <i class="fab fa-whatsapp text-success me-1"></i><?= htmlspecialchars($outlet['no_hp_toko']) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small mb-1">Investor Yang Menaungi</div>
                            <?php if (!empty($outlet['nama_investor'])) : ?>
                                <div class="d-flex align-items-center mb-1">
                                    <strong class="text-primary fs-15"><?= htmlspecialchars($outlet['nama_investor']) ?></strong>
                                </div>
                                <div class="small text-muted">
                                    <?php if (!empty($outlet['username_investor'])) : ?>
                                        <code>@<?= htmlspecialchars($outlet['username_investor']) ?></code>
                                    <?php endif; ?>
                                    <?php if (!empty($outlet['no_hp_investor'])) : ?>
                                        <span class="mx-1">&bull;</span> <i class="fab fa-whatsapp text-success me-1"></i><?= htmlspecialchars($outlet['no_hp_investor']) ?>
                                    <?php endif; ?>
                                </div>
                            <?php else : ?>
                                <div class="text-muted fs-15">Belum Ada Investor</div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small mb-1">Status Langganan</div>
                            <div class="mb-1">
                                Jatuh Tempo: <strong><?= $jatuhTempoBadge ?></strong>
                            </div>
                            <div class="small text-muted">
                                Biaya Langganan: <strong class="text-success fs-14">Rp <?= number_format($outlet['biaya_langganan_outlet'] ?? 100000, 0, ',', '.') ?> / Bln</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DataTable Riwayat -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover key-buttons text-nowrap w-100 align-middle" id="table-histori-langganan">
                        <thead>
                            <tr class="text-center">
                                <th class="text-center" style="width: 5%;">NO</th>
                                <th class="text-center">TANGGAL REQUEST</th>
                                <th class="text-center">TIPE REQUEST</th>
                                <th class="text-center">NOMINAL</th>
                                <th class="text-center">STATUS</th>
                                <th class="text-center">JATUH TEMPO</th>
                                <th class="text-center" style="width: 12%;">BUKTI BAYAR</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($riwayat)) : ?>
                                <?php $no = 1; foreach ($riwayat as $r) : ?>
                                    <?php
                                    // Tanggal request
                                    $tglRequest = '-';
                                    if (!empty($r['tgl_request'])) {
                                        $dt = explode(' ', $r['tgl_request']);
                                        $p  = explode('-', $dt[0]);
                                        $tglRequest = (count($p) === 3 ? $p[2].'/'.$p[1].'/'.$p[0] : $dt[0])
                                                    . (isset($dt[1]) ? ' '.substr($dt[1], 0, 5) : '');
                                    }
                                    // Tipe request
                                    $tipeHtml = ($r['tipe_request'] ?? 'baru') === 'perpanjangan'
                                        ? '<span class="badge bg-warning text-dark"><i class="fas fa-sync-alt me-1"></i>Perpanjangan</span>'
                                        : '<span class="badge bg-info text-white"><i class="fas fa-plus-circle me-1"></i>Pendaftaran Baru</span>';
                                    // Nominal
                                    $nominalHtml = '<span class="text-success fw-bold">Rp ' . number_format($r['nominal_transfer'] ?? 0, 0, ',', '.') . '</span>';
                                    // Jatuh tempo
                                    $jt = '-';
                                    if (!empty($r['tgl_jatuh_tempo'])) {
                                        $jtParts = explode('-', explode(' ', $r['tgl_jatuh_tempo'])[0]);
                                        $jt = count($jtParts) === 3 ? $jtParts[2].'/'.$jtParts[1].'/'.$jtParts[0] : $r['tgl_jatuh_tempo'];
                                    }
                                    // Status
                                    $statusHtml = '-';
                                    if ($r['status'] === 'pending') {
                                        $statusHtml = '<span class="badge bg-warning text-dark">Pending</span>';
                                    } elseif ($r['status'] === 'active') {
                                        $statusHtml = '<span class="badge bg-success">Disetujui</span>';
                                    } elseif ($r['status'] === 'reject') {
                                        $alasan = htmlspecialchars($r['alasan_penolakan'] ?? '', ENT_QUOTES);
                                        $statusHtml = '<span class="badge bg-danger" title="' . $alasan . '">Ditolak</span>';
                                    }
                                    ?>
                                    <tr class="text-center">
                                        <td class="text-center fw-semibold text-muted"><?= $no++ ?></td>
                                        <td class="text-center"><?= $tglRequest ?></td>
                                        <td class="text-center"><?= $tipeHtml ?></td>
                                        <td class="text-center"><?= $nominalHtml ?></td>
                                        <td class="text-center"><?= $statusHtml ?></td>
                                        <td class="text-center text-muted"><?= $jt ?></td>
                                        <td class="text-center">
                                            <?php if (!empty($r['bukti_pembayaran'])) : ?>
                                                <button type="button" class="btn btn-outline-info btn-sm py-1 px-2.5"
                                                        onclick="previewBukti('<?= htmlspecialchars($r['bukti_pembayaran'], ENT_QUOTES) ?>',
                                                                              '<?= htmlspecialchars($outlet['nama_outlet'], ENT_QUOTES) ?>',
                                                                              '<?= htmlspecialchars($outlet['nama_investor'] ?? '-', ENT_QUOTES) ?>',
                                                                              '<?= number_format($r['nominal_transfer'] ?? 0, 0, ',', '.') ?>')">
                                                    <i class="fas fa-image me-1"></i>Lihat Bukti
                                                </button>
                                            <?php else : ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Belum ada riwayat pembayaran untuk outlet ini.</td>
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
function previewBukti(filePath, namaOutlet, namaInvestor, biayaLangganan) {
    if (!filePath) {
        Swal.fire('Informasi', 'Bukti pembayaran belum diunggah.', 'info');
        return;
    }
    var adminUrl = '<?= SystemInfo::app("ADMIN_URL") ?>';
    var proxyUrl = adminUrl + '/image-proxy.php?file=' + encodeURIComponent(filePath);
    var ext = filePath.split('.').pop().toLowerCase();
    if (ext === 'pdf') {
        window.open(proxyUrl, '_blank');
        return;
    }

    var infoHtml = '<div class="text-start bg-light p-3 rounded mb-3" style="font-size:13.5px; border:1px solid #e9ecef;">'
        + '<div class="d-flex align-items-center mb-2">'
        + '  <i class="fa fa-building text-primary me-2" style="width:20px; text-align:center;"></i>'
        + '  <span style="min-width:140px;" class="fw-bold">Nama Outlet:</span>'
        + '  <span class="text-dark fw-semibold">' + namaOutlet + '</span>'
        + '</div>'
        + '<div class="d-flex align-items-center mb-2">'
        + '  <i class="fa fa-handshake-o text-success me-2" style="width:20px; text-align:center;"></i>'
        + '  <span style="min-width:140px;" class="fw-bold">Nama Investor:</span>'
        + '  <span class="text-dark">' + (namaInvestor || '-') + '</span>'
        + '</div>'
        + '<div class="d-flex align-items-center">'
        + '  <i class="fa fa-money text-warning me-2" style="width:20px; text-align:center;"></i>'
        + '  <span style="min-width:140px;" class="fw-bold">Biaya Langganan:</span>'
        + '  <span class="text-success fw-bold">Rp ' + (biayaLangganan || '0') + '</span>'
        + '</div>'
        + '</div>';

    Swal.fire({
        title: '<i class="fa fa-file-text-o me-2 text-info"></i>Bukti Pembayaran Pendaftaran Outlet',
        html: infoHtml
            + '<img src="' + proxyUrl + '" '
            + 'style="max-width:100%;max-height:60vh;border-radius:8px;border:1px solid #dee2e6;object-fit:contain;" '
            + 'onerror="this.outerHTML=\'<p class=\\\'text-danger mt-2\\\'><i class=\\\'fa fa-exclamation-triangle me-1\\\'></i> Gambar gagal dimuat</p>\'">',
        showCloseButton: true,
        showConfirmButton: false,
        scrollbarPadding: false,
        heightAuto: false,
        width: 640
    });
}

$(document).ready(function() {
    if ($.fn.DataTable && !$.fn.DataTable.isDataTable('#table-histori-langganan')) {
        $('#table-histori-langganan').DataTable({
            processing: true,
            deferRender: true,
            scrollX: true,
            lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
            pageLength: 5,
            language: {
                searchPlaceholder: 'Cari riwayat...',
                sSearch: '',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                paginate: { first: 'First', last: 'Last', next: 'Next', previous: 'Previous' }
            },
            order: [[1, 'desc']]
        });

        if ($.fn.select2) {
            setTimeout(function() {
                $('#table-histori-langganan_wrapper .dataTables_length select').select2({
                    minimumResultsForSearch: Infinity,
                    width: 'auto'
                });
            }, 50);
        }
    }
});
</script>
