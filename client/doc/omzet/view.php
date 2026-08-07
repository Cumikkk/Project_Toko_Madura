<?php
use Config\Core\Database;
use App\Models\User;
use Config\Core\SystemInfo;

$user = User::user();
$db = Database::connect();
$userId = (int)($user['MBR_ID'] ?? $user['id_users'] ?? 0);
$role = strtolower($user['role'] ?? 'outlet');
$isInvestor = ($role === 'investor');

if ($isInvestor) {
    header("Location: " . SystemInfo::app('CLIENT_URL') . "/bagi-hasil");
    exit;
}

// Array nama bulan Bahasa Indonesia
$bulanIndo = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Get Outlet Record for Logged-In User
$resOutlet = $db->query("SELECT o.*, u_inv.alamat as alamat_investor, u_inv.nama_lengkap as nama_investor FROM outlet o LEFT JOIN investor i ON o.id_investor = i.id_investor LEFT JOIN users u_inv ON i.id_users = u_inv.id_users WHERE o.id_users = {$userId} LIMIT 1");
$outlet = $resOutlet ? $resOutlet->fetch_assoc() : null;

// Get Current Cut Percentage directly from Outlet record (set during registration by Investor)
$presentaseGlobal = isset($outlet['persentase_potongan']) ? (float)$outlet['persentase_potongan'] : 10.00;

$activeTab = $_GET['tab'] ?? 'input';

// Filter Logic (Rentang Tanggal tgl_mulai & tgl_selesai)
$selectedTglMulai   = isset($_GET['tgl_mulai']) && !empty($_GET['tgl_mulai']) ? trim($_GET['tgl_mulai']) : '';
$selectedTglSelesai = isset($_GET['tgl_selesai']) && !empty($_GET['tgl_selesai']) ? trim($_GET['tgl_selesai']) : '';
$selectedBulan      = isset($_GET['bulan']) ? (int)$_GET['bulan'] : 0;
$selectedTahun      = isset($_GET['tahun']) ? (int)$_GET['tahun'] : 0;

$laporanList = [];
$totalOmzet = 0;
$totalHariInput = 0;
$availableYears = [];
$isLastDayDone = false;

if ($outlet) {
    $idOutlet = (int)$outlet['id_outlet'];

    // Fetch distinct years available in database
    $resYears = $db->query("SELECT DISTINCT YEAR(periode_laporan) as y_periode FROM laporan_omzet WHERE id_outlet = {$idOutlet} ORDER BY y_periode DESC");
    if ($resYears) {
        while ($yRow = $resYears->fetch_assoc()) {
            $availableYears[] = (int)$yRow['y_periode'];
        }
    }
    if (!in_array((int)date('Y'), $availableYears)) {
        array_unshift($availableYears, (int)date('Y'));
    }

    $whereConditions = ["id_outlet = {$idOutlet}"];
    $labelParts = [];

    if (!empty($selectedTglMulai) && !empty($selectedTglSelesai)) {
        $safeMulai = $db->real_escape_string($selectedTglMulai);
        $safeSelesai = $db->real_escape_string($selectedTglSelesai);
        $whereConditions[] = "periode_laporan BETWEEN '{$safeMulai}' AND '{$safeSelesai}'";
        
        if ($selectedTglMulai === $selectedTglSelesai) {
            $labelParts[] = date('d/m/Y', strtotime($selectedTglMulai));
        } else {
            $labelParts[] = date('d/m/Y', strtotime($selectedTglMulai)) . ' - ' . date('d/m/Y', strtotime($selectedTglSelesai));
        }
    } elseif (!empty($selectedTglMulai)) {
        $safeMulai = $db->real_escape_string($selectedTglMulai);
        $whereConditions[] = "periode_laporan >= '{$safeMulai}'";
        $labelParts[] = 'Mulai ' . date('d/m/Y', strtotime($selectedTglMulai));
    } elseif (!empty($selectedTglSelesai)) {
        $safeSelesai = $db->real_escape_string($selectedTglSelesai);
        $whereConditions[] = "periode_laporan <= '{$safeSelesai}'";
        $labelParts[] = 's/d ' . date('d/m/Y', strtotime($selectedTglSelesai));
    } else {
        if ($selectedBulan > 0) {
            $whereConditions[] = "MONTH(periode_laporan) = {$selectedBulan}";
            $labelParts[] = $bulanIndo[$selectedBulan] ?? '';
        }
        if ($selectedTahun > 0) {
            $whereConditions[] = "YEAR(periode_laporan) = {$selectedTahun}";
            $labelParts[] = $selectedTahun;
        }
    }

    $periodeLabelStr = !empty($labelParts) ? implode(" ", $labelParts) : "Semua Periode";
    $whereSql = implode(" AND ", $whereConditions);

    // Determine last day of selected month/year for checking if end of month is reached
    $checkBulan = ($selectedBulan > 0) ? $selectedBulan : (int)date('n');
    $checkTahun = ($selectedTahun > 0) ? $selectedTahun : (int)date('Y');
    $lastDayDateStr = sprintf('%04d-%02d-%02d', $checkTahun, $checkBulan, cal_days_in_month(CAL_GREGORIAN, $checkBulan, $checkTahun));

    // Check if last day of this month is submitted
    $chkLastDay = $db->query("SELECT id_laporan FROM laporan_omzet WHERE id_outlet = {$idOutlet} AND periode_laporan = '{$lastDayDateStr}' LIMIT 1");
    $isLastDayDone = ($chkLastDay && $chkLastDay->num_rows > 0);

    // Fetch all matching reports for accumulation
    $allLaporanList = [];
    $sqlLaporan = "SELECT * FROM laporan_omzet WHERE {$whereSql} ORDER BY periode_laporan DESC, id_laporan DESC";
    $resLaporan = $db->query($sqlLaporan);
    if ($resLaporan) {
        while ($row = $resLaporan->fetch_assoc()) {
            $allLaporanList[] = $row;
            $totalOmzet += (float)$row['omzet'];
        }
    }
    $totalHariInput = count($allLaporanList);

    // Server-side Pagination Logic (Max 10 Records per Page)
    $limitPerPage = 10;
    $totalOmzetRecords = $totalHariInput;
    $totalPages = ($totalOmzetRecords > 0) ? (int)ceil($totalOmzetRecords / $limitPerPage) : 1;
    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($currentPage < 1) $currentPage = 1;
    if ($currentPage > $totalPages) $currentPage = $totalPages;

    $offset = ($currentPage - 1) * $limitPerPage;
    $laporanList = array_slice($allLaporanList, $offset, $limitPerPage);
}

// Calculate 10% monthly cut ONLY IF last day submitted
$totalPotonganBulanan = $isLastDayDone ? round($totalOmzet * ($presentaseGlobal / 100), 2) : 0.00;
$totalBersihOutlet = $totalOmzet - $totalPotonganBulanan;
?>

<style>
/* Auto Theme Adaptive Variables & Controls */
.custom-tab-container {
    background-color: var(--bs-tertiary-bg, #f8fafc) !important;
    border: 1px solid var(--bs-border-color, #e2e8f0) !important;
    border-radius: 14px;
    padding: 5px;
    display: inline-flex;
    align-items: center;
}
.custom-tab-container .nav-pills {
    margin: 0;
    padding: 0;
    width: 100%;
    display: flex;
    flex-wrap: nowrap;
    gap: 4px;
}
.custom-tab-container .nav-item {
    margin: 0;
}
.custom-tab-container .nav-link {
    color: var(--bs-body-color, #475569) !important;
    background-color: transparent !important;
    font-weight: 700 !important;
    font-size: 14px !important;
    padding: 10px 22px !important;
    border-radius: 10px !important;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    white-space: nowrap !important;
    border: none !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}
.custom-tab-container .nav-link:hover {
    color: #7D0A0A !important;
    background-color: rgba(125, 10, 10, 0.08) !important;
}
.custom-tab-container .nav-link.active,
.custom-tab-container .nav-link.active:hover,
.custom-tab-container .nav-link.active:focus,
.custom-tab-container .nav-link.active:active {
    background: linear-gradient(135deg, #7D0A0A 0%, #4D0709 100%) !important;
    color: #ffffff !important;
    box-shadow: 0 4px 14px rgba(125, 10, 10, 0.3) !important;
}

@media (max-width: 575.98px) {
    .custom-tab-container {
        width: 100% !important;
        display: flex !important;
        padding: 4px !important;
        border-radius: 12px !important;
    }
    .custom-tab-container .nav-pills {
        width: 100% !important;
        gap: 2px !important;
    }
    .custom-tab-container .nav-link {
        font-size: 12px !important;
        padding: 9px 8px !important;
        border-radius: 8px !important;
        text-align: center !important;
        width: 100% !important;
    }
    .custom-tab-container .nav-link i {
        font-size: 13px !important;
        margin-right: 4px !important;
    }
}

/* Premium Theme Adaptive Rekapitulasi Banner */
.rekap-card-grand {
    background-color: var(--bs-card-bg, #ffffff);
    border-radius: 16px;
    border: 1px solid var(--bs-border-color, #edf2f7);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}
.rekap-item-box {
    padding: 16px 20px;
    border-radius: 14px;
    transition: all 0.25s ease;
}
.rekap-box-omzet {
    background: rgba(25, 135, 84, 0.08);
    border: 1px solid rgba(25, 135, 84, 0.25);
    color: var(--bs-body-color);
}
.rekap-box-potongan {
    background: rgba(220, 53, 69, 0.08);
    border: 1px solid rgba(220, 53, 69, 0.25);
    color: var(--bs-body-color);
}
.rekap-box-bersih {
    background: linear-gradient(135deg, #7D0A0A 0%, #4D0709 100%);
    border: 1px solid #7D0A0A;
    color: #ffffff !important;
}

/* Override layout agar footer naik mendekati card form (bukan nempel di dasar viewport) */
/* main.js mengeset min-height: windowHeight-70px secara dinamis, harus ditimpa */
.main-content {
    padding-bottom: 0px !important;
    min-height: auto !important;
}
.footer {
    position: relative !important;
    margin-top: 12px !important;
}
#paneInputOmzet {
    padding-top: 8px;
    padding-bottom: 16px;
}

/* Sleek Clickable Date Input Field Styling */
.date-input-custom-group .input-group-text {
    background-color: rgba(125, 10, 10, 0.08) !important;
    border-color: var(--bs-border-color, #dee2e6) !important;
    color: #7D0A0A !important;
    font-size: 18px;
    padding-left: 16px;
    padding-right: 16px;
    cursor: pointer;
}
.date-input-custom-group .form-control {
    border-color: var(--bs-border-color, #dee2e6) !important;
    color: var(--bs-body-color) !important;
    background-color: var(--bs-body-bg) !important;
    font-weight: 600;
    transition: all 0.2s ease;
    cursor: pointer;
}
.date-input-custom-group .form-control:focus {
    border-color: #7D0A0A !important;
    box-shadow: 0 0 0 0.25rem rgba(125, 10, 10, 0.15) !important;
}

@media (max-width: 575.98px) {
    .custom-tab-container {
        width: 100%;
        display: flex;
    }
    .custom-tab-container .nav-pills {
        width: 100%;
    }
    .custom-tab-container .nav-item {
        flex: 1;
    }
    .custom-tab-container .nav-link {
        font-size: 13px !important;
        padding: 9px 8px !important;
        width: 100% !important;
    }
    .rekap-item-box {
        padding: 12px 14px;
    }
}
</style>

<div class="main-content-inner pt-0 mt-0">
    <?php if (!$outlet) : ?>
        <div class="alert alert-warning border-0 shadow-sm rounded-4 p-4 mb-4 text-center">
            <i class="fa-light fa-circle-exclamation text-warning mb-3" style="font-size: 50px;"></i>
            <h4 class="fw-bold text-body-emphasis mb-2">Akun Belum Terhubung dengan Outlet</h4>
            <p class="text-body-secondary mb-0">Akun Anda belum memiliki data outlet terdaftar di sistem. Mohon hubungi pihak Investor untuk melakukan pendaftaran outlet Anda.</p>
        </div>
    <?php else : ?>

        <?php
            $tglJoinRaw = !empty($outlet['tanggal_bergabung']) ? $outlet['tanggal_bergabung'] : (!empty($outlet['tanggal_disetujui']) ? $outlet['tanggal_disetujui'] : (!empty($outlet['tanggal_request']) ? $outlet['tanggal_request'] : ''));
            $tglJoinFormatted = (!empty($tglJoinRaw) && strtotime($tglJoinRaw) > 0) ? (date('d', strtotime($tglJoinRaw)) . ' ' . ($bulanIndo[(int)date('n', strtotime($tglJoinRaw))] ?? '') . ' ' . date('Y', strtotime($tglJoinRaw))) : '-';
            $namaInvestorStr = !empty($outlet['nama_investor']) ? $outlet['nama_investor'] : 'Investor Mitra';
            
            $kecamatanStr = !empty($outlet['kecamatan']) ? trim($outlet['kecamatan']) : '';
            $alamatLengkapStr = !empty($outlet['alamat_outlet']) ? trim($outlet['alamat_outlet']) : '';

            if (isset($isInvestor) && $isInvestor) {
                // Investor mode: Show Kecamatan only
                $lokasiDisplay = !empty($kecamatanStr) ? ('Kec. ' . $kecamatanStr) : (!empty($alamatLengkapStr) ? $alamatLengkapStr : 'Lokasi belum diisi');
            } else {
                // Outlet mode: Show BOTH Kecamatan and Alamat Lengkap
                if (!empty($kecamatanStr) && !empty($alamatLengkapStr)) {
                    $lokasiDisplay = 'Kec. ' . $kecamatanStr . ' — ' . $alamatLengkapStr;
                } elseif (!empty($kecamatanStr)) {
                    $lokasiDisplay = 'Kec. ' . $kecamatanStr;
                } elseif (!empty($alamatLengkapStr)) {
                    $lokasiDisplay = $alamatLengkapStr;
                } else {
                    $lokasiDisplay = 'Lokasi & Alamat Toko belum diisi';
                }
            }

            // Calculate Expiration Days Remaining for Outlet Warning Banner
            $jtRaw = $outlet['tgl_jatuh_tempo'] ?? '';
            $jtFormatted = (!empty($jtRaw) && strtotime($jtRaw) > 0) ? (date('d', strtotime($jtRaw)) . ' ' . ($bulanIndo[(int)date('n', strtotime($jtRaw))] ?? '') . ' ' . date('Y', strtotime($jtRaw))) : '-';

            $daysRemaining = null;
            if (!empty($jtRaw) && strtotime($jtRaw) > 0) {
                $todayTs = strtotime(date('Y-m-d'));
                $jtTs = strtotime(date('Y-m-d', strtotime($jtRaw)));
                $daysRemaining = (int)ceil(($jtTs - $todayTs) / 86400);
            }
        ?>

        <!-- Banner Warning Masa Langganan (Mendekati Expired H-7 s.d H-0) -->
        <?php if (!$isInvestor && $daysRemaining !== null && $daysRemaining >= 0 && $daysRemaining <= 7) : ?>
            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-warning border border-warning-subtle shadow-sm rounded-4 p-3 mb-0 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: rgba(255, 193, 7, 0.1);">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; font-size: 18px;">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1" style="font-size: 13.5px;">
                                    Peringatan Masa Langganan Toko (H-<?= $daysRemaining; ?> Expired)
                                </h6>
                                <p class="text-body-secondary mb-0" style="font-size: 12px;">
                                    Masa langganan toko Anda akan berakhir pada <strong><?= $jtFormatted; ?></strong> (tersisa <strong><?= $daysRemaining; ?> hari lagi</strong>). Silakan ingatkan Investor Anda (<strong><?= htmlspecialchars($namaInvestorStr); ?></strong>) untuk melakukan perpanjangan.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Header Banner Card (TAMPILAN PROFIL OUTLET BALANCED & PRESISI TINGGI PC/MOBILE) -->
        <div class="row mt-0 pt-0 mb-3">
            <div class="col-12">
                <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 16px; background: linear-gradient(135deg, #7D0A0A 0%, #4D0709 100%); color: #fff;">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                            
                            <!-- Sisi Kiri: Identitas Utama Toko -->
                            <div class="me-md-auto">
                                <div class="d-flex align-items-center flex-nowrap gap-2.5 mb-2">
                                    <i class="fa-solid fa-store text-warning fs-4 flex-shrink-0"></i>
                                    <h3 class="fw-extrabold mb-0 text-white fs-3 lh-sm text-nowrap"><?= htmlspecialchars($outlet['nama_outlet']); ?></h3>
                                </div>
                                <div class="d-flex align-items-start gap-2.5 text-white-50 small mt-1.5" style="line-height: 1.45;">
                                    <i class="fa-solid fa-location-dot text-warning flex-shrink-0 mt-0.5 me-1" style="font-size: 13.5px;"></i>
                                    <span><?= htmlspecialchars($lokasiDisplay); ?></span>
                                </div>
                            </div>

                            <!-- Sisi Kanan (POJOK KANAN AT PC): Metadata Badges -->
                            <div class="w-100 w-md-auto ms-md-auto">
                                <div class="d-flex flex-row align-items-center gap-2 justify-content-end">
                                    <!-- Investor Mitra -->
                                    <div class="flex-fill flex-md-grow-0 px-3 py-2 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-15">
                                        <small class="text-white-50 d-block text-uppercase fw-semibold mb-0.5 text-nowrap" style="font-size: 10px; letter-spacing: 0.5px;">
                                            <i class="fa-solid fa-handshake me-1 text-warning"></i>Investor Mitra
                                        </small>
                                        <span class="fw-bold text-white fs-6 d-block text-truncate"><?= htmlspecialchars($namaInvestorStr); ?></span>
                                    </div>
                                    <!-- Tanggal Bergabung -->
                                    <div class="flex-fill flex-md-grow-0 px-3 py-2 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-15">
                                        <small class="text-white-50 d-block text-uppercase fw-semibold mb-0.5 text-nowrap" style="font-size: 10px; letter-spacing: 0.5px;">
                                            <i class="fa-solid fa-calendar-check me-1 text-warning"></i>Tgl Bergabung
                                        </small>
                                        <span class="fw-bold text-white fs-6 d-block text-truncate"><?= htmlspecialchars($tglJoinFormatted); ?></span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>        <?php if ($activeTab !== 'riwayat') : ?>
                <!-- HALAMAN 1: FORM INPUT OMZET HARIAN -->
                <div class="row justify-content-center mb-4">
                    <div class="col-12 col-md-10 col-lg-8 col-xl-7">
                        <div class="card border border-body-subtle shadow-sm mb-0" style="border-radius: 20px;">
                            <div class="card-body p-4 p-md-5">
                                <div class="text-center mb-4">
                                    <div class="badge bg-danger-subtle text-danger fw-bold rounded-pill px-3 py-2 mb-2">
                                        <i class="fa-light fa-cash-register me-1"></i> Form Penginputan Harian
                                    </div>
                                    <h4 class="fw-extrabold text-body-emphasis mb-1">Input Omzet Harian Toko</h4>
                                    <p class="text-body-secondary small mb-0">Masukkan total hasil penjualan kotor toko Anda untuk tanggal hari ini</p>
                                </div>

                                <form id="formInputOmzet" method="POST">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="id_outlet" value="<?= (int)($outlet['id_outlet'] ?? 0); ?>">

                                     <!-- Tanggal Omzet -->
                                     <div class="mb-4">
                                         <label for="periode_laporan" class="form-label fw-bold text-body-emphasis small text-uppercase">
                                             <i class="fa-light fa-calendar-day me-1 text-danger"></i>Tanggal Omzet <span class="text-danger">*</span>
                                         </label>
                                         <div class="input-group input-group-lg date-picker-wrapper cursor-pointer">
                                             <span class="input-group-text bg-body-tertiary border-body-subtle text-danger">
                                                 <i class="fa-solid fa-calendar-days fs-5"></i>
                                             </span>
                                             <input type="date" name="periode_laporan" id="periode_laporan" class="form-control border-body-subtle bg-body text-body-emphasis fw-bold cursor-pointer" value="<?= date('Y-m-d'); ?>" required onclick="if(this.showPicker){this.showPicker();}">
                                         </div>
                                     </div>

                                    <!-- Nominal Omzet (with Right Corner Up/Down Arrow Stepper Buttons) -->
                                    <div class="mb-4">
                                        <label for="omzet_input_display" class="form-label fw-bold text-body-emphasis small text-uppercase">
                                            <i class="fa-light fa-money-bill-wave me-1 text-danger"></i>Nominal Omzet (Rp) <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-body-tertiary border-body-subtle fw-bold text-body">Rp</span>
                                            <input type="text" id="omzet_input_display" class="form-control border-body-subtle bg-body text-body fw-extrabold fs-4" placeholder="0" required autocomplete="off">
                                            <input type="hidden" name="omzet" id="omzet_input_real" value="">

                                            <!-- Tombol Panah Naik & Turun di Pojok Kanan Kolom -->
                                            <div class="input-group-text p-0 bg-body border-body-subtle overflow-hidden d-flex flex-column" style="border-top-right-radius: 0.5rem; border-bottom-right-radius: 0.5rem;">
                                                <button type="button" class="btn btn-sm btn-light border-0 rounded-0 px-3 py-1 flex-fill text-danger border-bottom" id="btnOmzetPlus" title="Tambah Kelipatan Rp 500" style="line-height: 1;">
                                                    <i class="fa-solid fa-chevron-up fs-6"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-light border-0 rounded-0 px-3 py-1 flex-fill text-secondary" id="btnOmzetMinus" title="Kurangi Kelipatan Rp 500" style="line-height: 1;">
                                                    <i class="fa-solid fa-chevron-down fs-6"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-danger btn-lg rounded-pill fw-bold py-3 shadow">
                                            <i class="fa-solid fa-paper-plane me-2"></i> Simpan Omzet Harian
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <!-- HALAMAN 2: RIWAYAT & REKAP BAGI HASIL -->
                <?php 
                    // Aturan Potongan 10% Diterapkan Setiap Hari (10% Per Hari)
                    $isDeductionActive = true;
                    $potongan10Val  = round($totalOmzet * ($presentaseGlobal / 100), 2);
                    $hakInvestorVal = round($potongan10Val * 0.50, 2);
                    $hakOutletVal   = round($potongan10Val * 0.50, 2);
                ?>

                <!-- 1. TABEL RIWAYAT OMZET (DITAMPILKAN DI ATAS DULUAN) -->
                <div class="card border border-body-subtle shadow-sm mb-4" style="border-radius: 16px;">
                    <div class="card-header bg-transparent border-0 pt-3 pt-md-4 px-3 px-md-4 pb-0 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
                        <div>
                            <h5 class="fw-bold mb-1 fs-5 text-body-emphasis text-nowrap"><i class="fa-solid fa-receipt me-2 text-danger"></i>Riwayat Input Omzet Toko</h5>
                            <p class="text-body-secondary small mb-0">Periode: <strong><?= htmlspecialchars($periodeLabelStr); ?></strong> • <span class="badge bg-primary-subtle text-primary fw-bold rounded-pill px-2 py-1"><i class="fa-solid fa-calendar-check me-1"></i><?= $totalHariInput; ?> Hari</span></p>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-nowrap w-100 w-sm-auto justify-content-between justify-content-sm-end">
                            <!-- Tombol Filter Data (Membuka Modal) -->
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-2 shadow-sm fw-bold flex-fill flex-sm-grow-0 text-nowrap" data-bs-toggle="modal" data-bs-target="#modalFilterOmzetRiwayat">
                                <i class="fa-solid fa-filter me-1"></i> Filter Data
                            </button>
                            <!-- Tombol Cetak PDF Laporan -->
                            <a href="<?= SystemInfo::app('CLIENT_URL'); ?>/doc/omzet/export_pdf.php?tgl_mulai=<?= urlencode($selectedTglMulai); ?>&tgl_selesai=<?= urlencode($selectedTglSelesai); ?>&bulan=<?= $selectedBulan; ?>&tahun=<?= $selectedTahun; ?>" target="_blank" class="btn btn-danger btn-sm rounded-pill px-3 py-2 shadow-sm fw-bold flex-fill flex-sm-grow-0 text-nowrap text-center">
                                <i class="fa-solid fa-file-pdf me-1"></i> Cetak PDF
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-2 p-md-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 w-100" id="tableRiwayatOmzet">
                                <thead class="table-group-divider bg-body-secondary">
                                    <tr class="text-uppercase small text-body-secondary">
                                        <th class="ps-3" style="width: 40px;">No</th>
                                        <th>Tanggal Omzet</th>
                                        <th>Waktu Input</th>
                                        <th>Nominal Omzet Harian</th>
                                        <th class="text-center pe-3" style="width: 100px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($laporanList)) : ?>
                                        <?php foreach ($laporanList as $index => $row) : ?>
                                            <?php 
                                                $omz = (float)$row['omzet'];
                                                $t = strtotime($row['periode_laporan']);
                                                $tglStr = date('d/m/Y', $t);
                                                $itemNo = $offset + $index + 1;
                                            ?>
                                            <tr>
                                                <td class="ps-3 fw-bold text-body-secondary"><?= $itemNo; ?></td>
                                                <td>
                                                    <span class="fw-bold text-body-emphasis fs-6">
                                                        <i class="fa-solid fa-calendar-days text-danger me-1"></i>
                                                        <?= date('d M Y', $t); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-body-secondary">
                                                        <?= date('d/m/Y H:i', strtotime($row['waktu_input'])); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-body-emphasis fs-6">
                                                        Rp <?= number_format($omz, 0, ',', '.'); ?>
                                                    </span>
                                                </td>
                                                <td class="text-center pe-3">
                                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                                        <button type="button" class="btn btn-sm btn-light border text-info btn-detail-laporan rounded-3 px-2 py-1" data-id="<?= $row['id_laporan']; ?>" title="Lihat Detail">
                                                            <i class="fa-light fa-eye"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-light border text-warning btn-edit-laporan rounded-3 px-2 py-1" data-id="<?= $row['id_laporan']; ?>" title="Edit Laporan">
                                                            <i class="fa-light fa-pen-to-square"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
                                                <div class="py-4">
                                                    <i class="fa-light fa-file-invoice-dollar text-body-secondary mb-3" style="font-size: 60px; opacity: 0.5;"></i>
                                                    <h5 class="fw-bold text-body-secondary mb-1">Belum Ada Omzet Harian Terinput</h5>
                                                    <p class="text-body-secondary small mb-3">Gunakan form input di tab sebelah untuk mendaftarkan omzet harian toko Anda.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- PAGINASI TABLE (MAX 10 DATA PER HALAMAN) -->
                    <?php if (isset($totalPages) && $totalPages > 1) : ?>
                        <div class="card-footer bg-transparent border-top py-3 px-3 px-md-4 d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2">
                            <small class="text-body-secondary fw-semibold">
                                Menampilkan <?= min($offset + 1, $totalOmzetRecords); ?> - <?= min($offset + count($laporanList), $totalOmzetRecords); ?> dari total <?= $totalOmzetRecords; ?> data omzet
                            </small>
                            <nav aria-label="Navigasi Halaman Riwayat Omzet">
                                <ul class="pagination pagination-sm mb-0">
                                    <!-- Previous Button -->
                                    <li class="page-item <?= ($currentPage <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link rounded-pill-start px-3" href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage - 1])); ?>">
                                            <i class="fa-solid fa-chevron-left me-1"></i> Prev
                                        </a>
                                    </li>

                                    <!-- Page Number Buttons -->
                                    <?php for ($p = 1; $p <= $totalPages; $p++) : ?>
                                        <li class="page-item <?= ($p === $currentPage) ? 'active' : ''; ?>">
                                            <a class="page-link fw-bold" href="?<?= http_build_query(array_merge($_GET, ['page' => $p])); ?>"><?= $p; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- Next Button -->
                                    <li class="page-item <?= ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                                        <a class="page-link rounded-pill-end px-3" href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])); ?>">
                                            Next <i class="fa-solid fa-chevron-right ms-1"></i>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- REKAPITULASI RINGKASAN OMZET OUTLET -->
                <div class="row g-3 mb-4">
                    <!-- Total Omzet Toko -->
                    <div class="col-12 col-md-6">
                        <div class="card border border-body-subtle shadow-sm h-100" style="border-radius: 16px; background: linear-gradient(135deg, rgba(13,110,253,0.03) 0%, rgba(13,110,253,0.08) 100%);">
                            <div class="card-body p-3 p-md-4">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-body-secondary small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Total Omzet Terkumpul</span>
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                        <i class="fa-solid fa-coins fs-5"></i>
                                    </div>
                                </div>
                                <h3 class="fw-extrabold text-body-emphasis mb-1 fs-3">Rp <?= number_format($totalOmzet, 0, ',', '.'); ?></h3>
                                <p class="text-body-secondary small mb-0"><i class="fa-solid fa-circle-info me-1 text-primary"></i>Akumulasi total omzet berjalan toko</p>
                            </div>
                        </div>
                    </div>

                    <!-- Total Hari Input Omzet -->
                    <div class="col-12 col-md-6">
                        <div class="card border border-body-subtle shadow-sm h-100" style="border-radius: 16px; background: linear-gradient(135deg, rgba(25,135,84,0.03) 0%, rgba(25,135,84,0.08) 100%);">
                            <div class="card-body p-3 p-md-4">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-success small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Total Hari Input Omzet</span>
                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                        <i class="fa-solid fa-calendar-check fs-5"></i>
                                    </div>
                                </div>
                                <h3 class="fw-extrabold text-success mb-1 fs-3"><?= $totalHariInput; ?> Hari</h3>
                                <p class="text-body-secondary small mb-0"><i class="fa-solid fa-circle-check me-1 text-success"></i>Jumlah hari terdaftar penginputan omzet</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Compact Footer Distance -->
                <div class="pb-2"></div>
            <?php endif; ?>
    <?php endif; ?>
</div>

<!-- ========================================================================= -->
<!-- MODAL: EDIT LAPORAN OMZET (Auto Theme Adaptive) -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalEditLaporan" tabindex="-1" aria-labelledby="modalEditLaporanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow bg-body" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-body-emphasis" id="modalEditLaporanLabel">
                    <i class="fa-light fa-pen-to-square me-2 text-warning"></i>Edit Input Omzet Harian
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditLaporan" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id_laporan" id="edit_id_laporan" value="">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-body-secondary required">Tanggal Omzet</label>
                        <div class="input-group date-input-custom-group">
                            <span class="input-group-text border-end-0 rounded-start-3" title="Klik untuk pilih tanggal">
                                <i class="fa-solid fa-calendar-days text-danger"></i>
                            </span>
                            <input type="date" name="tanggal_omzet" id="edit_tanggal_omzet" class="form-control border-start-0 rounded-end-3" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-body-secondary required">Total Omzet Penjualan Harian</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-warning text-dark fw-bold border-end-0">Rp</span>
                            <input type="text" name="omzet" id="edit_omzet_val" class="form-control amount-formatter fw-bold border-start-0 text-body-emphasis fs-4 bg-body" placeholder="0" autocomplete="off" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold rounded-pill px-4">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Update Omzet Harian
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: DETAIL LAPORAN OMZET (Auto Theme Adaptive) -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalDetailLaporan" tabindex="-1" aria-labelledby="modalDetailLaporanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow bg-body" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-body-emphasis" id="modalDetailLaporanLabel">
                    <i class="fa-light fa-file-invoice-dollar me-2 text-info"></i>Rincian Omzet Harian
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="detailLaporanLoading" class="text-center py-4">
                    <div class="spinner-border text-danger" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div id="detailLaporanContent" class="d-none">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success p-3 mb-2" style="width: 64px; height: 64px;">
                            <i class="fa-light fa-money-bill-trend-up fs-2"></i>
                        </div>
                        <h3 class="fw-bold mb-1 text-success" id="det_omzet_head">Rp 0</h3>
                        <span class="badge bg-danger px-3 py-1 rounded-pill fs-6" id="det_periode_head">Tanggal: -</span>
                    </div>

                    <div class="list-group list-group-flush rounded-3 border border-body-subtle mb-3">
                        <div class="list-group-item bg-body d-flex justify-content-between align-items-center py-3">
                            <span class="text-body-secondary small"><i class="fa-solid fa-calendar-days me-2 text-danger"></i>Tanggal Omzet Harian</span>
                            <span class="fw-bold text-body-emphasis" id="det_periode">-</span>
                        </div>
                        <div class="list-group-item bg-body d-flex justify-content-between align-items-center py-3">
                            <span class="text-body-secondary small"><i class="fa-light fa-clock me-2 text-body-secondary"></i>Waktu Penginputan</span>
                            <span class="fw-semibold text-body-emphasis" id="det_waktu">-</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: FILTER DATA RIWAYAT OMZET (Auto Theme Adaptive) -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalFilterOmzetRiwayat" tabindex="-1" aria-labelledby="modalFilterOmzetRiwayatLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow bg-body" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-body-emphasis" id="modalFilterOmzetRiwayatLabel">
                    <i class="fa-solid fa-filter me-2 text-danger"></i>Filter Data Riwayat Omzet
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="GET" action="<?= SystemInfo::app('CLIENT_URL'); ?>/omzet">
                <input type="hidden" name="tab" value="riwayat">
                <div class="modal-body p-4">
                    <!-- Rentang Tanggal (Bebas: 1 Hari, 3 Hari, 1 Minggu, dll) -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-bold text-body-secondary mb-0">
                                <i class="fa-regular fa-calendar-range me-1 text-danger"></i>Pilih Rentang Tanggal (Bebas)
                            </label>
                            <button type="button" class="btn btn-sm btn-outline-danger border-0 fw-bold px-2 py-0" id="btnResetTanggalFilterOutlet" style="font-size: 11px;">
                                <i class="fa-solid fa-rotate-left me-1"></i>Reset Tanggal
                            </button>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label for="filterOutletTglMulai" class="text-body-secondary small d-block mb-1 cursor-pointer">Tanggal Mulai</label>
                                <div class="input-group input-group-sm cursor-pointer date-picker-wrapper">
                                    <span class="input-group-text bg-body-tertiary border-body-subtle text-danger">
                                        <i class="fa-solid fa-calendar-days"></i>
                                    </span>
                                    <input type="date" name="tgl_mulai" id="filterOutletTglMulai" class="form-control bg-body border-body-subtle text-body-emphasis fw-semibold cursor-pointer" value="<?= htmlspecialchars($selectedTglMulai); ?>" onclick="if(this.showPicker){this.showPicker();}">
                                </div>
                            </div>
                            <div class="col-6">
                                <label for="filterOutletTglSelesai" class="text-body-secondary small d-block mb-1 cursor-pointer">Tanggal Selesai</label>
                                <div class="input-group input-group-sm cursor-pointer date-picker-wrapper">
                                    <span class="input-group-text bg-body-tertiary border-body-subtle text-danger">
                                        <i class="fa-solid fa-calendar-days"></i>
                                    </span>
                                    <input type="date" name="tgl_selesai" id="filterOutletTglSelesai" class="form-control bg-body border-body-subtle text-body-emphasis fw-semibold cursor-pointer" value="<?= htmlspecialchars($selectedTglSelesai); ?>" onclick="if(this.showPicker){this.showPicker();}">
                                </div>
                            </div>
                        </div>
                        <div class="form-text text-body-secondary small mt-2">
                            <i class="fa-solid fa-circle-info me-1 text-primary"></i>*Klik <strong>Reset Tanggal</strong> untuk menghapus filter tanggal dan menampilkan <strong>seluruh akumulasi data</strong> tanpa batasan periode.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Tampilkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const ACTION_URL = '<?= SystemInfo::app('CLIENT_URL'); ?>/doc/omzet/action.php';

    // Instant Datepicker Pop-up Handler when clicking icon, wrapper, or label
    $(document).on('click', '.date-picker-wrapper', function() {
        const input = $(this).find('input[type="date"]')[0];
        if (input) {
            if (typeof input.showPicker === 'function') {
                try {
                    input.showPicker();
                } catch(err) {
                    input.focus();
                }
            } else {
                input.focus();
            }
        }
    });

    // Reset Tanggal Filter Event (Outlet View)
    $('#btnResetTanggalFilterOutlet').on('click', function() {
        $('#filterOutletTglMulai').val('');
        $('#filterOutletTglSelesai').val('');
    });

    // Instant Datepicker Pop-up Handler when clicking icon or date input
    $(document).on('click', '.date-input-custom-group .input-group-text, .date-input-custom-group .form-control', function() {
        const group = $(this).closest('.date-input-custom-group');
        const dateInput = group.find('input[type="date"]')[0];
        if (dateInput) {
            if (typeof dateInput.showPicker === 'function') {
                try {
                    dateInput.showPicker();
                } catch(err) {
                    dateInput.focus();
                }
            } else {
                dateInput.focus();
            }
        }
    });

    // Multi-Mode Filter Switcher & Handler
    $('#filterModeSelect').on('change', function() {
        const mode = $(this).val();
        $('.filter-sub-box').addClass('d-none');
        if (mode === 'harian') $('#boxHarian').removeClass('d-none');
        else if (mode === 'mingguan') $('#boxMingguan').removeClass('d-none');
        else if (mode === 'bulanan') $('#boxBulanan').removeClass('d-none');
        else if (mode === 'tahunan') $('#boxTahunan').removeClass('d-none');
    });

    function applyPeriodFilters() {
        const mode = $('#filterModeSelect').val();
        let query = '<?= SystemInfo::app('CLIENT_URL'); ?>/omzet?tab=riwayat&tipe=' + mode;

        if (mode === 'harian') {
            query += '&tanggal=' + ($('#filterInputTanggal').val() || '');
        } else if (mode === 'mingguan') {
            query += '&tgl_mulai=' + ($('#filterInputTglMulai').val() || '') + '&tgl_selesai=' + ($('#filterInputTglSelesai').val() || '');
        } else if (mode === 'bulanan') {
            query += '&bulan=' + ($('#filterBulan').val() || 0) + '&tahun=' + ($('#filterTahun').val() || 0);
        } else if (mode === 'tahunan') {
            query += '&tahun=' + ($('#filterTahunOnly').val() || 0);
        }

        window.location.href = query;
    }

    $('#btnApplyAdvancedFilter').on('click', function() {
        applyPeriodFilters();
    });

    $('#filterBulan, #filterTahun, #filterTahunOnly, #filterInputTanggal').on('change', function() {
        applyPeriodFilters();
    });

    // Stepper Button (+500 / -) Handler for Omzet Nominal Input
    const STEP_OMZET = 500;

    function formatRupiahDisplay(val) {
        if (!val || val <= 0) return '';
        return new Intl.NumberFormat('id-ID').format(val);
    }

    function syncOmzetValues(newVal) {
        newVal = Math.max(0, newVal);
        $('#omzet_input_real').val(newVal > 0 ? newVal : '');
        $('#omzet_input_display').val(formatRupiahDisplay(newVal));
    }

    $('#btnOmzetMinus').on('click', function() {
        let currentVal = parseInt($('#omzet_input_real').val()) || 0;
        currentVal = Math.max(0, currentVal - STEP_OMZET);
        syncOmzetValues(currentVal);
    });

    $('#btnOmzetPlus').on('click', function() {
        let currentVal = parseInt($('#omzet_input_real').val()) || 0;
        currentVal += STEP_OMZET;
        syncOmzetValues(currentVal);
    });

    $('#omzet_input_display').on('input keyup change', function() {
        let rawVal = $(this).val().replace(/[^\d]/g, '');
        let numVal = parseInt(rawVal) || 0;
        $('#omzet_input_real').val(numVal > 0 ? numVal : '');
        $(this).val(formatRupiahDisplay(numVal));
    });

    // Checkbox Master & Item Selection Handlers
    function updateBulkDeleteButton() {
        const checkedCount = $('.check-item-omzet:checked').length;
        const totalCount = $('.check-item-omzet').length;
        
        if (checkedCount > 0) {
            $('#selectedCount').text(checkedCount);
            $('#btnDeleteSelected').removeClass('d-none');
        } else {
            $('#btnDeleteSelected').addClass('d-none');
        }

        if (totalCount > 0 && checkedCount === totalCount) {
            $('#checkAllOmzet').prop('checked', true);
        } else {
            $('#checkAllOmzet').prop('checked', false);
        }
    }

    $('#checkAllOmzet').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.check-item-omzet').prop('checked', isChecked);
        updateBulkDeleteButton();
    });

    $(document).on('change', '.check-item-omzet', function() {
        updateBulkDeleteButton();
    });

    // Bulk Delete Selected Handler
    $('#btnDeleteSelected').on('click', function() {
        const selectedIds = [];
        $('.check-item-omzet:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            Swal.fire('Peringatan', 'Pilih sekurang-kurangnya satu data omzet.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Hapus Data Terpilih?',
            text: `Apakah Anda yakin ingin menghapus ${selectedIds.length} data omzet yang dipilih?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Ya, Hapus (${selectedIds.length})`,
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menghapus Data Terpilih...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: ACTION_URL,
                    type: 'POST',
                    data: { action: 'delete_selected', ids: selectedIds },
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Gagal menghapus data omzet terpilih.', 'error');
                    }
                });
            }
        });
    });

    // 1. Submit Form Input Omzet Harian
    $('#formInputOmzet').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');

        submitBtn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i> Menyimpan Omzet Harian...');

        $.ajax({
            url: ACTION_URL,
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(res) {
                submitBtn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane me-2"></i> Simpan Omzet Harian');
                if (res.success) {
                    form[0].reset();
                    $('#periode_laporan').val('<?= date('Y-m-d'); ?>');
                    $('#omzet_input_real').val('');
                    $('#omzet_input_display').val('');

                    const dt = res.data || {};
                    const htmlMockup = `
                        <div class="py-2 text-start">
                            <div class="text-center mb-3">
                                <div class="badge bg-success-subtle text-success fw-bold rounded-pill px-3 py-2 fs-6 mb-2">
                                    <i class="fa-solid fa-circle-check me-1"></i> Omzet Terdaftar
                                </div>
                                <h2 class="fw-extrabold text-success mb-1">${dt.omzet_formatted || 'Rp 0'}</h2>
                                <p class="text-body-secondary small mb-0">Laporan omzet harian toko berhasil tersimpan ke sistem</p>
                            </div>
                            
                            <div class="p-3 rounded-4 bg-body-tertiary border border-body-subtle">
                                <div class="d-flex justify-content-between mb-2 small">
                                    <span class="text-body-secondary"><i class="fa-solid fa-calendar-day me-1 text-danger"></i>Tanggal Omzet:</span>
                                    <strong class="text-body-emphasis">${dt.tgl_formatted || '-'}</strong>
                                </div>
                                <div class="d-flex justify-content-between small">
                                    <span class="text-body-secondary"><i class="fa-solid fa-clock me-1 text-primary"></i>Waktu Input:</span>
                                    <span class="text-body-emphasis">${dt.waktu_input || '-'}</span>
                                </div>
                            </div>
                        </div>
                    `;

                    Swal.fire({
                        title: 'Berhasil Input Omzet!',
                        html: htmlMockup,
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonColor: '#0d6efd',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="fa-solid fa-list-check me-1"></i> Lihat Riwayat Omzet',
                        cancelButtonText: '<i class="fa-solid fa-plus me-1"></i> Input Lagi'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '<?= SystemInfo::app('CLIENT_URL'); ?>/omzet?tab=riwayat';
                        }
                    });
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            },
            error: function() {
                submitBtn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane me-2"></i> Simpan Omzet Harian');
                Swal.fire('Error', 'Terjadi kesalahan sistem saat mengirim laporan.', 'error');
            }
        });
    });

    // 2. Fetch Detail for Edit
    $(document).on('click', '.btn-edit-laporan', function() {
        const idLaporan = $(this).data('id');
        
        $.ajax({
            url: ACTION_URL,
            type: 'GET',
            data: { action: 'get_detail', id_laporan: idLaporan },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#edit_id_laporan').val(res.data.id_laporan);
                    $('#edit_tanggal_omzet').val(res.data.tgl_formatted);
                    $('#edit_omzet_val').val(new Intl.NumberFormat('id-ID').format(res.data.omzet));
                    $('#modalEditLaporan').modal('show');
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Gagal mengambil data laporan.', 'error');
            }
        });
    });

    // 3. Submit Edit Laporan
    $('#formEditLaporan').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');

        submitBtn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Updating...');

        $.ajax({
            url: ACTION_URL,
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(res) {
                submitBtn.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk me-1"></i> Update Omzet Harian');
                if (res.success) {
                    $('#modalEditLaporan').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            },
            error: function() {
                submitBtn.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk me-1"></i> Update Omzet Harian');
                Swal.fire('Error', 'Terjadi kesalahan sistem saat memperbarui data.', 'error');
            }
        });
    });

    // 4. Fetch Detail for View
    $(document).on('click', '.btn-detail-laporan', function() {
        const idLaporan = $(this).data('id');
        $('#detailLaporanLoading').removeClass('d-none');
        $('#detailLaporanContent').addClass('d-none');
        $('#modalDetailLaporan').modal('show');

        $.ajax({
            url: ACTION_URL,
            type: 'GET',
            data: { action: 'get_detail', id_laporan: idLaporan },
            dataType: 'json',
            success: function(res) {
                $('#detailLaporanLoading').addClass('d-none');
                if (res.success) {
                    const omzetFormatted = 'Rp ' + new Intl.NumberFormat('id-ID').format(res.data.omzet);
                    $('#det_omzet_head').text(omzetFormatted);
                    $('#det_periode_head').text('Tanggal: ' + res.data.tgl_indo);
                    $('#det_periode').text(res.data.tgl_indo);
                    $('#det_waktu').text(res.data.waktu_input);
                    $('#detailLaporanContent').removeClass('d-none');
                } else {
                    $('#modalDetailLaporan').modal('hide');
                    Swal.fire('Gagal', res.message, 'error');
                }
            },
            error: function() {
                $('#modalDetailLaporan').modal('hide');
                Swal.fire('Error', 'Gagal mengambil rincian omzet harian.', 'error');
            }
        });
    });

    // 5. Delete Single Laporan with SweetAlert2
    $(document).on('click', '.btn-delete-laporan', function() {
        const idLaporan = $(this).data('id');
        const tglStr = $(this).data('tgl');

        Swal.fire({
            title: 'Hapus Omzet Harian?',
            text: `Apakah Anda yakin ingin menghapus omzet harian tanggal ${tglStr}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menghapus...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: ACTION_URL,
                    type: 'POST',
                    data: { action: 'delete', id_laporan: idLaporan },
                    dataType: 'json',
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Gagal menghapus omzet harian.', 'error');
                    }
                });
            }
        });
    });
});
</script>
